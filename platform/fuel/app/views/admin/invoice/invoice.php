<?php

use Carbon\Carbon;
use Fuel\Core\Asset;
use Helpers\empire\InvoiceHelper;
use Helpers\FlashMessageHelper;
use Models\Whitelabel;
use Repositories\WhitelabelRepository;
use Services\GgrService;

$isNotSetParameter = !isset($_GET['paymentSum'],
    $_GET['bonusSum'],
    $_GET['incomeSum'],
    $_GET['royaltiesSum'],
    $_GET['dateEnd'],
    $_GET['whitelabelId']);

if ($isNotSetParameter) {
    header('Location: ' . 'https://' . $_SERVER['HTTP_HOST'], true, 301);
    exit();
}

/** @var Whitelabel $whitelabel */
$whitelabelId = $_GET['whitelabelId'];
$whitelabelRepository = Container::get(WhitelabelRepository::class);
$whitelabel = $whitelabelRepository->findOneById($whitelabelId);
$isV1 = $whitelabel->isV1();

$currency = mb_substr($_GET['paymentSum'], 0, 1) === '-' ? mb_substr($_GET['paymentSum'], 1, 1) : mb_substr($_GET['paymentSum'], 0, 1);
$currencyContainsLetter = ctype_alpha($currency);
if ($currencyContainsLetter) {
    $currency = mb_substr($_GET['paymentSum'], 0, 1) === '-' ? mb_substr($_GET['paymentSum'], 1, 3) : mb_substr($_GET['paymentSum'], 0, 3);
}
$paymentSum = str_replace([$currency , ','], '', $_GET['paymentSum']);
$bonusSum = str_replace([$currency , ','], '', $_GET['bonusSum']);
$incomeSum = (float) str_replace([$currency , ','], '', $_GET['incomeSum']);
$royaltiesSum = (float) str_replace([$currency , ','], '', $_GET['royaltiesSum']);

$date = Carbon::parse($_GET['dateEnd']);
$dateEnd = $date->format('d-m-Y');
$billingMonthWithYear = $date->format('m-Y');
$billingMonth = $date->format('m');
$billingYear = $date->format('Y');

$GgrService = Container::get(GgrService::class);
$additionalIncome = $GgrService->getCalculatedGgrIncomeForMonthPerWhitelabel($whitelabelId, $billingMonth, $billingYear);
$incomeSum = InvoiceHelper::calculateIncome($incomeSum, $additionalIncome['income']);
$royaltiesSum = InvoiceHelper::calculateRoyalties($royaltiesSum, $additionalIncome['royalties']);

$error = FlashMessageHelper::getLast();
?>
<div id="invoice" class="invoice">
    <div class="invoice-nav">
        <div class="first-section-invoice">
            <img class="wl-logo" src="/assets/images/crm/logo-icon.png">
            <p class="V2-extra-label">lottery white label software </p>
            <?= InvoiceHelper::generateBilledToSection($isV1, $whitelabel->domain); ?>
        </div>

        <div class="second-section-invoice">
            <h2 class="invoice-title"><?= $isV1 ? 'Monthly Statement' : 'Invoice' ?></h2>
            <p><b>Monthly Statement No :</b> <?= $whitelabel->prefix . '-' . $billingMonthWithYear ?> </p>
            <p><b>Date :</b> <?= Carbon::now()->format('d-m-Y') ?></p>
            <p><b>Customer ID :</b> <?= $whitelabel->domain ?></p>
        </div>
    </div>
    <div class="main-section-invoice">
        <div id="container-edit" class="invoice-edit">
            <button id="edit" class="btn btn-primary" type="button" value="off">Edit</button>
        </div>
        <div class="nav-main-section-invoice">
            <div class="blue-column-invoice"><b>Service</b></div>
            <div class="blue-column-invoice"><b>Summary</b></div>
            <div class="blue-column-invoice"><b>Total</b></div>
        </div>

        <div id="main-section-invoice">
        <?php if ($isV1): ?>
            <div class="row-invoice">
                <input class="grey-column-invoice" value="Income" disabled>
                <input class="grey-column-invoice" value="<?= $billingMonthWithYear ?>" disabled>
                <input class="grey-column-invoice" value="<?= $incomeSum ?>" type="number" disabled>
                <div class="delete-row closebtn">&times;</div>
            </div>

            <div class="row-invoice">
                <input class="grey-column-invoice" value="Royalties" disabled>
                <input class="grey-column-invoice" value="<?= $billingMonthWithYear ?>" disabled>
                <input class="grey-column-invoice" value="<?= $royaltiesSum ?>" type="number" disabled>
                <div class="delete-row closebtn">&times;</div>
            </div>

            <div class="row-invoice">
                <input class="grey-column-invoice" value="Bonus" disabled>
                <input class="grey-column-invoice" value="<?= $billingMonthWithYear ?>" disabled>
                <input class="grey-column-invoice" value="<?= $bonusSum ?>" type="number" disabled>
                <div class="delete-row closebtn">&times;</div>
            </div>

            <div class="row-invoice">
                <input class="grey-column-invoice" value="Payment Services" disabled>
                <input class="grey-column-invoice" value="<?= $billingMonthWithYear ?>" disabled>
                <input class="grey-column-invoice" value="<?= $paymentSum ?>" type="number" disabled>
                <div class="delete-row closebtn">&times;</div>
            </div>
            <?php else: ?>
            <div class="row-invoice">
                <input class="grey-column-invoice" value="Royalties" disabled>
                <input class="grey-column-invoice" value="<?= $billingMonthWithYear ?>" disabled>
                <input class="grey-column-invoice" value="<?= $royaltiesSum ?>" type="number" disabled>
                <div class="delete-row closebtn">&times;</div>
            </div>
            <?php endif; ?>
            <div class="row-invoice">
                <input class="grey-column-invoice" value="VPN" disabled>
                <input class="grey-column-invoice" value="<?= $billingMonthWithYear ?>" disabled>
                <input class="grey-column-invoice" value="35.00" type="number" disabled>
                <div class="delete-row closebtn">&times;</div>
            </div>
            <div class="row-invoice">
                <input class="grey-column-invoice" value="E-mail" disabled>
                <input class="grey-column-invoice" value="<?= $billingMonthWithYear ?>" disabled>
                <input class="grey-column-invoice" value="35.00" type="number" disabled>
                <div class="delete-row closebtn">&times;</div>
            </div>
            <div class="row-invoice">
                <input class="grey-column-invoice" value="SEPA Pay-out" disabled>
                <input class="grey-column-invoice" value="<?= $billingMonthWithYear ?>" disabled>
                <input class="grey-column-invoice" value="25.00" type="number" disabled>
                <div class="delete-row closebtn">&times;</div>
            </div>
        </div>
    </div>
    <div class="subtotal">
        <span>subtotal</span>
        <div id="amount" class="subtotal-sum">0,00</div> <?= $currency ?>
    </div>
    <div class="statement-close">
        Statement close day [<?= $dateEnd ?>]
    </div>
    <div id="new-row">
        <div id="new-row-alert">
            <?php if (!empty($error)): ?>
                <div class="alert">
                    <div><?= $error ?></div>
                    <div class="closebtn">&times;</div>
                </div>
            <?php endif; ?>
        </div>
        <form class="form-inline">
            <label>
                Add new row:
                <input id="services" class="form-control" placeholder="Services">
                <input id="summary" class="form-control" placeholder="Summary" value="<?= $billingMonthWithYear ?>">
                <input id="total" class="form-control" placeholder="Total (only numbers)" type="number">
            </label>
            <button id="add-row" class="btn btn-primary" type="button">Add</button>
        </form>
    </div>
    <div class="print">
        <button id="print-button" class="btn btn-primary" type="button">Print</button>
    </div>
</div>

<script>window.isV1 = <?= (int)$isV1 ?></script>
<?= Asset::js("InvoiceGenerator.min.js") ?>
