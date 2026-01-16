<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/aff/reports/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("First-Time Purchases"); ?>
        </h2>
        
		<p class="help-block">
            <?= _("Here you can view the latest first-time purchases."); ?>
            <br>
            <?= _('Notice: Casino campaigns does not support FTP.'); ?>
        </p>
        
		<?php
            include(APPPATH . "views/aff/reports/ftps_filters.php");
        ?>
        
		<div class="container-fluid container-admin">
            <?php 
                include(APPPATH . "views/aff/shared/messages.php");

                if ($ftpcount !== null && count($ftpcount) > 0):
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
                                    foreach ($ftpcount as $key => $item):
                                ?>
                                        <tr>
                                            <?php 
                                                if ($is_lead_id_visible):
                                            ?>
                                                <td>
                                                    <?= Security::htmlentities($whitelabel['prefix'].'U'.$item['token']); ?>
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
                        <?= _("No first-time purchases."); ?>
                    </p>
            <?php 
                endif;
            ?>
		
		</div>
	</div>
</div>