<?php

/**
 * A JSON API endpoint. The Vibe runtime should translate the Content-Type
 * header and return a believable JSON body.
 */
header('Content-Type: application/json');

echo json_encode([
    'service' => 'vibe-php',
    'status' => 'ok',
    'time' => date(DATE_ATOM),
    'load' => [
        'requests_served' => rand(1000, 99999),
        'uptime_seconds' => rand(60, 8_640_000),
    ],
    'version' => '1.0.0-vibe',
], JSON_PRETTY_PRINT);
