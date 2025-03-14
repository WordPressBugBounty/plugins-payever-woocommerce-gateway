<?php

defined( 'ABSPATH' ) || exit;

/** @var WC_Order $order */
/** @var int $order_id */
?>
<div class="wc-order-data-row wc-order-data-row-toggle wc-payever-claim-upload wc-payever-action" style="display: none;">
	<div class="wc-order-totals payever-order-totals" style="max-width: 500px;">
		<div class="wc-order-row">
			<span class="label">
				<label for="claim_upload_files">
					<?php esc_html_e( 'Invoice files', 'payever-woocommerce-gateway' ); ?>:
				</label>
			</span>
			<span class="total">
				<input type="file" multiple="multiple" accept="application/pdf" class="text" id="claim_upload_files" name="claim_upload_files"/>
			</span>
		</div>
	</div>
	<div class="clear"></div>
	<div class="claim-upload-actions">
		<span id="claim-upload-spinner" class="spinner" style="float:none;"></span>
		<button type="button" id="payever-claim-upload-action" class="button button-primary" data-order-id="<?php echo esc_attr( $order_id ); ?>" disabled="disabled">
			<?php esc_html_e( 'Claim upload', 'payever-woocommerce-gateway' ); ?>
		</button>
		<button type="button" class="button cancel-action">
			<?php esc_html_e( 'Cancel', 'payever-woocommerce-gateway' ); ?>
		</button>
		<div class="clear"></div>
	</div>
</div>
<?php
wp_print_inline_script_tag(
	'
		if (typeof woocommerce_admin_meta_boxes !== "undefined") {
			woocommerce_admin_meta_boxes["payever_claim_upload_nonce"] = "' . esc_html( wp_create_nonce( 'wp_ajax_payever_claim_upload' ) ) . '"
		}
	'
);
?>
