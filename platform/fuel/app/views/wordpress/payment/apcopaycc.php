<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>APCOPAY CC</title>
    </head>
    <body>
        <p>
            <?= _("Redirecting to the payment page..."); ?>
        </p>

        <form id="apcopayccform" 
              method="post" 
              action="<?= $fast_pay_url; ?>">
            <input name="params" 
                   type="hidden" 
                   value="<?= urlencode(trim($transaction_xml_string)); ?>">
        </form>

        <script type="text/javascript">
            document.getElementById("apcopayccform").submit();
        </script>
    </body>
</html>