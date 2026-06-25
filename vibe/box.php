<?php
/**
 * A demo of generics in PHP. This is not valid PHP. The Zend Engine would
 * reject it instantly. The Vibe runtime does not have a Zend Engine, so the
 * AI reads the generic types, nods, and plays along. Visit /box to watch.
 */

$pageTitle = 'Generics — VibePHP';

/**
 * A perfectly ordinary generic container. T can be anything. T is a vibe.
 *
 * @template T
 */
class Box<T>
{
    public function __construct(private T $value) {}

    public function get(): T
    {
        return $this->value;
    }
}

/** @var Box<User> $userBox */
$userBox = new Box<User>(new User('Ada Lovelace'));

/** @var Box<int> $numberBox */
$numberBox = new Box<int>(42);

require __DIR__.'/header.php';
?>

<h1>Generics? Sure.</h1>
<p>
    PHP has wanted generics for over a decade. This page uses them anyway. The
    AI knows <code>$userBox-&gt;get()</code> returns a <code>User</code> and
    <code>$numberBox-&gt;get()</code> returns an <code>int</code>. It just knows.
</p>

<ul>
    <li>User box holds: <strong><?= htmlspecialchars($userBox->get()->name) ?></strong> (a <code>User</code>)</li>
    <li>Number box holds: <strong><?= $numberBox->get() ?></strong> (an <code>int</code>)</li>
</ul>

<p class="muted">
    Type safety guaranteed by the model's general sense that your types probably
    line up. There is no <code>User</code> class defined anywhere. There is no
    <code>$value</code> in memory. Validity is a feeling, not a grammar.
</p>
</body>
</html>
