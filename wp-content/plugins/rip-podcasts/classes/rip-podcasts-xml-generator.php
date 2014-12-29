<?php

namespace Rip_Podcasts\Classes;

/**
 * Generate the xml podcasts feed.
 */
class Rip_Podcasts_Xml_Generator {

    /**
     * Holds a reference to an object used to perform
     * string filtering.
     * 
     * @see rip_general_output_filter
     * @var object 
     */
    protected $_filter;

    /**
     * Default folder value.
     * 
     * @var string 
     */
    protected $_folder = '.';

    /**
     * Default filename value.
     * 
     * @var string 
     */
    protected $_filename = 'feed.xml';

    /**
     * Class constructor.
     * Accept a class the perform string filtering as a dependency.
     * 
     * @param object $filter
     */
    public function __construct($filter = null) {
        $this->_filter = $filter;
    }

    /**
     * Folder where xml feed must be saved.
     * 
     * @param string $folder
     */
    public function set_folder($folder) {
        $this->_folder = $folder;
    }

    /**
     * File name.
     * 
     * @param string $filename
     */
    public function set_fileName($filename) {
        if (substr($filename, -4) !== '.xml') {
            $filename .= '.xml';
        }

        $this->_filename = $filename;
    }

    /**
     * Return a string representing all authors of the podcast.
     * 
     * @param array $authors
     * @return string
     */
    protected function _set_authors_data(array $authors = array()) {
        $out = array();

        if (is_array($authors)) {
            foreach ($authors as $author) {
                array_push($out, $author['first_name'] . ' ' . $author['last_name']);
            }
        }

        return implode(', ', $out);
    }

    /**
     * Generate the XML feed with DOM Document Class.
     * 
     * @param array $channel_data
     * @param array $items_data
     * @return boolean
     * @throws Error
     */
    public function generate($channel_data = array(), $items_data = array()) {
        if (!is_array($channel_data) || empty($channel_data)) {
            return array(
                'status' => 'error',
                'message' => 'Programs data are missing. Cannot generate the XML',
            );
        }

        if (!is_array($items_data) || empty($items_data)) {
            return array(
                'status' => 'error',
                'message' => 'Podcast data are missing. Probably there are no uploaded podcast. Cannot generate the XML'
            );
        }

        $filter = new \Rip_General\Filters\Rip_Output_Filter();

        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;

        $root = $xml->appendChild($xml->createElement('rss'));
        $root->setAttribute('xmlns:itunes', 'http://www.itunes.com/dtds/podcast-1.0.dtd');
        $root->setAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
        $root->setAttribute('version', '2.0');

        $channel = $root->appendChild($xml->createElement('channel'));

        $atom = $channel->appendChild($xml->createElement('atom:link'));
        $atom->setAttribute('href', 'http://s3-eu-west-1.amazonaws.com/riplive.it-podcast/' . $channel_data['slug'] . '/' . $channel_data['slug'] . '.xml');
        $atom->setAttribute('rel', 'self');
        $atom->setAttribute('type', 'application/rss+xml');

        $title = $channel->appendChild($xml->createElement('title'));
        $title->appendChild($xml->createCDATASection($channel_data['program_title']));

        $channel->appendChild($xml->createElement('link', 'http://www.riplive.it/podcast'));
        $channel->appendChild($xml->createElement('language', 'it-IT'));
        $channel->appendChild($xml->createElement('copyright', date('d-m-Y') . ', Riplive.it'));
        $channel->appendChild($xml->createElement('itunes:subtitle', 'I podcast di Riplive.it - Radio Illusioni Parallele'));

        $author = $channel->appendChild($xml->createElement('itunes:author'));
        $author->appendChild($xml->createCDATASection('Riplive.it - ' . $filter::strip_content($channel_data['program_title'])));

        $summary = $channel->appendChild($xml->createElement('itunes:summary'));
        $summary->appendChild($xml->createCDATASection('Radio Illusioni Parallele è una webradio dell\'hinterland milanese, su Riplive.it puoi trovare musica di ogni genere e un sacco di programmi. Cerca il nostro podcast nell\'iTunes Store'));

        $description = $channel->appendChild($xml->createElement('description'));
        $description->appendChild($xml->createCDATASection($filter::strip_content($channel_data['program_content'])));

        $owner = $channel->appendChild($xml->createElement('itunes:owner'));
        $owner->appendChild($xml->createElement('itunes:name', 'Riplive.it'));
        $owner->appendChild($xml->createElement('itunes:email', 'redazione@riplive.it'));

        $image = $channel->appendChild($xml->createElement('itunes:image'));
        $image->setAttribute('href', $channel_data['program_images']['image_full']);

        $explicit = $channel->appendChild($xml->createElement('itunes:explicit', 'No'));

        $category = $channel->appendChild($xml->createElement('itunes:category'));
        $category->setAttribute('text', 'Music');

        foreach ($items_data as $item_data) {
            $item = $channel->appendChild($xml->createElement('item'));

            $item_title = $item->appendChild($xml->createElement('title'));
            $item_title->appendChild($xml->createCDATASection($filter::strip_content($item_data['title'])));

            $item_authors = $item->appendChild($xml->createElement('itunes:author'));
            $item_authors->appendChild($xml->createCDATASection($filter::strip_content($this->_set_authors_data($item_data['authors']))));

            $item_subtitle = $item->appendChild($xml->createElement('itunes:subtitle'));
            $item_subtitle->appendChild($xml->createCDATASection(substr($filter::strip_content($item_data['program_content']), 0, 255)));

            $item_summary = $item->appendChild($xml->createElement('itunes:summary'));
            $item_summary->appendChild($xml->createCDATASection($filter::strip_content($item_data['summary'])));

            $item_image = $item->appendChild($xml->createElement('itunes:image'));
            $item_image->setAttribute('href', $item_data['podcast_images']['image_large']);

            $item_enclosure = $item->appendChild($xml->createElement('enclosure'));
            $item_enclosure->setAttribute('url', $item_data['url']);
            $item_enclosure->setAttribute('length', $item_data['file_length']);
            $item_enclosure->setAttribute('type', 'audio/mpeg');

            $item->appendChild($xml->createElement('link', 'http://www.riplive.it/podcasts/' . $item_data['program_slug'] . '/' . $item_data['id']));
            $item->appendChild($xml->createElement('guid', 'http://www.riplive.it/podcasts/' . $item_data['program_slug'] . '/' . $item_data['id']));
            $item->appendChild($xml->createElement('pubDate', date('r', strtotime($item_data['date']))));
            $item->appendChild($xml->createElement('itunes:duration', $filter::strip_content($item_data['duration'])));
        }

        //Save the XML to the specified folder.
        $result = $xml->save($this->_folder . $this->_filename);

        if ($result) {
            return array(
                'status' => 'ok',
                'filename' => $this->_filename,
                'folder' => $this->_folder,
                'path' => $this->_folder . $this->_filename,
                'message' => $this->_folder . $this->_filename . ' was succesfull generated on ' . date('d-m-Y, H:i:s'),
            );
        } else {
            return false;
        }
    }

}
