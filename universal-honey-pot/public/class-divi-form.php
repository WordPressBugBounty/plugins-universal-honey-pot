<?php

class Universal_Honey_Pot_Divi_Form {

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
    const PATH = 'divi-builder/divi-builder.php';

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
    public function add_honey_pot_fields_to_form( $html, $props, $attrs, $render_slug ) {
        if( $render_slug === 'et_pb_contact_form' ) {           

            $uniq_id    = 'universal-honey-pot-' . rand( 1000, 9999 );
            $script     = $this->validation_script($uniq_id);
            $args       = array(
                'id' => $uniq_id
            );

            return get_universal_honey_pot_inputs_html($args) . $html . $script;
        }
        return $html;
    }

    
    /**
     * validate_honey_pot_fields
     *
     * @param  mixed $spam
     * @return void
     */
    public function validate_honey_pot_fields( $processed_fields_values, $et_contact_error, $contact_form_info ) {

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

        if ( $spam  || $user_behaviour_suspected ) {
            update_universal_honey_pot_counter();
            $_POST = array();
        }
    }

    /**
     * Empty the $_POST global variable
     */
    private function empty_post() {
        foreach( $_POST as $key => $value ) {
            unset( $_POST[ $key ] );
        }
    }

    /**
     * JS script to validate the form
     */
    private function validation_script($uniq_id) {

        ob_start();

        ?>
            <script>

                'use strict';
                    
                (function(){

                    var currentForm = document.querySelector('form:has(#<?php echo $uniq_id; ?>)');
                    if ( !currentForm ) return;

                    currentForm.addEventListener('click', function(e){

                        var submitBtn = e.target.closest('button[type="submit"]');

                        if ( submitBtn ) {

                            var honeyPot = currentForm.querySelector('#<?php echo $uniq_id; ?>');
                            if ( honeyPot ) {
                                
                                var removeHoneyPot  = true;
                                var inputs          = honeyPot.querySelectorAll('input');
    
                                inputs.forEach(input => {
                                    if ( input.value !== '' ) {
                                        removeHoneyPot = false;
                                    }
                                });
    
                                if ( removeHoneyPot ) {
                                    honeyPot.remove();
                                }
                            }
                        }
                    });

                })();

            </script>
        <?php

        return ob_get_clean();
    }
}
