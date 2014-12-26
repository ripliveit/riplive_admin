<?php

namespace Rip_General\Classes;

/**
 * Abstract Data Access Object.
 * Implements method that all DAO inherits.
 */
class Rip_Abstract_Dao {

    /**
     * Holds a reference to the database.
     * 
     * @var Object 
     */
    private $_db = null;

    /**
     * Holds a reference to rip_general_service.
     * 
     * @var object 
     */
    protected $_general_service;

    /**
     * Number of items per page when listing all charts.
     * 
     * @var int 
     */
    protected $_items_per_page;

    /**
     * Return the global instance of wpdb.
     * 
     * @global Object $wpdb
     * @return Object
     */
    protected function get_db() {
        global $wpdb;

        if ($this->_db === null) {
            return $this->_db = $wpdb;
        }

        return $this->_db;
    }

    /**
     * A method that retun a formatted array
     * with genre's data.
     * 
     * @param array $genres
     * @return boolean|array
     */
    protected function _set_genres_data(array $genres = array()) {
        if (empty($genres)) {
            return false;
        }

        $out = array();

        foreach ($genres as $item) {
            array_push($out, array(
                'id' => $item->term_id,
                'slug' => $item->slug,
                'name' => $item->name,
                'count' => $item->count,
            ));
        }

        return $out;
    }

    /**
     * Return all attachments images, giving an attachments id.
     * 
     * @param int $attachment_id
     * @param string $size
     * @return string
     * @throws Exception
     */
    public function get_attachment_images($attachment_id, $size = null) {
        if (empty($attachment_id)) {
            throw new Exception('Please specify an attachment id');
        }

        if (empty($size)) {
            $size = 'thumbnail';
        }

        $image = wp_get_attachment_image_src($attachment_id, $size);

        return $image[0];
    }

    /**
     * Return all post's images, giving a proper post id.
     * 
     * @param int $post_id
     * @param string $size
     * @return string
     * @throws Exception
     */
    public function get_post_images($post_id, $size = null) {
        if (empty($post_id)) {
            throw new Exception('Please specify a post id');
        }

        if (empty($size)) {
            $size = 'thumbnail';
        }

        $image = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), $size);

        return $image[0];
    }

    /**
     * Set the number of items per page to retrieve.
     * 
     * @param type $count
     * @return \rip_songs_dao
     */
    public function set_items_per_page($count = null) {
        if ($count) {
            $this->_items_per_page = (int) $count;
        }

        return $this;
    }

    /**
     * Return the total number of pages retrieving the total number
     * of a specific custom post type.
     * 
     * @param array $terms
     * @return array
     */
    public function get_post_type_number_of_pages($post_type = null, $terms = null, $all_post_status = null) {
        $args = array('posts_per_page' => -1);
        $args['post_status'] = array('publish');

        // Set the post type.
        if ($post_type) {
            $args['post_type'] = $post_type;
        }

        // Set the args with the terms query.
        if ($terms) {
            foreach ($terms as $term => $slug) {
                $args[$term] = $slug;
            }
        }

        if ($all_post_status) {
            array_push($args['post_status'], 'pending');
        }

        $query = new WP_Query($args);
        $total_items = $query->post_count;

        if ($this->_items_per_page === null) {
            $number_of_pages = 1;
        } else {
            $number_of_pages = ceil($total_items / $this->_items_per_page);
        }

        return array(
            'count_total' => $total_items,
            'pages' => $number_of_pages
        );
    }

    /**
     * Return the query arguments for the pagination
     * 
     * @param array $args
     * @param int $count
     * @param int $page
     * @return array
     */
    public function get_pagination_args(array $args = array(), $count = null, $page = null) {
        if ($count) {
            $args['posts_per_page'] = (int) $count;
        } else {
            $args['posts_per_page'] = -1;
        }

        if ($page) {
            $args['paged'] = $page;
        } else {
            $args['paged'] = 1;
        }

        return $args;
    }

    /**
     * Apply the_content filter to add the html format 
     * to get_the_content() output.
     * 
     * @return string
     */
    public function get_the_content() {
        $content = apply_filters('the_content', get_the_content());
        $content = str_replace(']]>', ']]&gt;', $content);
        return $content;
    }

}
