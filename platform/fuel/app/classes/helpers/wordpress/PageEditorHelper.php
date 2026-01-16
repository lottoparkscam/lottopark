<?php

namespace Helpers\Wordpress;

use Container;
use Fuel\Core\Input;
use Models\Whitelabel;
use WordpressNoticeHelper;
use WP_Post;

class PageEditorHelper
{
    public const WORDPRESS_QUICK_EDITOR_BUTTON_ID = 'inline hide-if-no-js';
    public const WORDPRESS_BIN_BUTTON_ID = 'trash';
    public const WORDPRESS_WHITELABEL_USER_SUPPORT_LOGIN = 'wlsupport';
    public const PAGES_SLUGS_TO_DISABLE_EDITING = [
        'account',
        'login',
        'lostpassword',
        'signup',
        'deposit',
        'order',
        'failure',
        'success',
        'auth',
        'last-steps',
    ];
    public const PAGES_SLUGS_PER_WORDPRESS_USER_LOGIN_TO_ENABLE_EDITING = [
        self::WORDPRESS_WHITELABEL_USER_SUPPORT_LOGIN => [
            ...self::PAGES_SLUGS_TO_DISABLE_EDITING_FOR_WHITELABEL_V1,
            ...self::PAGES_SLUGS_TO_DISABLE_EDITING,
        ],
    ];
    public const PAGES_SLUGS_TO_DISABLE_EDITING_FOR_WHITELABEL_V1 = [
        'footer',
        'privacy',
        'terms',
    ];

    public static function turnOffPagesEditing(): void
    {
        add_filter('page_row_actions', fn(array $actions) => self::removeQuickEditorButton($actions), 10, 1);
        add_filter('page_row_actions', fn(array $actions) => self::removeBinButton($actions), 10, 1);
        add_action('load-page.php', fn() => self::disablePagesEditor(), 10, 0);
    }

    public static function getPagesSlugsToDisableEditing(): array
    {
        if (is_super_admin()) {
            return [];
        }

        $pagesSlugsToDisableEditing = self::PAGES_SLUGS_TO_DISABLE_EDITING;

        /** @var Whitelabel|null $whitelabel */
        $whitelabel = Container::get('whitelabel');
        if (!is_null($whitelabel) && $whitelabel->isV1()) {
            $additionalPagesToDisable = self::PAGES_SLUGS_TO_DISABLE_EDITING_FOR_WHITELABEL_V1;
            /** We remove duplicate values */
            $pagesSlugsToDisableEditing = array_unique(array_merge($pagesSlugsToDisableEditing, $additionalPagesToDisable));
        }

        $currentWordpressUser = wp_get_current_user();
        $isUserWithSpecialEditorAccess = key_exists($currentWordpressUser->user_login, self::PAGES_SLUGS_PER_WORDPRESS_USER_LOGIN_TO_ENABLE_EDITING);
        if ($isUserWithSpecialEditorAccess) {
            $currentUserPagesToEditing = self::PAGES_SLUGS_PER_WORDPRESS_USER_LOGIN_TO_ENABLE_EDITING[$currentWordpressUser->user_login];
            /** We removed pages which have access to editor */
            $pagesSlugsToDisableEditing = array_diff($pagesSlugsToDisableEditing, $currentUserPagesToEditing);
        }
        return $pagesSlugsToDisableEditing;
    }

    public static function disablePagesEditor(): void
    {
        /**
         * Post parameter exist only on page during editing and contains wpml id of the edited page.
         */
        $pageId = Input::get('post');
        if (is_null($pageId)) {
            return;
        }

        $pagesSlugsToDisableEditing = PageEditorHelper::getPagesSlugsToDisableEditing();

        /** Input get will return values as string we must cast this to int */
        $shouldEditorBeDisabledForCurrentPage = in_array(self::getEnglishPostNameForCurrentPageId((int)$pageId), $pagesSlugsToDisableEditing);
        if ($shouldEditorBeDisabledForCurrentPage) {
            WordpressNoticeHelper::showWarningNotice(_('Lotto Platform: You cannot edit this page.'));
            /**
             * This function disable page content editor.
             */
            remove_post_type_support('page', 'editor');

            /**
             * This function hide settings like: parent page, order, template.(all what page attributes contains)
             * This will work only when page content editor is disabled.
             */
            remove_meta_box('pageparentdiv', 'page', 'side');

            /**
             * This function hide slug editor section.
             * This will work only when page content editor is disabled.
             */
            remove_meta_box('slugdiv', 'page', 'side');

            /**
             * This filter function disable permalink editor.
             */
            add_filter('get_sample_permalink_html', fn() => '', 10, 0);

            /**
             * This filter function disable title editor.
             */
            remove_post_type_support('page', 'title');
        }
    }

    public static function removeQuickEditorButton(array $actions): array
    {
        $pageId = get_the_ID();
        $pagesSlugsToDisableEditing = PageEditorHelper::getPagesSlugsToDisableEditing();
        $isPageNotEditable = in_array(self::getEnglishPostNameForCurrentPageId($pageId), $pagesSlugsToDisableEditing);
        if ($isPageNotEditable) {
            unset($actions[self::WORDPRESS_QUICK_EDITOR_BUTTON_ID]);
        }
        return $actions;
    }

    public static function removeBinButton(array $actions): array
    {
        $pageId = get_the_ID();
        $pagesSlugsToDisableEditing = PageEditorHelper::getPagesSlugsToDisableEditing();
        $isPageNotEditable = in_array(self::getEnglishPostNameForCurrentPageId($pageId), $pagesSlugsToDisableEditing);
        if ($isPageNotEditable) {
            unset($actions[self::WORDPRESS_BIN_BUTTON_ID]);
        }
        return $actions;
    }

    private static function getEnglishPostNameForCurrentPageId(int $pageId): string
    {
        $id = apply_filters('wpml_object_id', $pageId, 'post', true, 'en');
        $post = get_post($id);
        return $post->post_name;
    }
}
