<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($translations['app']['title']) ?></title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
  <div class="container">
    <h1><?= htmlspecialchars($translations['app']['title']) ?></h1>
    <div id="drop-area">
      <p><?= htmlspecialchars($translations['app']['drag_drop']) ?> or <button id="fileSelect" type="button"><?= htmlspecialchars($translations['app']['select_file']) ?></button></p>
      <input type="file" id="fileElem" hidden>
      (Max 3 Gb)
    </div>
    <progress id="progress-bar" value="0" max="100"></progress>
    <div id="result"></div>

    <?php if (!empty($uploadedFiles)): ?>
      <h2><?= htmlspecialchars($translations['app']['my_files']) ?></h2>
      <ul id="file-list">
        <?php foreach ($uploadedFiles as $hash => $fileData): ?>
          <li data-hash="<?= htmlspecialchars($hash) ?>">
            <span class="file-icon file-icon-<?= str_replace('/', '-', $fileData['mime_type']) ?>"></span>
            <a href="<?= htmlspecialchars($baseHost . '/f/' . $hash) ?>" target="_blank">
              <?= htmlspecialchars($fileData['filename']) ?>
            </a>
            <button class="delete-btn" data-hash="<?= htmlspecialchars($hash) ?>" title="<?= htmlspecialchars($translations['app']['delete']) ?>"></button>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <input type="hidden" id="csrfToken" value="<?php echo htmlspecialchars(
       $_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'
   ); ?>">
  <div id="translations" style="display: none;"
       data-confirm-delete="<?= htmlspecialchars($translations['app']['confirm_delete']) ?>"
       data-upload-success="<?= htmlspecialchars($translations['success']['upload_complete']) ?>"
       data-upload-failed="<?= htmlspecialchars($translations['error']['upload_failed']) ?>"></div>

  <script src="/js/main.js"></script>
</body>
</html>