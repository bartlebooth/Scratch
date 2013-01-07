<div>
    <form id="valid-form-1" action="<?= $path('/prefix4/action2', 'POST') ?>" method="post">
        <?= $formRow('text', 'foo', 'Foo') ?>
        <?= $formRow('text', 'bar', 'Bar') ?>
    </form>
</div>