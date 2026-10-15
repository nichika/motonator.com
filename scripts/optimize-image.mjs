// Shrinks and recompresses generated images before upload. Uses sharp when it
// is installed (the workflow installs it); otherwise returns the input as-is.

let sharp = null;
try {
  sharp = (await import("sharp")).default;
} catch {
  // sharp not installed: keep the original bytes
}

export async function optimizeImage(buffer, size = 800, quality = 72) {
  if (!sharp) return buffer;
  const out = await sharp(buffer)
    .resize(size, size, { fit: "cover" })
    .jpeg({ quality, mozjpeg: true })
    .toBuffer();
  return out.length < buffer.length ? out : buffer;
}
