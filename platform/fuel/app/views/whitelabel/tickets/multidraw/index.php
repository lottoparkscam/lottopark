<?php

use Models\Whitelabel;

include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php
        include(APPPATH . "views/whitelabel/tickets/menu.php");
        ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Multi-draw Tickets"); ?>
        </h2>
        <p class="help-block">
            <?= _("Here you can view and manage users' multidraw tickets."); ?>
        </p>
        <?php
        include(APPPATH . "views/whitelabel/tickets/multidraw/index_filters.php");
        ?>
        <div class="container-fluid container-admin">
            <?php
            include(APPPATH . "views/whitelabel/shared/messages.php");

            if (isset($tickets_data) && count($tickets_data) > 0):
                $default_currency_code = Helpers_Currency::get_default_currency_code();
                echo $pages;
                ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered table-sort">
                        <thead>
                        <tr>
                            <th>
                                <?= _("Multidraw ID"); ?>
                            </th>
                            <th>
                                <?= _("User ID &bull; User Name"); ?>
                                <br>
                                <?= _("E-mail"); ?>
                            </th>
                            <th>
                                <?= _("Multidraw Tickets"); ?>
                            </th>
                            <th>
                                <?= _("First draw"); ?>
                            </th>
                            <th>
                                <?= _("Valid to draw"); ?>
                            </th>
                            <th>
                                <?= _("Current draw"); ?>
                            </th>
                            <th>
                                <?= _("Date"); ?>
                            </th>
                            <th class="text-center">
                                <?= _("Manage"); ?>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($tickets_data as $ticket_data):
                            ?>
                            <tr>
                                <td>
                                    <?= $ticket_data['multidraw_token']; ?>
                                    <br>
                                    <?= $ticket_data['lottery_name']; ?>
                                </td>
                                <td class="text-nowrap">
                                    <?php
                                    echo $ticket_data['user_token'];
                                    echo " &bull; ";
                                    echo $ticket_data['user_fullname'];
                                    echo "<br>";
                                    echo $ticket_data['user_email'];
                                    echo "<br>";
                                    /** @var Whitelabel $whitelabelModel */
                                    $whitelabelModel = Container::get('whitelabel');
                                    if ($whitelabelModel->loginForUserIsUsedDuringRegistration()) {
                                        echo $ticket_data['user_login'];
                                        echo "<br>";
                                    }

                                    ?>
                                </td>
                               
                                <td class="text-center">
                                    <?= $ticket_data['tickets']; ?>
                                </td>
                                <td class="text-center">
                                    <?= $ticket_data['first_draw']; ?>
                                </td>
                                <td class="text-center">
                                    <?= $ticket_data['valid_to_draw']; ?>
                                </td>
                                <td class="text-center">
                                    <?= $ticket_data['current_draw']; ?>
                                </td>
                                <td class="text-center">
                                    <?= $ticket_data['date']; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= $ticket_data['tickets_url']; ?>"
                                       class="btn btn-xs btn-warning">
                                        <span class="glyphicon glyphicon-list"></span>
                                        <?= _("View Tickets"); ?>
                                    </a>
<?php /*
                                    <!--<a href="/multidraw_tickets/cancellation/<?=$ticket_data['token'];?>"
                                       class="btn btn-xs btn-danger">
                                        <span class="glyphicon glyphicon-list"></span>
                                        <?= _("Multi-draw Cancellation"); ?>
                                    </a>--> */ ?>
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
                    <?= _("No tickets."); ?>
                </p>
            <?php
            endif;
            ?>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <?= _("Are you sure?"); ?>
                </h4>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <a href="#" id="confirmOK" class="btn btn-success">
                    <?= _("OK"); ?>
                </a>
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <?= _("Cancel"); ?>
                </button>
            </div>
        </div>
    </div>
</div>