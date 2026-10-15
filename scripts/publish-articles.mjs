// Publishes content/articles/*.json to WordPress as drafts via the REST API.
// Auth uses a WordPress Application Password (never the account password),
// supplied through env vars that GitHub Actions fills from Secrets.
//
// Idempotent via content/.published-articles.json: once a file is published,
// its post ID is recorded there so re-running the workflow won't create
// duplicates. That state file lives outside content/articles/ so committing
// it back doesn't re-trigger this workflow.

import { readFile, writeFile, readdir } from "node:fs/promises";
import { createHash } from "node:crypto";
import path from "node:path";

const API_BASE = process.env.WP_API_BASE; // e.g. https://motonator.com/wp-json
const USERNAME = process.env.WP_USERNAME;
const APP_PASSWORD = process.env.WP_APP_PASSWORD;

if (!API_BASE || !USERNAME || !APP_PASSWORD) {
  console.error("Missing WP_API_BASE, WP_USERNAME, or WP_APP_PASSWORD env vars.");
  process.exit(1);
}

const authHeader = "Basic " + Buffer.from(`${USERNAME}:${APP_PASSWORD}`).toString("base64");
const ARTICLES_DIR = path.join(process.cwd(), "content", "articles");
const STATE_FILE = path.join(process.cwd(), "content", ".published-articles.json");
// sha1 of each article's WordPress-facing fields at the time it was last synced.
// When a JSON file changes after publishing, the existing post is updated in place.
const HASH_FILE = path.join(process.cwd(), "content", ".content-hashes.json");

async function loadJson(file) {
  try {
    return JSON.parse(await readFile(file, "utf8"));
  } catch {
    return {};
  }
}

function hashArticle(article) {
  const fields = [article.title, article.ruby || "", article.genre || [], article.work || [], article.etc || [], article.mean || "", article.content];
  // Only mix sources into the hash when present, so articles without them keep their old hash.
  if (article.sources?.length) fields.push(article.sources);
  return createHash("sha1").update(JSON.stringify(fields)).digest("hex");
}

const escapeHtml = (s) => String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");

// Renders the "参考・出典" block appended to the end of the article body.
function sourcesHtml(article) {
  const valid = (article.sources || []).filter((s) => s?.title && /^https?:\/\//.test(s.url || ""));
  if (!valid.length) return "";
  const items = valid
    .map((s) => `<li><a href="${escapeHtml(s.url)}" target="_blank" rel="noopener">${escapeHtml(s.title)}</a></li>`)
    .join("");
  return `<div class="sources"><p class="sources-title">参考・出典</p><ul>${items}</ul></div>`;
}

async function fetchWithRetry(url, options, attempts = 3) {
  for (let i = 1; ; i++) {
    try {
      const res = await fetch(url, { ...options, signal: AbortSignal.timeout(30000) });
      if (res.status < 500 || i === attempts) return res;
    } catch (err) {
      if (i === attempts) throw err;
    }
    await new Promise((r) => setTimeout(r, 2000 * i));
  }
}

async function wpFetch(endpoint, options = {}) {
  const res = await fetchWithRetry(`${API_BASE}${endpoint}`, {
    ...options,
    headers: {
      Authorization: authHeader,
      "Content-Type": "application/json",
      ...(options.headers || {}),
    },
  });
  if (!res.ok) {
    const body = await res.text();
    throw new Error(`${options.method || "GET"} ${endpoint} -> ${res.status}: ${body}`);
  }
  return res.json();
}

// taxonomy key -> REST base (CPT UI lets these differ from the taxonomy slug;
// override here if your REST API base slugs are different).
const TAXONOMY_REST_BASE = { genre: "genre", work: "work", etc: "etc" };

async function resolveTermIds(taxonomyKey, names) {
  const restBase = TAXONOMY_REST_BASE[taxonomyKey];
  const ids = [];
  for (const name of names) {
    const found = await wpFetch(`/wp/v2/${restBase}?search=${encodeURIComponent(name)}`);
    let term = found.find((t) => t.name === name);
    if (!term) {
      term = await wpFetch(`/wp/v2/${restBase}`, {
        method: "POST",
        body: JSON.stringify({ name }),
      });
      console.log(`Created new "${taxonomyKey}" term: ${name} (id ${term.id})`);
    }
    ids.push(term.id);
  }
  return ids;
}

async function buildPayload(article) {
  const [genreIds, workIds, etcIds] = await Promise.all([
    resolveTermIds("genre", article.genre || []),
    resolveTermIds("work", article.work || []),
    resolveTermIds("etc", article.etc || []),
  ]);
  return {
    title: article.title,
    content: article.content + sourcesHtml(article),
    genre: genreIds,
    work: workIds,
    etc: etcIds,
    acf: {
      ruby: article.ruby || "",
      mean: article.mean || "",
    },
  };
}

async function publishArticle(file, article) {
  const post = await wpFetch("/wp/v2/posts", {
    method: "POST",
    body: JSON.stringify({ ...(await buildPayload(article)), status: "draft" }),
  });
  console.log(`Published draft for "${article.title}" -> post ID ${post.id} (${file})`);
  return post.id;
}

async function updateArticle(file, postId, article) {
  await wpFetch(`/wp/v2/posts/${postId}`, {
    method: "POST",
    body: JSON.stringify(await buildPayload(article)),
  });
  console.log(`Updated post ${postId} from ${file} (status unchanged)`);
}

async function loadExistingTitles() {
  const titles = new Map();
  for (let page = 1; ; page++) {
    const batch = await wpFetch(
      `/wp/v2/posts?per_page=100&page=${page}&status=publish,draft,pending,future&context=edit&_fields=id,title`
    );
    for (const p of batch) titles.set(p.title.raw, p.id);
    if (batch.length < 100) break;
  }
  return titles;
}

async function main() {
  const state = await loadJson(STATE_FILE);
  const hashes = await loadJson(HASH_FILE);
  const files = (await readdir(ARTICLES_DIR)).filter((f) => f.endsWith(".json"));
  const existingTitles = await loadExistingTitles();

  let stateChanged = false;
  let hashesChanged = false;
  for (const file of files) {
    const article = JSON.parse(await readFile(path.join(ARTICLES_DIR, file), "utf8"));
    const hash = hashArticle(article);

    if (state[file]) {
      if (!hashes[file]) {
        // First time we track this file: record it without touching WordPress,
        // so edits made in wp-admin are not overwritten.
        hashes[file] = hash;
        hashesChanged = true;
      } else if (hashes[file] !== hash) {
        try {
          await updateArticle(file, state[file], article);
          hashes[file] = hash;
          hashesChanged = true;
        } catch (err) {
          console.error(`Failed to update ${file}:`, err.message, err.cause || "");
          process.exitCode = 1;
        }
      } else {
        console.log(`Skipping ${file} (unchanged, post ${state[file]})`);
      }
      continue;
    }

    if (existingTitles.has(article.title)) {
      console.log(`Skipping ${file}: a post titled "${article.title}" already exists (id ${existingTitles.get(article.title)})`);
      state[file] = existingTitles.get(article.title);
      hashes[file] = hash;
      stateChanged = hashesChanged = true;
      continue;
    }
    try {
      state[file] = await publishArticle(file, article);
      hashes[file] = hash;
      stateChanged = hashesChanged = true;
    } catch (err) {
      console.error(`Failed to publish ${file}:`, err.message, err.cause || "");
      process.exitCode = 1;
    }
  }

  if (stateChanged) await writeFile(STATE_FILE, JSON.stringify(state, null, 2) + "\n");
  if (hashesChanged) await writeFile(HASH_FILE, JSON.stringify(hashes, null, 2) + "\n");
}

main();
