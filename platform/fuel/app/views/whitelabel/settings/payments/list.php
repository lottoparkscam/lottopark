<?php
include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/whitelabel/settings/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Payment methods"); ?>
        </h2>
        
        <p class="help-block">
            <?= _("Here you can view and manage site payment methods."); ?>
            <br>
            <span class="text-warning">
                <?= $warning_text; ?>
            </span>
        </p>
        
        <?php
            if ($show_new_button):
        ?>
            <div class="pull-right">
                <a href="<?= $urls["new"]; ?>" class="btn btn-success">
                    <span class="glyphicon glyphicon-plus"></span> <?= _("Add New"); ?>
                </a>
            </div>
        <?php
            endif;
        ?>

        <div class="btn-group" role="group">
            <a href="<?= $urls["main"]; ?>" class="btn btn-default active">
                <?= _("Payment methods"); ?>
            </a>
        </div>
        
        <div class="container-fluid container-admin">
        <?php
            include(APPPATH . "views/whitelabel/shared/messages.php");
            
            if (isset($methods) && count($methods) > 0):
                //echo $pages;
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
                                <?php
                                    if ($show_column_show_on_payment_page):
                                ?>
                                        <th class="text-center">
                                            <?= _("Show on payment page"); ?>
                                        </th>
                                <?php
                                    endif;
                                ?>
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
                                    if ($payment_method['hide_row']):
                                        continue;
                                    endif;
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
                                        <?php
                                            if ($show_column_show_on_payment_page):
                                        ?>
                                                <td class="text-center">
                                                    <?= $payment_method["show"]; ?>
                                                </td>
                                        <?php
                                            endif;
                                        ?>
                                        <td class="text-center">
                                            <?= $payment_method["default_payment_currency"]; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                                if ($payment_method['show_edit']):
                                            ?>
                                                    <a href="<?= $payment_method["edit_url"]; ?>" 
                                                       class="btn btn-xs btn-success">
                                                        <span class="glyphicon glyphicon-edit"></span> <?= _("Edit"); ?>
                                                    </a>
                                            <?php
                                                endif;
                                            ?>
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
                //echo $pages;
            else:
        ?>
                <p class="text-info"><?= _("No payment methods."); ?></p>
        <?php
            endif;
        ?>
        </div>
    </div>
</div>