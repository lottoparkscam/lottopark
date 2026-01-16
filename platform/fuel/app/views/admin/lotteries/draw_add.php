<?php include(APPPATH . "views/admin/shared/navbar.php"); ?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/admin/lotteries/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2><?= _("Add Draw"); ?>
            <small><?= Security::htmlentities($lottery['name']); ?></small>
        </h2>
        <p class="help-block">
            <span class="text-warning">
                <?php
                $help_block = _("You can manually add a lottery draw here. " .
                    "You should only do this when automatic update is not available!<br>" .
                    "All users' tickets will be processed according to the added draw data! " .
                    "This operation cannot be undone!");
                echo $help_block;
                ?>
            </span>
        </p>
        <a href="/lotteries/view/<?= $lottery['id']; ?>/s/<?= $params['page']; ?>" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
                <form method="post" action="/lotteries/view/<?= $lottery['id']; ?>/add">
                    <?php
                    if (isset($this->errors)) {
                        include(APPPATH . "views/admin/shared/errors.php");
                    }
                    ?>
                    <div class="row">
                        <label class="col-md-12 control-label" for="inputDate"><?= _("Date"); ?></label>
                        <div class="col-md-6">
                            <div class="form-group<?php if (isset($errors['input.date'])): echo ' has-error'; endif; ?>">
                                <input type="text"
                                       value="<?= Security::htmlentities(!empty(Input::post("input.date")) ? Input::post("input.date") : ''); ?>"
                                       required="required" class="form-control datepicker"
                                       id="inputDate"<?= isset($last_draw) ? ' data-date-start-date="' . $last_draw . '"' : ''; ?>
                                       data-date-max-view-mode="0"
                                       data-date-days-of-week-disabled="[<?= Lotto_View::get_disabled_days_of_week($lottery); ?>]"
                                       data-date-days-of-week-highlighted="[<?= Lotto_View::get_highlighted_days_of_week($lottery); ?>]"
                                       data-date-end-date="0d"
                                       data-date-week-start="<?= Lotto_View::get_first_day_of_week(); ?>"
                                       name="input[date]"
                                       placeholder="<?= _("Enter date"); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="time" required="required" class="form-control" name="input[time]"
                                       placeholder="<?= _("Time of the draw"); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group<?php if (isset($errors['input.jackpot'])): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputJackpot"><?= _("Next Jackpot"); ?></label>
                        <input type="text" required="required"
                               value="<?= Security::htmlentities(!empty(Input::post("input.jackpot")) ? Input::post("input.jackpot") : ''); ?>"
                               class="form-control" id="inputJackpot" name="input[jackpot]"
                               placeholder="<?= _("Enter next jackpot"); ?>">
                        <p class="help-block">
                            <?php
                            $jackpot_block = _("You should add a draw only if the next jackpot is already known." .
                                "<br>Unit: million (e.g. type '29.5' for 29.5 million jackpot). " .
                                "Use dot for decimal digits. <br>Currency: %s<br>Jackpot for this draw: %s");
                            $currency_code = Helpers_Currency::get_system_currency_code();
                            if (!empty($currencies) && !empty($lottery) &&
                                !empty($lottery['currency_id']) &&
                                !empty($currencies[$lottery['currency_id']]['code'])
                            ) {
                                $currency_code = $currencies[$lottery['currency_id']]['code'];
                            }
                            $current_jackpot = 1;
                            if (!empty($lottery['current_jackpot'])) {
                                $current_jackpot = $lottery['current_jackpot'];
                            }
                            $current_jackpot *= 1000000;
                            $formatted_code = Lotto_View::format_currency_code($currency_code);
                            $formatted_currency = Lotto_View::format_currency($current_jackpot, $currency_code);

                            echo sprintf($jackpot_block, $formatted_code, $formatted_currency);
                            ?>
                        </p>
                    </div>
                    <div class="form-group form-nomargin-b<?php if (isset($error_number)): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputNumber1"><?= _("Draw Numbers"); ?></label>
                    </div>
                    <div class="row">
                        <?php
                        for ($i = 1; $i <= $lottery_type['ncount']; $i++):
                            ?>
                            <div class="col-md-2 form-group form-nomargin-b<?php
                            if (isset($errors['input.number.' . ($i - 1)]) ||
                                isset($errors['input.number'])
                            ):
                                echo ' has-error';
                            endif;
                            ?>">
                                <input type="text"
                                       value="<?= Security::htmlentities(!empty(Input::post("input.number." . ($i - 1))) ? Input::post("input.number." . ($i - 1)) : ''); ?>"
                                       required="required" class="form-control" id="inputNumber<?= $i; ?>"
                                       name="input[number][]" placeholder="<?= sprintf(_("#%s"), $i); ?>">
                            </div>
                        <?php
                        endfor;
                        ?>
                    </div>
                    <div class="form-group<?php if (isset($error_number)): echo ' has-error'; endif; ?>">
                        <p class="help-block"><?= sprintf(_("Range: %s"), '1-' . $lottery_type['nrange']); ?></p>
                    </div>
                    <?php
                    if ($lottery_type['bextra'] > 0 || $lottery_type['bcount'] > 0):
                        ?>
                        <div class="form-group form-nomargin-b<?php if (isset($error_bnumber)): echo ' has-error'; endif; ?>">
                            <label class="control-label"
                                   for="inputBNumber1"><?= $lottery_type['bextra'] ? _("Extra Number") : _("Bonus Numbers"); ?></label>
                        </div>
                        <div class="row">
                            <?php
                            for ($i = 1; $i <= ($lottery_type['bextra'] > 0 ? $lottery_type['bextra'] : $lottery_type['bcount']); $i++):
                                ?>
                                <div class="col-md-2 form-group form-nomargin-b<?php
                                if (isset($errors['input.bnumber.' . ($i - 1)]) ||
                                    isset($errors['input.bnumber'])
                                ):
                                    echo ' has-error';
                                endif;
                                ?>">
                                    <input type="text"
                                           value="<?= Security::htmlentities(!empty(Input::post("input.bnumber." . ($i - 1))) ? Input::post("input.bnumber." . ($i - 1)) : ''); ?>"
                                           required="required" class="form-control" id="inputBNumber<?= $i; ?>"
                                           name="input[bnumber][]" placeholder="<?= sprintf(_("#%s"), $i); ?>">
                                </div>
                            <?php
                            endfor;
                            ?>
                        </div>
                        <div class="form-group<?php if (isset($error_bnumber)): echo ' has-error'; endif; ?>">
                            <p class="help-block">
                                <?= sprintf(_("Range: %s"), $lottery_type['bextra'] > 0 ? '1-' . $lottery_type['nrange'] : '1-' . $lottery_type['brange']); ?>
                                :
                            </p>
                        </div>
                    <?php
                    endif;

                    if ($lottery_type['additional_data']):
                        $additional_data = unserialize($lottery_type['additional_data']);
                        if (isset($additional_data['refund']) &&
                            isset($additional_data['refund_min']) &&
                            isset($additional_data['refund_max'])
                        ):
                            ?>
                            <div class="form-group form-nomargin-b">
                                <label class="control-label"
                                       for="inputBNumber1"><?= _("Refund"); ?></label>
                            </div>
                            <div class="row">
                                <div class="col-md-2 form-group form-nomargin-b<?php if (isset($errors['input.refund'])): echo ' has-error'; endif; ?>">
                                    <input type="text"
                                           value="<?= Security::htmlentities(!empty(Input::post("input.refund"))); ?>"
                                           required="required" class="form-control" id="inputRefund"
                                           name="input[refund]" placeholder="#1">
                                </div>
                            </div>
                            <div class="form-group">
                                <p class="help-block">
                                    <?= sprintf(_("Range: %s"), "{$additional_data['refund_min']}-{$additional_data['refund_max']}"); ?>
                                    :
                                </p>
                            </div>
                        <?php
                        endif;
                    endif;

                    if ($lottery_type['additional_data']): //TODO: refactor
                        $additional_data = unserialize($lottery_type['additional_data']);
                        if (isset($additional_data['super']) &&
                            isset($additional_data['super_min']) &&
                            isset($additional_data['super_max'])
                        ):
                            ?>
                            <div class="form-group form-nomargin-b">
                                <label class="control-label"
                                       for="inputBNumber1"><?= _("Super"); ?></label>
                            </div>
                            <div class="row">
                                <div class="col-md-2 form-group form-nomargin-b<?php if (isset($errors['input.super'])): echo ' has-error'; endif; ?>">
                                    <input type="text"
                                           value="<?= Security::htmlentities(!empty(Input::post("input.super"))); ?>"
                                           required="required" class="form-control" id="inputSuper"
                                           name="input[super]" placeholder="#1">
                                </div>
                            </div>
                            <div class="form-group">
                                <p class="help-block">
                                    <?= sprintf(_("Range: %s"), "{$additional_data['super_min']}-{$additional_data['super_max']}"); ?>
                                    :
                                </p>
                            </div>
                        <?php
                        endif;
                    endif;
                    ?>
                    <h3>
                        <?= _("Draw winners"); ?>
                    </h3>
                    <p class="help-block">
                        <?= _("Type 0's in case of no winner/prize.<br>You should add a draw only if prize breakout is already known."); ?>
                    </p>
                    <?php
                    foreach ($types as $key => $type):
                        ?>
                        <h4>
                            <?= _("Match"); ?> <?= $type['match_n'];
                            if ($lottery_type['bcount'] > 0 || $lottery_type['bextra'] > 0):
                                if ($lottery_type['bextra'] == 0 ||
                                    ($lottery_type['bextra'] > 0 && $type['match_b'])):
                                    echo ' + ' . $type['match_b'];
                                endif;
                            endif;
                            ?>
                            :
                        </h4>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group<?php if (isset($errors['input.wcount.' . $key])): echo ' has-error'; endif; ?>">
                                    <label class="control-label"
                                           for="inputWCount<?= $key; ?>"><?= _("Winners count"); ?>
                                        :</label>
                                    <input type="text"
                                           value="<?= Security::htmlentities(!empty(Input::post("input.wcount." . ($key))) ? Input::post("input.wcount." . ($key)) : ''); ?>"
                                           required="required" class="form-control" id="inputWCount<?= $key; ?>"
                                           name="input[wcount][]" placeholder="<?= _("Winners count"); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group<?php if (isset($errors['input.prize.' . $key])): echo ' has-error'; endif; ?>">
                                    <label class="control-label"
                                           for="inputPrize<?= $key; ?>"><?= _("Prize per winner"); ?>
                                        (<?= Lotto_View::format_currency_code($currencies[$lottery['currency_id']]['code']); ?>
                                        ):</label>
                                    <?php if ($type['type'] == 2): ?>
                                        <br><?= _("Free Quick Pick"); ?>
                                    <?php else: ?>
                                        <input type="text"
                                               value="<?= Security::htmlentities(null !== Input::post("input.prize." . $key) ? Input::post("input.prize." . ($key)) : ($type['type'] == 0 && !$type['is_jackpot'] ? $type['prize'] : '')); ?>"
                                               required="required" class="form-control"
                                               id="inputPrize<?= $key; ?>" name="input[prize][]"
                                               placeholder="<?= _("Prize"); ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php
                    endforeach;
                    ?>
                    <button type="submit" class="btn btn-primary">
                        <?= _("Submit"); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
