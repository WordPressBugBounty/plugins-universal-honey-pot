<?php

class Universal_Honey_Pot_Wpforms {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The path of the plugin.
	 */
	const PATH = 'wpforms-lite/wpforms.php';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}
    
    /**
     * add_honey_pot_fields_to_form
     *
     * @param  mixed $content
     * @return void
     */
    public function add_honey_pot_fields_to_form( $html ) {        
        echo get_universal_honey_pot_inputs_html();
    }

    
    /**
     * validate_honey_pot_fields
     *
     * @param  mixed $spam
     * @return void
     */
    public function validate_honey_pot_fields( $fields, $entry, $form_data, $entry_id ) {

		$filter_by_user_behaviour = get_option( 'universal_honey_pot_use_user_behaviour', array() );
		$user_behaviour_value     = $filter_by_user_behaviour[ self::PATH ] ?? '0';
		$spam                     = false;
		$user_behaviour_suspected = false;

		if ( $user_behaviour_value == '1' ) {
			$block_spam_class         = new Universal_Honey_Pot_Block_Spam();
			$user_behaviour_suspected = $block_spam_class->is_spam();
		}

        $hash = get_universal_honey_pot_hash();
        foreach( get_universal_honey_pot_fields() as $name => $data ) {
            $spam = isset( $_POST[ $name ] ) && !empty( $_POST[ $name ] ) ? true : $spam;
        }

        if( $spam || $user_behaviour_suspected ) {
            wpforms()->process->errors[ $form_data['id'] ] = __( 'You are a spammer !', 'universal-honey-pot' );
        }
    }
}
