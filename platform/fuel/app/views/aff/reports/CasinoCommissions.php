<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/aff/reports/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("Casino commissions"); ?>
        </h2>
		<p class="help-block">
            <?= _("Here you can view your latest casino commissions."); ?>
        </p>
        
		<?php
            include(APPPATH . "views/aff/reports/commissions_filters.php");
		?>
        
		<div class="container-fluid container-admin">
            <?php
		        include(APPPATH . "views/aff/shared/messages.php");

		if (count($casinoCommissions ?? []) > 0):
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
                                            <th>
                                                <?= _("Lead e-mail") ?>
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
                                    <th class="text-center">
                                        <?= _("Date"); ?>
                                    </th>
                                    <th>
                                        <?= _('GGR') ?>
                                    </th>
                                    <th>
                                        <?= _("Commission (NGR)"); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
		    foreach ($casinoCommissions as $commission):
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
                                            <td class="text-center">
                                                <?= $commission['created_at']; ?>
                                            </td>
                                            <td>
                                                <?= $commission['show_ggr'] ? $commission['ggr'] :'N\A'; ?>
                                            <?php
                                            
		        if (!empty($commission['ggrInUserCurrency'])):
		            ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?php
		                                echo $commission['ggrInUserCurrency'];
		            ?>"></span>
                                                        </small>
                                                <?php
		        endif;
		        ?>
                                            </td>
                                            <td>
                                                <?php
		                if (!empty($commission['show_commission'])):
		                    echo $commission['commission'];

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