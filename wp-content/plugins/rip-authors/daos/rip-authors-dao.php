<?php

namespace Rip_Authors\Daos;

/**
 * Data Access Object for site authors.
 */
class Rip_Authors_Dao extends \Rip_General\Classes\Rip_Abstract_Dao {

    /**
     * Return a list of all blog's authors 
     * (except the administrator).
     * 
     * @return array
     */
    public function get_all_authors($role = 'author') {
        $result = get_users(array(
            'role' => $role,
            'orderby' => 'display_name',
        ));

        return $result;
    }

    /**
     * Return a single author, retrieved by
     * its unique slug.
     * 
     * @param string $author_slug
     * @return array
     */
    public function get_author_by_slug($author_slug) {
        $result = get_user_by('slug', $author_slug);

        return $result;
    }

}
