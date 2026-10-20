<?php

/*
 * Hazelchat for Kirby: adds the Hazelchat AI chat agent to every page of a
 * Kirby site from one installation code.
 *
 * (C) 2026 Hazelchat. MIT License; see LICENSE.md.
 */

use Hazelchat\Kirby\Plugin;
use Kirby\Cms\App as Kirby;

require_once __DIR__ . '/src/Key.php';
require_once __DIR__ . '/src/Plugin.php';

Kirby::plugin('hazelchat/kirby', [
    // config.php: 'hazelchat.kirby.code' => '<installation code or key>',
    // 'hazelchat.kirby.consent' => true. Unset, the Panel dialog's values apply.
    'options' => [
        'code' => null,
        'consent' => null,
    ],
    'hooks' => [
        'page.render:after' => function (string $contentType, string $html) {
            return Plugin::render($contentType, $html);
        },
    ],
    'areas' => [
        'hazelchat' => fn () => Plugin::area(),
    ],
    'translations' => [
        'en' => require __DIR__ . '/translations/en.php',
        'de' => require __DIR__ . '/translations/de.php',
    ],
]);
