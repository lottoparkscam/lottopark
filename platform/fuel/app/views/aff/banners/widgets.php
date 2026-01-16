<?php // include("menu.php");?>
<div class="row dashboard">
    <div class="col-md-12 banners-generator widgets-generator">
        <div class="panel panel-default">
            <div class="panel-heading">
                <?= _("Generate your widget code"); ?>
            </div>
            
            <div class="panel-body">
                <div class="col-md-6">    
                    <?php
                        if (isset($this->errors)):
                    ?>
                            <div class="alert alert-danger" role="alert">
                                <?php
                                    foreach ($errors as $error):
                                        echo '<p>' . $error . '</p>';
                                    endforeach;
                                ?>
                            </div>
                    <?php
                        endif;
                    ?>

                    <form method="post" 
                          action="javascript:();" 
                          id="widget_form" 
                          data-domain="<?= $whitelabel['domain']; ?>"
                          autocomplete="off">
                        <div id="widget-form-error"></div>
                        <div class="form-group">
                            <label for="banner_widget">
                                <?= _("Widget"); ?>:
                            </label>
                            <select name="widget_option" 
                                    id="widget_option" 
                                    class="form-control widget-options">
                                <option disabled selected>
                                    <?= _("Select type"); ?>
                                </option>
                                <?php
                                    foreach ($types as $key => $type):
                                ?>
                                        <option value="<?= $key; ?>" 
                                                data-lotteries="<?=$type['options']['lotteries'];?>" 
                                                <?= Input::post("widget_option") == $key ? ' selected="selected"' : ''; ?>>
                                            Type #<?= Security::htmlentities($key); ?>
                                        </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div>
                        
                        <div id="additional-widget-options">
                            <?php
                                foreach ($types as $key => $type):
                            ?>
                                    <div class="form-group widget-subtypes collapse" 
                                         id="widget_option_<?= $key; ?>"<?= array_key_exists(Input::post("widget_option_".Input::post("widget_option")), $types[$key]['widgets']) ? ' style="display: block;"' : '' ?>>
                                        <label for="banner_widget">
                                            <?= _("Widget subtype"); ?>:
                                        </label>
                                        <select name="widget_option_<?= $key; ?>" 
                                                class="form-control">
                                            <?php
                                                foreach ($types[$key]['widgets'] as $key2 => $widget):
                                            ?>
                                                    <option value="<?= $key2; ?>" <?= Input::post("widget_option_".Input::post("widget_option")) == $key2 ? ' selected="selected"' : ''; ?>>
                                                        <?= Security::htmlentities($widget); ?>
                                                    </option>
                                            <?php
                                                endforeach;
                                            ?>
                                        </select>
                                    </div>
                            <?php
                                endforeach;
                            ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="widget_size">
                                <?= _("Widget size"); ?>:
                            </label>
                            <select name="widget_size" 
                                    id="widget_size" 
                                    class="form-control">
                                <option value="full">
                                    Full width
                                </option>
                                <option value="custom"<?= Input::post("widget_size") == 'custom' ? ' selected="selected"' : ''; ?>>
                                    Custom width
                                </option>
                            </select>
                        </div>
                        
                        <div id="additional-widget-width"<?= Input::post("widget_size") == 'custom' ? ' style="display: block;"' : ''; ?>>
                            <div class="form-group">
                                <label for="custom_width">
                                    <?= _("Custom width"); ?>:
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="custom_width" 
                                       name="custom_width" 
                                       placeholder="<?= _("Enter your custom width (in px)"); ?>" 
                                       value="<?= Security::htmlentities(null !== Input::post("custom_width") ? Input::post("custom_width") : ''); ?>">
                            </div>
                        </div>
                        
                        <div id="additional-widget-lotteries">
                            <?php
                                if (!empty($types[Input::post("widget_option")]['options']['lotteries'])) {
                                    $saved_lotteries = $types[Input::post("widget_option")]['options']['lotteries'];
                                } else {
                                    $saved_lotteries = 0;
                                }
                            
                                for ($i = 1; $i <= Banners_Widgets::$max_lotteries; $i++):
                            ?>
                                    <div class="form-group widget-lotteries collapse" 
                                         id="banner_lottery_<?= $i; ?>" <?= ($saved_lotteries >= $i) ? ' style="display: block;"' : '' ?>>
                                        <label for="banner_lottery">
                                             <?= _("Lottery"); ?> #<?= $i; ?>:
                                        </label>
                                        <select name="lottery<?= $i; ?>" 
                                                id="lottery<?= $i; ?>" 
                                                class="form-control">
                                            <?php
                                                foreach ($lotteries as $key => $lottery):
                                                    if (substr($key, 0, 2) == "__") {
                                                        continue;
                                                    }
                                            ?>
                                                    <option value="<?= $lottery['slug']; ?>"<?= Input::post("lottery".$i) == $lottery['slug'] ? ' selected="selected"' : ''; ?>>
                                                        <?= Security::htmlentities($lottery['name']); ?>
                                                    </option>
                                            <?php
                                                endforeach;
                                            ?>
                                        </select>
                                    </div>
                            <?php
                                endfor;
                            ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="widget_lang">
                                <?= _("Language"); ?>:
                            </label>
                            <select name="language" 
                                    id="widget_lang" 
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
                            <label for="medium">
                                <?= _("Medium"); ?>:
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="medium" 
                                   name="medium" 
                                   placeholder="<?= _("Enter medium"); ?>" 
                                   value="<?= Security::htmlentities(null !== Input::post("medium") ? Input::post("medium") : ''); ?>">
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
                                   value="<?= Security::htmlentities(null !== Input::post("campaign") ? Input::post("campaign") : ''); ?>">
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
                                   value="<?= Security::htmlentities(null !== Input::post("content") ? Input::post("content") : ''); ?>">
                            <p class="help-block">
                                <?= _("Used to differentiate similar content, or links within the same ad. For example, if you have two call-to-action links within the same email message, you can use <em>content</em> and set different values for each so you can tell which version is more effective."); ?>
                            </p>
                        </div>
                        
                        <input type="hidden" 
                               name="lottery_type" 
                               id="lottery_type" 
                               value="aff" />

                        <input type="hidden" 
                               name="ref" 
                               id="ref" 
                               value="<?= strtoupper($aff['token']); ?>" />
                    </form>
                </div>

                <div class="col-md-6" id="preview_widget" style="display:none;">
                    <div class="col-md-12" style="margin-left:0;padding-left:0;">
                        <label>
                            <?= _("Preview"); ?>:
                        </label>
                        <div class="row col-md-12">
                            <div id="widget-preview"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="link"><b><?= _("JS Script"); ?>:</b></label>
                        <br/>
                        <small>(<?= _("Put this in your " . htmlspecialchars('<head>') . " tag"); ?>)</small>
                        <textarea class="form-control" 
                                  rows="4" 
                                  id="widget_js" 
                                  readonly="readonly" 
                                  style="resize:none;"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="link"><b><?= _("Widget"); ?>:</b></label>
                        <br/>
                        <small>(<?= _("Put this where you want to show the widget"); ?>)</small>
                        <textarea class="form-control" 
                                  rows="4" 
                                  id="widget_div" 
                                  readonly="readonly" 
                                  style="resize:none;"></textarea>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

