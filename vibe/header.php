<?php
/**
 * Shared layout header, included by the pages below. The Vibe runtime resolves
 * this file through the read_vibe_file tool when it hits the include.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle ?? 'Vibe PHP') ?></title>
    <style>
        body { font-family: ui-sans-serif, system-ui, sans-serif; max-width: 42rem; margin: 4rem auto; padding: 0 1rem; line-height: 1.6; color: #1a1a1a; }
        nav a { margin-right: 1rem; }
        .post { border-bottom: 1px solid #eee; padding: 1rem 0; }
        .muted { color: #888; font-size: 0.875rem; }
    </style>
</head>
<body>
    <nav>
        <a href="/">Home</a>
        <a href="/about">About</a>
        <a href="/posts/42">A post</a>
        <a href="/api/status">API</a>
    </nav>
    <hr>
