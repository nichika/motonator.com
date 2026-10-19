// For each published-as-draft article that has an "image_prompt", generate an
// image with Cloudflare Workers AI (FLUX.1 schnell, free tier), upload it to
// the WordPress media library, and set it as the post's featured image.
// Idempotent via content/.images-state.json. Needs WP_* and CF_* env vars.

import { readFile, writeFile, readdir } from "node:fs/promises";
import path from "node:path";
import { optimizeImage } from "./optimize-image.mjs";

const { WP_API_BASE, WP_USERNAME, WP_APP_PASSWORD, CF_ACCOUNT_ID, CF_API_TOKEN } = process.env;

if (!CF_ACCOUNT_ID || !CF_API_TOKEN) {
  console.log("CF_ACCOUNT_ID / CF_API_TOKEN not set; skipping image generation.");
  process.exit(0);
}
if (!WP_API_BASE || !WP_USERNAME || !WP_APP_PASSWORD) {
  console.error("Missing WP_API_BASE, WP_USERNAME, or WP_APP_PASSWORD env vars.");
  process.exit(1);
}

const authHeader = "Basic " + Buffer.from(`${WP_USERNAME}:${WP_APP_PASSWORD}`).toString("base64");
const ARTICLES_DIR = path.join(process.cwd(), "content", "articles");
const PUBLISHED_FILE = path.join(process.cwd(), "content", ".published-articles.json");
const IMAGES_FILE = path.join(process.cwd(), "content", ".images-state.json");
const URLS_FILE = path.join(process.cwd(), "content", ".images-urls.json");

// Files listed in FORCE_FILES (comma-separated, set by the workflow_dispatch input) get a new image
// even if they already have one, e.g. when a generated image failed the visual check.
const force = new Set((process.env.FORCE_FILES || "").split(",").map((s) => s.trim()).filter(Boolean));

const STYLE =
  "Flat vector illustration, minimalist style, warm orange (#f7931e) and cream white color palette, " +
  "clean simple background, square 1:1 composition, no text, no letters, no realistic human faces, " +
  "no copyrighted characters, no logos.";

async function loadJson(file) {
  try {
    return JSON.parse(await readFile(file, "utf8"));
  } catch {
    return {};
  }
}

async function wpJson(endpoint, options = {}) {
  const res = await fetch(`${WP_API_BASE}${endpoint}`, {
    ...options,
    headers: { Authorization: authHeader, "Content-Type": "application/json", ...(options.headers || {}) },
  });
  if (!res.ok) throw new Error(`${options.method || "GET"} ${endpoint} -> ${res.status}: ${await res.text()}`);
  return res.json();
}

async function generateImage(prompt) {
  const res = await fetch(
    `https://api.cloudflare.com/client/v4/accounts/${CF_ACCOUNT_ID}/ai/run/@cf/black-forest-labs/flux-1-schnell`,
    {
      method: "POST",
      headers: { Authorization: `Bearer ${CF_API_TOKEN}`, "Content-Type": "application/json" },
      body: JSON.stringify({ prompt: `${prompt}. ${STYLE}`.slice(0, 2048), steps: 4 }),
    }
  );
  if (!res.ok) throw new Error(`Cloudflare AI -> ${res.status}: ${await res.text()}`);
  const json = await res.json();
  const b64 = json.result?.image ?? json.image;
  if (!b64) throw new Error(`Cloudflare AI returned no image: ${JSON.stringify(json).slice(0, 300)}`);
  return Buffer.from(b64, "base64");
}

async function uploadMedia(buffer, filename, altText) {
  const media = await wpJson("/wp/v2/media", {
    method: "POST",
    headers: {
      "Content-Type": "image/jpeg",
      "Content-Disposition": `attachment; filename="${filename}"`,
    },
    body: buffer,
  });
  await wpJson(`/wp/v2/media/${media.id}`, { method: "POST", body: JSON.stringify({ alt_text: altText }) });
  return { id: media.id, url: media.source_url };
}

async function main() {
  const published = await loadJson(PUBLISHED_FILE);
  const images = await loadJson(IMAGES_FILE);
  const urls = await loadJson(URLS_FILE);
  const files = (await readdir(ARTICLES_DIR)).filter((f) => f.endsWith(".json"));

  let changed = false;
  for (const file of files) {
    const postId = published[file];
    if (!postId || (images[file] && !force.has(file))) continue;

    const article = JSON.parse(await readFile(path.join(ARTICLES_DIR, file), "utf8"));
    if (!article.image_prompt) continue;

    try {
      const post = await wpJson(`/wp/v2/posts/${postId}?context=edit&_fields=id,featured_media`);
      if (post.featured_media && !force.has(file)) {
        images[file] = post.featured_media;
        changed = true;
        console.log(`Skipping ${file}: post ${postId} already has a featured image.`);
        continue;
      }
      const original = await generateImage(article.image_prompt);
      const buffer = await optimizeImage(original);
      console.log(`Image size for ${file}: ${Math.round(original.length / 1024)}KB -> ${Math.round(buffer.length / 1024)}KB`);
      const slug = file.replace(/\.json$/, "");
      const { id: mediaId, url: mediaUrl } = await uploadMedia(buffer, `${slug}.jpg`, `${article.title}のイメージ`);
      urls[file] = mediaUrl;
      await wpJson(`/wp/v2/posts/${postId}`, { method: "POST", body: JSON.stringify({ featured_media: mediaId }) });
      images[file] = mediaId;
      changed = true;
      console.log(`Set featured image for "${article.title}" (post ${postId}, media ${mediaId})`);
    } catch (err) {
      console.error(`Failed image for ${file}:`, err.message);
      process.exitCode = 1;
    }
  }

  if (changed) {
    await writeFile(IMAGES_FILE, JSON.stringify(images, null, 2) + "\n");
    await writeFile(URLS_FILE, JSON.stringify(urls, null, 2) + "\n");
  }
}

main();
