<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="utf-8">
    <title>ENTROPAY.COM</title>
</head>
<body>
    <p>
        <?php
            echo _("Redirecting to the payment page...");
            
            $action_url = 'https://';
            if ($pdata['test']) {
                $action_url .= 'staging';
            } else {
                $action_url .= 'secure2';
            }
            $action_url .= '.entropay.com/processes/upaffiliatelanding/';
            $action_url .= 'unprot/affiliatewelcome.do';
        ?>
    </p>
    <form action="<?php echo $action_url; ?>" 
          method="post" 
          accept-charset="utf-8" 
          id="entropayform">
        <?php
            foreach ($query as $key => $item):
        ?>
                <input type="hidden" 
                       name="<?php echo $key; ?>" 
                       value="<?php echo Security::htmlentities($item); ?>" />
        <?php
            endforeach;
        ?>
    </form>
    
    <script type="text/javascript">
        document.getElementById("entropayform").submit();
    </script>
</body>
</html>