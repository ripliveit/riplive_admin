<?php

/**
 * Songs ajax front controller.
 * Implements method invoked byt ajax method to retrieve songs's data.
 */
class rip_songs_ajax_front_controller {

    /**
     * Retrieve all songs.
     */
    public static function get_all_songs() {
        $dao = new rip_songs_dao();
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $count = $request->query->get('count');
        $page = $request->query->get('page');
        $divide = $request->query->get('divide');

        $results = $dao->set_items_per_page($count)->get_all_songs($page);
        $pages = $dao->get_post_type_number_of_pages('songs');

        if ($divide) {
            $service = new rip_general_service();
            $results = $service->divide_data_by_letter('song_title', $results);
        }

        $json_helper->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => (int) $pages['count_total'],
            'pages' => $pages['pages'],
            'songs' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Retrieve all songs with a specific genre.
     */
    public static function get_all_songs_by_genre_slug() {
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

        $dao = new rip_songs_dao();
        $results = $dao->set_items_per_page($count)->get_all_songs_by_genre_slug($slug, $page);
        $pages = $dao->get_post_type_number_of_pages('songs', array(
            'song-genre' => $slug
        ));

        if ($divide) {
            $service = new rip_general_service();
            $results = $service->divide_data_by_letter('song_title', $results);
        }

        $json_helper->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => (int) $pages['count_total'],
            'pages' => $pages['pages'],
            'genre' => get_term_by('slug', $slug, 'song-genre'),
            'songs' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Retrieve all songs with a specific tag.
     */
    public static function get_all_songs_by_tag_slug() {
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

        $dao = new rip_songs_dao();
        $results = $dao->set_items_per_page($count)->get_all_songs_by_tag_slug($slug, $page);
        $pages = $dao->get_post_type_number_of_pages('songs', array(
            'song-tag' => $slug
        ));

        if ($divide) {
            $service = new rip_general_service();
            $results = $service->divide_data_by_letter('song_title', $results);
        }

        $json_helper->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => (int) $pages['count_total'],
            'pages' => $pages['pages'],
            'tag' => get_term_by('slug', $slug, 'song-tag'),
            'songs' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Retrieve a song by it's unique identifier.
     */
    public static function get_song_by_slug() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $slug = $request->query->get('slug');

        if (empty($slug)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a song slug'
            ));
        }

        $dao = new rip_songs_dao();
        $results = $dao->get_song_by_slug($slug);

        if (empty($results)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Not found'
            ));
        }

        $json_helper->to_json(array(
            'status' => 'ok',
            'song' => $results
        ));
    }

    /**
     * A list of all taxonomy of custom post type 'Songs'.
     */
    public static function get_songs_genres() {
        $dao = new rip_songs_dao();
        $json_helper = rip_general_json_helper::get_instance();

        $results = $dao->get_songs_genres();

        $json_helper->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => count($results),
            'pages' => 1,
            'genres' => empty($results) ? array() : $results,
        ));
    }

}