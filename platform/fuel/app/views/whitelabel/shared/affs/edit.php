<?php
// shared between new and edit
// 22.03.2019 11:31 Vordis TODO: new is a little different than edit - doesn't need get_checked and get_selected
?>

<div class="form-group">
    <label class="control-label" for="input[lead_lifetime]">
        <?= _("Lead lifetime") ?>:
    </label>
    <select name="input[lead_lifetime]" id="inputLeadlifetime" class="form-control">
        <?php foreach ($lead_lifetimes AS $key => $lead_lifetime): ?>
            <option value="<?= $key ?>" <?= $post_selected_extended("input.lead_lifetime", $key, $user['aff_lead_lifetime'] ?? '') ?>><?= $lead_lifetime ?></option>
        <?php endforeach; ?>
    </select>
    <p class="help-block"><?= _("Choose how long the affiliate should receive commissions from his leads.") ?></p>
</div>

<?php
// NOTE: for $post_checked_extended('input.is_show_name', $user['is_show_name'] ?? true)
// ?? true is used to set checked on default (if it not exist)
?>
<div class="checkbox">
    <label>
        <input type="checkbox" name="input[is_show_name]"
               value="1" <?= $post_checked_extended('input.is_show_name', $user['is_show_name'] ?? false) ?>>
        <?= _("Show name and surname of the leads."); ?>
    </label>
</div>

<div class="checkbox">
    <label>
        <input type="checkbox" name="input[hide_lead_id]"
               value="1" <?= $post_checked_extended('input.hide_lead_id', $user['hide_lead_id'] ?? true) ?>>
        <?= _("Hide lead IDs."); ?>
    </label>
</div>

<div class="checkbox">
    <label>
        <input type="checkbox" name="input[hide_transaction_id]"
               value="1" <?= $post_checked_extended('input.hide_transaction_id', $user['hide_transaction_id'] ?? true) ?>>
        <?= _("Hide transaction IDs."); ?>
    </label>
</div>

