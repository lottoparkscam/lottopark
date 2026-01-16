<?php // include("menu.php");?>
<div class="row dashboard">
    <div class="col-md-12 banners-generator">
        <div class="panel panel-default">
            <div class="panel-heading"><?= _("Generate your banner code"); ?></div>
            <div class="panel-body">
                <div class="col-md-6">    
                    <?php
                        if (isset($this->errors)):
                    ?>
                            <div class="alert alert-danger" role="alert">
                                <?php foreach ($errors as $error): ?>
                                    <?= '<p>'.$error.'</p>'; ?>
                                <?php endforeach; ?>
                            </div>
                    <?php
                        endif;
                    ?>

                    <form method="post" action="">
                        <div class="form-group">
                            <label for="banner_lottery">
                                <?= _("Lottery"); ?>:
                            </label>
                            <select name="lottery" 
                                    id="banner_lottery" 
                                    class="form-control">
                                <?php
                                    foreach ($lotteries as $key => $lottery):
                                        if (substr($key, 0, 2) == "__") {
                                            continue;
                                        }
                                ?>
                                        <option value="<?= $lottery['slug']; ?>"<?= Input::post("lottery") == $lottery['slug'] ? ' selected="selected"' : ''; ?>>
                                            <?= Security::htmlentities($lottery['name']); ?>
                                        </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="banner_lang">
                                <?= _("Language"); ?>:
                            </label>
                            <select name="language" 
                                    id="banner_lang" 
                                    class="form-control">
                                <?php
                                    foreach ($langs as $lang):
                                ?>
                                        <option value="<?= $lang['code'];?>"<?php if ((Input::post("language") !== null && Input::post("language") == $lang['code']) || (Input::post("language") === null && $whitelabel['language_id'] == $lang['id'])): echo ' selected="selected"'; endif; ?>>
                                            <?= Security::htmlentities(Lotto_View::format_language($lang['code'])); ?>
                                        </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="banner_size">
                                <?= _("Banner size"); ?>:
                            </label>
                            <select name="banner_size" 
                                    id="banner_size" 
                                    class="form-control">
                                <?php
                                    foreach ($sizes as $key => $size):
                                ?>
                                        <option value="<?= $key; ?>"<?= Input::post("banner_size") == $key     ? ' selected="selected"' : ''; ?>>
                                            <?= Security::htmlentities($size); ?>
                                        </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="color_type">
                                <?= _("Colors"); ?>:
                            </label>
                            <select name="color_type" 
                                    id="color_type" 
                                    class="form-control">
                                <?php
                                    foreach (Banners_Create::$allowed_colors as $type => $color):
                                ?>
                                        <option value="<?= $type; ?>"<?= Input::post("color_type") == $type ? ' selected="selected"' : ''; ?>>
                                            <?= _($color); ?>
                                        </option>
                                <?php
                                    endforeach;
                                ?>
                                <option value="" 
                                        id="lottery_color"<?= (Input::post("color_type") !== null && Input::post("color_type") == "") ? ' selected="selected"' : ''; ?>>
                                    <?= _('Lottery colors'); ?>
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="medium">
                                <?= _("Medium"); ?>:
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="medium" 
                                   name="medium" 
                                   placeholder="<?= _("Enter medium"); ?>" 
                                   value="<?= Security::htmlentities(null !== Input::post("medium") && empty($this->errors) ? Input::post("medium") : ''); ?>">
                            <p class="help-block">
                                <?= _("The advertising or marketing medium, for example: cpc, banner, email newsletter."); ?>
                            </p>
                        </div>
                        <div class="form-group">
                            <label for="campaign">
                                <?= _("Campaign"); ?>:
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="campaign" 
                                   name="campaign" 
                                   placeholder="<?= _("Enter campaign"); ?>" 
                                   value="<?= Security::htmlentities(null !== Input::post("campaign") && empty($this->errors) ? Input::post("campaign") : ''); ?>">
                            <p class="help-block">
                                <?= _("The individual campaign name, slogan, promo code, etc."); ?>
                            </p>
                        </div>
                        <div class="form-group">
                            <label for="content">
                                <?= _("Content"); ?>:
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="content" 
                                   name="content" 
                                   placeholder="<?= _("Enter content"); ?>" 
                                   value="<?= Security::htmlentities(null !== Input::post("content") && empty($this->errors) ? Input::post("content") : ''); ?>">
                            <p class="help-block">
                                <?= _("Used to differentiate similar content, or links within the same ad. For example, if you have two call-to-action links within the same email message, you can use <em>content</em> and set different values for each so you can tell which version is more effective."); ?>
                            </p>
                        </div>
                        <input type="hidden" 
                               name="lottery_type" 
                               id="lottery_type" 
                               value="aff" />
                        <button type="submit" 
                                name="submit" 
                                value="submit" 
                                class="btn btn-primary"><?= _("Generate link"); ?></button>
                    </form>
                </div>

                <div class="col-md-6">
                    <?php
                        if (isset($code)):
                    ?>
                            <div class="form-group">
                                <label for="link">
                                    <?= _("Generated link"); ?>:
                                </label>
                                <textarea class="form-control" 
                                          rows="4" 
                                          id="link" 
                                          readonly="readonly" 
                                          style="resize:none;"><?= $code; ?></textarea>
                            </div>

                            <div class="form-group">
                                <div>
                                    <b><?= _("Generated banner"); ?>:</b>
                                </div>
                                <div class="generated-banner">
                                    <img id="banner" 
                                         src="<?= $link; ?>" 
                                         style="max-width:100%;" />
                                </div>
                            </div>
                    <?php
                        endif;
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

