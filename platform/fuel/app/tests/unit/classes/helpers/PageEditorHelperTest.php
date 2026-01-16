<?php

namespace Tests\Unit\Classes\Helpers;

use Container;
use Helpers\Wordpress\PageEditorHelper;
use Models\Whitelabel;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\MockObject\MockObject;
use Test_Unit;

/** @codeCoverageIgnore */
class PageEditorHelperTest extends Test_Unit
{
    use PHPMock;

    private MockObject $wordpressGetCurrentUserMock;
    private MockObject $isSuperAdminMock;
    private MockObject $removePostTypeSupportMock;
    private MockObject $removeMetaBoxMock;
    private MockObject $addFilterMock;
    private MockObject $applyFiltersMock;
    private MockObject $getPostMock;
    private MockObject $getTheIdMock;
    private Whitelabel|null $whitelabelStub;
    private object $wordpressUserStub;
    private object $wordpressPostStub;
    private const WORDPRESS_ACTIONS = [
        'edit' => 'Edit',
        PageEditorHelper::WORDPRESS_QUICK_EDITOR_BUTTON_ID => 'Quick Edit',
        PageEditorHelper::WORDPRESS_BIN_BUTTON_ID => 'Bin',
        'view' => 'View'];

    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabelStub = Container::get('whitelabel');
        $this->wordpressUserStub = new class () {
            public string $user_login = 'whitelotto';
        };
        $this->wordpressPostStub = new class () {
            public string $post_name = PageEditorHelper::PAGES_SLUGS_TO_DISABLE_EDITING[0];
        };
        $this->wordpressGetCurrentUserMock = $this->getFunctionMock('Helpers\Wordpress', 'wp_get_current_user');
        $this->isSuperAdminMock = $this->getFunctionMock('Helpers\Wordpress', 'is_super_admin');
        $this->removePostTypeSupportMock = $this->getFunctionMock('Helpers\Wordpress', 'remove_post_type_support');
        $this->removeMetaBoxMock = $this->getFunctionMock('Helpers\Wordpress', 'remove_meta_box');
        $this->addFilterMock = $this->getFunctionMock('Helpers\Wordpress', 'add_filter');
        $this->applyFiltersMock = $this->getFunctionMock('Helpers\Wordpress', 'apply_filters');
        $this->getPostMock = $this->getFunctionMock('Helpers\Wordpress', 'get_post');
        $this->getTheIdMock = $this->getFunctionMock('Helpers\Wordpress', 'get_the_ID');
        /** This function disable shown values from echo. */
        $this->setOutputCallback(fn() => null);
    }

    /** @test */
    public function getPagesSlugsToDisableEditing_forSuperAdminDontRemoveEditor(): void
    {
        $this->isSuperAdminMock->expects($this->any())
            ->willReturn(true);
        $results = PageEditorHelper::getPagesSlugsToDisableEditing();
        $this->assertSame($results, []);
    }

    /** @test */
    public function getPagesSlugsToDisableEditing_forWhitelabelSupportDontRemoveEditorForSpecialPages(): void
    {
        $this->whitelabelStub->type = Whitelabel::TYPE_V1;

        $this->wordpressUserStub->user_login = PageEditorHelper::WORDPRESS_WHITELABEL_USER_SUPPORT_LOGIN;
        $this->isSuperAdminMock->expects($this->any())
            ->willReturn(false);

        $this->wordpressGetCurrentUserMock->expects($this->any())
            ->willReturn($this->wordpressUserStub);

        $results = PageEditorHelper::getPagesSlugsToDisableEditing();
        $this->assertEmpty(array_intersect(
            $results,
            PageEditorHelper::PAGES_SLUGS_PER_WORDPRESS_USER_LOGIN_TO_ENABLE_EDITING[$this->wordpressUserStub->user_login]
        ));

        $this->whitelabelStub->type = Whitelabel::TYPE_V2;

        $this->isSuperAdminMock->expects($this->any())
            ->willReturn(false);

        $this->wordpressGetCurrentUserMock->expects($this->any())
            ->willReturn($this->wordpressUserStub);

        $results = PageEditorHelper::getPagesSlugsToDisableEditing();
        $this->assertEmpty(array_intersect(
            $results,
            PageEditorHelper::PAGES_SLUGS_PER_WORDPRESS_USER_LOGIN_TO_ENABLE_EDITING[$this->wordpressUserStub->user_login]
        ));
    }

    /** @test */
    public function getPagesSlugsToDisableEditing_forWhitelabelAdminRemoveSelectedPagesEditor(): void
    {
        $this->whitelabelStub->type = Whitelabel::TYPE_V1;

        $this->isSuperAdminMock->expects($this->any())
            ->willReturn(false);

        $this->wordpressGetCurrentUserMock->expects($this->any())
            ->willReturn($this->wordpressUserStub);

        $results = PageEditorHelper::getPagesSlugsToDisableEditing();
        $expectedResults = array_merge(
            array_diff(
                PageEditorHelper::PAGES_SLUGS_TO_DISABLE_EDITING,
                PageEditorHelper::PAGES_SLUGS_TO_DISABLE_EDITING_FOR_WHITELABEL_V1
            ),
            PageEditorHelper::PAGES_SLUGS_TO_DISABLE_EDITING_FOR_WHITELABEL_V1
        );
        $this->assertEquals(sort($expectedResults), sort($results));

        $this->whitelabelStub->type = Whitelabel::TYPE_V2;

        $this->isSuperAdminMock->expects($this->any())
            ->willReturn(false);

        $this->wordpressGetCurrentUserMock->expects($this->any())
            ->willReturn($this->wordpressUserStub);

        $results = PageEditorHelper::getPagesSlugsToDisableEditing();
        $this->assertEquals(PageEditorHelper::PAGES_SLUGS_TO_DISABLE_EDITING, $results);
    }

    /** @test */
    public function removeQuickEditor_forSuperAdminDontRemoveEditor(): void
    {
        $this->getTheIdMock->expects($this->any())
            ->willReturn(1);

        $this->applyFiltersMock->expects($this->any())
            ->willReturn(1);

        $this->isSuperAdminMock->expects($this->any())
            ->willReturn(true);

        $this->getPostMock->expects($this->any())
            ->willReturn($this->wordpressPostStub);

        $results = PageEditorHelper::removeQuickEditorButton(self::WORDPRESS_ACTIONS);
        $this->assertEquals(self::WORDPRESS_ACTIONS, $results);
    }

    /** @test */
    public function removeQuickEditor_forWhitelabelV1SupportDontRemoveEditorForSpecialPages(): void
    {
        $whitelabelSupportPagesToEnabled = PageEditorHelper::PAGES_SLUGS_PER_WORDPRESS_USER_LOGIN_TO_ENABLE_EDITING[PageEditorHelper::WORDPRESS_WHITELABEL_USER_SUPPORT_LOGIN];
        $firstPageToEnableEditingForWordpressSupportUser = array_shift($whitelabelSupportPagesToEnabled);
        $this->wordpressPostStub->post_name = $firstPageToEnableEditingForWordpressSupportUser;
        $this->wordpressUserStub->user_login = PageEditorHelper::WORDPRESS_WHITELABEL_USER_SUPPORT_LOGIN;
        $this->whitelabelStub->type = Whitelabel::TYPE_V1;

        $this->getTheIdMock->expects($this->any())
            ->willReturn(1);

        $this->applyFiltersMock->expects($this->any())
            ->willReturn(1);

        $this->isSuperAdminMock->expects($this->any())
            ->willReturn(false);

        $this->getPostMock->expects($this->any())
            ->willReturn($this->wordpressPostStub);

        $this->wordpressGetCurrentUserMock->expects($this->any())
            ->willReturn($this->wordpressUserStub);

        $results = PageEditorHelper::removeQuickEditorButton(self::WORDPRESS_ACTIONS);
        $this->assertTrue(key_exists(PageEditorHelper::WORDPRESS_QUICK_EDITOR_BUTTON_ID, $results));

        $firstPageToDisableEditingForWordpressSupportUser = array_diff(
            PageEditorHelper::PAGES_SLUGS_TO_DISABLE_EDITING,
            $whitelabelSupportPagesToEnabled,
            PageEditorHelper::PAGES_SLUGS_TO_DISABLE_EDITING_FOR_WHITELABEL_V1
        );

        if ($firstPageToDisableEditingForWordpressSupportUser) {
            $this->wordpressPostStub->post_name = array_shift($firstPageToDisableEditingForWordpressSupportUser);
            $this->wordpressUserStub->user_login = PageEditorHelper::WORDPRESS_WHITELABEL_USER_SUPPORT_LOGIN;
            $this->whitelabelStub->type = Whitelabel::TYPE_V1;

            $this->getTheIdMock->expects($this->any())
                ->willReturn(1);

            $this->applyFiltersMock->expects($this->any())
                ->willReturn(1);

            $this->isSuperAdminMock->expects($this->any())
                ->willReturn(false);

            $this->getPostMock->expects($this->any())
                ->willReturn($this->wordpressPostStub);

            $results = PageEditorHelper::removeQuickEditorButton(self::WORDPRESS_ACTIONS);
            $this->assertNotTrue(key_exists(PageEditorHelper::WORDPRESS_QUICK_EDITOR_BUTTON_ID, $results));
        }
    }

    /** @test */
    public function removeQuickEditor_forWhitelabelV2SupportDontRemoveEditorForSpecialPages(): void
    {
        $this->wordpressUserStub->user_login = PageEditorHelper::WORDPRESS_WHITELABEL_USER_SUPPORT_LOGIN;
        $whitelabelSupportPagesToEnabled = PageEditorHelper::PAGES_SLUGS_PER_WORDPRESS_USER_LOGIN_TO_ENABLE_EDITING[PageEditorHelper::WORDPRESS_WHITELABEL_USER_SUPPORT_LOGIN];
        $pagesToDisableEditingForWordpressSupportUser = array_diff(PageEditorHelper::PAGES_SLUGS_TO_DISABLE_EDITING, $whitelabelSupportPagesToEnabled);

        if ($pagesToDisableEditingForWordpressSupportUser) {
            $this->wordpressPostStub->post_name = array_shift($pagesToDisableEditingForWordpressSupportUser);
            $this->whitelabelStub->type = Whitelabel::TYPE_V2;

            $this->getTheIdMock->expects($this->any())
                ->willReturn(1);

            $this->applyFiltersMock->expects($this->any())
                ->willReturn(1);

            $this->isSuperAdminMock->expects($this->any())
                ->willReturn(false);

            $this->getPostMock->expects($this->any())
                ->willReturn($this->wordpressPostStub);

            $this->wordpressGetCurrentUserMock->expects($this->any())
                ->willReturn($this->wordpressUserStub);

            $results = PageEditorHelper::removeQuickEditorButton(self::WORDPRESS_ACTIONS);
            $this->assertTrue(!key_exists(PageEditorHelper::WORDPRESS_QUICK_EDITOR_BUTTON_ID, $results));

            $firstPageToEnabledEditingForWordpressSupportUser = array_shift($whitelabelSupportPagesToEnabled);
            $this->wordpressPostStub->post_name = $firstPageToEnabledEditingForWordpressSupportUser;

            $this->getTheIdMock->expects($this->any())
                ->willReturn(1);

            $this->applyFiltersMock->expects($this->any())
                ->willReturn(1);

            $this->isSuperAdminMock->expects($this->any())
                ->willReturn(false);

            $this->getPostMock->expects($this->any())
                ->willReturn($this->wordpressPostStub);

            $this->wordpressGetCurrentUserMock->expects($this->any())
                ->willReturn($this->wordpressUserStub);

            $results = PageEditorHelper::removeQuickEditorButton(self::WORDPRESS_ACTIONS);
            $this->assertTrue(key_exists(PageEditorHelper::WORDPRESS_QUICK_EDITOR_BUTTON_ID, $results));
        }
    }

    /** @test */
    public function removeQuickEditor_forWhitelabelAdminRemoveSelectedPagesEditor(): void
    {
        $this->whitelabelStub->type = Whitelabel::TYPE_V1;

        $this->getTheIdMock->expects($this->any())
            ->willReturn(1);

        $this->applyFiltersMock->expects($this->any())
            ->willReturn(1);

        $this->isSuperAdminMock->expects($this->any())
            ->willReturn(false);

        $this->getPostMock->expects($this->any())
            ->willReturn($this->wordpressPostStub);

        $this->wordpressGetCurrentUserMock->expects($this->any())
            ->willReturn($this->wordpressUserStub);

        $results = PageEditorHelper::removeQuickEditorButton(self::WORDPRESS_ACTIONS);
        $this->assertTrue(!key_exists(PageEditorHelper::WORDPRESS_QUICK_EDITOR_BUTTON_ID, $results));

        $this->whitelabelStub->type = Whitelabel::TYPE_V2;

        $this->getTheIdMock->expects($this->any())
            ->willReturn(1);

        $this->applyFiltersMock->expects($this->any())
            ->willReturn(1);

        $this->isSuperAdminMock->expects($this->any())
            ->willReturn(false);

        $this->getPostMock->expects($this->any())
            ->willReturn($this->wordpressPostStub);

        $this->wordpressGetCurrentUserMock->expects($this->any())
            ->willReturn($this->wordpressUserStub);

        $results = PageEditorHelper::removeQuickEditorButton(self::WORDPRESS_ACTIONS);
        $this->assertTrue(!key_exists(PageEditorHelper::WORDPRESS_QUICK_EDITOR_BUTTON_ID, $results));
    }

    /** @test */
    public function removeBinButton_forSuperAdminDontRemoveEditor(): void
    {
        $this->getTheIdMock->expects($this->any())
            ->willReturn(1);

        $this->applyFiltersMock->expects($this->any())
            ->willReturn(1);

        $this->isSuperAdminMock->expects($this->any())
            ->willReturn(true);

        $this->getPostMock->expects($this->any())
            ->willReturn($this->wordpressPostStub);

        $results = PageEditorHelper::removeBinButton(self::WORDPRESS_ACTIONS);
        $this->assertEquals(self::WORDPRESS_ACTIONS, $results);
    }

    /** @test */
    public function removeBinButton_forWhitelabelV1SupportDontRemoveEditorForSpecialPages(): void
    {
        $whitelabelSupportPagesToEnabled = PageEditorHelper::PAGES_SLUGS_PER_WORDPRESS_USER_LOGIN_TO_ENABLE_EDITING[PageEditorHelper::WORDPRESS_WHITELABEL_USER_SUPPORT_LOGIN];
        $firstPageToEnableEditingForWordpressSupportUser = array_shift($whitelabelSupportPagesToEnabled);
        $this->wordpressPostStub->post_name = $firstPageToEnableEditingForWordpressSupportUser;
        $this->wordpressUserStub->user_login = PageEditorHelper::WORDPRESS_WHITELABEL_USER_SUPPORT_LOGIN;
        $this->whitelabelStub->type = Whitelabel::TYPE_V1;

        $this->getTheIdMock->expects($this->any())
            ->willReturn(1);

        $this->applyFiltersMock->expects($this->any())
            ->willReturn(1);

        $this->isSuperAdminMock->expects($this->any())
            ->willReturn(false);

        $this->getPostMock->expects($this->any())
            ->willReturn($this->wordpressPostStub);

        $this->wordpressGetCurrentUserMock->expects($this->any())
            ->willReturn($this->wordpressUserStub);

        $results = PageEditorHelper::removeBinButton(self::WORDPRESS_ACTIONS);
        $this->assertTrue(key_exists(PageEditorHelper::WORDPRESS_BIN_BUTTON_ID, $results));

        $firstPageToDisableEditingForWordpressSupportUser = array_diff(
            PageEditorHelper::PAGES_SLUGS_TO_DISABLE_EDITING,
            $whitelabelSupportPagesToEnabled,
            PageEditorHelper::PAGES_SLUGS_TO_DISABLE_EDITING_FOR_WHITELABEL_V1
        );
        if ($firstPageToDisableEditingForWordpressSupportUser) {
            $this->wordpressPostStub->post_name = array_shift($firstPageToDisableEditingForWordpressSupportUser);
            $this->wordpressUserStub->user_login = PageEditorHelper::WORDPRESS_WHITELABEL_USER_SUPPORT_LOGIN;
            $this->whitelabelStub->type = Whitelabel::TYPE_V1;

            $this->getTheIdMock->expects($this->any())
                ->willReturn(1);

            $this->applyFiltersMock->expects($this->any())
                ->willReturn(1);

            $this->isSuperAdminMock->expects($this->any())
                ->willReturn(false);

            $this->getPostMock->expects($this->any())
                ->willReturn($this->wordpressPostStub);

            $results = PageEditorHelper::removeBinButton(self::WORDPRESS_ACTIONS);
            $this->assertTrue(!key_exists(PageEditorHelper::WORDPRESS_BIN_BUTTON_ID, $results));
        }
    }

    /** @test */
    public function removeBinButton_forWhitelabelV2SupportDontRemoveEditorForSpecialPages(): void
    {
        $this->wordpressUserStub->user_login = PageEditorHelper::WORDPRESS_WHITELABEL_USER_SUPPORT_LOGIN;
        $whitelabelSupportPagesToEnabled = PageEditorHelper::PAGES_SLUGS_PER_WORDPRESS_USER_LOGIN_TO_ENABLE_EDITING[PageEditorHelper::WORDPRESS_WHITELABEL_USER_SUPPORT_LOGIN];
        $pagesToDisableEditingForWordpressSupportUser = array_diff(PageEditorHelper::PAGES_SLUGS_TO_DISABLE_EDITING, $whitelabelSupportPagesToEnabled);
        if ($pagesToDisableEditingForWordpressSupportUser) {
            $this->wordpressPostStub->post_name = array_shift($pagesToDisableEditingForWordpressSupportUser);
            $this->whitelabelStub->type = Whitelabel::TYPE_V2;

            $this->getTheIdMock->expects($this->any())
                ->willReturn(1);

            $this->applyFiltersMock->expects($this->any())
                ->willReturn(1);

            $this->isSuperAdminMock->expects($this->any())
                ->willReturn(false);

            $this->getPostMock->expects($this->any())
                ->willReturn($this->wordpressPostStub);

            $this->wordpressGetCurrentUserMock->expects($this->any())
                ->willReturn($this->wordpressUserStub);

            $results = PageEditorHelper::removeBinButton(self::WORDPRESS_ACTIONS);
            $this->assertTrue(!key_exists(PageEditorHelper::WORDPRESS_BIN_BUTTON_ID, $results));

            $firstPageToEnabledEditingForWordpressSupportUser = array_shift($whitelabelSupportPagesToEnabled);
            $this->wordpressPostStub->post_name = $firstPageToEnabledEditingForWordpressSupportUser;

            $this->getTheIdMock->expects($this->any())
                ->willReturn(1);

            $this->applyFiltersMock->expects($this->any())
                ->willReturn(1);

            $this->isSuperAdminMock->expects($this->any())
                ->willReturn(false);

            $this->getPostMock->expects($this->any())
                ->willReturn($this->wordpressPostStub);

            $this->wordpressGetCurrentUserMock->expects($this->any())
                ->willReturn($this->wordpressUserStub);

            $results = PageEditorHelper::removeBinButton(self::WORDPRESS_ACTIONS);
            $this->assertTrue(key_exists(PageEditorHelper::WORDPRESS_BIN_BUTTON_ID, $results));
        }
    }

    /** @test */
    public function removeBinButton_forWhitelabelAdminRemoveSelectedPagesEditor(): void
    {
        $this->whitelabelStub->type = Whitelabel::TYPE_V1;

        $this->getTheIdMock->expects($this->any())
            ->willReturn(1);

        $this->applyFiltersMock->expects($this->any())
            ->willReturn(1);

        $this->isSuperAdminMock->expects($this->any())
            ->willReturn(false);

        $this->getPostMock->expects($this->any())
            ->willReturn($this->wordpressPostStub);

        $this->wordpressGetCurrentUserMock->expects($this->any())
            ->willReturn($this->wordpressUserStub);

        $results = PageEditorHelper::removeBinButton(self::WORDPRESS_ACTIONS);
        $this->assertTrue(!key_exists(PageEditorHelper::WORDPRESS_BIN_BUTTON_ID, $results));

        $this->whitelabelStub->type = Whitelabel::TYPE_V2;

        $this->getTheIdMock->expects($this->any())
            ->willReturn(1);

        $this->applyFiltersMock->expects($this->any())
            ->willReturn(1);

        $this->isSuperAdminMock->expects($this->any())
            ->willReturn(false);

        $this->getPostMock->expects($this->any())
            ->willReturn($this->wordpressPostStub);

        $this->wordpressGetCurrentUserMock->expects($this->any())
            ->willReturn($this->wordpressUserStub);

        $results = PageEditorHelper::removeBinButton(self::WORDPRESS_ACTIONS);
        $this->assertTrue(!key_exists(PageEditorHelper::WORDPRESS_BIN_BUTTON_ID, $results));
    }

    /** @test */
    public function disablePagesEditor_postParameterNotExists_doNothing(): void
    {
        $this->removeMetaBoxMock->expects($this->never());
        $this->addFilterMock->expects($this->never());
        $this->removePostTypeSupportMock->expects($this->never());

        PageEditorHelper::disablePagesEditor();
    }

    /** @test */
    public function disablePagesEditor_forSuperAdminRemoveSelectedPagesEditor(): void
    {
        $this->setInput('get', ['post' => 'test']);

        $this->applyFiltersMock->expects($this->any())
            ->willReturn(1);

        $this->isSuperAdminMock->expects($this->any())
            ->willReturn(true);

        $this->getPostMock->expects($this->any())
            ->willReturn($this->wordpressPostStub);

        $this->removeMetaBoxMock->expects($this->never());
        $this->addFilterMock->expects($this->never());
        $this->removePostTypeSupportMock->expects($this->never());

        PageEditorHelper::disablePagesEditor();
    }

    /** @test */
    public function disablePagesEditor_forWhitelabelV1SupportDontRemoveEditorForSpecialPages(): void
    {
        $this->wordpressUserStub->user_login = PageEditorHelper::WORDPRESS_WHITELABEL_USER_SUPPORT_LOGIN;
        $this->setInput('get', ['post' => 'test']);
        $this->whitelabelStub->type = Whitelabel::TYPE_V1;

        $this->applyFiltersMock->expects($this->any())
            ->willReturn(1);

        $this->isSuperAdminMock->expects($this->any())
            ->willReturn(false);

        $this->wordpressGetCurrentUserMock->expects($this->any())
            ->willReturn($this->wordpressUserStub);

        $this->getPostMock->expects($this->any())
            ->willReturn($this->wordpressPostStub);

        $this->removeMetaBoxMock->expects($this->any());
        $this->addFilterMock->expects($this->any());
        $this->removePostTypeSupportMock->expects($this->any());

        PageEditorHelper::disablePagesEditor();

        $this->wordpressUserStub->user_login = PageEditorHelper::WORDPRESS_WHITELABEL_USER_SUPPORT_LOGIN;
        $pagesToEnabledEditing = PageEditorHelper::PAGES_SLUGS_PER_WORDPRESS_USER_LOGIN_TO_ENABLE_EDITING[PageEditorHelper::WORDPRESS_WHITELABEL_USER_SUPPORT_LOGIN];
        $this->wordpressPostStub->post_name = array_shift($pagesToEnabledEditing);

        $this->applyFiltersMock->expects($this->any())
            ->willReturn(1);

        $this->isSuperAdminMock->expects($this->any())
            ->willReturn(false);

        $this->wordpressGetCurrentUserMock->expects($this->any())
            ->willReturn($this->wordpressUserStub);

        $this->getPostMock->expects($this->any())
            ->willReturn($this->wordpressPostStub);

        $this->removeMetaBoxMock->expects($this->never());
        $this->addFilterMock->expects($this->never());
        $this->removePostTypeSupportMock->expects($this->never());

        PageEditorHelper::disablePagesEditor();
    }

    /** @test */
    public function disablePagesEditor_forWhitelabelV2SupportDontRemoveEditorForSpecialPages(): void
    {
        $this->wordpressUserStub->user_login = PageEditorHelper::WORDPRESS_WHITELABEL_USER_SUPPORT_LOGIN;
        $this->setInput('get', ['post' => 'test']);
        $this->whitelabelStub->type = Whitelabel::TYPE_V2;

        $this->applyFiltersMock->expects($this->any())
            ->willReturn(1);

        $this->isSuperAdminMock->expects($this->any())
            ->willReturn(false);

        $this->wordpressGetCurrentUserMock->expects($this->any())
            ->willReturn($this->wordpressUserStub);

        $this->getPostMock->expects($this->any())
            ->willReturn($this->wordpressPostStub);

        $this->removeMetaBoxMock->expects($this->any());
        $this->addFilterMock->expects($this->any());
        $this->removePostTypeSupportMock->expects($this->any());

        PageEditorHelper::disablePagesEditor();

        $this->wordpressUserStub->user_login = PageEditorHelper::WORDPRESS_WHITELABEL_USER_SUPPORT_LOGIN;
        $firstPagesToEnabledEditing = PageEditorHelper::PAGES_SLUGS_PER_WORDPRESS_USER_LOGIN_TO_ENABLE_EDITING[PageEditorHelper::WORDPRESS_WHITELABEL_USER_SUPPORT_LOGIN];
        $this->wordpressPostStub->post_name = array_shift($firstPagesToEnabledEditing);
        $this->setInput('get', ['post' => 'test']);

        $this->applyFiltersMock->expects($this->any())
            ->willReturn(1);

        $this->isSuperAdminMock->expects($this->any())
            ->willReturn(false);

        $this->wordpressGetCurrentUserMock->expects($this->any())
            ->willReturn($this->wordpressUserStub);

        $this->getPostMock->expects($this->any())
            ->willReturn($this->wordpressPostStub);

        $this->removeMetaBoxMock->expects($this->never());
        $this->addFilterMock->expects($this->never());
        $this->removePostTypeSupportMock->expects($this->never());

        PageEditorHelper::disablePagesEditor();
    }

    /** @test */
    public function disablePagesEditor_forWhitelabelV1AdminRemoveSelectedPagesEditor(): void
    {
        $firstPageToDisableEditing = PageEditorHelper::PAGES_SLUGS_TO_DISABLE_EDITING[0];
        $this->wordpressPostStub->post_name = $firstPageToDisableEditing;
        $this->setInput('get', ['post' => 'test']);
        $this->whitelabelStub->type = Whitelabel::TYPE_V1;

        $this->applyFiltersMock->expects($this->any())
            ->willReturn(1);

        $this->isSuperAdminMock->expects($this->any())
            ->willReturn(false);

        $this->wordpressGetCurrentUserMock->expects($this->any())
            ->willReturn($this->wordpressUserStub);

        $this->getPostMock->expects($this->any())
            ->willReturn($this->wordpressPostStub);

        $this->removeMetaBoxMock->expects($this->any());
        $this->addFilterMock->expects($this->any());
        $this->removePostTypeSupportMock->expects($this->any());

        PageEditorHelper::disablePagesEditor();

        $firstPageToDisableEditing = PageEditorHelper::PAGES_SLUGS_TO_DISABLE_EDITING_FOR_WHITELABEL_V1[0];
        $this->wordpressPostStub->post_name = $firstPageToDisableEditing;

        $this->applyFiltersMock->expects($this->any())
            ->willReturn(1);

        $this->isSuperAdminMock->expects($this->any())
            ->willReturn(false);

        $this->wordpressGetCurrentUserMock->expects($this->any())
            ->willReturn($this->wordpressUserStub);

        $this->getPostMock->expects($this->any())
            ->willReturn($this->wordpressPostStub);

        $this->removeMetaBoxMock->expects($this->any());
        $this->addFilterMock->expects($this->any());
        $this->removePostTypeSupportMock->expects($this->any());

        PageEditorHelper::disablePagesEditor();
    }

    /** @test */
    public function disablePagesEditor_forWhitelabelV2AdminRemoveSelectedPagesEditor(): void
    {
        $firstPageToDisableEditing = PageEditorHelper::PAGES_SLUGS_TO_DISABLE_EDITING[0];
        $this->wordpressPostStub->post_name = $firstPageToDisableEditing;
        $this->setInput('get', ['post' => 'test']);
        $this->whitelabelStub->type = Whitelabel::TYPE_V2;

        $this->applyFiltersMock->expects($this->any())
            ->willReturn(1);

        $this->isSuperAdminMock->expects($this->any())
            ->willReturn(false);

        $this->wordpressGetCurrentUserMock->expects($this->any())
            ->willReturn($this->wordpressUserStub);

        $this->getPostMock->expects($this->any())
            ->willReturn($this->wordpressPostStub);

        $this->removeMetaBoxMock->expects($this->any());
        $this->addFilterMock->expects($this->any());
        $this->removePostTypeSupportMock->expects($this->any());

        PageEditorHelper::disablePagesEditor();
    }

    /** @test */
    public function disablePagesEditor_disableContentAndTitleEditor(): void
    {
        $firstPageToDisableEditing = PageEditorHelper::PAGES_SLUGS_TO_DISABLE_EDITING[0];
        $this->wordpressPostStub->post_name = $firstPageToDisableEditing;
        $this->setInput('get', ['post' => 'test']);
        $this->whitelabelStub->type = Whitelabel::TYPE_V1;

        $this->applyFiltersMock->expects($this->any())
            ->willReturn(1);

        $this->isSuperAdminMock->expects($this->any())
            ->willReturn(false);

        $this->wordpressGetCurrentUserMock->expects($this->any())
            ->willReturn($this->wordpressUserStub);

        $this->getPostMock->expects($this->any())
            ->willReturn($this->wordpressPostStub);

        $this->removePostTypeSupportMock->expects($this->any())
            ->withConsecutive(
                ['page', 'editor'],
                ['page', 'title'],
            );

        PageEditorHelper::disablePagesEditor();
    }

    /** @test */
    public function disablePagesEditor_disablePermalinkEditor(): void
    {
        $firstPageToDisableEditing = PageEditorHelper::PAGES_SLUGS_TO_DISABLE_EDITING[0];
        $this->wordpressPostStub->post_name = $firstPageToDisableEditing;
        $this->setInput('get', ['post' => 'test']);
        $this->whitelabelStub->type = Whitelabel::TYPE_V1;

        $this->applyFiltersMock->expects($this->any())
            ->willReturn(1);

        $this->isSuperAdminMock->expects($this->any())
            ->willReturn(false);

        $this->wordpressGetCurrentUserMock->expects($this->any())
            ->willReturn($this->wordpressUserStub);

        $this->getPostMock->expects($this->any())
            ->willReturn($this->wordpressPostStub);

        $this->addFilterMock->expects($this->any())
            ->with('get_sample_permalink_html', fn() => '', 10, 0);

        PageEditorHelper::disablePagesEditor();
    }

    /** @test */
    public function disablePagesEditor_disableSlugAndPageAttributeEditor(): void
    {
        $firstPageToDisableEditing = PageEditorHelper::PAGES_SLUGS_TO_DISABLE_EDITING[0];
        $this->wordpressPostStub->post_name = $firstPageToDisableEditing;
        $this->setInput('get', ['post' => 'test']);
        $this->whitelabelStub->type = Whitelabel::TYPE_V1;

        $this->applyFiltersMock->expects($this->any())
            ->willReturn(1);

        $this->isSuperAdminMock->expects($this->any())
            ->willReturn(false);

        $this->wordpressGetCurrentUserMock->expects($this->any())
            ->willReturn($this->wordpressUserStub);

        $this->getPostMock->expects($this->any())
            ->willReturn($this->wordpressPostStub);

        $this->removeMetaBoxMock->expects($this->any())
            ->withConsecutive(
                ['pageparentdiv', 'page', 'side'],
                ['slugdiv', 'page', 'side']
            );

        PageEditorHelper::disablePagesEditor();
    }
}
