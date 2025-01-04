<?php



/**
 * Class Wc_Rw_Debug
 * Handles debugging and error logging for the plugin.
 */
class Wc_Rw_Debug {

    /**
     * Prints a readable array or object structure for debugging.
     *
     * @param mixed $arr The data to print.
     */
    public static function debug( $arr) {
        echo '<pre>' . print_r($arr, true) . '</pre>';
    }

}



