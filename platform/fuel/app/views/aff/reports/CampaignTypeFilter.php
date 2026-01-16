<?php

use Fuel\Core\Input;
use Helpers\SanitizerHelper;

?>

<div class="form-group">
    <select name="filter[campaignType]" id="filterCampaignType" class="form-control filterSelect">
        <option value="all">
            <?= _("Source"); ?>
        </option>
        <?php
        $sources = [
            'lotteries',
            'casino'
        ];
        $campaignType = SanitizerHelper::sanitizeString(Input::get('filter.campaignType', 'all') ?? '');
        if (!empty($campaigns)) :
            foreach ($sources as $source) :
                $campaign_selected = '';
                if ($campaignType === $source) {
                    $campaign_selected = ' selected ';
                }
        ?>
                <option value="<?= $source ?>" <?= $campaign_selected; ?>>
                    <?= _($source) ?>
                </option>
        <?php
            endforeach;
        endif;
        ?>
    </select>
</div>