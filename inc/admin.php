<?php
namespace VideoWhisper\PictureGallery;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

trait Admin {

		// ! Settings


		static function getOptions() {

			$options = get_option( 'VWpictureGalleryOptions' );
			if ( ! $options ) {
				return self::adminOptionsDefault();
			}

			if ( empty( $options ) ) {
				return self::adminOptionsDefault();
			}

			return $options;
		}

		static function adminOptionsDefault() {
			$root_url   = get_bloginfo( 'url' ) . '/';
			$upload_dir = wp_upload_dir();

			return array(
				'fixAutoP' => 0, //fix auto paragraphing in shortcodes breaking JS
				'guestMessage' 					=> 'Picture was successfully uploaded and is pending approval.',
				'guestSubject'                 => 'Approve Guest Upload',
				'guestText'                    => 'A guest uploaded a picture. Open this link to review and Publish: ',
				'moderatorEmail'               => get_bloginfo('admin_email'),
		
				'uploadsIPlimit' => 3 ,
				'termsPage'                     => 0,
				'recaptchaSite'                   => '',
				'recaptchaSecret'                 => '',

				'themeMode' => '',
				'interfaceClass' => '', 

				'userName' => 'user_login',
				'listingsMenu'                    => 1, //show menu section for listings

				'mediaLibrary'      => 1,
				'mediaLibraryThumb' => 1,

				'allowDebug'        => '1',

				'rateStarReview'    => '1',

				'editURL'           => get_bloginfo( 'url' ) . '/' . 'edit-content?editID=',
				'editContent'       => 'all',

				'disableSetupPages' => '0',
				'vwls_gallery'      => '1',

				'importPath'        => '/home/[your-account]/public_html/streams/',
				'importClean'       => '45',
				'deleteOnImport'    => '1',

				'vwls_channel'      => '1',

				'custom_post'       => 'picture',
				'custom_taxonomy'   => 'gallery',

				'pictures'          => '1',

				'postTemplate'      => '+plugin',
				'taxonomyTemplate'  => '+plugin',

				'pictureWidth'      => '',

				'thumbWidth'        => '240',
				'thumbHeight'       => '180',
				'perPage'           => '12',

				'shareList'         => 'Super Admin, Administrator, Editor, Author, Contributor, Performer, Provider, Broadcaster',
				'publishList'       => 'Super Admin, Administrator, Editor, Author, Performer, Provider, Broadcaster',

				'role_gallery'      => '1',

				'watchList'         => 'Super Admin, Administrator, Editor, Author, Contributor, Subscriber, Performer, Client, Guest',
				'accessDenied'      => '<h3>Access Denied</h3>
<p>#info#</p>',

				'uploadsPath'       => $upload_dir['basedir'] . '/vw_pictures',
				'customCSSpicture' => '.vwPictureIMG {
					padding: 2px;
					}',
				'customCSS'         => <<<HTMLCODE

.videowhisperPicture
{
position: relative;
display:inline-block;

border:1px solid #aaa;
background-color:#777;
padding: 0px;
margin: 2px;

width: 240px;
height: 180px;
}

.videowhisperPicture:hover {
	border:1px solid #fff;
}

.videowhisperPicture IMG
{
padding: 0px;
margin: 0px;
border: 0px;
}

.videowhisperPictureTitle
{
position: absolute;
top:0px;
left:0px;
margin:8px;
font-size: 14px;
color: #FFF;
text-shadow:1px 1px 1px #333;
}

.videowhisperPictureEdit
{
position: absolute;
top:34px;
right:0px;
margin:8px;
font-size: 11px;
color: #FFF;
text-shadow:1px 1px 1px #333;
background: rgba(0, 100, 255, 0.7);
padding: 3px;
border-radius: 3px;
}

.videowhisperPictureDuration
{
position: absolute;
bottom:5px;
left:0px;
margin:8px;
font-size: 14px;
color: #FFF;
text-shadow:1px 1px 1px #333;
}

.videowhisperPictureDate
{
position: absolute;
bottom:5px;
right:0px;
margin: 8px;
font-size: 11px;
color: #FFF;
text-shadow:1px 1px 1px #333;
}

.videowhisperPictureViews
{
position: absolute;
bottom:16px;
right:0px;
margin: 8px;
font-size: 10px;
color: #FFF;
text-shadow:1px 1px 1px #333;
}

.videowhisperPictureRating
{
position: absolute;
bottom: 5px;
left:5px;
font-size: 15px;
color: #FFF;
text-shadow:1px 1px 1px #333;
z-index: 10;
}

HTMLCODE
				,
				'videowhisper'      => 0,
			);

		}

		static function setupOptions() {

			$adminOptions = self::adminOptionsDefault();

			$options = get_option( 'VWpictureGalleryOptions' );
			if ( ! empty( $options ) ) {
				foreach ( $options as $key => $option ) {
					$adminOptions[ $key ] = $option;
				}
			}
			update_option( 'VWpictureGalleryOptions', $adminOptions );

			return $adminOptions;
		}


		static function adminOptions() {
			
			$options = self::setupOptions();


			if ( isset( $_POST ) ) {
				if ( ! empty( $_POST ) ) {

					$nonce = $_REQUEST['_wpnonce'];
					if ( ! wp_verify_nonce( $nonce, 'vwsec' ) ) {
						echo 'Invalid nonce!';
						exit;
					}

					foreach ( $options as $key => $value ) {
						if ( isset( $_POST[ $key ] ) ) {
						
						if ( in_array( $key, [ 'accessDenied'] ) ) $options[ $key ] = trim( wp_kses_post(  $_POST[ $key ] ) ); //filtered html
						else $options[ $key ] = trim( sanitize_textarea_field( $_POST[ $key ] ) );
						
						}
					}
					
					
					
					update_option( 'VWpictureGalleryOptions', $options );
				}
			}

			self::setupPages();

			$optionsDefault = self::adminOptionsDefault();

			$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'server';
			?>


<div class="wrap">
<h2>Picture Gallery by VideoWhisper.com</h2>
For more details on using this plugin see <a href="admin.php?page=picture-gallery-docs">Documentation</a>.

<h2 class="nav-tab-wrapper">
	<a href="admin.php?page=picture-gallery&tab=server" class="nav-tab <?php echo $active_tab == 'server' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Server', 'picture-gallery' ); ?></a>
	<a href="admin.php?page=picture-gallery&tab=share" class="nav-tab <?php echo $active_tab == 'share' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Share', 'picture-gallery' ); ?></a>
	<a href="admin.php?page=picture-gallery&tab=guest" class="nav-tab <?php echo $active_tab == 'guest' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Guest Upload', 'picture-gallery' ); ?></a>
	<a href="admin.php?page=picture-gallery&tab=display" class="nav-tab <?php echo $active_tab == 'display' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Display', 'picture-gallery' ); ?></a>
	<a href="admin.php?page=picture-gallery&tab=access" class="nav-tab <?php echo $active_tab == 'access' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Access Control', 'picture-gallery' ); ?></a>
</h2>

<form method="post" action="<?php echo wp_nonce_url( $_SERVER['REQUEST_URI'], 'vwsec' ); ?>">

			<?php
			switch ( $active_tab ) {

				// ! guest upload options
				case 'guest':
					?>
					<h3>Guest Picture Upload</h3>
					Enable guests (visitors) to upload pictures with a special shortcode and specific security features to prevent flood/spam.
					Shortcode parameters (fill predefined values to hide these fields):  
<pre>
<code>[videowhisper_picture_upload_guest category="" gallery="" owner="" tag="" description="" terms="" email=""]</code>
category = category id, a dropdown will show if not provided
gallery = galleries (csv), Guest will be used for visitors if not provided
owner = owner id, 0 will be used for guest if not provided
tag = tags (csv), an input will show if not provided
description = post contents (text), a textarea field will show if not provided
terms = Terms of Use url, select default from settings below
email = uploader email, to associate with picture, a field will show if not provided
</pre>

<h4>Google reCAPTCHA v3: Site Key</h4>
<input name="recaptchaSite" type="text" id="recaptchaSite" size="100" maxlength="256" value="<?php echo esc_attr( $options['recaptchaSite'] ); ?>"/>
<br>Register your site for free for using <a href="https://www.google.com/recaptcha/admin/create">Google reCAPTCHA v3</a> to protect your site from spam bot uploads and brute force form submissions. This protection is not active until configured.

<h4>Google reCAPTCHA v3: Secret Key</h4>
<input name="recaptchaSecret" type="text" id="recaptchaSite" size="100" maxlength="256" value="<?php echo esc_attr( $options['recaptchaSecret'] ); ?>"/>

<h4>Limit Uploads per IP</h4>
<input name="uploadsIPlimit" type="text" id="uploadsIPlimit" size="5" maxlength="32" value="<?php echo esc_attr( $options['uploadsIPlimit'] ); ?>"/>
<br>Prevent multiple uploads from same IP, using this plugin. Tracking applies for guest upload shortcode only. Set 0 or blank to disable. Default: <?php echo esc_html( $optionsDefault['uploadsIPlimit'] ); ?>

<h4>Terms Page</h4>
<select name="termsPage" id="termsPage">
<option value='-1'
				<?php
			if ( $options['termsPage'] == -1 )
			{
				echo 'selected';}
?>
>None</option>
				<?php

			$args   = array(
				'sort_order'   => 'asc',
				'sort_column'  => 'post_title',
				'hierarchical' => 1,
				'post_type'    => 'page',
				'post_status'  => 'publish',
			);
			$sPages = get_pages( $args );
			foreach ( $sPages as $sPage )
			{
				echo '<option value="' . esc_attr( $sPage->ID ) . '" ' . ( $options['termsPage'] == ( $sPage->ID ) || ( $options['termsPage'] == 0 && $sPage->post_title == 'Terms of Use' ) ? 'selected' : '' ) . '>' . esc_html( $sPage->post_title ) . '</option>' . "\r\n";
			}
?>
</select>
<br>Site Terms of Use page, to include terms on picture upload page.

<h4>Success Message to Uploader</h4>
<textarea name="guestMessage" id="guestMessage" cols="100" rows="3"><?php echo esc_textarea( $options['guestMessage'] ); ?></textarea>

<h4>Moderator Email</h4>
<input name="moderatorEmail" type="text" id="moderatorEmail" size="64" maxlength="64" value="<?php echo esc_attr( $options['moderatorEmail'] ); ?>"/>
<br>An email is sent to admin/moderator to approve picture. Set blank to disable notifications.

<h4>Subject for Guest Upload Notification</h4>
<input name="guestSubject" type="text" id="guestSubject" size="100" maxlength="256" value="<?php echo esc_attr( $options['guestSubject'] ); ?>"/>
<br> Default: <?php echo esc_html( $optionsDefault['guestSubject'] ); ?>

<h4>Text for Guest Upload Notification Email</h4>
<textarea name="guestText" id="guestText" cols="100" rows="3"><?php echo esc_textarea( $options['guestText'] ); ?></textarea>

					<?php
				break;

				case 'server':
					?>
<h3>Server Configuration / WP Integration</h3>

<h4><?php _e( 'Uploads Path', 'picture-gallery' ); ?></h4>
<p><?php _e( 'Path where picture files will be stored. Make sure you use a location outside plugin folder to avoid losing files on updates and plugin uninstallation.', 'picture-gallery' ); ?></p>
<input name="uploadsPath" type="text" id="uploadsPath" size="80" maxlength="256" value="<?php echo esc_attr( $options['uploadsPath'] ); ?>"/>
<br>Ex: /home/-your-account-/public_html/wp-content/uploads/vw_pictureGallery
<br>If you ever decide to change this, previous files must remain in old location.

<h4>Media Library</h4>
<select name="mediaLibrary" id="mediaLibrary">
  <option value="0" <?php echo $options['mediaLibrary'] ? '' : 'selected'; ?>>No</option>
  <option value="1" <?php echo $options['mediaLibrary'] ? 'selected' : ''; ?>>Yes</option>
</select>
<br>Add uploaded pictures to WordPress Media Library.

<h4>Media Library Thumb</h4>
<select name="mediaLibraryThumb" id="mediaLibraryThumb">
  <option value="0" <?php echo $options['mediaLibraryThumb'] ? '' : 'selected'; ?>>No</option>
  <option value="1" <?php echo $options['mediaLibraryThumb'] ? 'selected' : ''; ?>>Yes</option>
</select>
<br>Add thumb to WordPress Media Library and set as featured image.

<h4>Setup Pages</h4>
<select name="disableSetupPages" id="disableSetupPages">
  <option value="0" <?php echo $options['disableSetupPages'] ? '' : 'selected'; ?>>Yes</option>
  <option value="1" <?php echo $options['disableSetupPages'] ? 'selected' : ''; ?>>No</option>
</select>
<br>Create pages for main functionality. Also creates a menu with these pages (VideoWhisper) that can be added to themes. If you delete the pages this option recreates these if not disabled. If you disable Setup Pages, check <a href="admin.php?page=picture-gallery-docs">Documentation</a> for shortcodes to manually use in your own pages.

<h3>Recommended Plugins</h3>
<h4><a target="_plugin" href="https://wordpress.org/plugins/paid-membership/">MicroPayments - Frontend Content Management & Monetization</a> - Manage content including pictures from frontend</h4>
					<?php
					if ( is_plugin_active( 'paid-membership/paid-membership.php' ) ) {
						echo 'Detected:  <a href="admin.php?page=paid-membership">Configure</a>';
					} else {
						echo 'Not detected. Please install and activate MicroPayments by VideoWhisper.com from <a href="plugin-install.php">Plugins > Add New</a>!';
					}
					?>

<h4><a target="_plugin" href="https://wordpress.org/plugins/rate-star-review/">Rate Star Review</a> - Enable frontend content reviews</h4>
					<?php
					if ( is_plugin_active( 'rate-star-review/rate-star-review.php' ) ) {
						echo 'Detected:  <a href="admin.php?page=rate-star-review">Configure</a>';
					} else {
						echo 'Not detected. Please install and activate Rate Star Review by VideoWhisper.com from <a href="plugin-install.php">Plugins > Add New</a>!';
					}
					?>
<BR><select name="rateStarReview" id="rateStarReview">
  <option value="0" <?php echo $options['rateStarReview'] ? '' : 'selected'; ?>>No</option>
  <option value="1" <?php echo $options['rateStarReview'] ? 'selected' : ''; ?>>Yes</option>
</select>
<br>Enables Rate Star Review integration. Shows star ratings on listings and review form, reviews on item pages.
					<?php
					break;

				case 'display':
					// ! display options

					$options['customCSS']       = htmlentities( stripslashes( $options['customCSS'] ) );
					$options['customCSSpicture']       = htmlentities( stripslashes( $options['customCSSpicture'] ) );

					$options['custom_post']     = preg_replace( '/[^\da-z]/i', '', strtolower( $options['custom_post'] ) );
					$options['custom_taxonomy'] = preg_replace( '/[^\da-z]/i', '', strtolower( $options['custom_taxonomy'] ) );

					?>
<h3><?php _e( 'Display &amp; Listings', 'picture-gallery' ); ?></h3>

<h4>Theme Mode (Dark/Light/Auto)</h4> 
<select name="themeMode" id="themeMode">
  <option value="" <?php echo $options['themeMode'] ? '' : 'selected'; ?>>None</option>
  <option value="light" <?php echo $options['themeMode'] == 'light' ? 'selected' : ''; ?>>Light Mode</option>
  <option value="dark" <?php echo $options['themeMode'] == 'dark' ? 'selected' : ''; ?>>Dark Mode</option>
  <option value="auto" <?php echo $options['themeMode'] == 'auto' ? 'selected' : ''; ?>>Auto Mode</option>
</select>
<br>This will use JS to apply ".inverted" class to Fomantic ".ui" elements mainly on AJAX listings. When using the <a href="https://fanspaysite.com/theme">FansPaysSite theme</a> this will be discarded and the dynamic theme mode will be used.

<h4>Interface Class(es)</h4>
<input name="interfaceClass" type="text" id="interfaceClass" size="30" maxlength="128" value="<?php echo esc_attr( $options['interfaceClass'] ?? '' ); ?>"/>
<br>Extra class to apply to interface (using Semantic UI). Use inverted when theme uses a static dark mode (a dark background with white text) or for contrast. Ex: inverted
<br>Some common Semantic UI classes: inverted = dark mode or contrast, basic = no formatting, secondary/tertiary = greys, red/orange/yellow/olive/green/teal/blue/violet/purple/pink/brown/grey/black = colors . Multiple classes can be combined, divided by space. Ex: inverted, basic pink, secondary green, secondary 

<h4>Listings Menu</h4>
<select name="listingsMenu" id="listingsMenu">
  <option value="1" <?php echo $options['listingsMenu'] == '1' ? 'selected' : ''; ?>>Menu</option>
  <option value="0" <?php echo $options['listingsMenu'] == '0' ? 'selected' : ''; ?>>Dropdowns</option>
</select>
<br>Show categories and order options as menu.


<h4>Setup Pages</h4>
<select name="disableSetupPages" id="disableSetupPages">
  <option value="0" <?php echo $options['disableSetupPages'] ? '' : 'selected'; ?>>Yes</option>
  <option value="1" <?php echo $options['disableSetupPages'] ? 'selected' : ''; ?>>No</option>
</select>
<br>Create pages for main functionality. Also creates a menu with these pages (VideoWhisper) that can be added to themes. If you delete the pages this option recreates these if not disabled. If you disable Setup Pages, check <a href="admin.php?page=picture-gallery-docs">Documentation</a> for shortcodes to manually use in your own pages.

<h4>Picture Post Name</h4>
<input name="custom_post" type="text" id="custom_post" size="12" maxlength="32" value="<?php echo esc_attr( $options['custom_post'] ); ?>"/>
<br>Custom post name for pictures (only alphanumeric, lower case). Will be used for picture urls. Ex: video
<br><a href="options-permalink.php">Save permalinks</a> to activate new url scheme.
<br>Warning: Changing post type name at runtime will hide previously added items. Previous posts will only show when their post type name is restored.

<h4>Picture Post Taxonomy Name</h4>
<input name="custom_taxonomy" type="text" id="custom_taxonomy" size="12" maxlength="32" value="<?php echo esc_attr( $options['custom_taxonomy'] ); ?>"/>
<br>Special taxonomy for organising pictures. Ex: gallery

<h4><?php _e( 'Default Pictures Per Page', 'picture-gallery' ); ?></h4>
<input name="perPage" type="text" id="perPage" size="3" maxlength="3" value="<?php echo esc_attr( $options['perPage'] ); ?>"/>


<h4><?php _e( 'Thumbnail Width', 'picture-gallery' ); ?></h4>
<input name="thumbWidth" type="text" id="thumbWidth" size="4" maxlength="4" value="<?php echo esc_attr( $options['thumbWidth'] ); ?>"/>

<h4><?php _e( 'Thumbnail Height', 'picture-gallery' ); ?></h4>
<input name="thumbHeight" type="text" id="thumbHeight" size="4" maxlength="4" value="<?php echo esc_attr( $options['thumbHeight'] ); ?>"/>

<h4>Picture Post Template Filename</h4>
<input name="postTemplate" type="text" id="postTemplate" size="20" maxlength="64" value="<?php echo esc_attr( $options['postTemplate'] ); ?>"/>
<br>Template file located in current theme folder, that should be used to render webcam post page. Ex: page.php, single.php
					<?php
					if ( $options['postTemplate'] != '+plugin' ) {
						$single_template = get_template_directory() . '/' . sanitize_text_field( $options['postTemplate'] );
						echo '<br>' . esc_html( $single_template ) . ' : ';
						if ( file_exists( $single_template ) ) {
							echo 'Found.';
						} else {
							echo 'Not Found! Use another theme file!';
						}
					}
					?>
<br>Set "+plugin" to use a template provided by this plugin, instead of theme templates.


<h4>Gallery Template Filename</h4>
<input name="taxonomyTemplate" type="text" id="taxonomyTemplate" size="20" maxlength="64" value="<?php echo esc_attr( $options['taxonomyTemplate'] ); ?>"/>
<br>Template file located in current theme folder, that should be used to render gallery post page. Ex: page.php, single.php
					<?php
					if ( $options['postTemplate'] != '+plugin' ) {
						$single_template = get_template_directory() . '/' . sanitize_text_field( $options['taxonomyTemplate'] );
						echo '<br>' . esc_html( $single_template ) . ' : ';
						if ( file_exists( $single_template ) ) {
							echo 'Found.';
						} else {
							echo 'Not Found! Use another theme file!';
						}
					}
					?>
<br>Set "+plugin" to use a template provided by this plugin, instead of theme templates.
<?php
	
$current_user = wp_get_current_user();
?>

<h4>Username</h4>
<select name="userName" id="userName">
  <option value="display_name" <?php echo $options['userName'] == 'display_name' ? 'selected' : ''; ?>>Display Name (<?php echo esc_html( $current_user->display_name ); ?>)</option>
  <option value="user_login" <?php echo $options['userName'] == 'user_login' ? 'selected' : ''; ?>>Login (<?php echo esc_html( $current_user->user_login ); ?>)</option>
  <option value="user_nicename" <?php echo $options['userName'] == 'user_nicename' ? 'selected' : ''; ?>>Nicename (<?php echo esc_html( $current_user->user_nicename ); ?>)</option>
  <option value="ID" <?php echo $options['userName'] == 'ID' ? 'selected' : ''; ?>>ID (<?php echo esc_html( $current_user->ID ); ?>)</option>
</select>
<br>Used for default user gallery. Your username with current settings:
					<?php
					$userName = sanitize_text_field( $options['userName'] ?? '' );
					if ( ! $userName ) {
						$userName = 'user_nicename';
					}
					echo esc_html( $username = $current_user->$userName  ?? 'undefined' );
					?>

<h4>Custom CSS for Listings</h4>
<textarea name="customCSS" id="customCSS" cols="64" rows="5"><?php echo esc_textarea( $options['customCSS'] ); ?></textarea>
<BR><?php _e( 'Styling used in elements added by this plugin. Should not include CSS container &lt;style type=&quot;text/css&quot;&gt; &lt;/style&gt; .', 'picture-gallery' ); ?>
Default:<br><textarea readonly cols="100" rows="3"><?php echo esc_textarea( $optionsDefault['customCSS'] ); ?></textarea>

<h4>Custom CSS for Picture</h4>
<textarea name="customCSSpicture" id="customCSSpicture" cols="64" rows="5"><?php echo esc_textarea( $options['customCSSpicture'] ); ?></textarea>
<BR>Picture IMG has these classes: ui fluid image vwPictureIMG
<BR>Default:<br><textarea readonly cols="100" rows="3"><?php echo esc_textarea( $optionsDefault['customCSSpicture'] ); ?></textarea>

<h4>Fix WP Auto Paragraphing</h4>
<select name="fixAutoP" id="fixAutoP">
  <option value="0" <?php echo $options['fixAutoP'] ? '' : 'selected'; ?>>No</option>
  <option value="1" <?php echo $options['fixAutoP'] ? 'selected' : ''; ?>>Yes</option>
</select>
<br>Enable if JS breaks due to auto paragraphing. 

<h4><?php _e( 'Show VideoWhisper Powered by', 'picture-gallery' ); ?></h4>
<select name="videowhisper" id="videowhisper">
  <option value="0" <?php echo $options['videowhisper'] ? '' : 'selected'; ?>>No</option>
  <option value="1" <?php echo $options['videowhisper'] ? 'selected' : ''; ?>>Yes</option>
</select>
<br>
					<?php
					_e(
						'Show a mention that pictures were posted with VideoWhisper plugin.
',
						'picture-gallery'
					);
					?>
					<?php
					break;

				case 'share':
					// ! share options
					?>
<h3><?php _e( 'Picture Sharing', 'picture-gallery' ); ?></h3>

<h4><?php _e( 'Users allowed to share pictures', 'picture-gallery' ); ?></h4>
<textarea name="shareList" cols="64" rows="2" id="shareList"><?php echo esc_textarea( $options['shareList'] ); ?></textarea>
<BR><?php _e( 'Who can share pictures: comma separated Roles, user Emails, user ID numbers.', 'picture-gallery' ); ?>
<BR><?php _e( '"Guest" will allow everybody including guests (unregistered users).', 'picture-gallery' ); ?>

<h4><?php _e( 'Users allowed to directly publish pictures', 'picture-gallery' ); ?></h4>
<textarea name="publishList" cols="64" rows="2" id="publishList"><?php echo esc_html( $options['publishList'] ); ?></textarea>
<BR><?php _e( 'Users not in this list will add pictures as "pending".', 'picture-gallery' ); ?>
<BR><?php _e( 'Who can publish pictures: comma separated Roles, user Emails, user ID numbers.', 'picture-gallery' ); ?>
<BR><?php _e( '"Guest" will allow everybody including guests (unregistered users).', 'picture-gallery' ); ?>

<br><br> - Your roles (for troubleshooting):
					<?php
					global $current_user;
					foreach ( $current_user->roles as $role ) {
						echo esc_html( $role ) . ' ';
					}
					?>
			<br> - Current WordPress roles:
					<?php
					global $wp_roles;
					foreach ( $wp_roles->roles as $role_slug => $role ) {
						echo esc_html( $role_slug ) . '= "' . esc_html( $role['name'] ) . '" ';
					}
					?>

					<?php
					break;

				case 'access':
					// ! vod options
					$options['accessDenied'] = stripslashes( $options['accessDenied'] );

					?>
<h3>Membership / Content On Demand</h3>

<h4>Members allowed to watch picture</h4>
<textarea name="watchList" cols="64" rows="3" id="watchList"><?php echo esc_textarea( $options['watchList'] ); ?></textarea>
<BR>Global picture access list: comma separated Roles, user Emails, user ID numbers. Ex: <i>Subscriber, Author, submit.ticket@videowhisper.com, 1</i>
<BR>"Guest" will allow everybody including guests (unregistered users) to watch pictures.

<h4>Role galleries</h4>
Enables access by role galleries: Assign picture to a gallery that is a role name.
<br><select name="role_gallery" id="role_gallery">
  <option value="1" <?php echo $options['role_gallery'] ? 'selected' : ''; ?>>Yes</option>
  <option value="0" <?php echo $options['role_gallery'] ? '' : 'selected'; ?>>No</option>
</select>
<br>Multiple roles can be assigned to same picture. User can have any of the assigned roles, to watch. If user has required role, access is granted even if not in global access list.
<br>Pictures without role galleries are accessible as per global picture access.

<h4>Exceptions</h4>
Assign pictures to these galleries:
<br><b>free</b> : Anybody can watch, including guests.
<br><b>registered</b> : All members can watch.
<br><b>unpublished</b> : Picture is not accessible.

<h4>Access denied message</h4>
<textarea name="accessDenied" cols="64" rows="3" id="accessDenied"><?php echo esc_textarea( $options['accessDenied'] ); ?>
</textarea>
<BR>HTML info, shows with preview if user does not have access to watch picture.
<br>Including #info# will mention rule that was applied.


<h4>Paid Membership and Content</h4>
Solution was tested and developed in combination with <a href="https://wordpress.org/plugins/paid-membership/">Paid Membership and Content</a>: Sell membership and content based on virtual wallet credits/tokens. Credits/tokens can be purchased with real money. This plugin also allows users to sell individual pictures (will get an edit button to set price and duration).
<BR>Paid Membership and Content:
					<?php

					if ( is_plugin_active( 'paid-membership/paid-membership.php' ) ) {
						echo '<a href="admin.php?page=paid-membership">Detected</a>';

						$optionsPM = get_option( 'VWpaidMembershipOptions' );
						if ( $optionsPM['p_videowhisper_content_edit'] ) {
							$editURL = add_query_arg( 'editID', '', get_permalink( sanitize_text_field( $optionsPM['p_videowhisper_content_edit'] ) ) ) . '=';
						}
					} else {
						echo 'Not detected. Please install and activate <a target="_mycred" href="https://wordpress.org/plugins/paid-membership/">Paid Membership and Content with Credits</a> from <a href="plugin-install.php">Plugins > Add New</a>!';
					}

					?>

<h4>Frontend Contend Edit</h4>
<select name="editContent" id="editContent">
  <option value="0" <?php echo $options['editContent'] ? '' : 'selected'; ?>>No</option>
  <option value="all" <?php echo $options['editContent'] ? 'selected' : ''; ?>>Yes</option>
</select>
<br>Allow owner and admin to edit content options for videos, from frontend. This will show an edit button on listings that can be edited by current user.

<h4>Edit Content URL</h4>
<input name="editURL" type="text" id="editURL" size="100" maxlength="256" value="<?php echo esc_attr( $options['editURL'] ); ?>"/>
<BR>Detected: <?php echo esc_html( $editURL ); ?>


					<?php
					break;

			}

			if ( ! in_array( $active_tab, array( 'shortcodes' ) ) ) {
				submit_button();
			}
			?>

</form>
</div>
			<?php
		}

		static function adminImport() {
			$options = self::setupOptions();

			if ( isset( $_POST ) ) {
				if ( ! empty( $_POST ) ) {

					$nonce = $_REQUEST['_wpnonce'];
					if ( ! wp_verify_nonce( $nonce, 'vwsec' ) ) {
						echo 'Invalid nonce!';
						exit;
					}

					foreach ( $options as $key => $value ) {
						if ( isset( $_POST[ $key ] ) ) {
							$options[ $key ] = trim( sanitize_textarea_field( $_POST[ $key ] ) );
						}
					}
					update_option( 'VWpictureGalleryOptions', $options );
				}
			}

			?>
<h2>Import Pictures from Folder</h2>
	Use this to mass import any number of pictures already existent on server.

			<?php
			if ( file_exists( $options['importPath'] ) ) {
				echo do_shortcode( '[videowhisper_picture_import path="' . esc_attr( $options['importPath'] ) . '"]' );
			} else {
				echo 'Import folder not found on server: ' . esc_html( $options['importPath'] );
			}
			?>
<h3>Import Settings</h3>
<form method="post" action="<?php echo wp_nonce_url( $_SERVER['REQUEST_URI'], 'vwsec' ); ?>">
<h4>Import Path</h4>
<p>Server path to import pictures from</p>
<input name="importPath" type="text" id="importPath" size="100" maxlength="256" value="<?php echo esc_attr( $options['importPath'] ); ?>"/>
<br>Ex: /home/[youraccount]/public_html/streams/
<h4>Delete Original on Import</h4>
<select name="deleteOnImport" id="deleteOnImport">
  <option value="1" <?php echo $options['deleteOnImport'] ? 'selected' : ''; ?>>Yes</option>
  <option value="0" <?php echo $options['deleteOnImport'] ? '' : 'selected'; ?>>No</option>
</select>
<br>Remove original file after copy to new location.
<h4>Import Clean</h4>
<p>Delete pictures older than:</p>
<input name="importClean" type="text" id="importClean" size="5" maxlength="8" value="<?php echo esc_attr( $options['importClean'] ); ?>"/>days
<br>Set 0 to disable automated cleanup. Cleanup does not occur more often than 10h to prevent high load.
			<?php submit_button(); ?>
</form>
			<?php

		}

				// ! Feature Pages and Menus
				static function setupPages() {
					$options = get_option( 'VWpictureGalleryOptions' );
					if ( $options['disableSetupPages'] ) {
						return;
					}
		
					$pages = array(
						'videowhisper_pictures'       => 'Pictures',
						'videowhisper_picture_upload' => 'Upload Pictures',
						'videowhisper_picture_upload_guest' => 'Guest Picture',
					);
		
					$noMenu = array();
		
					$parents = array(
						'videowhisper_picture_upload' => array( 'Peformer', 'Performer Dashboard', 'Channels', 'Videos', 'Creator' ),
						'videowhisper_pictures'       => array( 'Webcams', 'Channels', 'Videos' ),
						'videowhisper_picture_upload_guest' => array( 'Pictures', 'Content', 'Support' ),
					);
		
					$duplicate = array();
		
					// create a menu and add pages
					$menu_name   = 'VideoWhisper';
					$menu_exists = wp_get_nav_menu_object( $menu_name );
		
					if ( ! $menu_exists ) {
						$menu_id = wp_create_nav_menu( $menu_name );
					} else {
						$menu_id = $menu_exists->term_id;
					}
		
					// create pages if not created or existant
					foreach ( $pages as $key => $value ) {
		
						$pid = $options[ 'p_' . $key ] ?? 0;
						
						$page = null;
						if ( $pid ) {
							$page = get_post( $pid );
						}
						
						if ( ! $page ) {
							$pid = 0;
						}
		
						if ( ! $pid ) {
							// page exists (by shortcode title)
							global $wpdb;
							$pidE = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_name = '" . $value . "'" );
		
							if ( $pidE ) {
								$pid = $pidE;
							} else {
		
								$page                   = array();
								$page['post_type']      = 'page';
								$page['post_content']   = '[' . $key . ']';
								$page['post_parent']    = 0;
								$page['post_status']    = 'publish';
								$page['post_title']     = $value;
								$page['comment_status'] = 'closed';
		
								$pid = wp_insert_post( $page );
							}
		
							$options[ 'p_' . $key ] = $pid;
							$link                   = get_permalink( $pid );
		
							// get updated menu
							$menuItems = wp_get_nav_menu_items( $menu_id, array( 'output' => ARRAY_A ) );
		
							// find if menu exists, to update
							$foundID = 0;
							foreach ( $menuItems as $menuitem ) {
								if ( $menuitem->title == $value ) {
									$foundID = $menuitem->ID;
									break;
								}
							}
		
							if ( ! in_array( $key, $noMenu ) ) {
								if ( $menu_id ) {
									// select menu parent
									$parentID = 0;
									if ( array_key_exists( $key, $parents ) ) {
										foreach ( $parents[ $key ] as $parent ) {
											foreach ( $menuItems as $menuitem ) {
												if ( $menuitem->title == $parent ) {
																			$parentID = $menuitem->ID;
																			break 2;
												}
											}
										}
									}
									// update menu for page
									$updateID = wp_update_nav_menu_item(
											$menu_id,
											$foundID,
											array(
												'menu-item-title'  => $value,
												'menu-item-url'    => $link,
												'menu-item-status' => 'publish',
												'menu-item-object-id' => $pid,
												'menu-item-object' => 'page',
												'menu-item-type'   => 'post_type',
												'menu-item-parent-id' => $parentID,
											)
										);
		
									// duplicate menu, only first time for main menu
									if ( ! $foundID ) {
										if ( ! $parentID ) {
											if ( intval( $updateID ) ) {
												if ( in_array( $key, $duplicate ) ) {
													wp_update_nav_menu_item(
														$menu_id,
														0,
														array(
															'menu-item-title'  => $value,
															'menu-item-url'    => $link,
															'menu-item-status' => 'publish',
															'menu-item-object-id' => $pid,
															'menu-item-object' => 'page',
															'menu-item-type'   => 'post_type',
															'menu-item-parent-id' => $updateID,
														)
													);
												}
											}
										}
									}
								}
							}
						}
					}
		
					update_option( 'VWpictureGalleryOptions', $options );
				}

				static function admin_menu() {
					$options = get_option( 'VWpictureGalleryOptions' );
	   
				   add_menu_page( 'Picture Gallery', 'Picture Gallery', 'manage_options', 'picture-gallery', array( 'VWpictureGallery', 'adminOptions' ), 'dashicons-images-alt2', 81 );
				   add_submenu_page( 'picture-gallery', 'Picture Gallery', 'Options', 'manage_options', 'picture-gallery', array( 'VWpictureGallery', 'adminOptions' ) );
				   add_submenu_page( 'picture-gallery', 'Upload', 'Upload', 'manage_options', 'picture-gallery-upload', array( 'VWpictureGallery', 'adminUpload' ) );
				   add_submenu_page( 'picture-gallery', 'Import', 'Import', 'manage_options', 'picture-gallery-import', array( 'VWpictureGallery', 'adminImport' ) );
				   add_submenu_page( 'picture-gallery', 'Manage', 'Manage', 'manage_options', 'picture-gallery-manage', array( 'VWpictureGallery', 'adminManage' ) );
				   add_submenu_page( 'picture-gallery', 'Documentation', 'Documentation', 'manage_options', 'picture-gallery-docs', array( 'VWpictureGallery', 'adminDocs' ) );
	   
			   }
	   
			   static function admin_bar_menu( $wp_admin_bar ) {
				   if ( ! is_user_logged_in() ) {
					   return;
				   }
	   
				   $options = self::getOptions();
	   
				   if ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) {
	   
					   // find VideoWhisper menu
					   $nodes = $wp_admin_bar->get_nodes();
					   if ( ! $nodes ) {
						   $nodes = array();
					   }
					   $found = 0;
					   foreach ( $nodes as $node ) {
						   if ( $node->title == 'VideoWhisper' ) {
							   $found = 1;
						   }
					   }
	   
					   if ( ! $found ) {
						   $wp_admin_bar->add_node(
							   array(
								   'id'    => 'videowhisper',
								   'title' => '👁 VideoWhisper',
								   'href'  => admin_url( 'plugin-install.php?s=videowhisper&tab=search&type=term' ),
							   )
						   );
	   
						   // more VideoWhisper menus
						   $wp_admin_bar->add_node(
							   array(
								   'parent' => 'videowhisper',
								   'id'     => 'videowhisper-add',
								   'title'  => __( 'Add Plugins', 'paid-membership' ),
								   'href'   => admin_url( 'plugin-install.php?s=videowhisper&tab=search&type=term' ),
							   )
						   );
	   
						   $wp_admin_bar->add_node(
							array(
								'parent' => 'videowhisper',
								'id'     => 'videowhisper-consult',
								'title'  => __( 'Consult Developers', 'paid-membership' ),
								'href'   => 'https://consult.videowhisper.com/'),
							);

					   }
	   
					   $menu_id = 'videowhisper-picturegallery';
	   
					   $wp_admin_bar->add_node(
						   array(
							   'parent' => 'videowhisper',
							   'id'     => $menu_id,
							   'title'  => '📸 Picture Gallery',
							   'href'   => admin_url( 'admin.php?page=picture-gallery' ),
						   )
					   );
	   
						   $wp_admin_bar->add_node(
							   array(
								   'parent' => $menu_id,
								   'id'     => $menu_id . '-settings',
								   'title'  => __( 'Settings', 'ppv-live-webcams' ),
								   'href'   => admin_url( 'admin.php?page=picture-gallery' ),
							   )
						   );
	   
						   $wp_admin_bar->add_node(
							array(
								'parent' => $menu_id,
								'id'     => $menu_id . '-posts',
								'title'  => __( 'Picture Posts', 'ppv-live-webcams' ),
								'href'   => admin_url( 'edit.php?post_type=' . $options['custom_post'] ),
							)
						);

					   $wp_admin_bar->add_node(
						   array(
							   'parent' => $menu_id,
							   'id'     => $menu_id . '-upload',
							   'title'  => __( 'Upload', 'ppv-live-webcams' ),
							   'href'   => admin_url( 'admin.php?page=picture-gallery-upload' ),
						   )
					   );
	   
					   $wp_admin_bar->add_node(
						   array(
							   'parent' => $menu_id,
							   'id'     => $menu_id . '-import',
							   'title'  => __( 'Import', 'ppv-live-webcams' ),
							   'href'   => admin_url( 'admin.php?page=picture-gallery-import' ),
						   )
					   );
	   
					   $wp_admin_bar->add_node(
						   array(
							   'parent' => $menu_id,
							   'id'     => $menu_id . '-manage',
							   'title'  => __( 'Manage', 'ppv-live-webcams' ),
							   'href'   => admin_url( 'admin.php?page=picture-gallery-manage' ),
						   )
					   );
	   
					   $wp_admin_bar->add_node(
						   array(
							   'parent' => $menu_id,
							   'id'     => $menu_id . '-docs',
							   'title'  => __( 'Documentation', 'ppv-live-webcams' ),
							   'href'   => admin_url( 'admin.php?page=picture-gallery-docs' ),
						   )
					   );
	   
					   $wp_admin_bar->add_node(
						   array(
							   'parent' => $menu_id,
							   'id'     => $menu_id . '-wpdiscuss',
							   'title'  => __( 'Discuss WP Plugin', 'ppv-live-webcams' ),
							   'href'   => 'https://wordpress.org/support/plugin/picture-gallery/',
						   )
					   );
	   
					   $wp_admin_bar->add_node(
						   array(
							   'parent' => $menu_id,
							   'id'     => $menu_id . '-wpreview',
							   'title'  => __( 'Review WP Plugin', 'ppv-live-webcams' ),
							   'href'   => 'https://wordpress.org/support/plugin/picture-gallery/reviews/#new-post',
						   )
					   );
	   
					   $wp_admin_bar->add_node(
						   array(
							   'parent' => $menu_id,
							   'id'     => $menu_id . '-vsv',
							   'title'  => __( 'Video Hosting', 'ppv-live-webcams' ),
							   'href'   => 'https://videosharevod.com/hosting/',
						   )
					   );
	   
					   $wp_admin_bar->add_node(
						   array(
							   'parent' => $menu_id,
							   'id'     => $menu_id . '-webrtc',
							   'title'  => __( 'Live Stream Hosting', 'ppv-live-webcams' ),
							   'href'   => 'https://webrtchost.com/hosting-plans/',
						   )
					   );
	   
					   $wp_admin_bar->add_node(
						   array(
							   'parent' => $menu_id,
							   'id'     => $menu_id . '-turnkey',
							   'title'  => __( 'Full Feature Plans', 'ppv-live-webcams' ),
							   'href'   => 'https://paidvideochat.com/order/',
						   )
					   );
				   }
	   
			   }
	   
			   static function settings_link( $links ) {
				   $settings_link = '<a href="admin.php?page=picture-gallery">' . __( 'Settings' ) . '</a>';
				   array_unshift( $links, $settings_link );
				   return $links;
			   }

					// ! admin listing
		static function columns_head_picture( $defaults ) {
			$defaults['featured_image'] = 'Thumbnail';
			return $defaults;
		}



		static function columns_content_picture( $column_name, $post_id ) {

			if ( $column_name == 'featured_image' ) {

				$post_thumbnail_id = get_post_thumbnail_id( $post_id );

				if ( $post_thumbnail_id ) {

					$post_featured_image = wp_get_attachment_image_src( $post_thumbnail_id, 'featured_preview' );

					if ( $post_featured_image ) {
						// correct url

						$upload_dir  = wp_upload_dir();
						$uploads_url = self::path2url( $upload_dir['basedir'] );

						$iurl    = $post_featured_image[0];
						$relPath = substr( $iurl, strlen( $uploads_url ) );

						if ( file_exists( $relPath ) ) {
							$rurl = self::path2url( $relPath );
						} else {
							$rurl = $iurl;
						}

						echo '<img src="' . esc_url( $rurl ) . '" />';
					}
				} else {
					echo 'Generating ... ';
					self::updatePostThumbnail( $post_id );

				}

				echo '<br><a href="' . add_query_arg( array( 'updateInfo' => $post_id ), admin_url( 'admin.php?page=picture-gallery-manage' ) ) . '">' . __( 'Update Picture Info', 'picture-gallery' ) . '</a>';
				$url = add_query_arg( array( 'updateThumb' => $post_id ), admin_url( 'admin.php?page=picture-gallery-manage' ) );
				echo '<br><a href="' . esc_url( $url ) . '">' . __( 'Update Thumbnail', 'picture-gallery' ) . '</a>';

			}

		}


		static function adminUpload() {
			?>
		<div class="wrap">
		<h2>Upload - Picture Gallery by VideoWhisper.com</h2>
			<?php
			echo do_shortcode( '[videowhisper_picture_upload]' );
			?>
		Use this page to upload one or multiple pictures to server. Configure category, galleries and then choose files or drag and drop files to upload area.
		<br>Gallery(s): Assign pictures to multiple galleries, as comma separated values. Ex: subscriber, premium

		</div>
			<?php
		}

		static function adminManage() {
			$options = get_option( 'VWpictureGalleryOptions' );

			?>
		<div class="wrap">
		<h2>Manage Picture - Picture Gallery by VideoWhisper.com</h2>
		<a href="edit.php?post_type=<?php echo esc_attr( $options['custom_post'] ); ?>">Manage from Pictures Menu</a>
		<BR>
			<?php

			if ( $update_id = intval( $_GET['updateInfo'] ?? 0 ) ) {
				echo '<BR>Updating Picture #' . esc_html( $update_id ) . '... <br>';
				self::updatePicture( $update_id, true, true );
				unset( $_GET['updateInfo'] );

			}

			if ( $update_id = intval( $_GET['updateThumb'] ?? 0 ) ) {
				echo '<BR>Updating Thumbnail for Picture #' . esc_html( $update_id ) . '... <br>';
				self::updatePostThumbnail( $update_id, true, true );
				unset( $_GET['updateThumb'] );
			}

		}

		// ! Documentation
		static function adminDocs() {
			?>
		<div class="wrap">
		<h2>Documentation: Picture Gallery by VideoWhisper.com</h2>
		
		You can configure this plugin from <a href="admin.php?page=picture-gallery">Settings</a>.
		<br>This plugin creates 2 pages for your site users to access functionality: Pictures, Upload Pictures. You can add these to your menus or use shortcodes below to add functionality to own pages or posts. Integrates with <a href="https://paidvideochat.com">PaidVideochat - Turnkey Cam Site</a> for webcam room profile pictures, saving automated snapshots.
		

<h3>Links</h3>
<UL>
<LI><a href="https://consult.videowhisper.com/">Consult VideoWhisper: Support or Custom Development</a></LI>
<LI><a href="https://wordpress.org/plugins/picture-gallery/">WordPress Plugin Page</a></LI>
<LI><a href="https://wordpress.org/support/plugin/picture-gallery/">Plugin Forum: Discuss with other users</a></LI>
<LI><a href="https://wordpress.org/support/plugin/picture-gallery/reviews/#new-post">Review this Plugin</a></LI>
</UL>


		<h3>Shortcodes</h3>
		
		<h4>[videowhisper_pictures galleries="" category_id="" order_by="" perpage="" perrow="" select_category="1" select_tags="1" select_name="1" select_order="1" select_page="1" include_css="1" id=""]</h4>
		Displays pictures list. Loads and updates by AJAX. Optional parameters: picture gallery name, maximum pictures per page, maximum pictures per row.
		<br>order_by: post_date / picture-views / picture-lastview
		<br>select attributes enable controls to select category, order, page
		<br>include_css: includes the styles (disable if already loaded once on same page)
		<br>id is used to allow multiple instances on same page (leave blank to generate)

		<h4>[videowhisper_picture_upload_guest gallery="" category="" tags="" description="" owner="" terms="" email=""]</h4>
		Displays interface to upload pictures, for guests with special features like Google reCaptcha integration (configurable from settings).
		<br>gallery: A special taxonomy for pictures. Guest will be used for visitors if not provided.
		<br>category: ID of category. If not defined a dropdown is shown.
		<br>tags: Tags to be assigned to picture. Input field will be shown if not provided.
		<br>description: Description to be assigned to picture. Input field will be shown if not provided.
		<br>owner: Owner user id. You can set the id of user where guest content should be assigned.
		<br>email: If nothing is provided user will be asked to fill an email.
		<br>terms: Terms of Use page URL. Can be configured from settings to a site page.


		<h4>[videowhisper_picture_upload gallery="" category="" owner=""]</h4>
		Displays interface to upload pictures.
		<br>gallery: If not defined owner name is used as gallery for regular users. Admins with edit_users capability can write any gallery name. Multiple galleries can be provided as comma separated values.
		<br>category: ID of category. If not define a dropdown is listed.
		<br>owner: User is default owner. Only admins with edit_users capability can use different.


	   <h4>[videowhisper_picture_import path="" gallery="" category="" owner=""]</h4>
		Displays interface to import pictures.
		<br>path: Path where to import from.
		<br>gallery: If not defined owner name is used as gallery for regular users. Admins with edit_users capability can write any gallery name. Multiple galleries can be provided as comma separated values.
		<br>category: ID of category. If not defined a dropdown is listed.
		<br>owner: User is default owner. Only admins with edit_users capability can use different.

		<h4>[videowhisper_picture picture="0" player="" width=""]</h4>
		Displays video player. Video post ID is required.
		<br>Player: html5/html5-mobile/strobe/strobe-rtmp/html5-hls/ blank to use settings & detection
		<br>Width: Force a fixed width in pixels (ex: 640) and height will be adjusted to maintain aspect ratio. Leave blank to use video size.

		<h4>[videowhisper_picture_preview video="0"]</h4>
		Displays video preview (thumbnail) with link to picture post. Picture post ID is required.

	<h4>[videowhisper_postpictures post="post id"]</h4>
		Manage post associated pictures. Required: post

	<h4>[videowhisper_postpictures_process post="" post_type=""]</h4>
		Process post associated pictures (needs to be on same page with [videowhisper_postpictures] for that to work).

		<h3>Troubleshooting</h3>
		If galleries don't show up right on your theme, copy taxonomy-gallery.php from this plugin folder to your theme folder.

		</div>
			<?php
		}
   
	   

			   
}
