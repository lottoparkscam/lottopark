<?php
    use Helpers\AssetHelper;
    use Helpers\UrlHelper;
?>
<!DOCTYPE html>
<html lang="<?= Config::get("language"); ?>" dir="ltr">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <title>White Lotto CRM</title>
    <link rel="stylesheet" href="<?= AssetHelper::mix('css/toastr.min.css', AssetHelper::TYPE_CRM); ?>"></link>
    <link rel="stylesheet" href="<?= AssetHelper::mix('css/chartist.min.css', AssetHelper::TYPE_CRM); ?>"></link>
    <link rel="stylesheet" href="<?= AssetHelper::mix('css/jvectormap.min.css', AssetHelper::TYPE_CRM); ?>"></link>
    <link rel="stylesheet" href="<?= AssetHelper::mix('css/style.min.css', AssetHelper::TYPE_CRM); ?>"></link>
    <?php
        echo html_tag('link', [
            'rel' => 'icon',
            'href' => Asset::get_file('crm/favicon.png', 'img'),
        ]);
    ?>
    <script>
        window.casinoPrefix = '<?= UrlHelper::getCurrentCasinoPrefix() ?>';
    </script>
</head>
<body>
	<div id="app"></div>
    <script src="<?= AssetHelper::mix('js/libs/waves.min.js', AssetHelper::TYPE_CRM); ?>"></script>
    <script src="<?= AssetHelper::mix('js/libs/jquery-1.12.3.min.js', AssetHelper::TYPE_CRM); ?>"></script>
    <script src="<?= AssetHelper::mix('js/libs/bootstrap.min.js', AssetHelper::TYPE_CRM); ?>"></script>
    <script src="<?= AssetHelper::mix('js/app.min.js', AssetHelper::TYPE_CRM); ?>"></script>
</body>
</html>