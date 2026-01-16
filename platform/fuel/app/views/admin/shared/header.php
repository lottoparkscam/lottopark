<!DOCTYPE html>
<html lang="<?= Config::get("language"); ?>">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>
        <?php 
            $title_text = '';
            if (isset($title)) {
                $title_text .= $title . ' &bull; ';
            }
            $title_text .= _("Admin panel");
            $title_text .= ' &bull; ';
            $title_text .= _("Lotto network");
            
            echo $title_text;
        ?>
    </title>
<?php 
    echo Asset::css("bootstrap.min.css");
    echo Asset::css("bootstrap-datepicker3.min.css");
    echo Asset::css("admin.min.css");
?>
</head>
<body>
