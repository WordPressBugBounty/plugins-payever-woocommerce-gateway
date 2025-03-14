<?php

if ( ! defined( 'ABSPATH' ) || class_exists( 'WC_Payever_Customer_Company' ) ) {
	return;
}

/**
 * WC_Payever_Customer_Company class.
 */
class WC_Payever_Customer_Company {
	use WC_Payever_Wpdb_Trait;

	/**
	 * @param string $company
	 * @param string $email
	 * @param string $town
	 * @param string $zip
	 * @param string | array $company_data
	 * @return array|null
	 */
	public function add_item( $company, $email, $town, $zip, $company_data ) {
		$exists_data_model = $this->get_item( $company, $email, $town, $zip );

		if ( empty( $company_data ) ) {
			return $exists_data_model;
		}

		if ( ! is_string( $company_data ) ) {
			$company_data = serialize( $company_data );
		}

		if ( ! empty( $exists_data_model ) ) {
			$exists_data_model['company'] = $company_data;
			$this->update( $exists_data_model );

			return $exists_data_model;
		}

		if ( ! is_string( $company_data ) ) {
			$company_data = serialize( $company_data );
		}

		$this->add(
			array(
				'address_hash' => $this->generate_address_hash( $company, $email, $town, $zip ),
				'company'      => $company_data,
			)
		);

		return $this->get_item( $company, $email, $town, $zip );
	}

	/**
	 * @return string
	 */
	public function get_table_name() {
		$prefix = $this->get_wpdb()->prefix;

		return "{$prefix}woocommerce_payever_customer_company";
	}

	/**
	 * @param string $company
	 * @param string $email
	 * @param string $town
	 * @param string $zip
	 * @return array|null
	 */
	public function get_item( $company, $email, $town, $zip ) {
		$address_hash = $this->generate_address_hash( $company, $email, $town, $zip );

		return $this->get_wpdb()->get_row(
			$this->get_wpdb()->prepare(
				'SELECT * FROM ' . $this->get_table_name() . ' WHERE `address_hash` = %s LIMIT 1;',
				$address_hash
			),
			ARRAY_A
		);
	}

	/**
	 * @param string $company
	 * @param string $email
	 * @param string $town
	 * @param string $zip
	 *
	 * @return string
	 */
	private function generate_address_hash( $company, $email, $town, $zip ) {
		$params = array(
			'company' => $company,
			'email'   => $email,
			'town'    => $town,
			'zip'     => $zip,
		);

		return hash( 'sha256', json_encode( $params ) );
	}

	/**
	 * @param array $data
	 */
	private function add( array $data ) {
		$this->get_wpdb()->insert( $this->get_table_name(), $data );
	}

	/**
	 * @param array $data
	 */
	private function update( array $data ) {
		$this->get_wpdb()->update(
			$this->get_table_name(),
			$data,
			array(
				'id' => $data['id'],
			)
		);
	}
}
