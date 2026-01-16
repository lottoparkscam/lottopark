<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/aff/reports/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?php echo _("Affiliate details"); ?> <small><?php echo Security::htmlentities($user['email']); ?></small>
        </h2>
        <p class="help-block">
            <?php echo _("You can view sub-affiliate details here."); ?>
        </p>
        <a href="/subaffiliates" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?php echo _("Back"); ?>
        </a>
        <div class="container-fluid container-admin row">
            <?php include(APPPATH . "views/whitelabel/shared/messages.php"); ?>

            <div class="col-md-6 user-details">
                <?php if (!empty($details)) : ?>
                    <?php foreach ($details as $detail) : ?>
                        <span class="details-label"><?= $detail[0] ?></span>
                        <span class="details-value"><?= $detail[1] ?></span>
                        <br>
                    <?php endforeach; ?>
                <?php else : ?>
                    <?= _("No details.") ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

