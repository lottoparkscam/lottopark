<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>asiapaymentgateway</title>
</head>
<body>
    <p>
        <?php echo _("Redirecting to the payment page..."); ?>
    </p>
    <form action="<?php echo $pdata['apiurl']; ?>" 
          method="post" 
          accept-charset="utf-8" 
          id="asiapaymentgateway">
        <?php
            foreach ($request as $key => $item):
        ?>
                <input type="hidden" 
                       name="<?php echo $key; ?>" 
                       value="<?php echo Security::htmlentities($item); ?>" 
                       size="50" />
                <br>
        <?php
            endforeach;
        ?>
    </form>
    <script type="text/javascript">
        document.getElementById("asiapaymentgateway").submit();
    </script>
</body>
</html>
