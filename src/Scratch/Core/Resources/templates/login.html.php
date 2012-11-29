<?php if ($var('loginError', false)): ?>
    <div id="login-error" class="alert-error">
        <button type="button" class="close" data-dismiss="alert">×</button>
        Authentication has failed.
    </div>
<?php endif; ?>
<form id="login-form" class="form-horizontal" action="<?= $path('/login', 'POST') ?>" method="post">
    <fieldset>
        <div class="control-group">
            <label class="control-label" for="username">Username :</label>
            <div class="controls">
                <input type="text" id="username" name="username" value="" required="required"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="username">Password :</label>
            <div class="controls">
                <input type="password" id="password" name="password" required="required" />
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Login</button>
        </div>
    </fieldset>
</form>