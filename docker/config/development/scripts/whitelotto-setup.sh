#!/bin/bash

chown -R www-data:www-data /var/www

source /var/www/.env

# set up Redis AUTH for CLI
export REDISCLI_AUTH=${REDIS_PASSWORD}

# clear redis
redis-cli -h ${REDIS_HOST} -n 0 FLUSHDB
redis-cli -h ${REDIS_HOST} -n 1 FLUSHDB

# download wordpress
sudo -H -u www-data wp core download --skip-content --version=${WORDPRESS_VERSION} --locale=${WORDPRESS_LOCALE} --path=${PROJECT_FOLDER}/wordpress --url=https://${MAIN_DOMAIN}

# wait for mysql to start
i=0
while ! mysqladmin ping -u${WORDPRESS_MYSQL_DATABASE} -p${WORDPRESS_MYSQL_PASSWORD} -hlocalhost --silent; do
    sleep 1
    echo "[$i] Waiting for MySQL to start..."
    ((i=i+1))
done

# install composer and packages
sudo -H -u www-data composer install --no-dev --prefer-dist -d ${PROJECT_FOLDER}/platform

# prepare platform
cd ${PROJECT_FOLDER}/platform/
sudo -H -u www-data env php oil r migration:fresh

echo "Seeding database..."
sudo -H -u www-data env IS_DOCKER=1 WHITELABEL_DOMAINS=${WHITELABEL_DOMAINS} WORDPRESS_MAINSITE_TITLE="${WORDPRESS_MAINSITE_TITLE}" WORDPRESS_MAINSITE_THEME=${WORDPRESS_MAINSITE_THEME} LANGUAGE=${WORDPRESS_ADDITIONAL_LANGUAGE} php oil r seed

echo "Updating draw data..."
sudo -H -u www-data env php oil r lottery:update_draw_data

echo "Updating currency data..."
sudo -H -u www-data env wget -q --spider -4 https://empire.${MAIN_DOMAIN}/task/updatecurrencies/

# clear redis
redis-cli -h ${REDIS_HOST} -n 0 FLUSHDB
redis-cli -h ${REDIS_HOST} -n 1 FLUSHDB

# configure wordpress
sudo -H -u www-data wp config create --dbname=${WORDPRESS_MYSQL_DATABASE} --dbuser=${WORDPRESS_MYSQL_USER} --dbpass=${WORDPRESS_MYSQL_PASSWORD} --dbhost=${WORDPRESS_MYSQL_HOST} --dbcharset=${WORDPRESS_MYSQL_CHARSET} --dbcollate=${WORDPRESS_MYSQL_COLLATION} --path=${PROJECT_FOLDER}/wordpress --url=https://${MAIN_DOMAIN} --extra-php <<PHP
define('COOKIE_DOMAIN', str_replace("www.", "", \$_SERVER['HTTP_HOST']));
PHP

# install wordpress
sudo -H -u www-data wp core multisite-install --subdomains --url=https://${MAIN_DOMAIN} --title="${WORDPRESS_MULTISITE_TITLE}" --admin_user=${WORDPRESS_ADMIN_USER} --admin_password=${WORDPRESS_ADMIN_PASSWORD} --admin_email=${WORDPRESS_ADMIN_EMAIL} --skip-email --path=${PROJECT_FOLDER}/wordpress

# enable lotto-platform plugin network-wide
sudo -H -u www-data wp plugin activate lotto-platform --network --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress

# enable & activate whitelotto-2019 theme on whitelotto.loc
sudo -H -u www-data wp theme enable whitelotto-2019 --activate --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress

# install WPML plugins
sudo -H -u www-data wget --user ${DEVPUBLIC_USER} --password ${DEVPUBLIC_PASSWORD} -P ${PROJECT_FOLDER}/wordpress/wp-content/plugins/ --no-verbose https://devpublic.gginternational.work/wordpress_plugins/sitepress-multilingual-cms.${WORDPRESS_PLUGIN_SITEPRESS_VERSION}.zip
sudo -H -u www-data unzip -q ${PROJECT_FOLDER}/wordpress/wp-content/plugins/sitepress-multilingual-cms.${WORDPRESS_PLUGIN_SITEPRESS_VERSION}.zip -d ${PROJECT_FOLDER}/wordpress/wp-content/plugins/
sudo -H -u www-data rm ${PROJECT_FOLDER}/wordpress/wp-content/plugins/sitepress-multilingual-cms.${WORDPRESS_PLUGIN_SITEPRESS_VERSION}.zip
sudo -H -u www-data wp plugin activate sitepress-multilingual-cms --network --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress

sudo -H -u www-data wget --user ${DEVPUBLIC_USER} --password ${DEVPUBLIC_PASSWORD} -P ${PROJECT_FOLDER}/wordpress/wp-content/plugins/ --no-verbose https://devpublic.gginternational.work/wordpress_plugins/wpml-string-translation.${WORDPRESS_PLUGIN_WPML_STRING_TRANSLATION_VERSION}.zip
sudo -H -u www-data unzip -q ${PROJECT_FOLDER}/wordpress/wp-content/plugins/wpml-string-translation.${WORDPRESS_PLUGIN_WPML_STRING_TRANSLATION_VERSION}.zip -d ${PROJECT_FOLDER}/wordpress/wp-content/plugins/
sudo -H -u www-data rm ${PROJECT_FOLDER}/wordpress/wp-content/plugins/wpml-string-translation.${WORDPRESS_PLUGIN_WPML_STRING_TRANSLATION_VERSION}.zip
sudo -H -u www-data wp plugin activate wpml-string-translation --network --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress

# add SUNRISE constant
sudo -H -u www-data wp config set SUNRISE on --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress

# fix memory limit
sudo -H -u www-data wp config set WP_MEMORY_LIMIT 128M --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress

# install WPML key (converted with: https://www.unserialize.me/) and set other sitepress required settings
DOMAIN_LENGTH=${#MAIN_DOMAIN}
DOMAIN_LENGTH=$((DOMAIN_LENGTH + 8)) # add "https://" (8 chars)
sudo -H -u www-data wp db query "UPDATE wp_sitemeta SET meta_value = 'a:1:{s:4:\"wpml\";a:5:{s:3:\"key\";s:10:\"${WORDPRESS_WPML_KEY}\";s:8:\"key_type\";i:0;s:4:\"data\";O:8:\"stdClass\":4:{s:17:\"subscription_type\";i:6721;s:7:\"expires\";N;s:6:\"status\";s:1:\"1\";s:5:\"notes\";s:0:\"\";}s:13:\"registered_by\";i:1;s:8:\"site_url\";s:${DOMAIN_LENGTH}:\"https://${MAIN_DOMAIN}\";}}' WHERE site_id = 1 AND meta_key = 'wp_installer_network'" --url=https://${MAIN_DOMAIN}/ --path=${PROJECT_FOLDER}/wordpress
sudo -H -u www-data wp option update icl_sitepress_settings "{\"hide_upgrade_notice\":\"${WORDPRESS_PLUGIN_SITEPRESS_VERSION}\",\"icl_capabilities_verified\":true,\"active_languages\":[\"en\",\"${WORDPRESS_ADDITIONAL_LANGUAGE_SHORT}\"],\"interview_translators\":1,\"existing_content_language_verified\":1,\"language_negotiation_type\":3,\"icl_lso_link_empty\":0,\"sync_page_ordering\":1,\"sync_page_parent\":1,\"sync_page_template\":1,\"sync_ping_status\":1,\"sync_comment_status\":1,\"sync_sticky_flag\":1,\"sync_password\":1,\"sync_private_flag\":1,\"sync_post_format\":1,\"sync_delete\":0,\"sync_delete_tax\":0,\"sync_post_taxonomies\":1,\"sync_post_date\":0,\"sync_taxonomy_parents\":0,\"translation_pickup_method\":0,\"notify_complete\":1,\"translated_document_status\":1,\"remote_management\":0,\"auto_adjust_ids\":1,\"alert_delay\":0,\"promote_wpml\":0,\"automatic_redirect\":0,\"remember_language\":24,\"icl_lang_sel_copy_parameters\":\"\",\"translated_document_page_url\":\"auto-generate\",\"sync_comments_on_duplicates\":0,\"seo\":{\"head_langs\":1,\"canonicalization_duplicates\":1,\"head_langs_priority\":1},\"posts_slug_translation\":{\"on\":1,\"string_name_migrated\":1},\"urls\":{\"directory_for_default_language\":0,\"show_on_root\":\"\",\"root_html_file_path\":\"\",\"root_page\":0,\"hide_language_switchers\":1},\"xdomain_data\":1,\"custom_posts_sync_option\":{\"post\":1,\"page\":1,\"attachment\":1,\"wp_block\":1,\"wp_navigation\":1,\"wp_template\":1,\"wp_template_part\":1},\"taxonomies_sync_option\":{\"category\":1,\"post_tag\":1,\"translation_priority\":1},\"tm_block_retranslating_terms\":1,\"admin_default_language\":\"_default_\",\"st\":{\"db_ok_for_gettext_context\":true,\"strings_per_page\":10,\"icl_st_auto_reg\":\"disable\",\"strings_language\":\"en\",\"translated-users\":[],\"sw\":[]},\"taxonomy_names_checked\":true,\"setup_complete\":1,\"ajx_health_checked\":true,\"default_categories\":{\"en\":\"1\",\"${WORDPRESS_ADDITIONAL_LANGUAGE_SHORT}\":2},\"default_language\":\"en\",\"setup_wizard_step\":5,\"translation-management\":{\"custom_fields_translation\":{\"title\":2,\"description\":2,\"keywords\":2,\"_edit_last\":0,\"_edit_lock\":0,\"_wp_page_template\":0,\"_wp_attachment_metadata\":0,\"original_post_id\":0,\"_wpml_original_post_id\":0,\"_wp_old_slug\":0,\"_icl_translator_note\":0,\"_alp_processed\":0,\"_pingme\":0,\"_encloseme\":0,\"_icl_lang_duplicate_of\":0,\"_thumbnail_id\":0,\"_wp_attached_file\":0,\"_wp_attachment_image_alt\":2},\"custom_fields_readonly_config\":[\"title\",\"description\",\"keywords\",\"_edit_last\",\"_edit_lock\",\"_wp_page_template\",\"_wp_attachment_metadata\",\"original_post_id\",\"_wpml_original_post_id\",\"_wp_old_slug\",\"_icl_translator_note\",\"_alp_processed\",\"_pingme\",\"_encloseme\",\"_icl_lang_duplicate_of\",\"_thumbnail_id\",\"_wp_attached_file\",\"_wp_attachment_image_alt\"],\"custom_fields_translation_custom\":[],\"custom_fields_translation_custom_readonly\":[],\"doc_translation_method\":0,\"__custom_types_readonly_config_prev\":[],\"custom_types_readonly_config\":[],\"__custom_fields_readonly_config_prev\":[\"title\",\"description\",\"keywords\",\"_edit_last\",\"_edit_lock\",\"_wp_page_template\",\"_wp_attachment_metadata\",\"original_post_id\",\"_wpml_original_post_id\",\"_wp_old_slug\",\"_icl_translator_note\",\"_alp_processed\",\"_pingme\",\"_encloseme\",\"_icl_lang_duplicate_of\",\"_thumbnail_id\",\"_wp_attached_file\",\"_wp_attachment_image_alt\"],\"__custom_term_fields_readonly_config_prev\":[],\"custom_term_fields_readonly_config\":[],\"taxonomies_readonly_config\":{\"translation_priority\":1},\"custom-types_readonly_config\":{\"attachment\":1,\"wp_block\":1,\"wp_navigation\":1,\"wp_template\":1,\"wp_template_part\":1}},\"custom-types_sync_option\":{\"attachment\":1,\"wp_block\":1,\"wp_navigation\":1,\"wp_template\":1,\"wp_template_part\":1},\"language_selector_initialized\":1,\"languages_order\":[\"en\",\"${WORDPRESS_ADDITIONAL_LANGUAGE_SHORT}\"],\"dont_show_help_admin_notice\":true,\"site_key\":\"${WORDPRESS_WPML_KEY}\",\"site_does_not_use_icl\":1,\"xliff_newlines\":2,\"tm_xliff_version\":12}" --format=json --url=https://${MAIN_DOMAIN}/ --path=${PROJECT_FOLDER}/wordpress
sudo -H -u www-data wp option update WPML_Group_Keys "[\"WPML(setup)\",\"WPML(ST)\",\"WPML(TM)\",\"WPML(ST-MO)\",\"WPML(TM-has-run)\"]" --format=json --url=https://${MAIN_DOMAIN}/ --path=${PROJECT_FOLDER}/wordpress
sudo -H -u www-data wp option add "WPML(TM-has-run)" "{\"WPML\\\TM\\\ATE\\\Sitekey\\\Sync\":false}" --format=json --url=https://${MAIN_DOMAIN}/ --path=${PROJECT_FOLDER}/wordpress
sudo -H -u www-data wp db query "UPDATE wp_icl_languages SET active = '1' WHERE code IN ('en', '${WORDPRESS_ADDITIONAL_LANGUAGE_SHORT}')" --url=https://${MAIN_DOMAIN}/ --path=${PROJECT_FOLDER}/wordpress
sudo -H -u www-data wp db query "INSERT INTO wp_icl_locale_map (code, locale) VALUES ('en', 'en_US'), ('${WORDPRESS_ADDITIONAL_LANGUAGE_SHORT}', '${WORDPRESS_ADDITIONAL_LANGUAGE}');" --url=https://${MAIN_DOMAIN}/ --path=${PROJECT_FOLDER}/wordpress
sudo -H -u www-data mkdir ${PROJECT_FOLDER}/wordpress/wp-content/languages

# install WPCF7 plugin + recaptcha v2 downgrade
sudo -H -u www-data wp plugin install contact-form-7 --activate-network  --version=${WORDPRESS_PLUGIN_CONTACT_FORM_VERSION} --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress
sudo -H -u www-data wp plugin install wpcf7-recaptcha --activate-network --version=${WORDPRESS_PLUGIN_WPCF7_RECAPTCHA_VERSION} --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress

# set recaptcha keys (TODO: env vault)
sudo -H -u www-data wp network meta add 1 network_iqfix_recaptcha "{\"sitekey\":\"${DOCKER_RECAPTCHA_SITE_KEY}\",\"secret\":\"${DOCKER_RECAPTCHA_SECRET_KEY}\",\"iqfix_recaptcha\":2,\"recaptcha_source\":\"google.com\"}" --json --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress

# install W3TC
sudo -H -u www-data wp plugin install w3-total-cache --activate-network --version=${WORDPRESS_PLUGIN_W3_TOTAL_CACHE_VERSION} --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress

echo "Starting Wordpress White-Label set-up. Please wait patiently for an outcome..."
sudo -H -u www-data env IS_DOCKER=1 INSTALL_WORDPRESS=1 INSTALL_FUEL=0 WHITELABEL_DOMAINS=${WHITELABEL_DOMAINS} WORDPRESS_MAINSITE_TITLE="${WORDPRESS_MAINSITE_TITLE}" WORDPRESS_MAINSITE_THEME=${WORDPRESS_MAINSITE_THEME} php oil r seed:class Whitelabel
#sudo -H -u www-data php oil r create_whitelabel --domain=${WHITELABEL_DOMAINS} --name="${WORDPRESS_MAINSITE_TITLE}" --theme=${WORDPRESS_MAINSITE_THEME}

echo "Installing additional language (${WORDPRESS_ADDITIONAL_LANGUAGE})... Please wait patiently for an outcome..."
sudo -H -u www-data env IS_DOCKER=1 INSTALL_WORDPRESS=1 INSTALL_FUEL=0 WHITELABEL_DOMAINS=${WHITELABEL_DOMAINS} LANGUAGE=${WORDPRESS_ADDITIONAL_LANGUAGE} php oil r seed:class Whitelabel_Language

# set-up favicon
FAVICON_MEDIA_ID=`sudo -H -u www-data wp media import ${PROJECT_FOLDER}/resources/wordpress/images/fav.png --porcelain --url=https://${WHITELABEL_DOMAINS}/ --path=${PROJECT_FOLDER}/wordpress`
sudo -H -u www-data wp option update site_icon $FAVICON_MEDIA_ID --url=https://${WHITELABEL_DOMAINS}/ --path=${PROJECT_FOLDER}/wordpress

rm -rf ${PROJECT_FOLDER}/wordpress-images

# TODO: fill up whitelotto.loc website with articles and menus

# configure W3TC to use our redis
sudo -H -u www-data wp w3-total-cache option set objectcache.enabled true --type=boolean --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress
sudo -H -u www-data wp w3-total-cache option set objectcache.engine redis --type=string --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress
sudo -H -u www-data wp w3-total-cache option set objectcache.redis.servers "${REDIS_HOST}:6379" --type=string --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress
sudo -H -u www-data wp w3-total-cache option set objectcache.redis.password "${REDIS_PASSWORD}" --type=string --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress
sudo -H -u www-data wp w3-total-cache option set objectcache.fallback_transients false --type=boolean --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress
sudo -H -u www-data wp network meta update 1 w3tc_setupguide_completed `date +%s` --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress

# add DEBUG to Wordpress
sudo -H -u www-data wp config set WP_DEBUG true --raw --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress
sudo -H -u www-data wp config set WP_DEBUG_LOG '/var/log/whitelotto/wordpress.log' --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress
sudo -H -u www-data wp config set WP_DEBUG_DISPLAY true --raw --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress
sudo -H -u www-data wp config set ACCESS_TOKEN 'BGDDWjb8zaQnB8UckvuFm6M4' --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress

# we have everything installed - let's now make everything up to date
sudo -H -u www-data wp plugin deactivate w3-total-cache --network --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress
# sudo -H -u www-data wp core update --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress
sudo -H -u www-data wp plugin update --all --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress
sudo -H -u www-data wp language core update --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress
sudo -H -u www-data wp plugin activate w3-total-cache --network --url=https://${MAIN_DOMAIN} --path=${PROJECT_FOLDER}/wordpress

# download GeoIP
sudo -H -u www-data wget --user ${DEVPUBLIC_USER} --password ${DEVPUBLIC_PASSWORD} -P ${PROJECT_FOLDER}/platform/fuel/app/vendor/geoip2/ --no-verbose https://devpublic.gginternational.work/geoip/GeoIP2-Country.mmdb
sudo -H -u www-data wget --user ${DEVPUBLIC_USER} --password ${DEVPUBLIC_PASSWORD} -P ${PROJECT_FOLDER}/platform/fuel/app/vendor/geoip2/ --no-verbose https://devpublic.gginternational.work/geoip/GeoIP2-City.mmdb

# remove the script itself
rm -- "$0"
