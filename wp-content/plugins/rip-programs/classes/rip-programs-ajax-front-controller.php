<?php

/**
 * Programs ajax front controller.
 * Implements method invoked by ajax method to retrieve programs's data.
 */
class rip_programs_ajax_front_controller {

    /**
     * Retrieve all posts from 'Programs' custom post type.
     */
    public static function get_all_programs() {
        $dao = new rip_programs_dao();
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $count = $request->query->get('count');
        $page = $request->query->get('page');

        $results = $dao->set_items_per_page($count)->get_all_programs($page);
        $pages = $dao->get_post_type_number_of_pages('programs');

        $json_helper->to_json(array(
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
    public static function get_all_programs_for_podcasts() {
        $dao = new rip_programs_dao();
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $count = $request->query->get('count');
        $page = $request->query->get('page');

        $results = $dao->set_items_per_page($count)->get_all_programs_for_podcasts($page);
        $pages = $dao->get_post_type_number_of_pages('programs', null, true);

        $json_helper->to_json(array(
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
    public static function get_program_by_slug() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $slug = $request->query->get('slug');

        if (empty($slug)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a program slug'
            ));
        }

        $dao = new rip_programs_dao();
        $results = $dao->get_program_by_slug($slug);

        if (empty($results)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Not found'
            ));
        }

        $json_helper->to_json(array(
            'status' => 'ok',
            'program' => $results
        ));
    }

    /**
     * Return the programs week schedule.
     */
    public static function get_programs_schedule() {
        $dao = new rip_programs_dao();
        $json_helper = rip_general_json_helper::get_instance();

        $results = $dao->get_programs_schedule();

        if (empty($results)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Not found'
            ));
        }

        $json_helper->to_json(array(
            'status' => 'ok',
            'schedule' => $results
        ));
    }

}