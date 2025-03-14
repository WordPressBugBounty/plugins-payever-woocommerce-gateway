<?php

defined( 'ABSPATH' ) || exit;

trait WC_Payever_Api_Claim_Service_Trait {
	/**
	 * @var WC_Payever_Api_Claim_Service
	 */
	private $api_claim_service;

	/**
	 * @param WC_Payever_Api_Claim_Service $api_claim_service
	 * @return $this
	 * @internal
	 * @codeCoverageIgnore
	 */
	public function set_api_claim_service( WC_Payever_Api_Claim_Service $api_claim_service ) {
		$this->api_claim_service = $api_claim_service;

		return $this;
	}

	/**
	 * @return WC_Payever_Api_Claim_Service
	 * @codeCoverageIgnore
	 */
	protected function get_api_claim_service() {
		return null === $this->api_claim_service
			? $this->api_claim_service = new WC_Payever_Api_Claim_Service()
			: $this->api_claim_service;
	}
}
