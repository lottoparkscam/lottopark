<?php
    if (!defined('WPINC')) {
        die;
    }

    $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
    $aff_token = Helpers_General::user_aff_token();
    if (isset($aff_token)):
        $aff_reflink = 'https://' . $whitelabel['domain'] . '/?ref=' . strtoupper($aff_token);
        $whitelabel_link = '<a href="https://aff.' . $whitelabel['domain'] . '">https://aff.' . $whitelabel['domain'] . '</a>';
?>
            <div class="content-box">
                <section class="page-content myaccount-section">
                    <div class="myaccount-content">
                        <div class="myaccount-data myaccount-details">
                            <p>
                                <?php
                                    $aff_page = get_post(
    apply_filters(
                                                                                    'wpml_object_id',
                                                                                    lotto_platform_get_post_id_by_slug('account/affiliate'),
                                                                                    'page',
                                                                                    true
                                                                                )
);
                                    
                                    $aff_content = "";
                                    if (!empty($aff_page) && !empty($aff_page->post_content)) {
                                        $aff_content = apply_filters('the_content', $aff_page->post_content);
                                    } else {
                                        $aff_content = sprintf(_('Use this link to invite your friends. Your friends will be connected to your affiliate account (see more at %s)'), $whitelabel_link);
                                    }
                                    
                                    echo $aff_content;
                                ?>
                            </p>

                            <div class="platform-form">
                                <div class="refer-to-friend-link-wrapper" style="display: inline-block">
                                    <input id="myaccount-aff-link" type="text" value="<?= $aff_reflink ?>" class="form-control" readonly="true"/>
                                </div>
                                <div class="copy-to-clipboard-wrapper" style="display: inline-block">
                                    <button class="btn btn-primary" id="myaccount-afflink-copytoclipboard"><?= _('Copy to clipboard'); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
<?php
    endif;
?>