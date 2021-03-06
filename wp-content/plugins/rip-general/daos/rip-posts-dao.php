<?php

namespace Rip_General\Daos;

/**
 * Post Data Access Object.
 *
 * @author Gabriele
 */
class Rip_Posts_Dao extends \Rip_General\Classes\Rip_Abstract_Dao {
    
    /**
     * Return all posts.
     * 
     * @param array $page_args
     * @return \WP_Query
     */
    public function get_all_posts(array $page_args = array()) {
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        );

        if (!empty($page_args)) {
            $args = array_merge($args, $page_args);
        }

        $query = new \WP_Query($args);

        return $query;
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
     * Return all attachments images, 
     * given an attachments id.
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
     * Return the_content by post id, 
     * and apply the_content filter to add the html format to get_the_content() output.
     * 
     * @return string
     */
    public function get_the_content_by_id($id) {
        $post = get_post((int) $id);
        $content = $post->post_content;
        $content = apply_filters('the_content', $content);
        $content = str_replace(']]>', ']]&gt;', $content);

        return $content;
    }

    /**
     * Return the total number of pages
     * for a specific post type.
     * 
     * @param string $post_type
     * @param int $count
     * @param array $terms
     * @param type $all_post_status
     * @return type
     */
    public function get_post_type_number_of_page($post_type = null, $count = null, array $terms = array(), $all_post_status = null) {
        $args = array(
            'posts_per_page' => -1,
            'post_status' => array(
                'publish'
        ));

        // Set the post type.
        if ($post_type) {
            $args['post_type'] = $post_type;
        }

        // Set the args with the terms query.
        if (!empty($terms)) {
            foreach ($terms as $term => $slug) {
                $args[$term] = $slug;
            }
        }

        if ($all_post_status) {
            array_push($args['post_status'], 'pending');
        }

        $query = new \WP_Query($args);
        $total_items = $query->post_count;

        if ($count === null) {
            $number_of_pages = 1;
        } else {
            $number_of_pages = ceil($total_items / $count);
        }

        return array(
            'count_total' => $total_items,
            'pages' => $number_of_pages
        );
    }

}
