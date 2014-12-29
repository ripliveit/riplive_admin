<?php

namespace Rip_Highlights\Controllers;

/**
 * Highlights Controller.
 */
class Rip_Highlights_Controller extends \Rip_General\Classes\Rip_Abstract_Controller {

    /**
     * Retrieve all highlights.
     */
    public function get_all_highlights() {
        $count  = $this->_request->query->get('count');
        $page   = $this->_request->query->get('page');
        $divide = $this->_request->query->get('divide');

        $dao = new \Rip_Highlights\Daos\Rip_Highlights_Dao();
        $results = $dao->set_items_per_page($count)->get_all_highlights($page);
        $pages = $dao->get_post_type_number_of_pages('highlights');

        if ($divide) {
            $service = new rip_general_service();
            $results = $service->divide_data_by_letter('highlight_title', $results);
        }

        $this->_response->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => (int) $pages['count_total'],
            'pages' => $pages['pages'],
            'highlights' => empty($results) ? array() : $results,
        ));
    }

}
