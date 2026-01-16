<?php

namespace Test\Selenium\Interfaces;

interface PowerballInterface
{
    public const PLAY_URL = 'https://lottopark.loc/play/powerball/';
    public const RESULTS_URL = 'https://lottopark.loc/results/powerball/';
    public const INFORMATION_URL = 'https://lottopark.loc/lotteries/powerball/';
    public const LOTTOPARK_ACTIVE_COLOUR = 'rgba(65, 147, 76, 1)';
    public const LOTTOPARK_INACTIVE_COLOUR = 'rgba(255, 255, 255, 1)';
    public const LOTTOPARK_PLAYPAGE_QUICKPICK = 'body > div.content-area > div.widget.widget_lotto_platform_widget_ticket > div > div > div > div.widget-ticket-header-wrapper > div.widget-ticket-buttons-all > div > button.btn.btn-secondary.widget-ticket-quickpick-all';
    public const LOTTOPARK_PLAYPAGE_MORELINES = '#widget-ticket-form > div > div.widget-ticket-button-wrapper > div.widget-ticket-buttons-bottom > div.pull-left > button.btn.btn-sm.btn-tertiary.widget-ticket-button-more';
    public const LOTTOPARK_PLAYPAGE_LESSLINES = '#widget-ticket-form > div > div.widget-ticket-button-wrapper > div.widget-ticket-buttons-bottom > div.pull-left > button.btn.btn-sm.btn-tertiary.widget-ticket-button-less';
    public const LOTTOPARK_PLAYPAGE_BIN = 'body > div.content-area > div.widget.widget_lotto_platform_widget_ticket > div > div > div > div.widget-ticket-header-wrapper > div.widget-ticket-buttons-all > div > button.btn.btn-secondary.widget-ticket-clear-all';
    public const LOTTOPARK_PLAYPAGE_IMAGE = 'body > div.content-area > div.widget.widget_lotto_platform_widget_ticket > div > div > div > div.widget-ticket-header-wrapper > div.widget-ticket-image > img';
    public const LOTTOPARK_PLAYPAGE_JACKPOT = 'body > div.content-area > div.widget.widget_lotto_platform_widget_ticket > div > div > div > div.widget-ticket-header-wrapper > div.widget-ticket-header > div > span';
    public const LOTTOPARK_RESULTPAGE_DATESELECTOR = '#content-results-lottery > div > div.main-width.content-width > div > div.content-box-main > div.results-short-content > nav > select';
    public const LOTTOPARK_RESULTPAGE_WINNINGSNUMBERS = '#content-results-lottery > div > div.main-width.content-width > div > div.content-box-main > div.results-short-content > div.pull-left.results-short-line > div';
    public const LOTTOPARK_RESULTPAGE_JACKPOT = '#content-results-lottery > div > div.main-width.content-width > div > div.content-box-main > div.results-short-content > div.pull-left.results-short-jackpot';
    public const LOTTOPARK_INFORMATIONPAGE_LATESTRESULT = 'body > div.content-area > div.main-width.content-width > div > div.content-box-main > div.info-short-content > table > tbody > tr:nth-child(2) > td';
    public const POWERBALL_NUMBERS = 69;
    public const POWERBALL_BNUMBERS = 26;
    public const PLAY_CONTINUE = '#play-continue';
}