<?php 
    include(APPPATH . "views/whitelabel/shared/navbar.php");

    $aff_activation_types = Helpers_General::get_activation_types();
?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/whitelabel/affs/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2><?= _("Affiliate settings"); ?></h2>
		<p class="help-block"><?= _("You can adjust affiliate settings here."); ?></p>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
                <?php include(APPPATH . "views/aff/shared/messages.php"); ?>
				<form method="post" action="/affs/settings">
                    <?php 
                        if (isset($this->errors)) {
                            include(APPPATH . "views/whitelabel/shared/errors.php");
                        }
                    ?>
                    
                    <div class="form-group <?php if (isset($errors['input.activation_type'])): echo ' has-error'; endif; ?>">
                        <label class="control-label" for="inputType">
                            <?= _("Activation Type"); ?>:
                        </label>
                        <select required name="input[activation_type]" 
                                id="inputType" 
                                class="form-control">
                            <?php
                                foreach ($aff_activation_types as $activation_key => $activation_value):
                            ?>
                                    <option value="<?= $activation_key; ?>" 
                                            <?= $get_selected_extended("input.activation_type", $activation_key, $whitelabel['aff_activation_type']) ?>>
                                        <?= $activation_value; ?>
                                    </option>
                            <?php
                                endforeach;
                            ?>
                        </select>
                        <p class="help-block">
                            <?= $activation_help_block_text; ?>
                        </p>
                    </div>
                    
                    <div class="checkbox">
                      <label>
                          <input type="checkbox" 
                                 name="input[auto_accept]" 
                                 value="1" 
                                 <?= $get_checked_extended('input.auto_accept', $whitelabel['aff_auto_accept']); ?>>
                          <?= _("Automatically accept new affiliates"); ?>
                      </label>
                    </div>
                    
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" 
                                   name="input[payouttype]" 
                                   value="1" 
                                   <?= $get_checked_extended('input.payouttype', $whitelabel['aff_payout_type']) ?>>
                            <?= _("Automatically payout commissions"); ?>
                        </label>
                    </div>
                    
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" 
                                   name="input[leadautoaccept]" 
                                   value="1" 
                                   <?= $get_checked_extended('input.leadautoaccept', $whitelabel['aff_lead_auto_accept']) ?>>
                            <?= _("Automatically accept new affiliate leads (registrations)"); ?>
                        </label>
                    </div>
                    
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" 
                                   name="input[hide_ticket_and_payment_cost]" 
                                   value="1" 
                                   <?= $get_checked_extended('input.hide_ticket_and_payment_cost ', $whitelabel['aff_hide_ticket_and_payment_cost']) ?>>
                            <?= _("Hide Ticket cost and Payment cost fields for affiliates"); ?>
                        </label>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" 
                                   name="input[hide_amount]" 
                                   value="1" 
                                   <?= $get_checked_extended('input.hide_amount', $whitelabel['aff_hide_amount']) ?>>
                            <?= _("Hide Amount field for affiliates"); ?>
                        </label>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" 
                                   name="input[hide_income]" 
                                   value="1" 
                                   <?= $get_checked_extended('input.hide_income', $whitelabel['aff_hide_income']) ?>>
                            <?= _("Hide Income field for affiliates"); ?>
                        </label>
                    </div>
                    
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" 
                                   name="input[enable_sign_ups]" 
                                   id="enable_sign_ups" 
                                   value="1" 
                                   <?= $get_checked_extended('input.enable_sign_ups', $whitelabel['aff_enable_sign_ups']) ?>>
                            <?= _("Enable sign-ups"); ?>
                        </label>
                        <p class="help-block">
                            <?= $registration_url_help_block; ?>
                        </p>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" 
                                   name="input[disable_sign_ups_without_ref]" 
                                   id="disable_sign_ups_without_ref" 
                                   value="1" 
                                   <?= $get_checked_extended('input.disable_sign_ups_without_ref', $whitelabel['user_registration_through_ref_only']) ?>>
                            <?= _("Disable user sign-ups without affiliate link"); ?>
                        </label>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" 
                                   name="input[auto_new_user_aff]" 
                                   id="auto_new_user_aff" 
                                   value="1" 
                                   <?= $get_checked_extended('input.auto_new_user_aff', $whitelabel['aff_auto_create_on_register']) ?>>
                            <?= _("Automatically create an affiliate account for new users"); ?>
                        </label>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox"
                                   name="input[aff_can_create_sub_affiliates]"
                                   id="aff_can_create_sub_affiliates"
                                   value="1"
                                <?= $get_checked_extended('input.aff_can_create_sub_affiliates', $whitelabel['aff_can_create_sub_affiliates']) ?>>
                            <?= _("The affiliate can create sub affiliates"); ?>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="input[reflifetime]">
                            <?= _("Ref Cookie Lifetime") ?>:
                        </label>
                        <select name="input[reflifetime]" 
                                id="inputLeadlifetime" 
                                class="form-control" 
                                type="number">
                            <?php 
                                foreach ($ref_lifetimes as $key => $ref_lifetime):
                            ?>
                                    <option value="<?= $key ?>" 
                                            <?= $get_selected_extended("input.reflifetime", $key, $whitelabel['aff_ref_lifetime']) ?>>
                                        <?= $ref_lifetime ?>
                                    </option>
                            <?php 
                                endforeach;
                            ?>
                        </select>
                        <p class="help-block">
                            <?= _("Choose how long the ref should be kept in cookie.") ?>
                        </p>
                    </div>
                    
                    
                    <button type="submit" name="input[submit]" value="submit" class="btn btn-primary">
                        <?= _("Submit"); ?>
                    </button>
				</form>
			</div>
        </div>
    </div>
</div>
