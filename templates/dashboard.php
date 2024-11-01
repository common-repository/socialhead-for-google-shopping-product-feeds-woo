<?php
    $baseController = new \Socialshop\Base\BaseController();
    $pluginVersion  = $baseController->getVersion();
    $current_domain  = $baseController->getDomain();
?>
<section id="socialshop-page">
    <div class="text-center">
        <header>
            <img src="<?php echo SOCIALSHOP_PLUGIN_URL.'/assets/images/connect-to-socialhead.png' ?>" alt="Connect to socialhead" class="img-fluid" />
        </header>
        <main>
            <h2> Connect to Socialhead </h2>
            <p> Let's start creating one Socialhead Account for all your stores. After connecting the stores with our plugin, you can import products from different store sources as well as submit feed to multi-channels. </p>
            <div class="ss-actions">
                <button type="button" data-url="<?php echo $iframe_link ?>" id="ssLogin" class="btn btn-primary">
                    Get Started <span></span>
                </button>
                <a href="https://socialhead.io/socialshop/?utm_source=woocommerce&utm_medium=banner&utm_campaign=woo_button&utm_content=learnmore">Learn More</a>
            </div>
        </main>
        <footer>
            <ul class="list-unstyled d-flex align-items-center justify-content-center  m-0">
                <li> Copyright ©2020 <a href="https://socialhead.io" target="_blank">Socialhead - Version <?php echo $pluginVersion ?> </a></li>
                <li><a href="https://socialhead.io/terms-of-use/" target="_blank"> Term of use </a> </li>
                <li><a href="https://socialhead.io/privacy-policy/" target="_blank"> Privacy Policy </a> </li>
                <li><a href="https://help.socialhead.io/" target="_blank"> Help Center </a> </li>
            </ul>
		    <?php wp_nonce_field('socialshop_wpnonce_code','_wpnonce'); ?>
            <input type="hidden" id="ss_submit_change" value="<?php echo $channel_id ?>">
            <input type="hidden" id="ss_domain" value="<?php echo $current_domain ?>">
        </footer>
    </div>
</section>
<!-- #socialshop-page -->
