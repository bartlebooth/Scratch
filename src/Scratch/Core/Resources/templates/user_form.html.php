<form id="user-creation-form" class="form-horizontal" action="<?= $path('/user/create', 'POST') ?>" method="post">
    <fieldset>
        <div class="control-group">
            <label class="control-label" for="username">Username :</label>
            <div class="controls">
                <input type="text" id="username" name="username" value="<?= $var('username', '') ?>" required="required"/>
                <span class="help-inline"><?= $var('username::error', '') ?></span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="password">Password :</label>
            <div class="controls">
                <input type="password" id="password" name="password[]" required="required" />
                <span class="help-inline"><?= $var('password::error', '') ?></span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="passwordConfirm">Confirm :</label>
            <div class="controls">
                <input type="password" id="passwordConfirm" name="password[]" required="required" />
                <span class="help-inline"><?= $var('password::error', '') ?></span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="firstName">First name :</label>
            <div class="controls">
                <input type="text" id="firstName" name="firstName" value="<?= $var('firstName', '') ?>" required="required" />
                <span class="help-inline"><?= $var('firstName::error', '') ?></span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="lastName">Last name :</label>
            <div class="controls">
                <input type="text" id="lastName" name="lastName" value="<?= $var('lastName', '') ?>" required="required" />
                <span class="help-inline"><?= $var('lastName::error', '') ?></span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="email">Email :</label>
            <div class="controls">
                <input type="text" id="lastName" name="email" value="<?= $var('email', '') ?>" />
                <span class="help-inline"><?= $var('email::error', '') ?></span>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create</button>
        </div>
    </fieldset>
</form>