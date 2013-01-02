<h1>Template 3</h1>
<path><?= isset($path) && is_callable($path) ? 'ok' : 'ko '?></path>
<asset><?= isset($asset) && is_callable($asset) ? 'ok' : 'ko '?></asset>
<formRow><?= isset($formRow) && is_callable($formRow) ? 'ok' : 'ko '?></formRow>
<call><?= isset($call) && is_callable($call) ? 'ok' : 'ko '?></call>
<config><?= isset($config) && is_callable($config) ? 'ok' : 'ko '?></config>
<raw><?= isset($raw) && is_callable($raw) ? 'ok' : 'ko '?></raw>
<flashes><?= isset($flashes) && is_callable($flashes) ? 'ok' : 'ko '?></flashes>