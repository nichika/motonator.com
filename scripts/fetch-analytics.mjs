// Pulls Search Console and GA4 data through their official APIs (service
// account auth, no extra dependencies) and writes a snapshot the editorial
// agents can read: content/reports/analytics/latest.{json,md}.
//
// Env: GOOGLE_SA_KEY (service account JSON), GSC_SITE_URL, GA4_PROPERTY_ID.
// A section whose API call fails is recorded as an error in the report
// instead of aborting the others.

import { createSign } from "node:crypto";
import { mkdir, writeFile } from "node:fs/promises";
import path from "node:path";

const { GOOGLE_SA_KEY, GSC_SITE_URL, GA4_PROPERTY_ID } = process.env;

if (!GOOGLE_SA_KEY) {
  console.log("GOOGLE_SA_KEY is not set; skipping analytics fetch.");
  process.exit(0);
}

const key = JSON.parse(GOOGLE_SA_KEY);
const b64url = (v) => Buffer.from(v).toString("base64url");

async function getAccessToken(scopes) {
  const now = Math.floor(Date.now() / 1000);
  const unsigned =
    b64url(JSON.stringify({ alg: "RS256", typ: "JWT" })) +
    "." +
    b64url(
      JSON.stringify({
        iss: key.client_email,
        scope: scopes.join(" "),
        aud: "https://oauth2.googleapis.com/token",
        iat: now,
        exp: now + 3600,
      })
    );
  const signature = createSign("RSA-SHA256").update(unsigned).sign(key.private_key, "base64url");
  const res = await fetch("https://oauth2.googleapis.com/token", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      grant_type: "urn:ietf:params:oauth:grant-type:jwt-bearer",
      assertion: `${unsigned}.${signature}`,
    }),
  });
  if (!res.ok) throw new Error(`token request failed: ${res.status} ${await res.text()}`);
  return (await res.json()).access_token;
}

const iso = (d) => d.toISOString().slice(0, 10);
const daysAgo = (n) => new Date(Date.now() - n * 86400000);

async function postJson(url, token, body) {
  const res = await fetch(url, {
    method: "POST",
    headers: { Authorization: `Bearer ${token}`, "Content-Type": "application/json" },
    body: JSON.stringify(body),
  });
  if (!res.ok) throw new Error(`${res.status} ${await res.text()}`);
  return res.json();
}

async function fetchSearchConsole(token) {
  // Search Console data lags by ~2-3 days.
  const range = { startDate: iso(daysAgo(30)), endDate: iso(daysAgo(3)) };
  const url = `https://www.googleapis.com/webmasters/v3/sites/${encodeURIComponent(GSC_SITE_URL)}/searchAnalytics/query`;
  const [totals, queries, pages] = await Promise.all([
    postJson(url, token, { ...range }),
    postJson(url, token, { ...range, dimensions: ["query"], rowLimit: 100 }),
    postJson(url, token, { ...range, dimensions: ["page"], rowLimit: 100 }),
  ]);
  const row = (r) => ({ key: r.keys?.[0], clicks: r.clicks, impressions: r.impressions, ctr: r.ctr, position: r.position });
  return { range, totals: totals.rows?.[0] || {}, queries: (queries.rows || []).map(row), pages: (pages.rows || []).map(row) };
}

async function fetchGa4(token) {
  const url = `https://analyticsdata.googleapis.com/v1beta/properties/${GA4_PROPERTY_ID}:runReport`;
  const dateRanges = [{ startDate: "28daysAgo", endDate: "yesterday" }];
  const [pages, channels, totals] = await Promise.all([
    postJson(url, token, {
      dateRanges,
      dimensions: [{ name: "pagePath" }],
      metrics: [{ name: "screenPageViews" }, { name: "activeUsers" }],
      orderBys: [{ metric: { metricName: "screenPageViews" }, desc: true }],
      limit: 100,
    }),
    postJson(url, token, {
      dateRanges,
      dimensions: [{ name: "sessionDefaultChannelGroup" }],
      metrics: [{ name: "sessions" }],
      orderBys: [{ metric: { metricName: "sessions" }, desc: true }],
    }),
    postJson(url, token, { dateRanges, metrics: [{ name: "screenPageViews" }, { name: "activeUsers" }, { name: "sessions" }] }),
  ]);
  const num = (v) => Number(v ?? 0);
  return {
    range: { startDate: "28daysAgo", endDate: "yesterday" },
    totals: Object.fromEntries((totals.metricHeaders || []).map((h, i) => [h.name, num(totals.rows?.[0]?.metricValues?.[i]?.value)])),
    pages: (pages.rows || []).map((r) => ({ path: r.dimensionValues[0].value, views: num(r.metricValues[0].value), users: num(r.metricValues[1].value) })),
    channels: (channels.rows || []).map((r) => ({ channel: r.dimensionValues[0].value, sessions: num(r.metricValues[0].value) })),
  };
}

const pct = (v) => (v == null ? "-" : (v * 100).toFixed(1) + "%");

function render(report) {
  const lines = [`# アクセス分析スナップショット`, ``, `取得日: ${report.generatedAt}`, ``];
  const gsc = report.searchConsole;
  lines.push(`## Search Console（${gsc.range ? `${gsc.range.startDate} 〜 ${gsc.range.endDate}` : "取得失敗"}）`, ``);
  if (gsc.error) {
    lines.push(`取得エラー: ${gsc.error}`, ``);
  } else {
    const t = gsc.totals;
    lines.push(`合計: クリック ${t.clicks ?? 0} / 表示 ${t.impressions ?? 0} / CTR ${pct(t.ctr)} / 平均掲載順位 ${t.position?.toFixed?.(1) ?? "-"}`, ``);
    lines.push(`### 検索クエリ（上位30）`, ``, `| クエリ | クリック | 表示 | CTR | 順位 |`, `|---|---|---|---|---|`);
    for (const q of gsc.queries.slice(0, 30)) lines.push(`| ${q.key} | ${q.clicks} | ${q.impressions} | ${pct(q.ctr)} | ${q.position.toFixed(1)} |`);
    lines.push(``, `### ページ別（上位30）`, ``, `| ページ | クリック | 表示 | CTR | 順位 |`, `|---|---|---|---|---|`);
    for (const p of gsc.pages.slice(0, 30)) lines.push(`| ${p.key} | ${p.clicks} | ${p.impressions} | ${pct(p.ctr)} | ${p.position.toFixed(1)} |`);
    lines.push(``);
  }
  const ga = report.ga4;
  lines.push(`## GA4（直近28日）`, ``);
  if (ga.error) {
    lines.push(`取得エラー: ${ga.error}`, ``);
  } else {
    lines.push(`合計: ページビュー ${ga.totals.screenPageViews ?? 0} / ユーザー ${ga.totals.activeUsers ?? 0} / セッション ${ga.totals.sessions ?? 0}`, ``);
    lines.push(`### 流入元`, ``, `| チャネル | セッション |`, `|---|---|`);
    for (const c of ga.channels) lines.push(`| ${c.channel} | ${c.sessions} |`);
    lines.push(``, `### ページ別（上位30）`, ``, `| パス | PV | ユーザー |`, `|---|---|---|`);
    for (const p of ga.pages.slice(0, 30)) lines.push(`| ${p.path} | ${p.views} | ${p.users} |`);
    lines.push(``);
  }
  return lines.join("\n");
}

async function main() {
  const report = { generatedAt: new Date().toISOString(), searchConsole: {}, ga4: {} };
  let failed = false;

  if (GSC_SITE_URL) {
    try {
      report.searchConsole = await fetchSearchConsole(await getAccessToken(["https://www.googleapis.com/auth/webmasters.readonly"]));
    } catch (err) {
      report.searchConsole = { error: String(err.message).slice(0, 500) };
      failed = true;
    }
  } else {
    report.searchConsole = { error: "GSC_SITE_URL is not set" };
  }

  if (GA4_PROPERTY_ID) {
    try {
      report.ga4 = await fetchGa4(await getAccessToken(["https://www.googleapis.com/auth/analytics.readonly"]));
    } catch (err) {
      report.ga4 = { error: String(err.message).slice(0, 500) };
      failed = true;
    }
  } else {
    report.ga4 = { error: "GA4_PROPERTY_ID is not set" };
  }

  const dir = path.join(process.cwd(), "content", "reports", "analytics");
  await mkdir(dir, { recursive: true });
  await writeFile(path.join(dir, "latest.json"), JSON.stringify(report, null, 2) + "\n");
  await writeFile(path.join(dir, "latest.md"), render(report) + "\n");
  console.log("Wrote content/reports/analytics/latest.{json,md}");
  if (failed) process.exitCode = 1;
}

main();
