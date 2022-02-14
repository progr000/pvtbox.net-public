<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package Pvtbox
 */
?>
    <div class="hFooter"></div>

</div>

<footer class="footer">

    <div class="footer__cont">

        <div class="footer__row">

            <div class="footer__col">
                <div class="footer-logo"><a href="/"><img src="/themes/orange/images/logo-footer_new.svg" alt="Pvtbox"></a></div>
            </div>

            <div class="footer__col">
                <!--<span class="footer-text">PB Private Cloud Solutions Ltd. © 2019</span>-->
                <!--<a class="footer-link" href="javascript:void(0)">support@pvtbox.net</a>-->
                <div class="footer-menu">
                    <a href="/terms" class="">Terms and Conditions</a>
                    <a href="/privacy" class="">Privacy Policy</a>
                    <a href="/sla" class="">SLA</a>
                    <a href="/download" class="" target="_blank">Download</a>
                    <a href="/features" class="">Features</a>
                    <a href="/pricing" class="">Pricing</a>
                    <a href="/faq" class="">FAQ</a>
                    <a href="/about" class="">About</a>
                    <!-- <a href="/support" class="">Support</a> -->
                    <a href="/?signup" -data-toggle="modal" -data-target="#entrance" -data-whatever="reg" -class="signup-dialog">Registration</a>
                    <a name="name_copy" class="nolink">© Pvtbox 2019 All rights reserved</a>
                </div>
            </div>

            <div class="footer__col">

                <div class="social-networks">
                    <a class="social-networks__twitter" href="<?= \common\models\Preferences::getValueByKey('seoTwitterLink') ?>" target="_blank"></a>
                </div>

                <!--
                <div class="language dropup">
                    <span class="dropdown-toggle" data-toggle="dropdown"><a href="" title="en"><i class="en"></i> EN</a></span>
                    <ul class="dropdown-menu">

                    </ul>
                </div>
                -->
            </div>

        </div>

    </div>

</footer>

</body>
</html>