<?php

namespace Rip_Authors\Daos;

/**
 * Data Access Object for blog Authors.
 */
class Rip_Authors_Dao extends \Rip_General\Classes\Rip_Abstract_Dao {

    /**
     * A method that set the Authors data.
     * Return only authors and editors.
     * 
     * @param Object $authors
     * @return boolean|array
     */
    protected function _set_authors_data($authors) {
        if (empty($authors)) {
            return false;
        }

        $out = array();

        foreach ($authors as $author) {
            if ($author->roles[0] === 'administrator') {
                continue;
            }

            array_push($out, array(
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

        return $out;
    }

    /**
     * Return a list of all blog's authors (except the administrator).
     * 
     * @return array
     */
    public function get_all_authors() {
        $authors = get_users(array(
            'role' => 'author',
            'orderby' => 'display_name',
        ));

        $editors = get_users(array(
            'role' => 'editor',
            'orderby' => 'display_name',
        ));

        $users = array_merge($authors, $editors);
        ksort($users);

        $results = $this->_set_authors_data($users);

        return $results;
    }

    /**
     * Return the information about a single author, retrieved by
     * its slug.
     * 
     * @param string $author_slug
     * @return array
     */
    public function get_author_by_slug($author_slug) {
        $author = get_user_by('slug', $author_slug);

        $results = $this->_set_authors_data(empty($author) ? array() : array($author));

        return empty($results) ? false : current($results);
    }

}
