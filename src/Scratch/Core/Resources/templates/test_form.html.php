<form id="user-creation-form" class="form-horizontal" enctype="multipart/form-data" action="<?= $path('/user/test', 'POST') ?>" method="post">
    <fieldset>
        <?= $formRow('text', 'text', 'Text') ?>
        <?= $formRow('text', 'disabledText', 'Info (disabled)', ['disabled' => true]) ?>
        <?= $formRow('textarea', 'textarea', 'Text area', ['size' => 50]) ?>
        <?= $formRow('password', 'password', 'Password', ['arrayField' => true]) ?>
        <?= $formRow('password', 'password', 'Confirm', ['arrayField' => true]) ?>
        <?= $formRow('select', 'select', 'Select') ?>
        <?= $formRow('selectMultiple', 'selectMultiple', 'Select multiple') ?>
        <?= $formRow('radio', 'uncheckedRadio', 'Unchecked radio') ?>
        <?= $formRow('radio', 'checkedRadio', 'Checked radio') ?>
        <?= $formRow('checkbox', 'uncheckedBoxes', 'Unchecked boxes') ?>
        <?= $formRow('checkbox', 'checkedBoxes', 'Checked boxes') ?>
        <?= $formRow('file', 'file', 'File', ['size' => 2000000]) ?>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create</button>
        </div>
    </fieldset>
</form>