<?php

foreach (\App\FrontEnd::getInstance()->getConfig()['nav'] as $navItem) {
    echo '<p data-url="' . $navItem['url'] . '">' . $navItem['text'] . '</p>';
}
