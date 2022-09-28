<?php
/**
 * Plugin Name: International Telephone Input With Flags And Dial Codes
 * Plugin URI: https://wordpress.org/plugins/international-telephone-input-with-flags-and-dial-codes/
 * Description: Plugin turns the standard telephone input into an International Telephone Input with a national flag drop down list & respective Country dial codes.
 * Version: 1.0.1
 * Author: Sajjad Hossain Sagor
 * Author URI: https://sajjadhsagor.com/
 * Text Domain: international-telephone-input-with-flags-and-dial-codes
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'WPITFDC_ROOT_DIR', dirname( __FILE__ ) ); // Plugin root dir

define( 'WPITFDC_ROOT_URL', plugin_dir_url( __FILE__ ) ); // Plugin root url

// add settings api wrapper
require WPITFDC_ROOT_DIR . '/includes/class.settings-api.php';

use GeoIp2\Database\Reader;

/**
 * Plugin Main Class To Register Settings & Front End Scripts Enqueueing
 *
 * @author Sajjad Hossain Sagor
 */
class WPITFDC_SETTINGS
{
    private $settings_api;

    private $options;

    function __construct()
    {
    	$this->options = get_option( 'wpitfdc_basic_settings', array() );
        
        $this->settings_api = new WP_NinjaCoder_Settings_API;

        add_action( 'admin_init', array( $this, 'admin_init' ) );
        
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );

        add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        add_action( 'wp_footer', array( $this, 'footer' ) );
    }

    public function admin_init()
    {
        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        
        $this->settings_api->set_fields( $this->get_settings_fields() );

        //initialize settings
        $this->settings_api->admin_init();
    }

    public function admin_menu()
    {
        add_options_page( __( 'Intl Telephone Input', 'international-telephone-input-with-flags-and-dial-codes' ), __( 'Intl Telephone Input', 'international-telephone-input-with-flags-and-dial-codes' ), 'manage_options', 'international-telephone-input-with-flags-and-dial-codes', array( $this, 'render_telephone_input_settings' ) );
    }

	public function load_plugin_textdomain()
	{
		load_plugin_textdomain( 'international-telephone-input-with-flags-and-dial-codes', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	public function admin_enqueue_scripts()
	{
		if ( ! wp_style_is( 'select2' ) )
		{
			wp_enqueue_style( 'select2', WPITFDC_ROOT_URL . 'assets/vendor/css/select2.min.css', array(), false, 'all' );
		}

		if ( ! wp_script_is( 'select2' ) )
		{
			wp_enqueue_script( 'select2', WPITFDC_ROOT_URL . 'assets/vendor/js/select2.min.js', array(), '4.1.0-rc.0', true );
		}
		
		wp_enqueue_script( 'wpitfdc_script', WPITFDC_ROOT_URL . 'assets/js/admin.js', array( 'jquery' ), '1.0.1', true );
	}

	public function enqueue_scripts()
	{
		if ( isset( $this->options['enable'] ) && esc_attr( $this->options['enable'] ) == 'on' )
		{
			wp_enqueue_style( 'wpitfdc_intlTelInput', WPITFDC_ROOT_URL . 'assets/vendor/css/intlTelInput.min.css', array(), false, 'all' );
			
			wp_enqueue_script( 'wpitfdc_intlTelInput', WPITFDC_ROOT_URL . 'assets/vendor/js/intlTelInput-jquery.min.js', array( 'jquery' ), '1.0.1', true );
		}
	}

	public function footer()
	{
		if ( isset( $this->options['enable'] ) && esc_attr( $this->options['enable'] ) == 'on' )
		{
			$excludeCountries = empty( $this->options['excludeCountries'] ) ? array() : $this->options['excludeCountries'];
			
			$preferredCountries = empty( $this->options['preferredCountries'] ) ? array() : $this->options['preferredCountries'];
			
			$onlyCountries = empty( $this->options['onlyCountries'] ) ? array() : $this->options['onlyCountries'];

			if ( $this->options['enable_geoip_loopup'] == 'on' )
			{
				require_once WPITFDC_ROOT_DIR . '/includes/vendor/autoload.php';

				// This creates the Reader object, 
				$reader = new Reader( WPITFDC_ROOT_DIR . '/assets/vendor/GeoLite2-Country/GeoLite2-Country.mmdb' );

				try
				{	
					$VisitorGeo = $reader->country( $this->get_visitor_ip() );

					$initialCountry = $VisitorGeo->country->isoCode;
				}
				catch ( Exception $e )
				{
					$initialCountry = '';
				}
			}
			
			?>
				<script type="text/javascript">
					jQuery( document ).ready( function( $ )
					{
						var intlTelInput = $( "input[type='tel']" ).intlTelInput(
						{
							autoHideDialCode: false,
							excludeCountries: <?= json_encode( $excludeCountries ); ?>,
							initialCountry: "<?= strtolower( $initialCountry ); ?>",
							onlyCountries: <?= json_encode( $onlyCountries ); ?>,
							preferredCountries: <?= json_encode( $preferredCountries ); ?>,
							nationalMode: false,
						});
					});
				</script>
			<?php
		}
	}

    public function get_settings_sections()
    {   
        $sections = array(
            array(
                'id'    => 'wpitfdc_basic_settings',
                'title' => __( 'Plugin General Settings', 'international-telephone-input-with-flags-and-dial-codes' )
            )
        );
        
        return $sections;
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    public function get_settings_fields()
    {
    	$countries = $this->get_all_countries();
		
		$settings_fields = array(
            
            'wpitfdc_basic_settings' => array(
                array(
                    'name'    => 'enable',
                    'label'   => __( 'Enable/Disable', 'international-telephone-input-with-flags-and-dial-codes' ),
                    'desc'	  => __( ' Enable Transforming Default Telephone Input Into International Input', 'international-telephone-input-with-flags-and-dial-codes' ),
                    'type'    => 'checkbox'
                ),
                array(
                    'name'    => 'excludeCountries',
                    'label'   => __( 'Exclude Countries', 'international-telephone-input-with-flags-and-dial-codes' ),
                    'desc'	  => __( ' don\'t display these countries in the drowdown', 'international-telephone-input-with-flags-and-dial-codes' ),
                    'type'    => 'multiselect',
                    'size'	  => 'excludeCountries',
                    'options' => $countries
                ),
                array(
                    'name'    => 'onlyCountries',
                    'label'   => __( 'Only Show These Countries', 'international-telephone-input-with-flags-and-dial-codes' ),
                    'desc'	  => __( ' display only these countries in the drowdown', 'international-telephone-input-with-flags-and-dial-codes' ),
                    'type'    => 'multiselect',
                    'size'	  => 'onlyCountries',
                    'options' => $countries
                ),
                array(
                    'name'    => 'preferredCountries',
                    'label'   => __( 'Preferred Top Listed Countries', 'international-telephone-input-with-flags-and-dial-codes' ),
                    'desc'	  => __( ' the countries at the top of the list. defaults to united states and united kingdom', 'international-telephone-input-with-flags-and-dial-codes' ),
                    'type'    => 'multiselect',
                    'size'	  => 'onlyCountries',
                    'options' => $countries
                ),
                array(
                    'name'    => 'enable_geoip_loopup',
                    'label'   => __( 'Enable GeoIP Lookup', 'international-telephone-input-with-flags-and-dial-codes' ),
                    'desc'	  => __( ' Enable Detecting User Country Based On IP Address & Set it Default Automatically', 'international-telephone-input-with-flags-and-dial-codes' ),
                    'type'    => 'checkbox'
                ),
            )
        );

        return $settings_fields;
    }

    /**
     * Render settings fields
     *
     */
    public function render_telephone_input_settings()
    {    
        echo '<div class="wrap">';

	        $this->settings_api->show_navigation();
	       
	        $this->settings_api->show_forms();

        echo '</div>';
    }

    /**
     * Get All Countries Name & Code as an Associative Array
     *
     */
    public function get_all_countries()
    {    
        return array( "af" => "Afghanistan", "al" => "Albania", "dz" => "Algeria", "as" => "American Samoa", "ad" => "Andorra", "ao" => "Angola", "ai" => "Anguilla", "aq" => "Antarctica", "ag" => "Antigua and Barbuda", "ar" => "Argentina", "am" => "Armenia", "aw" => "Aruba", "au" => "Australia", "at" => "Austria", "az" => "Azerbaijan", "bs" => "Bahamas", "bh" => "Bahrain", "bd" => "Bangladesh", "bb" => "Barbados", "by" => "Belarus", "be" => "Belgium", "bz" => "Belize", "bj" => "Benin", "bm" => "Bermuda", "bt" => "Bhutan", "bo" => "Bolivia", "ba" => "Bosnia and Herzegovina", "bw" => "Botswana", "bv" => "Bouvet Island", "br" => "Brazil", "bq" => "British Antarctic Territory", "io" => "British Indian Ocean Territory", "vg" => "British Virgin Islands", "bn" => "Brunei", "bg" => "Bulgaria", "bf" => "Burkina Faso", "bi" => "Burundi", "kh" => "Cambodia", "cm" => "Cameroon", "ca" => "Canada", "ct" => "Canton and Enderbury Islands", "cv" => "Cape Verde", "ky" => "Cayman Islands", "cf" => "Central African Republic", "td" => "Chad", "cl" => "Chile", "cn" => "China", "cx" => "Christmas Island", "cc" => "Cocos [Keeling] Islands", "co" => "Colombia", "km" => "Comoros", "cg" => "Congo - Brazzaville", "cd" => "Congo - Kinshasa", "ck" => "Cook Islands", "cr" => "Costa Rica", "hr" => "Croatia", "cu" => "Cuba", "cy" => "Cyprus", "cz" => "Czech Republic", "ci" => "Côte d’Ivoire", "dk" => "Denmark", "dj" => "Djibouti", "dm" => "Dominica", "do" => "Dominican Republic", "nq" => "Dronning Maud Land", "dd" => "East Germany", "ec" => "Ecuador", "eg" => "Egypt", "sv" => "El Salvador", "gq" => "Equatorial Guinea", "er" => "Eritrea", "ee" => "Estonia", "et" => "Ethiopia", "fk" => "Falkland Islands", "fo" => "Faroe Islands", "fj" => "Fiji", "fi" => "Finland", "fr" => "France", "gf" => "French Guiana", "pf" => "French Polynesia", "tf" => "French Southern Territories", "fq" => "French Southern and Antarctic Territories", "ga" => "Gabon", "gm" => "Gambia", "ge" => "Georgia", "de" => "Germany", "gh" => "Ghana", "gi" => "Gibraltar", "gr" => "Greece", "gl" => "Greenland", "gd" => "Grenada", "gp" => "Guadeloupe", "gu" => "Guam", "gt" => "Guatemala", "gg" => "Guernsey", "gn" => "Guinea", "gw" => "Guinea-Bissau", "gy" => "Guyana", "ht" => "Haiti", "hm" => "Heard Island and McDonald Islands", "hn" => "Honduras", "hk" => "Hong Kong SAR China", "hu" => "Hungary", "is" => "Iceland", "in" => "India", "id" => "Indonesia", "ir" => "Iran", "iq" => "Iraq", "ie" => "Ireland", "im" => "Isle of Man", "il" => "Israel", "it" => "Italy", "jm" => "Jamaica", "jp" => "Japan", "je" => "Jersey", "jt" => "Johnston Island", "jo" => "Jordan", "kz" => "Kazakhstan", "ke" => "Kenya", "ki" => "Kiribati", "kw" => "Kuwait", "kg" => "Kyrgyzstan", "la" => "Laos", "lv" => "Latvia", "lb" => "Lebanon", "ls" => "Lesotho", "lr" => "Liberia", "ly" => "Libya", "li" => "Liechtenstein", "lt" => "Lithuania", "lu" => "Luxembourg", "mo" => "Macau SAR China", "mk" => "Macedonia", "mg" => "Madagascar", "mw" => "Malawi", "my" => "Malaysia", "mv" => "Maldives", "ml" => "Mali", "mt" => "Malta", "mh" => "Marshall Islands", "mq" => "Martinique", "mr" => "Mauritania", "mu" => "Mauritius", "yt" => "Mayotte", "fx" => "Metropolitan France", "mx" => "Mexico", "fm" => "Micronesia", "mi" => "Midway Islands", "md" => "Moldova", "mc" => "Monaco", "mn" => "Mongolia", "me" => "Montenegro", "ms" => "Montserrat", "ma" => "Morocco", "mz" => "Mozambique", "mm" => "Myanmar [Burma]", "na" => "Namibia", "nr" => "Nauru", "np" => "Nepal", "nl" => "Netherlands", "an" => "Netherlands Antilles", "nt" => "Neutral Zone", "nc" => "New Caledonia", "nz" => "New Zealand", "ni" => "Nicaragua", "ne" => "Niger", "ng" => "Nigeria", "nu" => "Niue", "nf" => "Norfolk Island", "kp" => "North Korea", "vd" => "North Vietnam", "mp" => "Northern Mariana Islands", "no" => "Norway", "om" => "Oman", "pc" => "Pacific Islands Trust Territory", "pk" => "Pakistan", "pw" => "Palau", "ps" => "Palestinian Territories", "pa" => "Panama", "pz" => "Panama Canal Zone", "pg" => "Papua New Guinea", "py" => "Paraguay", "yd" => "People's Democratic Republic of Yemen", "pe" => "Peru", "ph" => "Philippines", "pn" => "Pitcairn Islands", "pl" => "Poland", "pt" => "Portugal", "pr" => "Puerto Rico", "qa" => "Qatar", "ro" => "Romania", "ru" => "Russia", "rw" => "Rwanda", "re" => "Réunion", "bl" => "Saint Barthélemy", "sh" => "Saint Helena", "kn" => "Saint Kitts and Nevis", "lc" => "Saint Lucia", "mf" => "Saint Martin", "pm" => "Saint Pierre and Miquelon", "vc" => "Saint Vincent and the Grenadines", "ws" => "Samoa", "sm" => "San Marino", "sa" => "Saudi Arabia", "sn" => "Senegal", "rs" => "Serbia", "cs" => "Serbia and Montenegro", "sc" => "Seychelles", "sl" => "Sierra Leone", "sg" => "Singapore", "sk" => "Slovakia", "si" => "Slovenia", "sb" => "Solomon Islands", "so" => "Somalia", "za" => "South Africa", "gs" => "South Georgia and the South Sandwich Islands", "kr" => "South Korea", "es" => "Spain", "lk" => "Sri Lanka", "sd" => "Sudan", "sr" => "Suriname", "sj" => "Svalbard and Jan Mayen", "sz" => "Swaziland", "se" => "Sweden", "ch" => "Switzerland", "sy" => "Syria", "st" => "São Tomé and Príncipe", "tw" => "Taiwan", "tj" => "Tajikistan", "tz" => "Tanzania", "th" => "Thailand", "tl" => "Timor-Leste", "tg" => "Togo", "tk" => "Tokelau", "to" => "Tonga", "tt" => "Trinidad and Tobago", "tn" => "Tunisia", "tr" => "Turkey", "tm" => "Turkmenistan", "tc" => "Turks and Caicos Islands", "tv" => "Tuvalu", "um" => "U.S. Minor Outlying Islands", "pu" => "U.S. Miscellaneous Pacific Islands", "vi" => "U.S. Virgin Islands", "ug" => "Uganda", "ua" => "Ukraine", "su" => "Union of Soviet Socialist Republics", "ae" => "United Arab Emirates", "gb" => "United Kingdom", "us" => "United States", "zz" => "Unknown or Invalid Region", "uy" => "Uruguay", "uz" => "Uzbekistan", "vu" => "Vanuatu", "va" => "Vatican City", "ve" => "Venezuela", "vn" => "Vietnam", "wk" => "Wake Island", "wf" => "Wallis and Futuna", "eh" => "Western Sahara", "ye" => "Yemen", "zm" => "Zambia", "zw" => "Zimbabwe", "ax" => "Åland Islands" );
    }

    /**
	 * get the client IP address
	 * @return string
	 *
	 */
	public function get_visitor_ip()
	{
	    $ipaddress = '';
    
	    // If website is hosted behind CloudFlare protection.
		if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) )
			
			$ipaddress = $_SERVER['HTTP_CF_CONNECTING_IP'];

		else if ( isset( $_SERVER['X-Real-IP'] ) )
			
			$ipaddress = $_SERVER['X-Real-IP'];

	    else if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) )
	        
	        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	    
	    else if( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
	        
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	    
	    else if( isset( $_SERVER['HTTP_X_FORWARDED'] ) )
	    
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	    
	    else if( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) )
	    
	        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	    
	    else if( isset( $_SERVER['HTTP_FORWARDED'] ) )
	    
	        $ipaddress = $_SERVER['HTTP_FORWARDED'];
	    
	    else if( isset( $_SERVER['REMOTE_ADDR'] ) )
	    
	        $ipaddress = $_SERVER['REMOTE_ADDR'];
	    
	    // validate ip address
	    if ( filter_var( $ipaddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) )
	    {	
	    	return $ipaddress;
	    }

	    return $ipaddress;
	}
}

$WPITFDC_SETTINGS = new WPITFDC_SETTINGS();
