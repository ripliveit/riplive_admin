<?php

namespace Rip_Podcasts\Mappers;

/**
 * 
 *
 * @author Gabriele
 */
class Rip_Podcast_Mapper implements \Rip_General\Interfaces\Rip_Mapper_Array_Interface {

    /**
     * Holds a reference
     * to Post_Dao, used to hydrate
     * some properties that require a query through Wordpress system.
     * 
     * @var Object 
     */
    protected $_posts_dao;
   
    protected $_authors_query_service;

   
    public function __construct(
            \Rip_General\Classes\Rip_Abstract_Dao $posts_dao,
            \Rip_General\Classes\Rip_Abstract_Query_Service $authors_query_service
    ) {
        $this->_posts_dao = $posts_dao;
        $this->_authors_query_service = $authors_query_service;
    }

    public function map(array $podcasts = array()) {
        if (empty($podcasts)) {
            return array();
        }

        $accumulator = array();

        foreach ($podcasts as $podcast) {
            $authors = array();
            $authors_ids = get_post_meta($podcast['id_program'], 'programs-authors', true);
            

            // Set the author's data.
            if (!empty($authors_ids)) {
                foreach ($authors_ids as $author_id) {
                    $wp_author = get_user_by('id', $author_id);
                    $author_dto = $this->_authors_query_service->get_author_by_slug($wp_author->user_nicename);
    
                    
                    array_push($authors, $author_dto->get_author());
                }

                $podcast['authors'] = $authors;
            } else {
                $podcast['authors'] = '';
            }

            // Set podcast's images.
            // Use program's images if no images are presents.
            if (!empty($podcast['id_attachment'])) {
                $podcast['podcast_images'] = array(
                    'thumbnail' => $this->_posts_dao->get_attachment_images($podcast['id_attachment'], 'thumbnail'),
                    'image_medium' => $this->_posts_dao->get_attachment_images($podcast['id_attachment'], 'medium'),
                    'image_large' => $this->_posts_dao->get_attachment_images($podcast['id_attachment'], 'large'),
                    'image_full' => $this->_posts_dao->get_attachment_images($podcast['id_attachment'], 'full'),
                    'landscape_medium' => $this->_posts_dao->get_post_images($podcast['id_attachment'], 'medium-landscape'),
                    'landscape_large' => $this->_posts_dao->get_post_images($podcast['id_attachment'], 'large-landscape'),
                );
            } else {
                $podcast['podcast_images'] = array(
                    'thumbnail' => $this->_posts_dao->get_post_images($podcast['id_program'], 'thumbnail'),
                    'image_medium' => $this->_posts_dao->get_post_images($podcast['id_program'], 'medium'),
                    'image_large' => $this->_posts_dao->get_post_images($podcast['id_program'], 'large'),
                    'image_full' => $this->_posts_dao->get_post_images($podcast['id_program'], 'full'),
                    'landscape_medium' => $this->_posts_dao->get_post_images($podcast['id_program'], 'medium-landscape'),
                    'landscape_large' => $this->_posts_dao->get_post_images($podcast['id_program'], 'large-landscape'),
                );
            }

            array_push($accumulator, $podcast);
        }

        return $accumulator;
    }

}
