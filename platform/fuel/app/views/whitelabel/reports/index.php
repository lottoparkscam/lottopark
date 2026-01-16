<?php 
    include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php 
            include(APPPATH."views/whitelabel/reports/menu.php");
        ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("Winners"); ?>
        </h2>
        
		<p class="help-block">
            <?= _("Here you can view information about draw winners."); ?>
        </p>
        
		<div class="container-fluid container-admin">
            <?php 
                include(APPPATH . "views/whitelabel/shared/messages.php");

                if (isset($winners) && count($winners) > 0):
                    echo $pages;
            ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered table-sort">
                            <thead>
                                <tr>
                                    <th>
                                        <?= _("Lottery"); ?>
                                    </th>
                                    <th>
                                        <?= _("Date Processed"); ?>
                                    </th>
                                    <th>
                                        <?= _("Draw Date"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Total Winners"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Total Prizes"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Site Winners (Tickets/Lines)"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Site Prizes"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Paid Out Tickets/Lines"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Awaiting Tickets/Lines"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Site No-Winners (Tickets/Lines)"); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    foreach ($winners as $winner):
                                ?>
                                        <tr>
                                            <td>
                                                <?= _($winner['name']); ?>
                                            </td>
                                            <td>
                                                <?= $winner['date_download']; ?>
                                            </td>
                                            <td>
                                                <?= $winner['date_local']; ?>
                                            </td>
                                            <td class="text-center">
                                                <?= $winner['total_winners']; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php 
                                                    echo $winner['total_prize'];
                                                    echo $winner['total_jackpot_winners'];
                                                    echo $winner['total_quickpick_winners'];
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <?php 
                                                    echo $winner['site_ticket_winners'];
                                                    echo "/";
                                                    echo $winner['site_winners'];
                                                    
                                                    if ($winner['show_ticket_winners_button']):
                                                        echo "<br>";
                                                ?>
                                                        <a href="<?= $winner['ticket_winners_button_url']; ?>" 
                                                           class="btn btn-xs btn-primary">
                                                            <span class="glyphicon glyphicon-list"></span> <?= _("View tickets"); ?>
                                                        </a>
                                                <?php 
                                                    endif;
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <?php 
                                                    echo $winner['site_prizes_manager'];

                                                    if (!empty($winner['site_prizes_others'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?php 
                                                                    echo $winner['site_prizes_others'];
                                                            ?>"></span>
                                                        </small>
                                                <?php 
                                                    endif;
                                                    
                                                    echo $winner['site_jackpot_winners'];
                                                    echo $winner['site_quickpick_winners'];
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <?php 
                                                    echo $winner['payout_ticket_count'];
                                                    echo "/";
                                                    echo $winner['payout_count'];
                                                    echo "<br>";
                                                    
                                                    echo "(";
                                                    
                                                    echo $winner['payout_sum_manager'];
                                                    
                                                    if (!empty($winner['payout_sum_others'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?php 
                                                                    echo $winner['payout_sum_others'];
                                                            ?>"></span>
                                                        </small>
                                                <?php 
                                                    endif;
                                                    
                                                    echo ")";
                                                
                                                    if ($winner['show_ticket_payout_button']):
                                                        echo "<br>";
                                                ?>
                                                        <a href="<?= $winner['ticket_payout_button_url']; ?>" 
                                                           class="btn btn-xs btn-primary">
                                                            <span class="glyphicon glyphicon-list"></span> <?= _("View tickets"); ?>
                                                        </a>
                                                <?php 
                                                    endif;
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <?php 
                                                    echo $winner['npayout_ticket_count'];
                                                    echo "/";
                                                    echo $winner['npayout_count'];
                                                    echo "<br>";
                                                    
                                                    echo "(";
                                                    
                                                    echo $winner['npayout_sum_manager'];
                                                    
                                                    if (!empty($winner['npayout_sum_others'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?php 
                                                                    echo $winner['npayout_sum_others'];
                                                            ?>"></span>
                                                        </small>
                                                <?php 
                                                    endif;
                                                    
                                                    echo ")";
                                                    
                                                    if ($winner['show_ticket_npayout_button']):
                                                        echo "<br>";
                                                ?>
                                                        <a href="<?= $winner['ticket_npayout_button_url']; ?>" 
                                                           class="btn btn-xs btn-primary">
                                                            <span class="glyphicon glyphicon-list"></span> <?= _("View tickets"); ?>
                                                        </a>
                                                <?php 
                                                    endif;
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <?php 
                                                    echo $winner['ticket_nowinners'];
                                                    echo "/";
                                                    echo $winner['nowinners'];
                                                
                                                    if ($winner['show_ticket_nowinners_button']):
                                                        echo "<br>";
                                                ?>
                                                        <a href="<?= $winner['ticket_nowinners_button_url']; ?>" 
                                                           class="btn btn-xs btn-primary">
                                                            <span class="glyphicon glyphicon-list"></span> <?= _("View tickets"); ?>
                                                        </a>
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
                        <?= _("No winners data."); ?>
                    </p>
            <?php 
                endif;
            ?>
		</div>
	</div>
</div>