<?php
include(APPPATH . "views/admin/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/admin/reports/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Generate report"); ?>
        </h2>
        
        <p class="help-block">
            <?= _("Here you can generate a report for a given date range. The country and language fields are not required."); ?>
        </p>
        
        <?php
            include(APPPATH . "views/admin/reports/reports_filters.php");
        ?>
        
        <div class="container-fluid container-admin">
            <?php
                include(APPPATH . "views/whitelabel/shared/messages.php");
                
                foreach ($report_data_prepared as $full_report) {
                    if ($full_report['main_name']) {
                        $single_main_name = $full_report['main_name'];
                    }
                    
                    $single_main_info = $full_report['main_info'];
                    
                    if (!empty($full_report['finance_data'])) {
                        $single_finance_data = $full_report['finance_data'];
                    }
                    
                    if (!empty($full_report['sort'])) {
                        $single_sort = $full_report['sort'];
                    }
                    
                    if (!empty($full_report['finance_sums'])) {
                        $single_finance_sums = $full_report['finance_sums'];
                    }
                    
                    $single_payment_methods_purchase_report = $full_report['payment_methods_purchase_report'];
                    
                    $single_payment_methods_purchase_sums = $full_report['payment_methods_purchase_sums'];
                    
                    $single_payment_methods_deposit_report = $full_report['payment_methods_deposit_report'];
                    
                    $single_payment_methods_deposit_sums = $full_report['payment_methods_deposit_sums'];
                    
                    include(APPPATH . "views/admin/reports/single_report.php");
                }
            ?>
        </div>
    </div>
</div>