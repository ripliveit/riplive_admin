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
     * A method that
     * used to create an xml sitemap.
     * 
     * @param array $data
     * @return Object
     */
    public function generate($data = array()) {
        $this->_xml = new \DOMDocument('1.0', 'UTF-8');
        $this->_xml->preserveWhiteSpace = false;
        $this->_xml->formatOutput = true;

        $root = $this->_xml->appendChild($this->_xml->createElement('urlset'));
        $root->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        if (!empty($data)) {            
            foreach ($data as $item) {
                $url = $root->appendChild($this->_xml->createElement('url'));
                $loc = $url->appendChild($this->_xml->createElement('loc', $this->_base_uri . $item['path']));
                $changefreq = $url->appendChild($this->_xml->createElement('changefreq', $item['frequency']));
                $priority = $url->appendChild($this->_xml->createElement('priority', $item['priority']));
            }
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
