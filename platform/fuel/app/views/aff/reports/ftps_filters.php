<form class="form-inline form-filter" method="get" action="/<?= $action_full; ?>">
    <?php if ($is_subaff) : ?>
        <div class="form-group">
            <select name="filter[subaff]" id="filterSubaff" class="form-control filterSelect">
                <?php
                foreach ($subaffs as $key => $subaff) :
                ?>
                    <option value="<?= $key ?>" <?= $get_selected_extended("filter.subaff", $key, $subaff_id) ?>>
                        <?= $subaff ?>
                    </option>
                <?php
                endforeach;
                ?>
            </select>
        </div>
    <?php endif; ?>

    <div class="form-group">
        <select name="filter[medium]" id="filterMedium" class="form-control filterSelect">
            <option value="a">
                <?= _("Medium"); ?>
            </option>
            <?php
            if (!empty($mediums)) :
                foreach ($mediums as $key => $medium) :
                    $medium_selected = '';
                    if (
                        Input::get("filter.medium") !== null &&
                        Input::get("filter.medium") !== "a" &&
                        Input::get("filter.medium") == $key
                    ) {
                        $medium_selected = ' selected="selected"';
                    }
            ?>
                    <option value="<?= $key; ?>" <?= $medium_selected; ?>>
                        <?= Security::htmlentities($medium['medium']); ?>
                    </option>
            <?php
                endforeach;
            endif;
            ?>
        </select>
    </div>
    <div class="form-group">
        <select name="filter[campaign]" id="filterCampaign" class="form-control filterSelect">
            <option value="a">
                <?= _("Campaign"); ?>
            </option>
            <?php
            if (!empty($campaigns)) :
                foreach ($campaigns as $key => $campaign) :
                    $campaign_selected = '';
                    if (
                        Input::get("filter.campaign") !== null &&
                        Input::get("filter.campaign") !== "a" &&
                        Input::get("filter.campaign") == $key
                    ) {
                        $campaign_selected = ' selected="selected"';
                    }
            ?>
                    <option value="<?= $key; ?>" <?= $campaign_selected; ?>>
                        <?= Security::htmlentities($campaign['campaign']); ?>
                    </option>
            <?php
                endforeach;
            endif;
            ?>
        </select>
    </div>
    <div class="form-group">
        <select name="filter[content]" id="filterContent" class="form-control filterSelect">
            <option value="a">
                <?= _("Content"); ?>
            </option>
            <?php
            if (!empty($contents)) :
                foreach ($contents as $key => $content) :
                    $content_selected = '';
                    if (
                        Input::get("filter.content") !== null &&
                        Input::get("filter.content") !== "a" &&
                        Input::get("filter.content") == $key
                    ) {
                        $content_selected = ' selected="selected"';
                    }
            ?>
                    <option value="<?= $key; ?>" <?= $content_selected; ?>>
                        <?= Security::htmlentities($content['content']); ?>
                    </option>
            <?php
                endforeach;
            endif;
            ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">
        <?= _("Filter"); ?>
    </button>
    <button type="reset" class="btn btn-success" id="filter-form-whitelabel">
        <?= _("Reset"); ?>
    </button>
</form>