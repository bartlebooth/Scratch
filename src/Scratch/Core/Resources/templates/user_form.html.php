<form id="user-creation-form" class="form-horizontal" enctype="multipart/form-data" action="<?= $path('/user/create', 'POST') ?>" method="post">
    <fieldset>
        <div class="control-group">
            <label class="control-label" for="username">Username :</label>
            <div class="controls">
                <input type="text" id="username" name="username" value="<?= $var('username', '') ?>" required="required"/>
                <span class="help-inline">
                    <ul>
                        <?php foreach ($var('username::errors', []) as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="password">Password :</label>
            <div class="controls">
                <input type="password" id="password" name="password[]" required="required" />
                <span class="help-inline">
                    <ul>
                        <?php foreach ($var('password::errors', []) as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="passwordConfirm">Confirm :</label>
            <div class="controls">
                <input type="password" id="passwordConfirm" name="password[]" required="required" />
                <span class="help-inline">
                    <ul>
                        <?php foreach ($var('password::errors', []) as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="firstName">First name :</label>
            <div class="controls">
                <input type="text" id="firstName" name="firstName" value="<?= $var('firstName', '') ?>" required="required" />
                <span class="help-inline">
                    <ul>
                        <?php foreach ($var('firstName::errors', []) as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="lastName">Last name :</label>
            <div class="controls">
                <input type="text" id="lastName" name="lastName" value="<?= $var('lastName', '') ?>" required="required" />
                <span class="help-inline">
                    <ul>
                        <?php foreach ($var('lastName::errors', []) as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="email">Email :</label>
            <div class="controls">
                <input type="text" id="email" name="email" value="<?= $var('email', '') ?>" />
                <span class="help-inline">
                    <ul>
                        <?php foreach ($var('email::errors', []) as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="avatar">Avatar :</label>
            <div class="controls">
                <input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
                <input type="file" id="avatar" name="avatar" />
                <span class="help-inline">
                    <ul>
                        <?php foreach ($var('avatar::errors', []) as $error): ?>
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