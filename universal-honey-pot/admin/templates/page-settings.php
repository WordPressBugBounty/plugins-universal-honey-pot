<?php
/**
 * The admin settings of the plugin.
 * @since      1.0.0
 */
$themes                   = wp_get_themes();
$counter                  = get_option('universal_honey_pot_counter', 0);
$filter_by_user_behaviour = get_option( 'universal_honey_pot_use_user_behaviour', array() );
?>
<form method="POST">

    <section class="uhp-settings-title-container">
        <h1><?php _e( "Universal Honey Pot", 'universal-honey-pot' ); ?></h1>
        <?php wp_nonce_field( 'universal_honey_pot_settings' ); ?>
        <button type="submit" class="submit-btn"><?php _e( "Save", 'universal-honey-pot' ); ?></button>
        <!-- counter -->
        <div class="uhp-counter">
            <b class="uhp-counter-number"><?php echo esc_html( $counter ); ?></b>
            <span class="uhp-counter-text"><?php _e( "Spam(s) blocked since installation", 'universal-honey-pot' ); ?></span>
        </div>
    </section>

    <section class="uhp-supported-plugins-container">
        <?php
        foreach( get_universal_honey_pot_supported_plugins() as $path => $data ){

            $installed    = file_exists( WP_PLUGIN_DIR . '/' . $path );
            $is_active    = is_plugin_active( $path );
            $comming_soon = $data['comming_soon'] ?? false;

            if ( 'divi-builder/divi-builder.php' === $path && ! $is_active ) {
                foreach( $themes as $theme ){
                    if ( 'Divi' === $theme->get_stylesheet() ){
                        $installed = true;
                        $is_active = get_template_directory() == $theme->get_stylesheet_directory();
                        break;
                    }
                }
            }
            
            ?>
            <div class="<?php echo esc_attr( $comming_soon ? 'comming-soon' : '' ); ?>">
                <img class="logo" src="<?php echo esc_url( $data['img'] ?? '' ); ?>" alt="Icon <?php echo esc_attr( $data['name'] ?? '' ); ?>" />
                <h3><?php echo esc_html( $data['name'] ?? '' ); ?></h3>
                <hr>
                <?php
                if( $installed ){
                    if( $is_active && ! $comming_soon ){
                        ?>
                        <p class="green"><?php _e( 'Is active and protected.', 'universal-honey-pot' ); ?></p>
                        <?php
                    } else if( $is_active && $comming_soon ){
                        ?>
                        <p class="orange"><?php _e( 'Is active but not protected yet.', 'universal-honey-pot' ); ?></p>
                        <?php
                    } else {
                        ?>
                        <p><?php _e( 'Is installed but not active.', 'universal-honey-pot' ); ?></p>
                        <?php
                    }
                } else {
                    ?>
                    <p>
                        <?php _e( 'Is not installed.', 'universal-honey-pot' ); ?>
                    </p>
                    <?php
                }
                ?>

                <?php
                    $user_behaviour_value    = $filter_by_user_behaviour[ $path ] ?? '0'; 
                    $user_behaviour_disabled = $installed && $is_active && ! $comming_soon ? '' : 'disabled="disabled"';
                    $user_behaviour_checked  = ! $user_behaviour_disabled && $user_behaviour_value == '1';

                    if ( $path === Universal_Honey_Pot_Forminator::PATH ) {
                        $user_behaviour_disabled = 'disabled="disabled"';
                        $user_behaviour_checked  = '0';
                    }
                ?>
                <label>
                    <?php _e( 'Behavioral Spam Filter', 'universal-honey-pot' ); ?>
                    <div class="switch">
                        <input type="hidden" name="universal_honey_pot_use_user_behaviour[<?php echo esc_attr($path); ?>]" value="0">
                        <input type="checkbox" name="universal_honey_pot_use_user_behaviour[<?php echo esc_attr($path); ?>]" value="1" <?php checked( $user_behaviour_checked ); echo esc_attr($user_behaviour_disabled)?>>
                        <span class="slider round"></span>
                    </div>
                </label>

            </div>
            <?php
        }
        ?>
    </section>

    <section class="uhp-credits-container">
        <h2><?php _e( "Credits", 'universal-honey-pot' ); ?></h2>
        <p>
            <?php _e( "This plugin is developed by", 'universal-honey-pot' ); ?>
            <a href="https://webdeclic.com/" target="_blank">Webdeclic</a>.
            <?php _e( "You can support this project here:", 'universal-honey-pot' ); ?>
        </p>
        <p>
            <a class="buymeacoffee" href="https://www.buymeacoffee.com/ludwig" target="_blank"><img src="<?php echo esc_url( UNIVERSAL_HONEY_POT_PLUGIN_URL . 'public/images/buy-me-a-coffee.webp' ); ?>" alt="Buy Me A Coffee"></a>
        </p>
        <p>
            <?php _e( "You can show all Webdeclic's plugins on ", 'universal-honey-pot' ); ?>
            <a href="https://wordpress.org/plugins/search/webdeclic/" target="_blank"><?php _e( "wordpress.org", 'universal-honey-pot' ); ?></a>.
        </p>
    </section>

</form>
