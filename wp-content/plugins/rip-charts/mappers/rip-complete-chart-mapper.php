<?php

namespace Rip_Charts\Mappers;

/**
 * Description of rip-charts-mapper
 *
 * @author Gabriele
 */
class Rip_Complete_Chart_Mapper implements \Rip_General\Interfaces\Rip_Mapper_Array_Interface {

    protected $_dao;

    public function __construct(\Rip_General\Classes\Rip_Abstract_Dao $dao) {
        $this->_dao = $dao;
    }

    public function map(array $chart_data) {
       if (empty($chart_data)) {
            return array();
        }

        $accumulator = array();

        // First populate the chart's data.
        foreach ($chart_data as $value) {
            $archive_slug = $value['chart_archive_slug'];

            $accumulator[$archive_slug]['id_chart_archive'] = (int) $value['id_chart_archive'];
            $accumulator[$archive_slug]['chart_archive_slug'] = $value['chart_archive_slug'];
            $accumulator[$archive_slug]['id_chart'] = (int) $value['id_chart'];
            $accumulator[$archive_slug]['chart_slug'] = $value['chart_slug'];
            $accumulator[$archive_slug]['chart_title'] = $value['chart_title'];
            $accumulator[$archive_slug]['chart_content'] = $value['chart_content'];
            $accumulator[$archive_slug]['chart_excerpt'] = $value['chart_excerpt'];
            $accumulator[$archive_slug]['chart_date'] = $value['chart_date'];
            $accumulator[$archive_slug]['chart_locale_date'] = date_i18n('d F Y', strtotime($value['chart_date']));
            $accumulator[$archive_slug]['chart_creation_date'] = $value['chart_creation_date'];
            $accumulator[$archive_slug]['chart_songs_number'] = $value['chart_songs_number'];
            $accumulator[$archive_slug]['chart_genre'] = wp_get_post_terms((int) $value['id_chart'], 'chart-genre');
            $accumulator[$archive_slug]['chart_tags'] = wp_get_post_terms((int) $value['id_chart'], 'chart-tag');
            $accumulator[$archive_slug]['chart_images'] = array(
                'thumbnail' => $this->_dao->get_post_images($value['id_chart'], 'thumbnail'),
                'image_medium' => $this->_dao->get_post_images($value['id_chart'], 'medium'),
                'image_large' => $this->_dao->get_post_images($value['id_chart'], 'large'),
                'image_full' => $this->_dao->get_post_images($value['id_chart'], 'full'),
                'landscape_medium' => $this->_dao->get_post_images($value['id_chart'], 'medium-landscape'),
                'landscape_large' => $this->_dao->get_post_images($value['id_chart'], 'large-landscape'),
            );
            $accumulator[$archive_slug]['songs'] = array();
        }

        // Then fill with all relative's songs.
        foreach ($chart_data as $item) {
            $archive_slug = $item['chart_archive_slug'];

            array_push($accumulator[$archive_slug]['songs'], array(
                'id_chart_song' => (int) $item['id'],
                'id_song' => (int) $item['id_song'],
                'song_slug' => $item['song_slug'],
                'song_title' => $item['song_title'],
                'song_content' => $item['song_content'],
                'song_excerpt' => $item['song_excerpt'],
                'song_genre' => wp_get_post_terms($item['id_song'], 'song-genre'),
                'song_tags' => wp_get_post_terms($item['id_song'], 'song-tag'),
                'song_images' => array(
                    'thumbnail' => $this->_dao->get_post_images($item['id_song'], 'thumbnail'),
                    'image_medium' => $this->_dao->get_post_images($item['id_song'], 'medium'),
                    'image_large' => $this->_dao->get_post_images($item['id_song'], 'large'),
                    'image_full' => $this->_dao->get_post_images($item['id_song'], 'full'),
                    'landscape_medium' => $this->_dao->get_post_images($item['id_song'], 'medium-landscape'),
                    'landscape_large' => $this->_dao->get_post_images($item['id_song'], 'large-landscape'),
                ),
                'song_artist' => get_post_meta($item['id_song'], 'songs-artist', true),
                'song_album' => get_post_meta($item['id_song'], 'songs-album', true),
                'url_spotify' => get_post_meta($item['id_song'], 'songs-spotify', true),
                'user_vote' => (int) $item['user_vote'],
            ));
        }

        return array_values($accumulator);
    }

}
