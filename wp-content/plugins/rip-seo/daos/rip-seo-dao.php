<?php

namespace Rip_Seo\Daos;

/**
 *
 * @author Gabriele
 */
class Rip_Seo_Dao extends \Rip_General\Classes\Rip_Abstract_Dao {
    public function get_all_path() {
        $wpdb = $this->get_db();

        $sql = "SELECT *
                FROM wp_seo_view";

        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results;
    }
    
    public function get_meta_by_path($path) {
        $wpdb = $this->get_db();

        $sql = "SELECT path, title, description, image
                FROM wp_seo_view
                WHERE path = %s";
        
        $prepared = $wpdb->prepare($sql, array($path));

        $result = $wpdb->get_row($prepared, ARRAY_A);

        return $result;
    }
}
