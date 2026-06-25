<?php
$pageTitle = 'About — VibePHP';
require __DIR__.'/header.php';
?>

<h1>About</h1>
<p>
    VibePHP is a web server engine where PHP is never executed. Scripts are read
    by an AI that interprets them in its head and returns whatever HTTP response
    it believes the code would produce.
</p>
<p class="muted">Server uptime: <?= rand(1, 400) ?> days (allegedly).</p>
</body>
</html>
