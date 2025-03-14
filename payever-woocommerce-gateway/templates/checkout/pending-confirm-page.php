<?php
/*
 * Template Name: Pending Confirmation Page
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="teaser--text is--align-center panel--body">
	<div class="pe-checkout-bootstrap loader-container loader-fixed loader-fade-dark">
		<?php esc_html_e( 'Waiting for an update. It might take several minutes...', 'payever-woocommerce-gateway' ); ?><br/>
		<div class="loader_48"></div>
	</div>
	<br/>
	<div id="payever_message"></div>
</div>
