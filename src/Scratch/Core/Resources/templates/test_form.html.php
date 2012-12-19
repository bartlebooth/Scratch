<form id="user-creation-form" class="form-horizontal" enctype="multipart/form-data" action="<?= $path('/user/test', 'POST') ?>" method="post">
    <fieldset>

        <?= $formRow('text', 'text', 'Text') ?>
        <?= $formRow('password', 'password', 'Password', true) ?>
        <?= $formRow('password', 'password', 'Confirm', true) ?>
        <?= $formRow('select', 'select', 'Select') ?>
        <?= $formRow('selectMultiple', 'selectMultiple', 'Select multiple') ?>
        <?= $formRow('radio', 'uncheckedRadio', 'Unchecked radio') ?>
        <?= $formRow('radio', 'checkedRadio', 'Checked radio') ?>
        <?= $formRow('checkbox', 'uncheckedBoxes', 'Unchecked boxes') ?>
        <?= $formRow('checkbox', 'checkedBoxes', 'Checked boxes') ?>

        <div class="control-group">
            <label class="control-label" for="file">File :</label>
            <div class="controls">
                <input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
                <input type="file" id="file" name="file" />
                <span class="help-inline">
                    <ul>
                        <?php foreach ($var('file::errors', []) as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </span>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create</button>
        </div>
    </fieldset>
</form>