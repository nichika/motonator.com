// One-off maintenance: unset every post's featured image, then permanently
// delete those media files from the library. Run manually via
// workflow_dispatch — this is destructive (media files are force-deleted,
// not trashed) and should never run automatically on a push.

const API_BASE = process.env.WP_API_BASE;
const USERNAME = process.env.WP_USERNAME;
const APP_PASSWORD = process.env.WP_APP_PASSWORD;

if (!API_BASE || !USERNAME || !APP_PASSWORD) {
  console.error("Missing WP_API_BASE, WP_USERNAME, or WP_APP_PASSWORD env vars.");
  process.exit(1);
}

const authHeader = "Basic " + Buffer.from(`${USERNAME}:${APP_PASSWORD}`).toString("base64");

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

async function getAllPosts() {
  let page = 1;
  let all = [];
  while (true) {
    const batch = await wpFetch(`/wp/v2/posts?per_page=100&page=${page}&_fields=id,title,featured_media&context=edit`);
    all = all.concat(batch);
    if (batch.length < 100) break;
    page++;
  }
  return all;
}

async function main() {
  const posts = await getAllPosts();
  const targets = posts.filter((p) => p.featured_media && p.featured_media !== 0);
  console.log(`Found ${targets.length} posts with a featured image set.`);

  const mediaIds = [...new Set(targets.map((p) => p.featured_media))];

  for (const post of targets) {
    try {
      await wpFetch(`/wp/v2/posts/${post.id}`, {
        method: "POST",
        body: JSON.stringify({ featured_media: 0 }),
      });
      console.log(`Unset featured image on post ${post.id} (${post.title.rendered})`);
    } catch (err) {
      console.error(`Failed to unset featured image on post ${post.id}:`, err.message);
    }
  }

  for (const mediaId of mediaIds) {
    try {
      await wpFetch(`/wp/v2/media/${mediaId}?force=true`, { method: "DELETE" });
      console.log(`Deleted media ${mediaId}`);
    } catch (err) {
      console.error(`Failed to delete media ${mediaId}:`, err.message);
    }
  }

  console.log(`Done. Unset ${targets.length} posts, deleted ${mediaIds.length} media files.`);
}

main();
