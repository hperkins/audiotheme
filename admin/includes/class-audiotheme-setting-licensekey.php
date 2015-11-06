<?php
/**
 * License key setting.
 *
 * @package AudioTheme\Settings
 * @since 1.9.0
 */

/**
 * License key setting class.
 *
 * @package AudioTheme\Settings
 * @since 1.9.0
 */
class AudioTheme_Setting_LicenseKey {
	/**
	 * Option group.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $page = 'audiotheme-settings';

	/**
	 * License key option name.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $option_name = 'audiotheme_license_key';

	/**
	 * Constructor method.
	 *
	 * @since 1.9.0
	 */
	public function __construct() {
		if ( is_multisite() ) {
			$this->page = 'audiotheme-network-settings';
		}
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_audiotheme_ajax_activate_license', array( $this,'ajax_activate_license' ) );
		add_action( 'audiotheme_update_response_error',         array( $this, 'clear_status' ) );
		add_action( 'update_option_' . $this->option_name,      array( $this, 'on_option_update' ) );
		add_action( 'audiotheme_save_network_settings',         array( $this, 'save_network_settings' ) );

		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_init', array( $this, 'add_sections' ) );
		add_action( 'admin_init', array( $this, 'add_fields' ) );
	}

	/**
	 * Register the settings option.
	 *
	 * @since 1.9.0
	 */
	public function register_settings() {
		register_setting(
			$this->page,
			$this->option_name,
			'sanitize_text_field'
		);
	}

	/**
	 * Add settings sections.
	 *
	 * @since 1.9.0
	 */
	public function add_sections() {
		add_settings_section(
			'audiotheme-license',
			__( 'License', 'audiotheme' ),
			array( $this, 'display_section_description' ),
			$this->page
		);
	}

	/**
	 * Register settings fields.
	 *
	 * @since 1.9.0
	 */
	public function add_fields() {
		add_settings_field(
			$this->option_name,
			__( 'License Key', 'audiotheme' ),
			array( $this, 'display_field' ),
			$this->page,
			'audiotheme-license'
		);
	}

	/**
	 * Display the license section.
	 *
	 * @since 1.9.0
	 */
	public function display_section_description() {
		printf(
			__( 'Find your license key in <a href="%s" target="_blank">your account</a> on AudioTheme.com. Your license key allows you to recieve automatic upgrades and support.', 'audiotheme' ),
			'https://audiotheme.com/account/'
		);

		wp_enqueue_script( 'audiotheme-license' );
	}

	/**
	 * Display the license key field.
	 *
	 * @since 1.9.0
	 */
	public function display_field() {
		$value  = get_option( $this->option_name, '' );
		$status = get_option( 'audiotheme_license_status' );
		?>
		<p>
			<input type="text" name="<?php echo $this->option_name; ?>" id="audiotheme-license-key" value="<?php echo esc_attr( $value ); ?>" class="audiotheme-settings-license-text audiotheme-settings-text regular-text">

			<?php if ( ! isset( $status->status ) || 'ok' !== $status->status ) : ?>
				<input type="button" value="<?php esc_attr_e( 'Activate', 'audiotheme' ); ?>" disabled="disabled" class="audiotheme-settings-license-button button button-primary">
				<span class="spinner" style="float: none; margin-top: 0; vertical-align: middle"></span>
				<br><span class="audiotheme-response"></span>
			<?php else : ?>
				<strong class="audiotheme-response is-valid"><?php esc_html_e( 'Activated!', 'audiotheme' ); ?></strong>
			<?php endif; ?>
		</p>
		<?php
	}

	/**
	 * Send a request to the remote API to activate the license for the current
	 * site.
	 *
	 * @since 1.9.0
	 */
	public function ajax_activate_license() {
		check_ajax_referer( 'audiotheme-activate-license', 'nonce' );

		$key = sanitize_key( $_POST['license'] );
		update_option( $this->option_name, $key );

		$updater = new Audiotheme_Updater();
		$response = $updater->activate_license( $key );
		update_option( 'audiotheme_license_status', $response );

		if ( isset( $response->status ) && 'ok' === $response->status ) {
			// @todo Clear the last update status check with a 'not_activated' response.
			update_option( $this->option_name, $key );
		}

		wp_send_json_success( $response );
	}

	/**
	 * Clear the license status option when the key is modified.
	 *
	 * Forces the new key to be activated.
	 *
	 * @since 1.9.0
	 */
	public function on_option_update() {
		update_option( 'audiotheme_license_status', '' );
	}

	/**
	 * Clear the license status option if an update response was invalid.
	 *
	 * Forces the license key to be reactivated.
	 *
	 * @since 1.9.0
	 *
	 * @param object $response Update response.
	 */
	public function clear_status( $response ) {
		$license_errors = array(
			'empty_license',
			'expired_license',
			'invalid_license',
			'not_activated',
		);

		if ( ! isset( $response->status ) || in_array( $response->status, $license_errors ) ) {
			update_option( 'audiotheme_license_status', '' );
		}
	}

	/**
	 * Manually save network settings.
	 *
	 * @since 1.9.0
	 */
	public function save_network_settings() {
		$is_valid_nonce = ! empty( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'audiotheme-network-settings-options' );

		if ( ! is_network_admin() || ! $is_valid_nonce ) {
			return;
		}

		// Update the license key.
		$key = empty( $_POST[ $this->option_name ] ) ? '' : sanitize_key( $_POST[ $this->option_name ] );
		update_option( $this->option_name, $key );
	}
}
