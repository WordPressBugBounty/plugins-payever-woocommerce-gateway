<?php

defined( 'ABSPATH' ) || exit;

/** @var string $payment_url */

$is_local_redirect = false;
if ( isset( $_SERVER['HTTP_HOST'] ) && strpos( $payment_url, sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) ) !== false ) {
	$is_local_redirect = true;
}
?>

<?php if ( ! $is_local_redirect ) : ?>
<iframe allow="payment"
	sandbox="allow-same-origin allow-forms allow-top-navigation allow-scripts allow-modals allow-popups allow-popups-to-escape-sandbox"
	id="payever_iframe"
	width="100%"
	src="<?php echo esc_url( $payment_url ); ?>"
	style="border:none; min-height: 600px;">
</iframe>
<?php else : ?>
<script>
	document.location.href = "<?php echo htmlspecialchars_decode( esc_url( ( $payment_url ) ) ); ?>";
</script>
<?php endif; ?>