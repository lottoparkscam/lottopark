<?php

namespace Services\WordpressApi\Internal;

class Widget
{
    public function generate()
    {
        global $sitepress;

        $widget = \Input::get('widget');
        $id = \Input::get('id');
        $lottery1 = \Input::get('lottery1');
        $lottery2 = \Input::get('lottery2');
        $lottery3 = \Input::get('lottery3');

        if (!empty($widget) && !empty($lottery1) && !empty($id)) {
            // Set javascript headers
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding");
            header('Content-Type: application/javascript');

            $preview = \Input::get('preview');

            // Load lottery info by slug
            $ext_lottery1 = lotto_platform_get_lottery_by_slug($lottery1);

            if (!$ext_lottery1 || empty($ext_lottery1['slug'])) {
                echo "/* " . _("Lottery doesn't exist") . " */";
                exit();
            }

            $lotteries[] = $ext_lottery1;

            if (!empty($lottery2) || !empty($lottery3)) {
                $ext_lottery2 = lotto_platform_get_lottery_by_slug($lottery2);
                $ext_lottery3 = lotto_platform_get_lottery_by_slug($lottery3);

                if (
                    !$ext_lottery2 ||
                    !$ext_lottery3 ||
                    empty($ext_lottery2['slug']) ||
                    empty($ext_lottery3['slug'])
                ) {
                    echo "/* " . _("Lottery doesn't exist") . " */";
                    exit();
                }

                $lotteries[] = $ext_lottery2;
                $lotteries[] = $ext_lottery3;
            }

            $lang = $sitepress->get_current_language();

            // Get translated text
            $translated_jackpot = _("next jackpot");
            $translated_play = _("Play now");

            $translations = [
                'lang_code' => ($lang != null) ? $lang : get_locale(),
                'nearest_jackpot' => $translated_jackpot,
                'play_now' => $translated_play,
            ];

            // Create widget
            $obj = new \Banners_Widgets();
            $view = $obj->create_widget($lotteries, $lang, $translations, $id)->render();

            $uniq_function_name = md5($id . uniqid());

            // Get custom widget width
            $check_width = \Input::get('width');

            if (ctype_digit($check_width)) {
                $custom_width = $check_width . 'px';
            } else {
                $custom_width = "100%";
            }

            // Generate javascript code
            if ($preview != "true") {
                echo 'window.addEventListener("load", function() {';
            }

            echo "
                function checkWidget" . $uniq_function_name . "() {
                    var id = '" . $id . "';
                    var view = `" . $view . "`;

                    var widgetElement = document.getElementById(id);

                    widgetElement.innerHTML = view;
                    widgetElement.style.width = '" . $custom_width . "';
                    widgetElement.style.display = 'table';
                }

                function checkWidgetSize" . $uniq_function_name . "() {
                        var id = '" . $id . "';

                        var widgetElement = document.getElementById(id);

                        var parentWidth = widgetElement.parentElement.clientWidth;
                        var widgetContent = document.getElementById('widget-content-'+id);

                        widgetContent.classList.remove('widget-one-lottery', 'widget-two-lotteries');
                        if (parentWidth < 480) {
                            widgetContent.classList.add('widget-one-lottery');
                        } else if (parentWidth < 800) {
                            widgetContent.classList.add('widget-two-lotteries');
                        }
                };

                if (document.getElementById('" . $id . "')) {
                    checkWidget" . $uniq_function_name . "();

                    checkWidgetSize" . $uniq_function_name . "();

                    window.addEventListener('resize', function () {checkWidgetSize" . $uniq_function_name . "();});
                }
            ";

            if ($preview != "true") {
                echo "}, false);";
            }

            exit;
        }
    }
}