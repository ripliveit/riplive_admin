<?php

namespace Rip_Seo\Classes;

/**
 * A Class responsible
 * to create an xml sitemap.
 *
 * @author Gabriele
 */
class Rip_Sitemap_Generator {
    
    /**
     * The url used
     * to construct each <loc> url.
     * 
     * @var string 
     */
    private $_base_uri = 'http://www.riplive.it';

    /**
     * Default folder value.
     * 
     * @var string 
     */
    private $_folder = '.';

    /**
     * Default filename value.
     * 
     * @var string 
     */
    private $_filename = 'sitemap.xml';
        
    /**
     * Class constructor.
     * 
     * @param \Rip_General\Dto\Message $message
     */
    public function __construct(\Rip_General\Dto\Message $message) {
        $this->_message = $message;
    }

    /**
     * Set the 
     * folder where xml feed will be saved.
     * 
     * @param string $folder
     */
    public function set_folder($folder) {
        $this->_folder = $folder;

        return $this;
    }

    /**
     * Set the filename of the xml file.
     * 
     * @param string $filename
     */
    public function set_fileName($filename) {
        if (substr($filename, -4) !== '.xml') {
            $filename .= '.xml';
        }

        $this->_filename = $filename;

        return $this;
    }
    
    /**
     * A method used to add an <urlset> object
     * to the xml sitemap.
     * 
     * @param array $data
     * @param Object $root
     * @param string $base_loc
     * @param string|array $prop
     * @param string $change_frequency
     * @param string $url_priority
     */
    private function _add_urlset(array $data = array(), $root, $base_loc, $prop, $change_frequency = 'weekly', $url_priority = '0.8') {
        foreach ($data as $item) {
            $url = $root->appendChild($this->_xml->createElement('url'));

            if (!empty($prop)) {
                if (is_array($prop)) {
                    $accumulator = array();

                    foreach ($prop as $key => $value) {
                        array_push($accumulator, $item[$prop[$key]]);
                    }

                    $path = implode('/', $accumulator);
                } else {
                    $path = $item[$prop];
                }
            }

            $loc = $url->appendChild($this->_xml->createElement('loc', $base_loc . '/' . $path));
            $changefreq = $url->appendChild($this->_xml->createElement('changefreq', $change_frequency));
            $priority = $url->appendChild($this->_xml->createElement('priority', $url_priority));
        }
    }
    
    /**
     * A method that
     * used to create an xml sitemap.
     * 
     * @param array $artists_data
     * @param array $authors_data
     * @param array $charts_data
     * @param array $podcasts_data
     * @param array $posts_data
     * @param array $programs_data
     * @param array $songs_data
     * @return Object
     */
    public function generate(
            $artists_data = array(), 
            $authors_data = array(), 
            $charts_data = array(), 
            $podcasts_data = array(), 
            $posts_data = array(), 
            $programs_data = array(), 
            $songs_data = array()
    ) {
        $this->_xml = new \DOMDocument('1.0', 'UTF-8');
        $this->_xml->preserveWhiteSpace = false;
        $this->_xml->formatOutput = true;

        $root = $this->_xml->appendChild($this->_xml->createElement('urlset'));
        $root->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        if (!empty($artists_data)) {
            $base_loc = $this->_base_uri . '/artists';
            $this->_add_urlset($artists_data, $root, $base_loc, 'artist_slug', 'weekly', '0.5');
        }
        
        if (!empty($authors_data)) {
            $base_loc = $this->_base_uri . '/authors';
            $this->_add_urlset($authors_data, $root, $base_loc, 'slug', 'weekly', '0.5');
        }
      
        if (!empty($charts_data)) {
            $base_loc = $this->_base_uri . '/charts';
            $this->_add_urlset($charts_data, $root, $base_loc, 'chart_archive_slug', 'weekly', '0.7');
        }

        if (!empty($podcasts_data)) {
            $base_loc = $this->_base_uri . '/podcasts';
            $this->_add_urlset($podcasts_data, $root, $base_loc, array('program_slug', 'id', 'genre'), 'weekly', '0.8');
        }
        
        if (!empty($posts_data)) {
            $base_loc = $this->_base_uri . '/news';
            $this->_add_urlset($posts_data, $root, $base_loc, array('slug'), 'daily', '1');
        }

        if (!empty($programs_data)) {
            $base_loc = $this->_base_uri . '/programs';
            $this->_add_urlset($programs_data, $root, $base_loc, array('slug'), 'weekly', '0.7');
        }
        
        if (!empty($songs_data)) {
            $base_loc = $this->_base_uri . '/songs';
            $this->_add_urlset($songs_data, $root, $base_loc, array('song_slug'), 'weekly', '0.7');
        }

       //Save the XML to the specified folder.
        $result = $this->_xml->save($this->_folder . $this->_filename);

        if (!$result) {
            $this->_message->set_code(500)
                    ->set_status('error')
                    ->set_message('Error during XML generation');

            return $this->_message;
        }

        $this->_message->set_code(200)
                ->set_status('ok')
                ->set_filename($this->_filename)
                ->set_folder($this->_folder)
                ->set_path($this->_folder . $this->_filename)
                ->set_message($this->_folder . $this->_filename . ' was succesfull generated on ' . date('d-m-Y, H:i:s'));

        return $this->_message;
    }

}
