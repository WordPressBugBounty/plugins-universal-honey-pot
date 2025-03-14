<?php
/**
 * The global functions for this plugin
 * 
 * @since    1.0.0
 */

function get_universal_honey_pot_supported_plugins(){
    $supported_plugins = array(
        'contact-form-7/wp-contact-form-7.php' => array(
            'name' => __( 'Contact Form 7', 'universal-honey-pot' ),
            'img'  => UNIVERSAL_HONEY_POT_PLUGIN_URL . 'public/images/contact-form-7.svg',
        ),
        'elementor-pro/elementor-pro.php' => array(
            'name' => __( 'Elementor Pro', 'universal-honey-pot' ),
            'img'  => UNIVERSAL_HONEY_POT_PLUGIN_URL . 'public/images/elementor-pro.svg',
        ),
        'formidable/formidable.php' => array(
            'name' => __( 'Formidable Forms', 'universal-honey-pot' ),
            'img'  => UNIVERSAL_HONEY_POT_PLUGIN_URL . 'public/images/formidable-forms.png',
        ),
        'forminator/forminator.php' => array(
            'name' => __( 'Forminator', 'universal-honey-pot' ),
            'img'  => UNIVERSAL_HONEY_POT_PLUGIN_URL . 'public/images/forminator.png',
        ),
        'divi-builder/divi-builder.php' => array(
            'name' => __( 'Divi', 'universal-honey-pot' ),
            'img'  => UNIVERSAL_HONEY_POT_PLUGIN_URL . 'public/images/divi.png',
        ),
        'wpforms-lite/wpforms.php' => array(
            'name' => __( 'WPForms', 'universal-honey-pot' ),
            'img'  => UNIVERSAL_HONEY_POT_PLUGIN_URL . 'public/images/wp-forms.svg',
        ),
        'fluentform/fluentform.php' => array(
            'name' => __( 'Fluent Form', 'universal-honey-pot' ),
            'img'  => UNIVERSAL_HONEY_POT_PLUGIN_URL . 'public/images/fluent-form.png',
            'comming_soon' => true,
        ),
        'jetpack/jetpack.php' => array(
            'name' => __( 'Jetpack', 'universal-honey-pot' ),
            'img'  => UNIVERSAL_HONEY_POT_PLUGIN_URL . 'public/images/jetpack.svg',
            'comming_soon' => true,
        ),
        'gravityforms/gravityforms.php' => array(
            'name' => __( 'Gravity Forms', 'universal-honey-pot' ),
            'img'  => UNIVERSAL_HONEY_POT_PLUGIN_URL . 'public/images/gravity-forms.png',
            'comming_soon' => true,
        ),
        'everest-forms/everest-forms.php' => array(
            'name' => __( 'Everest Forms', 'universal-honey-pot' ),
            'img'  => UNIVERSAL_HONEY_POT_PLUGIN_URL . 'public/images/everest-forms.svg',
            'comming_soon' => true,
        ),
    );
    return apply_filters( 'universal_honey_pot/supported_plugins', $supported_plugins );
}

/**
 * get_universal_honey_pot_fields
 *
 * @return array
 */
function get_universal_honey_pot_fields(){
    $blog_name = get_bloginfo( 'name' );
    $hash = get_universal_honey_pot_hash();
    $array = array(
        'website'   => array(
            'label' => _x( 'Website', 'label', 'universal-honey-pot' ),
            'type'  => 'url',
        ),
        'firstname' => array(
            'label' => _x( 'First Name', 'label', 'universal-honey-pot' ),
            'type'  => 'text',
        ),
        'lastname'  => array(
            'label' => _x( 'Last Name', 'label', 'universal-honey-pot' ),
            'type'  => 'text',
        ),
        'email'    => array(
            'label' => _x( 'Email', 'label', 'universal-honey-pot' ),
            'type'  => 'email',
        ),
        $hash      => array(
            'label' =>  $blog_name,
            'type'  => 'text',
        ),
    );
    return apply_filters( 'universal_honey_pot/fields', $array );
}

/**
 * get_universal_honey_pot_hash
 *
 * @return string
 */
function get_universal_honey_pot_hash(){
    $salt = apply_filters( 'universal_honey_pot/salt', 'C99;d$QF>m6=zJnAO[VkHM})s-MSgVL(p<[ [aEpc]}>29SIn#7b}D[DRJvfY-cB' );
    if(defined('SECURE_AUTH_COOKIE') ){
        $phrase = SECURE_AUTH_COOKIE . $salt;
    } else {
        $phrase = get_bloginfo( 'url' ) . $salt;
    }
    $hash   = hash( 'sha256', $phrase );
    $hash   = substr( $hash, 0, 30 );
    return $hash;
}

/**
 * update_universal_honey_pot_counter
 *
 * @return void
 */
function update_universal_honey_pot_counter(){
    $counter = (int) get_option( 'universal_honey_pot_counter', 0 );
    $counter++;
    update_option( 'universal_honey_pot_counter', (int) $counter );
    return $counter;
}

/**
 * get_universal_honey_pot_inputs_html
 *
 * @return string
 */
function get_universal_honey_pot_inputs_html( $args = array() ){

    $args = wp_parse_args( $args, array(
        'id' => '',
    ));

    $hash                 = get_universal_honey_pot_hash();
    $hash_without_numbers = preg_replace( '/[0-9]+/', '', $hash );

    $honey_pot = '<style type="text/css">.'. $hash_without_numbers .' { position: absolute !important; left: -9999vw !important; }</style>';

    $honey_pot .= '<div id="'.$args['id'].'" class="'. $hash_without_numbers .'">';

    foreach( get_universal_honey_pot_fields() as $name => $data ) {
        $input_id = $name . '-' . $hash_without_numbers;
        $honey_pot .= '<label for="'. $input_id .'">'. $data['label'] .'</label>';
        $honey_pot .= '<input type="'. $data['type'] .'" name="'. $name .'" value="" autocomplete="disable" tabindex="-1" id="'. $input_id .'"  />';
    }

    $honey_pot .= '</div>';

    return $honey_pot;
}

/**
 * universal_honey_pot_logger
 * 
 * Activate the debug mode in the wp-config.php file
 * define( 'UNIVERSAL_HONEY_POT_DEBUG', true );
 */
function universal_honey_pot_logger( $message, $type = 'blocked' ){

    if( ! defined( 'UNIVERSAL_HONEY_POT_DEBUG' ) || ! UNIVERSAL_HONEY_POT_DEBUG ){
        return;
    }

    $uploads_dir = wp_upload_dir();
    $log_file    = $uploads_dir['basedir'] . '/universal-honey-pot/' . $type . '.log';
    $message     = '[' . date( 'Y-m-d H:i:s' ) . '] ' . $message . PHP_EOL;

    if( ! file_exists( $log_file ) ){
        $log_dir = dirname( $log_file );
        if( ! file_exists( $log_dir ) ){
            mkdir( $log_dir, 0755, true );
        }
        file_put_contents( $log_file, '' );
    }

    error_log( $message, 3, $log_file );
}

/**
 * Sanitize the input
 */
function universal_honey_pot_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'universal_honey_pot_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}