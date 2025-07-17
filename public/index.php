<?php declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use App\LocalStorage;
use App\UrlShortener;
use App\CookieManager;
use App\FileUploader;

$baseHost = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
          . '://' . $_SERVER['HTTP_HOST']
          . rtrim(dirname($_SERVER['PHP_SELF']), '/');

$uploader       = new FileUploader(
    new LocalStorage(__DIR__ . '/../uploads'),
    new UrlShortener(__DIR__ . '/../data/files.db', $baseHost . '/f'),
    new CookieManager()
);
$cookieManager  = new CookieManager();
$uploadedHashes = $cookieManager->getUploadedHashes();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Fun File Uploader</title>
  <style>
    body {
      margin: 0; font-family: sans-serif;
      background: url('https://picsum.photos/seed/<?php echo random_int(1,1000); ?>/1200/800') no-repeat center/cover;
      height:100ch;
    }
    .container {
      max-width: 600px; margin: 50px auto;
      background: rgba(255,255,255,0.7);
      padding: 20px; border-radius: 10px;
    }
    #drop-area {
      border: 2px dashed #888; padding: 20px; text-align: center;
      cursor: pointer; border-radius: 8px;
    }
    progress { width: 100%; display: none; margin-top: 10px; }
    #result { margin-top: 15px; }
  </style>
</head>
<body>
  <div class="container">
    <h1>Upload File</h1>
    <div id="drop-area">
      <p>Drag & Drop here or <button id="fileSelect">choose</button></p>
      <input type="file" id="fileElem" hidden>
    </div>
    <progress id="progress-bar" value="0" max="100"></progress>
    <div id="result"></div>

    <?php if (!empty($uploadedHashes)): ?>
      <h2>Your Files</h2>
      <ul>
        <?php foreach ($uploadedHashes as $h): ?>
          <li>
            <a href="<?php echo htmlspecialchars($baseHost . '/f/' . $h); ?>" target="_blank">
              <?php echo htmlspecialchars($h); ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <script>
    const dropArea   = document.getElementById('drop-area');
    const fileElem   = document.getElementById('fileElem');
    const fileSelect = document.getElementById('fileSelect');
    const progress   = document.getElementById('progress-bar');
    const result     = document.getElementById('result');

    ['dragenter','dragover','dragleave','drop']
      .forEach(e => dropArea.addEventListener(e, ev => { ev.preventDefault(); ev.stopPropagation(); }, false));

    dropArea.addEventListener('drop', e => {
      if (e.dataTransfer.files.length) uploadFile(e.dataTransfer.files[0]);
    });

    fileSelect.addEventListener('click', () => fileElem.click());
    fileElem.addEventListener('change', () => uploadFile(fileElem.files[0]));

    function uploadFile(file) {
      progress.style.display = 'block';
      const xhr = new XMLHttpRequest();
      xhr.open('POST', 'upload.php');
      xhr.upload.onprogress = e => {
        if (e.lengthComputable) progress.value = (e.loaded / e.total) * 100;
      };
      xhr.onload = () => {
        progress.style.display = 'none';
        if (xhr.status === 200) {
          const res = JSON.parse(xhr.responseText);
          result.innerHTML = `<p>Uploaded: <a href="${res.url}" target="_blank">${res.url}</a></p>`;
        } else {
          result.textContent = 'Upload failed';
        }
      };
      const fd = new FormData();
      fd.append('file', file);
      xhr.send(fd);
    }
  </script>
</body>
</html>
