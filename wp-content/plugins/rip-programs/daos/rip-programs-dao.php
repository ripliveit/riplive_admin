<?php

namespace Rip_Programs\Daos;

/**
 * Programs Data Access Object.
 */
class Rip_Programs_Dao extends \Rip_General\Classes\Rip_Abstract_Dao {

    /**
     * A method that set the Program data.
     * Retrieve the total count of podcasts associated to a program via the podcasts dao.
     * 
     * @param \WP_Query $query
     * @return array
     */
    protected function _set_programs_data(\WP_Query $query) {
        $podcasts_dao = new \Rip_Podcasts\Daos\Rip_Podcasts_Dao();

        $out = array();

        while ($query->have_posts()) {
            $query->the_post();
            $post = get_post(get_the_ID());

            $number_of_podcasts = $podcasts_dao->get_podcasts_number_by_program_slug($post->post_name);

            array_push($out, array(
                'id_program' => get_the_ID(),
                'slug' => $post->post_name,
                'date' => $post->post_date,
                'modified' => $post->post_modified,
                'status' => $post->post_status,
                'program_title' => get_the_title(),
                'program_content' => $this->get_the_content(),
                'program_excpert' => get_the_excerpt(),
                'program_images' => array(
                    'thumbnail' => $this->get_post_images(get_the_ID(), 'thumbnail'),
                    'image_medium' => $this->get_post_images(get_the_ID(), 'medium'),
                    'image_large' => $this->get_post_images(get_the_ID(), 'large'),
                    'image_full' => $this->get_post_images(get_the_ID(), 'full'),
                    'landscape_medium' => $this->get_post_images(get_the_ID(), 'medium-landscape'),
                    'landscape_large' => $this->get_post_images(get_the_ID(), 'large-landscape'),
                ),
                'program_category' => wp_get_post_terms(get_the_ID(), 'program-category'),
                'program_tags' => wp_get_post_terms(get_the_ID(), 'program-tag'),
                'program_information' => $this->get_program_information(get_the_ID()),
                'total_podcasts' => (int) $number_of_podcasts['total_items'],
            ));
        }

        wp_reset_query();
        wp_reset_postdata();

        return $out;
    }

    /**
     * Retrieve all programs.
     * 
     * @return array
     */
    public function get_all_programs($page = null) {
        $args = array(
            'post_type' => 'programs',
            'post_status' => 'publish',
        );

        $args = $this->get_pagination_args(
                $args, $this->_items_per_page, $page
        );

        $query = new \WP_Query($args);
        $results = $this->_set_programs_data($query);

        return $results;
    }

    /**
     * Retrieve all programs (Even the ones in pending status).
     * 
     * @return array
     */
    public function get_all_programs_for_podcasts($page = null) {
        $args = array(
            'post_type' => 'programs',
            'post_status' => array(
                'publish',
                'pending',
            ),
        );

        $args = $this->get_pagination_args(
                $args, $this->_items_per_page, $page
        );

        $query = new \WP_Query($args);
        $results = $this->_set_programs_data($query);

        return $results;
    }

    /**
     * Return a single program by its unique slug.
     * 
     * @param string $slug
     * @return \WP_Query
     */
    public function get_program_by_slug($slug) {
        $args = array(
            'post_type' => 'programs',
            'name' => $slug,
            'post_status' => array(
                'publish',
                'pending',
            ),
        );

        $query = new \WP_Query($args);
        $results = $this->_set_programs_data($query);

        return current($results);
    }

    /**
     * Return the program's week schedule.
     */
    public function get_programs_schedule() {
        $programs = $this->get_all_programs();
        $days = $this->get_days_for_metaboxes();

        if (empty($programs) || empty($days)) {
            return false;
        }

        $schedule = array();
        $out = array();

        foreach ($days as $day) {
            $day['label'] = ucfirst($day['label']);

            if (date('l', time()) === ucfirst($day['label'])) {
                $day['today'] = true;
            } else {
                $day['today'] = false;
            }

            $day['programs'] = array();

            // Cycle each program. If a program is scheduled for the current day
            // than save it into the final $day['programs'] array.
            foreach ($programs as $k => $program) {
                $schedule[$day['value']] = array();
                $information = $program['program_information'];

                // Check if the current program is scheduled for the current day, than remove all unnecessary information.
                if (!empty($information['programs-days']) && in_array(date('l', strtotime('Sunday + ' . $day['value'] . 'Days')), $information['programs-days'])) {
                    $program['rerun'] = false;
                    $program['schedule'] = $information['programs-schedule'];
                    $program['authors'] = $information['programs-authors'];

                    unset($program['program_information']);

                    array_push($day['programs'], $program);
                }

                //Check if the program is a rerun, than remove all unnecessary information.
                if (!empty($information['reruns-days']) && in_array(date('l', strtotime('Sunday + ' . $day['value'] . 'Days')), $information['reruns-days'])) {
                    $program['rerun'] = true;
                    $program['schedule'] = $information['reruns-schedule'];
                    $program['authors'] = $information['programs-authors'];

                    unset($program['program_information']);

                    array_push($day['programs'], $program);
                }
            }

            usort($day['programs'], array($this, 'sort_programs_by_schedule'));

            array_push($out, $day);
        }

        return $out;
    }

    /**
     * Function used to sort all programs by their relative schedule time.
     * 
     * @param date $a
     * @param date $b
     * @return int
     */
    private function sort_programs_by_schedule($a, $b) {
        $a = strtotime($a['schedule']);
        $b = strtotime($b['schedule']);

        if ($a == $b) {
            return 0;
        }

        return ($a > $b) ? 1 : -1;
    }

    /**
     * Return all custom field of a specific program.
     * 
     * @param int $id_program
     * @return array
     */
    public function get_program_information($id_program) {
        if (empty($id_program)) {
            return false;
        }

        $author_dao = new \Rip_Authors\Daos\Rip_Authors_Dao();

        $fields = get_post_custom($id_program);
        $meta = array();

        // Loop over all custom fields to populate $meta array.
        // Retrieve all authors information instead the default's one.
        foreach ($fields as $key => $value) {
            // private meta
            if (substr($key, 0, 1) === '_') {
                continue;
            }

            // Remove programs-hidden meta.
            if ($key === 'programs-hidden') {
                continue;
            }

            $meta[$key] = get_post_meta($id_program, $key, true);

            // Set authors data.
            if ($key === 'programs-authors') {
                $authors = array();

                foreach ($meta[$key] as $author_id) {
                    $author_slug = get_the_author_meta('user_nicename', $author_id);

                    array_push($authors, $author_dao->get_author_by_slug($author_slug));
                }

                $meta[$key] = $authors;
            }

            //Set days data.
            if ($key === 'programs-days' || $key === 'reruns-days') {
                $days = array();

                foreach ($meta[$key] as $day) {
                    array_push($days, date('l', strtotime('Sunday +' . $day . 'Days')));
                }

                $meta[$key] = $days;
            }
        }

        return $meta;
    }

    /**
     * Return an array with all wordpress users and their respective id.
     * @return array
     */
    public function get_all_users_for_metabox() {
        $users = get_users();

        if (empty($users)) {
            return false;
        }

        $out = array();

        foreach ($users as $user) {
            array_push($out, array(
                'label' => $user->display_name,
                'value' => $user->ID
            ));
        }

        return $out;
    }

    /**
     * Return a list of week days.
     * @return array
     */
    public function get_days_for_metaboxes() {
        $out = array();

        for ($i = 1; $i <= 7; $i++) {
            array_push($out, array(
                'label' => date('l', strtotime("Sunday + $i days")),
                'value' => $i
            ));
        }

        return $out;
    }

}