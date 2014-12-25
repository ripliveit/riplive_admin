<?php
namespace Rip_General\Classes;

/**
 * Abstract plugin class.
 * Set methods, implemented by concrete classes, that manage
 * the creation of plugin custom post type, custom taxonomies,
 * sidebar, widget, shortcodes and pages.
 * All of these elements can be configured with a relative associative array.
 * 
 * @author Gabriele D'Arrigo - @acirdesign
 */
abstract class Rip_Abstract_Plugin {

    /**
     * Define a list of pages that plugin
     * will use.
     * 
     * @var type 
     */
    protected $_pages = array();

    /**
     * Template parts used by the plugins.
     * 
     * @var type 
     */
    protected $_templates = array();

    /**
     * Define sidebars configuration.
     * 
     * @var type 
     */
    protected $_sidebars;

    /**
     * Hols all widgets.
     * @var type 
     */
    protected $_widgets;

    /**
     * Holds taxonomy configuration.
     * @var type 
     */
    protected $_taxonomies = array();

    /**
     * Holds post typeconfiguration.
     * 
     * @var type 
     */
    protected $_post_types = array();

    /**
     * Prefix of all current plugin metabozes.
     * 
     * @var type 
     */
    protected $_metabox_prefix;

    /**
     * Hold all metabox configuration.
     * 
     * @var type 
     */
    protected $_metaboxes;

    /**
     * Holds shortcode's array configuration.
     * 
     * @var type 
     */
    protected $_shortcodes = array();

    /**
     * The theme route folder, the destination of the copied plugin templates
     * 
     * @var string
     */
    protected $_theme_root;

    /**
     * The templates folder to use to store file to copy into the theme folder 
     * 
     * @var string
     */
    protected $_template_folder;

    /**
     * The asset folder, containing all static asset necessary to the plugin.
     * 
     * @var type 
     */
    protected $_assets_folder;

    /**
     * Holds all assets.
     * 
     * @var type 
     */
    protected $_assets;

    /**
     * Holds all admin pages.
     * 
     * @var type 
     */
    protected $_menu_pages = array();

    /**
     * Admin sub pages.
     * 
     * @var type 
     */
    protected $_sub_menu_pages = array();

    /**
     * Plugin Sql Table.
     * 
     * @var type 
     */
    protected $_tables = array();

    /**
     * Holds SQL dump.
     * @var type 
     */
    protected $_dump = array();

    /**
     * Holds all ajax methods.
     * 
     * @var ajax 
     */
    protected $_ajax = array();

    /**
     * Holds all filters that must be added.
     * @var type 
     */
    protected $_filters_to_add = array();

    /**
     * Holds all filters that must be removed.
     * @var type 
     */
    protected $_filters_to_remove = array();

    /**
     * Define the contract that client classes must respect.
     */
    abstract protected function _init();

    /**
     * On construct activate all custom post type and custom metabox.
     */
    public function __construct() {
        $this->_init();

        $this->_set_ajax();

        if (!empty($this->_sidebars)) {
            add_action('widgets_init', array($this, 'register_sidebars'));
        }

        if (!empty($this->_widgets)) {
            add_action('widgets_init', array($this, 'register_widgets'));
        }

        if (!empty($this->_post_types)) {
            add_action('init', array($this, 'register_post_types'));
        }

        if (!empty($this->_taxonomies)) {
            add_action('init', array($this, 'register_taxonomies'));

            add_action('restrict_manage_posts', array($this, 'register_taxonomies_filter'));

            add_filter('parse_query', array($this, 'perform_taxonomies_filtering'));
        }

        if (!empty($this->_metaboxes)) {
            add_action('admin_menu', array($this, 'add_metaboxes'));
            add_action('save_post', array($this, 'save_metaboxes_data'));
        }

        if (!empty($this->_shortcodes)) {
            add_action('init', array($this, 'register_shortcodes'));
        }

        if (!empty($this->_menu_pages)) {
            add_action('admin_menu', array($this, 'register_admin_menus'));
        }

        if (!empty($this->_filters_to_add)) {
            add_action('init', array($this, 'add_filters'));
        }

        if (!empty($this->_filters_to_remove)) {
            add_action('init', array($this, 'remove_filters'));
        }
    }

    /**
     * Registers the ajax calls
     */
    protected function _set_ajax() {
        if (!empty($this->_ajax)) {
            foreach ($this->_ajax as $hook => $config) {
                add_action('wp_ajax_' . $hook, array($config['class'], $config['method_name']));
                add_action('wp_ajax_nopriv_' . $hook, array($config['class'], $config['method_name']));
            }
        }
    }

    /**
     * Register all custom post types
     * and set post thumbnail.
     */
    public function register_post_types() {
        if (!empty($this->_post_types)) {
            foreach ($this->_post_types as $key => $post_type) {
                register_post_type($post_type['name'], $post_type['args']);
            }

            add_theme_support('post-thumbnails');
            add_image_size('landscape-medium', 640, 250, true);
            add_image_size('landscape-large', 950, 350, true);
        }
    }

    /**
     * Register all custom taxonomies.
     */
    public function register_taxonomies() {
        if (!empty($this->_taxonomies)) {
            foreach ($this->_taxonomies as $key => $taxonomy) {
                register_taxonomy($taxonomy['taxonomy_name'], $taxonomy['object_type'], $taxonomy['args']);
            }
        }
    }

    /**
     * Add filter list, based on hierarchical custom taxonomy, on custom post type
     * admin page. 
     * 
     * @global type $wp_query
     */
    public function register_taxonomies_filter() {
        if (!empty($this->_taxonomies)) {

            $screen = get_current_screen();

            global $wp_query;

            foreach ($this->_taxonomies as $key => $taxonomy) {
                // Check if current edit screen is the current post type screen,
                // and check if custom taxonomy is hierarchical before proceed to draw
                // the dropdown list.
                if ($screen->post_type == $taxonomy['object_type'][0]) {
                    if ($taxonomy['args']['hierarchical'] == 1) {
                        wp_dropdown_categories(array(
                            'show_option_all' => 'Mostra ' . $taxonomy['args']['label'],
                            'taxonomy' => $taxonomy['taxonomy_name'],
                            'name' => $taxonomy['taxonomy_name'],
                            'orderby' => 'name',
                            'selected' => ( isset($wp_query->query[$taxonomy['taxonomy_name']]) ? $wp_query->query[$taxonomy['taxonomy_name']] : '' ),
                            'hierarchical' => true,
                            'depth' => 3,
                            'show_count' => true,
                            'hide_empty' => true,
                        ));
                    }
                }
            }
        }
    }

    /**
     * Filter custom post type on admin page menu,
     * based on choosed custom taxonomy.
     * 
     * @param type $query
     */
    public function perform_taxonomies_filtering($query) {
        $query_vars = &$query->query_vars;

        if (!empty($this->_taxonomies)) {
            foreach ($this->_taxonomies as $key => $taxonomy) {
                if ($taxonomy['args']['hierarchical'] == 1) {
                    if (isset($query_vars[$taxonomy['taxonomy_name']]) && is_numeric($query_vars[$taxonomy['taxonomy_name']])) {

                        //Set query vars with the slug of filtered taxonomies.
                        $term = get_term_by('id', $query_vars[$taxonomy['taxonomy_name']], $taxonomy['taxonomy_name']);

                        $query_vars[$taxonomy['taxonomy_name']] = $term->slug;
                    }
                }
            }
        }
    }

    /**
     * Add all defined metaboxes.
     */
    public function add_metaboxes() {
        if (!empty($this->_metaboxes)) {
            foreach ($this->_metaboxes as $key => $value) {
                add_meta_box($value['args']['id'], $value['args']['title'], array($this, 'format_metabox'), $value['args']['post_type'], $value['args']['context'], $value['args']['priority']);
            }
        }
    }

    /**
     * Format all desired metaboxes.
     * 
     * @global type $meta_box
     * @global type $post
     */
    public function format_metabox() {
        global $post;

        foreach ($this->_metaboxes as $key => $value) {

            foreach ($value['fields'] as $field) {
                // get current post meta data
                $meta = array(
                    'key' => $field['id'],
                    'value' => get_post_meta($post->ID, $field['id'], true)
                );

                $class_name = $this->_metabox_prefix . $field['type'] . '_metabox';

                $factory = new rip_factory_metabox();

                $metabox = $factory->create_metabox($field, $meta, $class_name);

                $metabox->render();
            }
        }
    }

    /**
     * 
     * @global type $meta_box
     * @global type $post
     * @param type $post_id
     * @return type
     */
    public function save_metaboxes_data($post_id) {

        // Check if nonce issett.
        foreach ($this->_post_types as $key => $post_type) {

            $name = $post_type['name'] . '-hidden';
            $nonce = $name . '-nonce';

            if (!isset($_POST[$name]) || !wp_verify_nonce($_POST[$name], $nonce)) {
                return $post_id;
            }
        }

        //Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        //Check permissions
        if (isset($_POST['post_type']) && $_POST['post_type'] === 'page') {
            if (!current_user_can('edit_page', $post_id)) {
                return $post_id;
            }
        } elseif (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }

        foreach ($this->_metaboxes as $key => $value) {
            foreach ($value['fields'] as $field) {
                $old_meta = get_post_meta($post_id, $field['id'], true);
                $new_meta = $_POST[$field['id']];

                if (isset($new_meta) && $new_meta != $old_meta) {
                    update_post_meta($post_id, $field['id'], $new_meta);
                } elseif ($new_meta == '' && $old_meta) {
                    delete_post_meta($post_id, $field['id'], $old_meta);
                }
            }
        }
    }

    /**
     * On activation do the following
     */
    public function activate() {
        $this->_load_templates();
        $this->_load_pages();
        $this->_load_assets();
    }

    /**
     * Function invoked on plugin deactivation.
     * All template and pages that were activated are now deleted.
     */
    public function deactivate() {
        $this->_unload_pages();
        $this->_unload_templates();
        $this->_unload_assets();
    }

    /**
     * Loads templates files: $this->_templates
     */
    protected function _load_templates() {
        if (!empty($this->_templates)) {
            foreach ($this->_templates as $template) {
                $this->_load_template($template);
            }
        }
    }

    /**
     * Copies a file fron plugin template directory into the wp theme directory
     * 
     * @param string $template the name of the file to load
     */
    protected function _load_template($template) {
        $source = $this->_template_folder . '/' . $template;
        $destination = get_template_directory() . '/' . $template;
        @copy($source, $destination);
    }

    /**
     * Delete all templates from wp root directory.
     * 
     */
    protected function _unload_templates() {
        if (!empty($this->_templates)) {
            foreach ($this->_templates as $template) {
                $this->_unload_template($template);
            }
        }
    }

    /**
     * Delete a template copied into the wp root directory.
     * 
     * @param type $template
     */
    protected function _unload_template($template) {
        @unlink(get_template_directory() . '/' . $template);
    }

    /**
     * Loads the pages: $this->_pages
     */
    protected function _load_pages() {
        if (!empty($this->_pages)) {
            foreach ($this->_pages as $title => $page_params) {
                $page_params['title'] = $title;
                $this->_load_page($page_params);
            }
        }
    }

    /**
     * Inserts the pages into the wp databases and loads
     * the related templates files into the wp theme
     *       
     * @param array $params
     */
    protected function _load_page(array $params) {

        $page = get_page_by_title($params['title']);

        $new_page_params = array(
            'post_type' => 'page',
            'post_title' => $params['title'],
            'post_content' => '',
            'post_status' => 'publish',
            'post_author' => 1,
        );

        $this->_load_template($params['template']);

        if (!$page->ID) {

            $page = wp_insert_post($new_page_params);

            update_post_meta($page, '_wp_page_template', $params['template']);
        }
    }

    /**
     * Cycle through @array _pages to unload each
     * page created by the plugin.
     */
    protected function _unload_pages() {
        foreach ($this->_pages as $title => $page_params) {
            $this->_unload_page($title);
        }
    }

    /**
     * If a page with the same title passed as parameter was
     * previously created than that page is erased.
     * 
     * @param array $params
     */
    protected function _unload_page($title) {
        $page = get_page_by_title($title);

        if ($page->ID) {
            wp_delete_post($page->ID, true);
        }
    }

    /**
     * Load all asset useful to the plugin.
     */
    protected function _load_assets() {
        if (!empty($this->_assets)) {
            foreach ($this->_assets as $type => $asset) {
                $this->_load_asset($type, $asset);
            }
        }
    }

    /**
     * Load a single asset into the wordpress root theme.
     * Asset type can be js, css, img etc etc.
     * Asset is the name of the asset.
     * 
     * @param type $type
     * @param type $asset
     */
    protected function _load_asset($type, $asset) {
        $source = $this->_assets_folder . '/' . $type . '/' . $asset;
        $destination = get_template_directory() . '/' . $type . '/' . $asset;
        @copy($source, $destination);
    }

    /**
     * Unload all asset moved to wordpress root folder during activation.
     */
    protected function _unload_assets() {
        if (!empty($this->_assets)) {
            foreach ($this->_assets as $type => $asset) {
                $this->_unload_asset($type, $asset);
            }
        }
    }

    /**
     * Load a single asset into the wordpress root theme.
     * Asset type can be js, css, img etc etc.
     * Asset is the name of the asset.
     * 
     * @param type $type
     * @param type $asset
     */
    protected function _unload_asset($type, $asset) {
        @unlink(get_template_directory() . '/' . $type . '/' . $asset);
    }

    /**
     * Registers the widgets 
     */
    public function register_widgets() {
        if (!empty($this->_widgets)) {
            foreach ($this->_widgets as $class_name) {
                register_widget($class_name);
            }
        }
    }

    /**
     * Registers all sidebars.
     */
    public function register_sidebars() {
        if (!empty($this->_sidebars)) {
            foreach ($this->_sidebars as $config_array) {
                register_sidebars(1, $config_array);
            }
        }
    }

    /**
     * Registers all shortcodes.
     */
    public function register_shortcodes() {
        foreach ($this->_shortcodes as $tag => $class_name) {
            add_shortcode($tag, array(new $class_name, 'init'));
        }
    }

    /**
     * Registers the admin menus and submenus.
     */
    public function register_admin_menus() {
        if (!empty($this->_menu_pages)) {

            foreach ($this->_menu_pages as $admin_page) {
                add_menu_page(
                        $admin_page['page_title'], $admin_page['menu_title'], $admin_page['capability'], $admin_page['menu_slug'], $admin_page['function']
                );
            }

            if (!empty($this->_sub_menu_pages)) {
                foreach ($this->_sub_menu_pages as $sub_page) {
                    add_submenu_page(
                            $sub_page['parent_slug'], $sub_page['page_title'], $sub_page['menu_title'], $sub_page['capability'], $sub_page['menu_slug'], $sub_page['function']
                    );
                }
            }
        }
    }

    /**
     * Add all registered filters
     */
    public function add_filters() {
        if (!empty($this->_filters_to_add)) {
            foreach ($this->_filters_to_add as $key => $filter) {
                add_filter($filter['tag'], array($filter['class'], $filter['function']));
            }
        }
    }

    /**
     * Remove all registered filters
     */
    public function remove_filters() {
        if (!empty($this->_filters_to_remove)) {
            foreach ($this->_filters_to_remove as $key => $filter) {
                remove_filter($filter['tag'], $filter['function']);
            }
        }
    }

    /**
     * Load all plugin SQL table.
     * 
     * @global type $wpdb
     */
    public function load_tables() {
        global $wpdb;

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        if (!empty($this->_tables)) {
            foreach ($this->_tables as $key => $table) {
                $table_name = $wpdb->prefix . $table['name'];

                $sql = str_replace('{NAME}', $table_name, $table['sql']);

                dbDelta($sql);
            }

            $this->load_dump();
        }
    }

    /**
     * Load all associated data into plugin SQL table.
     * 
     * @global type $wpdb
     */
    public function load_dump() {
        global $wpdb;

        if (!empty($this->_dump)) {
            if ($file = file_get_contents($this->_dump)) {
                foreach (explode(";", $file) as $query) {
                    $query = trim($query);

                    if (!empty($query) && $query !== ";") {
                        @$wpdb->query($query);
                    }
                }
            }
        }
    }

}