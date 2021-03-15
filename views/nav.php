<?php

foreach(App::get()->getConfig()['nav'] as $navItem) {
    echo '<p data-url="' . $navItem['url'] . '">' . $navItem['text'] . '</p>';
}
