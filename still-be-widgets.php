<?php

/*
Plugin Name: Still BE Widgets
Plugin URI:  https://still-be.com/
Description: Add some original widgets, example, Instagram Timeline (Available after April 2020 too).
Version:     1.0.2
Author:      Daisuke Yamamoto (Analog Studio)
Author URI:  https://web.analogstd.com/
License:     GPL2
Text Domain: stillbe_widgets
Domain Path: /languages/
*/


// Do not allow direct access to the file.
if( !defined( 'ABSPATH' ) ) {
	exit;
}






// Define the key name used option table
define( 'STILLBE_WIDGETS_OPTION_KEY_NAME', 'stillbe_widgets_settings' );

// Define the setting group name
define( 'STILLBE_WIDGETS_SETTING_GROUP',   'stillbe-widgets-setting-group' );



// Just after Initializing
add_action( 'init', function() {

	// Load translation file
	load_plugin_textdomain( 'stillbe_widgets', false, dirname( plugin_basename( __FILE__ ) ). '/languages/' );

	// Add Polyfill of Intersection Observer API
	// When Internet Explorer can be ignored, it can be deleted
	$js_polyfill = plugins_url( 'asset/js/intersection_observer_polyfill.js', __FILE__ );
	wp_enqueue_script( 'stillbe_widgets-intersection_observer_polyfill', $js_polyfill, array(), null );

} );



// Resister the widget
add_action( 'widgets_init', function() {

	// Load functions related Instagram
	//   * StillBE_Widgets_Instagram_Timeline  | Class : for Widget, this constructor instantiates the functions Class
	//   * StillBE_Widgets_Instagram_Functions | Class : for Managing Instagram Graph API & Caching
	require_once( __DIR__. '/instagram.php' );

	// Resister the Instagram Widget
	register_widget( new StillBE_Widgets_Instagram_Timeline( STILLBE_WIDGETS_OPTION_KEY_NAME ) );

	// Resister others for later...
//	resister_widget( 'StillBE_Widgets_******************' );

} );



// Add Settings Page in Admin
add_action( 'admin_menu', function() {

	$stillbe_menu = new StillBE_Widgets_Setting( STILLBE_WIDGETS_OPTION_KEY_NAME, STILLBE_WIDGETS_SETTING_GROUP );

} );



// Resister Setting Group
add_action( 'admin_init', function(){


} );









// Setting Manager
class StillBE_Widgets_Setting {

	const PARENT_SLUG = 'stillbe-widgets-settings';

	private $opt_key  = '';
	private $group    = '';
	private $settings = null;

	// Constructor
	function __construct( $setting_opt_name = '', $setting_group_name = '' ) {
		if ( ! current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'stillbe_widgets' ) );
		}
		// Get option table key
		if( empty( $setting_opt_name ) || empty( $setting_group_name ) ) {
			wp_die( __( 'You must set an option table setting key and instantiate.', 'stillbe_widgets' ) );
		} else {
			$this->opt_key  = $setting_opt_name;
			$this->group    = $setting_group_name;
			$this->settings = get_option( $this->opt_key, array() );
		}
		// Add menu pages
		add_menu_page   (                    'Still BE Widgets',                'Still BE Widgets', 'manage_options', self::PARENT_SLUG,                       array( $this, 'parent_page'  ) );
		add_submenu_page( self::PARENT_SLUG, 'Insta Widget | Still BE Widgets', 'Insta Widget',     'manage_options', 'stillbe-widgets-settings_insta-widget', array( $this, 'insta_widget' ) );
	//	add_submenu_page( self::PARENT_SLUG, '***** Widget | Still BE Widgets', '***** Widget',     'manage_options', 'stillbe-widgets-settings_*****-widget', array( $this, '*****_widget' ) );
		// Arg 1 : Group Name using Setting API
		// Arg 2 : Saving Key Name in WP Options table (in SQL)
		register_setting( $this->group, $this->opt_key );
	}


	// Select Setting Pages
	public function parent_page() {
		global $submenu;
		$submenu_arr  = $submenu[ self::PARENT_SLUG ];
		$adminphp_url = admin_url( 'admin.php' );
		echo '<div class="wrap">';
		echo   '<h1>Still BE Widgets Setting</h1>';
		echo   '<p>'. __( 'Select the item you want to set.', 'stillbe_widgets' ). '</p>';
		echo   '<ul>';
		foreach( $submenu_arr as $s ) {
			if( $s[2] === self::PARENT_SLUG ) {
				continue;
			}
			echo '<li><a href="'. ( $adminphp_url. '?page='. $s[2] ). '">'. $s[0]. '</a></li>';
		}
		echo   '</ul>';
		echo '</div>';
	}


	// Insta Widget Setting Screen Rendering
	public function insta_widget() {
		// Instagram Settings
		$ig_settings = $this->settings['ig'];
		// Common Settings
		$common = $ig_settings['common'] ?: array();
		// Exist Users
		$users  = $ig_settings['users']  ?: array();
		// Instagram Setting Key
		$input_basename = $this->opt_key. '[ig]';
		// Wrapper
		echo '<div class="wrap">';
		// Style
		echo '<style>';
		echo   'input[type=number]::-webkit-inner-spin-button,';
		echo   'input[type=number]::-webkit-outer-spin-button {';
		echo   '	-webkit-appearance : none;';
		echo   '	        appearance : none;';
		echo   '	margin             : 0;';
		echo   '}';
		echo   '.setting-wrapper{';
		echo   '	margin             : 32px 0;';
		echo   '	padding            : 8px 16px;';
		echo   '	border             : 1px dotted #B4B4B480;';
		echo   '}';
		echo   '.form-table input{';
		echo   '	width              : 240px;';
		echo   '}';
		echo   '.form-table textarea{';
		echo   '	width              : 480px;';
		echo   '	max-width          : 100%;';
		echo   '	height             : 160px;';
		echo   '}';
		echo   '.submit-button{';
		echo   '	margin             : 32px 0;';
		echo   '}';
		echo '</style>';
		// Title
		echo '<h1>[ Still BE ] Insta Widget</h1>';
		// Saving
	/*
		// This operation is move to Setting API
		if( isset( $_POST[ $save_flag_name ] ) && $_POST[ $save_flag_name ] === 'true' ) {
			// POST data
			$common = filter_input( INPUT_POST, 'common', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			$users  = filter_input( INPUT_POST, 'users',  FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			// Sanitize the POST data
			if( ! empty( $common ) ) {
				if( ! empty( $common['cache-lifetime'] ) ) {
					$common['cache-lifetime'] = absint( $common['cache-lifetime'] );
					$ig_settings['common'] = empty( $ig_settings['common'] ) ? array() : $ig_settings['common'];
					$ig_settings['common']['cache-lifetime'] = $common['cache-lifetime'] < 30 ? 600 : $common['cache-lifetime'];
				}
			}
			if( ! empty( $users ) ){
				$ig_settings['users'] = array();
				$user_name = array();
				foreach( $users as $u ){
					// Add if setting values are complete
					$u['id'] = preg_replace( '/\D/', '', $u['id'] );
					if( ! empty( $u['name'] ) && ! in_array( $u['name'], $user_name ) && ! empty( $u['id'] ) && ! empty( $u['token'] ) ) {
						$ig_settings['users'][] = $u;
						$user_name[] = $u['name'];
					}
				}
			}
			// Save Options
			$this->settings['ig'] = $ig_settings;
			update_option( $this->opt_key, $this->settings );
			// Output Saving Notice
			echo '<div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible"><p><strong>'. __( 'Settings saved.', 'stillbe_widgets' ). '</strong></p></div>';
		}
	*/
		// Note
		echo '<p>';
		echo   __( 'Save API settings to get information from Instagram.', 'stillbe_widgets' ). '<BR>';
		echo   __( 'Select the user using &quot;Distinguished Name&quot; in the each widget setting.', 'stillbe_widgets' );
		echo '</p>';
		echo '<p>';
		echo   __( 'Please refer to the following article for how to get a business account ID and an indefinite access token.', 'stillbe_widgets' );
		echo '</p>';
		echo '<p>';
		echo   '<a href="https://web.analogstd.com/tips/posts/api/instagram-grapgh-api-facebook.php" target="_blank" rel="noopener">「Instagram API」が廃止に！代替の「Instagram Graph API」に移行しよう！ | Analog Studio</a><BR>';
		echo   '<small>* Japanese Only</small><BR>';
		echo   '<small>* '. __( 'It is a paid article of 560 yen', 'stillbe_widgets' ). '</small>';
		echo '</p>';
		// Setting Form
		echo '<form name="stillbe-form" method="POST" action="options.php">';
		// Add the information using Setting API
		// Group Name / Action Name / Nonce / This Page Path
		settings_fields( 'stillbe-widgets-setting-group' );
		// Settings
		echo   '<div class="submit-button">';
		echo     '<input type="submit" value="'. __( 'Save', 'stillbe_widgets' ). '" class="button button-primary">';
		echo   '</div>';
		echo   '<section class="setting-wrapper">';
		echo     '<h2>'. __( 'Common Setting', 'stillbe_widgets' ). '</h2>';
		echo     '<table class="form-table"><tbody><tr scope="row">';
		echo       '<th><label for="cache_lifetime">'. __( 'Cache Lifetime', 'stillbe_widgets' ). '</label></th>';
		echo       '<td>';
		echo         '<input id="cache_lifetime" type="number" name="'. $input_basename. '[common][cache-lifetime]" value="'. esc_attr( empty( $common['cache-lifetime'] ) ? 600 : $common['cache-lifetime'] ). '">';
		echo         '<p>'. __( 'Set the min time interval for accessing the API [sec] (min: 30sec)', 'stillbe_widgets' ). '</p>';
		echo       '</td>';
		echo     '</tr></tbody></table>';
		echo   '</section>';
		for( $i = 0, $n = 5; $i < $n; $i++ ) {
			echo '<section class="setting-wrapper">';
			echo   '<h2>'. sprintf( __( 'User %d', 'stillbe_widgets' ), $i + 1 ). '</h2>';
			echo   '<table class="form-table"><tbody>';
			echo     '<tr scope="row">';
			echo       '<th><label for="user_'. $i. '_name">'. __( 'Distinguished Name', 'stillbe_widgets' ). '</label></th>';
			echo       '<td>';
			echo         '<input id="user_'. $i. '_name" type="text" name="'. $input_basename. '[users]['. $i. '][name]" value="'. esc_attr( empty( $users[ $i ] ) || empty( $users[ $i ]['name'] ) ? '' : $users[ $i ]['name'] ). '">';
			echo         '<p>'. __( 'An identification name used in widgets, for example, using Instagram User ID is better', 'stillbe_widgets' ). '</p>';
			echo       '</td>';
			echo     '</tr>';
			echo     '<tr scope="row">';
			echo       '<th><label for="user_'. $i. '_id">'. __( 'Instagram Buisiness ID', 'stillbe_widgets' ). '</label></th>';
			echo       '<td>';
			echo         '<input id="user_'. $i. '_id" type="number" name="'. $input_basename. '[users]['. $i. '][id]" value="'. esc_attr( empty( $users[ $i ] ) || empty( $users[ $i ]['id'] ) ? '' : $users[ $i ]['id'] ). '">';
			echo       '</td>';
			echo     '</tr>';
			echo     '<tr scope="row">';
			echo       '<th><label for="user_'. $i. '_token">'. __( 'Access Token (Expires: Never)', 'stillbe_widgets' ). '</label></th>';
			echo       '<td>';
			echo         '<textarea id="user_'. $i. '_token" name="'. $input_basename. '[users]['. $i. '][token]" row="5">'. esc_attr( empty( $users[ $i ] ) || empty( $users[ $i ]['token'] ) ? '' : $users[ $i ]['token'] ). '</textarea>';
			echo       '</td>';
			echo     '</tr>';
			echo   '</tbody></table>';
			echo '</section>';
		}
		echo   '<div class="submit-button">';
		echo     '<input type="hidden" name="saving" value="true">';
	//	echo     '<input type="submit" value="'. __( 'Save', 'stillbe_widgets' ). '" class="button button-primary">';
		submit_button();
		echo   '</div>';
		echo '</form>';
		// Closing DOM
		echo '</div>';
	}


}







///////// END /////////


?>