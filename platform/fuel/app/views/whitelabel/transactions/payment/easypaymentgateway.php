<?php
/**
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 19.04.2019
 * Time: 11:45
 */
if (isset($adata) && is_array($adata)):
    ?>
    <h3>
        <?= Helpers_Payment_Method::EASY_PAYMENT_GATEWAY_NAME; ?>
    </h3>
    <span class="details-label" title="Additional data">
            <?= _('Additional data') ?>:
    </span>
    <a href="#" class="btn btn-xs btn-success show-data"><span class="glyphicon glyphicon-plus-sign"></span> <?= _("Show data"); ?></a>
    <pre class="hidden"><?= htmlspecialchars(json_encode($adata, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)); ?></pre>
    <?php
endif;
?>
