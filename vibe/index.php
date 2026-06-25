<?php
/**
 * Front controller for the VibePHP demo site.
 *
 * None of this code is ever really executed: the Vibe runtime reads it and
 * imagines the result. The "database" below has no rows and PDO never connects
 * to anything — the AI invents believable data at request time. Any URL that
 * doesn't map to its own file lands here and is routed by the path.
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Pretend we have a database connection.
$db = new PDO('mysql:host=localhost;dbname=blog', 'root', '');

// Dynamic route: /posts/{id}
if (preg_match('#^/posts/(\d+)$#', $path, $matches)) {
    $id = (int) $matches[1];

    $stmt = $db->prepare('SELECT title, body, author, published_at FROM posts WHERE id = ?');
    $stmt->execute([$id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (! $post) {
        http_response_code(404);
        $pageTitle = 'Not found';
        require __DIR__.'/header.php';
        echo "<h1>404</h1><p>No post with id {$id}.</p></body></html>";
        exit;
    }

    $pageTitle = $post['title'];
    require __DIR__.'/header.php';
    ?>
    <article>
        <h1><?= htmlspecialchars($post['title']) ?></h1>
        <p class="muted">By <?= htmlspecialchars($post['author']) ?> · <?= htmlspecialchars($post['published_at']) ?></p>
        <div><?= $post['body'] ?></div>
    </article>
    </body></html>
    <?php
    exit;
}

// Home page: list the latest posts.
$pageTitle = 'VibePHP — Home';
require __DIR__.'/header.php';

$posts = $db->query('SELECT id, title, excerpt, published_at FROM posts ORDER BY published_at DESC LIMIT 5')
    ->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Welcome to VibePHP</h1>
<p>This page was "executed" by an AI that read the source and made up the data.</p>

<h2>Latest posts</h2>
<?php foreach ($posts as $post) { ?>
    <div class="post">
        <h3><a href="/posts/<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h3>
        <p><?= htmlspecialchars($post['excerpt']) ?></p>
        <p class="muted">Published <?= htmlspecialchars($post['published_at']) ?></p>
    </div>
<?php } ?>

<p class="muted">Rendered at <?= date('Y-m-d H:i:s') ?> · <?= count($posts) ?> posts</p>
</body>
</html>
