<?php

namespace Rip_General\Filters;

/**
 * Helper used to encode or decode Wordpress output.
 */
class Rip_Output_Filter {

    /**
     * Encode all html entities.
     * 
     * @param string $value
     * @return string
     */
    public static function encode_wp_output($value) {
        return htmlentities($value, ENT_COMPAT, 'UTF-8');
    }

    /**
     * Decode all html entity.
     * 
     * @param string $value
     * @return string
     */
    public static function decode_wp_output($value) {
        return html_entity_decode($value, ENT_COMPAT, 'UTF-8');
    }

    /**
     * Strip all html and js tag, remove all line breaks
     * and encode all html entity.
     * 
     * @param string $value
     * @return string
     */
    public static function strip_content($value) {
        $value = strip_tags($value);
        $value = str_replace(array("\n", "\r"), '', $value);
        $value = self::decode_wp_output($value);

        return $value;
    }

    /**
     * Remove the ellipsis.
     * 
     * @param string $value
     * @return string
     */
    public static function remove_ellipsis($value) {
        return html_entity_decode($value, ENT_COMPAT, 'UTF-8');
    }

}
