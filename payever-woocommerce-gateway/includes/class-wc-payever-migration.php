<?php

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WC_Payever_Migration' ) ) {
	return;
}

/**
 * WC_Payever_Migration.
 * @codeCoverageIgnore
 */
class WC_Payever_Migration {

	const PAYEVER_DB_VERSION                = 'wc_payever_db_version';
	const PAYEVER_PLUGIN_REGISTERED_VERSION = 'wc_payever_plugin_registered_version';

	/** @var string[] */
	private static $db_migrations = array(
		'1.0.0'  => 'update_1_0_0_synchronization_queue',
		'1.24.0' => 'update_1_24_0_product_uuid',
		'2.4.0'  => 'update_2_4_0_payment_action',
		'3.2.0'  => 'update_3_2_0_action',
		'3.3.0'  => 'update_3_3_0_customer_company',
	);

	/** @var wpdb */
	private static $wpdb;

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action(
			'init',
			array(
				__CLASS__,
				'check_version',
			),
			5
		);

		add_action(
			'init',
			array(
				__CLASS__,
				'register_plugin',
			),
			10
		);
	}

	/**
	 * @throws Exception
	 */
	private static function migrate_db() {
		$current_version = get_option( self::PAYEVER_DB_VERSION, 0 );
		foreach ( self::$db_migrations as $version => $function_name ) {
			if ( version_compare( $current_version, $version, '<' ) ) {

				call_user_func(
					array(
						__CLASS__,
						$function_name,
					)
				);

				update_option( self::PAYEVER_DB_VERSION, $version );
				$current_version = $version;
			}
		}
	}

	/**
	 * Check payever DB version and run the migration if required.
	 *
	 * This check is done on all requests and runs if he versions do not match.
	 */
	public static function check_version() {
		try {
			$current_version = get_option( self::PAYEVER_DB_VERSION, 0 );
			$version_keys    = array_keys( self::$db_migrations );
			if ( version_compare( $current_version, end( $version_keys ), '<' ) ) {
				self::migrate_db();
			}
		} catch ( Exception $e ) {
			$logger = wc_get_logger();
			$logger->error( $e->getMessage(), array( $e->getTraceAsString() ) );

			if ( is_admin() ) {
				add_action(
					'admin_notices',
					array(
						__CLASS__,
						'migration_failed_notice',
					)
				);
			}
		}
	}

	/**
	 * Registers plugin
	 */
	public static function register_plugin() {
		$plugin_registered_version = get_option( self::PAYEVER_PLUGIN_REGISTERED_VERSION, 0 );
		if ( version_compare( $plugin_registered_version, WC_PAYEVER_PLUGIN_VERSION, '<' ) ) {
			WC_Payever_Plugin_Command_Cron::execute_plugin_commands();
			update_option( self::PAYEVER_PLUGIN_REGISTERED_VERSION, WC_PAYEVER_PLUGIN_VERSION );
		}
	}

	/**
	 * @throws Exception
	 */
	public static function update_1_0_0_synchronization_queue() {
		$prefix    = esc_sql( self::get_wpdb()->prefix );
		$collation = esc_sql( self::get_collation() );

		$sql       = "CREATE TABLE IF NOT EXISTS {$prefix}woocommerce_payever_synchronization_queue (
			`id` INT(10) NOT NULL AUTO_INCREMENT,
			`direction` VARCHAR(255) NOT NULL,
			`action` VARCHAR(255) NOT NULL,
			`payload` LONGTEXT NULL,
			`attempts` INT(1) NULL,
			PRIMARY KEY (`id`)
		) {$collation}";

		$result = self::get_wpdb()->query( $sql );
		self::assert_result_is_successful( $result );
	}

	/**
	 * @throws Exception
	 */
	public static function update_1_24_0_product_uuid() {
		$prefix    = esc_sql( self::get_wpdb()->prefix );
		$collation = esc_sql( self::get_collation() );

		$sql       = "CREATE TABLE IF NOT EXISTS {$prefix}woocommerce_payever_product_uuid (
			`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
			`product_id` BIGINT(20) UNSIGNED NOT NULL,
			`uuid` CHAR(36) NOT NULL,
			PRIMARY KEY (`id`),
			key (`uuid`),
			UNIQUE KEY (`product_id`, `uuid`)
		) {$collation}";

		$result = self::get_wpdb()->query( $sql );
		self::assert_result_is_successful( $result );
	}

	/**
	 * @return void
	 * @throws Exception
	 */
	public static function update_3_2_0_action() {
		delete_option( WC_Payever_Helper::KEY_API_VERSION );
		// result no matter
	}

	/**
	 * @throws Exception
	 */
	public static function update_2_4_0_payment_action() {
		$prefix    = esc_sql( self::get_wpdb()->prefix );
		$collation = esc_sql( self::get_collation() );

		$sql       = "CREATE TABLE IF NOT EXISTS {$prefix}woocommerce_payever_payment_action (
			`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`unique_identifier` VARCHAR(36) NOT NULL,
			`order_id` bigint(20) UNSIGNED NOT NULL,
			`action_type` VARCHAR(32) NOT NULL,
			`action_source` VARCHAR(32) NOT NULL,
			`amount` FLOAT NULL,
			`created_at` datetime NOT NULL,
			UNIQUE KEY unique_identifier (unique_identifier)
		) {$collation}";

		$result = self::get_wpdb()->query( $sql );
		self::assert_result_is_successful( $result );
	}

	/**
	 * @throws Exception
	 */
	public static function update_3_3_0_customer_company() {
		$prefix    = esc_sql( self::get_wpdb()->prefix );
		$collation = esc_sql( self::get_collation() );
		$sql       = "CREATE TABLE IF NOT EXISTS {$prefix}woocommerce_payever_customer_company (
			`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`address_hash` varchar(64) NOT NULL UNIQUE COMMENT 'Address hash',
			`company` TEXT DEFAULT '' COMMENT 'company',
			KEY `PAYEVER_CUSTOMER_COMPANY_ADDRESS_HASH` (`address_hash`)
		) {$collation}";

		$result = self::get_wpdb()->query( $sql );
		self::assert_result_is_successful( $result );
	}

	/**
	 * @return void
	 */
	public static function migration_failed_notice() {
		echo '<div id="notice" class="error"><p>';
		echo esc_html__( 'Migration failed', 'payever-woocommerce-gateway' );
		echo '</p></div>';
	}

	/**
	 * @return wpdb
	 */
	private static function get_wpdb() {
		if ( null === self::$wpdb ) {
			global $wpdb;
			self::$wpdb = $wpdb;
		}

		return self::$wpdb;
	}

	/**
	 * @return string
	 */
	private static function get_collation() {
		$collate = '';
		if ( self::get_wpdb()->has_cap( 'collation' ) ) {
			$collate = self::get_wpdb()->get_charset_collate();
		}

		return $collate;
	}

	/**
	 * @param mixed $result
	 * @throws Exception
	 */
	private static function assert_result_is_successful( $result ) {
		if ( false === $result ) {
			throw new \InvalidArgumentException( esc_html( self::get_wpdb()->last_error ) );
		}
	}
}
