<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($translations['app']['title']) ?></title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
  <!-- Language Selector -->
  <div id="language-selector">
    <select id="language-select">
      <option value="fr" data-icon="flag-fr">Français</option>
      <option value="en" data-icon="flag-en">English</option>
      <option value="es" data-icon="flag-es">Español</option>
      <option value="de" data-icon="flag-de">Deutsch</option>
      <option value="it" data-icon="flag-it">Italiano</option>
      <option value="pt" data-icon="flag-pt">Português</option>
      <option value="ar" data-icon="flag-ar">العربية</option>
      <option value="zh" data-icon="flag-zh">中文</option>
    </select>
  </div>

  <div class="container">
    <h1><?= htmlspecialchars($translations['app']['title']) ?></h1>
    <div id="drop-area">
      <p><?= htmlspecialchars($translations['app']['drag_drop']) ?> or <button id="fileSelect" type="button" class="btn-with-icon"><span class="icon icon-upload"></span><?= htmlspecialchars($translations['app']['select_file']) ?></button></p>
      <input type="file" id="fileElem" hidden>
      (Max 3 Gb)
    </div>
    <progress id="progress-bar" value="0" max="100"></progress>
    <div id="result"></div>

    <?php if (!empty($uploadedFiles)): ?>
      <h2><span class="icon icon-folder"></span> <?= htmlspecialchars($translations['app']['my_files']) ?></h2>
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

  <!-- Footer Links -->
  <div class="footer-links">
    <div class="footer-left">
      <a href="https://picsum.photos/" target="_blank" class="footer-link">
        <span class="icon icon-image"></span> <?= htmlspecialchars($translations['app']['picsum_link']) ?>
      </a>

    </div>
    <div class="footer-right">
      <a href="https://github.com/yrbane/upload" target="_blank" class="footer-link">
        <span class="icon icon-github"></span> <?= htmlspecialchars($translations['app']['github_link']) ?>
      </a>
    </div>
  </div>

  <input type="hidden" id="csrfToken" value="<?php echo htmlspecialchars(
       $_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'
   ); ?>">
  <div id="translations" style="display: none;"
       data-confirm-delete="<?= htmlspecialchars($translations['app']['confirm_delete']) ?>"
       data-upload-success="<?= htmlspecialchars($translations['success']['upload_complete']) ?>"
       data-upload-failed="<?= htmlspecialchars($translations['app']['upload_failed']) ?>"
       data-uploaded-label="<?= htmlspecialchars($translations['app']['uploaded']) ?>"
       data-error-deleting="<?= htmlspecialchars($translations['app']['error_deleting']) ?>"
       data-separator="<?= htmlspecialchars($translations['app']['separator']) ?>"
       data-current-locale="<?= htmlspecialchars($currentLocale) ?>"></div>

  <script src="/js/main.js"></script>
</body>
</html>