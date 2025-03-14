<?php

use Payever\Sdk\Payments\Http\RequestEntity\CompanySearch\AddressEntity;
use Payever\Sdk\Payments\Http\RequestEntity\CompanySearch\CompanyEntity;
use Payever\Sdk\Payments\Http\RequestEntity\CompanySearchRequest;
use Payever\Sdk\Payments\Http\ResponseEntity\CompanySearchResponse;

if ( ! defined( 'ABSPATH' ) || class_exists( 'WC_Payever_Company_Search' ) ) {
	return;
}

/**
 * WC_Payever_Company_Search Class.
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class WC_Payever_Company_Search {
	use WC_Payever_WP_Wrapper_Trait;
	use WC_Payever_Api_Wrapper_Trait;

	/**
	 * @param WC_Payever_WP_Wrapper|null $wp_wrapper
	 */
	public function __construct( $wp_wrapper = null ) {
		if ( null !== $wp_wrapper ) {
			$this->set_wp_wrapper( $wp_wrapper );
		}

		$this->get_wp_wrapper()->add_action(
			'wp_enqueue_scripts',
			array( $this, 'enqueue_stylesheets' ),
			10,
			1
		);

		$this->get_wp_wrapper()->add_action(
			'wp_enqueue_scripts',
			array( $this, 'enqueue_js' ),
			10,
			1
		);
		$this->get_wp_wrapper()->add_action(
			'woocommerce_checkout_before_customer_details',
			array( $this, 'add_externalid_field' )
		);

		$this->get_wp_wrapper()->add_filter(
			'woocommerce_checkout_get_value',
			array( $this, 'checkout_get_value' ),
			10,
			2
		);

		$this->get_wp_wrapper()->add_action( 'wp_ajax_payever_company_search', array( $this, 'company_search' ) );
		$this->get_wp_wrapper()->add_action( 'wp_ajax_nopriv_payever_company_search', array( $this, 'company_search' ) );
		$this->get_wp_wrapper()->add_action( 'wp_ajax_payever_save_external_data', array( $this, 'save_external_data' ) );
		$this->get_wp_wrapper()->add_action( 'wp_ajax_nopriv_payever_save_external_data', array( $this, 'save_external_data' ) );
	}

	/**
	 * @param $value
	 * @param $input
	 *
	 * @return mixed|string
	 */
	public function checkout_get_value( $value, $input ) {
		if ( 'billing_company' === $input ) {
			// Reset company name if external id is missing
			return $this->get_external_id() ? $value : '';
		}

		return $value;
	}

	public function enqueue_stylesheets() {
		$this->get_wp_wrapper()->wp_register_style(
			'payever_autocomplete',
			WC_PAYEVER_PLUGIN_URL . '/assets/css/payever_autocomplete.css'
		);

		$this->get_wp_wrapper()->wp_enqueue_style( 'payever_autocomplete' );
	}

	public function enqueue_js() {
		if ( $this->wp_wrapper->is_checkout() && get_option( WC_Payever_Helper::PAYEVER_B2B_COMPANY_SEARCH ) ) {
			// Add styles
			$this->get_wp_wrapper()->wp_register_style(
				'payever_company_search_css',
				WC_PAYEVER_PLUGIN_URL . '/assets/css/company_search.css',
				''
			);
			$this->get_wp_wrapper()->wp_enqueue_style( 'payever_company_search_css' );

			// Add scripts
			$this->get_wp_wrapper()->wp_register_script(
				'payever_company_search',
				WC_PAYEVER_PLUGIN_URL . '/assets/js/company-search.js'
			);

			$available_countries = $this->get_available_countries();
			$b2b_countries = WC_Payever_Helper::instance()->get_b2b_countries();
			$json_config = array(
				'onlyCountries'         => array_intersect( $b2b_countries, $available_countries['onlyCountries'] ),
				'countryMapping'        => $available_countries['countryMapping'],
				'companyRequestUrl'     => admin_url( 'admin-ajax.php' )
					. '?action=payever_company_search&nonce=' . wp_create_nonce( 'company_search' ),
				'companySaveRequestUrl' => admin_url( 'admin-ajax.php' )
					. '?action=payever_save_external_data&nonce=' . wp_create_nonce( 'company_search' ),
			);
			// Localize the script
			$translation_array = array(
				'json_config' => $json_config,
			);
			$this->get_wp_wrapper()->wp_localize_script( 'payever_company_search', 'Payever_Company_Search', $translation_array ); //phpcs:ignore

			// Enqueued script with localized data
			$this->get_wp_wrapper()->wp_enqueue_script( 'payever_company_search' );
		}
	}

	public function add_externalid_field() {
		$external_id = $this->get_external_id();
		if ( $external_id ) {
			?>
			<input type="hidden" class="payever_external_id" name="payever_external_id" value="<?php echo esc_attr( $external_id ); ?>"/>
			<?php
		}
	}

	/**
	 * Company search ajax action.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function company_search() {
		if ( ! wp_verify_nonce( wc_clean( $_REQUEST['nonce'] ), 'company_search' ) ) {
			$this->get_wp_wrapper()->wp_send_json( 'No naughty business' );
			return;
		}

		$term          = isset( $_REQUEST['term'] ) ? wc_clean( $_REQUEST['term'] ) : '';
		$country       = isset( $_REQUEST['country'] ) ? wc_clean( $_REQUEST['country'] ) : '';
		$search_result = $this->find_company( $term, $country );

		$result = array();
		foreach ( $search_result as $company ) {
			$result[] = $company->toArray();
		}

		$this->get_wp_wrapper()->wp_send_json( array_filter( $result ) );
	}

	public function save_external_data() {
		if ( ! wp_verify_nonce( wc_clean( $_REQUEST['nonce'] ), 'company_search' ) ) {
			$this->get_wp_wrapper()->wp_send_json( 'No naughty business' );
			return;
		}

		$raw_data   = json_decode(
			wp_kses_post(
				sanitize_text_field(
					file_get_contents( 'php://input' )
				)
			),
			true
		); // WPCS: input var ok, CSRF ok.
		$company = isset( $raw_data['company'] ) ? wc_clean( $raw_data['company'] ) : '';
		$email = isset( $raw_data['email'] ) ? wc_clean( $raw_data['email'] ) : '';
		$town = isset( $raw_data['town'] ) ? wc_clean( $raw_data['town'] ) : '';
		$zip = isset( $raw_data['zip'] ) ? wc_clean( $raw_data['zip'] ) : '';
		$company_data = isset( $raw_data['companyData'] ) ? wc_clean( $raw_data['companyData'] ) : '';

		$customer_company_service = new WC_Payever_Customer_Company();
		$company_data_model = $customer_company_service->add_item( $company, $email, $town, $zip, $company_data );

		$this->get_wp_wrapper()->wp_send_json( $company_data_model );
	}

	/**
	 * Find compnay.
	 *
	 * @param $company
	 * @param $country
	 *
	 * @return \Payever\Sdk\Payments\Http\MessageEntity\CompanySearchResultEntity[]
	 * @throws Exception
	 */
	private function find_company( $company, $country ) {
		$companyEntity = new CompanyEntity();
		$companyEntity->setName( $company );

		$addressEntity = new AddressEntity();
		$addressEntity->setCountry( $country );

		$companySearchRequestEntity = new CompanySearchRequest();
		$companySearchRequestEntity->setCompany( $companyEntity );
		$companySearchRequestEntity->setAddress( $addressEntity );

		$response = $this->get_api_wrapper()->get_payments_api_client()
			->searchCompany( $companySearchRequestEntity );

		/** @var CompanySearchResponse $responseEntity */
		$responseEntity = $response->getResponseEntity();

		return $responseEntity->getResult();
	}

	/**
	 * Get buyer ID.
	 *
	 * @param $order_id
	 *
	 * @return string|false
	 */
	private function get_external_id( $order_id = null ) {
		if ( ! $order_id ) {
			$order_id = absint( WC()->session->get( 'order_awaiting_payment' ) );
		}

		$external_id = $this->wp_wrapper->get_post_meta( $order_id, '_payever_external_id', true );
		if ( ! empty( $external_id ) ) {
			return $external_id;
		}

		return false;
	}

	/**
	 * @return array|array[]
	 */
	private function get_available_countries() {
		$countryList = new WC_Countries();

		$result = array(
			'onlyCountries'  => array(),
			'countryMapping' => array(),
		);

		foreach ( array_keys( $countryList->get_allowed_countries() ) as $code ) {
			$result['onlyCountries'][] = strtolower( $code );
			$result['countryMapping'][ strtolower( $code ) ] = $code;
		}

		return $result;
	}
}
