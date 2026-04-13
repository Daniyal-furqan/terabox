# TeraDown — Terabox Downloader Website

## Files List
- `index.html` — Main website (frontend)
- `api.php`    — Backend API (Terabox se link nikalta hai)
- `.htaccess`  — Apache security & performance settings
- `README.md`  — Ye instructions

---

## Hosting Par Upload Kaise Karein

### Step 1: cPanel File Manager
1. cPanel mein jakar **File Manager** open karein
2. `public_html` folder mein jayein
3. Teen files upload karein:
   - `index.html`
   - `api.php`
   - `.htaccess`

### Step 2: PHP Version Check
- cPanel → **PHP Selector** ya **MultiPHP Manager**
- PHP **7.4 ya upar** select karein
- cURL extension enabled honi chahiye (usually default hoti hai)

### Step 3: Test Karein
- Apni domain open karein
- Ek Terabox link paste kar ke "Download" click karein

---

## Troubleshooting

### "Server se connection nahi hua" error
→ api.php ka path check karein — dono files same folder mein honi chahiye

### API kaam nahi kar rahi
→ cPanel mein cURL extension enable karein:
   PHP Extensions → cURL → Enable

### .htaccess kaam nahi kar raha
→ Apache mod_rewrite aur mod_headers enable hona chahiye
→ Shared hosting par ye usually default on hota hai

---

## Customization

### Website ka naam badlein
`index.html` mein search karein:
- "TeraDown" → apna naam
- "Tera<span>Down</span>" → nav logo

### SEO ke liye
`index.html` mein `<title>` aur `<meta name="description">` update karein

### AdSense lagana ho
`</head>` se pehle AdSense script add karein

---

## Notes
- Ye tool sirf publicly shared Terabox links ke liye kaam karta hai
- Private ya password protected files download nahi hongi
- API third-party services use karti hai — agar ek kaam na kare to dusri try hoti hai
