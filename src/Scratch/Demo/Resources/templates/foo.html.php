<h2>THIS IS FOO TEMPLATE</h2>

<li>Value of foo : <?= $var('foo') ?></li>
<li>Value of bar : <?= $var('bar') ?></li>
<li>Value of x : <?= $var('x', 'y') ?></li>
<a href="<?= $path('/prefixFoo/index') ?>">Link</a>
<a href="<?= $path('/prefixFoo/index') ?>">Link</a>
<a href="<?= $asset('/css/foo.css') ?>">Asset</a>

<?php $render(__DIR__ . '/bar.html.php') ?>
<?php $render(__DIR__ . '/bar.html.php', array('bar' => 'newBar')) ?>

<h2>END OF FOO TEMPLATE</h2>