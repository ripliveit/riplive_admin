<?php

namespace Rip_Podcasts\Daos;

/**
 * Data Access object for Podcasts
 */
class Rip_Podcasts_Dao extends \Rip_General\Classes\Rip_Abstract_Dao {

    /**
     * Number of items per page.
     * @var type 
     */
    protected $_items_per_page = 24;

    /**
     * Return number of pages.
     * 
     * @global object $wpdb
     * @param int $id_program
     * @return array
     */
    public function get_podcasts_number_of_pages($slug = null, $count) {
        $wpdb = $this->get_db();

        $sql = "SELECT COUNT(p.id) AS total_items
                    FROM wp_podcasts AS p, wp_posts AS ps
                    WHERE p.id_program = ps.ID
                    AND ps.post_type = 'programs'";

        if ($slug !== null) {
            $sql .= ' AND ps.post_name = %s';

            $prepared = $wpdb->prepare($sql, array($slug));
        } else {
            $prepared = $sql;
        }

        $results = $wpdb->get_row($prepared, ARRAY_A);

        if (empty($results)) {
            return false;
        }

        if ($count === null) {
            $number_of_pages = 1;
        } else {
            $number_of_pages = ceil($results['total_items'] / $count);
        }

        return array(
            'count_total' => $results['total_items'],
            'pages' => $number_of_pages
        );
    }

    /**
     * Retrieve all podcasts
     * 
     * @global object $wpdb
     * @param int $page
     * @return array
     */
    public function get_all_podcasts($count = null, $page = null) {
        $wpdb = $this->get_db();

        $sql = "SELECT p.*, pa.id_attachment, ps.post_name AS program_slug, 
                       ps.post_title AS program_title, ps.post_content AS program_content
		FROM wp_podcasts p 
		INNER JOIN wp_posts ps
		ON p.id_program = ps.ID
		LEFT JOIN wp_podcasts_attachments pa
		ON p.id = pa.id_podcast
		ORDER BY p.date DESC";

        if ($page) {
            $offset = ($page * $count) - $count;
            $sql .= ' LIMIT ' . $offset . ', ' . $count;
        } else {
            $sql .= ' LIMIT ' . (int) $count;
        }

        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results;
    }

    /**
     * Retrieve all podcasts with a specific program slug.
     * 
     * @global object $wpdb
     * @param int $id_program
     * @param int $page
     * @return array
     */
    public function get_all_podcasts_by_program_slug($slug, $count = null, $page = null) {
        $wpdb = $this->get_db();

        $sql = "SELECT p.*, pa.id_attachment, 
                    ps.post_title AS program_title, ps.post_name AS program_slug, ps.post_content AS program_content
		FROM wp_podcasts p
		INNER JOIN wp_posts ps
		ON p.id_program = ps.ID
		LEFT JOIN wp_podcasts_attachments pa
		ON p.id = pa.id_podcast
		WHERE ps.post_name = %s
		ORDER BY p.date DESC";


        if ($page) {
            $offset = ($page * $count) - $count;
            $sql .= ' LIMIT ' . $offset . ', ' . $count;
        } else {
            $sql .= ' LIMIT ' . (int) $count;
        }

        $prepared = $wpdb->prepare($sql, array(
            $slug
        ));

        $results = $wpdb->get_results($prepared, ARRAY_A);

        return $results;
    }

    /**
     * Retrieve a podcast by its unique identifier.
     * 
     * @global object $wpdb
     * @param int $id_podcast
     * @return array
     */
    public function get_podcast_by_id($id_podcast) {
        $wpdb = $this->get_db();

        $sql = "SELECT  p.*, pa.id_attachment, 
                    ps.post_title AS program_title,
                    ps.post_name AS program_slug, ps.post_content AS program_content
		FROM wp_podcasts p 
		INNER JOIN wp_posts ps
		ON p.id_program = ps.ID
		LEFT JOIN wp_podcasts_attachments pa
		ON p.id = pa.id_podcast
		WHERE p.id = %d";

        $prepared = $wpdb->prepare($sql, array(
            (int) $id_podcast
        ));

        $result = $wpdb->get_row($prepared, ARRAY_A);

        return $result;
    }

    /**
     * Return all number of podcast of a particular program.
     * 
     * @param string $slug
     */
    public function get_podcasts_number_by_program_slug($slug) {
        $wpdb = $this->get_db();

        $sql = "SELECT COUNT(p.id) as total_items
                    FROM wp_podcasts AS p, wp_posts AS ps
                    WHERE p.id_program = ps.ID
                    AND ps.post_name = %s";

        $prepared = $wpdb->prepare($sql, array(
            $slug
        ));

        $result = $wpdb->get_row($prepared, ARRAY_A);

        return $result;
    }

    /**
     * Insert a podcast.
     * If a record with UNIQUE KEY (file_name, year) is already present than
     * an upsert is performed.
     * 
     * @global object $wpdb
     * @param int $data
     * @return array
     */
    public function insert_podcast($data = array()) {
        $wpdb = $this->get_db();

        $sql = "INSERT INTO wp_podcasts (
                            id_program,
                            title,
                            summary,
                            genre,
                            authors,
                            file_name,
                            file_length,
                            duration,
                            year,
                            date,
                            upload_date,
                            url
                )
                VALUES(%d, %s, %s, %s, %s, %s, %d, %s, %s, %s, %s, %s)
                ON DUPLICATE KEY UPDATE
                            id_program = %d,
                            title = %s,
                            summary = %s,
                            genre = %s,
                            authors = %s,
                            file_name = %s,
                            file_length = %d,
                            duration = %s,
                            year = %s,
                            date = %s,
                            upload_date = %s,
                            url = %s";

        $prepared = $wpdb->prepare($sql, array(
            (int) $data['id_program'],
            $data['title'],
            $data['summary'],
            $data['genre'],
            $data['authors'],
            $data['file_name'],
            (int) $data['file_length'],
            $data['duration'],
            $data['year'],
            date('Y-m-d', strtotime($data['date'])),
            date('Y-m-d H:i:s', time()),
            $data['url'],
            (int) $data['id_program'],
            $data['title'],
            $data['summary'],
            $data['genre'],
            $data['authors'],
            $data['file_name'],
            (int) $data['file_length'],
            $data['duration'],
            $data['year'],
            date('Y-m-d', strtotime($data['date'])),
            date('Y-m-d H:i:s', time()),
            $data['url'],
        ));

        $result = $wpdb->query($prepared);
        
        return $result;
    }

    /**
     * Insert a podcast's image.
     * into wp_podcasts_attachment.
     * 
     * @param array $data
     * @return array
     */
    public function insert_podcast_attachment($id_podcast, $id_attachment) {
        $wpdb = $this->get_db();

        $sql = "INSERT INTO wp_podcasts_attachments (
                        id_podcast,
                        id_attachment,
                        upload_date
                )
                VALUES(%d, %d, %s)
                ON DUPLICATE KEY UPDATE
                        id_podcast = %d,
                        id_attachment = %d,
                        upload_date = %s";

        $prepared = $wpdb->prepare($sql, array(
            (int) $id_podcast,
            (int) $id_attachment,
            date('Y-m-d H:i:s', time()),
            (int) $id_podcast,
            (int) $id_attachment,
            date('Y-m-d H:i:s', time()),
        ));

        $result = $wpdb->query($prepared);
       
        return $result;
    }

    /**
     * Update each row of a complete chart.
     * A complete chart is made by 50 songs with the same chart_id and chart_date.
     * Each row id must be specified to perform the update in a transaction.
     * 
     * @global object $wpdb
     * @param type $data
     * @return array
     */
    public function update_podcast($id_podcast, $data = array()) {
        $wpdb = $this->get_db();

        $sql = "UPDATE wp_podcasts 
                    SET title = %s, 
                    summary = %s
                    WHERE id = %d";
        
        $prepared = $wpdb->prepare($sql, array(
            $data['title'],
            $data['summary'],
            (int) $id_podcast,
        ));
        
        $result = $wpdb->query($prepared);
        
        $affected_rows = $wpdb->rows_affected;      

        return $affected_rows;
    }

    /**
     * Delete a single podcast.
     * 
     * @global object $wpdb
     * @param int $id_podcast
     * @return array
     */
    public function delete_podcast($id_podcast) {
        $wpdb = $this->get_db();

        $sql = $wpdb->prepare(
                "DELETE FROM wp_podcasts 
                 WHERE id = %d", array($id_podcast)
        );

        $result = $wpdb->query($sql);

        return $result;
    }

}
