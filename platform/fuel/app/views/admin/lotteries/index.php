<?php
include(APPPATH . "views/admin/shared/navbar.php");

$draw_title_text = _(
    "Amount of numbers (range of numbers) + " .
    "Amount of bonus numbers (range of bonus numbers)&#10;" .
    "*Amount of extra numbers within the base range of draw numbers"
);

$modal_body_text = _(
    "Are you sure you want to disable this lottery?&#10;All bought users' " .
    "tickets will stay pending unless you provide the latest draw numbers manually."
);
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/admin/lotteries/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <div class="pull-right">
            <a href="/lotteries/logs" class="btn btn-primary btn-sm">
                <span class="glyphicon glyphicon-list"></span> <?= _("Show Logs"); ?>
            </a>
        </div>
        <h2>
            <?= _("Lotteries"); ?> <small><?= _("Lottery List"); ?></small>
        </h2>
        <p class="help-block">
            <?= _("This is an overview of the lotteries. Draws and Jackpots are automatically updated every 20 minutes."); ?>
        </p>
        <div class="container-fluid container-admin">
            <?php
            include(APPPATH . "views/admin/shared/messages.php");

            if ($lotteries != null && count($lotteries)):
                ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th><?= _("Name"); ?></th>
                            <th class="text-center"><?= _("Enabled"); ?></th>
                            <th class="text-center"><?= _("Source"); ?></th>
                            <th><?= _("Last Update"); ?></th>
                            <th class="text-center"><?= _("Current Jackpot"); ?></th>
                            <th>
                                <?= _("Draw Dates"); ?>
                                <br/>
                                <small><?= _("in lottery timezone"); ?></small>
                            </th>
                            <th class="text-center">
                                <?= _("Draw Rules"); ?> <span class="glyphicon glyphicon-info-sign"
                                                              data-toggle="tooltip"
                                                              data-placement="top"
                                                              title="<?= $draw_title_text; ?>">
                                        </span>
                            </th>
                            <th><?= _("Time Zone"); ?></th>
                            <th class="text-center"><?= _("Last (Next) Draw Date"); ?></th>
                            <th class="text-center"><?= _("Last Numbers"); ?></th>
                            <th class="text-center"><?= _("Last Bonus Numbers"); ?></th>
                            <th class="text-center"><?= _("Last Extra Numbers"); ?></th>
                            <th><?= _("Manage"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($lotteries as $item):
                            $tr_tag_class = '';
                            $td_tag_last_update_class = '';
                            $lottery_updated = \Carbon\Carbon::parse($item['last_update'])->diffInDays('now') >= 1;
                            if ($lottery_updated) {
                                if ((int)$item['is_enabled'] === 1) {
                                    $tr_tag_class = ' class="danger"';
                                    $td_tag_last_update_class = ' class="text-danger"';
                                } else {
                                    $tr_tag_class = ' class="warning"';
                                    $td_tag_last_update_class = ' class="text-warning"';
                                }
                            }

                            $item['name'] = Security::htmlentities($item['name']);
                            $item['is_lottery_enabled_class'] = Lotto_View::show_boolean_class($item['is_enabled']);
                            $item['is_lottery_enabled_icon'] = Lotto_View::show_boolean($item['is_enabled']);
                            $item['source_href'] = $sources[$item['source_id']][1];
                            $item['source_title'] = Security::htmlentities($sources[$item['source_id']][0]);
                            $item['last_update_formatted'] = Lotto_View::format_date(
                                $item['last_update'],
                                IntlDateFormatter::SHORT
                            );

                            $current_jackpot_text = '';
                            if ($item['current_jackpot'] == 0) {
                                $current_jackpot_text = _("pending");
                            } else {
                                $current_jackpot_multiplied = $item['current_jackpot'] * 1000000;
                                $current_jackpot_text = Lotto_View::format_currency(
                                    $current_jackpot_multiplied,
                                    $item['currency']
                                );
                            }
                            $item['current_jackpot_text'] = $current_jackpot_text;
                            $item['draw_dates'] = json_decode($item['draw_dates']);
                            $item['draw_days_text'] = "";
                            foreach ($item['draw_dates'] as $draw_date_key => $draw_date) {
                                if ($draw_date_key >= 5) {
                                    $item['draw_days_text'] .= "...";
                                    break;
                                }
                                $item['draw_days_text'] .= \Carbon\Carbon::parse($draw_date)->format("l H:i") . "<br/>"; // NOTE:: timezone is not needed since we only use this object to format
                            }

                            $lottery_type = Model_Lottery_Type::last_for_lottery($item['id']);

                            $draw_rules_text = intval($lottery_type['ncount']);
                            $draw_rules_text .= '(' . intval($lottery_type['nrange']) . ')';
                            if ((int)$lottery_type['bcount'] > 0) {
                                $draw_rules_text .= '+' . intval($lottery_type['bcount']);
                                $draw_rules_text .= '(' . intval($lottery_type['brange']) . ')';
                            }

                            if ((int)$lottery_type['bextra'] > 0) {
                                $draw_rules_text .= '*' . $lottery_type['bextra'];
                            }

                            $item['draw_rules_text'] = $draw_rules_text;

                            $time_zone_text = Lotto_View::format_time_zone(
                                $item['timezone'],
                                true
                            );
                            $item['time_zone_text'] = Security::htmlentities($time_zone_text);

                            $item['last_date_local_text'] = $item['last_date_local']
                                ? Lotto_View::format_date(
                                    $item['last_date_local'],
                                    IntlDateFormatter::MEDIUM,
                                    IntlDateFormatter::NONE
                                )
                                : "-";

                            $item['next_date_local_text'] = Lotto_View::format_date(
                                $item['next_date_local'],
                                IntlDateFormatter::MEDIUM,
                                IntlDateFormatter::NONE
                            );

                            $item['last_numbers_text'] = Lotto_View::format_numbers($item['last_numbers']);

                            $last_bonus_numbers_text = '&nbsp;';
                            if ((int)$lottery_type['bextra'] === 0) {
                                $last_bonus_numbers_text = Lotto_View::display_additional_numbers($item['last_bnumbers'], $item['additional_data']);
                            }
                            $item['last_bonus_numbers_text'] = $last_bonus_numbers_text;

                            $last_bonus_extra_numbers_text = '&nbsp;';
                            if ((int)$lottery_type['bextra'] > 0) {
                                $last_bonus_extra_numbers_text = Lotto_View::format_numbers($item['last_bnumbers']);
                            }
                            $item['last_bonus_extra_numbers_text'] = $last_bonus_extra_numbers_text;
                            ?>
                            <tr <?= $tr_tag_class; ?>>
                                <td>
                                    <?= $item['name']; ?>
                                </td>
                                <td class="text-center <?= $item['is_lottery_enabled_class']; ?>">
                                    <?= $item['is_lottery_enabled_icon']; ?>
                                </td>
                                <td class="text-center">
                                    <a target="_blank"
                                       href="<?= $item['source_href']; ?>"
                                       data-toggle="tooltip"
                                       data-placement="bottom"
                                       title="<?= $item['source_title']; ?>"><span
                                                class="glyphicon glyphicon-globe"></span>
                                    </a>
                                </td>
                                <td <?= $td_tag_last_update_class; ?>>
                                    <?= $item['last_update_formatted']; ?>
                                </td>
                                <td class="text-center text-nowrap">
                                    <?= $item['current_jackpot_text']; ?>
                                    <br>
                                    <a href="/lotteries/jackpot/<?= $item['id']; ?>"
                                       class="btn btn-xs btn-warning"><span
                                                class="glyphicon glyphicon-edit"></span> <?= _("Edit"); ?>
                                    </a>
                                </td>
                                <td>
                                    <?= $item['draw_days_text']; ?>
                                </td>
                                <td class="text-center">
                                    <?= $item['draw_rules_text']; ?>
                                </td>
                                <td>
                                    <?= $item['time_zone_text']; ?>
                                </td>
                                <td class="text-center text-nowrap">
                                    <?= $item['last_date_local_text']; ?>
                                    <br>(<?= $item['next_date_local_text']; ?>)
                                    <br>
                                    <a href="/lotteries/nextdraw/<?= $item['id']; ?>"
                                       class="btn btn-xs btn-warning">
                                        <span class="glyphicon glyphicon-edit"></span> <?= _("Next Draw"); ?>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <?= $item['last_numbers_text']; ?>
                                </td>
                                <td class="text-center">
                                    <?= $item['last_bonus_numbers_text']; ?>
                                </td>
                                <td class="text-center">
                                    <?= $item['last_bonus_extra_numbers_text']; ?>
                                </td>
                                <td>
                                    <a href="/lotteries/view/<?= $item['id']; ?>"
                                       class="btn btn-xs btn-primary">
                                        <span class="glyphicon glyphicon-list"></span> <?= _("View Draws"); ?>
                                    </a>
                                    <a href="/lotteries/source/<?= $item['id']; ?>"
                                       class="btn btn-xs btn-warning">
                                        <span class="glyphicon glyphicon-globe"></span> <?= _("Source"); ?>
                                    </a>
                                    <?php
                                    if ($item['is_enabled']):
                                        ?>
                                        <button type="button"
                                                class="btn btn-xs btn-danger"
                                                data-id="<?= $item['id']; ?>"
                                                data-toggle="modal"
                                                data-target="#confirmswitchModal">
                                            <span class="glyphicon glyphicon-remove"></span> <?= _("Disable"); ?>
                                        </button>
                                    <?php
                                    else:
                                        ?>
                                        <a href="/lotteries/switch/<?= $item['id']; ?>"
                                           class="btn btn-xs btn-success">
                                            <span class="glyphicon glyphicon-ok"></span> <?= _("Enable"); ?>
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
            else:
                ?>
                <p class="text-info">
                    <?= _("There are no lotteries."); ?>
                </p>
            <?php
            endif;
            ?>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmswitchModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <?= _("Confirm lottery switch off"); ?>
                </h4>
            </div>
            <div class="modal-body">
                <?= $modal_body_text; ?>
            </div>
            <div class="modal-footer">
                <a href="#" id="confirmswitchA" class="btn btn-warning">
                    <?= _("Disable"); ?>
                </a>
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <?= _("Cancel"); ?>
                </button>
            </div>
        </div>
    </div>
</div>