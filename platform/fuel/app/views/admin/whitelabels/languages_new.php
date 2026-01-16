<?php include(APPPATH."views/admin/shared/navbar.php"); ?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH."views/admin/whitelabels/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Languages"); ?> <small><?= $whitelabel['name']; ?></small>
        </h2>
        <p class="help-block">
            <?= _("Here you can add language for the whitelabel."); ?>
        </p>
        <a href="/whitelabels/languages/<?= $whitelabel['id']; ?>" 
           class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
                <form method="post" autocomplete="off" 
                      action="/whitelabels/languages/<?= $whitelabel['id']; ?>/new">
                    <?php
                        if (isset($this->errors)) {
                            include(APPPATH . "views/admin/shared/errors.php");
                        }
                        
                        $has_error_class = '';
                        if (isset($errors['input.language'])) {
                            $has_error_class = ' has-error';
                        }
                    ?>
                    <div class="form-group <?= $has_error_class; ?>">
                        <label class="control-label" for="inputLanguage">
                            <?= _("Language"); ?>:
                        </label>
                        <select autofocus required name="input[language]" 
                                id="inputLanguage" class="form-control">
                            <option value="0">
                                <?= _("Choose language"); ?>
                            </option>
                            <?php
                                foreach ($languages as $key => $language):
                                    $selected_text = '';
                                    if ((Input::post("input.language") !== null &&
                                            Input::post("input.language") == $key) ||
                                        (Input::post("input.language") === null &&
                                            isset($edit['language']) &&
                                            $edit['language'] == $key)
                                    ) {
                                        $selected_text = ' selected="selected"';
                                    }
                            ?>
                                    <option value="<?= $key; ?>" <?= $selected_text; ?>>
                                        <?= Security::htmlentities($language['code']); ?>
                                    </option>
                            <?php
                                endforeach;
                            ?>
                        </select>
                     </div>
                    <button type="submit" class="btn btn-primary">
                        <?= _("Submit"); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
