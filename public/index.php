<?php
    use \App\FrontEnd;
    require __DIR__ . '/../bootstrap.php';
    FrontEnd::getInstance()->sendHeaders();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Au plus court</title>
        <meta charset="utf-8" />
        <link rel="stylesheet" type="text/css" href="/css/index.css">
        <link rel="icon" href="data:;base64,=">
        <?php FrontEnd::getInstance()->includeCSS(); ?>
    </head>
    <body>
        <header>
            <h1>Calculateur de trajets</h1>
        </header>
        <div id="main">
            <nav><?php require FrontEnd::VIEWS_DIR . '/nav.php'; ?></nav>
            <div id="view">
                <?php require FrontEnd::VIEWS_DIR . '/' . FrontEnd::getInstance()->getView() . '.php'; ?>
            </div>
        </div>
        <?php FrontEnd::getInstance()->includeJS(); ?>
        <script src="/js/index.js"></script>
    </body>
</html>
