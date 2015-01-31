<?php

namespace Rip_Charts\Daos;

/**
 * Data Access object for Charts Custom Post Type.
 */
class Rip_Charts_Dao extends \Rip_General\Classes\Rip_Abstract_Dao {

    /**
     * Number of items per page.
     * @var type 
     */
    protected $_items_per_page = 24;

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

        $query = new \WP_Query($args);

        return $query;
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

        $query = new \WP_Query($args);

        return $query;
    }

    /**
     * Return the total number of pages,
     * extracting all complete charts from wp_charts_songs. 
     * A complete chart is a chart where all songs has the same chart_archive_slug
     *  
     * @param string $slug
     * @return array
     */
    public function get_complete_charts_number_of_pages($slug = null) {
        $wpdb = $this->get_db();

        $sql = "SELECT COUNT(id) AS total_items
                FROM wp_charts_archive";

        if ($slug !== null) {
            $sql .= ' WHERE chart_type = %s';

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
     * Return a paginated array.
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

        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results;
    }

    /**
     * Return and retrieve all complete chart of a specific chart type.
     * Include the song in first position.
     * 
     * @param string $slug
     * @return array
     */
    public function get_all_complete_charts_by_chart_type($slug, $page = null) {
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
                AND   a.chart_type = %s
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

        $results = $wpdb->get_results($prepared, ARRAY_A);

        return $results;
    }

    /**
     * Retrieve a list of the latest
     * complete charts, one per type.
     * Return a paginated array.
     * 
     * @param int $page
     * @return array
     */
    public function get_latest_complete_charts() {
        $wpdb = $this->get_db();

        $sql = "SELECT 
                        a.id AS id_chart_archive, a.chart_archive_slug, a.chart_type, 
                        a.chart_date, a.chart_creation_date, a.songs_number AS chart_songs_number,
                        c.id, c.id_chart, c.id_song, c.user_vote,
                        p1.post_name AS chart_slug, p1.post_title AS chart_title, 
                        p1.post_content AS chart_content, p1.post_excerpt AS chart_excerpt,
                        p2.post_name AS song_slug, p2.post_title AS song_title, 
                        p2.post_content AS song_content, p2.post_excerpt AS song_excerpt
                FROM wp_charts_archive AS a, wp_charts_songs AS c, wp_posts AS p1, wp_posts AS p2
                WHERE a.chart_archive_slug = c.chart_archive_slug
                AND   c.id_chart   = p1.ID
                AND   c.id_song    = p2.ID
                AND   a.chart_date = (
                        SELECT MAX(ca.chart_date) AS chart_date
                                FROM wp_charts_archive AS ca
                                WHERE ca.chart_type = a.chart_type
                )
                GROUP BY a.chart_type
                ORDER BY a.chart_date DESC";

        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results;
    }

    /**
     * Return the last, single complete chart
     * that has the specified song.
     * 
     * @param string $slug
     * @return array
     */
    public function get_last_complete_chart_by_song_id($id) {
        $wpdb = $this->get_db();

        $sql = "SELECT 
                        a.id AS id_chart_archive, a.chart_archive_slug, a.chart_date, 
                        a.chart_creation_date, a.songs_number AS chart_songs_number,
                        c.id, c.id_chart, c.id_song, c.user_vote,
                        p1.post_name AS chart_slug, p1.post_title AS chart_title, 
                        p1.post_content AS chart_content, p1.post_excerpt AS chart_excerpt,
                        p2.post_name AS song_slug, p2.post_title AS song_title, 
                        p2.post_content AS song_content, p2.post_excerpt AS song_excerpt
                FROM wp_charts_archive AS a, 
                     wp_charts_songs AS c, 
                     wp_posts AS p1, 
                     wp_posts AS p2
                WHERE a.chart_archive_slug = c.chart_archive_slug
                AND   c.id_chart = p1.ID
                AND   c.id_song  = p2.ID 
                AND   c.id_song  = %d
                ORDER BY a.chart_date DESC
                LIMIT 1";

        $prepared = $wpdb->prepare($sql, array(
            $id
        ));

        $result = $wpdb->get_row($prepared, ARRAY_A);

        return $result;
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

        $result = $wpdb->get_row($prepared, ARRAY_A);

        return $result;
    }

    /**
     * Insert a chart into wp_chart_archive, (that store all archive about chart)
     * 
     * @param array $data
     * @return boolean
     */
    public function insert_complete_chart(array $data = array()) {
        $wpdb = $this->get_db();
        $wpdb->hide_errors();
        $date = date('Y-m-d', time());
        $date_time = date('Y-m-d H:i:s', time());

        // First insert into wp_chart_archive.
        $first_query = "INSERT INTO wp_charts_archive (
                            chart_archive_slug, 
                            id_chart,
                            chart_type, 
                            chart_date, 
                            chart_creation_date, 
                            songs_number
                         )
                        VALUES (%s, %d, %s, %s, %s, %s)";

        $prepared = $wpdb->prepare($first_query, array(
            $data['chart_archive_slug'],
            (int) $data['id_chart'],
            $data['chart_slug'],
            $date,
            $date_time,
            $data['chart_songs_number'],
        ));

        $result = $wpdb->query($prepared);

        if ($result === false) {
            return false;
        }

        return $result;
    }

    /**
     * Insert a song into wp_charts_songs, 
     * (that store all songs associated with a specific chart)
     * 
     * @param array $data
     * @return boolean
     */
    public function insert_chart_song($chart_archive_slug = null, $id_chart = null, $id_song = null) {
        $wpdb = $this->get_db();
        $wpdb->hide_errors();

        $sql = "INSERT INTO wp_charts_songs (chart_archive_slug, id_chart, id_song)
                    VALUES (%s, %d, %d)";

        $prepared = $wpdb->prepare($sql, array(
            $chart_archive_slug,
            (int) $id_chart,
            (int) $id_song,
        ));

        $result = $wpdb->query($prepared);

        if ($result === false) {
            return false;
        }

        return $result;
    }

    /**
     * Update a single song's position
     * into wp_charts_songs.
     * 
     * @param int $id_chart_song
     * @param string $chart_archive_slug
     * @param int $id_song
     * @param int $user_vote
     * @return boolean
     */
    public function update_chart_song(
    $id_chart_song = null, $chart_archive_slug = null, $id_song = null, $user_vote = null
    ) {
        $wpdb = $this->get_db();

        $sql = "UPDATE wp_charts_songs 
                    SET id_song = %d, user_vote = %d
                    WHERE id = %d
                    AND chart_archive_slug = %s";

        $prepared = $wpdb->prepare($sql, array(
            (int) $id_song,
            (int) $user_vote,
            (int) $id_chart_song,
            $chart_archive_slug,
        ));

        $result = $wpdb->query($prepared);

        if ($result === false) {
            return false;
        }

        $affected_rows = $wpdb->rows_affected;

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

        $sql = "DELETE FROM wp_charts_archive 
                        WHERE chart_archive_slug = %s";

        $prepared = $wpdb->prepare($sql, array(
            $slug,
        ));

        $result = $wpdb->query($prepared);

        if ($result === false) {
            return false;
        }

        return $result;
    }

    /**
     * Insert a user vote on wp_charts_songs_vote
     * 
     * @param array $data
     * @return array
     */
    public function insert_complete_chart_vote($chart_archive_slug, $id_song) {
        $wpdb = $this->get_db();
        $wpdb->hide_errors();

        $sql = "INSERT INTO wp_charts_songs_vote (
                    chart_archive_slug,
                    id_song,
                    vote_date,
                    vote_time
                )
                VALUES (%s, %d, %s, %s)";

        $prepared = $wpdb->prepare($sql, array(
            $chart_archive_slug,
            $id_song,
            date('Y-m-d', time()),
            date('H:i:s', time())
        ));

        $result = $wpdb->query($prepared);

        if ($result === false) {
            return false;
        }

        return $result;
    }

}
