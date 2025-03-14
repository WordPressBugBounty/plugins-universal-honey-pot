<?php

/**
 * The class responsible of block spam functionality.
 * 
 */
class Universal_Honey_Pot_Block_Spam {

    private $submission_time_threshold               = 1000;
    private $keypress_rate_threshold                 = 0.1;
    private $mouse_movement_rate_threshold           = 0.1;
    private $mouse_clicks_rate_threshold             = 0.1;
    private $mouse_movement_speed_threshold          = 0.1;
    private $keypress_speed_threshold                = 10;
    private $mouse_clicks_down_speed_threshold       = 5;
    private $mouse_clicks_down_after_speed_threshold = 50;
    private $touchscreen_down_speed_threshold        = 50;
    private $touchscreen_after_speed_threshold       = 100;
    private $touchscreen_rate_threshold              = 0.1;

    /**
     * Calculate the probability of the form submission being spam.
     * 
     * @return boolean
     */
    public function is_spam() {

        /**
         * @var int $bib
         * When did the user begin entering any input?
         */
        $bib  = isset( $_POST['uhp_bib'] ) ? (int)sanitize_text_field( $_POST['uhp_bib'] ) : 0;

        /**
         * @var int $bfs
         * When was the form submitted?
         */
        $bfs  = isset( $_POST['uhp_bfs'] ) ? (int)sanitize_text_field( $_POST['uhp_bfs'] ) : 0;

        /**
         * @var int $bkpc
         * How many keypresses did they make?
         */
        $bkpc = isset( $_POST['uhp_bkpc'] ) ? sanitize_text_field( $_POST['uhp_bkpc'] ) : '';

        /**
         * @var int $bmcc
         * How many mouseclicks did they make?
         */
        $bmcc = isset( $_POST['uhp_bmcc'] ) ? sanitize_text_field( $_POST['uhp_bmcc'] ) : '';

        /**
         * @var int $bmmc
         * How many times did they move the mouse?
         */
        $bmmc = isset( $_POST['uhp_bmmc'] ) ? sanitize_text_field( $_POST['uhp_bmmc'] ) : '';

        /**
         * @var string $bmm
         * How quickly did they move the mouse, and how long between moves?
         */
        $bmm  = isset( $_POST['uhp_bmm'] ) ? sanitize_text_field( $_POST['uhp_bmm'] ) : '';

		/**
         * @var string $bkp
         * How quickly did they press a sample of keys, and how long between them?
         */
        $bkp  = isset( $_POST['uhp_bkp'] ) ? sanitize_text_field( $_POST['uhp_bkp'] ) : '';

		/**
         * @var string $bmc
         * How quickly did they click the mouse, and how long between clicks?
         */
        $bmc  = isset( $_POST['uhp_bmc'] ) ? sanitize_text_field( $_POST['uhp_bmc'] ) : '';

        /**
         * @var string $bmk
         * When did they press modifier keys (like Shift or Capslock)?
         */
        $bmk  = isset( $_POST['uhp_bmk'] ) ? sanitize_text_field( $_POST['uhp_bmk'] ) : '';

		/**
         * @var string $bck
         * When did they correct themselves? e.g., press Backspace, or use the arrow keys to move the cursor back
         */
        $bck  = isset( $_POST['uhp_bck'] ) ? sanitize_text_field( $_POST['uhp_bck'] ) : '';

        /**
         * @var string $bsc
         * How many times did they scroll?
         */
        $bsc  = isset( $_POST['uhp_bsc'] ) ? sanitize_text_field( $_POST['uhp_bsc'] ) : '';

        /**
         * @var string $btmc
         * How many times did they move around using a touchscreen?
         */
        $btmc = isset( $_POST['uhp_btmc'] ) ? sanitize_text_field( $_POST['uhp_btmc'] ) : '';

        /**
         * @var string $bte
         * How quickly did they perform touch events, and how long between them?
         */
        $bte  = isset( $_POST['uhp_bte'] ) ? sanitize_text_field( $_POST['uhp_bte'] ) : '';

        /**
         * @var string $btec
         * How many touch events were there?
         */
        $btec = isset( $_POST['uhp_btec'] ) ? sanitize_text_field( $_POST['uhp_btec'] ) : '';

        if ( $bib === 0 && $bfs === 0 && empty( $bkpc ) && empty( $bmcc ) && empty( $bmmc ) && empty( $bmm ) && empty( $bkp ) && empty( $bmc ) && empty( $bmk ) && empty( $bck ) && empty( $bsc ) && empty( $btmc ) && empty( $bte ) && empty( $btec ) ) {
            universal_honey_pot_logger( 'Fields are empty' );
            return true;
        }

        $form_score           = 10;
        $form_score_min       = 6;
        $message_text         = '';
        $form_submission_time = $bfs - $bib;
        $form_submission_time = $form_submission_time > 0 ? $form_submission_time : 1;

        // Check submission time
        $message_text .= 'Form submitted in(' . $form_submission_time . ')';
        if ( $form_submission_time < $this->submission_time_threshold ) {
            $form_score--;
            $message_text .= '--';
        }
        $message_text .= ', ';

        // Check keypress rate
        $keypress_rate = $bkpc / $form_submission_time;
        $message_text .= 'Keypress rate(' . $keypress_rate . ')';
        if ( $keypress_rate <= 0 || $keypress_rate > $this->keypress_rate_threshold ) {
            $form_score--;
            $message_text .= '--';
        }
        $message_text .= ', ';

        // Check mouse movement rate
        $mouse_movement_rate = $bmmc / $form_submission_time;
        $message_text .= 'Mouse movement rate(' . $mouse_movement_rate . ')';
        if ( $mouse_movement_rate <= 0 || $mouse_movement_rate > $this->mouse_movement_rate_threshold ) {
            $form_score--;
            $message_text .= '--';
        }
        $message_text .= ', ';

        // Check mouse clicks rate
        $mouse_click_rate = $bmcc / $form_submission_time;
        $message_text .= 'Mouse clicks rate(' . $mouse_click_rate . ')';
        if ( $mouse_click_rate <= 0 || $mouse_click_rate > $this->mouse_clicks_rate_threshold ) {
            $form_score--;
            $message_text .= '--';
        }
        $message_text .= ', ';

        // Check mouse movement speed
        $bmm_array           = explode( ';', $bmm );
        $fast_mouse_movement = [];
        foreach ( $bmm_array as $value ) {

            if ( empty( $value ) ) {
                continue;
            }

            $value_array = explode( ',', $value );
            $time        = $value_array[0] ?? 0;
            $distance    = $value_array[1] ?? 1;
            $speed       = $time / $distance;

            if ( $speed < $this->mouse_movement_speed_threshold ) {
                $fast_mouse_movement[] = $value;
            }
        }
        $message_text .= 'Mouse movement(' . $bmm . ')';
        if ( empty( $bmm ) ) {
            $form_score--;
            $message_text .= '--';
        }
        if ( ! empty( $fast_mouse_movement ) && ! empty( $bmm ) ) {
            if ( count( $fast_mouse_movement ) / count( $bmm_array ) > 0.5 ) {
                $form_score--;
                $message_text .= '--';
            }
        }
        $message_text .= ', ';

        // Check keypress speed
        $bkp_array     = explode( ';', $bkp );
        $fast_keypress = [];
        foreach ( $bkp_array as $value ) {

            if ( empty( $value ) ) {
                continue;
            }

            $value_array = explode( ',', $value );

            if ( count( $value_array ) < 2 ) {
                continue;
            }

            $down_time  = $value_array[0] ?? 0;
            $after_time = $value_array[1] ?? 0;

            if ( $down_time < $this->keypress_speed_threshold || $after_time < $this->keypress_speed_threshold ) {
                $fast_keypress[] = $value;
            }
        }
        $message_text .= 'Keypress speed(' . $bkp . ')';
        if ( empty( $bkp ) ) {
            $form_score--;
            $message_text .= '--';
        }
        if ( ! empty( $fast_keypress ) && ! empty( $bkp ) ) {
            if ( count( $fast_keypress ) / count( $bkp_array ) > 0.5 ) {
                $form_score--;
                $message_text .= '--';
            }
        }
        $message_text .= ', ';

        // Check mouse click speed
        $bmc_array        = explode( ';', $bmc );
        $fast_mouse_click = [];
        foreach ( $bmc_array as $value ) {
            
            if ( empty( $value ) ) {
                continue;
            }

            $value_array = explode( ',', $value );

            if ( count( $value_array ) < 2 ) {
                continue;
            }

            $down_time  = $value_array[0] ?? 0;
            $after_time = $value_array[1] ?? 0;

            if ( $down_time < $this->mouse_clicks_down_speed_threshold || $after_time < $this->mouse_clicks_down_after_speed_threshold ) {
                $fast_mouse_click[] = $value;
            }
        }
        $message_text .= 'Mouse click speed(' . $bmc . ')';
        if ( empty( $bmc) ) {
            $form_score--;
            $message_text .= '--';
        }
        if ( ! empty( $fast_mouse_click ) && ! empty( $bmc ) ) {
            if ( count( $fast_mouse_click ) / count( $bmc_array ) > 0.5 ) {
                $form_score--;
                $message_text .= '--';
            }
        }
        $message_text .= ', ';

        // Check modifier keys
        $message_text .= 'Modifier keys(' . $bmk . ')';
        if ( ! empty( $bmk ) ) {
            $form_score++;
            $message_text .= '++';
        }
        $message_text .= ', ';

        // Check correct keys
        $message_text .= 'Correct keys(' . $bck . ')';
        if ( ! empty( $bck ) ) {
            $form_score++;
            $message_text .= '++';
        }
        $message_text .= ', ';

        // Check scrolling
        $message_text .= 'Scrolling(' . $bsc . ')';
        if ( is_numeric($bsc) && $bsc > 2 ) {
            $form_score++;
            $message_text .= '++';
        }
        $message_text .= ', ';

        // Check if there a touchscreen movement
        if ( $btmc > 0 && $btec > 0 ) {
    
            // Check touchscreen movement events
            $bte_array        = explode( ';', $bte );
            $fast_touchscreen = [];
            foreach ( $bte_array as $value ) {
            
                if ( empty( $value ) ) {
                    continue;
                }
    
                $value_array = explode( ',', $value );
    
                if ( count( $value_array ) < 2 ) {
                    continue;
                }
    
                $down_time  = $value_array[0] ?? 0;
                $after_time = $value_array[1] ?? 0;
    
                if ( $down_time < $this->touchscreen_down_speed_threshold || $after_time < $this->touchscreen_after_speed_threshold ) {
                    $fast_touchscreen[] = $value;
                }
            }
            $message_text .= 'Touchscreen speed(' . $bte . ')';
            if ( ! empty( $fast_touchscreen ) && ! empty( $bte ) ) {
                if ( count( $fast_touchscreen ) / count( $bte_array ) > 0.5 ) {
                    $form_score--;
                    $message_text .= '--';
                }
            }
            $message_text .= ', ';
    
            // Check touchscreen rate
            $touchscreen_rate = $btec / $form_submission_time;
            $message_text .= 'Touchscreen rate(' . $touchscreen_rate . '), ';
            if ( $touchscreen_rate > $this->touchscreen_rate_threshold ) {
                $form_score--;
                $message_text .= '--';
            }
            $message_text .= ', ';
        }

        if ( $form_score < $form_score_min ) {
            universal_honey_pot_logger( 'Form score(' . $form_score . '), ' . $message_text );
            return true;
        } else {
            universal_honey_pot_logger( 'Form score(' . $form_score . '), ' . $message_text, 'passed' );
            return false;
        }
    }
}
