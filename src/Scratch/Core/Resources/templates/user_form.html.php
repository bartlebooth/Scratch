<form id="user-creation-form" class="form-horizontal" enctype="multipart/form-data" action="<?= $path('/user/create', 'POST') ?>" method="post">
    <fieldset>
        <?= $formRow('text', 'username', 'Username')// required... ?>
        <?= $formRow('password', 'password', 'Password', ['arrayField' => true]) ?>
        <?= $formRow('password', 'password', 'Confirm', ['arrayField' => true]) ?>
        <?= $formRow('text', 'firstName', 'First name')// required... ?>
        <?= $formRow('text', 'lastName', 'Last name')// required... ?>
        <?= $formRow('text', 'email', 'Email')// required... ?>
        <?= $formRow('file', 'avatar', 'Avatar', ['size' => 1000000]) ?>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create</button>
        </div>
    </fieldset>
</form>