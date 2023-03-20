<?php
/**
 * The file that defines the plugin settings class
 *
 * This is used to define saving settings, writing wp-config.php defines.
 *
 * @link https://vcore.au
 *
 * @package CF_Images
 * @subpackage CF_Images/App
 * @author Anton Vanyukov <a.vanyukov@vcore.ru>
 * @since 1.0.0
 */

namespace CF_Images\App;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The plugin settings class.
 *
 * @since 1.0.0
 */
class Settings {

	use Traits\Ajax;

	/**
	 * Do initial setup by storing user provided Cloudflare account ID and API key in wp-config.php file.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function ajax_do_setup() {

		$this->check_ajax_request();

		// Data sanitized later in code.
		parse_str( wp_unslash( $_POST['data'] ), $form ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( ! isset( $form['account-id'] ) || ! isset( $form['api-key'] ) ) {
			wp_die();
		}

		$this->write_config( 'CF_IMAGES_ACCOUNT_ID', sanitize_text_field( $form['account-id'] ) );
		$this->write_config( 'CF_IMAGES_KEY_TOKEN', sanitize_text_field( $form['api-key'] ) );

		// Remove any auth errors.
		delete_option( 'cf-images-auth-error' );

		wp_send_json_success();

	}

	/**
	 * Save settings.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function ajax_save_settings() {

		$this->check_ajax_request();

		// Data sanitized later in code.
		parse_str( wp_unslash( $_POST['data'] ), $form ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// List of settings. The key corresponds to the name of the form field, the value corresponds to the name of the option.
		$options = array(
			'auto-offload'  => 'cf-images-auto-offload',
			'custom-id'     => 'cf-images-custom-id',
			'auto-resize'   => 'cf-images-auto-resize',
			'disable-sizes' => 'cf-images-disable-generation',
			'disable-async' => 'cf-images-disable-async',
		);

		foreach ( $options as $key => $option ) {
			if ( ! isset( $form[ $key ] ) ) {
				delete_option( $option );
			} else {
				update_option( $option, (bool) $form[ $key ], false );
			}
		}

		if ( ! isset( $form['custom-domain'] ) ) {
			delete_option( 'cf-images-custom-domain' );
		} else {
			$value = (bool) $form['custom-domain'];
			if ( isset( $form['custom_domain_input'] ) ) {
				$url = esc_url( $form['custom_domain_input'] );
				if ( wp_http_validate_url( $url ) ) {
					$value = $url;
				}
			}

			update_option( 'cf-images-custom-domain', $value, false );
		}

		wp_send_json_success();

	}

	/**
	 * Write key/value pair to wp-config.php file.
	 *
	 * If the $value is not set, the constant will be removed.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name       Name of the constant to add.
	 * @param string $value      Value of the constant to add.
	 * @param bool   $overwrite  Overwrite the current value. Default: false.
	 *
	 * @return void
	 */
	public function write_config( string $name, string $value = '', bool $overwrite = false ) {

		$path_to_wp_config = ABSPATH . 'wp-config.php';

		// wp-config.php file not found - exit early.
		if ( ! file_exists( $path_to_wp_config ) ) {
			return;
		}

		$config_file = file( $path_to_wp_config );

		$new_file_content = array();
		foreach ( $config_file as $line ) {
			if ( preg_match( "/define\(\s*'$name'/i", $line ) && ! $overwrite ) {
				continue;
			}

			if ( ! empty( $value ) && preg_match( "/\/\* That's all, stop editing!.*/i", $line ) ) {
				$new_file_content[] = "define( '$name', '$value' );\n";

				// Exit early, if we are overwriting.
				if ( $overwrite ) {
					continue;
				}
			}

			$new_file_content[] = $line;
		}

		$this->write( $path_to_wp_config, $new_file_content );

		update_option( 'cf-images-config-written', ! empty( $value ), false );

		// On some hosts, the wp-config.php updates take several seconds to "un-cache".
		sleep( 2 );

	}

	/**
	 * Filesystem write to wp-config.php file.
	 *
	 * @since 1.0.0
	 *
	 * @param string $wp_config_path  Path to wp-config.php file.
	 * @param array  $content         Array of lines to add to the file.
	 *
	 * @return void
	 */
	private function write( string $wp_config_path, array $content ) {

		$handle = fopen( $wp_config_path, 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
		fwrite( $handle, implode( '', $content ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose

		if ( ! defined( 'FS_CHMOD_FILE' ) ) {
			define( 'FS_CHMOD_FILE', ( fileperms( ABSPATH . 'index.php' ) & 0777 | 0644 ) );
		}

		chmod( $wp_config_path, FS_CHMOD_FILE );

	}

	/**
	 * Disconnect from Cloudflare.
	 *
	 * @since 1.1.2
	 *
	 * @return void
	 */
	public function ajax_disconnect() {

		delete_site_option( 'cf-images-hash' );
		delete_option( 'cf-images-setup-done' );
		delete_option( 'cf-images-config-written' );
		delete_option( 'cf-images-auth-error' );

		// Remove defines from wp-config.php file.
		$this->write_config( 'CF_IMAGES_ACCOUNT_ID' );
		$this->write_config( 'CF_IMAGES_KEY_TOKEN' );

		wp_send_json_success();

	}

}
