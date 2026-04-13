export default async function handler(req, res) {
  // CORS headers
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  const url = req.method === 'POST' ? req.body?.url : req.query?.url;

  if (!url) {
    return res.status(400).json({ success: false, error: 'URL provide karein' });
  }

  const validDomains = ['terabox.com', '1024tera.com', '4funbox.com', 'terafileshare.com', 'teraboxapp.com', 'mirrobox.com', 'momerybox.com', 'tibibox.com'];
  const isValid = validDomains.some(d => url.includes(d));
  if (!isValid) {
    return res.status(400).json({ success: false, error: 'Sirf Terabox links supported hain' });
  }

  const headers = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
    'Accept': 'application/json, text/plain, */*',
    'Accept-Language': 'en-US,en;q=0.9',
    'Referer': 'https://www.terabox.com/',
  };

  // Try API 1 - teraboxapp.xyz
  try {
    const r1 = await fetch(`https://teraboxapp.xyz/api?url=${encodeURIComponent(url)}`, { headers });
    if (r1.ok) {
      const d = await r1.json();
      if (d && !d.error) {
        const links = [];
        if (d.download_link) links.push({ label: 'HD Download', url: d.download_link, quality: 'High Quality' });
        if (d.url) links.push({ label: 'Direct Download', url: d.url, quality: '' });
        if (Array.isArray(d.links)) {
          d.links.forEach((l, i) => {
            const lu = typeof l === 'string' ? l : (l.url || l.link || '');
            if (lu) links.push({ label: `Download ${i + 1}`, url: lu, quality: l.quality || '' });
          });
        }
        if (links.length > 0) {
          return res.json({
            success: true,
            file_name: d.file_name || d.filename || d.title || 'terabox_file',
            file_size: d.size || d.file_size || '',
            file_type: d.type || '',
            thumbnail: d.thumbnail || d.thumb || '',
            links
          });
        }
      }
    }
  } catch (e) {}

  // Try API 2 - teradownloader vercel
  try {
    const r2 = await fetch(`https://teradownloader.vercel.app/api/terabox?url=${encodeURIComponent(url)}`, { headers });
    if (r2.ok) {
      const d = await r2.json();
      if (d && (d.downloadLink || d.url)) {
        return res.json({
          success: true,
          file_name: d.title || d.filename || 'terabox_file',
          file_size: d.size || '',
          file_type: '',
          thumbnail: d.thumbnail || '',
          links: [{ label: 'Download', url: d.downloadLink || d.url, quality: '' }]
        });
      }
    }
  } catch (e) {}

  // Try API 3 - stacher
  try {
    const r3 = await fetch(`https://stacher.io/api/terabox?url=${encodeURIComponent(url)}`, { headers });
    if (r3.ok) {
      const d = await r3.json();
      if (d && d.url) {
        return res.json({
          success: true,
          file_name: d.title || d.name || 'terabox_file',
          file_size: d.size || '',
          file_type: '',
          thumbnail: '',
          links: [{ label: 'Download', url: d.url, quality: '' }]
        });
      }
    }
  } catch (e) {}

  // Try API 4 - tera.instavideosave
  try {
    const r4 = await fetch(`https://tera.instavideosave.com/?url=${encodeURIComponent(url)}`, { headers });
    if (r4.ok) {
      const d = await r4.json();
      if (d && d.url) {
        return res.json({
          success: true,
          file_name: d.title || d.name || 'terabox_file',
          file_size: d.size || '',
          file_type: '',
          thumbnail: '',
          links: [{ label: 'Download', url: d.url, quality: '' }]
        });
      }
    }
  } catch (e) {}

  // All failed
  return res.json({
    success: false,
    error: 'Abhi download link nahi mila. Alternatives try karein.',
    alternatives: [
      { name: 'TBDown.net', url: `https://tbdown.net/?url=${encodeURIComponent(url)}` },
      { name: 'TeraDownloader.com', url: `https://teradownloader.com/?url=${encodeURIComponent(url)}` },
      { name: 'TeraBoxApp.xyz', url: `https://teraboxapp.xyz/?url=${encodeURIComponent(url)}` },
    ]
  });
}
