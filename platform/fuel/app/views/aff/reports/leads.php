<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/aff/reports/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("Leads"); ?>
        </h2>

		<p class="help-block">
            <?= _("Here you can view your latest leads."); ?>
        </p>
        
		<?php
            include(APPPATH . "views/aff/reports/leads_filters.php");
        ?>
        
		<div class="container-fluid container-admin">
		<?php 
            include(APPPATH . "views/aff/shared/messages.php");
            
            if (isset($regcount) && count($regcount) > 0):
                echo $pages;
        ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered table-sort">
                        <thead>
                            <tr>
                                <th>
                                    <?= _("Active"); ?>
                                </th>
                                <th>
                                    <?= _("Confirmed"); ?>
                                </th>
                                <th>
                                    <?= _("Expired"); ?>
                                </th>
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
                                ?>
                                <th>
                                    <?= _("Register country"); ?>
                                </th>
                                <th>
                                    <?= _("Last country"); ?>
                                </th>
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
                                    <?= _("Registered"); ?>
                                </th>
                                <th class="text-center">
                                    <?= _("First purchase"); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                foreach ($regcount as $key => $item):
                            ?>
                                    <tr>
                                        <td>
                                            <span class="<?= Lotto_View::show_boolean_class($item['is_active']); ?>">
                                                <?= Lotto_View::show_boolean($item['is_active']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="<?= Lotto_View::show_boolean_class($item['is_confirmed']); ?>">
                                                <?= Lotto_View::show_boolean($item['is_confirmed']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="<?= Lotto_View::show_boolean_class($item['is_expired']); ?>">
                                                <?= Lotto_View::show_boolean($item['is_expired']); ?>
                                            </span>
                                        </td>
                                        <?php 
                                            if ($is_lead_id_visible):
                                        ?>
                                                <td>
                                                    <?= Security::htmlentities($whitelabel['prefix'] . 'U' . $item['token']); ?>
                                                </td>
                                        <?php
                                            endif;
                                            
                                            if ($user['is_show_name']):
                                        ?>
                                                <td>
                                                    <?= $item['lead_name'] ?>
                                                </td>
                                        <?php 
                                            endif;
                                            
                                            if ($user['is_show_name']):
                                        ?>
                                                <td>
                                                    <?= $item['lead_email'] ?>
                                                </td>
                                        <?php 
                                            endif;
                                        ?>
                                        <td>
                                            <?php 
                                                if (!empty($item['register_country'])):
                                                    echo Security::htmlentities($countries[$item['register_country']] ?? '');
                                                endif;
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                                if (!empty($item['last_country'])):
                                                    echo Security::htmlentities($countries[$item['last_country']] ?? '');
                                                endif;
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                                if (!empty($item['medium'])):
                                                    echo Security::htmlentities($item['medium']);
                                                endif;
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                                if (!empty($item['campaign'])):
                                                    echo Security::htmlentities($item['campaign']);
                                                endif;
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                                if (!empty($item['content'])):
                                                    echo Security::htmlentities($item['content']);
                                                endif;
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?= Lotto_View::format_date($item['date_register'], IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT); ?>
                                        </td>
                                        <td class="text-center">
                                            <?= !empty($item['first_purchase']) ? Lotto_View::format_date($item['first_purchase'], IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT) : ''; ?>
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
                    <?= _("No leads."); ?>
                </p>
		<?php 
            endif;
        ?>
		</div>
	</div>
</div>