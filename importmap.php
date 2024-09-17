<?php

declare(strict_types=1);

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.ts',
        'entrypoint' => true,
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@symfony/ux-live-component' => [
        'path' => './vendor/symfony/ux-live-component/assets/dist/live_controller.js',
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@hotwired/turbo' => [
        'version' => '8.0.6',
    ],
    'bootstrap-icons/font/bootstrap-icons.min.css' => [
        'version' => '1.11.3',
        'type' => 'css',
    ],
    'bootstrap' => [
        'version' => '5.3.3',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'codemirror' => [
        'version' => '6.0.1',
    ],
    '@codemirror/lang-sql' => [
        'version' => '6.7.1',
    ],
    '@codemirror/view' => [
        'version' => '6.33.0',
    ],
    '@codemirror/state' => [
        'version' => '6.4.1',
    ],
    '@codemirror/language' => [
        'version' => '6.10.2',
    ],
    '@codemirror/commands' => [
        'version' => '6.6.1',
    ],
    '@codemirror/search' => [
        'version' => '6.5.6',
    ],
    '@codemirror/autocomplete' => [
        'version' => '6.18.1',
    ],
    '@codemirror/lint' => [
        'version' => '6.8.1',
    ],
    '@lezer/highlight' => [
        'version' => '1.2.1',
    ],
    '@lezer/lr' => [
        'version' => '1.4.2',
    ],
    'style-mod' => [
        'version' => '4.1.2',
    ],
    'w3c-keyname' => [
        'version' => '2.2.8',
    ],
    '@lezer/common' => [
        'version' => '1.2.1',
    ],
    'crelt' => [
        'version' => '1.0.6',
    ],
    'chart.js' => [
        'version' => '4.4.4',
    ],
    '@kurkle/color' => [
        'version' => '0.3.2',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.3',
        'type' => 'css',
    ],
];
