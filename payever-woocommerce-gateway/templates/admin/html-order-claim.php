<?php

defined( 'ABSPATH' ) || exit;

/** @var WC_Order $order */
/** @var int $order_id */
?>
<div class="wc-order-data-row wc-order-data-row-toggle wc-payever-claim wc-payever-action" style="display: none;">
	<div class="wc-order-totals payever-order-totals">
		<div class="wc-order-row">
			<span class="label">
				<label for="is_disputed">
					<?php esc_html_e( 'Is invoice disputed', 'payever-woocommerce-gateway' ); ?>:
				</label>
			</span>
			<span class="total">
				<input type="checkbox" id="is_disputed" name="is_disputed" value="1"/>
			</span>
		</div>
	</div>
	<div class="clear"></div>
	<div class="claim-actions">
		<span id="claim-spinner" class="spinner" style="float:none;"></span>
		<button type="button" id="payever-claim-action" class="button button-primary" data-order-id="<?php echo esc_attr( $order_id ); ?>">
			<?php esc_html_e( 'Claim', 'payever-woocommerce-gateway' ); ?>
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
			woocommerce_admin_meta_boxes["payever_claim_nonce"] = "' . esc_html( wp_create_nonce( 'wp_ajax_payever_claim' ) ) . '"
		}
	'
);
?>
