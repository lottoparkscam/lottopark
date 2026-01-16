<?php
use Helpers\UrlHelper;
echo lotto_platform_messages();
        
if (!empty($cards) && count($cards) > 0):
?>
    <table class="table table-payments">
        <thead>
            <tr>
                <th class="text-left">
                    <?= Security::htmlentities(_("Type")); ?>
                </th>
                <th class="text-left">
                    <?= Security::htmlentities(_("Number")); ?>
                </th>
                <th class="text-left">
                    <?= Security::htmlentities(_("Expiration date")); ?>
                </th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach ($cards as $key => $card):
                    $expiration_date = sprintf("%02d", $card['exp_month']);
                    $expiration_date .= '/';
                    $expiration_date .= sprintf("%02d", $card['exp_year']);
                    
                    $account_remove_link = lotto_platform_get_permalink_by_slug('account');
                    $account_remove_link .= 'payments/remove/';
                    $account_remove_link .= ($key+1);
            ?>
                    <tr>
                        <td class="card-title platform-card">
                            <?= $card['type']; ?>
                        </td>
                        <td class="platform-card">
                            <?= $card['card_number']; ?>
                        </td>
                        <td class="platform-card">
                            <span class="mobile-only-label">
                                <?= Security::htmlentities(_("Expiration date")); ?>:
                            </span> <?= $expiration_date; ?>
                        </td>
                        <td class="text-center">
                        <a href="<?= UrlHelper::esc_url($account_remove_link); ?>" 
                           class="confirm tooltip tooltip-bottom" 
                           data-tooltip="<?= Security::htmlentities(_("Remove")); ?>" 
                           data-title="<?= _("Confirm"); ?>" 
                           data-confirm="<?= _("Are you sure?"); ?>">
                                <span class="fa fa-times"></span>
                        </a>
                    </tr>
            <?php
                endforeach;
            ?>
        </tbody>
    </table>
<?php

else:
    
?>
    <p>
        <?= Security::htmlentities(_("No payment methods saved!")); ?>
    </p>
<?php

endif;
