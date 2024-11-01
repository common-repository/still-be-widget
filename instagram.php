<?php

/************
 * インスタグラム表示関数
 * Get datas function by Instagram graph API
 ************/


// Do not allow direct access to the file.
if( !defined( 'ABSPATH' ) ) {
	exit;
}









// インスタグラム投稿を表示
// Display the Instagram timeline
class StillBE_Widgets_Instagram_Timeline extends WP_Widget {

	const VERSION = '1.0';   // the local version of each widget
	const CSS     = 'asset/css/instagram.css';
	const LAZY_JS = 'asset/js/lazyload.js';

	private $func     = null;
	private $settings = null;


	/* Constructor */
	function __construct( $setting_opt_name = '', $indent = 0 ) {
		parent::__construct(
			'stillbe_widgets-insta_widget',
			__( 'Still BE Instagram Timeline Widget', 'stillbe_widgets' ),
			array(
				'description' => __( 'Show the Instagram Timeline Box', 'stillbe_widgets' ),
			)
		);
		$this->indents = str_repeat( ' ', absint( $indent ) );
		// Get option table key
		if( empty( $setting_opt_name ) ) {
			wp_die( __( 'You must set an option table setting key and instantiate.', 'stillbe_widgets' ) );
		} else {
			$this->settings = get_option( $setting_opt_name, array() );
		}
		// Instantiate Functions Class
		$this->func = new StillBE_Widgets_Instagram_Functions( $setting_opt_name );
		// Load lazy Load
		$js_lazyload = plugins_url( self::LAZY_JS, __FILE__ );
		wp_enqueue_script( 'stillbe_widgets-insta_widget_lazyload', $js_lazyload, array(), self::VERSION );
		// Add the CSS for Instagram
		$css = plugins_url( self::CSS, __FILE__ );
		wp_enqueue_style( 'stillbe_widgets-insta_widget_css', $css, array(), self::VERSION );
	}


	// Generate a widget HTML code
	function widget( $args, $instance ) {
		// Check the $instance
		if( empty( $instance['title'] ) ) {
			$title = $args['title'];
		} else {
			$title = apply_filters( 'widget_title', $instance['title'] );
		}
		$style = empty( $instance['style'] ) ? 'grid' : $instance['style'];
		$user  = empty( $instance['user']  ) ?  null  : $instance['user'];
		$id    = empty( $instance['id']    ) ?  null  : $instance['id'];
		$token = empty( $instance['token'] ) ?  null  : $instance['token'];
		// Instagram Settings
		$ig_settings = $this->settings['ig'];
		// Buisiness ID & Access Token
		$update_enable = false;
		if( empty( $id ) || empty( $token ) ) {
			$id    = null;
			$token = null;
			$update_enable = true;
		}
		// HTML code
		$html = array();
		// Before widget code
		$html[] = $args['before_widget'];
		// Title
		if( ! empty( $title ) ) {
			$html[] = "  {$args['before_title']}";
			$html[] = "    {$title}";
			$html[] = "  {$args['after_title']}";
		}
		// Generate HTML codes
		if( empty( $user ) && empty( $id ) ) {
			$html[] = '  <p class="stillbe-instagram-timeline">'. __( 'Set the ID & token for API', 'stillbe_widgets' ). '</p>';
		} else {
			// Get the Instagram API object
			if( ! empty( $id ) ) {
				$instagram = $this->func->get_ig_object( array(
					'id'    => $id,
					'token' => $token,
				), 9 );
			} else {
				$instagram = $this->func->get_ig_object( $user, 9 );
			}
			// Embed the DataURL of the logo and icons
			$ig_brand_icon = 'data:image/png;base64,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';
			$ig_brand_logo = 'data:image/svg+xml;base64,PHN2ZyB4bWxuczpkYz0iaHR0cDovL3B1cmwub3JnL2RjL2VsZW1lbnRzLzEuMS8iIHhtbG5zOmNjPSJodHRwOi8vY3JlYXRpdmVjb21tb25zLm9yZy9ucyMiIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyIgeG1sbnM6c3ZnPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB3aWR0aD0iODQwIiBoZWlnaHQ9IjMwMCIgdmlld0JveD0iMCAwIDg0MCAzMDAiIHZlcnNpb249IjEuMSI+PHBhdGggZD0iTTY1IDQ5Yy0xNiA3LTM0IDI2LTQwIDUwLTcgMzEgMjMgNDQgMjUgMzkgMy01LTUtNi03LTIyLTItMjEgNy00MyAxOS01MyAzLTIgMiAwIDIgNXYxMDJjMCAyMS0xIDI4LTMgMzUtMSA3LTQgMTEtMiAxM3MxMi0zIDE3LTEwYzctOSA5LTIwIDEwLTMydi01MS02OGMwLTUtMTQtMTEtMjEtOG01NzUgMTAyYzAgMTEtMyAyMC02IDI2LTYgMTItMTggMTYtMjMtMS0zLTEwLTQtMjYtMS0zOSAyLTEzIDgtMjMgMTctMjIgMTAgMCAxNCAxMyAxMyAzNnptLTE2MiA3MGMwIDE5LTMgMzUtOSA0MC05IDctMjEgMS0xOS0xMiAyLTEyIDEzLTI1IDI4LTQwdjEyem0tMi03MGMtMSAxMC00IDIwLTYgMjYtNiAxMi0xOSAxNi0yNC0xLTQtMTItMy0yOC0xLTM3IDItMTMgOC0yNCAxOC0yNCA5IDAgMTQgMTAgMTMgMzZ6bS05NC0xYy0xIDExLTMgMjAtNiAyNy03IDEyLTE5IDE2LTI0LTEtNC0xMy0zLTMwLTEtMzkgMi0xNCA4LTIzIDE4LTIyIDkgMCAxNCAxMyAxMyAzNXptNDMwIDEzYy0yIDAtMyAzLTQgNy0zIDE0LTYgMTctMTAgMTctNSAwLTktNy0xMC0yMXYtNTJjMC00LTEtOC0xMi0xMi01LTItMTItNC0xNSA0YTIwOSAyMDkgMCAwIDAtMTUgNTBjLTEtNi0yLTE3LTItNDEtMS00LTEtOC03LTExLTMtMi0xMy02LTE2LTJzLTcgMTMtMTEgMjVsLTUgMTV2LTM0YzAtNC0yLTUtMy01bC0xMi0zYy00IDAtNSAyLTUgNXY1OGMtMiAxMS04IDI1LTE1IDI1cy0xMC02LTEwLTMzbDEtMzR2LTEzYzAtMy02LTUtOS01bC03LTFjLTIgMC00IDItNCA0djRjLTQtNi0xMC05LTEzLTEwLTEwLTMtMjEtMS0yOSAxMC02IDktMTAgMTktMTEgMzMtMiAxMS0xIDIyIDEgMzEtMyAxMC03IDE0LTEyIDE0LTcgMC0xMi0xMS0xMS0zMSAwLTEzIDMtMjIgNi0zNSAxLTYgMC04LTMtMTEtMi0zLTctNC0xMy0zbC0xOSA0IDEtNGMyLTE1LTE0LTE0LTE5LTktMyAzLTUgNi02IDEyLTEgOSA3IDEzIDcgMTNhMTM5IDEzOSAwIDAgMS0yNCA1MSAxMzkwIDEzOTAgMCAwIDEgMC01NGwxLTEyYzAtMy0yLTQtNS01bC05LTJjLTUtMS03IDItNyA0djRjLTQtNi05LTktMTMtMTAtMTAtMy0yMS0xLTI5IDEwLTYgOS0xMCAyMS0xMSAzM3YyOWMtMSA4LTYgMTYtMTEgMTYtNyAwLTEwLTYtMTAtMzN2LTM0bDEtMTNjMC0zLTYtNS05LTVsLTgtMWMtMiAwLTQgMi00IDR2NGMtNC02LTktOS0xMy0xMC0xMC0zLTIwLTEtMjggMTAtNiA3LTEwIDE1LTEyIDMzbC0xIDE1Yy0yIDEyLTExIDI3LTE5IDI3LTQgMC04LTktOC0yN2wxLTYyaDI3YzMgMCA2LTEyIDMtMTNsLTE2LTEtMTMtMSAxLTI1YzAtMi0zLTQtNC00bC0xMS0zYy01LTEtOCAwLTggNGwtMSAyNy0yMS0xYy00IDAtOCAxNi0zIDE2bDIzIDEtMSA0NnYzYy0zIDE5LTE2IDI5LTE2IDI5IDMtMTItMy0yMi0xMy0zMGwtMjAtMTVzNS00IDktMTRjMy03IDMtMTQtNC0xNi0xMi0zLTIyIDYtMjUgMTYtMyA3LTIgMTMgMyAxOGwxIDItMTAgMThjLTkgMTUtMTUgMjgtMjEgMjgtNCAwLTQtMTMtNC0yNGwyLTQxYzAtNS0zLTgtNy0xMS0zLTItOC01LTExLTUtNSAwLTE5IDEtMzMgMzlsLTUgMTQgMS00Ni0yLTNjLTItMS04LTQtMTQtNC0yIDAtMyAxLTMgNGwtMSA3MiAxIDE1IDIgNiA1IDNjMiAwIDEyIDIgMTMtMiAwLTUgMC0xMCA2LTMwIDgtMzAgMjAtNDUgMjUtNTBoMmwtMiAzOGMtMSAzNyA2IDQ0IDE2IDQ0IDcgMCAxOC03IDI5LTI2bDE5LTMyIDExIDExYzkgOCAxMiAxNiAxMCAyNC0yIDYtNyAxMi0xNyA2bC03LTVoLTZjLTMgMy02IDYtNyAxMS0xIDQgMyA2IDggOCA0IDIgMTIgMyAxNyA0IDIxIDAgMzctMTAgNDktMzggMiAyNCAxMSAzNyAyNiAzNyAxMCAwIDIwLTEzIDI1LTI2IDEgNiAzIDEwIDYgMTQgMTEgMTkgMzQgMTUgNDYtMWw0LTdjMSAxNSAxMyAyMCAyMCAyMCA4IDAgMTYtMyAyMS0xNmwzIDRjMTEgMTkgMzQgMTUgNDYtMWwxLTJ2MTBsLTEwIDljLTE4IDE2LTMxIDI5LTMyIDQzLTIgMTggMTMgMjUgMjQgMjYgMTIgMSAyMi02IDI5LTE1IDUtOCA5LTI1IDktNDNsLTEtMjVhMjAwIDIwMCAwIDAgMCAzOC02NmgxNGMyIDAgMiAwIDIgMnMtOSAzNC0xIDU1YzUgMTUgMTcgMTkgMjQgMTkgOCAwIDE2LTYgMjAtMTVsMSAzYzEyIDE5IDM1IDE1IDQ2LTFsNC03YzMgMTYgMTUgMjAgMjIgMjBzMTQtMyAxOS0xNmwxIDEyIDQgM2M3IDIgMTMgMSAxNiAxIDItMSAzLTIgMy02IDEtOSAwLTI1IDMtMzYgNS0yMCA5LTI3IDExLTMxIDItMiAzLTIgMyAwbDIgMzVjMSAxMyAzIDIxIDUgMjMgNCA3IDggOCAxMiA4IDMgMCA4LTEgOC01IDAtMyAwLTE2IDUtMzUgMy0xMyA4LTI0IDEwLTI4YTI1MiAyNTIgMCAwIDAgMyA1MmM1IDIxIDE4IDIzIDIzIDIzIDExIDAgMTktNyAyMi0yOCAwLTUtMS05LTQtOSIgZmlsbD0iIzI2MjYyNiIvPjwvc3ZnPg==';
			// 
			if( empty( $instagram ) || empty( $instagram->media ) || empty( $instagram->media->data ) ) {
				$html[] = '  <p class="stillbe-instagram-timeline">'. __( 'No post', 'stillbe_widgets' ). '</p>';
			} else {
				$html[] = '  <article class="stillbe-ig-embed">';
				$html[] = '    <template class="stillbe-load-later">';
				$html[] = '      <header>';
				$html[] = '        <section class="ig-basic-info">';
				$html[] = '          <h1 class="ig-brad-gryph">';
				$html[] = '            <a href="https://www.instagram.com/" target="_blank" rel="noopener">';
				$html[] = '              <span>Instagram</span>';
				$html[] = '              <img class="ig-brand-icon" src="'. $ig_brand_icon. '" alt="Instagram Icon">';
				$html[] = '              <img class="ig-brand-logo" src="'. $ig_brand_logo. '" alt="Instagram Logo">';
				$html[] = '            </a>';
				$html[] = '          </h1>';
				$html[] = '          <h2 class="ig-user-info">';
				$html[] = '            <figure>';
				$html[] = '              <img src="'. $instagram->profile_picture_url. '" alt="User Icon">';
				$html[] = '            </figure>';
				$html[] = '            <a href="https://www.instagram.com/'. $instagram->username. '/" target="_blank" rel="noopener">';
				$html[] = '              <span class="ig-user-name">'. $instagram->name. '</span>';
				$html[] = '              <span class="ig-user-id">@'. $instagram->username. '</span>';
				$html[] = '            </a>';
				$html[] = '            <div>';
				$html[] = '              <span class="ig-user-media-count">'. $instagram->media_count. '</span>';
				$html[] = '              <span class="ig-user-followers">'. $instagram->followers_count. '</span>';
				$html[] = '            </div>';
				$html[] = '          </h2>';
				$html[] = '        </section>';
				$html[] = '        <nav class="ig-select-show-type">';
				$html[] = '          <label for="'. $args['widget_id']. '__show_list" class="ig-select-list-button">List</label>';
				$html[] = '          <label for="'. $args['widget_id']. '__show_grid" class="ig-select-grid-button">Grid</label>';
				$html[] = '        </nav>';
				$html[] = '      </header>';
				$html[] = '      <input type="radio" name="'. $args['widget_id']. '--style" class="display-none" id="'. $args['widget_id']. '__show_list" value="list"'. ( $style === 'list' ? ' checked' : '' ). '>';
				$html[] = '      <input type="radio" name="'. $args['widget_id']. '--style" class="display-none" id="'. $args['widget_id']. '__show_grid" value="grid"'. ( $style === 'grid' ? ' checked' : '' ). '>';
				$html[] = '      <main>';
				if( ! empty( $instagram ) && ! empty( $instagram->media ) && is_array( $instagram->media->data ) ) {
					$i = 0;
					foreach( $instagram->media->data as $post ) {
						$i = $i + 1;
						$caption = $post->caption;
						$caption = mb_convert_encoding( $caption, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN' );
						$caption = preg_replace( '/[\n\r]+/', '<BR>', $caption );
						$caption = str_replace( '&quot;', '', $caption );
						if( empty( $post->children ) ) {
							if( isset( $post->thumbnail_url ) ) {
								$media    = $post->thumbnail_url;
								$video    = $post->media_url;
								$is_video = true;
							} else {
								$media    = $post->media_url;
								$is_video = false;
							}
						} else {
							if( isset( $post->children->data[0]->thumbnail_url ) ) {
								$media    = $post->children->data[0]->thumbnail_url;
								$video    = $post->children->data[0]->media_url;
								$is_video = true;
							} else {
								$media    = $post->children->data[0]->media_url;
								$is_video = false;
							}
						}
						$html[] = '        <div class="ig-posts ig-post-'. $i. '">';
						$html[] = '          <a href="'. $post->permalink. '" class="ig-permalink object-fit-cover" target="_blank" rel="noopener" title="Likes: '. $post->like_count. '">';
						$html[] = '            <figure>';
						$html[] = '              <img src="'. $media. '" alt="Instagram Post '. $i. '">';
						$html[] = '            </figure>';
						$html[] = '          </a>';
						$html[] = '          <time class="ig-post-date" datetime="'. $post->timestamp. '">';
						$html[] = '            <span>'. date( 'Y/m/d', strtotime( $post->timestamp ) ). '</span>';
						$html[] = '          </time>';
						$html[] = '          <p class="ig-caption">';
						$html[] = '            '. $caption;
						$html[] = '          </p>';
						$html[] = '        </div>';
					}
				}
				$html[] = '      </main>';
				// Update an Instagram cache data
				if( $instagram->is_update && $update_enable ){
					$update_js_raw = '
						setTimeout(function(){
							var refleshCache = new XMLHttpRequest();
							refleshCache.open("GET", "'. $instagram->ajax->url. '?action='. $instagram->ajax->action_name. '&user='. $instagram->ajax->user_name. '&nonce_check='. $instagram->ajax->nonce_check. '", true);
							refleshCache.timeout = 10 * 1000;
							refleshCache.onload = function(){
								var result = JSON.parse(refleshCache.responseText);
							//	console.log(result);
								if("ok" in result){
									console.log("[ Still BE Widgets ] '. $instagram->ajax->user_name. '\'s Instagram cache update; " + result.message);
								} else{
									console.log("[ Still BE Widgets ] '. $instagram->ajax->user_name. '\'s Instagram cache update; XHR response failed...");
								}
							};
							refleshCache.send();
						}, 5 * 1000);
					';
					$pattern     = array( '/(?<!:)\/\/.*/', '/[\n\t]/', '/\s+/' );
					$replacement = array( ''              , ''        , ' '     );
					$update_js_min = preg_replace( $pattern, $replacement, $update_js_raw );
				//	$update_js_url = 'data:text/javascript;base64,'. base64_encode( $update_js_min );
				//	$html[] = '      <script type="text/javascript" src="'. $update_js_url. '"></script>';
					$html[] = '      <script>'. $update_js_min. '</script>';
				}
				// Close tags and add <noscript> tag
				$html[] = '    </template>';
				$html[] = '    <noscript><p>Enable Javascript to show Instagram</p></noscript>';
				$html[] = '  </article>';
			}
		}
		$html[] = $args['after_widget'];
		// Output
		echo $this->indents. implode( "\n{$this->indents}", $html ). "\n";
	}


	// Setting form
	function form( $instance ) {
		$title = empty( $instance['title'] ) ? __( 'Instagram Timeline', 'stillbe_widgets' ) : $instance['title'];
		$style = empty( $instance['style'] ) ? 'grid' : $instance['style'];
		$user  = empty( $instance['user']  ) ? ''     : $instance['user'];
		$id    = empty( $instance['id']    ) ? ''     : $instance['id'];
		$token = empty( $instance['token'] ) ? ''     : $instance['token'];
		// Resisterd instagram users
		$ig_settings = $this->settings['ig'];
		$ig_users    = ! empty( $ig_settings ) && ! empty( $ig_settings['users'] ) ? $ig_settings['users'] : array();
?>
		<p>
		  <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'stillbe_widgets' ); ?>:</label>
		  <input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
		  <?php _e( 'Posts show style', 'stillbe_widgets' ); ?>
		</p>
		<p>
		  <input type="radio" name="<?php echo $this->get_field_name( 'style' ); ?>" id="<?php echo $this->get_field_id( 'style_grid' ); ?>" value="grid"<?php echo $style === 'grid' ? ' checked' : ''; ?>>
		  <label for="<?php echo $this->get_field_id( 'style_grid' ); ?>"><?php _e( 'Arrange on the grid', 'stillbe_widgets' ); ?></label>
		</p>
		<p>
		  <input type="radio" name="<?php echo $this->get_field_name( 'style' ); ?>" id="<?php echo $this->get_field_id( 'style_list' ); ?>" value="list"<?php echo $style === 'list' ? ' checked' : ''; ?>>
		  <label for="<?php echo $this->get_field_id( 'style_list' ); ?>"><?php _e( 'Arrange on the list', 'stillbe_widgets' ); ?></label>
		</p>
		<p>
		  <label for="<?php echo $this->get_field_id( 'user' ); ?>"><?php _e( 'Registered the Distinguished Name', 'stillbe_widgets' ); ?>:</label>
		  <select id="<?php echo $this->get_field_id( 'user' ); ?>" name="<?php echo $this->get_field_name( 'user' ); ?>" required>
		    <option value="">- <?php _e( 'Select', 'stillbe_widgets' ); ?> -</oprion>
<?php
		foreach( $ig_users as $u ) {
?>
		    <option value="<?php echo $u['name']; ?>" <?php echo $user === $u['name'] ? ' selected' : ''; ?>><?php echo $u['name']; ?></oprion>
<?php
		}
?>
		  </select>
		</p>
		<p>
		  <?php _e( 'Please move to setting page for register some Distinguished Names.', 'stillbe_widgets' ); ?><BR>
		  <a href="<?php echo esc_url( admin_url( 'admin.php' ). '?page=stillbe-widgets-settings_insta-widget' ); ?>"><?php _e( 'Setting Page', 'stillbe_widgets' ); ?></a>
		</p>
		<p>
		  <?php _e( 'If you want to set an other Instagram User with Buisiness ID & Access Token, fill the bellow inputs.', 'stillbe_widgets' ); ?><BR>
		  <?php _e( 'But cache updating on background is disable.', 'stillbe_widgets' ); ?>
		</p>
		<p>
		  <label for="<?php echo $this->get_field_id( 'id' ); ?>"><?php _e( 'Instagram Buisiness ID', 'stillbe_widgets' ); ?>:</label><BR>
		  <input type="number" id="<?php echo $this->get_field_id( 'id' ); ?>" name="<?php echo $this->get_field_name( 'id' ); ?>" value="<?php echo esc_attr( $id ); ?>">
		</p>
		<p>
		  <label for="<?php echo $this->get_field_id( 'token' ); ?>"><?php _e( 'Instagram Access Token', 'stillbe_widgets' ); ?>:</label>
		  <textarea id="<?php echo $this->get_field_id( 'token' ); ?>" name="<?php echo $this->get_field_name( 'token' ); ?>" style="width:100%;height:10em;"><?php echo esc_html( $token ); ?></textarea>
		</p>
<?php
	}


	// Sanitize setting values before saving
	function update( $new_instance, $old_instance ) {
		$instance = array();
		// Sanitize
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['title'] = ! empty( $instance['title'] )  ? $instance['title']  : __( 'Instagram Timeline', 'stillbe_widgets' );
		$instance['style'] = $new_instance['style'] === 'list' ? 'list' : 'grid';
		$instance['user']  = strip_tags( $new_instance['user'] );
		$instance['id']    = absint( $new_instance['id'] );
		$instance['token'] = strip_tags( $new_instance['token'] );
		// Return sanitized settings
		return $instance;
	}

}








class StillBE_Widgets_Instagram_Functions {

	const API_BASE_URL   = 'https://graph.facebook.com/v5.0';
	const CACHE_DATA_MAX = 100;

	const AJAX_ACTION    = 'stillbe_widgets_ig_cache_update';

	private $settings    = null;
	private $file_system = null;


	/* Constructor */
	function __construct( $setting_opt_name = '' ) {
		// Get option table key
		if( empty( $setting_opt_name ) ) {
			wp_die( __( 'You must set an option table setting key and instantiate.', 'stillbe_widgets' ) );
		} else {
			$this->settings = get_option( $setting_opt_name, array() );
		}
		// Initialize WP_FileSystem
		require_once( ABSPATH. 'wp-admin/includes/file.php' );
		global $wp_filesystem;
	//	add_filter( 'request_filesystem_credentials', '__return_true' );
	//	$creds = request_filesystem_credentials( plugins_url( 'instagram.php', __FILE__ ), '', false, false, null );
		if( WP_Filesystem( /*$creds*/ ) ) {
			$this->file_system = &$wp_filesystem;
		}
		// Set the Cache Updating of Instagram Graph API with Ajax
		add_action( 'wp_ajax_'. self::AJAX_ACTION,        array( $this, 'ig_cache_update' ) );
		add_action( 'wp_ajax_nopriv_'. self::AJAX_ACTION, array( $this, 'ig_cache_update' ) );
	}


	// Facebook の Instagram Graph API を利用して最新投稿の情報を取得する
	// Get the latest posts by Instagram Graph API in Facebook
	public function get_ig_object( $user = null, $max = 10 ) {

		if( empty( $user ) ) {
			return array();
		}

		// Get Instagram settings
		$ig_settings = $this->settings['ig'];

		// Inatagram Buisiness ID & Access Token
		$ig_buisiness_id = null;
		$token           = null;
		if( is_array( $user ) ) {
			if( ! empty( $user['id'] ) && ! empty( $user['token'] ) ) {
				$ig_buisiness_id = $user['id'];
				$token           = $user['token'];
			}
		} else {
			// If empty users data
			if( empty( $ig_settings ) || empty( $ig_settings['users'] ) ) {
				return array();
			}
			// Search ID & token
			foreach( $ig_settings['users'] as $u ) {
				if( $u['name'] === $user && ! empty( $u['id'] ) && ! empty( $u['token'] ) ) {
					$ig_buisiness_id = $u['id'];
					$token           = $u['token'];
					break;
				}
			}
		}

		// If not found ID or token
		if( empty( $token ) ) {
			return array();
		}

		// Create a cache directory for a gotten API data
		$cache_dir = plugin_dir_path( __FILE__ ). 'asset/temp/cache/';
		if( file_exists( $cache_dir ) ) {
			if( 0774 !== fileperms( $cache_dir ) ) {
				chmod( $cache_dir, 0774 );
			}
		} else {
			$temp_dir = plugin_dir_path( __FILE__ ). 'asset/temp/';
			if( file_exists( $temp_dir ) ) {
				if( 0774 !== fileperms( $temp_dir ) ) {
					chmod( $temp_dir, 0774 );
				}
			} else {
				if( mkdir( $temp_dir, 0774 ) ) {
					chmod( $temp_dir, 0774 );
				}
			}
			if( mkdir( $cache_dir, 0774 ) ) {
				chmod( $cache_dir, 0774 );
			}
		}

		// Cannot create or exist cache directory
		if( ! file_exists( $cache_dir ) ) {
			return array();
		}

		// Instagram Graph API request base URL
		$graph_api       = self::API_BASE_URL;

		// Cache file
		$cache_file      = plugin_dir_path( __FILE__ ). 'asset/temp/cache/ig_timeline_'. $ig_buisiness_id. '.dat';

		// Get modified timestamp
		$cache_modified  = @ filemtime( $cache_file ) ?: 0;

		// Set cache lifetime [sec]
		$cache_lifetime  = empty( $ig_settings['common'] ) || empty( $ig_settings['common']['cache-lifetime'] ) ? 600 : absint( $ig_settings['common']['cache-lifetime'] );
		$cache_lifetime  = empty( $cache_lifetime ) ? 600 : $cache_lifetime;

		// Set get fields
		$query_fields    = 'name,username,profile_picture_url,media_count,followers_count,follows_count,media.limit('. self::CACHE_DATA_MAX. '){caption,like_count,media_url,permalink,timestamp,thumbnail_url,comments_count,children{media_url,thumbnail_url}}';

		// Load cache data
		// When cache is fresh, use the cache data
	//	$ig_api_json     = @ file_get_contents( $cache_file );
		$ig_api_json     = $this->file_system->get_contents( $cache_file );

		// Check cache last modified time
		if( empty( $cache_modified ) || empty( $ig_api_json ) ) {
			// When cache is empty, request to Instagram Graph API
			$response = wp_remote_get( "{$graph_api}/{$ig_buisiness_id}?fields={$query_fields}&access_token={$token}" );
			$status   = wp_remote_retrieve_response_code( $response );
			// Save to cache
			$ig_api_json = wp_remote_retrieve_body( $response );
			if( 200 == $status && ! empty( $ig_api_json ) ) {
			//	@ file_put_contents( $cache_file, $ig_api_json, LOCK_EX );
				$this->file_system->put_contents( $cache_file, $ig_api_json );
			}
			// Cache update with Ajax is not nessesary
			$is_cache_update = false;
		} else if( ( time() - $cache_modified ) > $cache_lifetime ) {
			// When cache is expired, request to Instagram Graph API on background with Ajax
			$is_cache_update = true;
		} else {
			$is_cache_update = false;
		}

		// Expand the JSON data to Object
		if( $ig_api_json ){
			$ig_data = @ json_decode( $ig_api_json ) ?: null;
			if( empty( $ig_data ) || isset( $ig_data->error ) ) {
				$ig_data = null;
			} else{
				$ig_data->is_update = $is_cache_update;
			}
		} else{
			$ig_data = null;
		}

		// Slice return data
		if( ! empty( $ig_data ) ) {
			if( ! empty( $ig_data->media ) && ! empty( $ig_data->media->data ) ) {
				$ig_data->media->data = array_slice( $ig_data->media->data ?: array(), 0, $max );
			}
		}

		// Cache update settings
		if( $is_cache_update ) {
			$ig_data->ajax = new stdClass;
			$ig_data->ajax->url         = admin_url( 'admin-ajax.php' );
			$ig_data->ajax->action_name = self::AJAX_ACTION;
			$ig_data->ajax->user_name   = urlencode( $user );
			$ig_data->ajax->nonce_check = wp_create_nonce( self::AJAX_ACTION. $user );
		}

		// Return result
		return $ig_data;

	}


	// Set Ajax request accepting function
	public function ig_cache_update() {

		// Getting posts number
		$user = filter_input( INPUT_GET, 'user' );

		// Check Nonce
		check_ajax_referer( self::AJAX_ACTION. $user, 'nonce_check', true );

		// Get Instagram settings
		$ig_settings = $this->settings['ig'];

		// If empty users data
		if( empty( $ig_settings ) || empty( $ig_settings['users'] ) ) {
			exit( json_encode(
				array(
					'ok'      => false,
					'message' => 'Instagram settings is empty...',
				)
			) );
		}

		// Search ID & token
		$ig_buisiness_id = null;
		$token           = null;
		foreach( $ig_settings['users'] as $u ) {
			if( $u['name'] === $user && ! empty( $u['id'] ) && ! empty( $u['token'] ) ) {
				$ig_buisiness_id = $u['id'];
				$token           = $u['token'];
				break;
			}
		}

		// If not found ID or token
		if( empty( $token ) ) {
			exit( json_encode(
				array(
					'ok'      => false,
					'message' => 'Not found the user; '. $user,
				)
			) );
		}

		// Instagram Graph API request base URL
		$graph_api       = self::API_BASE_URL;
		// Cache file
		$cache_file      = plugin_dir_path( __FILE__ ). 'asset/temp/cache/ig_timeline_'. $ig_buisiness_id. '.dat';
		// Get modified timestamp
		$cache_modified  = @ filemtime( $cache_file ) ?: 0;
		// Set get fields
		$query_fields    = 'name,username,profile_picture_url,media_count,followers_count,follows_count,media.limit('. self::CACHE_DATA_MAX. '){caption,like_count,media_url,permalink,timestamp,thumbnail_url,comments_count,children{media_url,thumbnail_url}}';
		// Check the modified date of cache data
		if( ! empty( $cache_modified ) && ! empty( $ig_api_json ) && ! ( time() - $cache_modified ) > $cache_lifetime ) {
			exit( json_encode(
				array(
					'ok'      => true,
					'message' => 'Cache has not expired, so WP did not request to api',
				)
			) );
		}
		// Request to Instagram Graph API
	//	$ig_api_json_update = @ file_get_contents( "{$graph_api}/{$ig_buisiness_id}?fields={$query_fields}&access_token={$token}" ) ?: '{}';
		$response = wp_remote_get( "{$graph_api}/{$ig_buisiness_id}?fields={$query_fields}&access_token={$token}" );
		$status   = wp_remote_retrieve_response_code( $response );
		// Save to cache
		$ig_api_json_update = wp_remote_retrieve_body( $response );
		if( 200 == $status && ! empty( $ig_api_json ) ) {
		//	@ file_put_contents( $cache_file, $ig_api_json, LOCK_EX );
			$this->file_system->put_contents( $cache_file, $ig_api_json_update );
		}
		// JSON parse
		$ig_data_update = @ json_decode( $ig_api_json_update );
		// Save to cache
		if( empty( $ig_data_update ) || isset( $ig_data_update->error ) ) {
			exit( json_encode(
				array(
					'ok'      => false,
				//	'result'  => $ig_data_update,
				//	'json'    => $ig_api_json_update,
					'message' => 'Cache update failed...',
				)
			) );
		} else{
		//	$cache_update_result = @ file_put_contents( $cache_file, $ig_api_json_update, LOCK_EX );
			$cache_update_result = $this->file_system->put_contents( $cache_file, $ig_api_json_update );
			exit( json_encode(
				array(
					'ok'      => $cache_update_result,
				//	'result'  => $ig_data_update,
				//	'json'    => $ig_api_json_update,
					'message' => 'Cache updated successfully!!',
				)
			) );
		}
	}

}



///////// END /////////


?>