<?php

namespace Rip_Programs\Controllers;

/**
 * Programs Controller.
 * Each method, publicly available, return data in JSON format.
 */
class Rip_Programs_Controller extends \Rip_General\Classes\Rip_Abstract_Controller {

    /**
     * Return a list of programs.
     */
    public function get_all_programs() {
        $count = $this->_request->query->get('count');
        $page = $this->_request->query->get('page');
        $status = $this->_request->query->get('status');
        $service = $this->_container['programsQueryService'];

        $result = $service->get_all_programs($count, $page, $status);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Return a single program by its relative id.
     */
    public function get_program_by_slug() {
        $slug = $this->_request->query->get('slug');

        $service = $this->_container['programsQueryService'];
        $result = $service->get_program_by_slug($slug);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Return the programs week schedule.
     */
    public function get_programs_schedule() {
        $service = $this->_container['programsQueryService'];
        $result = $service->get_programs_schedule();

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

}
