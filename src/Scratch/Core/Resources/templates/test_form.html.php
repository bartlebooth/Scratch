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
                    <?php foreach ($var('select::items') as $id => $item): ?>
                        <option value="<?= $id ?>" <?= $var('select', false) && ($var('select') == $id) ? 'selected="selected"': null ?>><?= $item ?></option>
                    <?php endforeach; ?>
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
                    <?php foreach ($var('selectMultiple::items') as $id => $item): ?>
                        <option value="<?= $id ?>"  <?= is_array($var('selectMultiple', false)) && in_array($id, $var('selectMultiple')) ? 'selected="selected"' : null ?>><?= $item ?></option>
                    <?php endforeach; ?>
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
                <?php foreach ($var('uncheckedRadio::items') as $id => $item): ?>
                        <input type="radio" name="uncheckedRadio" value="<?= $id ?>" <?= $var('uncheckedRadio', false) && ($var('uncheckedRadio') == $id) ? 'checked="checked"' : null ?>/><?= $item ?>
                <?php endforeach; ?>
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
                <?php foreach ($var('checkedRadio::items') as $id => $item): ?>
                        <input type="radio" name="checkedRadio" value="<?= $id ?>" <?= $var('checkedRadio', false) && ($var('checkedRadio') == $id) ? 'checked="checked"' : null ?>/><?= $item ?>
                <?php endforeach; ?>
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
                <?php foreach ($var('uncheckedBoxes::items') as $id => $item): ?>
                    <input type="checkbox" name="uncheckedBoxes[]" value="<?= $id ?>" <?= is_array($var('uncheckedBoxes', false)) && in_array($id, $var('uncheckedBoxes')) ? 'checked="checked"' : null ?>/><?= $item ?>
                <?php endforeach; ?>
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
                <?php foreach ($var('checkedBoxes::items') as $id => $item): ?>
                    <input type="checkbox" name="checkedBoxes[]" value="<?= $id ?>" <?= is_array($var('checkedBoxes', false)) && in_array($id, $var('checkedBoxes')) ? 'checked="checked"' : null ?>/><?= $item ?>
                <?php endforeach; ?>
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