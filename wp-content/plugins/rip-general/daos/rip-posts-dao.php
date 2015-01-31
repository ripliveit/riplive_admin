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

}
