<?php
if (!defined('WPINC')) {
    die;
}

get_header();

echo lotto_platform_messages(true, true);

get_template_part('content', 'login-register-box-mobile');
?>
<div class="content-area">
    <div class="main-width">
        <?php
            if (function_exists("lotto_platform_myaccount_nav")):
                echo lotto_platform_myaccount_nav();
            endif;
        ?>
    </div>
    <div class="main-width content-width">
        <?php
            if (get_query_var("section") != "referafriend") {
                get_template_part('content', 'aff-ref');
            }
        ?>
        <div class="content-box">
            <section class="page-content myaccount-section">
                <div class="myaccount-inside">
                    <?php
                        if (!(get_query_var("section") == "transactions" &&
                                get_query_var("action") == "details") &&
                            !(get_query_var("section") == "tickets" &&
                                get_query_var("action") == "details")
                        ):
                            $subtitle = Lotto_Settings::getInstance()->get('subtitle');
                    ?>
                            <article class="page">
                                <h1 class="account">
                                    <?php
                                        the_title();
                                        if (!empty($subtitle)):
                                            echo ' - ' . Security::htmlentities($subtitle);
                                        endif;
                                    ?>
                                </h1>
                                <?php
                                    if (empty(get_query_var("section"))):
                                ?>
                                        <a href="<?php echo lotto_platform_get_permalink_by_slug('account'); ?>profile/" 
                                           class="btn btn-primary">
                                            <?php echo Security::htmlentities(_("Edit profile")); ?>
                                        </a>
                                <?php
                                    endif;
                                    
                                  if (get_query_var("section") == 'transactions' &&
                                      empty(get_query_var("action")) &&
                                      !IS_CASINO
                                  ):
                                ?>
                                        <div class="myaccount-filter">
                                             <form method="get" action=".">
                                                 <label for="myaccount-filter-select" class="table-sort-label hidden-normal">
                                                     <?php echo Security::htmlentities(_("Show")); ?>: 
                                                 </label>
                                                 <select id="myaccount-filter-select" class="myaccount-filter-select" name="filter[status]">
                                                     <option value="a"<?php if (Input::get("filter.status") == "a" || Input::get("filter.status") == null): echo ' selected="selected"'; endif; ?>>
                                                         <?php echo Security::htmlentities(_("show all")); ?>
                                                     </option>
                                                     <option value="1"<?php if (Input::get("filter.status") == "1"): echo ' selected="selected"'; endif; ?>>
                                                         <?php echo Security::htmlentities(_("show approved")); ?>
                                                     </option>
                                                     <option value="0"<?php if (Input::get("filter.status") == "0"): echo ' selected="selected"'; endif; ?>>
                                                         <?php echo Security::htmlentities(_("show pending")); ?>
                                                     </option>
                                                     <option value="2"<?php if (Input::get("filter.status") == "2"): echo ' selected="selected"'; endif; ?>>
                                                         <?php echo Security::htmlentities(_("show failure")); ?>
                                                     </option>
                                                 </select>
                                             </form>
                                         </div>
                                         <div class="clearfix"></div>
                                <?php
                                    endif;
                                    
                                    the_content();
                                ?>
                            </article>
                    <?php
                        endif;

                        if (function_exists("lotto_platform_myaccount_box")):
                            echo lotto_platform_myaccount_box();
                        endif;
                    ?>
                </div>
                <div class="clearfix"></div>
            </section>
        </div>
    </div>
</div>
<?php
get_footer();
