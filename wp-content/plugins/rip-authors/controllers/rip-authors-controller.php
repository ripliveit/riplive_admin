<?php

namespace Rip_Authors\Controllers;

/**
 * Authors Controller.
 * Implements methods invoked by ajax request
 * to retrieve authors's data.
 */
class Rip_Authors_Controller extends \Rip_General\Classes\Rip_Abstract_Controller {

    /**
     * Retrieve all blog authors.
     */
    public function get_all_authors() {
        $dao = new \Rip_Authors\Daos\Rip_Authors_Dao();
        $results = $dao->get_all_authors();

        $this->_response->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => count($results),
            'pages' => 1,
            'authors' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Return a single author, retrieved by its relative slug.
     */
    public function get_author_by_slug() {
        $slug = $this->_request->query->get('slug');

        if (empty($slug)) {
            return $this->_response->set_code(400)->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify an author slug'
            ));
        }

        $dao = new \Rip_Authors\Daos\Rip_Authors_Dao();
        $result = $dao->get_author_by_slug($slug);

        if (empty($result)) {
            return $this->_response->set_code(404)->to_json(array(
                        'status' => 'error',
                        'message' => 'Not found'
            ));
        }

        $this->_response->to_json(array(
            'status' => 'ok',
            'author' => $result
        ));
    }

}
