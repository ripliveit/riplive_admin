<?php

namespace Rip_General\Daos;

/**
 * Description of rip-posts-dao
 *
 * @author Gabriele
 */
class Rip_Posts_Dao extends \Rip_General\Classes\Rip_Abstract_Dao {

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
    }

    /**
     * Return the total number of pages retrieving the total number
     * of a specific custom post type.
     * 
     * @param array $terms
     * @return array
     */
    public function get_posts_type_number_of_pages($post_type = null, $count = null, array $terms = array(), $all_post_status = null) {
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
