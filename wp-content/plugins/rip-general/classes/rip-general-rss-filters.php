<?php

/**
 * Modify the rss feed output
 */
class rip_general_rss_filters {

    /**
     * Create the rss url
     * 
     * @param a parsed uri array, created with parse_url $parsed
     * @return string
     */
    protected static function _create_rss_url(array $parsed = array()) {
        $url = 'http://www.riplive.it';

        if (empty($parsed)) {
            return $url;
        }

        if (isset($_GET['post_type'])) {
            $path = $parsed['path'];
        } else {
            $path = '/news' . $parsed['path'];
        }

        return $url . $path;
    }

    /**
     * Change the GUID link
     * 
     * @param string $guid
     * @return string
     */
    public static function change_rss_guid_link($guid) {
        $guid = str_replace('&#038;', '&', $guid);
        $parts = parse_url($guid);

        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);

            if (!empty($query['p'])) {
                $post = get_post($query['p']);
                $post_type = $post->post_type;
                $link = '/' . $post->post_name;
            }
        }

        $url = 'http://www.riplive.it/' . ($post_type === 'post' ? 'news' : $post_type) . $link;
        return $url;
    }

    /**
     * Change the rss link.
     * 
     * @param string $url
     * @return string
     */
    public static function change_rss_link($url) {
        $parts = parse_url($url);
        $url = self::_create_rss_url($parts);

        return $url;
    }

    /**
     * Change the comments link
     * 
     * @param string $url
     * @return string
     */
    public static function change_rss_comment_link($url) {
        if (isset($_GET['post_type'])) {
            return '';
        }

        $parts = parse_url($url);
        $url = self::_create_rss_url($parts);

        return $url . '#comments';
    }

    /**
     * Add the featured image to the rss feed content.
     * 
     * @global Object $post
     * @param string $content
     * @return string
     */
    public static function add_featured_image($content) {
        global $post;

        if (has_post_thumbnail($post->ID)) {
            $content = '<div class="featured_image_post_rss">' . get_the_post_thumbnail($post->ID, 'thumbnail') . '</div>' . $content;
        }
        
        return $content;
    }

}
