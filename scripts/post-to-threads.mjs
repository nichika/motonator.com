// Posts newly PUBLISHED articles to Threads (Threads API is free). Nothing is posted until a
// human has published the article in WordPress: only posts the public REST API returns count.
// State lives in content/.social-state.json so no article is posted twice.
//
// Env: THREADS_USER_ID, THREADS_ACCESS_TOKEN (GitHub Secrets), DRY_RUN=true to only print,
//      MAX_PER_RUN (default 2), WINDOW_DAYS (default 7), SITE_BASE (default https://motonator.com)

import { readFile, writeFile } from "node:fs/promises";
import path from "node:path";

const SITE = process.env.SITE_BASE || "https://motonator.com";
const DRY_RUN = String(process.env.DRY_RUN || "").toLowerCase() === "true";
const MAX_PER_RUN = Number(process.env.MAX_PER_RUN || 2);
const WINDOW_DAYS = Number(process.env.WINDOW_DAYS || 7);
const { THREADS_USER_ID, THREADS_ACCESS_TOKEN } = process.env;
const STATE_FILE = path.join(process.cwd(), "content", ".social-state.json");
const GRAPH = "https://graph.threads.net/v1.0";

if (!DRY_RUN && (!THREADS_USER_ID || !THREADS_ACCESS_TOKEN)) {
  console.log("THREADS_USER_ID / THREADS_ACCESS_TOKEN are not set; skipping Threads posting.");
  process.exit(0);
}

const decode = (s) =>
  s
    .replace(/<[^>]*>/g, "")
    .replace(/&#(\d+);/g, (_, n) => String.fromCodePoint(Number(n)))
    .replace(/&amp;/g, "&")
    .replace(/&lt;/g, "<")
    .replace(/&gt;/g, ">")
    .replace(/&quot;/g, '"')
    .replace(/&#8217;|&rsquo;/g, "'")
    .trim();

async function getJson(url) {
  const res = await fetch(url, { signal: AbortSignal.timeout(30000) });
  if (!res.ok) throw new Error(`GET ${url} -> ${res.status}`);
  return res.json();
}

async function loadState() {
  try {
    return JSON.parse(await readFile(STATE_FILE, "utf8"));
  } catch {
    return { threads: {} };
  }
}

const GENRE_TAG = { ネット用語: "#ネットスラング", ゲーム: "#ゲーム", アニメ: "#アニメ", マンガ: "#漫画" };

function shorten(text, max) {
  if (text.length <= max) return text;
  const cut = text.slice(0, max);
  const end = Math.max(cut.lastIndexOf("。"), cut.lastIndexOf("」"));
  return end > 40 ? cut.slice(0, end + 1) : cut.slice(0, max - 1) + "…";
}

function compose(post, genreName) {
  const mean = post.acf?.mean ? decode(post.acf.mean.split(/<\/p>/)[0]) : "";
  const title = decode(post.title.rendered);
  const lead = mean ? shorten(mean, 170) : `「${title}」の元ネタ・意味を解説しています。`;
  const url = `${post.link}?utm_source=threads&utm_medium=social&utm_campaign=auto_post`;
  const tags = ["#元ネタ", GENRE_TAG[genreName]].filter(Boolean).join(" ");
  return `${lead}\n\n出典つきの解説はこちら👇\n${url}\n\n${tags}`;
}

async function postToThreads(text) {
  const create = await fetch(`${GRAPH}/${THREADS_USER_ID}/threads`, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({ media_type: "TEXT", text, access_token: THREADS_ACCESS_TOKEN }),
    signal: AbortSignal.timeout(30000),
  });
  const created = await create.json();
  if (!create.ok || !created.id) throw new Error(`create container -> ${create.status}: ${JSON.stringify(created)}`);
  await new Promise((r) => setTimeout(r, 15000)); // Meta recommends a short wait before publishing
  const publish = await fetch(`${GRAPH}/${THREADS_USER_ID}/threads_publish`, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({ creation_id: created.id, access_token: THREADS_ACCESS_TOKEN }),
    signal: AbortSignal.timeout(30000),
  });
  const published = await publish.json();
  if (!publish.ok || !published.id) throw new Error(`publish -> ${publish.status}: ${JSON.stringify(published)}`);
  return published.id;
}

async function main() {
  const state = await loadState();
  state.threads ??= {};

  const genres = await getJson(`${SITE}/wp-json/wp/v2/genre?per_page=100&_fields=id,name`);
  const genreName = Object.fromEntries(genres.map((g) => [g.id, decode(g.name)]));
  // The public REST API only returns published posts (drafts need authentication).
  const posts = await getJson(`${SITE}/wp-json/wp/v2/posts?per_page=100&orderby=date&order=desc&_fields=id,date,link,title,acf,genre`);

  const cutoff = Date.now() - WINDOW_DAYS * 24 * 60 * 60 * 1000;
  // Oldest first, so the posts go out in the order they were published.
  const todo = posts
    .filter((p) => !state.threads[p.id] && new Date(p.date + "Z").getTime() >= cutoff - 9 * 3600 * 1000)
    .reverse()
    .slice(0, MAX_PER_RUN);

  if (!todo.length) {
    console.log("Nothing new to post to Threads.");
    return;
  }

  for (const post of todo) {
    const text = compose(post, genreName[post.genre?.[0]]);
    console.log(`--- post ${post.id} ---\n${text}\n(${text.length} chars)`);
    if (DRY_RUN) continue;
    try {
      const id = await postToThreads(text);
      state.threads[post.id] = { postedAt: new Date().toISOString(), threadsId: id };
      console.log(`Posted to Threads: ${id}`);
    } catch (err) {
      console.error(`Failed to post ${post.id}:`, err.message);
      process.exitCode = 1;
    }
  }
  if (!DRY_RUN) await writeFile(STATE_FILE, JSON.stringify(state, null, 2) + "\n");
}

main();
