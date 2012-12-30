<h1>Template 3</h1>
<path><?= isset($path) && is_callable($path) ? 'ok' : 'ko '?></path>
<asset><?= isset($asset) && is_callable($asset) ? 'ok' : 'ko '?></asset>
<formRow><?= isset($formRow) && is_callable($formRow) ? 'ok' : 'ko '?></formRow>