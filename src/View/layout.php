<?php
// src/View/layout.php
// Layout principal avec header et navigation

// Initialiser les métriques de performance
require_once __DIR__ . '/../Service/PerformanceMetrics.php';
$performanceMetrics = App\Service\PerformanceMetrics::getInstance();
$performanceMetrics->startTimer();

// Chemin vers les composants JavaScript
define('JS_PATH', '/js/components/');

// Chemin vers les composants CSS
define('CSS_PATH', '/css/components/');

/**
 * Helper pour inclure les scripts JavaScript de manière sécurisée.
 */
function includeJs(array $scripts)
{
    foreach ($scripts as $js)
    {
        $path = __DIR__ . '/../../public' . JS_PATH . $js;
        if (file_exists($path))
        {
            $version = filemtime($path);
            echo '<script src="' . JS_PATH . htmlspecialchars($js) . '?v=' . $version . '"></script>';
        }
    }
}

/**
 * Helper pour inclure les feuilles de style CSS de manière sécurisée.
 */
function includeCss(array $styles)
{
    foreach ($styles as $css)
    {
        $path = __DIR__ . '/../../public' . CSS_PATH . $css;
        if (file_exists($path))
        {
            echo '<link rel="stylesheet" href="' . CSS_PATH . htmlspecialchars($css) . '">';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= isset($title) ? $title : 'Suivi Nash'; ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon/favicon.ico">
    <link rel="apple-touch-icon" href="/favicon/favicon.svg">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <style>
        :root {
            --z-base: 1;
            --z-footer: 10;
            --z-header: 20;
            --z-overlay: 30;
            --z-modal: 40;
        }
    </style>

    <!-- Alpine.js (loaded in head for proper initialization) -->
    <script defer src="https://unpkg.com/alpinejs@3.15.3/dist/cdn.min.js"></script>
    <!-- Alpine Focus plugin for x-trap (modal focus trapping) -->
    <script defer src="https://unpkg.com/@alpinejs/focus@3.15.3/dist/cdn.min.js"></script>
    <!-- Alpine Collapse plugin for x-collapse -->
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.15.3/dist/cdn.min.js"></script>

    <!-- Système de notifications global -->
<script src="/js/components/alpine-notifications.js?v=<?= time(); ?>"></script>

    <!-- Chart.js for graphics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Page-specific CSS -->
    <?php
    if (isset($pageCss))
    {
        includeCss((array)$pageCss);
    }
?>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-purple-50 min-h-screen">
    <!-- Skip link pour l'accessibilité -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:bg-white/80 focus:px-3 focus:py-2 rounded-md">Aller au contenu</a>

    <!-- Système de notifications global -->
    <?php include __DIR__ . '/components/notifications.php'; ?>

    <?php include __DIR__ . '/components/header.php'; ?>
    <main id="main-content" class="max-w-[1280px] mx-auto px-4 flex flex-col min-h-[60vh] pb-24">
        <?php
    // Render main content from $content if set
    if (isset($content))
    {
        echo $content;
    } else
    {
        include __DIR__ . '/components/card.php';
    }
?>
    </main>
    <?php include __DIR__ . '/components/footer.php'; ?>

    <!-- Page-specific JavaScript -->
    <?php
    if (isset($pageJs))
    {
        includeJs((array)$pageJs);
    }
?>
</body>
</html>
