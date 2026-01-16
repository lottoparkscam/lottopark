<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Provide password</title>
    <?php echo Asset::css('bootstrap.min.css'); ?>
</head>
<body>
<div class="container">
    <div>
    <h1>You have to provide password to proceed</h1>
    <?php
    if(isset($error))
    {
        echo '<p class="text-danger">'.$error.'</p>';
    }
    ?>
    <form action="" method="POST">
        <input type="password" name="password" placeholder="Password">
        <input type="submit" value="Proceed">
    </form>
    </div>
</div>
</body>
</html>