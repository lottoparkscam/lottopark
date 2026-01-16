<?php
$prizeBreakdown = Model_Lottery_Type_Data::get_keno_prize_breakdown_data($lottery_type['id']);
?>
<div class="clearfix"></div>
<div class="results-short-winnings-table-wrapper">
    <div id="winnings-table-multiplier-wrapper">
        <label for="winnings-table-multiplier"><?= _("Multiplier") ?>:</label>
        <select name="winnings-table-multiplier" id="winnings-table-multiplier" data-jackpot="<?php echo (isset($lottery['current_jackpot'])) ? $lottery['current_jackpot'] * 1000000: 0;?>">
            <?php foreach (Model_Lottery_Type_Multiplier::for_lottery($lottery['id']) as $multiplier): ?>
                <option value="<?= $multiplier['multiplier'] ?>"><?= $multiplier['multiplier'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="pull-left results-short-winnings-table"
         data-multiplier="1"
         data-currencycode="<?= htmlspecialchars($lottery['currency']); ?>"
    >
        <div class="table-container">
            <div class="table-parent grid-cols-12 grid-rows-8">
                <div class="table-content" id="winnings-table-content-subtable">
                    <div class="table-row">
                        <div class="table-separator"></div>
                        <?php foreach (array_keys($prizeBreakdown[key($prizeBreakdown)]) as $colHead): ?>
                            <div class="table-heading"><?= $colHead ?></div>
                        <?php endforeach; ?>
                    </div>
                    <?php foreach ($prizeBreakdown as $rowKey => $row): ?>
                        <div class="table-row">
                            <div class="table-heading"><?= $rowKey ?></div>
                            <?php foreach ($row as $cellKey => $cell): ?>
                                <div class="table-cell" data-value="<?= $cell ?>"><?= $cell ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="table-content" id="winnings-table-content-label-horizontal">
                    <span><?= _("Typed numbers") ?></span>
                </div>
                <div class="table-content" id="winnings-table-content-label-vertical"><span
                            class="sideways-lr"><?= _("Correctly picked numbers") ?></span></div>
                <div class="table-content" id="winnings-table-content-blinder"></div>
            </div>
        </div>
    </div>
</div>