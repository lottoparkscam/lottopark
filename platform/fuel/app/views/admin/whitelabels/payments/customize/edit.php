<?php
include(APPPATH . "views/admin/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/admin/whitelabels/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= $title; ?>
        </h2>
        
        <p class="help-block">
            <?= $main_help_block_text; ?>
        </p>
        
        <a href="<?= $urls['back']; ?>" 
           class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
                <form method="post" autocomplete="off" action="<?= $urls['add_edit']; ?>">
                    <?php
                        if (isset($this->errors)) {
                            include(APPPATH . "views/admin/shared/errors.php");
                        }
                    ?>
                    <div class="form-group <?= $error_class['language_id']; ?>">
                        <label class="control-label" for="inputLanguageId">
                            <?= _("Language"); ?>:
                        </label>
                        <div class="row">
                            <div class="col-md-3">
                                <select name="input[language_id]" 
                                        id="inputLanguageId" 
                                        class="form-control">
                                    <?php
                                        foreach ($languages_list as $single_language):
                                    ?>
                                            <option value="<?= $single_language['id']; ?>" 
                                                    <?= $single_language['selected']; ?> >
                                                <?= $single_language['show_text']; ?>
                                            </option>
                                    <?php
                                        endforeach;
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group <?= $error_class['title']; ?>">
                        <label class="control-label" for="inputTitle">
                            <?= _("Title"); ?>:
                        </label>
                        <input type="text" 
                               required="required" 
                               value="<?= $edit["title"]; ?>"
                               class="form-control"
                               id="inputTitle" 
                               name="input[title]"
                               maxlength="255"
                               placeholder="<?= _("Enter title"); ?>">
                    </div>
                    
                    <div class="form-group <?= $error_class['title_for_mobile']; ?>">
                        <label class="control-label" for="inputTitleForMobile">
                            <?= _("Title for mobile"); ?>:
                        </label>
                        <input type="text" 
                               required="required" 
                               value="<?= $edit["title_for_mobile"]; ?>"
                               class="form-control"
                               id="inputTitleForMobile" 
                               name="input[title_for_mobile]"
                               maxlength="255"
                               placeholder="<?= _("Enter title for mobile"); ?>">
                    </div>
                    
                    <div class="form-group <?= $error_class['title_in_description']; ?>">
                        <label class="control-label" for="inputTitleInDescription">
                            <?= _('Title in description area (first line of description - default "Pay using ...")'); ?>:
                        </label>
                        
                        <input type="text" 
                               value="<?= $edit["title_in_description"]; ?>"
                               class="form-control"
                               name="input[title_in_description]"
                               maxlength="255"
                               placeholder="<?= _("Enter title in description area"); ?>">
                    </div>
                    
                    <div class="form-group <?= $error_class['description']; ?>">
                        <label class="control-label" for="inputDescription">
                            <?= _("Description"); ?>:
                        </label>
                        <textarea style="width: 400px; resize: none;" 
                                  rows="5" 
                                  class="form-control" 
                                  id="inputDescription" 
                                  name="input[description]" 
                                  placeholder="<?= _("Enter description"); ?>"><?= $edit["description"]; ?></textarea>
                    </div>
                    
                    <div class="form-group <?= $error_class['additional_failure_text']; ?>">
                        <label class="control-label" for="inputAdditionalFailureText">
                            <?= _("Additional text on failure page"); ?>:
                        </label>
                        <textarea style="width: 400px; resize: none;" 
                                  rows="5" 
                                  class="form-control" 
                                  id="inputAdditionalFailureText" 
                                  name="input[additional_failure_text]" 
                                  placeholder="<?= _("Enter text on failure page"); ?>"><?= $edit["additional_failure_text"]; ?></textarea>
                    </div>
                    
                    <div class="form-group <?= $error_class['additional_success_text']; ?>">
                        <label class="control-label" for="inputAdditionalSuccessText">
                            <?= _("Additional text on success page"); ?>:
                        </label>
                        <textarea style="width: 400px; resize: none;" 
                                  rows="5" 
                                  class="form-control" 
                                  id="inputAdditionalSuccessText" 
                                  name="input[additional_success_text]" 
                                  placeholder="<?= _("Enter text on success page"); ?>"><?= $edit["additional_success_text"]; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <?= _("Submit"); ?>
                    </button>
                </form>
            </div>
        </div>
        
    </div>
</div>
