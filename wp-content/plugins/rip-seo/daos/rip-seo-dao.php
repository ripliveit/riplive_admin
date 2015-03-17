<?php

namespace Rip_Seo\Daos;

/**
 * Description of rip-seo-dao
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
}
