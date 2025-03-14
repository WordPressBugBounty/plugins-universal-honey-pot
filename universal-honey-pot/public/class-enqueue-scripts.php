<?php

/**
 * The class responsible of enqueue scripts.
 * 
 */
class Universal_Honey_Pot_Enqueue_Scripts {

    /**
     * Enqueue scripts in the front.
     */
    public function enqueue_scripts_front() {

        wp_enqueue_script( 'universal-honey-pot-script', UNIVERSAL_HONEY_POT_PLUGIN_URL . 'public/assets/build/uhp-frontend.js', array(), UNIVERSAL_HONEY_POT_VERSION, true );
    }
}
