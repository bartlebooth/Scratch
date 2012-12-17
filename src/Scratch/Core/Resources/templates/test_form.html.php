<form id="user-creation-form" class="form-horizontal" enctype="multipart/form-data" action="<?= $path('/user/test', 'POST') ?>" method="post">
    <fieldset>
        <div class="control-group">
            <label class="control-label" for="text">Text :</label>
            <div class="controls">
                <input type="text" id="text" name="text" value="<?= $var('text', '') ?>"/>
                <span class="help-inline">
                    <ul>
                        <?php foreach ($var('text::errors', []) as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="password">Password :</label>
            <div class="controls">
                <input type="password" id="password" name="password[]" />
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
                <input type="password" id="passwordConfirm" name="password[]" />
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
        <div class="control-group">
            <label class="control-label" for="select">Select :</label>
            <div class="controls">
                <select id="select" name="select">
                    <option value="1">Option 1</option>
                    <option value="2">Option 2</option>
                    <option value="3">Option 3</option>
                </select>
                <span class="help-inline">
                    <ul>
                        <?php foreach ($var('select::errors', []) as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="selectMultiple">Select multiple :</label>
            <div class="controls">
                <select id="selectMultiple" name="selectMultiple[]" multiple="multiple">
                    <option value="1">Option multiple 1</option>
                    <option value="2">Option multiple 2</option>
                    <option value="3">Option multiple 3</option>
                </select>
                <span class="help-inline">
                    <ul>
                        <?php foreach ($var('selectMultiple::errors', []) as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="uncheckedRadio">Unchecked radio :</label>
            <div class="controls">
                <input type="radio" name="uncheckedRadio" value="Unchecked radio 1"/>Unchecked radio 1
                <input type="radio" name="uncheckedRadio" value="Unchecked radio 2"/>Unchecked radio 2
                <input type="radio" name="uncheckedRadio" value="Unchecked radio 3"/>Unchecked radio 3
                <span class="help-inline">
                    <ul>
                        <?php foreach ($var('uncheckedRadio::errors', []) as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="checkedRadio">Checked radio :</label>
            <div class="controls">
                <input type="radio" name="checkedRadio" value="Checked radio 1" checked="checked"/>Checked radio 1
                <input type="radio" name="checkedRadio" value="Checked radio 2"/>Checked radio 2
                <input type="radio" name="checkedRadio" value="Checked radio 3"/>Checked radio 3
                <span class="help-inline">
                    <ul>
                        <?php foreach ($var('checkedRadio::errors', []) as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="uncheckedBoxes">Unchecked boxes :</label>
            <div class="controls">
                <input type="checkbox" name="uncheckedBoxes[]" value="Unchecked boxes 1"/>Unchecked boxes 1
                <input type="checkbox" name="uncheckedBoxes[]" value="Unchecked boxes 2"/>Unchecked boxes 2
                <input type="checkbox" name="uncheckedBoxes[]" value="Unchecked boxes 3"/>Unchecked boxes 3
                <span class="help-inline">
                    <ul>
                        <?php foreach ($var('uncheckedBoxes::errors', []) as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </span>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="checkedBoxes">Checked boxes :</label>
            <div class="controls">
                <input type="checkbox" name="checkedBoxes[]" value="Checked boxes 1" checked="checked"/>Checked boxes 1
                <input type="checkbox" name="checkedBoxes[]" value="Checked boxes 2"/>Checked boxes 2
                <input type="checkbox" name="checkedBoxes[]" value="Checked boxes 3"/>Checked boxes 3
                <span class="help-inline">
                    <ul>
                        <?php foreach ($var('checkedBoxes::errors', []) as $error): ?>
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