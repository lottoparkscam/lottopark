<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=en"></script>
<?php echo Asset::js("jquery-1.12.3.min.js"); ?>
<?php echo Asset::js("bootstrap.min.js"); ?>
<?php echo Asset::js("bootstrap-datepicker.min.js"); ?>
<?php if (isset($this->captcha)): ?>
<?php 	echo Asset::js(\Helpers\CaptchaHelper::getCaptchaApiUrl()); ?>
<?php endif; ?>
<?php echo Asset::js("whitelabel.min.js"); ?>
</body>
</html>