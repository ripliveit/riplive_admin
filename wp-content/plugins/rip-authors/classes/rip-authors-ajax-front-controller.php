<?php

/**
 * Author ajax front controller.
 * Implements method invoked by ajax method to retrieve authors's data.
 */
class rip_authors_ajax_front_controller {

    /**
     * Retrieve all blog authors.
     */
    public static function get_all_authors() {
        $dao = new rip_authors_dao();
        $json_helper = rip_general_json_helper::get_instance();

        $results = $dao->get_all_authors();

        $json_helper->to_json(array(
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
    public static function get_author_by_slug() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $slug = $request->query->get('slug');

        if (empty($slug)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify an author slug'
            ));
        }

        $dao = new rip_authors_dao();
        $result = $dao->get_author_by_slug($slug);

        if (empty($result)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Not found'
            ));
        }

        $json_helper->to_json(array(
            'status' => 'ok',
            'author' => $result
        ));
    }

}