<?php /* Template name: Contact */ ?>
<?php
if (!defined('WPINC')) {
    die;
}
?>
<?php get_header(); ?>
<?php get_template_part('template-parts/header', 'small'); ?>

    <div class="container contact-page">
        <div class="row no-gutters">
            <div class="col-lg-5 col-12 pr-lg-5 order-lg-0 order-1">
                <?= the_content() ?>
            </div>
            <div class="col-lg col-12 address">
                <h2>Address</h2>
                <div class="row no-gutters">
                    <div class="col-sm col-12 mr-sm-5">
                        Our offices:
                        <p>
                            UK - 19 Crundale Tower, Tintagel Road, Orpington, Kent, England, BR5 4LE<br />
                            Curaçao - White Lotto B.V., Fransche Bloemweg 4<br />
                            Cyprus - White Lto Limited, Voukourestiou, 25 Neptun House, 1st floor, Flat/Office 11, Zakaki,3045, Limassol,<br />
                            BVI - GG International Limited, Trident Chambers, Road Town, Tortola<br />
                            Poland - T2G Europe, Wojska Polskiego 8, 41-208 Sosnowiec<br />
                        </p>
                    </div>
                    <div class="col-sm col-12 address-contact">
                        <p>Phone: <a href="tel:+48570059652">+48570059652</a> (also: WhatsApp, Signal)</p>
                        <p>E-mail: <a href="https://whitelotto.com/contact/">Please use the contact form</a></p>
                        <p>Skype ID: <a href="skype:live:8f92c955cbf8e9c3?call">live:8f92c955cbf8e9c3</a></p>
                        <p>Telegram: <a href="https://t.me/tnigg" target="_blank">@tnigg</a></p>
                    </div>
                </div>
                <img src="<?php echo get_template_directory_uri(); ?>/images/contact.jpg" alt="contact">
            </div>

        </div>
    </div>

<?php get_footer(); ?>