<?php

use Exceptions\SocialMedia\IncorrectAdapterException;
use Exceptions\SocialMedia\IncorrectTypeException;
use Exceptions\SocialMedia\WhitelabelDoesNotUseSocialConnectException;
use Fuel\Core\Session;
use Helpers\SocialMediaConnect\ConnectHelper;
use Helpers\SocialMediaConnect\LastStepsHelper;
use Helpers\UrlHelper;
use Services\Logs\FileLoggerService;
use Services\SocialMediaConnect\AdapterFactory;
use Services\SocialMediaConnect\LastStepsService;
use Services\SocialMediaConnect\MessageHelper;

if (!defined('WPINC')) {
    die;
}

get_header();

try {
    $socialType = LastStepsHelper::getSocialType();
    $adapterFactory = Container::get(AdapterFactory::class);
    $socialAdapter = $adapterFactory->createAdapter($socialType);
    /** @var LastStepsService $lastStepsService */
    $lastStepsService = Container::make(LastStepsService::class, ['socialAdapter' => $socialAdapter]);
    $lastStepsService->startSocialMediaAccountIntegration();
} catch (WhitelabelDoesNotUseSocialConnectException|IncorrectAdapterException|IncorrectTypeException) {
    Session::set('message', ['error', MessageHelper::getTranslatedSecureError()]);
    UrlHelper::redirectToHomepage();
} catch (Throwable $exception) {
    $fileLoggerService = Container::get(FileLoggerService::class);
    $fileLoggerService->error($exception->getMessage());
    ConnectHelper::setSecurityError();
    UrlHelper::redirectToSignUpPage();
}

?>
    <div class="content-area">
        <div class="main-width content-width">
            <div class="content-box">
                <section class="page-content">
                    <article class="page">
                        <h1><?php the_title(); ?></h1>
                        <?php the_content(); ?>
                    </article>
                    <?php
                        try {
                            echo lotto_platform_register_box();
                        } catch (Throwable $exception) {
                            $fileLoggerService = Container::get(FileLoggerService::class);
                            $fileLoggerService->error('Function register box not exists' . $exception->getMessage());
                        }
                    ?>
                </section>
            </div>
        </div>
    </div>

<?php
get_footer();
