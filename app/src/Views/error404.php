<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($translations['error']['page_not_found'] ?? '404 - Page non trouvée') ?> - <?= htmlspecialchars($translations['app']['title']) ?></title>
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
    <div class="error-container">
      <h1 class="error-title">404</h1>
      <h2><?= htmlspecialchars($translations['error']['page_not_found'] ?? 'Page non trouvée') ?></h2>
      <p class="error-message"><?= htmlspecialchars($translations['error']['page_not_found_description'] ?? 'La page que vous recherchez n\'existe pas ou a été déplacée.') ?></p>
      
      <div class="error-actions">
        <a href="/" class="btn-primary">
          <span class="icon icon-home"></span>
          <?= htmlspecialchars($translations['error']['back_to_home'] ?? 'Retour à l\'accueil') ?>
        </a>
      </div>
    </div>
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

  <div id="translations" style="display: none;"
       data-current-locale="<?= htmlspecialchars($currentLocale) ?>"></div>

  <script src="/js/main.js"></script>
</body>
</html>