<?php
// Send proper 404 HTTP status and ensure session is available before any output
http_response_code(404);
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// ======================= LANGUAGE SETUP =======================
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['en','ar']) ? $_GET['lang'] : 'en';
$text = [];
$translationsFile = __DIR__ . "/translations/{$lang}.json";
if (file_exists($translationsFile)) {
    $json = file_get_contents($translationsFile);
    $text = json_decode($json, true) ?: [];
}

// Fallback localized strings for this page
$local = [
    'en' => [
        'title' => 'Page Not Found',
        'subtitle' => "We couldn’t find the page you’re looking for.",
        'desc' => 'It may have been moved, renamed, or never existed.',
        'button' => 'Back to Home',
    ],
    'ar' => [
        'title' => 'الصفحة غير موجودة',
        'subtitle' => 'لم نتمكن من العثور على الصفحة التي تبحث عنها.',
        'desc' => 'قد تكون نُقلت أو أُعيدت تسميتها أو أنها غير موجودة.',
        'button' => 'العودة إلى الرئيسية',
    ],
];
$L = $local[$lang];

$pageTitle = ($text['notFoundPageTitle'] ?? $L['title']) . ' - RAWEE';

// Compute home URL relative to current location (works in subfolders)
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$basePath = ($scriptDir === '' || $scriptDir === '/') ? '' : $scriptDir;
$homeUrl = $basePath . '/index.php?lang=' . $lang;
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo ($lang === 'ar') ? 'rtl' : 'ltr'; ?>">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <?php $styleCssVer = @filemtime(__DIR__ . '/css/style.css') ?: time(); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css?v=<?php echo $styleCssVer; ?>" />
    <?php if ($lang === 'ar'): ?>
      <link rel="stylesheet" href="css/rtl.css" />
      <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <?php endif; ?>
    <style>
      /* Page-scoped styles matching theme tokens */
      .nf-hero {
        position: relative;
        min-height: 82vh;
        display: grid;
        place-items: center;
        padding: 6rem 1.25rem 4rem;
        background: var(--gradient-secondary);
        overflow: hidden;
        isolation: isolate;
      }
      .nf-hero::before {
        content: "";
        position: absolute;
        inset: -20% -10% auto -10%;
        height: 60vh;
        background: radial-gradient(60% 60% at 50% 40%, rgba(122, 226, 207, 0.25), transparent 70%),
                    radial-gradient(40% 40% at 80% 20%, rgba(8, 131, 149, 0.25), transparent 70%);
        filter: blur(25px);
        z-index: 0;
      }
      .nf-card {
        position: relative;
        z-index: 1;
        width: min(780px, 92vw);
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.12);
        color: #fff;
        box-shadow: var(--shadow-2xl);
        border-radius: var(--radius-2xl);
        padding: clamp(1.5rem, 4vw, 2.5rem);
        backdrop-filter: blur(10px);
        text-align: center;
        animation: slideInUp 0.6s ease-out both;
      }
      .nf-icon {
        width: 110px;
        height: 110px;
        margin-inline: auto;
        margin-bottom: 1rem;
        display: grid;
        place-items: center;
        border-radius: 20px;
        background: linear-gradient(135deg, rgba(55,183,195,.25), rgba(7,122,125,.25));
        border: 1px solid rgba(255,255,255,0.16);
        animation: float 6s ease-in-out infinite;
      }
      .nf-404 {
        font-size: clamp(3rem, 12vw, 7rem);
        line-height: 1;
        margin: .25rem 0 .75rem;
        font-weight: 800;
        letter-spacing: 2px;
        background: linear-gradient(135deg, var(--verdigris), #fff 40%, var(--turquoise));
        -webkit-background-clip: text; background-clip: text; color: transparent;
        text-shadow: 0 10px 30px rgba(0,0,0,.25);
      }
      .nf-title {
        font-size: clamp(1.25rem, 2.8vw, 1.8rem);
        font-weight: 700;
        margin: 0 0 .25rem;
      }
      .nf-desc {
        color: rgba(255,255,255,.85);
        margin: 0 0 1.25rem;
      }
      .nf-actions { display:flex; gap:.8rem; justify-content:center; flex-wrap:wrap; }
      .nf-btn {
        --btn-bg: var(--gradient-primary);
        display: inline-flex;
        align-items: center;
        gap: .6rem;
        padding: .9rem 1.25rem;
        font-weight: 700;
        color: #fff;
        background: var(--btn-bg);
        border: none;
        border-radius: var(--radius-xl);
        text-decoration: none;
        box-shadow: 0 10px 25px rgba(7,122,125,.25);
        transition: transform var(--transition-normal), box-shadow var(--transition-normal), filter var(--transition-normal);
        will-change: transform;
      }
      .nf-btn:hover { transform: translateY(-3px); box-shadow: 0 16px 35px rgba(7,122,125,.35); filter: brightness(1.05); }
      .nf-btn:active { transform: translateY(-1px); }
      .nf-btn i { opacity:.9; }
      html[dir="rtl"] .nf-btn i { transform: scaleX(-1); }

      /* Decorative orbs */
      .nf-orb { position:absolute; border-radius:50%; filter: blur(6px); opacity:.2; z-index: 0; }
      .nf-orb.a { width:240px; height:240px; background: var(--verdigris); top: 10%; left: -60px; animation: float 9s ease-in-out infinite; }
      .nf-orb.b { width:180px; height:180px; background: var(--teal); bottom: 8%; right: -40px; animation: float 7.2s ease-in-out -2s infinite; }

      .nf-footer-hint { color: rgba(255,255,255,.65); font-size:.9rem; margin-top: .75rem; }

      /* SVG colors follow theme */
      .nf-magnify { stroke: #fff; stroke-opacity:.85; }
      .nf-leaf { fill: var(--turquoise); }

      /* Respect small screens */
      @media (max-width: 520px){
        .nf-card{ padding: 1.25rem; }
        .nf-icon{ width: 90px; height: 90px; }
      }
    </style>
  </head>
  <body>
    <?php
      // Include header if available (safe in any structure)
      $headerPath = __DIR__ . '/includes/header.php';
      if (file_exists($headerPath)) {
          include $headerPath;
      }
    ?>

    <main>
      <section class="nf-hero" aria-labelledby="nf-heading">
        <span class="nf-orb a"></span>
        <span class="nf-orb b"></span>
        <div class="nf-card" role="group" aria-label="404 not found">
          <div class="nf-icon" aria-hidden="true">
            <!-- Clean, minimal illustration: magnifying glass with leaf -->
            <svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <circle cx="25" cy="25" r="14" stroke="url(#g1)" stroke-width="3" opacity="0.9"></circle>
              <defs>
                <linearGradient id="g1" x1="11" y1="11" x2="35" y2="35" gradientUnits="userSpaceOnUse">
                  <stop stop-color="var(--verdigris)"/>
                  <stop offset="1" stop-color="var(--teal)"/>
                </linearGradient>
              </defs>
              <path class="nf-magnify" d="M36 36 L50 50" stroke-width="4" stroke-linecap="round"/>
              <path class="nf-leaf" d="M25 18c3 0 6 2 7 5-3 1-6 3-8 6-3-2-4-6-2-9 1-1 2-2 3-2z"/>
            </svg>
          </div>
          <div class="nf-404" id="nf-heading">404</div>
          <h1 class="nf-title"><?php echo htmlspecialchars($text['notFoundTitle'] ?? $L['title']); ?></h1>
          <p class="nf-desc"><?php echo htmlspecialchars($text['notFoundSubtitle'] ?? $L['subtitle']); ?></p>
          <p class="nf-desc" style="opacity:.8; font-size:.95rem;">
            <?php echo htmlspecialchars($text['notFoundDescription'] ?? $L['desc']); ?>
          </p>
          <div class="nf-actions">
            <a class="nf-btn" href="<?php echo htmlspecialchars($homeUrl); ?>">
              <i class="fas fa-arrow-left"></i>
              <span><?php echo htmlspecialchars($text['backToHome'] ?? $L['button']); ?></span>
            </a>
          </div>
          <div class="nf-footer-hint">
            <!-- Optional hint to help users recover navigation -->
            <?php echo ($lang==='ar') ? 'أو استخدم شريط التنقل بالأعلى.' : 'Or use the navigation above.'; ?>
          </div>
        </div>
      </section>
    </main>

    <?php
      $footerPath = __DIR__ . '/includes/footer.php';
      if (file_exists($footerPath)) {
          include $footerPath;
      }
    ?>

    <!-- Global scripts (if your project loads them at the end) -->
    <script src="js/edits.js"></script>
    <script src="js/product.js"></script>
    <script src="js/cart_ajax.js"></script>
  </body>
</html>
