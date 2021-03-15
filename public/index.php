<?php require __DIR__ . '/../App.php'; ?>

<!DOCTYPE html>
<html>
    <head>
        <title>Au plus court</title>
        <meta charset="utf-8" />
        <link rel="stylesheet" type="text/css" href="/css/index.css">
        <?php App::get()->includeCSS(); ?>
    </head>
    <body>
        <header>
            <h1>Calculateur de trajets</h1>
            <?php require __DIR__ . '/../test/testDijkstra.php'; ?>
        </header>
        <div id="main">
            <nav><?php require App::get()->getViewsDir() . '/nav.php'; ?></nav>
            <div id="view">
                <?php require App::get()->getViewsDir() . '/' . App::get()->getView() . '.php'; ?>
            </div>
        </div>
        <?php App::get()->includeJS(); ?>
        <script src="/js/index.js"></script>
    </body>
</html>
