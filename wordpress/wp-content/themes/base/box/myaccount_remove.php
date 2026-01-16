<?php
if (!defined('WPINC')) {
    die;
}
?>

<form class="platform-form platform-form-profile profile-remove" 
      autocomplete="off" 
      method="post" 
      action=".">
    <input type="hidden" 
           name="<?= \Config::get('security.csrf_token_key'); ?>" 
           value="<?= \Security::fetch_token(); ?>" />
    <?php
        if (!empty(Input::post("myaccount_remove")) &&
            !empty($this->errors) &&
            count($this->errors) > 0
        ):
    ?>
            <div class="platform-alert platform-alert-error">
                <?php
                    foreach ($this->errors as $error):
                        echo '<p><span class="fa fa-exclamation-circle"></span> ' .
                            wp_kses($error, array("a" => array("href" => array()))).
                            '</p>';
                    endforeach;
                ?>
            </div>
    <?php
        endif;
    ?>
    
    <p>
        <?= _("Are you sure you want to delete your account? This operation cannot be undone."); ?>
    </p>
    <div class="profile-remove-password">
        <label for="myaccount-remove-confirmation-password">
            <?= _("Confirmation Password") ?>:
        </label>
        <input type="password" 
               class="form-control" 
               id="myaccount-remove-confirmation-password" 
               name="myaccount_remove[password]" 
               autocomplete="off">
    </div>
    <div class="dialog-buttons">
        <div class="pull-left">			
            <button type="button" 
                    class="btn btn-sm btn-tertiary myaccount-remove-dialog-close">
                <?= _("Cancel") ?>
            </button>
        </div>
        <div class="pull-right">
            <button type="submit" 
                    id="myaccount-remove-submit" 
                    class="btn btn-sm btn-primary disabled" disabled="disabled">
                <?= _("OK") ?>
            </button>
        </div>
        <div class="clearfix"></div>
    </div>
</form>

