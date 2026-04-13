<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

function fetchUrl($url, $postData = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json, text/plain, */*',
        'Accept-Language: en-US,en;q=0.9',
        'Referer: https://www.terabox.com/',
    ]);
    if ($postData) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
            'Content-Type: application/json',
        ], []));
    }
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['body' => $response, 'code' => $httpCode];
}

$input = json_decode(file_get_contents('php://input'), true);
$teraUrl = isset($input['url']) ? trim($input['url']) : '';

if (empty($teraUrl)) {
    $teraUrl = isset($_GET['url']) ? trim($_GET['url']) : '';
}

if (empty($teraUrl)) {
    echo json_encode(['success' => false, 'error' => 'URL provide karein']);
    exit();
}

// Validate Terabox URL
$validDomains = ['terabox.com', '1024tera.com', '4funbox.com', 'terafileshare.com', 'teraboxapp.com', 'mirrobox.com', 'momerybox.com', 'tibibox.com'];
$isValid = false;
foreach ($validDomains as $domain) {
    if (strpos($teraUrl, $domain) !== false) {
        $isValid = true;
        break;
    }
}
if (!$isValid) {
    echo json_encode(['success' => false, 'error' => 'Sirf Terabox links supported hain']);
    exit();
}

// Try API 1: teraboxapp.xyz
$api1 = fetchUrl('https://teraboxapp.xyz/api?url=' . urlencode($teraUrl));
if ($api1['code'] == 200 && !empty($api1['body'])) {
    $data = json_decode($api1['body'], true);
    if ($data && !isset($data['error']) && (isset($data['download_link']) || isset($data['url']) || isset($data['links']))) {
        $result = [
            'success' => true,
            'source' => 'api1',
            'file_name' => $data['file_name'] ?? $data['filename'] ?? $data['title'] ?? 'terabox_file',
            'file_size' => $data['size'] ?? $data['file_size'] ?? '',
            'file_type' => $data['type'] ?? '',
            'thumbnail' => $data['thumbnail'] ?? $data['thumb'] ?? '',
            'links' => []
        ];
        if (isset($data['download_link'])) $result['links'][] = ['label' => 'HD Download', 'url' => $data['download_link'], 'quality' => 'High Quality'];
        if (isset($data['url']))           $result['links'][] = ['label' => 'Direct Download', 'url' => $data['url'], 'quality' => ''];
        if (isset($data['links']) && is_array($data['links'])) {
            foreach ($data['links'] as $i => $l) {
                $lurl = is_array($l) ? ($l['url'] ?? $l['link'] ?? '') : $l;
                $result['links'][] = ['label' => 'Download ' . ($i+1), 'url' => $lurl, 'quality' => is_array($l) ? ($l['quality'] ?? '') : ''];
            }
        }
        echo json_encode($result);
        exit();
    }
}

// Try API 2: teradownloader vercel
$api2 = fetchUrl('https://teradownloader.vercel.app/api/terabox?url=' . urlencode($teraUrl));
if ($api2['code'] == 200 && !empty($api2['body'])) {
    $data = json_decode($api2['body'], true);
    if ($data && isset($data['downloadLink'])) {
        echo json_encode([
            'success' => true,
            'source' => 'api2',
            'file_name' => $data['title'] ?? $data['filename'] ?? 'terabox_file',
            'file_size' => $data['size'] ?? '',
            'file_type' => '',
            'thumbnail' => $data['thumbnail'] ?? '',
            'links' => [['label' => 'Download', 'url' => $data['downloadLink'], 'quality' => '']]
        ]);
        exit();
    }
}

// Try API 3: stacher.io
$api3 = fetchUrl('https://stacher.io/api/terabox?url=' . urlencode($teraUrl));
if ($api3['code'] == 200 && !empty($api3['body'])) {
    $data = json_decode($api3['body'], true);
    if ($data && isset($data['url'])) {
        echo json_encode([
            'success' => true,
            'source' => 'api3',
            'file_name' => $data['title'] ?? 'terabox_file',
            'file_size' => $data['size'] ?? '',
            'file_type' => '',
            'thumbnail' => '',
            'links' => [['label' => 'Download', 'url' => $data['url'], 'quality' => '']]
        ]);
        exit();
    }
}

// All APIs failed — return helpful alternatives
echo json_encode([
    'success' => false,
    'error' => 'Is waqt download link nahi mila. Alternatives try karein.',
    'alternatives' => [
        ['name' => 'TBDown.net', 'url' => 'https://tbdown.net/?url=' . urlencode($teraUrl)],
        ['name' => 'TeraDownloader.com', 'url' => 'https://teradownloader.com/?url=' . urlencode($teraUrl)],
        ['name' => 'Terabox App', 'url' => 'https://teraboxapp.xyz/?url=' . urlencode($teraUrl)],
    ]
]);
