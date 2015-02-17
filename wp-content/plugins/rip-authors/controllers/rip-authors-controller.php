<?php

namespace Rip_Authors\Controllers;

/**
 * Authors Controller.
 * Implements methods invoked by ajax request
 * to retrieve authors's data.
 */
class Rip_Authors_Controller extends \Rip_General\Classes\Rip_Abstract_Controller {

    /**
     * Retrieve all site's authors.
     */
    public function get_all_authors() {
        $service = $this->_container['authorsQueryService'];
        $result = $service->get_all_authors();

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Return a single author.
     */
    public function get_author_by_slug() {
        $slug = $this->_request->query->get('slug');
        $service = $this->_container['authorsQueryService'];

        $result = $service->get_author_by_slug($slug);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

}
