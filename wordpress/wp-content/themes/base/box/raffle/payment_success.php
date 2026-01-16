<div class="success-content">
    <table class="table table-success raffle-order-summary">
        <tbody>
            <?php

            use Helpers\UrlHelper;

            $lottery_image = Lotto_View::get_lottery_image($raffle_ticket['raffle_id']);

                $play_info_href = lotto_platform_get_permalink_by_slug('raffle/play-2') . $raffle_ticket['slug'];
                $play_text = sprintf(
                    _('<a href="%s">%s</a> '),
                    $play_info_href,
                    _($raffle_ticket['name'])
                );
                $allowed_html_play = array("a" => array("href" => array()));

                $mobile_hide_text = sprintf(_("%s to win"), "€" . $raffle_ticket['main_prize']);
                $allowed_html_mobile = array(
                    "span" => array(
                        "class" => array(),
                        "aria-hidden" => array(),
                        "data-tooltip" => array()
                    )
                );

                $amount_value = Lotto_View::format_currency(
                    $raffle_ticket['amount'],
                    $currencies[$raffle_ticket['currency_id']]['code'],
                    true
                );

                $lines_text = sprintf(
                    _("Choosen lines (%s):"),
                    $raffle_ticket['line_count']
                );
                $allowed_html_order = array(
                    "span" => array(),
                    "strong" => array()
                );
            ?>
                <tr>
                    <td class="order-summary-image-wrapper">
                        <div class="order-summary-image">
                            <img src="<?= UrlHelper::esc_url($lottery_image); ?>"
                                 alt="<?= Security::htmlentities(_($raffle_ticket['name'])); ?>">
                        </div>
                    </td>
                    <td>
                        <div class="order-summary-content">
                                        <span class="order-summary-content-header">
                                        <?= wp_kses($play_text, $allowed_html_play); ?>
                                        </span><span class="mobile-hide"> - </span>
                            <?= wp_kses($mobile_hide_text, $allowed_html_mobile); ?>
                            <br class="mobile-hide" />
                            <span class="mobile-only">, </span>
                            <span class="order-summary-content-desc">
                                <span class="raffle-draw-summary"><?= _("Draw number: <strong>1</strong> (10.12.2019, 14:00)") ?></span>
                                <br />
                                <?= wp_kses($lines_text, $allowed_html_order); ?>
                                <br />
                                <div class="widget-ticket-numbers">
                                <?php foreach ($lines as $line): ?>
                                    <div class="raffle-number"><?= str_pad(strval($line['number']), 4, "0", STR_PAD_LEFT) ?></div>
                                <?php endforeach; ?>
                                </div>
                            </span>
                            <br>
                        </div>
                    </td>
                    <td class="text-right col-amount">
                        <?= Security::htmlentities($amount_value); ?>
                    </td>
                </tr>
        </tbody>
    </table>
</div>