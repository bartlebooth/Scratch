<!DOCTYPE html>
<!--[if lt IE 7]>
<html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="<?= $config('locale') ?>">
<![endif]-->
<!--[if IE 7]>
<html class="no-js lt-ie9 lt-ie8" lang="<?= $config('locale') ?>">
<![endif]-->
<!--[if IE 8]>
<html class="no-js lt-ie9" lang="<?= $config('locale') ?>">
<![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="<?= $config('locale') ?>">
<!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $var('title', 'Scratch') ?></title>
        <link rel="shortcut icon" href="<?= $asset($var('favicon', '/img/claroline.ico')) ?>"/>
        <link rel="stylesheet" type="text/css" href="<?= $asset('/css/bootstrap.css') ?>"/>
        <link rel="stylesheet" type="text/css" href="<?= $asset('/css/bootstrap-responsive.css') ?>"/>
        <!-- The Modernizr script MUST stay before the body tag. -->
        <script src="<?= $asset('/js/modernizr-2.5.3.min.js') ?>"></script>
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class=chromeframe>
                Your browser is <em>ancient!</em>
                <a href="http://browsehappy.com/">Upgrade to a different browser</a> or
                <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.
            </p>
        <![endif]-->
        <?= $call('Scratch\Core\Renderer\NavbarRenderer') ?>
        <div id="wrapper">
            <div id="grid" class="container-fluid">
                <div class="row-fluid">
                    <div class="span12">
                        <h2 class="section-header"><?= $var('sectionTitle', '') ?></h2>
                    </div>
                </div>
                <div class="row-fluid">
                    <?php foreach ($flashes() as $category => $messages): ?>
                        <div class="alert alert-<?= $category ?>">
                            <a class="close" data-dismiss="alert" href="#">×</a>
                            <ul>
                                <?php foreach ($messages as $message): ?>
                                    <li><?= $message ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="row-fluid">
                    <?= $raw('body', '') ?>
                </div>
            </div>
        </div>
        <?= $call('Scratch\Core\Renderer\FooterRenderer') ?>
        <script type="text/javascript" src="<?= $asset('/js/jquery-1.7.1.min.js') ?>"></script>
        <script type="text/javascript" src="<?= $asset('/js/bootstrap.js') ?>"></script>
    </body>
</html>