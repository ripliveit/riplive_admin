<?php

namespace Rip_Charts\Services;

/**
 * A service used by other Chart plugin's classes
 * to implement and run chart's business logic.
 */
class Rip_Charts_Query_Service extends \Rip_General\Classes\Rip_Abstract_Query_Service {

    /**
     * Holds a reference to Chart Dao.
     * 
     * @var Object 
     */
    private $_charts_dao;

    /**
     * Holds a reference to Complete Charts Dao.
     * 
     * @var Object 
     */
    private $_complete_charts_dao;

    /**
     * Holds a reference to Posts Dao.
     * 
     * @var Object 
     */
    private $_posts_dao;

    /**
     * On construction
     * set the dependencies.
     * 
     * @param \Rip_General\Classes\Rip_Abstract_Dao $charts_dao
     * @param \Rip_General\Classes\Rip_Abstract_Dao $complete_charts_dao
     * @param \Rip_General\Classes\Rip_Abstract_Dao $posts_dao
     */
    public function __construct(
    \Rip_General\Classes\Rip_Abstract_Dao $charts_dao, \Rip_General\Classes\Rip_Abstract_Dao $complete_charts_dao, \Rip_General\Classes\Rip_Abstract_Dao $posts_dao
    ) {
        $this->_charts_dao = $charts_dao;
        $this->_complete_charts_dao = $complete_charts_dao;
        $this->_posts_dao = $posts_dao;
        $this->set_items_per_page(24);
    }

    /**
     * Query for all
     * Charts Custom Post Type.
     * 
     * @return \Rip_General\Dto\Message
     */
    public function get_all_charts() {
        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_Charts\Mappers\Rip_Chart_Mapper', $this->_posts_dao
        );

        $data = $mapper->map($this->_charts_dao->get_all_charts());

        $message = new \Rip_General\Dto\Message();
        $message->set_status('ok')
                ->set_code(200)
                ->set_count(count($data))
                ->set_count_total(count($data))
                ->set_pages(1)
                ->set_charts(empty($data) ? array() : $data);

        return $message;
    }

    /**
     * Query for a single post Charts Custom Post Type.
     * 
     * @param string $slug
     * @return \Rip_General\Dto\Message
     */
    public function get_chart_by_slug($slug = null) {
        $message = new \Rip_General\Dto\Message();

        if (empty($slug)) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify a chart slug');

            return $message;
        }

        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_Charts\Mappers\Rip_Chart_Mapper', $this->_posts_dao
        );
        $data = $mapper->map($this->_charts_dao->get_chart_by_slug($slug));

        if (empty($data)) {
            $message->set_code(404)
                    ->set_status('error')
                    ->set_message('Chart not found');

            return $message;
        }

        $message->set_code(200)
                ->set_status('ok')
                ->set_chart(current($data));

        return $message;
    }

    /**
     * Using the total
     * number of items per page return the total number
     * of page for a specific or for all charts.
     * 
     * @param string $slug
     * @return \Rip_General\Dto\Message
     */
    public function get_complete_charts_number_of_pages($slug = null) {
        $message = new \Rip_General\Dto\Message();
        $data = $this->_complete_charts_dao->get_complete_charts_number_of_pages($slug, $this->get_items_per_page());

        if (empty($data)) {
            $message->set_code(404)
                    ->set_status('error')
                    ->set_message('Cannot find number of pages');

            return $message;
        }

        $message->set_code(200)
                ->set_status('ok')
                ->set_number_of_pages($data);

        return $message;
    }

    /**
     * Return a list of all complete charts, 
     * ordered by date.
     */
    
    /**
     * Return a list of all
     * complete charts. 
     * Return a paginated result.
     * 
     * @param int $count
     * @param int $page
     * @return \Rip_General\Dto\Message
     */
    public function get_all_complete_charts($count = null, $page = null) {
        $message = new \Rip_General\Dto\Message();
        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_Charts\Mappers\Rip_Complete_Chart_Mapper', $this->_posts_dao
        );

        $count = $this->validate_items_per_page((int) $count);
        $data = $mapper->map(
                $this->_complete_charts_dao->get_all_complete_charts($count, $page)
        );
        $pages = $this->_complete_charts_dao->get_complete_charts_number_of_pages(null, $count);

        $message->set_status('ok')
                ->set_code(200)
                ->set_count(count($data))
                ->set_count_total((int) $pages['count_total'])
                ->set_pages($pages['pages'])
                ->set_complete_charts(empty($data) ? array() : $data);

        return $message;
    }

    /**
     * Return all complete charts of a specific type, 
     * specifing the slug of the desired type.
     *  
     * @param string $slug
     * @param int $count
     * @param int $page
     * @return \Rip_General\Dto\Message
     */
    public function get_all_complete_charts_by_chart_type($slug = null, $count = null, $page = null) {
        $message = new \Rip_General\Dto\Message();

        if (empty($slug)) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify a chart type slug, for example rock-chart');

            return $message;
        }

        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_Charts\Mappers\Rip_Complete_Chart_Mapper', $this->_posts_dao
        );

        $count = $this->validate_items_per_page((int) $count);
        $data = $mapper->map(
                $this->_complete_charts_dao->get_all_complete_charts_by_chart_type($slug, $count, $page)
        );

        if (empty($data)) {
            $message->set_code(404)
                    ->set_status('error')
                    ->set_message('Cannot find charts of type ' . $slug);

            return $message;
        }

        $pages = $this->_complete_charts_dao->get_complete_charts_number_of_pages($slug, $count);

        $message->set_status('ok')
                ->set_code(200)
                ->set_count(count($data))
                ->set_count_total((int) $pages['count_total'])
                ->set_pages($pages['pages'])
                ->set_complete_charts(empty($data) ? array() : $data);

        return $message;
    }

    /**
     * Return lastest complete charts, one per genre.
     * (Genres are rock, pop etc).
     * 
     * @return \Rip_General\Dto\Message
     */
    public function get_latest_complete_charts() {
        $message = new \Rip_General\Dto\Message();

        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_Charts\Mappers\Rip_Complete_Chart_Mapper', $this->_posts_dao
        );

        $data = $mapper->map($this->_complete_charts_dao->get_latest_complete_charts());

        $message->set_status('ok')
                ->set_code(200)
                ->set_count(count($data))
                ->set_count_total((int) count($data))
                ->set_pages(1)
                ->set_complete_charts(empty($data) ? array() : $data);

        return $message;
    }
    
    /**
     * Return the last chart (in chronological order)
     * that has the specified song.
     * 
     * @param int $id
     * @return \Rip_General\Dto\Message
     */
    public function get_last_complete_chart_by_song_id($id) {
        $message = new \Rip_General\Dto\Message();

        if (empty($id)) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify a song id');

            return $message;
        }

        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_Charts\Mappers\Rip_Complete_Chart_Mapper', $this->_posts_dao
        );

        $data = $mapper->map(array(
            $this->_complete_charts_dao->get_last_complete_chart_by_song_id($id)
        ));

        if (empty($data)) {
            $message->set_code(404)
                    ->set_status('error')
                    ->set_message('Chart not found');

            return $message;
        }

        $message->set_code(200)
                ->set_status('ok')
                ->set_complete_chart(current($data));

        return $message;
    }

    /**
     * Return a single complete chart.
     * 
     * @param string $slug
     * @return \Rip_General\Dto\Message
     */
    public function get_complete_chart_by_chart_archive_slug($slug) {
        $message = new \Rip_General\Dto\Message();

        if (empty($slug)) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify a chart archive slug');

            return $message;
        }

        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_Charts\Mappers\Rip_Complete_Chart_Mapper', $this->_posts_dao
        );

        $data = $mapper->map(
            $this->_complete_charts_dao->get_complete_chart_by_chart_archive_slug($slug)
        );

        if (empty($data)) {
            $message->set_code(404)
                    ->set_status('error')
                    ->set_message('Chart not found');

            return $message;
        }

        $message->set_code(200)
                ->set_status('ok')
                ->set_complete_chart(current($data));

        return $message;
    }

}
