<?php

namespace Rip_Authors\Mappers;

/**
 * Map author's data
 * in a coherent strcuture.
 *
 * @author Gabriele
 */
class Rip_Author_Mapper implements \Rip_General\Interfaces\Rip_Mapper_Array_Interface {

    /**
     * A method that map the author's data
     * into an array,
     * Return only authors and editors.
     * 
     * @param Object $authors
     * @return boolean|array
     */
    public function map(array $authors = array()) {
        if (empty($authors)) {
            return false;
        }

        $accumulator = array();

        foreach ($authors as $author) {
            if ($author->roles[0] === 'administrator') {
                continue;
            }

            array_push($accumulator, array(
                'id' => $author->data->ID,
                'slug' => $author->data->user_nicename,
                'name' => $author->data->user_login,
                'first_name' => get_the_author_meta('first_name', $author->data->ID),
                'last_name' => get_the_author_meta('last_name', $author->data->ID),
                'nickname' => $author->data->display_name,
                'email' => $author->data->user_email,
                'author_images' => array(
                    'thumbnail' => get_wp_user_avatar_src($author->data->ID, 'thumbnail'),
                    'image_medium' => get_wp_user_avatar_src($author->data->ID, 'medium'),
                    'image_large' => get_wp_user_avatar_src($author->data->ID, 'large'),
                    'image_full' => get_wp_user_avatar_src($author->data->ID, 'original'),
                    'landscape_medium' => get_wp_user_avatar_src($author->data->ID, 'medium-landscape'),
                    'landscape_large' => get_wp_user_avatar_src($author->data->ID, 'large-landscape'),
                ),
                'url' => $author->data->user_url,
                'description' => get_the_author_meta('description', $author->data->ID),
                'number_of_posts' => count_user_posts($author->data->ID),
            ));
        }

        return $accumulator;
    }

}
