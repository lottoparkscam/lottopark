<?php
include(APPPATH . "views/admin/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/admin/whitelabels/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <div class="pull-right">
            <a href="<?= $urls["new"]; ?>" class="btn btn-success btn-sm">
                <span class="glyphicon glyphicon-plus"></span> <?= _("Add new"); ?>
            </a>
        </div>
        
        <h2>
            <?= _("Payment methods"); ?> <small><?= $whitelabel['name']; ?></small>
        </h2>
        
        <p class="help-block">
            <?= _("Here you can manage payment methods of your whitelabels."); ?>
            <br>
            <span class="text-warning">
                <?= $warning_text; ?>
            </span>
        </p>

        <div class="btn-group" role="group">
            <a href="<?= $urls["main"]; ?>" class="btn btn-default active">
                <?= _("Payment methods"); ?>
            </a>
        </div>
        
        <div class="container-fluid container-admin">
        <?php
            include(APPPATH . "views/admin/shared/messages.php");
            
            if ($methods !== null && count($methods) > 0):
        ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered table-sort">
                        <thead>
                            <tr>
                                <th>
                                    <?= _("Language"); ?>
                                </th>
                                <th>
                                    <?= _("Order"); ?>
                                </th>
                                <th>
                                    <?= _("Name"); ?>
                                </th>
                                <th>
                                    <?= _("Integrated Method"); ?>
                                </th>
                                <th class="text-center">
                                    <?= _("Show on payment page"); ?>
                                </th>
                                <th class="text-center">
                                    <?= _("Default payment currency"); ?>
                                </th>
                                <th class="text-center">
                                    <?= _("Manage"); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach ($payment_methods as $payment_method):
                            ?>
                                    <tr>
                                        <td>
                                            <?= $payment_method["lang_code"]; ?>
                                        </td>
                                        <td>
                                            <?= $payment_method["order"]; ?>
                                            <div class="pull-right">
                                                <?php
                                                    if ($payment_method['show_order_up']):
                                                ?>
                                                        <a href="<?= $payment_method["orderup_url"]; ?>" 
                                                           class="btn btn-xs btn-success"><span class="glyphicon glyphicon-chevron-up nmr"></span>
                                                        </a>
                                                <?php
                                                    endif;
                                                
                                                    if ($payment_method['show_order_down']):
                                                ?>
                                                        <a href="<?= $payment_method["orderdown_url"]; ?>" 
                                                           class="btn btn-xs btn-success"><span class="glyphicon glyphicon-chevron-down nmr"></span>
                                                        </a>
                                                <?php
                                                    endif;
                                                ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?= $payment_method['name']; ?>
                                        </td>
                                        <td>
                                            <?= $payment_method['pname']; ?>
                                        </td>
                                        <td class="text-center">
                                            <?= $payment_method["show"]; ?>
                                        </td>
                                        <td class="text-center">
                                            <?= $payment_method["default_payment_currency"]; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= $payment_method["edit_url"]; ?>" 
                                               class="btn btn-xs btn-success">
                                                <span class="glyphicon glyphicon-edit"></span> <?= _("Edit"); ?>
                                            </a>
                                            <a href="<?= $payment_method["currency_list_url"]; ?>" 
                                               class="btn btn-xs btn-success">
                                                <span class="glyphicon glyphicon-list"></span> <?= _("Currency list"); ?>
                                            </a>
                                            <a href="<?= $payment_method["customize_url"]; ?>" 
                                               class="btn btn-xs btn-success">
                                                <span class="glyphicon glyphicon-list"></span> <?= _("Customize"); ?>
                                            </a>
                                        </td>
                                    </tr>
                            <?php
                                endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
        <?php
            else:
        ?>
                <p class="text-info">
                    <?= _("There are no payment methods specified for this whitelabel."); ?>
                </p>
        <?php
            endif;
        ?>
        </div>
    </div>
</div>