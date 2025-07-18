<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>File Uploader</title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
  <div class="container">
    <h1>Upload File</h1>
    <div id="drop-area">
      <p>Drag & Drop here or <button id="fileSelect">choose</button></p>
      <input type="file" id="fileElem" hidden>
      (Max 3 Gb)
    </div>
    <progress id="progress-bar" value="0" max="100"></progress>
    <div id="result"></div>

    <?php if (!empty($uploadedFiles)): ?>
      <h2>Your Files</h2>
      <ul id="file-list">
        <?php foreach ($uploadedFiles as $hash => $fileData): ?>
          <li data-hash="<?= htmlspecialchars($hash) ?>">
            <span class="file-icon file-icon-<?= str_replace('/', '-', $fileData['mime_type']) ?>"></span>
            <a href="<?= htmlspecialchars($baseHost . '/f/' . $hash) ?>" target="_blank">
              <?= htmlspecialchars($fileData['filename']) ?>
            </a>
            <button class="delete-btn" data-hash="<?= htmlspecialchars($hash) ?>"></button>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <input type="hidden" id="csrfToken" value="<?php echo htmlspecialchars(
       $_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'
   ); ?>">

  <script src="/js/main.js"></script>
</body>
</html>