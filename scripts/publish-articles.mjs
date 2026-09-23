// Publishes content/articles/*.json to WordPress as drafts via the REST API.
// Auth uses a WordPress Application Password (never the account password),
// supplied through env vars that GitHub Actions fills from Secrets.
//
// Idempotent via content/.published-articles.json: once a file is published,
// its post ID is recorded there so re-running the workflow won't create
// duplicates. That state file lives outside content/articles/ so committing
// it back doesn't re-trigger this workflow.

import { readFile, writeFile, readdir } from "node:fs/promises";
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

async function loadState() {
  try {
    return JSON.parse(await readFile(STATE_FILE, "utf8"));
  } catch {
    return {};
  }
}

async function wpFetch(endpoint, options = {}) {
  const res = await fetch(`${API_BASE}${endpoint}`, {
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

async function publishArticle(file, article) {
  const [genreIds, workIds, etcIds] = await Promise.all([
    resolveTermIds("genre", article.genre || []),
    resolveTermIds("work", article.work || []),
    resolveTermIds("etc", article.etc || []),
  ]);

  const post = await wpFetch("/wp/v2/posts", {
    method: "POST",
    body: JSON.stringify({
      title: article.title,
      content: article.content,
      status: "draft",
      genre: genreIds,
      work: workIds,
      etc: etcIds,
      acf: {
        ruby: article.ruby || "",
        mean: article.mean || "",
      },
    }),
  });

  console.log(`Published draft for "${article.title}" -> post ID ${post.id} (${file})`);
  return post.id;
}

async function main() {
  const state = await loadState();
  const files = (await readdir(ARTICLES_DIR)).filter((f) => f.endsWith(".json"));

  let changed = false;
  for (const file of files) {
    if (state[file]) {
      console.log(`Skipping ${file} (already published as post ${state[file]})`);
      continue;
    }
    const article = JSON.parse(await readFile(path.join(ARTICLES_DIR, file), "utf8"));
    try {
      state[file] = await publishArticle(file, article);
      changed = true;
    } catch (err) {
      console.error(`Failed to publish ${file}:`, err.message, err.cause || "");
      process.exitCode = 1;
    }
  }

  if (changed) {
    await writeFile(STATE_FILE, JSON.stringify(state, null, 2) + "\n");
  }
}

main();
