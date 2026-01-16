<?php
    include(APPPATH . "views/admin/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php
            include(APPPATH . "views/admin/payment/menu.php");
        ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Payments"); ?> <small><?= _("Payment Logs"); ?></small>
        </h2>
        
        <p class="help-block">
            <?= _("Here you can find all logs related to payments. Logs are kept for 1 year."); ?>
        </p>
        
        <?php
            include(APPPATH . "views/admin/payment/paymentlogs_filters.php");
        ?>
        
        <div class="container-fluid container-admin">
            <?php
                if ($logs !== null && count($logs) > 0):
                    echo $pages->render();
            ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?= _("Type"); ?></th>
                                    <th><?= _("Date"); ?></th>
                                    <th><?= _("Whitelabel"); ?></th>
                                    <th class="text-nowrap"><?= _("Payment Method"); ?></th>
                                    <th class="text-nowrap"><?= _("Transaction ID"); ?></th>
                                    <th><?= _("Message"); ?></th>
                                    <th><?= _("Data"); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                foreach ($logs as $item):
                                    $tr_class = Lotto_View::type_to_class($item['type']);
                                    $tr_class .= " text-";
                                    $tr_class .= Lotto_View::type_to_class($item['type']);
                                    
                                    $item_name = '';
                                    if (!empty($item['name'])) {
                                        $item_name = Security::htmlentities($item['name']);
                                    }
                                    
                                    $payment_type_name = '';
                                    switch ($item['payment_method_type']):
                                        case Helpers_General::PAYMENT_TYPE_BONUS_BALANCE:
                                            $payment_type_name = _("Bonus balance");
                                            break;
                                        case Helpers_General::PAYMENT_TYPE_BALANCE:
                                            $payment_type_name = _("User balance");
                                            break;
                                        case Helpers_General::PAYMENT_TYPE_CC:
                                            $payment_type_name = _("Credit Card");
                                            $payment_type_name .= "<br>";
                                            if (isset($item['cc_method'])) {
                                                $payment_type_name .= $ccmethods[$item['cc_method']];
                                            }
                                            break;
                                        case Helpers_General::PAYMENT_TYPE_OTHER:
                                            $payment_type_name = "";
                                            if (!empty($item['payment_method_name'])) {
                                                $payment_type_name .= $item['payment_method_name'];
                                            }
                                            if (!empty($item['whitelabel_payment_method_name'])) {
                                                $payment_type_name .= " (" .
                                                    $item['whitelabel_payment_method_name'] .
                                                    ")";
                                            }
                                            break;
                                    endswitch;
                                    
                                    $item_transaction_text = '';
                                    if (!empty($item['whitelabel_transaction_id'])) {
                                        $item_transaction_text .= intval($item['whitelabel_transaction_id']);
                                        $item_transaction_text .= "<br>";
                                        $item_transaction_text .= $item['prefix'];
                                        $item_transaction_text .= ($item['wt_type'] == 0 ? 'P' : 'D');
                                        $item_transaction_text .= $item['token'];
                                    }
                                    
                                    $item_message = $item['message'];
                                    if (!empty($item['name'])) {
                                        $item_message = Security::htmlentities($item['message']);
                                    }
                            ?>
                                    <tr class="<?= $tr_class; ?>">
                                        <td>
                                            <strong>
                                                <?= Lotto_View::type_to_name($item['type']); ?>
                                            </strong>
                                        </td>
                                        <td class="text-nowrap">
                                            <?= Lotto_View::format_date($item['date']); ?>
                                        </td>
                                        <td class="text-nowrap">
                                            <?= $item_name; ?>
                                        </td>
                                        <td class="text-nowrap">
                                            <?= $payment_type_name; ?>
                                        </td>
                                        <td>
                                            <?= $item_transaction_text; ?>
                                        </td>
                                        <td class="text-break">
                                            <?= $item_message; ?>
                                        </td>
                                        <td>

                                            <?php
                                                $asArray = json_decode($item['data_json'], true);
                                                if (!empty($asArray['transaction']['whitelabel_payment_method']['data_json'])) {
                                                    $asArray['transaction']['whitelabel_payment_method']['data_json'] = json_decode($asArray['transaction']['whitelabel_payment_method']['data_json'], true);
                                                }
                                                ob_start();
                                                if (empty($asArray) && !empty(unserialize($item['data']))) {
                                                    var_dump(unserialize($item['data']));
                                                }
                                                echo json_encode($asArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                                                $data = ob_get_clean();

                                                if (!empty($item['data']) || !empty($item['data_json'])):
                                            ?>
                                                    <a href="#" class="btn btn-xs btn-success show-data">
                                                        <span class="glyphicon glyphicon-plus-sign"></span> <?= _("Show data"); ?>
                                                    </a>
                                            <?php
                                                endif;
                                            ?>
                                            <pre class="hidden">
                                                <?= htmlspecialchars($data); ?>
                                            </pre>
                                        </td>
                                    </tr>
                            <?php
                                endforeach;
                            ?>
                            </tbody>
                        </table>
                    </div>
            <?php
                    echo $pages->render();
                else:
            ?>
                    <p class="text-info">
                        <?= _("There are no logs for this criteria."); ?>
                    </p>
            <?php
                endif;
            ?>
        </div>
    </div>
</div>
