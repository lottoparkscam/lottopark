<!DOCTYPE html>
<html lang="<?php echo Config::get("language"); ?>">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo isset($title) ? $title.' &bull; ' : ''; ?><?php echo sprintf(_("%s management panel"), _("Affiliate")); ?> &bull; <?php echo _("Lotto network"); ?></title>
<?php echo Asset::css("bootstrap.min.css"); ?>
<?php echo Asset::css("bootstrap-datepicker3.min.css"); ?>
<?php echo Asset::css("admin.min.css"); ?>
</head>
<body>
