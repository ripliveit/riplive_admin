<?php


/**
 * Perform a filer on JSON_API wordpress plugin iutput.
 */
class rip_programs_json_api_filter {
    
    /**
     * Cycle all post and try to unserialize the required post_meta
     * 
     * @param object $response
     * @return object
     */
    public static function check_programs_meta($response) {
        if (isset($response['posts'])) {
            foreach ($response['posts'] as &$post) {
                self::unserialize_programs_meta($post);
            }
        } else if (isset($response['post'])) {
            self::unserialize_programs_meta($response['post']);
        }

        return $response;
    }
    
    /**
     * Unserialize programs-day, 
     * reruns-days and programs-authors post_meta.
     * 
     * @param object $post
     * @return object
     */
    public static function unserialize_programs_meta($post) {
        if (property_exists($post->custom_fields, 'programs-days')) {
            $post->custom_fields->{'programs-days'}[0] = unserialize($post->custom_fields->{'programs-days'}[0]);
        }

        if (property_exists($post->custom_fields, 'reruns-days')) {
            $post->custom_fields->{'reruns-days'}[0] = unserialize($post->custom_fields->{'reruns-days'}[0]);
        }

        if (property_exists($post->custom_fields, 'programs-authors')) {
            $post->custom_fields->{'programs-authors'}[0] = unserialize($post->custom_fields->{'programs-authors'}[0]);
        }

        return $post;
    }

}