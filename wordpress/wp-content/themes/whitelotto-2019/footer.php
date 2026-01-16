<?php
if (!defined('WPINC')) {
    die;
}
?>
<footer class="container-fluid footer">
    <div class="container">
        <div class="row no-gutters justify-content-between">
            <div class="col-md col-12 text-center text-md-left">
                Our offices:
                <p>
                    UK - 19 Crundale Tower, Tintagel Road, Orpington, Kent, England, BR5 4LE<br />
                    Curaçao - White Lotto B.V., Fransche Bloemweg 4<br />
                    Cyprus - White Lto Limited, Voukourestiou, 25 Neptun House, 1st floor, Flat/Office 11, Zakaki,3045, Limassol,<br />
                    BVI - GG International Limited, Trident Chambers, Road Town, Tortola<br />
                    Poland - T2G Europe, Wojska Polskiego 8, 41-208 Sosnowiec<br />
                </p>
            </div>
            <div class="col contact row justify-content-md-end justify-content-center align-items-center mt-2 mt-xs-5 mt-md-0">
                <a href="https://whitelotto.com/contact/" class="contact-circle">
                    <i class="fas fa-envelope"></i>
                </a>
                <a href="https://t.me/tnigg" target="_blank" class="contact-circle">
                    <i class="fab fa-telegram"></i>
                </a>
                <a href="skype:live:8f92c955cbf8e9c3?call" class="contact-circle">
                    <i class="fab fa-skype"></i>
                </a>
                <a href="https://wa.me/48570059652" target="_blank" class="contact-circle">
                    <i class="fab fa-whatsapp"></i>
                </a>
            </div>
        </div>
    </div>
</footer>
<div class="container articles-nav text-center">
    <a href="https://whitelotto.com/how-to-start-lottery-business/">How to start lottery business</a>
    <a href="https://whitelotto.com/what-is-the-best-lottery-software-program/">What is the best lottery software program</a>
    <a href="https://whitelotto.com/open-lottery-platform/">Open lottery platform</a>
    <a href="https://whitelotto.com/make-your-own-lottery/">Make your own lottery</a>
    <a href="https://whitelotto.com/lottery-turnkey-solution/">Lottery turnkey solutions</a>
    <a href="https://whitelotto.com/which-lottery-website-script-is-currently-the-best-on-the-market/">Which lottery website script is currently the best on the market?</a>
    <a href="https://whitelotto.com/which-lottery-website-template-is-currently-recommended/">Which lottery website template is currently recommended?</a>
    <a href="https://whitelotto.com/curacao-gaming-license-for-lottery-whitelabel-operators/">Curacao gaming license for lottery whitelabel operators</a>
    <a href="https://whitelotto.com/bitcoin-lottery-platform-cryptocurrency-world-meets-lottery-industry/">Bitcoin lottery platform: cryptocurrency world meets lottery industry</a>
    <a href="https://whitelotto.com/whitelotto-the-best-lottery-solution/">WhiteLotto – the best lottery solution</a>
    <a href="https://whitelotto.com/keno-whitelabel-a-ready-made-idea-for-an-online-business/">Keno whitelabel – a ready-made idea for an online business</a>
    <a href="https://whitelotto.com/create-your-own-keno-platform-with-whitelotto-keno-solution/">Create your own Keno platform with WhiteLotto Keno solution</a>
    <a href="https://whitelotto.com/the-best-raffle-software-create-your-own-raffle-platform/">The best Raffle software – create your own raffle platform</a>
    <a href="https://whitelotto.com/casino-white-label-solution-is-it-the-best-idea-for-your-business/">Casino white label solution - an idea for your business</a>
    <a href="https://whitelotto.com/sportsbetting-white-label/">Sports betting white label - the best idea for business online?</a>
    <a href="https://whitelotto.com/bingo-white-label-poker-white-label/">Bingo & poker white label solution vs lottery white label</a>
    <a href="https://whitelotto.com/crypto-exchange-white-label-is-it-the-best-idea-for-your-business/">Crypto exchange white label solution for your own business?</a>
    <a href="https://whitelotto.com/forex-white-label-solutions-is-it-the-best-idea-for-your-business/">Forex white label solution or lottery white label</a>
    <a href="https://whitelotto.com/social-sweepstake-casino-faq/">Social & Sweepstake Casino FAQ & White Label Solution</a>
    <a href="https://whitelotto.com/lottery-as-a-service/">Lottery as a Service (LaaS Solution)</a>
</div>

<?php //get_template_part('template-parts/banner'); ?>


<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="<?php echo get_template_directory_uri(); ?>/js/bootstrap.min.js"></script>
<?php wp_footer(); ?>
<script>
    jQuery(document).ready(function($) {
        $('#header-menu').on('show.bs.collapse', function() {
            $("#nav-menu").addClass("roll-menu");
            $("#nav-menu .demo-wrapper").removeClass("d-none");
        });

        $('#header-menu').on('hide.bs.collapse', function() {
            $("#nav-menu").removeClass("roll-menu");
            $("#nav-menu .demo-wrapper").addClass("d-none");
        });
    });
</script>

<!--<script src="//code.tidio.co/5a8kzggxtbzqmhv1xnexbgbpcjo0ixj9.js"></script>-->
<script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@latest/dist/js/splide.min.js"></script>
</body>

</html>