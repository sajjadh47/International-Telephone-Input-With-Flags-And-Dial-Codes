<?php
/**
 * This file contains the definition of the International_Telephone_Input_With_Flags_And_Dial_Codes_Public class, which
 * is used to load the plugin's public-facing functionality.
 *
 * @package       International_Telephone_Input_With_Flags_And_Dial_Codes
 * @subpackage    International_Telephone_Input_With_Flags_And_Dial_Codes/public
 * @author        Sajjad Hossain Sagor <sagorh672@gmail.com>
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version and other methods.
 *
 * @since    2.0.0
 */
class International_Telephone_Input_With_Flags_And_Dial_Codes_Public {
	/**
	 * The ID of this plugin.
	 *
	 * @since     2.0.0
	 * @access    private
	 * @var       string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since     2.0.0
	 * @access    private
	 * @var       string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @param     string $plugin_name The name of the plugin.
	 * @param     string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since     2.0.0
	 * @access    public
	 */
	public function enqueue_styles() {
		$enabled = International_Telephone_Input_With_Flags_And_Dial_Codes::get_option( 'enable', 'wpitfdc_basic_settings', 'off' );

		if ( 'on' === $enabled ) {
			wp_enqueue_style( $this->plugin_name, INTERNATIONAL_TELEPHONE_INPUT_WITH_FLAGS_AND_DIAL_CODES_PLUGIN_URL . 'public/css/public.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since     2.0.0
	 * @access    public
	 */
	public function enqueue_scripts() {
		$enabled = International_Telephone_Input_With_Flags_And_Dial_Codes::get_option( 'enable', 'wpitfdc_basic_settings', 'off' );

		if ( 'on' === $enabled ) {
			wp_enqueue_script( $this->plugin_name, INTERNATIONAL_TELEPHONE_INPUT_WITH_FLAGS_AND_DIAL_CODES_PLUGIN_URL . 'public/js/public.js', array( 'jquery' ), $this->version, false );

			$initial_country     = 'on' === International_Telephone_Input_With_Flags_And_Dial_Codes::get_option( 'enable_geoip_lookup', 'wpitfdc_basic_settings', 'off' ) ? $this->get_visitor_country_from_ip() : '';
			$exclude_countries   = International_Telephone_Input_With_Flags_And_Dial_Codes::get_option( 'excludeCountries', 'wpitfdc_basic_settings', array() );
			$preferred_countries = International_Telephone_Input_With_Flags_And_Dial_Codes::get_option( 'preferredCountries', 'wpitfdc_basic_settings', array() );
			$only_countries      = International_Telephone_Input_With_Flags_And_Dial_Codes::get_option( 'onlyCountries', 'wpitfdc_basic_settings', array() );
			$geoip_loopup_ajax   = International_Telephone_Input_With_Flags_And_Dial_Codes::get_option( 'enable_geoip_lookup_ajax', 'wpitfdc_basic_settings', 'off' );
			$query_selectors     = International_Telephone_Input_With_Flags_And_Dial_Codes::get_option( 'targetClass', 'wpitfdc_basic_settings', 'input[type="tel"]' );

			wp_localize_script(
				$this->plugin_name,
				'InternationalTelephoneInputWithFlagsAndDialCodes',
				array(
					'ajaxurl'            => admin_url( 'admin-ajax.php' ),
					'pluginurl'          => INTERNATIONAL_TELEPHONE_INPUT_WITH_FLAGS_AND_DIAL_CODES_PLUGIN_URL,
					'initialCountry'     => $initial_country,
					'excludeCountries'   => $exclude_countries,
					'preferredCountries' => $preferred_countries,
					'onlyCountries'      => $only_countries,
					'geoIpLookupAjax'    => $geoip_loopup_ajax,
					'querySelectors'     => $query_selectors,
				)
			);
		}
	}

	/**
	 * Retrieves the client's IP address.
	 *
	 * This function attempts to determine the client's IP address by checking various
	 * server variables. It handles cases where the client might be behind a proxy or load balancer.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @return    string|null The client's IP address, or null if it cannot be determined.
	 */
	public function get_visitor_ip() {
		$ipaddress = '';

		// If website is hosted behind CloudFlare protection.
		if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
		} elseif ( isset( $_SERVER['X-Real-IP'] ) ) {
			$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['X-Real-IP'] ) );
		} elseif ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
			$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED'] ) );
		} elseif ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
			$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_FORWARDED_FOR'] ) );
		} elseif ( isset( $_SERVER['HTTP_FORWARDED'] ) ) {
			$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_FORWARDED'] ) );
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		// validate ip address.
		if ( filter_var( $ipaddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
			return $ipaddress;
		}

		if ( ! empty( $ipaddress ) ) {
			$ipaddresses = explode( ',', $ipaddress );

			if ( ! empty( $ipaddresses ) ) {
				$ipaddress = trim( $ipaddresses[0] );
			}
		}

		return $ipaddress;
	}

	/**
	 * Retrieves the visitor's country ISO code based on their IP address.
	 *
	 * This function uses the GeoLite2-Country database to determine the visitor's
	 * country based on their IP address. It attempts to read the database and
	 * perform the lookup, returning the ISO code of the country. If the IP address
	 * cannot be determined or an error occurs during the lookup, it returns null.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @output    string|null The client's country ISO code, or null if it cannot be determined.
	 */
	public function get_visitor_country() {
		die( esc_html( $this->get_visitor_country_from_ip() ) );
	}

	/**
	 * Retrieves the visitor's country ISO code based on their IP address.
	 *
	 * This function uses the GeoLite2-Country database to determine the visitor's
	 * country based on their IP address. It attempts to read the database and
	 * perform the lookup, returning the ISO code of the country. If the IP address
	 * cannot be determined or an error occurs during the lookup, it returns null.
	 *
	 * @since    2.0.0
	 * @access   public
	 * @return   string|null The client's country ISO code, or null if it cannot be determined.
	 */
	public function get_visitor_country_from_ip() {
		/**
		 * Loads the composer autoload file to include packages.
		 */
		require_once INTERNATIONAL_TELEPHONE_INPUT_WITH_FLAGS_AND_DIAL_CODES_PLUGIN_PATH . 'vendor/autoload.php';

		try {
			// This creates the Reader object.
			$reader          = new \GeoIp2\Database\Reader( INTERNATIONAL_TELEPHONE_INPUT_WITH_FLAGS_AND_DIAL_CODES_PLUGIN_PATH . 'public/geoip-db/GeoLite2-Country.mmdb' );
			$visitor_geo     = $reader->country( $this->get_visitor_ip() );
			$visitor_country = $visitor_geo->country->isoCode;
		} catch ( Exception $e ) {
			$visitor_country = '';
		}

		return strtolower( $visitor_country );
	}
}
