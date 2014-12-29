<?php

namespace Rip_Programs\Controllers;

/**
 * Programs Controller.
 * Implements method invoked by ajax request to retrieve programs's data.
 */
class Rip_Programs_Controller extends \Rip_General\Classes\Rip_Abstract_Controller {

    /**
     * Retrieve all posts from 'Programs' custom post type.
     */
    public function get_all_programs() {
        $dao = new \Rip_Programs\Daos\Rip_Programs_Dao();
        $count = $this->_request->query->get('count');
        $page = $this->_request->query->get('page');

        $results = $dao->set_items_per_page($count)->get_all_programs($page);
        $pages = $dao->get_post_type_number_of_pages('programs');
        
        $this->_response->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => (int) $pages['count_total'],
            'pages' => $pages['pages'],
            'programs' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Retrieve all posts from 'Programs' custom post type, who are eligible to have
     * podcasts, even the ones in pending status.
     */
    public function get_all_programs_for_podcasts() {
        $dao = new \Rip_Programs\Daos\Rip_Programs_Dao();
        $count = $this->_request->query->get('count');
        $page = $this->_request->query->get('page');

        $results = $dao->set_items_per_page($count)->get_all_programs_for_podcasts($page);
        $pages = $dao->get_post_type_number_of_pages('programs', null, true);

        $this->_response->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => (int) $pages['count_total'],
            'pages' => $pages['pages'],
            'programs' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Return a single program by its relative id.
     */
    public function get_program_by_slug() {
        $slug = $this->_request->query->get('slug');

        if (empty($slug)) {
            return $this->_response->set_code(400)->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a program slug'
            ));
        }

        $dao = new \Rip_Programs\Daos\Rip_Programs_Dao();
        $results = $dao->get_program_by_slug($slug);

        if (empty($results)) {
            return $this->_response->set_code(404)->to_json(array(
                        'status' => 'error',
                        'message' => 'Not found'
            ));
        }

        $this->_response->to_json(array(
            'status' => 'ok',
            'program' => $results
        ));
    }

    /**
     * Return the programs week schedule.
     */
    public function get_programs_schedule() {
        $dao = new \Rip_Programs\Daos\Rip_Programs_Dao();
        $results = $dao->get_programs_schedule();

        if (empty($results)) {
            return $this->_response->to_json(array(
                        'status' => 'error',
                        'message' => 'Not found'
            ));
        }

        $this->_response->to_json(array(
            'status' => 'ok',
            'schedule' => $results
        ));
    }

}
