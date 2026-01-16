<?php

namespace Test\Selenium\Login;

use Fuel\Core\Config;
use Model_Whitelabel_User;
use Facebook\WebDriver\WebDriverBy;
use Test\Selenium\Interfaces\CrmInterface;
use Test\Selenium\Interfaces\UserInterface;
use Test\Selenium\Abstracts\AbstractSelenium;
use Facebook\WebDriver\WebDriverExpectedCondition;

class SeleniumLoginService extends AbstractSelenium implements UserInterface, CrmInterface
{
    public const MANAGER_LOGIN_URL = "https://manager.lottopark.loc";
    public const MANAGER_SUPERUSER_LOGIN = "blacklotto";
    public const MANAGER_SUPERUSER_PASSWORD = "blacklottopassword";
    public const MANAGER_LOTTOPARK_LOGIN = "lottopark";
    public const MANAGER_LOTTOPARK_PASSWORD = "lottoparkpassword";

    const PASSWORD_INPUT = [
        'loginId' => 'inputLogin',
        'passwordId'  => 'inputLoginPassword',
        'loginValue' => self::TEST_USER_EMAIL,
        'passwordValue' => self::TEST_USER_PASSWORD,
        'submitSelector' => 'div.platform-form-btn'
    ];

    public function loginUser(string $loginEmail = self::TEST_USER_EMAIL, string $password = self::TEST_USER_PASSWORD): void
    {
        $url = self::LOGIN_URL;
        $credentials = array_replace(
            self::PASSWORD_INPUT,
            ['loginValue' => $loginEmail, 'passwordValue' => $password]
        );

        self::login($url, $credentials);
    }

    public function loginUserWithWrongPassword(): void
    {
        $url = self::LOGIN_URL;
        $loginData = array_replace(self::PASSWORD_INPUT, ['passwordValue' => 'bad password']);
        self::login($url, $loginData);
    }

    public function loginUserWithWrongEmail(): void
    {
        $url = self::LOGIN_URL;
        $loginData = array_replace(self::PASSWORD_INPUT, ['loginValue' => 'bad@email.com']);
        self::login($url, $loginData);
    }

    /** Login to superuser account */
    public function loginManagerSuperadmin(): void
    {
        $loginData = [
            'loginValue' => self::MANAGER_SUPERUSER_LOGIN,
            'passwordValue' => self::MANAGER_SUPERUSER_PASSWORD,
        ];
        self::loginManagerLottoparkLoc($loginData);
    }

    /** Login to example whitelabel account: lottopark */
    public function loginManagerLottopark(): void
    {
        $loginData = [
            'loginValue' => self::MANAGER_LOTTOPARK_LOGIN,
            'passwordValue' => self::MANAGER_LOTTOPARK_PASSWORD,
        ];
        self::loginManagerLottoparkLoc($loginData);
    }

    /** Login wrapper to manager.lottopark.loc */
    private function loginManagerLottoparkLoc(array $loginData): void
    {
        $passDefault = [
            'loginId' => 'inputLogin',
            'passwordId'  => 'inputPassword',
            'submitSelector' => 'body > div > form > button'
        ];
        $passDefault += $loginData;
        self::login(self::MANAGER_LOGIN_URL, $passDefault);
    }

    /** Login to admin.whitelotto with an example superadmin account */
    public function loginCrmSuperadmin(): void
    {
        $loginData = [
            'login_value' => self::CRM_SUPERADMIN_LOGIN,
            'password_value' => self::CRM_SUPERADMIN_PASSWORD
        ];
        self::loginCrmUser($loginData);
    }

    /**
     * Login wrapper to admin.whitelotto.loc
     *
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeoutException
     */
    private function loginCrmUser(array $loginFormButtonPath): void
    {
        $this->driver->get(self::CRM_LOGIN_URL);
        $this->driver->wait()->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath('//*[@id="loginform"]/div[4]/div/button')
            )
        );
        $passDefault = [
            'login_selector' => 'input[type="text"]',
            'password_selector'  => 'input[type="password"]',
            'submit_selector' => '#loginform > div.form-group.text-center > div > button'
        ];

        $passDefault += $loginFormButtonPath;

        $this->driver->findElement(WebDriverBy::cssSelector($passDefault['login_selector']))->sendKeys($passDefault['login_value']);
        $this->driver->findElement(WebDriverBy::cssSelector($passDefault['password_selector']))->sendKeys($passDefault['password_value']);
        $this->driver->findElement(WebDriverBy::cssSelector($passDefault['submit_selector']))->click();
        $this->driver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::xpath('//*[@id="main-wrapper"]/header/nav/div[1]/div[2]/a/span/img')
            )
        );
    }

    /** Login to any page */
    private function login(string $url, array $loginData): void
    {
        $this->driver->get($url);
        $this->driver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::id($loginData['loginId'])
            )
        );
        $this->driver->findElement(WebDriverBy::id($loginData['loginId']))->sendKeys($loginData['loginValue']);
        $this->driver->findElement(WebDriverBy::id($loginData['passwordId']))->sendKeys($loginData['passwordValue']);
        $this->driver->findElement(WebDriverBy::cssSelector($loginData['submitSelector']))->click();
    }

    public function registerUserToWhitelabel(
        bool $inputEmail,
        bool $inputPassword,
        bool $confirmPassword,
        bool $acceptTermsPolicy,
        string $email = self::TEST_USER_EMAIL
    ): void {
        if ($inputEmail) {
            //here, after doing another more than 1 test, it may cause an error by the existence of a user about this email in the DB.
            $this->driver->findElement(WebDriverBy::id("inputEmail"))->sendKeys($email);
        }
        if ($inputPassword) {
            $this->driver->findElement(WebDriverBy::id("inputPassword"))->sendKeys(self::TEST_USER_PASSWORD);
        }
        if ($confirmPassword) {
            $this->driver->findElement(WebDriverBy::id("inputRPassword"))->sendKeys(self::TEST_USER_PASSWORD);
        }
        if ($acceptTermsPolicy) {
            $this->driver->findElement(WebDriverBy::xpath("/html/body/div[1]/div/div/section/form/div[5]/label/input"))->click();
        }
        $this->driver->findElement(WebDriverBy::xpath("/html/body/div[1]/div/div/section/form/div[7]/button"))->click();
    }

    public function logout()
    {
        $this->driver->get(self::MANAGER_LOGIN_URL . "/signout");
    }

    public static function getTestUserModel($email = self::TEST_USER_EMAIL): Model_Whitelabel_User
    {
        return Model_Whitelabel_User::find_by(["email" => $email])[0];
    }

    private static function appUrl(string $path = null): string
    {
        return Config::get("test.selenium.app_url") . $path;
    }
}
