<?php
/**
 * WordPress admin UI.
 *
 * @package Hindi_To_Lat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Hindi_To_Lat_Admin {

	/** @var Hindi_To_Lat_Transliterator */
	private $transliterator;

	/** @var Hindi_To_Lat_Migration */
	private $migration;

	public function __construct( Hindi_To_Lat_Transliterator $transliterator, Hindi_To_Lat_Migration $migration ) {
		$this->transliterator = $transliterator;
		$this->migration      = $migration;

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_post_hindi_to_lat_migrate', array( $this, 'handle_migration' ) );
	}

	/** @return void */
	public function admin_menu() {
		add_options_page(
			__( 'Hindi To Lat', 'hindi-to-lat' ),
			__( 'Hindi To Lat', 'hindi-to-lat' ),
			'manage_options',
			'hindi-to-lat',
			array( $this, 'render_settings' )
		);

		add_management_page(
			__( 'Hindi To Lat Migration', 'hindi-to-lat' ),
			__( 'Hindi To Lat Migration', 'hindi-to-lat' ),
			'manage_options',
			'hindi-to-lat-migration',
			array( $this, 'render_migration' )
		);
	}

	/** @return void */
	public function register_settings() {
		register_setting(
			'hindi_to_lat',
			'hindi_to_lat_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array( 'filenames' => 1 ),
			)
		);

		register_setting(
			'hindi_to_lat',
			'hindi_to_lat_dictionary_text',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_dictionary_text' ),
				'default'           => '',
			)
		);
	}

	/** @return array */
	public function sanitize_settings( $value ) {
		$value = is_array( $value ) ? $value : array();
		return array(
			'filenames' => empty( $value['filenames'] ) ? 0 : 1,
		);
	}

	/**
	 * Store a human-editable "Hindi = latin" dictionary and mirror it as an
	 * array option consumed by the runtime.
	 *
	 * @param string $value Textarea value.
	 * @return string
	 */
	public function sanitize_dictionary_text( $value ) {
		$value      = sanitize_textarea_field( (string) $value );
		$dictionary = array();

		foreach ( preg_split( '/\r\n|\r|\n/', $value ) as $line ) {
			$line = trim( $line );
			if ( '' === $line || 0 === strpos( $line, '#' ) || false === strpos( $line, '=' ) ) {
				continue;
			}

			list( $from, $to ) = array_map( 'trim', explode( '=', $line, 2 ) );
			if ( '' === $from || '' === $to || ! $this->transliterator->contains_devanagari( $from ) ) {
				continue;
			}

			$to = sanitize_title_with_dashes( $to, '', 'save' );
			if ( '' !== $to ) {
				$dictionary[ $from ] = $to;
			}
		}

		update_option( 'hindi_to_lat_dictionary', $dictionary, false );
		return $value;
	}

	/** @return void */
	public function render_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = get_option( 'hindi_to_lat_settings', array( 'filenames' => 1 ) );
		$text     = get_option( 'hindi_to_lat_dictionary_text', '' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Hindi To Lat', 'hindi-to-lat' ); ?></h1>
			<p><?php esc_html_e( 'Version 2 transliterates new Hindi slugs safely. Existing published URLs are never bulk-changed on activation.', 'hindi-to-lat' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'hindi_to_lat' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Media filenames', 'hindi-to-lat' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="hindi_to_lat_settings[filenames]" value="1" <?php checked( ! empty( $settings['filenames'] ) ); ?> />
								<?php esc_html_e( 'Transliterate Hindi upload filenames.', 'hindi-to-lat' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="hindi-to-lat-dictionary"><?php esc_html_e( 'Custom dictionary', 'hindi-to-lat' ); ?></label></th>
						<td>
							<textarea id="hindi-to-lat-dictionary" class="large-text code" rows="10" name="hindi_to_lat_dictionary_text"><?php echo esc_textarea( $text ); ?></textarea>
							<p class="description"><?php esc_html_e( 'One replacement per line, for example: जैसलमेर = jaisalmer. Exact dictionary replacements run before automatic transliteration.', 'hindi-to-lat' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
			<p><a href="<?php echo esc_url( admin_url( 'tools.php?page=hindi-to-lat-migration' ) ); ?>"><?php esc_html_e( 'Open the legacy URL migration tool', 'hindi-to-lat' ); ?></a></p>
		</div>
		<?php
	}

	/** @return void */
	public function render_migration() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$candidates = $this->migration->preview( 50 );
		$result     = isset( $_GET['htl_result'] ) ? sanitize_text_field( wp_unslash( $_GET['htl_result'] ) ) : '';
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Hindi To Lat — Legacy URL Migration', 'hindi-to-lat' ); ?></h1>
			<div class="notice notice-warning inline"><p><strong><?php esc_html_e( 'SEO safety:', 'hindi-to-lat' ); ?></strong> <?php esc_html_e( 'This tool changes only URLs you explicitly select. Review the preview and take a database backup before migration.', 'hindi-to-lat' ); ?></p></div>
			<?php if ( '' !== $result ) : ?>
				<div class="notice notice-success inline"><p><?php echo esc_html( $result ); ?></p></div>
			<?php endif; ?>

			<?php if ( empty( $candidates ) ) : ?>
				<p><?php esc_html_e( 'No legacy Devanagari slugs were found in the current preview window.', 'hindi-to-lat' ); ?></p>
				<?php return; ?>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="hindi_to_lat_migrate" />
				<?php wp_nonce_field( 'hindi_to_lat_migrate_batch' ); ?>
				<table class="widefat striped">
					<thead><tr>
						<td class="manage-column check-column"><input type="checkbox" id="htl-select-all" /></td>
						<th><?php esc_html_e( 'Object', 'hindi-to-lat' ); ?></th>
						<th><?php esc_html_e( 'Current slug', 'hindi-to-lat' ); ?></th>
						<th><?php esc_html_e( 'Proposed slug', 'hindi-to-lat' ); ?></th>
					</tr></thead>
					<tbody>
					<?php foreach ( $candidates as $candidate ) : ?>
						<tr>
							<th class="check-column"><input type="checkbox" name="candidate[]" value="<?php echo esc_attr( $candidate['key'] ); ?>" /></th>
							<td><strong><?php echo esc_html( $candidate['label'] ? $candidate['label'] : $candidate['key'] ); ?></strong><br /><code><?php echo esc_html( $candidate['key'] ); ?></code></td>
							<td><code><?php echo esc_html( $candidate['old_slug'] ); ?></code></td>
							<td><code><?php echo esc_html( $candidate['new_slug'] ); ?></code></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php submit_button( __( 'Convert selected URLs and remember 301 redirects', 'hindi-to-lat' ), 'primary', 'submit', true, array( 'onclick' => "return confirm('Change the selected published URLs?');" ) ); ?>
			</form>
			<script>
			(function(){
				var all = document.getElementById('htl-select-all');
				if (!all) return;
				all.addEventListener('change', function(){
					document.querySelectorAll('input[name="candidate[]"]').forEach(function(box){ box.checked = all.checked; });
				});
			}());
			</script>
		</div>
		<?php
	}

	/** @return void */
	public function handle_migration() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to migrate URLs.', 'hindi-to-lat' ) );
		}

		check_admin_referer( 'hindi_to_lat_migrate_batch' );
		$keys   = isset( $_POST['candidate'] ) ? (array) wp_unslash( $_POST['candidate'] ) : array();
		$result = $this->migration->convert( $keys );
		$message = sprintf(
			/* translators: 1: converted count, 2: skipped count, 3: error count */
			__( 'Converted: %1$d; skipped: %2$d; errors: %3$d.', 'hindi-to-lat' ),
			absint( $result['converted'] ),
			absint( $result['skipped'] ),
			count( $result['errors'] )
		);

		wp_safe_redirect(
			add_query_arg(
				'htl_result',
				rawurlencode( $message ),
				admin_url( 'tools.php?page=hindi-to-lat-migration' )
			)
		);
		exit;
	}
}
