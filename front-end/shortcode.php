<?php
/**
 * Description: This file contains the shortcode handler for the
 * 'influactive_form' shortcode.
 *
 * @throws RuntimeException If the WordPress environment is not loaded.
 * @package Influactive Forms
 */

if ( ! defined( 'ABSPATH' ) ) {
	throw new RuntimeException( 'WordPress environment not loaded. Exiting...' );
}

/**
 * Registers the 'influactive_form' shortcode.
 */
function register_influactive_form_shortcode(): void {
	add_shortcode( 'influactive_form', 'influactive_form_shortcode_handler' );
}

add_action( 'init', 'register_influactive_form_shortcode', 1 );

/**
 * Handles the influactive form shortcode.
 *
 * @param array $atts The shortcode attributes.
 *
 * @return string The HTML generated by the shortcode.
 * @throws RuntimeException If the form ID is not found.
 */
function influactive_form_shortcode_handler( array $atts ): string {
	ob_start();

	$atts = shortcode_atts(
		array(
			'id'      => '0',
			'form_id' => '0',
		),
		$atts,
		'influactive_form'
	);

	$form_id = (int) $atts['id'];

	if ( ! $form_id ) {
		throw new RuntimeException( 'Form ID not found. Exiting...' );
	}

	// Showing the form if it exists.
	$form = get_post( $form_id );

	if ( $form ) {
		update_post_meta( get_the_ID(), 'influactive_form_id', $form_id );

		$fields = get_post_meta( $form_id, '_influactive_form_fields', true ) ?? array();
		?>
		<div class="influactive-form-wrapper">
			<form id="influactive-form-<?php echo esc_attr( $form_id ); ?>"
						class="influactive-form">

				<?php
				wp_nonce_field( 'influactive_send_email', 'nonce' );
				?>

				<input type="hidden" name="form_id"
							 value="<?php echo esc_attr( $form_id ); ?>">

				<?php
				$options_captcha  = get_option( 'influactive-forms-captcha-fields' ) ?? array();
				$public_site_key  = $options_captcha['google-captcha']['public-site-key'] ?? '';
				$secret_site_key  = $options_captcha['google-captcha']['secret-site-key'] ?? '';

				if ( ! empty( $public_site_key ) && ! empty( $secret_site_key ) ) {
					?>

					<input type="hidden"
								 id="recaptchaResponse-<?php echo esc_attr( $form_id ); ?>"
								 name="recaptcha_response">
					<input type="hidden"
								 id="recaptchaSiteKey-<?php echo esc_attr( $form_id ); ?>"
								 name="recaptcha_site_key"
								 value="<?php echo esc_attr( $public_site_key ); ?>">

					<?php
				}

				if ( is_plugin_active( 'influactive-forms/functions.php' ) && get_option( 'modal_form_select' ) ) {
					?>

					<input type="hidden" name="brochure"
								 value="<?php echo esc_attr( get_option( 'modal_form_file_select' ) ); ?>">

					<?php
				}

				foreach ( $fields as $field ) {
					if ( isset( $field['required'] ) && '1' === $field['required'] ) {
						$required = 'required';
					} else {
						$required = '';
					}

					switch ( $field['type'] ) {
						case 'text':
							?>

							<label>
								<?php echo esc_attr( $field['label'] ); ?>:
								<input type="text" <?php echo esc_attr( $required ); ?>
											 name="<?php echo esc_attr( $field['name'] ); ?>">
							</label>

							<?php
							break;
						case 'email':
							?>

							<label>
								<?php echo esc_attr( $field['label'] ); ?>:
								<input type="email" <?php echo esc_attr( $required ); ?>
											 name="<?php echo esc_attr( $field['name'] ); ?>"
											 autocomplete="email">
							</label>

							<?php
							break;
						case 'number':
							?>

							<label>
								<?php echo esc_attr( $field['label'] ); ?>:
								<input
									type="number" <?php echo esc_attr( $required ); ?>
									name="<?php echo esc_attr( $field['name'] ); ?>">
							</label>

							<?php
							break;
						case 'textarea':
							?>

							<label>
								<?php echo esc_attr( $field['label'] ); ?>:
								<textarea <?php echo esc_attr( $required ); ?>
									name="<?php echo esc_attr( $field['name'] ); ?>"
									rows="10">
								</textarea>
							</label>

							<?php
							break;
						case 'select':
							?>

							<label>
								<?php echo esc_attr( $field['label'] ); ?>:
								<select <?php echo esc_attr( $required ); ?>
									name="<?php echo esc_attr( $field['name'] ); ?>">

									<?php
									foreach ( $field['options'] as $option ) {
										?>

										<option
											value="<?php echo esc_attr( $option['value'] ); ?>:<?php echo esc_attr( $option['label'] ); ?>">
											<?php echo esc_attr( $option['label'] ); ?>
										</option>

										<?php
									}
									?>

								</select>
							</label>

							<?php
							break;
						case 'gdpr':
							$gdpr_translate = __( 'Check our Privacy Policy', 'influactive-forms' );
							$gdpr_text  = '<a href="' . get_privacy_policy_url() . '" target="_blank" title="Privacy Policy">' . $gdpr_translate . '</a>';
							$pp_content = get_privacy_policy_url() ? $gdpr_text : '';
							?>

							<label>
								<input type="checkbox"
											 name="<?php echo esc_attr( $field['name'] ); ?>"
											 required>
								<?php echo esc_attr( $field['label'] ) . ' ' . esc_attr( $pp_content ); ?>
							</label>

							<?php
							break;
						case 'free_text':
							?>

							<div class="free-text">
								<?php echo esc_attr( $field['label'] ); ?>
							</div>

							<input type="hidden"
										 name="<?php echo esc_attr( $field['name'] ); ?>"
										 value="<?php echo esc_attr( $field['label'] ); ?>">

							<?php
							break;
					}
				}
				?>

				<input type="submit">
				<div class="influactive-form-message"></div>
			</form>
		</div>

		<?php
	}

	return ob_get_clean();
}

/**
 * Enqueues the dynamic style file for a specific form.
 *
 * @throws RuntimeException If the WordPress environment is not loaded or form
 *   ID is not found.
 */
function enqueue_form_dynamic_style(): void {
	if ( is_admin() ) {
		throw new RuntimeException( 'WordPress environment not loaded. Exiting...' );
	}

	$form_id = get_post_meta( get_the_ID(), 'influactive_form_id', true ) ?? 0;
	if ( ! $form_id ) {
		throw new RuntimeException( 'Form ID not found. Exiting...' );
	}

	wp_enqueue_style(
		'influactive-form-dynamic-style',
		plugin_dir_url( __FILE__ ) . '/dynamic-style.php?post_id=' . $form_id,
		array(),
		'1.2.6'
	);
}

add_action( 'wp_enqueue_scripts', 'enqueue_form_dynamic_style' );

/**
 * Sends an email based on the submitted form data.
 *
 * @return void
 * @throws RuntimeException If the WordPress environment is not loaded.
 */
function influactive_send_email(): void {
	$_POST = array_map( 'sanitize_text_field', $_POST );

	if ( isset( $_POST['nonce'] ) ) {
		$nonce = sanitize_text_field( wp_unslash( $_POST['nonce'] ) );
	}

	// Check if our nonce is set and verify it.
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'influactive_send_email' ) ) {
		wp_send_json_error( array( 'message' => __( 'Nonce verification failed', 'influactive-forms' ) ) );

		exit;
	}

	if ( empty( $_POST['form_id'] ) ) {
		wp_send_json_error( array( 'message' => __( 'Form ID is required', 'influactive-forms' ) ) );

		exit;
	}

	$form_id = (int) $_POST['form_id'];

	$fields = get_post_meta( $form_id, '_influactive_form_fields', true ) ?? array();

	foreach ( $fields as $field ) {
		if ( isset( $_POST[ $field['name'] ] ) && empty( $_POST[ $field['name'] ] ) && '1' === $field['required'] ) {
			$name = $field['name'];
			/* translators: %s is a placeholder for the field name */
			$message = sprintf( __( 'The field %s is required', 'influactive-forms' ), $name );
			wp_send_json_error( array( 'message' => $message ) );

			exit;
		}
	}

	$email_layout = get_post_meta( $form_id, '_influactive_form_email_layout', true ) ?? array();
	$sitename     = get_bloginfo( 'name' );

	$options_captcha = get_option( 'influactive-forms-captcha-fields' ) ?? array();
	$secret_site_key = $options_captcha['google-captcha']['secret-site-key'] ?? '';

	if ( isset( $_POST['recaptcha_site_key'] ) ) {
		$public_site_key = sanitize_text_field( $_POST['recaptcha_site_key'] );
	}

	if ( isset( $_POST['recaptcha_response'] ) ) {
		$recaptcha_response = sanitize_text_field( $_POST['recaptcha_response'] );
	}

	if ( ! empty( $secret_site_key ) && ! empty( $public_site_key ) && isset( $recaptcha_response ) ) {
		$recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';

		$url = $recaptcha_url
					 . '?secret=' .
					 urlencode( $secret_site_key )
					 . '&response=' . urlencode( $recaptcha_response );

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		try {
			$response = curl_exec( $ch );
			if ( curl_errno( $ch ) ) {
				throw new RuntimeException( curl_error( $ch ) );
			}
		} catch ( RuntimeException $e ) {
			wp_send_json_error( array(
				'message' => __( 'Failed to verify reCAPTCHA', 'influactive-forms' ),
				'error'   => $e->getMessage(),
			) );
			curl_close( $ch );
			exit;
		}
		curl_close( $ch );

		try {
			$recaptcha = json_decode( $response, false, 512, JSON_THROW_ON_ERROR );

			if ( $recaptcha->score < 0.5 ) {
				// Not likely to be a human
				wp_send_json_error( array(
					'message' => __( 'Bot detected', 'influactive-forms' ),
					'score'   => $recaptcha->score,
				) );

				exit;
			}
		} catch ( JsonException $e ) {
			wp_send_json_error( array(
				'message' => __( 'Failed to verify reCAPTCHA', 'influactive-forms' ),
				'error'   => $e->getMessage(),
			) );

			exit;
		}
	}

	$layouts = $email_layout ?? array();
	$error   = 0;
	foreach ( $layouts as $layout ) {
		$content      = $layout['content'] ?? '';
		$subject      = $layout['subject'] ?? '';
		$to           = $layout['recipient'] ?? get_bloginfo( 'admin_email' );
		$from         = $layout['sender'] ?? get_bloginfo( 'admin_email' );
		$allowed_html = [
			'br'         => array(),
			'p'          => array(),
			'a'          => [
				'href'   => array(),
				'title'  => array(),
				'target' => array(),
			],
			'h1'         => array(),
			'h2'         => array(),
			'h3'         => array(),
			'h4'         => array(),
			'h5'         => array(),
			'h6'         => array(),
			'strong'     => array(),
			'em'         => array(),
			'ul'         => array(),
			'ol'         => array(),
			'li'         => array(),
			'blockquote' => array(),
			'pre'        => array(),
			'code'       => array(),
			'img'        => [
				'src' => array(),
				'alt' => array(),
			],
		];

		foreach ( $fields as $field ) {
			// Convert textarea newlines to HTML breaks
			if ( $field['type'] === 'textarea' ) {
				$_POST[ $field['name'] ] = nl2br( $_POST[ $field['name'] ] );
				$content                 = str_replace(
					'{' . $field['name'] . '}',
					wp_kses( $_POST[ $field['name'] ], $allowed_html ),
					$content
				);
				$subject                 = str_replace(
					'{' . $field['name'] . '}',
					wp_kses( $_POST[ $field['name'] ], $allowed_html ),
					$subject
				);
				$to                      = str_replace(
					'{' . $field['name'] . '}',
					wp_kses( $_POST[ $field['name'] ], $allowed_html ),
					$to
				);
				$from                    = str_replace(
					'{' . $field['name'] . '}',
					wp_kses( $_POST[ $field['name'] ], $allowed_html ),
					$from
				);
			} elseif ( $field['type'] === 'select' ) {
				$content = replace_field_placeholder( $content, $field['name'], explode( ':', $_POST[ $field['name'] ] ) );
				$subject = replace_field_placeholder( $subject, $field['name'], explode( ':', $_POST[ $field['name'] ] ) );
				$to      = replace_field_placeholder( $to, $field['name'], explode( ':', $_POST[ $field['name'] ] ) );
				$from    = replace_field_placeholder( $from, $field['name'], explode( ':', $_POST[ $field['name'] ] ) );
			} elseif ( $field['type'] === 'email' ) {
				$content = str_replace( '{' . $field['name'] . '}', sanitize_email( $_POST[ $field['name'] ] ), $content );
				$subject = str_replace( '{' . $field['name'] . '}', sanitize_email( $_POST[ $field['name'] ] ), $subject );
				$to      = str_replace( '{' . $field['name'] . '}', sanitize_email( $_POST[ $field['name'] ] ), $to );
				$from    = str_replace( '{' . $field['name'] . '}', sanitize_email( $_POST[ $field['name'] ] ), $from );
			} else {
				$content = str_replace(
					'{' . $field['name'] . '}',
					sanitize_text_field( $_POST[ $field['name'] ] ),
					$content
				);
				$subject = str_replace(
					'{' . $field['name'] . '}',
					sanitize_text_field( $_POST[ $field['name'] ] ),
					$subject
				);
				$to      = str_replace(
					'{' . $field['name'] . '}',
					sanitize_text_field( $_POST[ $field['name'] ] ),
					$to
				);
				$from    = str_replace(
					'{' . $field['name'] . '}',
					sanitize_text_field( $_POST[ $field['name'] ] ),
					$from
				);
			}
		}

		if ( isset( $_POST['brochure'] )
				 && is_plugin_active( 'influactive-forms/functions.php' ) && get_option( 'modal_form_select' ) ) {
			$relative_url  = wp_get_attachment_url( $_POST['brochure'] );
			$file_url      = home_url( $relative_url );
			$download_link = sprintf(
			/* translators: %s is a placeholder for the file URL */
				__(
					"<a href='%s' target='_blank' title='Download our brochure'>Download our brochure</a>",
					'influactive-forms'
				),
				$file_url
			);
			$content       = str_replace( '{brochure}', $download_link, $content );
		}

		$from = sanitize_email( $from );
		$to   = sanitize_email( $to );

		$headers = [
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . $sitename . ' <' . $from . '>',
			'Reply-To: ' . $from,
		];

		if ( ! wp_mail( $to, $subject, $content, $headers ) ) {
			$error ++;
		}
	}

	if ( $error === 0 ) {
		wp_send_json_success( array(
			'message' => __( 'Email sent successfully', 'influactive-forms' ),
		) );
	} else {
		wp_send_json_error( array(
			'message' => __( 'Failed to send email', 'influactive-forms' ),
		) );

		exit;
	}

	exit;
}

add_action( 'wp_ajax_send_email', 'influactive_send_email' );
add_action( 'wp_ajax_nopriv_send_email', 'influactive_send_email' );

/**
 * Replaces field placeholders in a string with the corresponding label and
 * value.
 *
 * @param string $string The string to replace placeholders in.
 * @param string $field_name The name of the field.
 * @param array $label_value An array containing the label and value of the
 *         field.
 *
 * @return string The string with replaced placeholders.
 */
function replace_field_placeholder( string $string, string $field_name, array $label_value ): string {
	// Replace label placeholder if it exists
	if ( str_contains( $string, '{' . $field_name . ':label}' ) ) {
		$string = str_replace( '{' . $field_name . ':label}', $label_value[1], $string );
	}

	// Replace value placeholder if it exists
	if ( str_contains( $string, '{' . $field_name . ':value}' ) ) {
		$string = str_replace( '{' . $field_name . ':value}', $label_value[0], $string );
	}

	return $string;
}
