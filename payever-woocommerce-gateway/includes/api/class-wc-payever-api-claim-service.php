<?php

defined( 'ABSPATH' ) || exit;

use Payever\Sdk\Payments\Http\RequestEntity\ClaimPaymentRequest;
use Payever\Sdk\Payments\Http\RequestEntity\ClaimUploadPaymentRequest;
use Payever\Sdk\Payments\PaymentsApiClient;
use Payever\Sdk\Payments\Action\ActionDecider;

// @codeCoverageIgnoreStart
if ( class_exists( 'WC_Payever_Api_Claim_Service' ) ) {
	return;
}
// @codeCoverageIgnoreEnd

class WC_Payever_Api_Claim_Service {
	use WC_Payever_Helper_Trait;
	use WC_Payever_WP_Wrapper_Trait;
	use WC_Payever_Api_Wrapper_Trait;
	use WC_Payever_Action_Decider_Wrapper_Trait;
	use WC_Payever_Payment_Action_Wrapper_Trait;

	/**
	 * @var PaymentsApiClient
	 */
	private $api;

	/**
	 * @var ActionDecider
	 */
	private $action_decider;

	/**
	 * @var WC_Payever_Payment_Action_Wrapper
	 */
	private $payment_action;

	public function __construct() {
		$this->api            = $this->get_api_wrapper()->get_payments_api_client();
		$this->action_decider = $this->get_action_decider_wrapper()->get_action_decider( $this->api );
		$this->payment_action = $this->get_payment_action_wrapper();
	}

	/**
	 * Cancels items from a WC_Order.
	 * If the control_amount is provided and it does not match the calculated items amount,
	 * a refund by the control_amount will be performed instead.
	 *
	 * @param WC_Order $order The order to cancel items from.
	 * @param bool     $is_disputed Is invoice disputed.
	 *
	 * @return void
	 * @throws BadMethodCallException If the order does not have a payment ID.
	 * @throws Exception If an error occurs during the cancelItemsPaymentRequest API call.
	 */
	public function claim( WC_Order $order, $is_disputed ) {
		$payment_id = $this->get_helper()->get_payment_id( $order );
		if ( ! $payment_id ) {
			throw new \BadMethodCallException( 'Order does not have payment ID.' );
		}

		try {
			$claim_payment_request = new ClaimPaymentRequest();
			$claim_payment_request->setIsDisputed( (bool) $is_disputed );

			$this->api->claimPaymentRequest( $payment_id, $claim_payment_request );
		} catch ( Exception $exception ) {
			// Reset tokens in case some of them caused this error.
			$this->api->getTokens()->clear()->save();

			throw $exception;
		}

		$order->add_order_note(
			sprintf(
			/* translators: %s: Order note */
				'<p style="color: green;">%s</p>',
				__( 'The claim was successfully processed.', 'payever-woocommerce-gateway' )
			)
		);
	}

	/**
	 * Cancels items from a WC_Order.
	 * If the control_amount is provided and it does not match the calculated items amount,
	 * a refund by the control_amount will be performed instead.
	 *
	 * @param WC_Order $order The order to cancel items from.
	 * @param array    $files The invoice files.
	 *
	 * @return void
	 * @throws BadMethodCallException If the order does not have a payment ID.
	 * @throws Exception If an error occurs during the cancelItemsPaymentRequest API call.
	 */
	public function claim_upload( WC_Order $order, array $files ) {
		$payment_id = $this->get_helper()->get_payment_id( $order );
		if ( ! $payment_id ) {
			throw new \BadMethodCallException( 'Order does not have payment ID.' );
		}

		try {
			foreach ( $files['name'] as $key => $invoice ) {
				$content = file_get_contents( $files['tmp_name'][ $key ] );

				$upload_payment_request = new ClaimUploadPaymentRequest();
				$upload_payment_request->setFileName( $invoice );
				$upload_payment_request->setBase64Content( base64_encode( $content ) );
				$upload_payment_request->setMimeType( $files['type'][ $key ] );
				$upload_payment_request->setDocumentType( ClaimUploadPaymentRequest::DOCUMENT_TYPE_INVOICE );

				$this->api->claimUploadPaymentRequest( $payment_id, $upload_payment_request );
			}
		} catch ( Exception $exception ) {
			// Reset tokens in case some of them caused this error.
			$this->api->getTokens()->clear()->save();

			throw $exception;
		}

		$order->add_order_note(
			sprintf(
				/* translators: %s: Order note */
				'<p style="color: green;">%s</p>',
				__( 'The claim invoice was successfully uploaded.', 'payever-woocommerce-gateway' )
			)
		);
	}
}
