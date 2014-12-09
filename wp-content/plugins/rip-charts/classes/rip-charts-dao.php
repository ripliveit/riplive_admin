<?php

/**
 * Data Access object for Charts Custom Post Type.
 */
class rip_charts_dao extends rip_abstract_dao {

    /**
     * Number of items per page.
     * @var type 
     */
    protected $_items_per_page = 24;

    /**
     * A method that set the Charts data.
     * 
     * @param WP_Query $query
     * @return array
     */
    protected function _set_charts_data(WP_Query $query) {
        $out = array();

        while ($query->have_posts()) {
            $query->the_post();
            $post = get_post(get_the_ID());

            array_push($out, array(
                'id_chart' => html_entity_decode(get_the_ID(), ENT_COMPAT, 'UTF-8'),
                'chart_slug' => $post->post_name,
                'chart_title' => get_the_title(),
                'chart_content' => $this->get_the_content(),
                'chart_excerpt' => get_the_excerpt(),
                'chart_genre' => wp_get_post_terms(get_the_ID(), 'chart-genre'),
                'chart_tags' => wp_get_post_terms(get_the_ID(), 'chart-tag'),
                'chart_images' => array(
                    'thumbnail' => $this->get_post_images(get_the_ID(), 'thumbnail'),
                    'image_medium' => $this->get_post_images(get_the_ID(), 'medium'),
                    'image_large' => $this->get_post_images(get_the_ID(), 'large'),
                    'image_full' => $this->get_post_images(get_the_ID(), 'full'),
                    'landscape_medium' => $this->get_post_images(get_the_ID(), 'medium-landscape'),
                    'landscape_large' => $this->get_post_images(get_the_ID(), 'large-landscape'),
                ),
            ));
        }

        wp_reset_query();
        wp_reset_postdata();

        return $out;
    }

    /**
     * A method that set the Complete Chart's data.
     * 
     * @param array $chart_data
     * @return boolean|array
     */
    protected function _set_complete_chart_data(array $chart_data) {
        if (empty($chart_data)) {
            return array();
        }

        $out = array();
        // First populate the chart's data.
        foreach ($chart_data as $value) {
            $archive_slug = $value['chart_archive_slug'];

            $out[$archive_slug]['id_chart_archive'] = (int) $value['id_chart_archive'];
            $out[$archive_slug]['chart_archive_slug'] = $value['chart_archive_slug'];
            $out[$archive_slug]['id_chart'] = (int) $value['id_chart'];
            $out[$archive_slug]['chart_slug'] = $value['chart_slug'];
            $out[$archive_slug]['chart_title'] = $value['chart_title'];
            $out[$archive_slug]['chart_content'] = $value['chart_content'];
            $out[$archive_slug]['chart_excerpt'] = $value['chart_excerpt'];
            $out[$archive_slug]['chart_date'] = $value['chart_date'];
            $out[$archive_slug]['chart_locale_date'] = date_i18n('d F Y', strtotime($value['chart_date']));
            $out[$archive_slug]['chart_creation_date'] = $value['chart_creation_date'];
            $out[$archive_slug]['chart_songs_number'] = $value['chart_songs_number'];
            $out[$archive_slug]['chart_genre'] = wp_get_post_terms((int) $value['id_chart'], 'chart-genre');
            $out[$archive_slug]['chart_tags'] = wp_get_post_terms((int) $value['id_chart'], 'chart-tag');
            $out[$archive_slug]['chart_images'] = array(
                'thumbnail' => $this->get_post_images($value['id_chart'], 'thumbnail'),
                'image_medium' => $this->get_post_images($value['id_chart'], 'medium'),
                'image_large' => $this->get_post_images($value['id_chart'], 'large'),
                'image_full' => $this->get_post_images($value['id_chart'], 'full'),
                'landscape_medium' => $this->get_post_images($value['id_chart'], 'medium-landscape'),
                'landscape_large' => $this->get_post_images($value['id_chart'], 'large-landscape'),
            );
            $out[$archive_slug]['songs'] = array();
        }

        // Then fill with all relative's songs.
        foreach ($chart_data as $item) {
            $archive_slug = $item['chart_archive_slug'];

            array_push($out[$archive_slug]['songs'], array(
                'id_chart_song' => (int) $item['id'],
                'id_song' => (int) $item['id_song'],
                'song_slug' => $item['song_slug'],
                'song_title' => $item['song_title'],
                'song_content' => $item['song_content'],
                'song_excerpt' => $item['song_excerpt'],
                'song_genre' => wp_get_post_terms($item['id_song'], 'song-genre'),
                'song_tags' => wp_get_post_terms($item['id_song'], 'song-tag'),
                'song_images' => array(
                    'thumbnail' => $this->get_post_images($item['id_song'], 'thumbnail'),
                    'image_medium' => $this->get_post_images($item['id_song'], 'medium'),
                    'image_large' => $this->get_post_images($item['id_song'], 'large'),
                    'image_full' => $this->get_post_images($item['id_song'], 'full'),
                    'landscape_medium' => $this->get_post_images($item['id_song'], 'medium-landscape'),
                    'landscape_large' => $this->get_post_images($item['id_song'], 'large-landscape'),
                ),
                'song_artist' => get_post_meta($item['id_song'], 'songs-artist', true),
                'song_album' => get_post_meta($item['id_song'], 'songs-album', true),
                'url_spotify' => get_post_meta($item['id_song'], 'songs-spotify', true),
                'user_vote' => (int) $item['user_vote'],
            ));
        }

        return array_values($out);
    }

    /**
     * Retrieve all posts 
     * from Charts Custom Post Type. 
     * 
     * @return array
     */
    public function get_all_charts() {
        $args = array(
            'post_type' => 'charts',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'ID',
            'order' => 'DESC'
        );

        $query = new WP_Query($args);

        $results = $this->_set_charts_data($query);

        return $results;
    }

    /**
     * Retrieve a single chart's post by it's slug.
     * 
     * @param string $slug
     * @return array
     */
    public function get_chart_by_slug($slug) {
        $args = array(
            'post_type' => 'charts',
            'name' => $slug,
        );

        $query = new WP_Query($args);

        $results = $this->_set_charts_data($query);

        return current($results);
    }

    /**
     * Return the total number of pages,
     * extracting all complete charts from wp_charts_songs. 
     * A complete chart is a chart where all songs has the same chart_archive_slug)
     *  
     * @param string $slug
     * @return array
     */
    public function get_complete_charts_number_of_pages($slug = null) {
        $wpdb = $this->get_db();

        $sql = "SELECT COUNT(id) AS total_items
                FROM wp_charts_archive";

        if ($slug !== null) {
            $sql .= ' WHERE chart_slug = %s';

            $prepared = $wpdb->prepare($sql, array($slug));
        } else {
            $prepared = $sql;
        }

        $results = $wpdb->get_row($prepared, ARRAY_A);

        if ($this->_items_per_page === null) {
            $number_of_pages = 1;
        } else {
            $number_of_pages = ceil($results['total_items'] / $this->_items_per_page);
        }

        return array(
            'count_total' => $results['total_items'],
            'pages' => $number_of_pages
        );
    }

    /**
     * Retrieve a list of all complete charts.
     * Returned a paginated array.
     * 
     * @param int $page
     * @return array
     */
    public function get_all_complete_charts($page = null) {
        $wpdb = $this->get_db();

        $sql = "SELECT 
                    a.id AS id_chart_archive, a.chart_archive_slug, a.chart_date, a.chart_creation_date, a.songs_number AS chart_songs_number,
                    c.id, c.id_chart, c.id_song, c.user_vote,
                    p1.post_name AS chart_slug, p1.post_title AS chart_title, 
                    p1.post_content AS chart_content, p1.post_excerpt AS chart_excerpt,
                    p2.post_name AS song_slug, p2.post_title AS song_title, 
                    p2.post_content AS song_content, p2.post_excerpt AS song_excerpt
                FROM wp_charts_archive AS a, wp_charts_songs AS c, wp_posts AS p1, wp_posts AS p2
                WHERE a.chart_archive_slug = c.chart_archive_slug
                AND   c.id_chart = p1.ID
                AND   c.id_song  = p2.ID 
                GROUP BY c.chart_archive_slug
                ORDER BY a.chart_date DESC";

        if ($page) {
            $offset = ($page * $this->_items_per_page) - $this->_items_per_page;
            $sql .= ' LIMIT ' . $offset . ', ' . $this->_items_per_page;
        } else {
            $sql .= ' LIMIT ' . (int) $this->_items_per_page;
        }

        $chart_data = $wpdb->get_results($sql, ARRAY_A);

        $results = $this->_set_complete_chart_data($chart_data);

        return $results;
    }

    /**
     * Return and retrieve all complete chart of a specific chart, 
     * specifing the slug of the chart 
     * Include the song in first position.
     * 
     * @param string $slug
     * @return array
     */
    public function get_all_complete_charts_by_chart_slug($slug, $page = null) {
        $wpdb = $this->get_db();

        $sql = "SELECT 
                    a.id AS id_chart_archive, a.chart_archive_slug, a.chart_date, a.chart_creation_date, a.songs_number AS chart_songs_number,
                    c.id, c.id_chart, c.id_song, c.user_vote,
                    p1.post_name AS chart_slug, p1.post_title AS chart_title, 
                    p1.post_content AS chart_content, p1.post_excerpt AS chart_excerpt,
                    p2.post_name AS song_slug, p2.post_title AS song_title, 
                    p2.post_content AS song_content, p2.post_excerpt AS song_excerpt
                FROM wp_charts_archive AS a, wp_charts_songs AS c, wp_posts AS p1, wp_posts AS p2
                WHERE a.chart_archive_slug = c.chart_archive_slug
                AND   c.id_chart = p1.ID
                AND   c.id_song  = p2.ID 
                AND   a.chart_slug = %s
                GROUP BY c.chart_archive_slug
                ORDER BY a.id DESC, c.user_vote DESC";

        if ($page) {
            $offset = ($page * $this->_items_per_page) - $this->_items_per_page;
            $sql .= ' LIMIT ' . $offset . ', ' . $this->_items_per_page;
        } else {
            $sql .= ' LIMIT ' . (int) $this->_items_per_page;
        }

        $prepared = $wpdb->prepare($sql, array(
            $slug
        ));

        $chart_data = $wpdb->get_results($prepared, ARRAY_A);

        $results = $this->_set_complete_chart_data($chart_data);

        return $results;
    }

    /**
     * Return and retrieve a specific complete chart, passing a chart_archive_slug 
     * as argument.
     * $chart_archive_slug is the unique identifier in wp_charts_archive table.
     * 
     * @param string $slug
     * @return array
     */
    public function get_complete_chart_by_chart_archive_slug($slug) {
        $wpdb = $this->get_db();

        $sql = "SELECT 
                    a.id AS id_chart_archive, a.chart_archive_slug, a.chart_date, a.chart_creation_date, a.songs_number AS chart_songs_number,
                    c.id, c.id_chart, c.id_song, c.user_vote,
                    p1.post_name AS chart_slug, p1.post_title AS chart_title, 
                    p1.post_content AS chart_content, p1.post_excerpt AS chart_excerpt,
                    p2.post_name AS song_slug, p2.post_title AS song_title, 
                    p2.post_content AS song_content, p2.post_excerpt AS song_excerpt
                FROM wp_charts_archive AS a, wp_charts_songs AS c, wp_posts AS p1, wp_posts AS p2
                WHERE a.chart_archive_slug = c.chart_archive_slug
                AND   c.id_chart = p1.ID
                AND   c.id_song  = p2.ID 
                AND   a.chart_archive_slug = %s 
                ORDER BY c.user_vote DESC";


        $prepared = $wpdb->prepare($sql, array(
            $slug
        ));

        $chart_data = $wpdb->get_results($prepared, ARRAY_A);

        $results = $this->_set_complete_chart_data($chart_data);

        return empty($results) ? false : current($results);
    }

    /**
     * First insert a chart's record into wp_chart_archive, (that store all archive about chart)
     * Then insert a complete chart into wp_charts_songs.
     * Accept as parameter an array of rows to insert in a transaction. 
     * Each row specify: the chart archive slug, the id_chart, and the id_song
     * Return the last inserted item.
     * 
     * @param array $data
     * @return array
     */
    public function insert_complete_chart(array $data = array()) {
        if (empty($data['chart_archive_slug'])) {
            return array(
                'status' => 'error',
                'message' => 'Please specify a chart_archive_slug'
            );
        }

        $wpdb = $this->get_db();
        $wpdb->hide_errors();

        $wpdb->query('START TRANSACTION');

        $date = date('Y-m-d', time());
        $date_time = date('Y-m-d H:i:s', time());

        // First insert into wp_chart_archive.
        $first_query = "INSERT INTO wp_charts_archive (
                            chart_archive_slug, 
                            id_chart,
                            chart_slug, 
                            chart_date, 
                            chart_creation_date, 
                            songs_number
                         )
                        VALUES (%s, %d, %s, %s, %s, %s)";

        $first_prepared = $wpdb->prepare($first_query, array(
            $data['chart_archive_slug'],
            (int) $data['id_chart'],
            $data['chart_slug'],
            $date,
            $date_time,
            $data['chart_songs_number'],
        ));

        if ($wpdb->query($first_prepared) === false) {
            $wpdb->query('ROLLBACK');

            return array(
                'status' => 'error',
                'type' => 'duplicate',
                'message' => 'Error in inserting to wp_chart_archive. Probably the chart is already presents.'
            );
        }

        // Insert all songs in wp_charts_song
        foreach ($data['songs'] as $song) {
            $sql = "INSERT INTO wp_charts_songs (chart_archive_slug, id_chart, id_song)
                    VALUES (%s, %d, %d)";

            $prepared = $wpdb->prepare($sql, array(
                $data['chart_archive_slug'],
                (int) $data['id_chart'],
                (int) $song['id_song'],
            ));

            try {
                $wpdb->query($prepared);
            } catch (Exception $exc) {
                $wpdb->query('ROLLBACK');

                return array(
                    'status' => 'error',
                    'message' => 'Error in inserting all songs in wp_chart_songs.'
                );
            }
        }

        $wpdb->query('COMMIT');

        $results = $this->get_complete_chart_by_chart_archive_slug($data['chart_archive_slug']);

        return $results;
    }

    /**
     * Update each row of a complete chart.
     * A complete chart is made by 5, 10, 20, or 50 songs with the same chart_archive_slug.
     * 
     * IMPORTANT: 
     * Each row id must be specified to perform the update in a transaction.
     * The row id is passed AS id_chart_song
     * 
     * @param array $data
     * @return int (the number of the affected row).
     */
    public function update_complete_chart(array $data = array()) {
        if (empty($data)) {
            return array(
                'status' => 'error',
                'message' => 'No complete chart data was supplied',
            );
        }

        $wpdb = $this->get_db();

        $wpdb->query('START TRANSACTION');

        foreach ($data['songs'] as $item) {
            try {
                $sql = "UPDATE wp_charts_songs 
                        SET id_song = %d, user_vote = %d
                        WHERE id = %d
                        AND chart_archive_slug = %s";

                $prepared = $wpdb->prepare($sql, array(
                    (int) $item['id_song'],
                    (int) $item['user_vote'],
                    (int) $item['id_chart_song'],
                    $data['chart_archive_slug'],
                ));

                $results = $wpdb->query($prepared);
            } catch (Exception $exc) {
                $wpdb->query('ROLLBACK');
                echo $exc->getTraceAsString();
            }
        }
        $affected_rows = $wpdb->rows_affected;

        $wpdb->query('COMMIT');

        return $affected_rows;
    }

    /**
     * Delete a complete chart from wp_charts_archive table,
     * given it's chart archive slug.
     * 
     * IMPORTANT: all row in wp_charts_songs are automatically deleted
     * cause the foreign key chart_archive_slug.
     * 
     * @param string $slug
     * @return type
     */
    public function delete_complete_chart($slug) {
        $wpdb = $this->get_db();

        $wpdb->query('START TRANSACTION');

        $sql = "DELETE FROM wp_charts_archive 
                        WHERE chart_archive_slug = %s";

        $prepared = $wpdb->prepare($sql, array(
            $slug,
        ));

        try {
            $results = $wpdb->query($prepared);
        } catch (Exception $exc) {
            $wpdb->query('ROLLBACK');

            return array(
                'status' => 'error',
                'message' => 'Error in deleting the complete charts'
            );
        }

        $wpdb->query('COMMIT');

        return $results;
    }

    /**
     * Duplicate a complete chart, retrieved by it's unique chart_archive_slug.
     * Return the duplicated chart.
     * 
     * @param string $slug
     * @return array
     */
    public function duplicate_complete_chart($slug) {
        $data = $this->get_complete_chart_by_chart_archive_slug($slug);

        if (empty($data)) {
            return array(
                'status' => 'error',
                'message' => 'Cannot duplicate. Please specify a chart to duplicate.'
            );
        }

        // Set the new chart archive slug.
        // If already present @insert_complete_chart will not perform the insert.
        $data['chart_archive_slug'] = $data['chart_slug'] . '-' . $date = date('Y-m-d', time());

        $results = $this->insert_complete_chart($data);

        return $results;
    }

    /**
     * Insert a user vote on wp_charts_songs_vote
     * 
     * @param array $data
     * @return array
     */
    public function insert_complete_chart_vote(array $data = array()) {
        if (empty($data)) {
            return array(
                'status' => 'error',
                'message' => 'Please specify correct vote data'
            );
        }

        $wpdb = $this->get_db();
        $wpdb->hide_errors();

        $wpdb->query('START TRANSACTION');

        $sql = "INSERT INTO wp_charts_songs_vote (
                    chart_archive_slug,
                    id_song,
                    vote_date
                )
                VALUES (%s, %d, %s )";

        $prepared = $wpdb->prepare($sql, array(
            $data['chart_archive_slug'],
            (int) $data['id_song'],
            date('Y-m-d', time())
        ));

        if ($wpdb->query($prepared) === false) {
            $wpdb->query('ROLLBACK');

            return array(
                'status' => 'error',
                'type' => 'duplicate',
                'message' => 'Error in inserting to wp_charst_songs_vote. Probably already voted'
            );
        }

        $wpdb->query('COMMIT');

        return array(
            'status' => 'ok',
            'message' => 'Vote insertion was successfull'
        );
    }

}
