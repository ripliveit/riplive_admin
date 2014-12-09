<?php

/**
 * Artist ajax front controller.
 * Implements method invoked byt ajax method to retrieve artists's data.
 */
class rip_artists_ajax_front_controller {

    /**
     * Retrieve all artists.
     */
    public static function get_all_artists() {
        $dao = new rip_artists_dao();
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $count = $request->query->get('count');
        $page = $request->query->get('page');
        $divide = $request->query->get('divide');

        $results = $dao->set_items_per_page($count)->get_all_artists($page);
        $pages = $dao->get_post_type_number_of_pages('artists');

        if ($divide) {
            $service = new rip_general_service();
            $results = $service->divide_data_by_letter('artist_title', $results);
        }

        $json_helper->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => (int) $pages['count_total'],
            'pages' => $pages['pages'],
            'artists' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Retrieve all artists with a specific genre.
     */
    public static function get_all_artists_by_genre_slug() {
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

        $dao = new rip_artists_dao();
        $results = $dao->set_items_per_page($count)->get_all_artists_by_genre_slug($slug, $page);
        $pages = $dao->get_post_type_number_of_pages('artists', array(
            'artist-genre' => $slug
        ));

        if ($divide) {
            $service = new rip_general_service();
            $results = $service->divide_data_by_letter('artist_title', $results);
        }

        $json_helper->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => (int) $pages['count_total'],
            'pages' => $pages['pages'],
            'genre' => get_term_by('slug', $slug, 'artist-genre'),
            'artists' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Retrieve all artists with a specific tag.
     */
    public static function get_all_artists_by_tag_slug() {
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

        $dao = new rip_artists_dao();
        $results = $dao->set_items_per_page($count)->get_all_artists_by_tag_slug($slug, $page);
        $pages = $dao->get_post_type_number_of_pages('artists', array(
            'artist-tag' => $slug
        ));

        if ($divide) {
            $service = new rip_general_service();
            $results = $service->divide_data_by_letter('artist_title', $results);
        }

        $json_helper->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => (int) $pages['count_total'],
            'pages' => $pages['pages'],
            'tag' => get_term_by('slug', $slug, 'artist-tag'),
            'artists' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Retrieve a artist by it's unique identifier.
     */
    public static function get_artist_by_slug() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $slug = $request->query->get('slug');

        if (empty($slug)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a artist slug'
            ));
        }

        $dao = new rip_artists_dao();
        $results = $dao->get_artist_by_slug($slug);

        if (empty($results)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Not found'
            ));
        }

        $json_helper->to_json(array(
            'status' => 'ok',
            'artist' => $results
        ));
    }

    /**
     * A list of all taxonomy of custom post type 'Songs'.
     */
    public static function get_artists_genres() {
        $dao = new rip_artists_dao();
        $json_helper = rip_general_json_helper::get_instance();

        $results = $dao->get_artists_genres();

        $json_helper->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => count($results),
            'pages' => 1,
            'genres' => empty($results) ? array() : $results,
        ));
    }

}