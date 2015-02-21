<?php

namespace Rip_Highlights\Controllers;

/**
 * Highlights Controller.
 */
class Rip_Highlights_Controller extends \Rip_General\Classes\Rip_Abstract_Controller {

    /**
     * Return a list of last highlights.
     */
    public function get_all_highlights() {
        $count = $this->_request->query->get('count');
        $page = $this->_request->query->get('page');

        $service = $this->_container['highlightsQueryService'];
        $result = $service->set_items_per_page($count)->get_all_highlights($count, $page);


        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

}
