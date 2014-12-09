<?php

/**
 * Data Access object for Podcasts
 */
class rip_podcasts_dao extends rip_abstract_dao {

    /**
     * Number of items per page.
     * @var type 
     */
    protected $_items_per_page = 24;

    /**
     * A method that set the Podcast data.
     * 
     * @param array $podcasts
     * @return boolean|array
     */
    protected function _set_podcasts_data(array $podcasts) {
        if (empty($podcasts)) {
            return array();
        }

        $authors_dao = new rip_authors_dao();

        $out = array();

        foreach ($podcasts as $podcast) {
            $authors = array();
            $authors_id = get_post_meta($podcast['id_program'], 'programs-authors');

            // Set the author's data.
            if (!empty($authors_id)) {
                foreach ($authors_id[0] as $author_id) {
                    $wp_author = get_user_by('id', $author_id);
                    $author = $authors_dao->get_author_by_slug($wp_author->user_nicename);

                    array_push($authors, $author);
                }

                $podcast['authors'] = $authors;
            } else {
                $podcast['authors'] = '';
            }

            // Set podcast's images.
            // Use program's images if no images are presents.
            if (!empty($podcast['id_attachment'])) {
                $podcast['podcast_images'] = array(
                    'thumbnail' => $this->get_attachment_images($podcast['id_attachment'], 'thumbnail'),
                    'image_medium' => $this->get_attachment_images($podcast['id_attachment'], 'medium'),
                    'image_large' => $this->get_attachment_images($podcast['id_attachment'], 'large'),
                    'image_full' => $this->get_attachment_images($podcast['id_attachment'], 'full'),
                    'landscape_medium' => $this->get_post_images($podcast['id_attachment'], 'medium-landscape'),
                    'landscape_large' => $this->get_post_images($podcast['id_attachment'], 'large-landscape'),
                );
            } else {
                $podcast['podcast_images'] = array(
                    'thumbnail' => $this->get_post_images($podcast['id_program'], 'thumbnail'),
                    'image_medium' => $this->get_post_images($podcast['id_program'], 'medium'),
                    'image_large' => $this->get_post_images($podcast['id_program'], 'large'),
                    'image_full' => $this->get_post_images($podcast['id_program'], 'full'),
                    'landscape_medium' => $this->get_post_images($podcast['id_program'], 'medium-landscape'),
                    'landscape_large' => $this->get_post_images($podcast['id_program'], 'large-landscape'),
                );
            }

            array_push($out, $podcast);
        }

        return $out;
    }

    /**
     * Return number of pages.
     * 
     * @global object $wpdb
     * @param int $id_program
     * @return array
     */
    public function get_podcasts_number_of_pages($slug = null) {
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
     * Retrieve all podcasts
     * 
     * @global object $wpdb
     * @param int $page
     * @return array
     */
    public function get_all_podcasts($page = null) {
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
            $offset = ($page * $this->_items_per_page) - $this->_items_per_page;
            $sql .= ' LIMIT ' . $offset . ', ' . $this->_items_per_page;
        } else {
            $sql .= ' LIMIT ' . (int) $this->_items_per_page;
        }

        $results = $wpdb->get_results($sql, ARRAY_A);

        $podcasts_data = $this->_set_podcasts_data($results);

        return $podcasts_data;
    }

    /**
     * Retrieve all podcasts with a specific program slug.
     * 
     * @global object $wpdb
     * @param int $id_program
     * @param int $page
     * @return array
     */
    public function get_all_podcasts_by_program_slug($slug, $page = null) {
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
            $offset = ($page * $this->_items_per_page) - $this->_items_per_page;
            $sql .= ' LIMIT ' . $offset . ', ' . $this->_items_per_page;
        } else {
            $sql .= ' LIMIT ' . (int) $this->_items_per_page;
        }

        $prepared = $wpdb->prepare($sql, array(
            $slug
        ));

        $results = $wpdb->get_results($prepared, ARRAY_A);

        $podcasts_data = $this->_set_podcasts_data($results);

        return $podcasts_data;
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

        $results = $wpdb->get_row($prepared, ARRAY_A);

        $podcasts_data = $this->_set_podcasts_data(empty($results) ? array() : array($results));

        return empty($podcasts_data) ? false : current($podcasts_data);
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

        $results = $wpdb->get_row($prepared, ARRAY_A);

        return $results;
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

        $wpdb->query('START TRANSACTION');

        try {
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

            $results = $wpdb->query($prepared);
        } catch (Exception $exc) {
            $wpdb->query('ROLLBACK');
            echo $exc->getTraceAsString();
        }

        $wpdb->query('COMMIT');

        $last_id = $wpdb->insert_id;

        $podcast = $this->get_podcast_by_id($last_id);

        return $podcast;
    }

    /**
     * Insert a podcast's image.
     * into wp_podcasts_attachment.
     * 
     * @param array $data
     * @return array
     */
    public function insert_podcast_attachment($data = array()) {
        $wpdb = $this->get_db();

        $wpdb->query('START TRANSACTION');

        try {
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
                (int) $data['id_podcast'],
                (int) $data['id_attachment'],
                date('Y-m-d H:i:s', time()),
                (int) $data['id_podcast'],
                (int) $data['id_attachment'],
                date('Y-m-d H:i:s', time()),
            ));

            $results = $wpdb->query($prepared);
        } catch (Exception $exc) {
            $wpdb->query('ROLLBACK');
            echo $exc->getTraceAsString();
        }

        $wpdb->query('COMMIT');

        $podcast = $this->get_podcast_by_id($data['id_podcast']);

        return $podcast;
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

        $wpdb->query('START TRANSACTION');

        try {
            $sql = "UPDATE wp_podcasts 
                        SET title = %s, 
                        summary = %s
                        WHERE id = %d";

            $prepared = $wpdb->prepare($sql, array(
                $data['title'],
                $data['summary'],
                (int) $id_podcast,
            ));

            $results = $wpdb->query($prepared);
        } catch (Exception $exc) {
            $wpdb->query('ROLLBACK');
            echo $exc->getTraceAsString();
        }

        $affected_rows = $wpdb->rows_affected;

        $wpdb->query('COMMIT');

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

        $results = $wpdb->query($sql);

        return $results;
    }

}