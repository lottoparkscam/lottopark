<?php

namespace Services;

use Container;
use Exception;
use Fuel\Core\Validation;
use Lotto_Security;
use Model_Whitelabel;
use DateTime;
use DateTimeZone;
use Models\WhitelabelAff;
use Repositories\Aff\WhitelabelAffRepository;
use Services\Logs\FileLoggerService;

class AffAuthService
{
    private MailerService $mailer;

    private ?WhitelabelAff $aff;

    private WhitelabelAffRepository $whitelabelAffRepository;
    private FileLoggerService $fileLoggerService;

    private ?array $whitelabel = null;

    private ?string $newHash = null;

    private ?string $newSalt = null;

    private ?string $newTimeHash = null;

    /** @throws Exception when recaptcha is invalid */
    private function validateRecaptcha(): void
    {
        if (!Lotto_Security::check_captcha()) {
            throw new Exception('Incorrect captcha! Please try again.');
        }
    }

    public function __construct()
    {
        $this->mailer = Container::get(MailerService::class);
        $this->whitelabelAffRepository = Container::get(WhitelabelAffRepository::class);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->findAndSetWhitelabel();
    }

    private function generateNewSalt(): void
    {
        $this->newSalt = Lotto_Security::generate_salt();
    }

    private function generateNewHash(string $password): void
    {
        $this->newHash = Lotto_Security::generate_hash($password, $this->newSalt);
    }

    private function generateNewTimeHash(): void
    {
        $now = new DateTime("now", new DateTimeZone("UTC"));
        if (empty($this->newTimeHash)) {
            $this->newTimeHash = Lotto_Security::generate_time_hash(
                $this->aff->salt,
                $now
            );
        }
    }

    /** @throws Exception when recaptcha is invalid */
    public function processPasswordResetForm()
    {
        $this->validateRecaptcha();

        $validate = $this->prepareValidationResetPassword();
        if ($validate->run()) {
            $inputEmail = $validate->validated('input.email');
            $this->findAndSetAffiliateByEmail($inputEmail, $this->whitelabel['id']);

            if (!$this->aff) {
                throw new Exception('Account with the given e-mail address hasn\'t been found.');
            }

            $this->generateNewTimeHash();
            $this->updateAffiliatePasswordResetHash();

            try {
                $this->sendResetPasswordMail($inputEmail);
            } catch (Exception $e) {
                $this->fileLoggerService->error(
                    $e->getMessage()
                );
                throw new Exception('Please try again later.');
            }
        } else {
            throw new Exception('Invalid email address format.');
        }
    }

    /** @throws Exception when recaptcha is invalid */
    public function processNewPasswordForm(string $hash): void
    {
        $validatedForm = $this->prepareValidationNewPassword();

        if (!$this->whitelabel) {
            throw new Exception('Please try again later.');
        }

        $this->validateRecaptcha();

        if ($validatedForm->run()) {
            $affiliate = $this->whitelabelAffRepository->findAffiliateByPasswordResetHash($hash, $this->whitelabel['id']);

            if (!$affiliate) {
                throw new Exception('Incorrect reset password link.');
            }

            $this->generateNewSalt();
            $this->generateNewHash($validatedForm->validated('password'));

            $this->saveNewPassword($affiliate);
        } else {
            $error = $validatedForm->error()['password'] ?? $validatedForm->error()['repeatPassword'];
            throw new Exception($error->get_message());
        }
    }

    private function saveNewPassword(WhitelabelAff $affiliate): void
    {
        $affiliate->salt = $this->newSalt;
        $affiliate->hash = $this->newHash;
        $affiliate->passwordResetHash = null;
        $affiliate->save();
    }

    private function prepareValidationResetPassword(): Validation
    {
        $validator = Validation::forge();
        $validator
            ->add('input.email', _('E-mail'))
            ->add_rule('required')
            ->add_rule('valid_email');

        return $validator;
    }

    private function prepareValidationNewPassword(): Validation
    {
        $validator = Validation::forge();
        $validator
            ->add('password', _('Password'))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('min_length', 6);

        $validator
            ->add('repeatPassword', _('Repeat password'))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('min_length', 6)
            ->add_rule('match_field', 'password');

        return $validator;
    }

    private function preparePasswordResetLink(): string
    {
        $resetPasswordSlug = 'https://aff.' . $this->whitelabel['domain'] . '/password/lost/';

        return $resetPasswordSlug . $this->newTimeHash . '/';
    }

    private function prepareMailTemplate(): string
    {
        $resetPasswordLink = $this->preparePasswordResetLink();

        $emailTemplate = <<< 'HTML'
            <table width="100%">
                <tbody>
                    <tr>
                        <td style="line-height: 150%; font-size: 19px; font-family: Roboto, Arial; color: #505050;" align="left">
                            We have received a password reset request for your affiliate account. 
                            To complete the process please follow the link:
                        </td>
                    </tr>
                </tbody>
            </table>
            <table width="100%" align="center">
                <tbody>
                <tr>
                    <td style="padding-top: 30px;">
                        <a href="{resetPasswordLink}" style="color:#000;font-size: 18px;font-family:Arial" target="_blank">
                            Reset password
                        </a>
                    </td>
                </tr>
                </tbody>
            </table>
            <table width="100%">
                <tbody>
                <tr>
                    <td style="line-height: 150%; font-size: 19px; font-family: Roboto, Arial; color: #505050;padding-top:35px;" align="left">
                        Please ignore this e-mail if you did not request a password reset.
                    </td>
                </tr>
                </tbody>
            </table>
        HTML;

        $emailTemplate = str_replace('{resetPasswordLink}', $resetPasswordLink, $emailTemplate);

        return $emailTemplate;
    }

    private function sendResetPasswordMail(string $email): void
    {
        $emailTitle = 'Affiliate Panel - Forgotten Password';
        $emailTemplate = $this->prepareMailTemplate();

        $this->mailer->sendPublic(
            $email,
            $emailTitle,
            $emailTemplate,
            $this->whitelabel['name'],
            true
        );
    }

    private function updateAffiliatePasswordResetHash(): void
    {
        try {
            $this->whitelabelAffRepository->updatePasswordResetHash($this->aff->id, $this->newTimeHash);
        } catch (Exception $exception) {
            $this->fileLoggerService->error(
                $exception->getMessage()
            );
        }
    }

    private function findAndSetAffiliateByEmail(string $email, int $whitelabelId): void
    {
        $this->aff = $this->whitelabelAffRepository->findAffiliateByEmail($email, $whitelabelId);
    }

    private function findAndSetWhitelabel(): void
    {
        $domain = $_SERVER['HTTP_HOST'];
        $domain = explode('.', $domain);
        if ($domain[0] == "aff") {
            array_shift($domain);
        } else {
            return;
        }

        $domain = implode('.', $domain);

        $this->whitelabel = Model_Whitelabel::get_by_domain($domain);
    }
}
