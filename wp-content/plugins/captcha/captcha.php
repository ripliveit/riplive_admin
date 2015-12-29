<?php
/*
Plugin Name: Captcha by BestWebSoft
Plugin URI: http://bestwebsoft.com/products/
Description: Plugin Captcha intended to prove that the visitor is a human being and not a spam robot. Plugin asks the visitor to answer a math question.
Author: BestWebSoft
Text Domain: captcha
Domain Path: /languages
Version: 4.1.7
Author URI: http://bestwebsoft.com/
License: GPLv2 or later
*/

/*  © Copyright 2015  BestWebSoft  ( http://support.bestwebsoft.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! function_exists( 'cptch_admin_menu' ) ) {
	function cptch_admin_menu() {
		bws_general_menu();
		$settings_page = add_submenu_page( 'bws_plugins', __( 'Captcha Settings', 'captcha' ), __( 'Captcha', 'captcha' ), 'manage_options', "captcha.php", 'cptch_settings_page' );
		add_action( "load-{$settings_page}", 'cptch_add_tabs' );
	}
}

if ( ! function_exists( 'cptch_plugins_loaded' ) ) {
	function cptch_plugins_loaded() {
		/* Internationalization */
		load_plugin_textdomain( 'captcha', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
	}
}

if ( ! function_exists ( 'cptch_init' ) ) {
	function cptch_init() {
		global $cptch_plugin_info;
		
		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );

		if ( ! $cptch_plugin_info ) {
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$cptch_plugin_info = get_plugin_data( __FILE__ );
		}

		/* Function check if plugin is compatible with current WP version */
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $cptch_plugin_info, '3.8', '3.1' );

		/* Call register settings function */
		if ( ! is_admin() || ( isset( $_GET['page'] ) && "captcha.php" == $_GET['page'] ) )
			cptch_settings();

	}
}

if ( ! function_exists ( 'cptch_admin_init' ) ) {
	function cptch_admin_init() {
		global $bws_plugin_info, $cptch_plugin_info;		
		/* Add variable for bws_menu */
		if ( ! isset( $bws_plugin_info ) || empty( $bws_plugin_info ) )
			$bws_plugin_info = array( 'id' => '75', 'version' => $cptch_plugin_info["Version"] );
	}
}

if ( ! function_exists( 'cptch_create_table' ) ) {
	function cptch_create_table() {
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "cptch_whitelist` (
			`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`ip` CHAR(31) NOT NULL,
			`ip_from_int` BIGINT,
			`ip_to_int` BIGINT,
			`add_time` DATETIME,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		dbDelta( $sql );

		$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->base_prefix . "cptch_images` (
			`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`name` CHAR(100) NOT NULL,
			`package_id` INT NOT NULL,
			`number` INT NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		dbDelta( $sql );

		$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->base_prefix . "cptch_packages` (
			`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`name` CHAR(100) NOT NULL,
			`folder` CHAR(100) NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		dbDelta( $sql );

		/* remove unnecessary columns from 'whitelist' table */
		$column_exists = $wpdb->query( "SHOW COLUMNS FROM `" . $wpdb->prefix . "cptch_whitelist` LIKE 'ip_from'" );
		if ( 0 < $column_exists )
			$wpdb->query( "ALTER TABLE `" . $wpdb->prefix . "cptch_whitelist` DROP `ip_from`;" );
		$column_exists = $wpdb->query( "SHOW COLUMNS FROM `" . $wpdb->prefix . "cptch_whitelist` LIKE 'ip_to'" );
		if ( 0 < $column_exists )
			$wpdb->query( "ALTER TABLE `" . $wpdb->prefix . "cptch_whitelist` DROP `ip_to`;" );
		/* add new columns to 'whitelist' table */
		$column_exists = $wpdb->query( "SHOW COLUMNS FROM `" . $wpdb->prefix . "cptch_whitelist` LIKE 'add_time'" );
		if ( 0 == $column_exists )
			$wpdb->query( "ALTER TABLE `" . $wpdb->prefix . "cptch_whitelist` ADD `add_time` DATETIME;" );
		/* add unique key */
		if ( 0 == $wpdb->query( "SHOW KEYS FROM `" . $wpdb->prefix . "cptch_whitelist` WHERE Key_name='ip'" ) )
			$wpdb->query( "ALTER TABLE `" . $wpdb->prefix  . "cptch_whitelist` ADD UNIQUE(`ip`);" );
		/* remove not necessary indexes */
		$indexes = $wpdb->get_results( "SHOW INDEX FROM `" . $wpdb->prefix . "cptch_whitelist` WHERE Key_name Like '%ip_%'" );
		if ( ! empty( $indexes ) ) {
			$query = "ALTER TABLE `" . $wpdb->prefix . "cptch_whitelist`";
			$drop = array();
			foreach( $indexes as $index )
				$drop[] = " DROP INDEX " . $index->Key_name;
			$query .= implode( ',', $drop );
			$wpdb->query( $query );
		}
	}
}

/**
 * Activation plugin function
 */
if ( ! function_exists( 'cptch_plugin_activate' ) ) {
	function cptch_plugin_activate( $networkwide ) {
		global $wpdb;
		/* Activation function for network, check if it is a network activation - if so, run the activation function for each blog id */
		if ( function_exists( 'is_multisite' ) && is_multisite() && $networkwide ) {
			$old_blog = $wpdb->blogid;
			/* Get all blog ids */
			$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );
				cptch_create_table();
				cptch_settings();
			}
			switch_to_blog( $old_blog );
			return;
		}
		cptch_create_table();
		cptch_settings();
		if ( ! class_exists( 'Cptch_package_loader' ) ) 
			require_once( dirname( __FILE__ ) . '/includes/package_loader.php' );
		$package_loader = new Cptch_package_loader();
		$package_loader->parse_packages( dirname( __FILE__ ) . '/images/package' );
	}
}

/* Register settings function */
if ( ! function_exists( 'cptch_settings' ) ) {
	function cptch_settings() {
		global $cptch_options, $cptch_plugin_info, $cptch_option_defaults, $wpdb;
		$db_version = '1.2';

		$cptch_option_defaults = array(
			'plugin_option_version'			=> $cptch_plugin_info["Version"],
			'plugin_db_version'				=> $db_version,
			'cptch_str_key'					=>	array( 'time' => '', 'key' => '' ),
			'cptch_login_form'				=>	'1',
			'cptch_register_form'			=>	'1',
			'cptch_lost_password_form'		=>	'1',
			'cptch_comments_form'			=>	'1',
			'cptch_hide_register'			=>	'1',
			'cptch_contact_form'			=>	'0',
			'cptch_math_action_plus'		=>	'1',
			'cptch_math_action_minus'		=>	'1',
			'cptch_math_action_increase'	=>	'1',
			'cptch_label_form'				=>	'',
			'cptch_required_symbol'			=>	'*',
			'cptch_error_empty_value'		=>	__( 'Please enter a CAPTCHA value.', 'captcha' ),
			'cptch_error_incorrect_value'	=>	__( 'Please enter a valid CAPTCHA value.', 'captcha' ),
			'cptch_error_time_limit'		=>	__( 'Time limit is exhausted. Please enter CAPTCHA value again.', 'captcha' ),
			'whitelist_message'				=>	__( 'You are in the white list', 'captcha' ),
			'cptch_difficulty_number'		=>	'1',
			'cptch_difficulty_word'			=>	'1',
			'cptch_difficulty_image'		=>	'0',
			'first_install'					=>	strtotime( "now" ),
			'display_settings_notice'		=> 1,
			'display_notice_about_images'	=> 1,
			'use_limit_attempts_whitelist'	=> 0,
			'display_reload_button'			=> 1,
			'used_packages'					=> array(),
			'enlarge_images'				=> 0,
			'whitelist_is_empty'			=> true,
			'use_time_limit'				=> 1,
			'time_limit'					=> 120
		);

		/* Install the option defaults */
		if ( ! get_option( 'cptch_options' ) )
			add_option( 'cptch_options', $cptch_option_defaults );

		/* Get options from the database */
		$cptch_options = get_option( 'cptch_options' );

		/* Array merge incase this version has added new options */
		if ( ! isset( $cptch_options['plugin_option_version'] ) || $cptch_options['plugin_option_version'] != $cptch_plugin_info["Version"] ) {
			$cptch_option_defaults['display_settings_notice'] = 0;
			$cptch_option_defaults['display_notice_about_images'] = 0;
			if ( ! isset( $cptch_option_defaults['cptch_difficulty_image'] ) )
				$cptch_option_defaults['cptch_difficulty_image'] = 0;
			$cptch_options = array_merge( $cptch_option_defaults, $cptch_options );
			$cptch_options['hide_premium_options']  = 0;
			$cptch_options['plugin_option_version'] = $cptch_plugin_info["Version"];
			$update_option = true;
		}	
		/* Update tables when update plugin and tables changes*/
		if ( ! isset( $cptch_options['plugin_db_version'] ) || $cptch_options['plugin_db_version'] != $db_version ) {
			cptch_create_table();
			if ( ! class_exists( 'Cptch_package_loader' ) ) 
				require_once( dirname( __FILE__ ) . '/includes/package_loader.php' );
			$package_loader = new Cptch_package_loader();
			$package_loader->parse_packages( dirname( __FILE__ ) . '/images/package' );
			if( ! is_null( $wpdb->get_var( "SELECT `id` FROM `{$wpdb->prefix}cptch_whitelist` LIMIT 1" ) ) )
				$cptch_options['whitelist_is_empty'] = false;
			/* update DB version */
			$cptch_options['plugin_db_version'] = $db_version;
			$update_option = true;
		}
		if ( isset( $update_option ) )
			update_option( 'cptch_options', $cptch_options );
	}
}

/* Generate key */
if ( ! function_exists( 'cptch_generate_key' ) ) {
	function cptch_generate_key( $lenght = 15 ) {
		global $cptch_options;
		/* Under the string $simbols you write all the characters you want to be used to randomly generate the code. */
		$simbols = get_bloginfo( "url" ) . time();
		$simbols_lenght = strlen( $simbols );
		$simbols_lenght--;
		$str_key = NULL;
		for ( $x = 1; $x <= $lenght; $x++ ) {
			$position = rand( 0, $simbols_lenght );
			$str_key .= substr( $simbols, $position, 1 );
		}

		$cptch_options['cptch_str_key']['key']	=	md5( $str_key );
		$cptch_options['cptch_str_key']['time']	=	time();
		update_option( 'cptch_options', $cptch_options );
	}
}

if ( ! function_exists( 'cptch_whitelisted_ip' ) ) {
	function cptch_whitelisted_ip() {
		global $cptch_options, $wpdb;
		$checked = false;
		if ( empty( $cptch_options ) )
			$cptch_options = get_option( 'cptch_options' );
		$table = 1 == $cptch_options['use_limit_attempts_whitelist'] ? 'lmtttmpts_whitelist' : 'cptch_whitelist';
		if ( 'lmtttmpts_whitelist' == $table || ( 'cptch_whitelist' == $table && ! $cptch_options['whitelist_is_empty'] ) ) {
			$whitelist_exist = $wpdb->query( "SHOW TABLES LIKE '{$wpdb->prefix}{$table}'" );
			
			if ( ! empty( $whitelist_exist ) ) {
				$ip = '';
				if ( isset( $_SERVER ) ) {
					$sever_vars = array( 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
					foreach( $sever_vars as $var ) {
						if ( isset( $_SERVER[ $var ] ) && ! empty( $_SERVER[ $var ] ) ) {
							if ( filter_var( $_SERVER[ $var ], FILTER_VALIDATE_IP ) ) {
								$ip = $_SERVER[ $var ];
								break;
							} else { /* if proxy */
								$ip_array = explode( ',', $_SERVER[ $var ] );
								if ( is_array( $ip_array ) && ! empty( $ip_array ) && filter_var( $ip_array[0], FILTER_VALIDATE_IP ) ) {
									$ip = $ip_array[0];
									break;
								}
							}
						}
					}
				}

				if ( ! empty( $ip ) ) {
					$ip_int = sprintf( '%u', ip2long( $ip ) );
					$result = $wpdb->get_var( 
						"SELECT `id` 
						FROM `{$wpdb->prefix}{$table}` 
						WHERE ( `ip_from_int` <= {$ip_int} AND `ip_to_int` >= {$ip_int} ) OR `ip` LIKE '{$ip}' LIMIT 1;" 
					);
					$checked = is_null( $result ) || ! $result ? false : true;
				}
			}
		}
		return $checked;
	}
}

/* Add global setting for Captcha */
global $cptch_time, $cptch_ip_in_whitelist, $cptch_options;
$cptch_options = get_option( 'cptch_options' );
$cptch_time = time();
if ( empty( $cptch_options ) ) {
	cptch_settings();
	$cptch_options = get_option( 'cptch_options' );
}
$cptch_ip_in_whitelist = is_admin() ? false : cptch_whitelisted_ip();

if ( ! $cptch_ip_in_whitelist || ! empty( $cptch_options['whitelist_message'] ) ) {
	/* Add captcha into login form */
	if ( 1 == $cptch_options['cptch_login_form'] ) {
		add_action( 'login_form', 'cptch_login_form' );
		if ( ! $cptch_ip_in_whitelist )
			add_filter( 'authenticate', 'cptch_login_check', 21, 1 );	
	}
	/* Add captcha into comments form */
	if ( 1 == $cptch_options['cptch_comments_form'] ) {
		global $wp_version;
		if ( version_compare( $wp_version,'3','>=' ) ) { /* wp 3.0 + */
			add_action( 'comment_form_after_fields', 'cptch_comment_form_wp3', 1 );
			add_action( 'comment_form_logged_in_after', 'cptch_comment_form_wp3', 1 );
		}
		/* For WP before WP 3.0 */
		add_action( 'comment_form', 'cptch_comment_form' );
		if ( ! $cptch_ip_in_whitelist )
			add_filter( 'preprocess_comment', 'cptch_comment_post' );	 
	}
	/* Add captcha in the register form */
	if ( 1 == $cptch_options['cptch_register_form'] ) {
		add_action( 'register_form', 'cptch_register_form' );
		add_action( 'signup_extra_fields', 'wpmu_cptch_register_form' );
		if ( ! $cptch_ip_in_whitelist ) {
			add_filter( 'registration_errors', 'cptch_register_check', 10, 1 );
			if ( is_multisite() )
				add_filter( 'wpmu_validate_user_signup', 'cptch_register_validate' );
		}
	}
	/* Add captcha into lost password form */
	if ( 1 == $cptch_options['cptch_lost_password_form'] ) {
		add_action( 'lostpassword_form', 'cptch_lostpassword_form' );
		if ( ! $cptch_ip_in_whitelist )
			add_filter( 'allow_password_reset', 'cptch_lostpassword_check' );
	}
	/* Add captcha to Contact Form */
	if ( 1 == $cptch_options['cptch_contact_form'] ) {
		add_filter( 'cntctfrm_display_captcha', 'cptch_custom_form', 10, 3 );
		add_filter( 'cntctfrmpr_display_captcha', 'cptch_custom_form', 10, 3 );
		if ( ! $cptch_ip_in_whitelist ) {
			add_filter( 'cntctfrm_check_form', 'cptch_check_custom_form' );
			add_filter( 'cntctfrmpr_check_form', 'cptch_check_custom_form' );
		}
	}
}
/* Function for display captcha settings page in the admin area */
if ( ! function_exists( 'cptch_settings_page' ) ) {
	function cptch_settings_page() {
		global $cptch_options, $wp_version, $cptch_plugin_info, $cptch_option_defaults, $wpdb;
		$error = $message = "";
		$plugin_basename  = plugin_basename( __FILE__ );
		
		/* These fields for the 'Enable CAPTCHA on the' block which is located at the admin setting captcha page */
		$cptch_admin_fields_enable = array (
			array( 'cptch_login_form', __( 'Login form', 'captcha' ), 'login_form.jpg' ),
			array( 'cptch_register_form', __( 'Registration form', 'captcha' ), 'register_form.jpg' ),
			array( 'cptch_lost_password_form', __( 'Reset Password form', 'captcha' ), 'lost_password_form.jpg' ),
			array( 'cptch_comments_form', __( 'Comments form', 'captcha' ), 'comment_form.jpg' ),
		);
		$cptch_admin_fields_hide = array(
			array( 'cptch_hide_register', __( 'in Comments form for registered users', 'captcha' ) ),
		);
		/* These fields for the 'Arithmetic actions for CAPTCHA' block which is located at the admin setting captcha page */
		$cptch_admin_fields_actions = array (
			array( 'cptch_math_action_plus', __( 'Plus (&#43;)', 'captcha' ), __( 'Plus', 'captcha' ) ),
			array( 'cptch_math_action_minus', __( 'Minus (&minus;)', 'captcha' ), __( 'Minus', 'captcha' ) ),
			array( 'cptch_math_action_increase', __( 'Multiplication (&times;)', 'captcha' ), __( 'Multiply', 'captcha' ) ),
		);

		/* This fields for the 'Difficulty for CAPTCHA' block which is located at the admin setting captcha page */
		$cptch_admin_fields_difficulty = array (
			array( 'cptch_difficulty_number', __( 'Numbers', 'captcha' ), __( 'Numbers', 'captcha' ) ),
			array( 'cptch_difficulty_word', __( 'Words', 'captcha' ), __( 'Words', 'captcha' ) ),
			array( 'cptch_difficulty_image', __( 'Images', 'captcha' ) )
		);

		if ( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$all_plugins = get_plugins();
		$is_network  = is_multisite() && is_network_admin();
		$admin_url   = $is_network ? network_admin_url( '/' ) : admin_url( '/' );
		$bws_contact_form = cptch_plugin_status( array( 'contact-form-plugin/contact_form.php', 'contact-form-pro/contact_form_pro.php' ), $all_plugins, $is_network );
	
		/* Save data for settings page */
		if ( isset( $_REQUEST['cptch_form_submit'] ) && check_admin_referer( $plugin_basename, 'cptch_nonce_name' ) ) {

			/* hide pro blocks */
			if ( isset( $_POST['bws_hide_premium_options'] ) && check_admin_referer( $plugin_basename, 'cptch_nonce_name' ) ) {
				$hide_result   = bws_hide_premium_options( $cptch_options );
				$cptch_options = $hide_result['options'];
			}
			
			$cptch_options['cptch_login_form']				=	isset( $_REQUEST['cptch_login_form'] ) ? 1 : 0;
			$cptch_options['cptch_register_form']			=	isset( $_REQUEST['cptch_register_form'] ) ? 1 : 0;
			$cptch_options['cptch_lost_password_form']		=	isset( $_REQUEST['cptch_lost_password_form'] ) ? 1 : 0;
			$cptch_options['cptch_comments_form'] 			=	isset( $_REQUEST['cptch_comments_form'] ) ? 1 : 0;
			$cptch_options['cptch_hide_register'] 			=	isset( $_REQUEST['cptch_hide_register'] ) ? 1 : 0;
			$cptch_options['cptch_contact_form'] 			=	isset( $_REQUEST['cptch_contact_form'] ) ? 1 : 0;

			$cptch_options['cptch_label_form'] 				=	isset( $_REQUEST['cptch_label_form'] ) ? stripslashes( esc_html( $_REQUEST['cptch_label_form'] ) ) : '';
			$cptch_options['cptch_required_symbol'] 		=	isset( $_REQUEST['cptch_required_symbol'] ) ? stripslashes( esc_html( $_REQUEST['cptch_required_symbol'] ) ) : '';

			$cptch_options['cptch_error_empty_value']		=	isset( $_REQUEST['cptch_error_empty_value'] ) ? stripslashes( esc_html( $_REQUEST['cptch_error_empty_value'] ) ) : '';
			$cptch_options['cptch_error_incorrect_value']	=	isset( $_REQUEST['cptch_error_incorrect_value'] ) ? stripslashes( esc_html( $_REQUEST['cptch_error_incorrect_value'] ) ) : '';
			$cptch_options['cptch_error_time_limit']		=	isset( $_REQUEST['cptch_error_time_limit'] ) ? stripslashes( esc_html( $_REQUEST['cptch_error_time_limit'] ) ) : '';
			$cptch_options['whitelist_message'] 			= isset( $_REQUEST['cptch_whitelist_message'] ) ? stripslashes( esc_html( $_REQUEST['cptch_whitelist_message'] ) ) : '';

			if ( $cptch_options['cptch_error_empty_value'] == '' )
				$cptch_options['cptch_error_empty_value'] = $cptch_option_defaults['cptch_error_empty_value'];
			if ( $cptch_options['cptch_error_incorrect_value'] == '' )
				$cptch_options['cptch_error_incorrect_value'] = $cptch_option_defaults['cptch_error_incorrect_value'];
			if ( $cptch_options['cptch_error_time_limit'] == '' )
				$cptch_options['cptch_error_time_limit'] = $cptch_option_defaults['cptch_error_time_limit'];

			$cptch_options['cptch_math_action_plus']		=	isset( $_REQUEST['cptch_math_action_plus'] ) ? 1 : 0;
			$cptch_options['cptch_math_action_minus'] 		=	isset( $_REQUEST['cptch_math_action_minus'] ) ? 1 : 0;
			$cptch_options['cptch_math_action_increase']	=	isset( $_REQUEST['cptch_math_action_increase'] ) ? 1 : 0;

			$cptch_options['cptch_difficulty_number']		=	isset( $_REQUEST['cptch_difficulty_number'] ) ? 1 : 0;
			$cptch_options['cptch_difficulty_word'] 		=	isset( $_REQUEST['cptch_difficulty_word'] ) ? 1 : 0;
			$cptch_options['cptch_difficulty_image'] 		=	isset( $_REQUEST['cptch_difficulty_image'] ) ? 1 : 0;

			$cptch_options['used_packages']					=	isset( $_REQUEST['cptch_used_packages'] ) ? $_REQUEST['cptch_used_packages'] : array();
			$cptch_options['display_reload_button']			=	isset( $_REQUEST['cptch_display_reload_button'] ) ? 1 : 0;
			$cptch_options['enlarge_images']				=	isset( $_REQUEST['cptch_enlarge_images'] ) ? 1 : 0;
			$cptch_options['use_time_limit']				=	isset( $_REQUEST['cptch_use_time_limit'] ) ? 1 : 0;
			$cptch_options['time_limit']					= 
					! isset( $_REQUEST['cptch_time_limit'] ) ||
					! is_numeric( $_REQUEST['cptch_time_limit'] ) || 
					10 > $_REQUEST['cptch_time_limit'] 
				? 
					120 
				: 
					$_REQUEST['cptch_time_limit'];
			/* Check select one point in the blocks Arithmetic actions and Difficulty on settings page */
			$arithmetic_actions = isset( $_REQUEST['cptch_math_action_plus'] ) || isset( $_REQUEST['cptch_math_action_minus'] ) || isset( $_REQUEST['cptch_math_action_increase'] ) ? true : false;
			$complexity_level = isset( $_REQUEST['cptch_difficulty_number'] ) || isset( $_REQUEST['cptch_difficulty_word'] ) || isset( $_REQUEST['cptch_difficulty_image'] ) ? true : false;
			$image_optins = ( isset( $_REQUEST['cptch_difficulty_image'] ) && isset( $_REQUEST['cptch_used_packages'] ) ) || ! isset( $_REQUEST['cptch_difficulty_image'] ) ? true : false;
			/* if 'Arithmetic actions'- or 'Complexity level'- options are disabled */
			if ( ! $arithmetic_actions || ! $complexity_level ) {
				$error = __( "Please select one item in the block Arithmetic and Complexity for CAPTCHA", 'captcha' );
			/* if 'Use packages'-options are disabled */
			} elseif( ! $image_optins ) {
				$error = __( "Please select one item in the block Enable image packages", 'captcha' );
			} else {
				if( ! is_null( $wpdb->get_var( "SELECT `id` FROM `{$wpdb->prefix}cptch_whitelist` LIMIT 1" ) ) )
					$cptch_options['whitelist_is_empty'] = false;
				/* Update options in the database */
				update_option( 'cptch_options', $cptch_options );
				$message = __( "Settings saved.", 'captcha' );
			}
		}

		if ( ! class_exists( 'Cptch_package_loader' ) ) 
			require_once( dirname( __FILE__ ) . '/includes/package_loader.php' );
		$package_loader = new Cptch_package_loader();
		$error .= $package_loader->error;

		if ( isset( $_REQUEST['bws_restore_confirm'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
			$cptch_options = $cptch_option_defaults;
			update_option( 'cptch_options', $cptch_options );
			$message =  __( 'All plugin settings were restored.', 'captcha' );
		}

		require_once( dirname( __FILE__ ) . '/includes/pro_banners.php' );

		/* GO PRO */
		if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) {
			$go_pro_result = bws_go_pro_tab_check( $plugin_basename, 'cptch_options' );
			if ( ! empty( $go_pro_result['error'] ) )
				$error = $go_pro_result['error'];
			elseif ( ! empty( $go_pro_result['message'] ) )
				$message = $go_pro_result['message'];
		} 
		/* Display form on the setting page */ ?>
		<div class="wrap">
			<h1><?php _e( 'Captcha Settings', 'captcha' ); ?></h1>
			<ul class="subsubsub cptch_how_to_use">
				<li><a href="https://docs.google.com/document/d/11_TUSAjMjG7hLa53lmyTZ1xox03hNlEA4tRmllFep3I/edit" target="_blank"><?php _e( 'How to Use Step-by-step Instruction', 'captcha' ); ?></a></li>
			</ul>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab<?php if ( ! isset( $_GET['action'] ) ) echo ' nav-tab-active'; ?>" href="admin.php?page=captcha.php"><?php _e( 'Basic', 'captcha' ); ?></a>
				<a class="nav-tab<?php if ( isset( $_GET['action'] ) && 'advanced' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=captcha.php&amp;action=advanced"><?php _e( 'Advanced', 'captcha' ); ?></a>
				<a class="nav-tab <?php if ( isset( $_GET['action'] ) && 'whitelist' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=captcha.php&amp;action=whitelist"><?php _e( 'Whitelist', 'captcha' ); ?></a>
				<a class="nav-tab bws_go_pro_tab<?php if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=captcha.php&amp;action=go_pro"><?php _e( 'Go PRO', 'captcha' ); ?></a>
			</h2>
			<div class="updated fade" <?php if ( '' == $message || "" != $error ) echo "style=\"display:none\""; ?>><p><strong><?php echo $message; ?></strong></p></div>
			<div class="error" <?php if ( "" == $error ) echo "style=\"display:none\""; ?>><p><strong><?php echo $error; ?></strong></p></div>
			<?php if ( ! isset( $_GET['action'] ) || ! in_array( $_GET['action'], array( 'whitelist', 'go_pro' ) ) ) { 
				if ( isset( $_REQUEST['bws_restore_default'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
					bws_form_restore_default_confirm( $plugin_basename );
				} else {
					bws_show_settings_notice(); 
					if ( ! empty( $hide_result['message'] ) ) { ?>
						<div class="updated fade"><p><strong><?php echo $hide_result['message']; ?></strong></p></div>
					<?php } ?>
					<form class="bws_form" method="post" action="admin.php?page=captcha.php<?php if ( isset( $_GET['action'] ) && 'advanced' == $_GET['action'] ) echo '&action=advanced'; ?>">
						<table class="form-table"<?php if ( isset( $_GET['action'] ) ) echo ' style="display: none;"'; ?>>
							<tr valign="top">
								<th scope="row"><?php _e( 'Enable CAPTCHA for', 'captcha' ); ?>:</th>
								<td>
									<fieldset>
										<legend class="screen-reader-text"><span><?php _e( 'Enable CAPTCHA for', 'captcha' ); ?></span></legend>
										<p><i><?php _e( 'WordPress default', 'captcha' ); ?></i></p>
										<?php foreach ( $cptch_admin_fields_enable as $fields ) {
											if ( 
												in_array( $fields[0], array( 'cptch_register_form', 'cptch_lost_password_form' ) ) && 
												! in_array( get_current_blog_id(), array( 0, 1 ) ) 
											) {
												$notice = '<br /><span class="bws_info">' . __( 'This option is available only for main blog', 'captcha' ) . '</span>';
												$disable_reg_form = ' disabled="disabled"';
												$checked = '';
											} else {
												$notice = $disable_reg_form = '';
												$checked = 1 == $cptch_options[ $fields[0] ] ? ' checked="checked"' : '';
											} ?>
											<label><input<?php echo $disable_reg_form . $checked; ?> type="checkbox" name="<?php echo $fields[0]; ?>" value="<?php echo $fields[0]; ?>"/> <?php echo $fields[1]; ?></label>
											<div class="bws_help_box dashicons dashicons-editor-help cptch_thumb_block">
												<div class="bws_hidden_help_text"><img src="<?php echo plugins_url( 'captcha/images') . '/' . $fields[2]; ?>" title="<?php echo $fields[1]; ?>" alt="<?php echo $fields[1]; ?>"/></div>
											</div>
											<?php echo $notice; ?>
											<br />
										<?php } ?>
										<br />
										<p><i><?php _e( 'Plugins', 'captcha' ); ?></i></p>
										<?php if ( 'actived' == $bws_contact_form['status'] ) {
											$disabled_attr = $info = '';
										} elseif ( 'deactivated' == $bws_contact_form['status'] ) { 
											$disabled_attr = "disabled='disabled'";
											$info = 
												'<span class="bws_info">' . 
													__( 'You should', 'captcha' ) . '&nbsp;<a href="' . $admin_url . 'plugins.php">' . __( 'activate', 'captcha' ) . '&nbsp;Contact Form&nbsp;' . ( is_network_admin() ? __( 'for network', 'captcha' ) : '' ) . '</a>' . '&nbsp;' . __( 'to use this functionality', 'captcha' ) . 
												'</span>';
										} elseif ( 'not_installed' == $bws_contact_form['status'] ) { 
											$disabled_attr = "disabled='disabled'";
											$info = 
												'<span class="bws_info">' . 
													__( 'You should', 'captcha' ) . 
													'&nbsp;<a href="http://http://bestwebsoft.com/products/contact-form/?k=9ab9d358ad3a23b8a99a8328595ede2e&pn=72&v=' . $cptch_plugin_info["Version"] . '&wp_v=' . $wp_version .'">' . __( 'download', 'captcha' ) . '&nbsp;Contact Form&nbsp;</a>' . 
													'&nbsp;' . __( 'to use this functionality', 'captcha' ) . 
												'</span>';
										} ?>
										<label><input <?php echo $disabled_attr; ?> type="checkbox" name="cptch_contact_form" value="1" <?php if ( 1 == $cptch_options['cptch_contact_form'] ) echo 'checked="checked"'; ?> /> Contact Form by BestWebSoft</label> 
										<div class="bws_help_box dashicons dashicons-editor-help cptch_thumb_block">
											<div class="bws_hidden_help_text">
												<img src="<?php echo plugins_url( 'captcha/images/contact_form.jpg' ); ?>" title="Contact Form" alt="Contact Form"/>
											</div>
										</div>
										<?php echo $info; ?>
										<br />
										<?php echo apply_filters( 'cptch_forms_list', '' ); ?>
										<span class="bws_info"><?php _e( 'If you would like to add Captcha to a custom form, please see', 'captcha' ); ?> <a href="http://bestwebsoft.com/products/captcha/faq" target="_blank">FAQ</a></span>
									</fieldset>
								</td>
							</tr>
						</table>
						<?php if ( ! isset( $_GET['action'] ) )
							cptch_pro_block( 'cptch_basic_banner' ); ?>
						<table class="form-table"<?php if( isset( $_GET['action'] ) ) echo ' style="display: none;"'; ?>>
							<tr valign="top">
								<th scope="row"><?php _e( 'Hide CAPTCHA', 'captcha' ); ?></th>
								<td><?php foreach ( $cptch_admin_fields_hide as $fields ) { ?>
										<label><input type="checkbox" name="<?php echo $fields[0]; ?>" value="<?php echo $fields[0]; ?>" <?php if ( 1 == $cptch_options[ $fields[0] ] ) echo "checked=\"checked\""; ?> /> <?php echo $fields[1]; ?></label><br />
									<?php } ?>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Title', 'captcha' ); ?></th>
								<td><input class="cptch_settings_input" type="text" name="cptch_label_form" value="<?php echo $cptch_options['cptch_label_form']; ?>" maxlength="100" /></td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( "Required symbol", 'captcha' ); ?></th>
								<td colspan="2">
									<input class="cptch_settings_input" type="text" name="cptch_required_symbol" value="<?php echo $cptch_options['cptch_required_symbol']; ?>" maxlength="100" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Show "Reload" button', 'captcha' ); ?></th>
								<td>
									<input type="checkbox" name="cptch_display_reload_button" value="1" <?php if ( 1 == $cptch_options['display_reload_button'] ) echo 'checked="checked"'; ?> />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Arithmetic actions', 'captcha' ); ?></th>
								<td colspan="2">
									<fieldset>
										<legend class="screen-reader-text"><span><?php _e( 'Arithmetic actions for CAPTCHA', 'captcha' ); ?></span></legend>
										<?php foreach ( $cptch_admin_fields_actions as $actions ) { ?>
											<label><input type="checkbox" name="<?php echo $actions[0]; ?>" value="1" <?php if ( 1 == $cptch_options[$actions[0]] ) echo "checked=\"checked\""; ?> /> <?php echo $actions[1]; ?></label>
											<div class="bws_help_box dashicons dashicons-editor-help">
												<div class="bws_hidden_help_text"><?php cptch_display_example( $actions[0] ); ?></div>
											</div>				
											<br />
										<?php } ?>
									</fieldset>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Complexity', 'captcha' ); ?></th>
								<td colspan="2"><fieldset>
									<legend class="screen-reader-text"><span><?php _e( 'Complexity', 'captcha' ); ?></span></legend>
									<?php foreach ( $cptch_admin_fields_difficulty as $diff ) { ?>
										<label><input type="checkbox" name="<?php echo $diff[0]; ?>" value="<?php echo $cptch_options[$diff[0]]; ?>" <?php if ( 1 == $cptch_options[$diff[0]] ) echo "checked=\"checked\""; ?> /> <?php echo $diff[1]; ?></label>
										<div class="bws_help_box dashicons dashicons-editor-help">
											<div class="bws_hidden_help_text<?php echo 'cptch_difficulty_image' == $diff[0] ? ' cptch_images_example': ''; ?>"><?php cptch_display_example( $diff[0] ); ?></div>
										</div>
										<br />
									<?php } ?>
								</fieldset></td>
							</tr>
						</table>
						<?php $package_list = $wpdb->get_results( "SELECT `id`, `name` FROM `{$wpdb->base_prefix}cptch_packages` ORDER BY `name` ASC;" ); 
							if ( ! empty( $package_list ) ) { ?>
								<table class="form-table"<?php if ( ! isset( $_GET['action'] ) ) echo ' style="display: none;"'; ?>>
									<tr class="cptch_packages">
										<th scope="row"><?php _e( 'Enable image packages', 'captcha' ); ?></th>
										<td>
											<select name="cptch_used_packages[]" multiple="multiple">
												<?php foreach( $package_list as $pack ) { ?>
													<option value="<?php echo $pack->id; ?>"<?php if ( in_array( $pack->id, $cptch_options['used_packages'] ) ) echo ' selected="selected"'; ?>><?php echo $pack->name; ?></option>
												<?php } ?>
											</select>
										</td>
									</tr>
									<tr class="cptch_packages">
										<th scope="row"><?php _e( 'Enlarge images on mouseover', 'captcha' ); ?></th>
										<td>
											<input type="checkbox" name="cptch_enlarge_images" value="1"<?php if ( 1 == $cptch_options['enlarge_images'] ) echo ' checked="checked"'; ?> /><br/>
										</td>
									</tr>
								</table>
						<?php }
						if ( isset( $_GET['action'] ) && 'advanced' == $_GET['action'] )
							cptch_pro_block( 'cptch_advanced_banner' ); ?>
						<table class="form-table"<?php if ( ! isset( $_GET['action'] ) ) echo ' style="display: none;"'; ?>>
							<tr valign="top">
								<th scope="row"><?php _e( 'Enable time limit', 'captcha' ); ?></th>
								<td>
									<input type="checkbox" name="cptch_use_time_limit" value="1"<?php if ( 1 == $cptch_options['use_time_limit'] ) echo ' checked="checked"'; ?> />
								</td>
							</tr>
							<tr valign="top" class="cptch_limt_options"<?php if ( 0 == $cptch_options['use_time_limit'] ) echo ' style="display: none;"'; ?>>
								<th scope="row"><?php _e( 'Set time limit', 'captcha' ); ?></th>
								<td>
									<label for="cptch_time_limit">
										<input type="number" name="cptch_time_limit" id ="cptch_time_limit" min="10" max="9999" step="1" value="<?php echo $cptch_options['time_limit']; ?>" style="width: 70px;"/>&nbsp;<?php _e( 'seconds', 'captcha' ); ?>

									</label>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( "Notification messages", 'captcha' ); ?></th>
								<td colspan="2">
									<p><i><?php _e( "Error", 'captcha' ); ?></i></p>
									<p><input class="cptch_settings_input" type="text" name="cptch_error_empty_value" value="<?php echo $cptch_options['cptch_error_empty_value']; ?>" maxlength="100" />&nbsp;<?php _e( 'If CAPTCHA field is empty', 'captcha' ); ?></p>
									<p><input class="cptch_settings_input" type="text" name="cptch_error_incorrect_value" value="<?php echo $cptch_options['cptch_error_incorrect_value']; ?>" maxlength="100" />&nbsp;<?php _e( 'If CAPTCHA is incorrect', 'captcha' ); ?></p>
									<p><input class="cptch_settings_input" type="text" name="cptch_error_time_limit" value="<?php echo $cptch_options['cptch_error_time_limit']; ?>" maxlength="100" />&nbsp;<?php _e( 'If time limit is exhausted', 'captcha' ); ?></p>
									<p><i><?php _e( "Info", 'captcha' ); ?></i></p>
									<p><input class="cptch_settings_input" type="text" name="cptch_whitelist_message" value="<?php echo $cptch_options['whitelist_message']; ?>" maxlength="100"  />&nbsp;<?php _e( 'If the user IP is added to the whitelist (this message will be displayed instead of CAPTCHA).', 'captcha' ); ?></p>
								</td>
							</tr>
						</table>
						<input type="hidden" name="cptch_form_submit" value="submit" />
						<p class="submit">
							<input id="bws-submit-button" type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'captcha' ) ?>" />
						</p>
						<?php wp_nonce_field( $plugin_basename, 'cptch_nonce_name' ); ?>
					</form>
					<?php bws_form_restore_default_settings( $plugin_basename );
					
				}
			} elseif ( 'go_pro' == $_GET['action'] ) {
				$show = bws_hide_premium_options_check( $cptch_options ) ? true : false;
				bws_go_pro_tab_show( 
					$show, 
					$cptch_plugin_info, 
					$plugin_basename, 
					'captcha.php', 
					'captcha_pro.php', 
					'captcha-pro/captcha_pro.php', 
					'captcha', 
					'9701bbd97e61e52baa79c58c3caacf6d', 
					'75', 
					isset( $go_pro_result['pro_plugin_is_activated'] ) 
				);
			} elseif ( 'whitelist' == $_GET['action'] ) {
				$limit_attempts_info = cptch_plugin_status( array( 'limit-attempts/limit-attempts.php', 'limit-attempts-pro/limit-attempts-pro.php' ), $all_plugins, $is_network );
				require_once( dirname( __FILE__ ) . '/includes/whitelist.php' );
				$cptch_whitelist = new Cptch_Whitelist( $plugin_basename, $limit_attempts_info );
				$cptch_whitelist->display_content();
			}
			bws_plugin_reviews_block( $cptch_plugin_info['Name'], 'captcha' ); ?>
		</div>
	<?php } 
}


if ( ! function_exists( 'cptch_plugin_status' ) ) {
	function cptch_plugin_status( $plugins, $all_plugins, $is_network ) {
		$result = array(
			'status'      => '',
			'plugin'      => '',
			'plugin_info' => array(),
		);
		foreach ( (array)$plugins as $plugin ) {
			if ( array_key_exists( $plugin, $all_plugins ) ) {
				if ( 
					( $is_network && is_plugin_active_for_network( $plugin ) ) || 
					( ! $is_network && is_plugin_active( $plugin ) ) 
				) {
					$result['status']      = 'actived';
					$result['plugin']      = $plugin;
					$result['plugin_info'] = $all_plugins[$plugin];
					break;
				} else {
					$result['status']      = 'deactivated';
					$result['plugin']      = $plugin;
					$result['plugin_info'] = $all_plugins[$plugin];
				}

			}
		}
		if ( empty( $result['status'] ) )
			$result['status'] = 'not_installed';
		return $result; 
	}
}

if ( ! function_exists( 'cptch_version_check' )) {
	function cptch_version_check( $plugin, $all_plugins ) {
			switch ( $plugin ) {
				case 'contact-form-plugin/contact_form.php': 
					$min_version = '3.95';
					break;
				case 'contact-form-pro/contact_form_pro.php':
					$min_version = '2.0.6';
					break;
				default:
					$min_version = false;
					break;
			}
		return $min_version ? version_compare( $all_plugins[ $plugin ]['Version'], $min_version, '>' ) : false;
	}
}


/* This function adds captcha to the login form */
if ( ! function_exists( 'cptch_login_form' ) ) {
	function cptch_login_form() {
		global $cptch_options, $cptch_ip_in_whitelist;
		if ( ! $cptch_ip_in_whitelist ) {
			if ( "" == session_id() )
				@session_start();
			if ( isset( $_SESSION["cptch_login"] ) ) 
				unset( $_SESSION["cptch_login"] );
		}

		echo '<p class="cptch_block">';
		if ( "" != $cptch_options['cptch_label_form'] )	
			echo '<span class="cptch_title">' . $cptch_options['cptch_label_form'] . '<span class="required"> ' . $cptch_options['cptch_required_symbol'] . '</span></span>';
		
		if ( isset( $_SESSION['cptch_error'] ) ) {
			echo "<br /><span style='color:red'>" . $_SESSION['cptch_error'] . "</span><br />";
			unset( $_SESSION['cptch_error'] );
		}
		if ( ! $cptch_ip_in_whitelist )
			echo cptch_display_captcha();
		else
			echo '<label class="cptch_whitelist_message">' . $cptch_options['whitelist_message'] . '</label>';
		echo '</p><br />';
		return true;
	}
}
/* End function cptch_login_form */

/* This function checks the captcha posted with a login when login errors are absent */
if ( ! function_exists( 'cptch_login_check' ) ) {
	function cptch_login_check( $user ) {
		global $cptch_options;
		$str_key = $cptch_options['cptch_str_key']['key'];

		if ( "" == session_id() )
			@session_start();

		if ( ( isset( $_SESSION["cptch_login"] ) && true === $_SESSION["cptch_login"] ) )
			return $user;

		/* Delete errors, if they set */
		if ( isset( $_SESSION['cptch_error'] ) )
			unset( $_SESSION['cptch_error'] );

		if ( ! function_exists( 'is_plugin_active' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		if ( is_plugin_active( 'limit-login-attempts/limit-login-attempts.php' ) ) { 
			if ( isset( $_REQUEST['loggedout'] ) && isset( $_REQUEST['cptch_number'] ) && "" == $_REQUEST['cptch_number'] ) {
				return $user;
			}	
		}		

		if ( cptch_limit_exhausted() ) {
			$_SESSION['cptch_login'] = false;
			$error = new WP_Error();
			$error->add( 'cptch_error', '<strong>' . __( 'ERROR', 'captcha' ) . '</strong>:' . '&nbsp;' . $cptch_options['cptch_error_time_limit'] );
			return $error;
		}

		/* Add error if captcha is empty */			
		if ( ( ! isset( $_REQUEST['cptch_number'] ) || "" == $_REQUEST['cptch_number'] ) && isset( $_REQUEST['loggedout'] ) ) {
			$error = new WP_Error();
			$error->add( 'cptch_error', '<strong>' . __( 'ERROR', 'captcha' ) . '</strong>: ' . $cptch_options['cptch_error_empty_value'] );
			wp_clear_auth_cookie();
			return $error;
		}
		if ( isset( $_REQUEST['cptch_result'] ) && isset( $_REQUEST['cptch_number'] ) && isset( $_REQUEST['cptch_time'] ) ) {
			if ( 0 === strcasecmp( trim( cptch_decode( $_REQUEST['cptch_result'], $str_key, $_REQUEST['cptch_time'] ) ), $_REQUEST['cptch_number'] ) ) {
				/* Captcha was matched */
				$_SESSION['cptch_login'] = true;
				return $user;								
			} else {
				$_SESSION['cptch_login'] = false;
				wp_clear_auth_cookie();
				/* Add error if captcha is incorrect */
				$error = new WP_Error();
				if ( "" == $_REQUEST['cptch_number'] )
					$error->add( 'cptch_error', '<strong>' . __( 'ERROR', 'captcha' ) . '</strong>: ' . $cptch_options['cptch_error_empty_value'] );
				else
					$error->add( 'cptch_error', '<strong>' . __( 'ERROR', 'captcha' ) . '</strong>: ' . $cptch_options['cptch_error_incorrect_value'] );
				return $error;
			}
		} else {
			if ( isset( $_REQUEST['log'] ) && isset( $_REQUEST['pwd'] ) ) {
				/* captcha was not found in _REQUEST */
				$_SESSION['cptch_login'] = false;
				$error = new WP_Error();
				$error->add( 'cptch_error', '<strong>' . __( 'ERROR', 'captcha' ) . '</strong>: ' . $cptch_options['cptch_error_empty_value'] );
				return $error;
			} else {
				/* it is not a submit */
				return $user;
			}
		}
	}
}
/* End function cptch_login_check */

/* This function adds captcha to the comment form */
if ( ! function_exists( 'cptch_comment_form' ) ) {
	function cptch_comment_form() {
		global $cptch_options, $cptch_ip_in_whitelist;
		/* Skip captcha if user is logged in and the settings allow */
		if ( ( is_user_logged_in() && 1 == $cptch_options['cptch_hide_register'] ) ) {
			return true;
		}

		/* captcha html - comment form */
		echo '<p class="cptch_block">';
		if ( "" != $cptch_options['cptch_label_form'] )	
			echo '<span class="cptch_title">' . $cptch_options['cptch_label_form'] . '<span class="required"> ' . $cptch_options['cptch_required_symbol'] . '</span></span>';
		echo '<br />';
		if ( ! $cptch_ip_in_whitelist )
			echo cptch_display_captcha();
		else
			echo '<label class="cptch_whitelist_message">' . $cptch_options['whitelist_message'] . '</label>';
		echo '</p>';
		return true;
	}
}
/* End function cptch_comment_form */

/* This function adds captcha to the comment form */
if ( ! function_exists( 'cptch_comment_form_default_wp3' ) ) {
	function cptch_comment_form_default_wp3( $args ) {
		global $cptch_options;
		/* skip captcha if user is logged in and the settings allow */
		if ( ( is_user_logged_in() && 1 == $cptch_options['cptch_hide_register'] ) ) {
			return $args;
		}
		/* captcha html - comment form */
		$args['comment_notes_after'] .= cptch_custom_form( "" );
		remove_action( 'comment_form', 'cptch_comment_form' );
		return $args;
	}
}
/* End function cptch_comment_form_default_wp3 */

/* This function adds captcha to the comment form */
if ( ! function_exists( 'cptch_comment_form_wp3' ) ) {
	function cptch_comment_form_wp3() {
		global $cptch_options, $cptch_ip_in_whitelist;

		/* Skip captcha if user is logged in and the settings allow */
		if ( ( is_user_logged_in() && 1 == $cptch_options['cptch_hide_register'] ) )
			return true;

		/* captcha html - comment form */
		echo '<p class="cptch_block">';
		if ( "" != $cptch_options['cptch_label_form'] )	
			echo '<span class="cptch_title">' . $cptch_options['cptch_label_form'] . '<span class="required"> ' . $cptch_options['cptch_required_symbol'] . '</span></span>';
		echo '<br />';
		if ( ! $cptch_ip_in_whitelist )
			echo cptch_display_captcha();
		else
			echo '<label class="cptch_whitelist_message">' . $cptch_options['whitelist_message'] . '</label>';
		echo '</p>';
		remove_action( 'comment_form', 'cptch_comment_form' );
		return true;
	}
}
/* End function cptch_comment_form_wp3 */

/* This function checks captcha posted with the comment */
if ( ! function_exists( 'cptch_comment_post' ) ) {
	function cptch_comment_post( $comment ) {	
		global $cptch_options;
		if ( ( is_user_logged_in() && 1 == $cptch_options['cptch_hide_register'] ) ) {
			return $comment;
		}
	
		$str_key = $cptch_options['cptch_str_key']['key'];
		$time_limit_exhausted = cptch_limit_exhausted();
		$error_message = $time_limit_exhausted ? $cptch_options['cptch_error_time_limit'] : $cptch_options['cptch_error_empty_value'];
		
		/* Added for compatibility with WP Wall plugin */
		/* This does NOT add CAPTCHA to WP Wall plugin, */
		/* It just prevents the "Error: You did not enter a Captcha phrase." when submitting a WP Wall comment */
		if ( function_exists( 'WPWall_Widget' ) && isset( $_REQUEST['wpwall_comment'] ) ) {
			/* Skip capthca */
			return $comment;
		}

		/* Skip captcha for comment replies from the admin menu */
		if ( isset( $_REQUEST['action'] ) && 'replyto-comment' == $_REQUEST['action'] &&
		( check_ajax_referer( 'replyto-comment', '_ajax_nonce', false ) || check_ajax_referer( 'replyto-comment', '_ajax_nonce-replyto-comment', false ) ) ) {
			/* Skip capthca */
			return $comment;
		}

		/* Skip captcha for trackback or pingback */
		if ( '' != $comment['comment_type'] && 'comment' != $comment['comment_type'] ) {
			/* Skip captcha */
			return $comment;
		}
		
		/* If captcha is empty */
		if ( ( isset( $_REQUEST['cptch_number'] ) && "" ==  $_REQUEST['cptch_number'] ) || $time_limit_exhausted )
			wp_die( __( 'Error', 'captcha' ) . ':&nbsp' . $error_message . ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ? '' : ' ' . __( "Click the BACK button on your browser, and try again.", 'captcha' ) ) );

		if ( isset( $_REQUEST['cptch_result'] ) && isset( $_REQUEST['cptch_number'] ) && isset( $_REQUEST['cptch_time'] ) && 0 === strcasecmp( trim( cptch_decode( $_REQUEST['cptch_result'], $str_key, $_REQUEST['cptch_time'] ) ), $_REQUEST['cptch_number'] ) ) {
			/* Captcha was matched */
			return( $comment );
		} else {
			wp_die( __( 'Error', 'captcha' ) . ':&nbsp' . $cptch_options['cptch_error_incorrect_value'] . ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ? '' : ' ' . __( "Click the BACK button on your browser, and try again.", 'captcha' ) ) );
		}
	}
}
/* End function cptch_comment_post */

/* This function adds the captcha to the register form */
if ( ! function_exists( 'cptch_register_form' ) ) {
	function cptch_register_form() {
		global $cptch_options, $cptch_ip_in_whitelist;
		/* the captcha html - register form */
		echo '<p class="cptch_block">';
		if ( "" != $cptch_options['cptch_label_form'] )	
			echo '<span class="cptch_title">' . $cptch_options['cptch_label_form'] . '<span class="required"> ' . $cptch_options['cptch_required_symbol'] . '</span></span>';
		if ( ! $cptch_ip_in_whitelist )
			echo cptch_display_captcha();
		else
			echo '<label class="cptch_whitelist_message">' . $cptch_options['whitelist_message'] . '</label>';
		echo '</p><br />';
		return true;
	}
}
/* End function cptch_register_form */

/* this function adds the captcha to the lost password form */
if ( ! function_exists ( 'cptch_lostpassword_form' ) ) {
	function cptch_lostpassword_form() {
		global $cptch_options, $cptch_ip_in_whitelist;
		/* the captcha html - register form */
		echo '<p class="cptch_block" style="text-align:left;">';
		if ( "" != $cptch_options['cptch_label_form'] )
			echo '<span class="cptch_title">' . $cptch_options['cptch_label_form'] . '<span class="required"> ' . $cptch_options['cptch_required_symbol'] . '</span></span>';
		
		if ( ! $cptch_ip_in_whitelist )
			echo cptch_display_captcha();
		else
			echo '<label class="cptch_whitelist_message">' . $cptch_options['whitelist_message'] . '</label>';
		echo '</p><br />';
		return true;
	}
}


/* this function adds the captcha to the register form in multisite */
if ( ! function_exists ( 'wpmu_cptch_register_form' ) ) {
	function wpmu_cptch_register_form( $errors ) {	
		global $cptch_options, $cptch_ip_in_whitelist;
		/* the captcha html - register form */
		echo '<div class="cptch_block">';
		if ( "" != $cptch_options['cptch_label_form'] )	
			echo '<span class="cptch_title">' . $cptch_options['cptch_label_form'] . '<span class="required"> ' . $cptch_options['cptch_required_symbol'] . '</span></span>';

		if ( ! $cptch_ip_in_whitelist ) {		
			if ( is_wp_error( $errors ) ) {
				$error_codes = $errors->get_error_codes();
				if ( is_array( $error_codes ) && ! empty( $error_codes ) ) {
					foreach ( $error_codes as $error_code ) {
						if ( "captcha_" == substr( $error_code, 0, 8 ) ) {
							$error_message = $errors->get_error_message( $error_code );
							echo '<p class="error">' . $error_message . '</p>';
						}
					}
				}
			}
			echo cptch_display_captcha();
		} else
			echo '<label class="cptch_whitelist_message">' . $cptch_options['whitelist_message'] . '</label>';
		echo '</div><br />';
	}
}
/* End function wpmu_cptch_register_form */

/* This function checks captcha posted with registration */
if ( ! function_exists( 'cptch_register_post' ) ) {
	function cptch_register_post( $login, $email, $errors ) {
		global $cptch_options;
		$str_key = $cptch_options['cptch_str_key']['key'];
		$time_limit_exhausted = cptch_limit_exhausted();
		if ( $time_limit_exhausted ) {
			$error_slug    = 'captcha_time_limit';
			$error_message = $cptch_options['cptch_error_time_limit'];
		} else {
			$error_slug    = 'captcha_blank';
			$error_message = $cptch_options['cptch_error_empty_value'];
		}
		/* If captcha is blank - add error */
		if ( ( isset( $_REQUEST['cptch_number'] ) && "" ==  $_REQUEST['cptch_number'] ) || $time_limit_exhausted ) {
			$errors->add( $error_slug, '<strong>' . __( 'ERROR', 'captcha' ) . '</strong>: ' . $error_message );
			return $errors;
		}

		if ( isset( $_REQUEST['cptch_result'] ) && isset( $_REQUEST['cptch_number'] ) && isset( $_REQUEST['cptch_time'] )
			&& 0 === strcasecmp( trim( cptch_decode( $_REQUEST['cptch_result'], $str_key, $_REQUEST['cptch_time'] ) ), $_REQUEST['cptch_number'] ) ) {
			/* Captcha was matched */
		} else {
			$errors->add( 'captcha_wrong', '<strong>'. __( 'ERROR', 'captcha') . '</strong>: ' . $cptch_options['cptch_error_incorrect_value'] );
		}
		return( $errors );
	}
}


/* this function checks the captcha posted with REGISTER form */
if ( ! function_exists ( 'cptch_register_check' ) ) {
	function cptch_register_check( $error ) {
		global $cptch_options;
		$str_key = $cptch_options['cptch_str_key']['key'];

		if ( cptch_limit_exhausted() ) {
			if ( ! is_wp_error( $error ) )
				$error = new WP_Error();
			$error->add( 'captcha_error', __( 'ERROR', 'captcha' ) . ':&nbsp;' . $cptch_options['cptch_error_time_limit'] );
		} elseif ( isset( $_REQUEST['cptch_number'] ) && "" == $_REQUEST['cptch_number'] ) {
			if ( ! is_wp_error( $error ) )
				$error = new WP_Error();
			$error->add( 'captcha_error', __( 'ERROR', 'captcha' ) . ':&nbsp;' . $cptch_options['cptch_error_empty_value'] );
		} elseif ( 
			! isset( $_REQUEST['cptch_result'] ) || 
			! isset( $_REQUEST['cptch_number'] ) || 
			! isset( $_REQUEST['cptch_time'] ) ||
			0 !== strcasecmp( trim( cptch_decode( $_REQUEST['cptch_result'], $str_key, $_REQUEST['cptch_time'] ) ), $_REQUEST['cptch_number'] )
		) {
			if ( ! is_wp_error( $error ) )
				$error = new WP_Error();
			$error->add( 'captcha_error', __( 'ERROR', 'captcha' ) . ':&nbsp;' . $cptch_options['cptch_error_incorrect_value'] );
		}
		return $error;
	}
}

/* End function cptch_register_post */
if ( ! function_exists( 'cptch_register_validate' ) ) {
	function cptch_register_validate( $results ) {
		global $cptch_options;
		$str_key = $cptch_options['cptch_str_key']['key'];
		$time_limit_exhausted = cptch_limit_exhausted();
		if ( $time_limit_exhausted ) {
			$error_slug    = 'captcha_time_limit';
			$error_message = $cptch_options['cptch_error_time_limit'];
		} else {
			$error_slug    = 'captcha_blank';
			$error_message = $cptch_options['cptch_error_empty_value'];
		}

		/* If captcha is blank - add error */
		if ( ( isset( $_REQUEST['cptch_number'] ) && "" ==  $_REQUEST['cptch_number'] ) || $time_limit_exhausted ) {
			$results['errors']->add( $error_slug, '<strong>' . __( 'ERROR', 'captcha' ) . '</strong>: ' . $error_message );
			return $results;
		}

		if ( 
			! isset( $_REQUEST['cptch_result'] ) || 
			! isset( $_REQUEST['cptch_number'] ) || 
			! isset( $_REQUEST['cptch_time'] ) ||
			0 !== strcasecmp( trim( cptch_decode( $_REQUEST['cptch_result'], $str_key, $_REQUEST['cptch_time'] ) ), $_REQUEST['cptch_number'] ) 
		)
			$results['errors']->add( 'captcha_wrong', '<strong>' . __( 'ERROR', 'captcha' ) . '</strong>: ' . $cptch_options['cptch_error_incorrect_value'] );
		return( $results );
	}
}
/* End function cptch_register_post */

/* this function checks the captcha posted with lostpassword form */
if ( ! function_exists ( 'cptch_lostpassword_check' ) ) {
	function cptch_lostpassword_check( $allow ) {
		global $cptch_options;
		$str_key = $cptch_options['cptch_str_key']['key'];
		$error   = '';

		if ( cptch_limit_exhausted() ) {
			$error = new WP_Error();
			$error->add( 'captcha_error', __( 'ERROR', 'captcha' ) . ':&nbsp;' . $cptch_options['cptch_error_time_limit'] );
		} elseif ( isset( $_REQUEST['cptch_number'] ) && "" == $_REQUEST['cptch_number'] ) {
			$error = new WP_Error();
			$error->add( 'captcha_error', __( 'ERROR', 'captcha' ) . ':&nbsp;' . $cptch_options['cptch_error_empty_value'] );
		} elseif ( 
			! isset( $_REQUEST['cptch_result'] ) || 
			! isset( $_REQUEST['cptch_number'] ) || 
			! isset( $_REQUEST['cptch_time'] ) ||
			0 !== strcasecmp( trim( cptch_decode( $_REQUEST['cptch_result'], $str_key, $_REQUEST['cptch_time'] ) ), $_REQUEST['cptch_number'] )
		) {
			$error = new WP_Error();
			$error->add( 'captcha_error', __( 'ERROR', 'captcha' ) . ':&nbsp;' . $cptch_options['cptch_error_incorrect_value'] );
		} 
		return is_wp_error( $error ) ? $error : $allow;
	}
}
/* function cptch_lostpassword_check */

/* This function checks the captcha posted with lostpassword form */
if ( ! function_exists( 'cptch_lostpassword_post' ) ) {
	function cptch_lostpassword_post() {
		global $cptch_options;
		$str_key = $cptch_options['cptch_str_key']['key'];
		$time_limit_exhausted = cptch_limit_exhausted();
		$error_message = $time_limit_exhausted ? $cptch_options['cptch_error_time_limit'] : $cptch_options['cptch_error_empty_value'];
		$error_message = __( 'Error', 'captcha' ) . ':&nbsp;' . $error_message . '&nbsp;' . __( 'Click the BACK button on your browser, and try again.', 'captcha' );

		/* If field 'user login' is empty - return */
		if( isset( $_REQUEST['user_login'] ) && "" == $_REQUEST['user_login'] )
			return;

		/* If captcha doesn't entered */
		if ( ( isset( $_REQUEST['cptch_number'] ) && "" ==  $_REQUEST['cptch_number'] ) || $time_limit_exhausted ) {
			wp_die( $error_message );
		}
		
		/* Check entered captcha */
		if ( isset( $_REQUEST['cptch_result'] ) && isset( $_REQUEST['cptch_number'] ) && isset( $_REQUEST['cptch_time'] ) 
			&& 0 === strcasecmp( trim( cptch_decode( $_REQUEST['cptch_result'], $str_key, $_REQUEST['cptch_time'] ) ), $_REQUEST['cptch_number'] ) ) {
			return;
		} else {			
			wp_die( __( 'Error', 'captcha' ) . ':&nbsp' . $cptch_options['cptch_error_incorrect_value'] . ' ' . __( "Click the BACK button on your browser, and try again.", 'captcha' ) );
		}
	}
}
/* function cptch_lostpassword_post */

/* Functionality of the captcha logic work */
if ( ! function_exists( 'cptch_display_captcha' ) ) {
	function cptch_display_captcha() {
		global $cptch_options, $cptch_time, $cptch_plugin_info;

		if ( ! $cptch_plugin_info ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$cptch_plugin_info = get_plugin_data( __FILE__ );
		}

		if ( ! isset( $cptch_options['cptch_str_key'] ) )
			$cptch_options = get_option( 'cptch_options' );
		if ( '' == $cptch_options['cptch_str_key']['key'] || $cptch_options['cptch_str_key']['time'] < time() - ( 24 * 60 * 60 ) )
			cptch_generate_key();
		$str_key = $cptch_options['cptch_str_key']['key'];
		
		/*
		 * array of math actions
		 */
		$math_actions = array();
		if ( 1 == $cptch_options['cptch_math_action_plus'] ) /* If Plus enabled */
			$math_actions[] = '&#43;';
		if ( 1 == $cptch_options['cptch_math_action_minus'] ) /* If Minus enabled */
			$math_actions[] = '&minus;';
		if ( 1 == $cptch_options['cptch_math_action_increase'] ) /* If Increase enabled */
			$math_actions[] = '&times;';
		/* current math action */
		$rand_math_action = rand( 0, count( $math_actions) - 1 );

		/*
		 * get elements of mathematical expression
		 */
		$array_math_expretion    = array();
		$array_math_expretion[0] = rand( 1, 9 ); /* first part */
		$array_math_expretion[1] = rand( 1, 9 ); /* second part */
		/* Calculation of the result */
		switch( $math_actions[ $rand_math_action ] ) {
			case "&#43;":
				$array_math_expretion[2] = $array_math_expretion[0] + $array_math_expretion[1];
				break;
			case "&minus;":
				/* Result must not be equal to the negative number */
				if ( $array_math_expretion[0] < $array_math_expretion[1] ) {
					$number = $array_math_expretion[0];
					$array_math_expretion[0] = $array_math_expretion[1];
					$array_math_expretion[1] = $number;
				}
				$array_math_expretion[2] = $array_math_expretion[0] - $array_math_expretion[1];
				break;
			case "&times;":
				$array_math_expretion[2] = $array_math_expretion[0] * $array_math_expretion[1];
				break;
		}

		/*
		 * array of allowed formats
		 */
		$allowed_formats = array();
		if ( 1 == $cptch_options["cptch_difficulty_number"] ) /* If Numbers enabled */
			$allowed_formats[] = 'number';
		if ( 1 == $cptch_options["cptch_difficulty_word"] ) /* If Words enabled */
			$allowed_formats[] = 'word';
		if ( 1 == $cptch_options["cptch_difficulty_image"] ) /* If Images enabled */
			$allowed_formats[] = 'image';
		$use_only_words = ( 1 == $cptch_options["cptch_difficulty_word"] && 0 == $cptch_options["cptch_difficulty_number"] ) || 0 == $cptch_options["cptch_difficulty_word"] ? true : false;
		/* number of field, which will be displayed as <input type="number" /> */
		$rand_input = rand( 0, 2 );

		/* 
		 * get current format for each operand 
		 * for example array( 'text', 'input', 'number' )
		 */
		$operand_formats = array();
		$max_rand_value = count( $allowed_formats ) - 1;
		for ( $i = 0; $i < 3; $i ++ ) {
			$operand_formats[] = $rand_input == $i ? 'input' : $allowed_formats[ mt_rand( 0, $max_rand_value ) ];
		}

		/*
		 * get value of each operand
		 */
		$operand = array();
		foreach ( $operand_formats as $key => $format ) {
			switch ( $format ) {
				case 'input':
					$operand[] = '<input id="cptch_input" class="cptch_input" type="text" autocomplete="off" name="cptch_number" value="" maxlength="2" size="2" aria-required="true" required="required" style="margin-bottom:0;display:inline;font-size: 12px;width: 40px;" />';
					break;
				case 'word':
					$operand[] = cptch_generate_value( $array_math_expretion[ $key ] );
					break;
				case 'image':
					$operand[] = cptch_get_image( $array_math_expretion[ $key ], $key, $cptch_options['used_packages'][ mt_rand( 0, count( $cptch_options['used_packages'] ) - 1 ) ], $use_only_words );
					break;
				case 'number':
				default:
					$operand[] = $array_math_expretion[ $key ];
					break;
			}
		}
		/*
		 * get html-structure of CAPTCHA
		 */
		$reload_button = 
				1 == $cptch_options['display_reload_button']
			?
				'<span class="cptch_reload_button_wrap hide-if-no-js">
					<noscript>
						<style type="text/css">
							.hide-if-no-js {
								display: none !important;
							}
						</style>
					</noscript>
					<span class="cptch_reload_button dashicons dashicons-update"></span>
				</span>'
			:
				'';
		return 
			'<span class="cptch_wrap">
				<label class="cptch_label" for="cptch_input">
					<span class="cptch_span">' . $operand[0] . '</span>
					<span class="cptch_span">&nbsp;' . $math_actions[ $rand_math_action ] . '&nbsp;</span>
					<span class="cptch_span">' . $operand[1] . '</span>
					<span class="cptch_span">&nbsp;=&nbsp;</span>
					<span class="cptch_span">' . $operand[2] . '</span>
					<input type="hidden" name="cptch_result" value="' . cptch_encode( $array_math_expretion[ $rand_input ], $str_key, $cptch_time ) . '" />
					<input type="hidden" name="cptch_time" value="' . $cptch_time . '" />
					<input type="hidden" value="Version: ' . $cptch_plugin_info["Version"] . '" />
				</label>' .
				$reload_button .
			'</span>';
	}
}


/**
 * Display image in CAPTCHA
 * @param    int     $value       value of element of mathematical expression
 * @param    int     $place       which is an element in the mathematical expression
 * @param    array   $package_id  what package to use in current CAPTCHA ( if it is '-1' then all )
 * @return   string               html-structure of element
 */
if ( ! function_exists( 'cptch_get_image' ) ) {
	function cptch_get_image( $value, $place, $package_id, $use_only_words ) {
		global $wpdb, $cptch_options;

		$result = array();
		if ( empty( $cptch_options ) )
			$cptch_options = get_option( 'cptch_options' );
		
		if ( empty( $cptch_options['used_packages'] ) )
			return cptch_generate_value( $value, $use_only_words );
		
		$where = -1 == $package_id ? ' IN (' . implode( ',', $cptch_options['used_packages'] ) . ')' : '=' . $package_id;
		$images = $wpdb->get_results(
			"SELECT 
				`{$wpdb->base_prefix}cptch_images`.`name` AS `file`, 
				`{$wpdb->base_prefix}cptch_packages`.`folder` AS `folder`
			FROM 
				`{$wpdb->base_prefix}cptch_images`
			LEFT JOIN
				`{$wpdb->base_prefix}cptch_packages`
			ON
				`{$wpdb->base_prefix}cptch_packages`.`id`=`{$wpdb->base_prefix}cptch_images`.`package_id`
			WHERE
				`{$wpdb->base_prefix}cptch_images`.`package_id` {$where}
				AND
				`{$wpdb->base_prefix}cptch_images`.`number`={$value};",
			ARRAY_N
		);
		if ( empty( $images ) )
			return cptch_generate_value( $value, $use_only_words );
		$upload_dir    = wp_upload_dir();
		$current_image = $images[ mt_rand( 0, count( $images ) - 1 ) ];
		$src = $upload_dir['basedir'] . '/bws_captcha_images/' . $current_image[1] . '/' . $current_image[0];
		if ( file_exists( $src ) ) {
			if ( 1 == $cptch_options['enlarge_images'] ) {
				switch( $place ) {
					case 0:
						$class = 'cptch_left'; 
						break;
					case 1:
						$class = 'cptch_center'; 
						break;
					case 2:
						$class = 'cptch_right'; 
						break;
					default:
						$class = '';
						break;
				}
			} else {
				$class = '';
			}
			$src = $upload_dir['basedir'] . '/bws_captcha_images/' . $current_image[1] . '/' . $current_image[0];
			$image_data = getimagesize( $src );
			return isset( $image_data['mime'] ) && ! empty( $image_data['mime'] ) ? '<img class="cptch_img ' . $class . '" src="data: ' . $image_data['mime'] . ';base64,'. base64_encode( file_get_contents( $src ) ) . '" />' :  cptch_generate_value( $value, $use_only_words );
		} else {
			return cptch_generate_value( $value, $use_only_words );
		}
	}
}

if ( ! function_exists( 'cptch_generate_value' ) ) {
	function cptch_generate_value( $value, $use_only_words = true ) {
		$random = $use_only_words  ? 1 : mt_rand( 0, 1 );
		if ( 1 == $random ) {
			/* In letters presentation of numbers 0-9 */
			$number_string = array(); 
			$number_string[0] = __( 'zero', 'captcha' );
			$number_string[1] = __( 'one', 'captcha' );
			$number_string[2] = __( 'two', 'captcha' );
			$number_string[3] = __( 'three', 'captcha' );
			$number_string[4] = __( 'four', 'captcha' );
			$number_string[5] = __( 'five', 'captcha' );
			$number_string[6] = __( 'six', 'captcha' );
			$number_string[7] = __( 'seven', 'captcha' );
			$number_string[8] = __( 'eight', 'captcha' );
			$number_string[9] = __( 'nine', 'captcha' ); 
			/* In letters presentation of numbers 11-19 */
			$number_two_string = array();
			$number_two_string[1] = __( 'eleven', 'captcha' );
			$number_two_string[2] = __( 'twelve', 'captcha' );
			$number_two_string[3] = __( 'thirteen', 'captcha' );
			$number_two_string[4] = __( 'fourteen', 'captcha' );
			$number_two_string[5] = __( 'fifteen', 'captcha' );
			$number_two_string[6] = __( 'sixteen', 'captcha' );
			$number_two_string[7] = __( 'seventeen', 'captcha' );
			$number_two_string[8] = __( 'eighteen', 'captcha' );
			$number_two_string[9] = __( 'nineteen', 'captcha' );
			/* In letters presentation of numbers 10, 20, 30, 40, 50, 60, 70, 80, 90 */
			$number_three_string = array();
			$number_three_string[1] = __( 'ten', 'captcha' );
			$number_three_string[2] = __( 'twenty', 'captcha' );
			$number_three_string[3] = __( 'thirty', 'captcha' );
			$number_three_string[4] = __( 'forty', 'captcha' );
			$number_three_string[5] = __( 'fifty', 'captcha' );
			$number_three_string[6] = __( 'sixty', 'captcha' );
			$number_three_string[7] = __( 'seventy', 'captcha' );
			$number_three_string[8] = __( 'eighty', 'captcha' );
			$number_three_string[9] = __( 'ninety', 'captcha' );
			if ( $value < 10 )
				return cptch_converting( $number_string[ $value ] );
			elseif ( $value < 20 && $value > 10 )
				return cptch_converting( $number_two_string[ $value % 10 ] );
			else {
				return 
						in_array( get_bloginfo( 'language', 'Display' ), array( "nl-NL", "de_DE" ) )
					?
						( 0 != $value % 10 ? $number_string[ $value % 10 ] . "&nbsp;" . __( "and", 'captcha' ) . "&nbsp;" : '' ) . $number_three_string[ $value / 10 ]
					:
						cptch_converting( $number_three_string[ $value / 10 ] ) . "&nbsp;" . ( 0 != $value % 10 ? cptch_converting( $number_string[ $value % 10 ] ) : '' );
			}
		} 
		return $value;
	}
}

if ( ! function_exists ( 'cptch_converting' ) ) {
	function cptch_converting( $number_string ) {
		global $cptch_options;

		if ( 1 == $cptch_options["cptch_difficulty_word"] && 'en-US' == get_bloginfo( 'language' ) ) {
			/* Array of htmlspecialchars for numbers and english letters */
			$htmlspecialchars_array			=	array();
			$htmlspecialchars_array['a']	=	'&#97;';
			$htmlspecialchars_array['b']	=	'&#98;';
			$htmlspecialchars_array['c']	=	'&#99;';
			$htmlspecialchars_array['d']	=	'&#100;';
			$htmlspecialchars_array['e']	=	'&#101;';
			$htmlspecialchars_array['f']	=	'&#102;';
			$htmlspecialchars_array['g']	=	'&#103;';
			$htmlspecialchars_array['h']	=	'&#104;';
			$htmlspecialchars_array['i']	=	'&#105;';
			$htmlspecialchars_array['j']	=	'&#106;';
			$htmlspecialchars_array['k']	=	'&#107;';
			$htmlspecialchars_array['l']	=	'&#108;';
			$htmlspecialchars_array['m']	=	'&#109;';
			$htmlspecialchars_array['n']	=	'&#110;';
			$htmlspecialchars_array['o']	=	'&#111;';
			$htmlspecialchars_array['p']	=	'&#112;';
			$htmlspecialchars_array['q']	=	'&#113;';
			$htmlspecialchars_array['r']	=	'&#114;';
			$htmlspecialchars_array['s']	=	'&#115;';
			$htmlspecialchars_array['t']	=	'&#116;';
			$htmlspecialchars_array['u']	=	'&#117;';
			$htmlspecialchars_array['v']	=	'&#118;';
			$htmlspecialchars_array['w']	=	'&#119;';
			$htmlspecialchars_array['x']	=	'&#120;';
			$htmlspecialchars_array['y']	=	'&#121;';
			$htmlspecialchars_array['z']	=	'&#122;';

			$simbols_lenght = strlen( $number_string );
			$simbols_lenght--;
			$number_string_new	=	str_split( $number_string );
			$converting_letters	=	rand( 1, $simbols_lenght );
			while ( $converting_letters != 0 ) {
				$position = rand( 0, $simbols_lenght );
				$number_string_new[ $position ] = isset( $htmlspecialchars_array[ $number_string_new[ $position ] ] ) ? $htmlspecialchars_array[ $number_string_new[ $position ] ] : $number_string_new[ $position ];
				$converting_letters--;
			}
			$number_string = '';
			foreach ( $number_string_new as $key => $value ) {
				$number_string .= $value;
			}
			return $number_string;			
		} else
			return $number_string;
	}
}

/* Function for encodinf number */
if ( ! function_exists( 'cptch_encode' ) ) {
	function cptch_encode( $String, $Password, $cptch_time ) {
		/* Check if key for encoding is empty */
		if ( ! $Password ) die ( __( "Encryption password is not set", 'captcha' ) );

		$Salt	=	md5( $cptch_time, true );
		$String	=	substr( pack( "H*", sha1( $String ) ), 0, 1 ) . $String;
		$StrLen	=	strlen( $String );
		$Seq	=	$Password;
		$Gamma	=	'';
		while ( strlen( $Gamma ) < $StrLen ) {
			$Seq = pack( "H*", sha1( $Seq . $Gamma . $Salt ) );
			$Gamma.=substr( $Seq, 0, 8 );
		}

		return base64_encode( $String ^ $Gamma );
	}
}

/* Function for decoding number */
if ( ! function_exists( 'cptch_decode' ) ) {
	function cptch_decode( $String, $Key, $cptch_time ) {
		/* Check if key for encoding is empty */
		if ( ! $Key ) die ( __( "Decryption password is not set", 'captcha' ) );

		$Salt	=	md5( $cptch_time, true );
		$StrLen	=	strlen( $String );
		$Seq	=	$Key;
		$Gamma	=	'';
		while ( strlen( $Gamma ) < $StrLen ) {
			$Seq = pack( "H*", sha1( $Seq . $Gamma . $Salt ) );
			$Gamma.= substr( $Seq, 0, 8 );
		}

		$String = base64_decode( $String );
		$String = $String^$Gamma;

		$DecodedString = substr( $String, 1 );
		$Error = ord( substr( $String, 0, 1 ) ^ substr( pack( "H*", sha1( $DecodedString ) ), 0, 1 )); 

		return $Error ? false : $DecodedString;
	}
}

/* This function adds captcha to the custom form */
if ( ! function_exists( 'cptch_custom_form' ) ) {
	function cptch_custom_form( $error_message, $content = "" ) {
		$cptch_options = get_option( 'cptch_options' );
		
		/* captcha html - login form */
		$content .= '<p class="cptch_block" style="text-align:left;">';
		if ( "" != $cptch_options['cptch_label_form'] )	
			$content .= '<span class="cptch_title">' . $cptch_options['cptch_label_form'] . '<span class="required"> ' . $cptch_options['cptch_required_symbol'] . '</span></span>';

		if ( isset( $error_message['error_captcha'] ) ) {
			$content .= "<span class='cptch_error' style='color:red'>" . $error_message['error_captcha'] . "</span><br />";
		}
		$content .= cptch_display_captcha_custom();
		$content .= '</p>';
		return $content;
	}
}
/* End function cptch_contact_form */

/* This function check captcha in the custom form */
if ( ! function_exists( 'cptch_check_custom_form' ) ) {
	function cptch_check_custom_form( $display_error = true ) {
		/** 
		 * this condition is necessary for compatibility 
		 * with Contact Form ( Free and Pro ) by BestWebsoft plugins versions
		 * that use $_POST as parameter for hook 
		 * apply_filters( 'cntctfrmpr_check_form', $_POST );
		 * @todo remove after some while
		 */
		if ( is_array( $display_error ) && ( isset( $_REQUEST['cntctfrm_contact_action'] ) || isset( $_REQUEST['cntctfrmpr_contact_action'] ) ) )
			$display_error = false;

		global $cptch_options, $cptch_ip_in_whitelist;
		$time_limit_exhausted = cptch_limit_exhausted();
		$str_key = $cptch_options['cptch_str_key']['key'];
		if ( empty( $cptch_ip_in_whitelist ) )
			$cptch_ip_in_whitelist = cptch_whitelisted_ip();
		if ( ! $cptch_ip_in_whitelist ) {
			if ( isset( $_REQUEST['cntctfrm_contact_action'] ) || isset( $_REQUEST['cntctfrmpr_contact_action'] ) ) {
				/* If captcha doesn't entered */
				if ( ( isset( $_REQUEST['cptch_number'] ) && "" ==  $_REQUEST['cptch_number'] ) || $time_limit_exhausted ) {
					$error = $time_limit_exhausted ? $cptch_options['cptch_error_time_limit'] : $cptch_options['cptch_error_empty_value'];
					return $display_error ? $error : false;;
				}
				
				/* Check entered captcha */
				if ( isset( $_REQUEST['cptch_result'] ) && isset( $_REQUEST['cptch_number'] ) && isset( $_REQUEST['cptch_time'] ) && 
					0 === strcasecmp( trim( cptch_decode( $_REQUEST['cptch_result'], $str_key, $_REQUEST['cptch_time'] ) ), $_REQUEST['cptch_number'] ) ) {
					return true;
				} else {
					return $display_error ? $cptch_options['cptch_error_incorrect_value'] : false;;
				}
			} else
				return false;
		} else {
			return true;
		}
	}
}
/* End function cptch_check_contact_form */

/* Functionality of the captcha logic work for custom form */
if ( ! function_exists( 'cptch_display_captcha_custom' ) ) {
	function cptch_display_captcha_custom() {
		global $cptch_options, $cptch_time, $cptch_plugin_info, $cptch_ip_in_whitelist;
		if ( empty( $cptch_ip_in_whitelist ) )
			$cptch_ip_in_whitelist = cptch_whitelisted_ip();

		if ( ! $cptch_ip_in_whitelist ) {
			if ( ! $cptch_plugin_info ) {
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				$cptch_plugin_info = get_plugin_data( __FILE__ );
			}

			if ( ! isset( $cptch_options['cptch_str_key'] ) )
				$cptch_options = get_option( 'cptch_options' );
			if ( '' == $cptch_options['cptch_str_key']['key'] || $cptch_options['cptch_str_key']['time'] < time() - ( 24 * 60 * 60 ) )
				cptch_generate_key();
			$str_key = $cptch_options['cptch_str_key']['key'];
			/*
			 * array of math actions
			 */
			$math_actions = array();
			if ( 1 == $cptch_options['cptch_math_action_plus'] ) /* If Plus enabled */
				$math_actions[] = '&#43;';
			if ( 1 == $cptch_options['cptch_math_action_minus'] ) /* If Minus enabled */
				$math_actions[] = '&minus;';
			if ( 1 == $cptch_options['cptch_math_action_increase'] ) /* If Increase enabled */
				$math_actions[] = '&times;';
			/* current math action */
			$rand_math_action = rand( 0, count( $math_actions) - 1 );

			/*
			 * get elements of mathematical expression
			 */
			$array_math_expretion    = array();
			$array_math_expretion[0] = rand( 1, 9 ); /* first part */
			$array_math_expretion[1] = rand( 1, 9 ); /* second part */
			/* Calculation of the result */
			switch( $math_actions[ $rand_math_action ] ) {
				case "&#43;":
					$array_math_expretion[2] = $array_math_expretion[0] + $array_math_expretion[1];
					break;
				case "&minus;":
					/* Result must not be equal to the negative number */
					if ( $array_math_expretion[0] < $array_math_expretion[1] ) {
						$number = $array_math_expretion[0];
						$array_math_expretion[0] = $array_math_expretion[1];
						$array_math_expretion[1] = $number;
					}
					$array_math_expretion[2] = $array_math_expretion[0] - $array_math_expretion[1];
					break;
				case "&times;":
					$array_math_expretion[2] = $array_math_expretion[0] * $array_math_expretion[1];
					break;
			}

			/*
			 * array of allowed formats
			 */
			$allowed_formats = array();
			if ( 1 == $cptch_options["cptch_difficulty_number"] ) /* If Numbers enabled */
				$allowed_formats[] = 'number';
			if ( 1 == $cptch_options["cptch_difficulty_word"] ) /* If Words enabled */
				$allowed_formats[] = 'word';
			if ( 1 == $cptch_options["cptch_difficulty_image"] ) /* If Images enabled */
				$allowed_formats[] = 'image';
			$use_only_words = ( 1 == $cptch_options["cptch_difficulty_word"] && 0 == $cptch_options["cptch_difficulty_number"] ) || 0 == $cptch_options["cptch_difficulty_word"] ? true : false;
			/* number of field, which will be displayed as <input type="text"/> */
			$rand_input = rand( 0, 2 );

			/* 
			 * get current format for each operand 
			 * for example array( 'text', 'input', 'number' )
			 */
			$operand_formats = array();
			$max_rand_value = count( $allowed_formats ) - 1;
			for ( $i = 0; $i < 3; $i ++ ) {
				$operand_formats[] = $rand_input == $i ? 'input' : $allowed_formats[ mt_rand( 0, $max_rand_value ) ];
			}

			/*
			 * get value of each operand
			 */
			$operand    = array();
			$id_postfix = rand( 0, 100 );
			foreach ( $operand_formats as $key => $format ) {
				switch ( $format ) {
					case 'input':
						$operand[] = '<input id="cptch_input_' . $id_postfix . '" class="cptch_input" type="text" autocomplete="off" name="cptch_number" value="" maxlength="2" size="2" aria-required="true" required="required" style="margin-bottom:0;display:inline;font-size: 12px;width: 40px;" />';
						break;
					case 'word':
						$operand[] = cptch_generate_value( $array_math_expretion[ $key ] );
						break;
					case 'image':
						$operand[] = cptch_get_image( $array_math_expretion[ $key ], $key, $cptch_options['used_packages'][ mt_rand( 0, count( $cptch_options['used_packages'] ) - 1 ) ], $use_only_words );
						break;
					case 'number':
					default:
						$operand[] = $array_math_expretion[ $key ];
						break;
				}
			}
			/*
			 * get html-structure of CAPTCHA
			 */
			$reload_button = 
					1 == $cptch_options['display_reload_button']
				?
					'<span class="cptch_reload_button_wrap hide-if-no-js">
						<noscript>
							<style type="text/css">
								.hide-if-no-js {
									display: none !important;
								}
							</style>
						</noscript>
						<span class="cptch_reload_button dashicons dashicons-update"></span>
					</span>'
				:
					'';
			return
				'<span class="cptch_wrap">
					<label class="cptch_label" for="cptch_input_' . $id_postfix . '">
						<span class="cptch_span">' . $operand[0] . '</span>
						<span class="cptch_span">&nbsp;' . $math_actions[ $rand_math_action ] . '&nbsp;</span>
						<span class="cptch_span">' . $operand[1] . '</span>
						<span class="cptch_span">&nbsp;=&nbsp;</span>
						<span class="cptch_span">' . $operand[2] . '</span>
						<input type="hidden" name="cptch_result" value="' . cptch_encode( $array_math_expretion[ $rand_input ], $str_key, $cptch_time ) . '" />
						<input type="hidden" name="cptch_time" value="' . $cptch_time . '" />
						<input type="hidden" value="Version: ' . $cptch_plugin_info["Version"] . '" />
					</label>' .
					$reload_button .
				'</span>';
		} else {
			return '<label class="cptch_whitelist_message">' . $cptch_options['whitelist_message'] . '</label>';
		}
	}
}

/**
 * Check CAPTCHA life time
 * @return boolean
 */
if ( ! function_exists( 'cptch_limit_exhausted' ) ) {
	function cptch_limit_exhausted() {
		global $cptch_options;
		if ( empty( $cptch_options ) )
			$cptch_options = get_option( 'cptch_options' );
		return 
				1 == $cptch_options['use_time_limit'] &&       /* if 'Enable time limit' option is enabled */
				isset( $_REQUEST['cptch_time'] ) &&            /* if form was sended */
				$cptch_options['time_limit'] < time() - $_REQUEST['cptch_time'] /* if time limit is exhausted */
			? 
				true 
			: 
				false;
	}
}

if ( ! function_exists ( 'cptch_display_example' ) ) {
	function cptch_display_example( $action ) {
		echo "<div class='cptch_example_fields_actions'>";
		switch( $action ) {
			case "cptch_math_action_plus":
				echo __( 'seven', 'captcha' ) . ' &#43; 1 = <img src="' . plugins_url( 'images/cptch_input.jpg' , __FILE__ ) . '" alt="" title="" />';
				break;
			case "cptch_math_action_minus":
				echo __( 'eight', 'captcha' ) . ' &minus; 6 = <img src="' . plugins_url( 'images/cptch_input.jpg' , __FILE__ ) . '" alt="" title="" />';
				break;
			case "cptch_math_action_increase":
				echo '<img src="' . plugins_url( 'images/cptch_input.jpg' , __FILE__ ) . '" alt="" title="" /> &times; 1 = ' . __( 'seven', 'captcha' );
				break;
			case "cptch_difficulty_number":
				echo '5 &minus; <img src="' . plugins_url( 'images/cptch_input.jpg' , __FILE__ ) . '" alt="" title="" /> = 1';
				break;
			case "cptch_difficulty_word":
				echo __( 'six', 'captcha' ) . ' &#43; ' . __( 'one', 'captcha' ) . ' = <img src="' . plugins_url( 'images/cptch_input.jpg' , __FILE__ ) . '" alt="" title="" />';
				break;
			case 'cptch_difficulty_image': 
				echo '<label class="cptch_label">
						<span class="cptch_span"><img src="' . plugins_url( 'images/6.png' , __FILE__ ) . '" /></span>' . 
						'<span class="cptch_span">&nbsp;&#43;&nbsp;</span>' . 
						'<span class="cptch_span"><img src="' . plugins_url( 'images/cptch_input.jpg' , __FILE__ ) . '" /></span> 
						<span class="cptch_span">&nbsp;=&nbsp;</span>
						<span class="cptch_span"><img src="' . plugins_url( 'images/7.png' , __FILE__ ).'" /></span>
					<label>';
				break;
			default:
				break;
		}
		echo "</div>";
	}
}


if ( ! function_exists( 'cptch_front_end_scripts' ) ) {
	function cptch_front_end_scripts() {
		if ( ! is_admin() ) {
			global $cptch_options;
			if ( empty( $cptch_options ) )
				$cptch_options = get_option( 'cptch_options' );
			wp_enqueue_style( 'cptch_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );
			wp_enqueue_style( 'dashicons' );
			/* if mobile device is NOT detected */
			if( ! isset( $_SERVER['HTTP_USER_AGENT'] ) || ! preg_match( '/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Windows Phone|Opera Mini/i', $_SERVER['HTTP_USER_AGENT'] ) )
				wp_enqueue_style( 'cptch_desktop_style', plugins_url( 'css/desktop_style.css', __FILE__ ) );
			
			wp_enqueue_script( 'cptch_front_end_script', plugins_url( 'js/front_end_script.js' , __FILE__ ), array( 'jquery' ) );
			$cptch_vars = array(
				'nonce'   => wp_create_nonce( 'cptch', 'cptch_nonce' ),
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'enlarge' => $cptch_options['enlarge_images']
			);
			wp_localize_script( 'cptch_front_end_script', 'cptch_vars', $cptch_vars );
		}
	}
}

if ( ! function_exists ( 'cptch_admin_head' ) ) {
	function cptch_admin_head() {
		if ( isset( $_REQUEST['page'] ) && 'captcha.php' == $_REQUEST['page'] ) {
			wp_enqueue_style( 'cptch_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );
			wp_enqueue_script( 'cptch_script', plugins_url( 'js/script.js', __FILE__ ) );
		}
	}
}

/* Function for interaction with Limit Attempts plugin */
if ( ! function_exists( 'cptch_lmtttmpts_interaction' ) ) {
	function cptch_lmtttmpts_interaction() {
		global $cptch_options;
		$str_key = $cptch_options['cptch_str_key']['key'];
		if ( 1 == $cptch_options['cptch_login_form'] ) { /* check for captcha existing in login form */
			if ( isset( $_REQUEST['cptch_result'] ) && isset( $_REQUEST['cptch_number'] ) && isset( $_REQUEST['cptch_time'] ) ) { /* check for existing request by captcha */
				if ( 0 !== strcasecmp( trim( cptch_decode( $_REQUEST['cptch_result'], $str_key, $_REQUEST['cptch_time'] ) ), $_REQUEST['cptch_number'] ) ) { /* is captcha wrong */
					if ( isset( $_SESSION["cptch_login"] ) && false === $_SESSION["cptch_login"] ) {
						return false; /* wrong captcha */
					}
				}
			}
		}
		return true; /* no captcha in login form or its right */
	}
}

/* add help tab */
if ( ! function_exists( 'cptch_add_tabs' ) ) {
	function cptch_add_tabs() {
		$args = array(
			'id'      => 'cptch',
			'section' => '200538879'
		);
		bws_help_tab( get_current_screen(), $args );
	}
}

if ( ! function_exists( 'cptch_plugin_action_links' ) ) {
	function cptch_plugin_action_links( $links, $file ) {		
		if ( ! is_network_admin() ) {
			static $this_plugin;
			if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);

			if ( $file == $this_plugin ) {
				$settings_link = '<a href="admin.php?page=captcha.php">' . __( 'Settings', 'captcha' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}

if ( ! function_exists( 'cptch_register_plugin_links' ) ) {
	function cptch_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			if ( ! is_network_admin() )
				$links[]	=	'<a href="admin.php?page=captcha.php">' . __( 'Settings', 'captcha' ) . '</a>';
			$links[]	=	'<a href="http://wordpress.org/plugins/captcha/faq/" target="_blank">' . __( 'FAQ', 'captcha' ) . '</a>';
			$links[]	=	'<a href="http://support.bestwebsoft.com">' . __( 'Support', 'captcha' ) . '</a>';
		}
		return $links;
	}
}

if ( ! function_exists ( 'cptch_plugin_banner' ) ) {
	function cptch_plugin_banner() {
		global $hook_suffix, $cptch_options, $cptch_plugin_info;
		$captcha_page = isset( $_GET['page'] ) && 'captcha.php' == $_GET['page'] ? true : false;
		if ( empty( $cptch_options ) )
				$cptch_options = get_option( 'cptch_options' );

		if ( 'plugins.php' == $hook_suffix ) {
			if ( isset( $cptch_options['first_install'] ) && strtotime( '-1 week' ) > $cptch_options['first_install'] )
				bws_plugin_banner( $cptch_plugin_info, 'cptch', 'captcha', '345f1af66a47b233cd05bc55b2382ff0', '75', '//ps.w.org/captcha/assets/icon-128x128.png' ); 

			bws_plugin_banner_to_settings( $cptch_plugin_info, 'cptch_options', 'captcha', 'admin.php?page=captcha.php' );
		}
		if ( 
			( $hook_suffix == 'plugins.php' || $captcha_page ) &&
			( ! isset( $cptch_options['display_notice_about_images'] ) || 1 == $cptch_options['display_notice_about_images'] )
		) {
			if ( $captcha_page ) {
				$cptch_options['display_notice_about_images'] = 0;
				update_option( 'cptch_options', $cptch_options ); 
				$settings_link = 'Captcha';
			} else {
				$settings_link = '<a href="' . admin_url( 'admin.php?page=captcha.php' ) .'">Captcha</a>';
			} ?>
			<div class="update-nag"><?php echo sprintf( __( 'Try New %s: with Pictures Now!', 'captcha' ), $settings_link ); ?></div>
		<?php }
	}
}

/* Function for delete delete options */
if ( ! function_exists ( 'cptch_delete_options' ) ) {
	function cptch_delete_options() {
		global $wpdb;
		$all_plugins = get_plugins();
		$another_captcha = array_key_exists( 'captcha-plus/captcha-plus.php', $all_plugins ) || array_key_exists( 'captcha-pro/captcha-pro.php', $all_plugins ) ? true : false;
		if ( is_multisite() ) {
			$old_blog = $wpdb->blogid;
			/* Get all blog ids */
			$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );
				delete_option( 'cptch_options' );
				if ( ! $another_captcha ) {
					$prefix = 1 == $blog_id ? $wpdb->base_prefix : $wpdb->base_prefix . $blog_id . '_';
					$wpdb->query( "DROP TABLE `{$prefix}cptch_whitelist`;" );
				}
			}
			switch_to_blog( $old_blog );
		} else {
			delete_option( 'cptch_options' );
			if ( ! $another_captcha )
				$wpdb->query( "DROP TABLE `{$wpdb->prefix}cptch_whitelist`;" );
		}
		/* delete images */
		if ( ! $another_captcha ) {
			$wpdb->query( "DROP TABLE `{$wpdb->base_prefix}cptch_images`, `{$wpdb->base_prefix}cptch_packages`;" );
			$upload_dir = wp_upload_dir();
			$images_dir = $upload_dir['basedir'] . '/bws_captcha_images';
			$packages   = scandir( $images_dir );
			if ( is_array( $packages ) ) {
				foreach ( $packages as $package ) {
					if ( ! in_array( $package, array( '.', '..' ) ) ) {
						/* remove all files from package */
						array_map( 'unlink', glob( $images_dir . "/" . $package . "/*.*" ) ); 
						/* remove package */
						rmdir( $images_dir . "/" . $package );
					}
				}
			}
			rmdir( $images_dir );
		}
	}
}


if ( ! function_exists( 'cptch_reload' ) ) {
	function cptch_reload() {
		check_ajax_referer( 'cptch', 'cptch_nonce' );
		echo cptch_display_captcha();
		die();
	}
}


register_activation_hook( __FILE__, 'cptch_plugin_activate' );

add_action( 'admin_menu', 'cptch_admin_menu' );

add_action( 'init', 'cptch_init' );
add_action( 'admin_init', 'cptch_admin_init' );

add_action( 'plugins_loaded', 'cptch_plugins_loaded' );

/* Additional links on the plugin page */
add_filter( 'plugin_action_links', 'cptch_plugin_action_links', 10, 2 );
add_filter( 'plugin_row_meta', 'cptch_register_plugin_links', 10, 2 );

add_action( 'admin_enqueue_scripts', 'cptch_admin_head' );
add_action( 'wp_enqueue_scripts', 'cptch_front_end_scripts' );
add_action( 'login_enqueue_scripts', 'cptch_front_end_scripts' );

add_action( 'wp_ajax_cptch_reload', 'cptch_reload' );
add_action( 'wp_ajax_nopriv_cptch_reload', 'cptch_reload' );

add_action( 'admin_notices', 'cptch_plugin_banner' );

register_uninstall_hook( __FILE__, 'cptch_delete_options' );