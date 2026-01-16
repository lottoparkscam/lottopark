<?php
    include(APPPATH."views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php
            include(APPPATH . "views/whitelabel/settings/menu.php");
        ?>
    </div>
    <div class="col-md-10 banners-generator">
        <script src="/assets/js/tinymce/tinymce.min.js"></script>
        <script>tinymce.init({selector:'textarea.tinymce', plugins: "code"});</script>
        
        <div class="container-fluid container-admin row">
            <?php
                include(APPPATH . "views/whitelabel/shared/messages.php");
            ?>
            <div class="col-md-12">
                <h2><?= _("Edit email template"); ?></h2>
                <p class="help-block">
                    <?= _("Here you can edit your email template."); ?>
                </p>
            </div>
            <div class="col-md-12">
                <?php
                    if (isset($this->errors)) {
                        include(APPPATH . "views/whitelabel/shared/errors.php");
                    }
                ?>
                
                <form method="post" action="">
                    <div class="col-md-12 remove-sides-padding">
                        <div class="form-group col-md-6 remove-sides-padding">
                            <label for="campaign">
                                <?= _("Title"); ?>:
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="title" 
                                   name="title" 
                                   placeholder="<?= _("Enter title"); ?>" 
                                   value="<?= htmlspecialchars($mail['title']); ?>">
                            <p class="help-block remove-bottom-margin">
                                <?= _('<b>{$name}</b> - Whitelabel name'); ?>
                            </p>
                        </div>
                    </div>
                    <div class="row col-md-12">
                        <div class="form-group">
                            <label for="email_content">
                                <?= _("Content"); ?>:
                            </label>
                            <textarea class="form-control tinymce"
                                      name="content"
                                      rows="30"
                                      id="email_content"
                                      style="resize:none;"><?= htmlspecialchars($mail['content']); ?></textarea>
                        </div>
                    </div>

                    <div class="row col-md-12">
                        <div class="form-group">
                            <label for="email_content">
                                <?= _("Content of text version"); ?>:
                            </label>
                            <textarea class="form-control"
                                      name="text_content"
                                      rows="15"
                                      id="email_content"
                                      style="resize:none;width:100%;"><?= htmlspecialchars($mail['text_content']); ?></textarea>
                        </div>
                    </div>

                    <?php
                        foreach ($mail['additional_translates'] as $translate_key => $translate_value):
                    ?>
                            <div class="col-md-12 remove-sides-padding">
                                <div class="form-group col-md-6  remove-sides-padding">
                                    <label for="campaign">
                                        <?= $translate_value['label']; ?>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="title" 
                                           name="additional_translates[<?= $translate_key ;?>]" 
                                           value="<?= htmlspecialchars($translate_value['translation']); ?>">
                                </div>
                            </div>
                    <?php
                        endforeach;
                    ?>
                    
                    <div class="col-md-12 remove-sides-padding">
                        <div class="form-group">
                            <?= _('Available variables:'); ?><br/>
                            <?php
                                foreach ($variables as $var => $description):
                                    echo '<div><b>' . $var . '</b> - ' . $description . '</div>';
                                endforeach;
                            ?>
                        </div>
                        <div class="col-md-12 remove-sides-padding" id="email_preview_content"></div>

                        <input type="hidden" name="lottery_type" id="lottery_type" value="whitelabel" />
                        
                        <input type="hidden" name="lang_code" id="lang_code" value="<?= $mail_lang; ?>" />
                        
                        <button type="submit" name="submit" value="submit" class="btn btn-primary">
                            <?= _("Edit email"); ?>
                        </button>
                        <?php
                            if ((string)$mail['slug'] !== "template"):
                        ?>
                                <button type="button" 
                                        id="email_preview" 
                                        class="btn btn-success">
                                    <?= _("Preview"); ?>
                                </button>
                        <?php
                            endif;
                        ?>
                        <a href="/mailsettings/restore/<?= $mail['slug']; ?>/<?= $mail_lang; ?>" 
                           onclick="return confirm('<?= _("Are you sure?"); ?>')" 
                           class="btn btn-warning">
                            <?= _("Restore default"); ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
