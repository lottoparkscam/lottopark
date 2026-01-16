<?php 
    if ($lines_data !== null && count($lines_data) > 0):
?>
        <h3>
            <?= _("Lines"); ?>
        </h3>
<?php 
        foreach ($lines_data as $line_data):
            echo $line_data['draw_line'];

            if (!$line_data['draw']):
?>
                <br>
<?php
            else:
?>
                <strong>
                    <?= Security::htmlentities(_("Match")); ?>:
                </strong>
<?php
                echo $line_data['match'];

                if (isset($line_data['lottery_type_data_id'])):
?>
                    <strong>
                        <?= Security::htmlentities(_("Prize")); ?>:
                    </strong>
                    <span>
                        <?php 
                            echo $line_data['prize_manager'];

                            if (!empty($line_data['other_prizes'])):
                        ?>
                                <small>
                                    <span class="glyphicon glyphicon-info-sign" 
                                          data-toggle="tooltip" 
                                          data-placement="top" 
                                          title="" 
                                          data-original-title="<?= $line_data['other_prizes']; ?>">
                                    </span>
                                </small>
                        <?php
                            endif;
                        ?>
                    </span>
                    <strong>
                        <?= Security::htmlentities(_("Status")); ?>:
                    </strong>
                    <span>
                        <?php 
                            echo $line_data['payout'];

                            if (!empty($line_data['confirm_text'])):
                        ?>
                                <button type="button" 
                                        data-href="<?= $line_data['confirm_url']; ?>" 
                                        class="btn btn-xs btn-success btn-ml" 
                                        data-toggle="modal" 
                                        data-target="#confirmModal" 
                                        data-confirm="<?= _("Are you sure?"); ?>">
                                    <span class="glyphicon glyphicon-ok"></span>
                                    <?= $line_data['confirm_text']; ?>
                                </button>
                        <?php
                            endif;
                        ?>
                    </span>
                    <br>
<?php 
                else:
?>
                    <strong>
                        <?= Security::htmlentities(_("Status")); ?>:
                    </strong>
                    <span>
                        <?= Security::htmlentities(_("No winnings")); ?>
                    </span>
                    <br>
<?php 
                endif;
            endif;
        endforeach;
    endif;
