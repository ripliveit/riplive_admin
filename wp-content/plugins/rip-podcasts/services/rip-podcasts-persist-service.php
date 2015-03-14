<?php

namespace Rip_Podcasts\Services;

/**
 * A service used
 * to persist podcast's data.
 *
 * @author Gabriele
 */
class Rip_Podcasts_Persist_Service {
    
    /**
     * Holds a reference to Podcasts_Dao
     * 
     * @var Object 
     */
    private $_podcasts_dao;
    
    /**
     * Holds a reference to Posts_Dao.
     * 
     * @var Object 
     */
    private $_posts_dao;

    /**
     * Holds a reference to Podcasts Query Service.
     * 
     * @var Object 
     */
    private $_query_service;
    
    /**
     * An object used to upload podcast's images.
     * 
     * @var Object 
     */
    private $_uploader;

    /**
     * An object used to open db
     * transaction.
     * 
     * @var Object 
     */
    private $_transaction;
    
    /**
     * On construction set all
     * service dependencies.
     * 
     * @param \Rip_General\Classes\Rip_Abstract_Dao $podcasts_dao
     * @param \Rip_General\Classes\Rip_Abstract_Dao $posts_dao
     * @param \Rip_General\Classes\Rip_Abstract_Query_Service $query_service
     * @param \Rip_General\Classes\Rip_Transaction $transaction
     * @param \Rip_Podcasts\Classes\Rip_Podcasts_Image_Uploader $uploader
     */
    public function __construct(
        \Rip_General\Classes\Rip_Abstract_Dao $podcasts_dao, 
        \Rip_General\Classes\Rip_Abstract_Dao $posts_dao, 
        \Rip_General\Classes\Rip_Abstract_Query_Service $query_service, 
        \Rip_General\Classes\Rip_Transaction $transaction, 
        \Rip_Podcasts\Classes\Rip_Podcasts_Image_Uploader $uploader
    ) {
        $this->_podcasts_dao = $podcasts_dao;
        $this->_posts_dao = $posts_dao;
        $this->_query_service = $query_service;
        $this->_transaction = $transaction;
        $this->_uploader = $uploader;
    }
    
    /**
     * Attempt to persist a podcast.
     * 
     * @param array $podcast
     * @return \Rip_General\Dto\Message
     */
    public function insert_podcast(array $podcast = array()) {
        $podcast = stripslashes_deep($podcast);
        $message = new \Rip_General\Dto\Message();

        if (empty($podcast)) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify a podcast');

            return $message;
        }

        $this->_transaction->start();

        $dao = new \Rip_Podcasts\Daos\Rip_Podcasts_Dao();
        $result = $dao->insert_podcast($podcast);

        if ($result === false) {
            $this->_transaction->rollback();

            $message->set_code(500)
                    ->set_status('error')
                    ->set_message('Error in inserting the podcast');

            return $message;
        }

        $this->_transaction->commit();

        $last_id = $this->_transaction->get_db()->insert_id;
        $podcast = $this->_query_service->get_podcast_by_id($last_id);

        return $podcast;
    }
    
    /**
     * Update a single podcast.
     * 
     * @param int $id_podcast
     * @param array $podcast
     * @return \Rip_General\Dto\Message
     */
    public function update_podcast($id_podcast = null, array $podcast = array()) {
        $message = new \Rip_General\Dto\Message();
        $podcast = stripslashes_deep($podcast);

        if (empty($id_podcast)) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify a podcast id');

            return $message;
        }

        if (empty($podcast)) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify podcast data to update');

            return $message;
        }

        $dao = new \Rip_Podcasts\Daos\Rip_Podcasts_Dao();
        $result = $dao->update_podcast($id_podcast, $podcast);

        $this->_transaction->start();

        if ((int) $result === 0) {
            $message->set_code(500)
                    ->set_status('error')
                    ->set_message('Cannot update the podcast');

            return $message;
        }

        $this->_transaction->commit();

        $last_id = $this->_transaction->get_db()->insert_id;
        $podcast = $this->_query_service->get_podcast_by_id($id_podcast);

        return $podcast;
    }
    
    /**
     * Delete a single podcast.
     * 
     * @param int $id_podcast
     * @return \Rip_General\Dto\Message
     */      
    public function delete_podcast($id_podcast) {
        $message = new \Rip_General\Dto\Message();

        if (empty($id_podcast)) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify a podcast id');

            return $message;
        }

        $dao = new \Rip_Podcasts\Daos\Rip_Podcasts_Dao();
        $result = $dao->delete_podcast((int) $id_podcast);

        if ((int) $result === 0) {
            $message->set_code(412)
                    ->set_status('error')
                    ->set_message('Podcast does not exists');

            return $message;
        }

        $message->set_code(200)
                ->set_status('success')
                ->set_message('Podcast successfully deleted');

        return $message;
    }

    /**
     * Upload a podcast image.
     * 
     * @param int $id_podcast
     * @param array $files
     * @return \Rip_General\Dto\Message
     */
    public function upload_podcast_image($id_podcast, array $files = array()) {
        $message = new \Rip_General\Dto\Message();
        
        if (empty($id_podcast)) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify a podcast id');

            return $message;
        }

        if (empty($files['file']) || $files['file']['size'] <= 0) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify a file to upload');

            return $message;
        }

        $uploaded = $this->_uploader->upload((int) $id_podcast, $files['file']);

        if ($uploaded->get_status() === 'error') {
            return $uploaded;
        }
       
        $result = $this->_podcasts_dao->insert_podcast_attachment(
                (int) $id_podcast, $uploaded->get_id_attachment()
        );

        if ((int) $result === 0) {
            $message->set_code(500)
                    ->set_status('error')
                    ->set_message('Error during image persistance into database');

            return $message;
        }

        $podcast = $this->_query_service->get_podcast_by_id($id_podcast);

        return $podcast;
    }

}
