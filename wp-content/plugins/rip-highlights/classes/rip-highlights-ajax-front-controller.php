<?php

/**
 * Highlights Ajax Front Controller.
 */
class rip_highlights_ajax_front_controller {
      /**
     * Retrieve all highlights.
     */
    public static function get_all_highlights() {
        $dao = new rip_highlights_dao();
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $count = $request->query->get('count');
        $page = $request->query->get('page');
        $divide = $request->query->get('divide');

        $results = $dao->set_items_per_page($count)->get_all_highlights($page);
        $pages = $dao->get_post_type_number_of_pages('highlights');

        if ($divide) {
            $service = new rip_general_service();
            $results = $service->divide_data_by_letter('highlight_title', $results);
        }

        $json_helper->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => (int) $pages['count_total'],
            'pages' => $pages['pages'],
            'highlights' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Retrieve all highlights with a specific genre.
     */
    public static function get_all_highlights_by_genre_slug() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $slug = $request->query->get('slug');
        $count = $request->query->get('count');
        $page = $request->query->get('page');
        $divide = $request->query->get('divide');

        if (empty($slug)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a genre slug'
            ));
        }

        $dao = new rip_highlights_dao();
        $results = $dao->set_items_per_page($count)->get_all_highlights_by_genre_slug($slug, $page);
        $pages = $dao->get_post_type_number_of_pages('highlights', array(
            'highlight-genre' => $slug
        ));

        if ($divide) {
            $service = new rip_general_service();
            $results = $service->divide_data_by_letter('highlight_title', $results);
        }

        $json_helper->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => (int) $pages['count_total'],
            'pages' => $pages['pages'],
            'highlights' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Retrieve all highlights with a specific tag.
     */
    public static function get_all_highlights_by_tag_slug() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $slug = $request->query->get('slug');
        $count = $request->query->get('count');
        $page = $request->query->get('page');
        $divide = $request->query->get('divide');

        if (empty($slug)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a tag slug'
            ));
        }

        $dao = new rip_highlights_dao();
        $results = $dao->set_items_per_page($count)->get_all_highlights_by_tag_slug($slug, $page);
        $pages = $dao->get_post_type_number_of_pages('highlights', array(
            'highlight-tag' => $slug
        ));

        if ($divide) {
            $service = new rip_general_service();
            $results = $service->divide_data_by_letter('highlight_title', $results);
        }

        $json_helper->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => (int) $pages['count_total'],
            'pages' => $pages['pages'],
            'highlights' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Retrieve a highlight by it's unique identifier.
     */
    public static function get_highlight_by_slug() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $slug = $request->query->get('slug');

        if (empty($slug)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a highlight slug'
            ));
        }

        $dao = new rip_highlights_dao();
        $results = $dao->get_highlight_by_slug($slug);

        if (empty($results)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Not found'
            ));
        }

        $json_helper->to_json(array(
            'status' => 'ok',
            'highlight' => $results
        ));
    }
}