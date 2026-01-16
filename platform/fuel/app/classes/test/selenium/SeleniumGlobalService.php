<?php

namespace Test\Selenium;

use DB;
use Container;
use Fuel\Core\Input;
use Models\WhitelabelUser;
use Models\WhitelabelTransaction;
use Facebook\WebDriver\WebDriverBy;
use Test\Selenium\Abstracts\AbstractSelenium;
use Repositories\Orm\WhitelabelUserRepository;
use Repositories\WhitelabelTransactionRepository;
use Facebook\WebDriver\WebDriverExpectedCondition;

class SeleniumGlobalService extends AbstractSelenium
{
    /**
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeoutException
     */
    public function waitForElementToBeClickableAndClick(string $selector): void
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector($selector)));
        $element = $this->driver->findElement(WebDriverBy::cssSelector($selector));
        $element->click();
    }

    public function convertElementsTextValuesToArray(string $selector): array
    {
        $values = [];
        $elements = $this->driver->findElements(WebDriverBy::cssSelector($selector));
        foreach ($elements as $element) {
            $values[] = $element->getText();
        }

        return $values;
    }

    public function countElements(string $selector): int
    {
        return count($this->driver->findElements(WebDriverBy::cssSelector($selector)));
    }

    public function getTestUserModel(string $email = "test@user.loc", int $whitelabelId = 1): WhitelabelUser
    {
        /** @var WhitelabelUserRepository $whitelabelUserRepository */
        $whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        return $whitelabelUserRepository->findEnabledUserByEmail($email, $whitelabelId);
    }

    public function updateBalanceByEmail(string $email, int $balance): void
    {
        $balanceUpdateQuery = DB::query(
            "UPDATE whitelabel_user 
            SET balance = :balance
            WHERE whitelabel_user.email = :email"
        );
        $balanceUpdateQuery->param(":balance", $balance);
        $balanceUpdateQuery->param(":email", $email);
        $balanceUpdateQuery->execute();
    }

    public static function getLotteryUrl(): string
    {
        return Input::server('SITE_URL');
    }

    public function getTransactionByUserEmail(string $userEmail): WhitelabelTransaction
    {
        $user = WhitelabelUser::find('last', [
            'where' => [
                'email' => $userEmail
            ]
        ]);

        $userTransaction = WhitelabelTransaction::find('last', [
            'where' => [
                'whitelabel_user_id' => $user['id']
            ]
        ]);

        return $userTransaction;
    }

    public function clickElement(string $cssSelector): void
    {
        $this->driver->findElement(WebDriverBy::cssSelector($cssSelector))->click();
    }

    public function getTransactionById(string $transactionId): WhitelabelTransaction
    {
        /** @var WhitelabelTransactionRepository $whitelabelTransactionRepository */
        $transactionRepository = Container::get(WhitelabelTransactionRepository::class);
        return $transactionRepository->findOneById($transactionId);
    }

    public function getTextByCssSelector(string $cssSelector): string
    {
        return $this->driver->findElement(WebDriverBy::cssSelector($cssSelector))->getText();
    }
}
