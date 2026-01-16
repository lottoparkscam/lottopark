<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/aff/reports/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("Commissions"); ?>
        </h2>
		<p class="help-block">
            <?= _("Here you can view your latest commissions."); ?>
        </p>
        
		<?php
            include(APPPATH . "views/aff/reports/commissions_filters.php");
        ?>
        
		<div class="container-fluid container-admin">
            <?php 
                include(APPPATH . "views/aff/shared/messages.php");

                if ($commissions !== null && count($commissions) > 0):
                    echo $pages;
            ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered table-sort">
                            <thead>
                                <tr>
                                    <?php 
                                        if ($is_lead_id_visible):
                                    ?>
                                            <th>
                                                <?= _("ID"); ?>
                                            </th>
                                    <?php 
                                        endif;
                                        
                                        if ($user['is_show_name']):
                                    ?>
                                            <th>
                                                <?= _("Lead name") ?>
                                            </th>
                                    <?php 
                                        endif;

                                        if ($user['is_show_name']):
                                    ?>
                                            <th>
                                                <?= _("Lead e-mail") ?>
                                            </th>
                                    <?php 
                                        endif;
                                        
                                        if ($is_transaction_id_visible):
                                    ?>
                                            <th>
                                                <?= _("Transaction ID"); ?>
                                            </th>
                                    <?php 
                                        endif;
                                    ?>
                                    <th>
                                        <?= _("Medium"); ?>
                                    </th>
                                    <th>
                                        <?= _("Campaign"); ?>
                                    </th>
                                    <th>
                                        <?= _("Content"); ?>
                                    </th>
                                    <th>
                                        <?= _("Type"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Date"); ?>
                                    </th>
                                    <?php 
                                        if ($is_amount_visible):
                                    ?>
                                            <th>
                                                <?= _("Amount"); ?>
                                            </th>
                                    <?php 
                                        endif;

                                        if ($are_ticket_and_payment_cost_visible):
                                    ?>
                                            <th class="text-nowrap">
                                                <?= _("Ticket cost"); ?>
                                                <br>
                                                <?= _("Payment cost"); ?>
                                            </th>
                                    <?php 
                                        endif;

                                        if ($is_income_visible):
                                    ?>
                                            <th>
                                                <?= _("Income"); ?>
                                            </th>
                                    <?php 
                                        endif;
                                    ?>
                                    <th>
                                        <?= _("Commission"); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    foreach ($commissions as $commission):
                                ?>
                                        <tr>
                                            <?php 
                                                if ($is_lead_id_visible):
                                            ?>
                                                    <td>
                                                        <?= $commission['user_full_name']; ?>
                                                    </td>
                                            <?php 
                                                endif;
                                                
                                                if ($user['is_show_name']):
                                            ?>
                                                    <td>
                                                        <?= $commission['lead_name']; ?>
                                                    </td>
                                            <?php 
                                                endif;

                                                if ($user['is_show_name']):
                                            ?>
                                                    <td>
                                                        <?= $commission['lead_email']; ?>
                                                    </td>
                                            <?php 
                                                endif;
                                                
                                                if ($is_transaction_id_visible):
                                            ?>
                                                    <td>
                                                        <?= $commission['transaction_id']; ?>
                                                    </td>
                                            <?php 
                                                endif;
                                            ?>
                                            <td>
                                                <?= $commission['medium']; ?>
                                            </td>
                                            <td>
                                                <?= $commission['campaign']; ?>
                                            </td>
                                            <td>
                                                <?= $commission['content']; ?>
                                            </td>
                                            <td>
                                                <?= $commission['aff_type']; ?>
                                                <br>
                                                <?= $commission['tier'];?>
                                            </td>
                                            <td class="text-center">
                                                <?= $commission['date_confirmed']; ?>
                                            </td>
                                            <?php 
                                                if ($is_amount_visible):
                                            ?>
                                                    <td>
                                                        <?php 
                                                            echo $commission['amount_manager'];

                                                            if (!empty($commission['amounts_other'])):
                                                        ?>
                                                                <small>
                                                                    <span class="glyphicon glyphicon-info-sign" 
                                                                          data-toggle="tooltip" 
                                                                          data-placement="top" 
                                                                          title="" 
                                                                          data-original-title="<?php 
                                                                            echo $commission['amounts_other'];
                                                                    ?>"></span>
                                                                </small>
                                                        <?php 
                                                            endif;
                                                        ?>
                                                    </td>
                                            <?php
                                                endif;

                                                if ($are_ticket_and_payment_cost_visible):
                                            ?>
                                                    <td>
                                                        <?php 
                                                            echo $commission['cost_manager'];

                                                            if (!empty($commission['costs_other'])):
                                                        ?>
                                                                <small>
                                                                    <span class="glyphicon glyphicon-info-sign" 
                                                                          data-toggle="tooltip" 
                                                                          data-placement="top" 
                                                                          title="" 
                                                                          data-original-title="<?php 
                                                                            echo $commission['costs_other'];
                                                                    ?>"></span>
                                                                </small>
                                                        <?php 
                                                            endif;
                                                        ?>
                                                        <br>
                                                        <?php 
                                                            echo $commission['payment_cost_manager'];

                                                            if (!empty($commission['payment_costs_other'])):
                                                        ?>
                                                                <small>
                                                                    <span class="glyphicon glyphicon-info-sign" 
                                                                          data-toggle="tooltip" 
                                                                          data-placement="top" 
                                                                          title="" 
                                                                          data-original-title="<?php 
                                                                            echo $commission['payment_costs_other'];
                                                                    ?>"></span>
                                                                </small>
                                                        <?php 
                                                            endif;
                                                        ?>
                                                    </td>
                                            <?php
                                                endif;

                                                if ($is_income_visible):
                                            ?>
                                                    <td>
                                                        <?php 
                                                            echo $commission['real_income_manager'];

                                                            if (!empty($commission['real_incomes_other'])):
                                                        ?>
                                                                <small>
                                                                    <span class="glyphicon glyphicon-info-sign" 
                                                                          data-toggle="tooltip" 
                                                                          data-placement="top" 
                                                                          title="" 
                                                                          data-original-title="<?php 
                                                                            echo $commission['real_incomes_other'];
                                                                    ?>"></span>
                                                                </small>
                                                        <?php 
                                                            endif;
                                                        ?>
                                                    </td>
                                            <?php
                                                endif;
                                            ?>
                                            <td>
                                                <?php 
                                                    echo $commission['commission_manager'];

                                                    if (!empty($commission['commissions_other'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?php 
                                                                    echo $commission['commissions_other'];
                                                            ?>"></span>
                                                        </small>
                                                <?php 
                                                    endif;
                                                ?>
                                            </td>
                                        </tr>
                                <?php 
                                    endforeach;
                                ?>
                            </tbody>
                        </table>
                    </div>
            <?php 
                    echo $pages;
                else:
            ?>
                    <p class="text-info">
                        <?= _("No commissions."); ?>
                    </p>
            <?php 
                endif;
            ?>
		
		</div>
	</div>
</div>