// Counts articles that were posted to WordPress as drafts but do not have a generated
// featured image yet ("waiting for an image"). The daily routine uses this to decide
// how many new articles to write today: if anything is still waiting, write fewer (5);
// otherwise write the full amount (10). Usage: node scripts/count-pending-images.mjs
import { readFile } from "node:fs/promises";
import path from "node:path";

const root = process.cwd();
const read = async (file, fallback) => {
  try {
    return JSON.parse(await readFile(file, "utf8"));
  } catch {
    return fallback;
  }
};

const published = await read(path.join(root, "content", ".published-articles.json"), {});
const images = await read(path.join(root, "content", ".images-state.json"), {});

const pending = [];
for (const file of Object.keys(published)) {
  if (images[file]) continue;
  const article = await read(path.join(root, "content", "articles", file), null);
  if (!article || !article.image_prompt) continue; // moved away, or never gets an image
  pending.push(file);
}

const dailyTarget = pending.length > 0 ? 5 : 10;
console.log(JSON.stringify({ pendingImages: pending.length, files: pending, dailyTarget }));
