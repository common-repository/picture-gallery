<?php
/*
Plugin Name: Picture Gallery - Frontend Image Uploads, AJAX Photo List
Plugin URI: https://videochat-scripts.com/picture-gallery-plugin/
Description: <strong>Picture Gallery - Frontend Image Uploads, AJAX Photo List</strong> plugin enables users (configured roles) to share pictures from frontend. Can integrate galleries for custom posts. Integrates with Media Library, <a href="https://wordpress.org/plugins/paid-membership/">MicroPayments Wallet – Paid Content</a> for selling pictures, <a href='https://paidvideochat.com'>PaidVideochat - Turnkey Cam Site</a> for profile pictures and snapshots. <a href='https://videowhisper.com/tickets_submit.php?topic=Picture-Gallery'>Contact Developers</a> . Leave a review if you find this plugin idea useful and would like more updates!
Version: 1.5.19
Author: VideoWhisper.com
Author URI: https://videowhisper.com/
Contributors: videowhisper, VideoWhisper.com
Requires PHP: 7.4
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once plugin_dir_path( __FILE__ ) . '/inc/admin.php';
require_once plugin_dir_path( __FILE__ ) . '/inc/shortcodes.php';

use VideoWhisper\PictureGallery;

if ( ! class_exists( 'VWpictureGallery' ) ) {
	class VWpictureGallery {

		use VideoWhisper\PictureGallery\Admin;
		use VideoWhisper\PictureGallery\Shortcodes;

		public function __construct() {
		}

		function VWpictureGallery() {
			// old constructor
			self::__construct();
		}

		static function install() {
			// do not generate any output here
			self::setupOptions();
			self::picture_post();
			flush_rewrite_rules();
		}

		// ! Supported extensions
		static function extensions_picture() {
			return array( 'jpg', 'png', 'gif', 'jpeg' );
		}


		// Register Custom Post Type
		static function picture_post() {

			$options = get_option( 'VWpictureGalleryOptions' );

			// only if missing
			if ( post_type_exists( $options['custom_post'] ) ) {
				return;
			}

			if ( $options['pictures'] ) {
				if ( ! post_type_exists( $options['custom_post'] ) ) {

					$labels = array(
						'name'                     => _x( 'Pictures', 'Post Type General Name', 'picture-gallery' ),
						'singular_name'            => _x( 'Picture', 'Post Type Singular Name', 'picture-gallery' ),
						'menu_name'                => __( 'Pictures', 'picture-gallery' ),
						'parent_item_colon'        => __( 'Parent Picture:', 'picture-gallery' ),
						'all_items'                => __( 'All Pictures', 'picture-gallery' ),
						'view_item'                => __( 'View Picture', 'picture-gallery' ),
						'add_new_item'             => __( 'Add New Picture', 'picture-gallery' ),
						'add_new'                  => __( 'New Picture', 'picture-gallery' ),
						'edit_item'                => __( 'Edit Picture', 'picture-gallery' ),
						'update_item'              => __( 'Update Picture', 'picture-gallery' ),
						'search_items'             => __( 'Search Pictures', 'picture-gallery' ),
						'not_found'                => __( 'No Pictures found', 'picture-gallery' ),
						'not_found_in_trash'       => __( 'No Pictures found in Trash', 'picture-gallery' ),

						// BuddyPress Activity
						'bp_activity_admin_filter' => __( 'New picture added', 'picture-gallery' ),
						'bp_activity_front_filter' => __( 'Pictures', 'picture-gallery' ),
						'bp_activity_new_post'     => __( '%1$s added a new <a href="%2$s">picture</a>', 'picture-gallery' ),
						'bp_activity_new_post_ms'  => __( '%1$s added a new <a href="%2$s">picture</a>, on the site %3$s', 'picture-gallery' ),

					);

					$args = array(
						'label'               => __( 'picture', 'picture-gallery' ),
						'description'         => __( 'Pictures', 'picture-gallery' ),
						'labels'              => $labels,
						'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'comments', 'custom-fields', 'page-attributes' ), //, 'buddypress-activity' //manual addign after snapshot
						'taxonomies'          => array( 'category', 'post_tag' ),
						'hierarchical'        => false,
						'public'              => true,
						'show_ui'             => true,
						'show_in_menu'        => true,
						'show_in_nav_menus'   => true,
						'show_in_admin_bar'   => true,
						'menu_position'       => 5,
						'can_export'          => true,
						'has_archive'         => true,
						'exclude_from_search' => false,
						'publicly_queryable'  => true,
						'menu_icon'           => 'dashicons-format-image',
						'capability_type'     => 'post',
					);

					// BuddyPress Activity
					if ( function_exists( 'bp_is_active' ) ) {
						if ( bp_is_active( 'activity' ) ) {
							$args['bp_activity'] = array(
								'component_id' => buddypress()->activity->id,
								'action_id'    => 'new_picture',
								'contexts'     => array( 'activity', 'member' ),
								'position'     => 40,
							);
						}
					}

					register_post_type( $options['custom_post'], $args );

					// Add new taxonomy, make it hierarchical (like categories)
					$labels = array(
						'name'              => _x( 'Galleries', 'taxonomy general name' ),
						'singular_name'     => _x( 'Gallery', 'taxonomy singular name' ),
						'search_items'      => __( 'Search Galleries', 'picture-gallery' ),
						'all_items'         => __( 'All Galleries', 'picture-gallery' ),
						'parent_item'       => __( 'Parent Gallery', 'picture-gallery' ),
						'parent_item_colon' => __( 'Parent Gallery:', 'picture-gallery' ),
						'edit_item'         => __( 'Edit Gallery', 'picture-gallery' ),
						'update_item'       => __( 'Update Gallery', 'picture-gallery' ),
						'add_new_item'      => __( 'Add New Gallery', 'picture-gallery' ),
						'new_item_name'     => __( 'New Gallery Name', 'picture-gallery' ),
						'menu_name'         => __( 'Galleries', 'picture-gallery' ),
					);

					$args = array(
						'hierarchical'          => true,
						'labels'                => $labels,
						'show_ui'               => true,
						'show_admin_column'     => true,
						'update_count_callback' => '_update_post_term_count',
						'query_var'             => true,
						'rewrite'               => array( 'slug' => $options['custom_taxonomy'] ),
					);
					register_taxonomy( $options['custom_taxonomy'], array( $options['custom_post'] ), $args );
				}
			}

		}

		static function picture_delete( $picture_id ) {
			$options = get_option( 'VWpictureGalleryOptions' );
			if ( get_post_type( $picture_id ) != $options['custom_post'] ) {
				return;
			}

			// delete source & thumb files
			$filePath = get_post_meta( $post_id, 'picture-source-file', true );

			$post = get_post( $picture_id );

				// delete from Media Library
						$filetype = wp_check_filetype( $filePath );

						$attachment_args = array(
							'guid'           => self::path2url( $filePath ),
							'post_parent'    => $post_id,
							'post_mime_type' => $filetype['type'],
							'post_title'     => sanitize_text_field( $post->post_title ),
							'post_content'   => '',
							'post_status'    => 'inherit',
						);

						// delete previous, if already present
						$attachments = get_posts( $attachment_args );
						if ( $attachments ) {
							foreach ( $attachments as $attachment ) {
								wp_delete_attachment( $attachment->ID, true );
							}
						}

						if ( file_exists( $filePath ) ) {
							unlink( $filePath );
						}

						$filePath = get_post_meta( $post_id, 'picture-thumbnail', true );
						if ( file_exists( $filePath ) ) {
							unlink( $filePath );
						}

		}


	
		static function plugins_loaded() {
			 $options = get_option( 'VWpictureGalleryOptions' );

			add_action( 'wp_enqueue_scripts', array( 'VWpictureGallery', 'scripts' ) );

			$plugin = plugin_basename( __FILE__ );
			add_filter( "plugin_action_links_$plugin", array( 'VWpictureGallery', 'settings_link' ) );

			// translations
			load_plugin_textdomain( 'picture-gallery', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

			if ($options['fixAutoP'] ?? false)
			{
			// prevent wp from adding <p> that breaks JS
			remove_filter( 'the_content', 'wpautop' );
			// move wpautop filter to BEFORE shortcode is processed
			add_filter( 'the_content', 'wpautop', 1 );
			// then clean AFTER shortcode
			add_filter( 'the_content', 'shortcode_unautop', 100 );
			}

			add_filter( 'manage_' . $options['custom_post'] . '_posts_columns', array( 'VWpictureGallery', 'columns_head_picture' ), 10 );
			add_action( 'manage_' . $options['custom_post'] . '_posts_custom_column', array( 'VWpictureGallery', 'columns_content_picture' ), 10, 2 );

			add_action( 'before_delete_post', array( 'VWpictureGallery', 'picture_delete' ) );

			// picture post page
			add_filter( 'the_content', array( 'VWpictureGallery', 'picture_page' ) );

			// ! shortcodes
			add_shortcode( 'videowhisper_picture_upload_guest', array( 'VWpictureGallery', 'videowhisper_picture_upload_guest' ) );

			add_shortcode( 'videowhisper_pictures', array( 'VWpictureGallery', 'videowhisper_pictures' ) );
			add_shortcode( 'videowhisper_picture', array( 'VWpictureGallery', 'videowhisper_picture' ) );
			add_shortcode( 'videowhisper_picture_preview', array( 'VWpictureGallery', 'videowhisper_picture_preview' ) );

			add_shortcode( 'videowhisper_picture_upload', array( 'VWpictureGallery', 'videowhisper_picture_upload' ) );
			add_shortcode( 'videowhisper_picture_import', array( 'VWpictureGallery', 'videowhisper_picture_import' ) );

			add_shortcode( 'videowhisper_postpictures', array( 'VWpictureGallery', 'videowhisper_postpictures' ) );
			add_shortcode( 'videowhisper_postpictures_process', array( 'VWpictureGallery', 'videowhisper_postpictures_process' ) );

			// ! widgets
			//wp_register_sidebar_widget( 'videowhisper_pictures', 'Pictures', array( 'VWpictureGallery', 'widget_pictures' ), array( 'description' => 'List pictures and updates using AJAX.' ) );
			//wp_register_widget_control( 'videowhisper_pictures', 'videowhisper_pictures', array( 'VWpictureGallery', 'widget_pictures_options' ) );

			// ! ajax

			// ajax pictures
			add_action( 'wp_ajax_vwpg_pictures', array( 'VWpictureGallery', 'vwpg_pictures' ) );
			add_action( 'wp_ajax_nopriv_vwpg_pictures', array( 'VWpictureGallery', 'vwpg_pictures' ) );

			// upload pictures
			add_action( 'wp_ajax_vwpg_upload', array( 'VWpictureGallery', 'vwpg_upload' ) );

		}


		static function archive_template( $archive_template ) {
			global $post;

			$options = get_option( 'VWpictureGalleryOptions' );

			if ( get_query_var( 'taxonomy' ) != $options['custom_taxonomy'] ) {
				return $archive_template;
			}

			if ( $options['taxonomyTemplate'] == '+plugin' ) {
				$archive_template_new = dirname( __FILE__ ) . '/taxonomy-gallery.php';
				if ( file_exists( $archive_template_new ) ) {
					return $archive_template_new;
				}
			}

			$archive_template_new = get_template_directory() . '/' . $options['taxonomyTemplate'];
			if ( file_exists( $archive_template_new ) ) {
				return $archive_template_new;
			} else {
				return $archive_template;
			}
		}

		// ! Widgets

		static function widgetSetupOptions() {
			 $widgetOptions = array(
				 'title'           => '',
				 'perpage'         => '8',
				 'perrow'          => '',
				 'gallery'         => '',
				 'order_by'        => '',
				 'category_id'     => '',
				 'select_category' => '1',
				 'select_tags'     => '1',
				 'select_name'     => '1',
				 'select_order'    => '1',
				 'select_page'     => '1',
				 'include_css'     => '0',

			 );

			 $options = get_option( 'VWpictureGalleryWidgetOptions' );

			 if ( ! empty( $options ) ) {
				 foreach ( $options as $key => $option ) {
					 $widgetOptions[ $key ] = $option;
				 }
			 }

			 update_option( 'VWpictureGalleryWidgetOptions', $widgetOptions );

			 return $widgetOptions;
		}

		static function widget_pictures_options( $args = array(), $params = array() ) {

			$options = self::widgetSetupOptions();

			if ( isset( $_POST ) ) {
				foreach ( $options as $key => $value ) {
					if ( isset( $_POST[ $key ] ) ) {
						$options[ $key ] = trim( sanitize_text_field( $_POST[ $key ] ) );
					}
				}
					update_option( 'VWpictureGalleryWidgetOptions', $options );
			}
			?>

			<?php _e( 'Title', 'picture-gallery' ); ?>:<br />
	<input type="text" class="widefat" name="title" value="<?php echo stripslashes( esc_attr( $options['title'] ) ); ?>" />
	<br /><br />

			<?php _e( 'Gallery', 'picture-gallery' ); ?>:<br />
	<input type="text" class="widefat" name="gallery" value="<?php echo stripslashes( esc_attr( $options['gallery'] ) ); ?>" />
	<br /><br />

			<?php _e( 'Category ID', 'picture-gallery' ); ?>:<br />
	<input type="text" class="widefat" name="category_id" value="<?php echo stripslashes( $options['category_id'] ); ?>" />
	<br /><br />

			<?php _e( 'Order By', 'picture-gallery' ); ?>:<br />
	<select name="order_by" id="order_by">
  <option value="post_date" <?php echo $options['order_by'] == 'post_date' ? 'selected' : ''; ?>><?php _e( 'Date', 'picture-gallery' ); ?></option>
	<option value="picture-views" <?php echo $options['order_by'] == 'picture-views' ? 'selected' : ''; ?>><?php _e( 'Views', 'picture-gallery' ); ?></option>
	<option value="picture-lastview" <?php echo $options['order_by'] == 'picture-lastview' ? 'selected' : ''; ?>><?php _e( 'Recently Watched', 'picture-gallery' ); ?></option>
</select><br /><br />

			<?php _e( 'Pictures per Page', 'picture-gallery' ); ?>:<br />
	<input type="text" class="widefat" name="perpage" value="<?php echo stripslashes( esc_attr( $options['perpage'] ) ); ?>" />
	<br /><br />

			<?php _e( 'Pictures per Row', 'picture-gallery' ); ?>:<br />
	<input type="text" class="widefat" name="perrow" value="<?php echo stripslashes( esc_attr( $options['perrow'] ) ); ?>" />
	<br /><br />

			<?php _e( 'Category Selector', 'picture-gallery' ); ?>:<br />
	<select name="select_category" id="select_category">
  <option value="1" <?php echo $options['select_category'] ? 'selected' : ''; ?>>Yes</option>
  <option value="0" <?php echo $options['select_category'] ? '' : 'selected'; ?>>No</option>
</select><br /><br />

			<?php _e( 'Tags Selector', 'picture-gallery' ); ?>:<br />
	<select name="select_tags" id="select_order">
  <option value="1" <?php echo $options['select_tags'] ? 'selected' : ''; ?>>Yes</option>
  <option value="0" <?php echo $options['select_tags'] ? '' : 'selected'; ?>>No</option>
</select><br /><br />

			<?php _e( 'Name Selector', 'picture-gallery' ); ?>:<br />
	<select name="select_name" id="select_name">
  <option value="1" <?php echo $options['select_name'] ? 'selected' : ''; ?>>Yes</option>
  <option value="0" <?php echo $options['select_name'] ? '' : 'selected'; ?>>No</option>
</select><br /><br />

			<?php _e( 'Order Selector', 'picture-gallery' ); ?>:<br />
	<select name="select_order" id="select_order">
  <option value="1" <?php echo $options['select_order'] ? 'selected' : ''; ?>>Yes</option>
  <option value="0" <?php echo $options['select_order'] ? '' : 'selected'; ?>>No</option>
</select><br /><br />

			<?php _e( 'Page Selector', 'picture-gallery' ); ?>:<br />
	<select name="select_page" id="select_page">
  <option value="1" <?php echo $options['select_page'] ? 'selected' : ''; ?>>Yes</option>
  <option value="0" <?php echo $options['select_page'] ? '' : 'selected'; ?>>No</option>
</select><br /><br />

			<?php _e( 'Include CSS', 'picture-gallery' ); ?>:<br />
	<select name="include_css" id="include_css">
  <option value="1" <?php echo $options['include_css'] ? 'selected' : ''; ?>>Yes</option>
  <option value="0" <?php echo $options['include_css'] ? '' : 'selected'; ?>>No</option>
</select><br /><br />
			<?php
		}

		static function widget_pictures( $args = array(), $params = array() ) {

			$options = get_option( 'VWpictureGalleryWidgetOptions' );

			echo stripslashes( esc_html( $args['before_widget'] ) );

			echo stripslashes( esc_html( $args['before_title'] ) );
			echo stripslashes( esc_html( $options['title'] ) );
			echo stripslashes( esc_html( $args['after_title'] ) );

			echo do_shortcode( '[videowhisper_pictures gallery="' . esc_attr( $options['gallery'] ) . '" category_id="' . esc_attr( $options['category_id'] ) . '" order_by="' . esc_attr( $options['order_by'] ) . '" perpage="' . esc_attr( $options['perpage'] ) . '" perrow="' . esc_attr( $options['perrow'] ) . '" select_category="' . esc_attr( $options['select_category'] ) . '" select_order="' . esc_attr( $options['select_order'] ) . '" select_page="' . $esc_attr( options['select_page'] ) . '" include_css="' . esc_attr( $options['include_css'] ) . '"]' );

			echo stripslashes( esc_html( $args['after_widget'] ) );
		}

		// ! Shortcodes

		static function videowhisper_picture_import( $atts ) {
			global $current_user;

			if ( ! is_user_logged_in() ) {
				return __( 'Login is required to import pictures!', 'picture-gallery' );
			}

			$current_user = wp_get_current_user();
			$userName     = $options['userName'];
			if ( ! $userName ) {
				$userName = 'user_nicename';
			}
			$username = $current_user->$userName;

			$options = get_option( 'VWpictureGalleryOptions' );
			if ( ! self::hasPriviledge( $options['shareList'] ) ) {
				return __( 'You do not have permissions to share pictures!', 'picture-gallery' );
			}

			$atts = shortcode_atts(
				array(
					'category'    => '',
					'gallery'     => '',
					'owner'       => '',
					'path'        => '',
					'prefix'      => '',
					'tag'         => '',
					'description' => '',
				),
				$atts,
				'videowhisper_picture_import'
			);

			if ( ! $atts['path'] ) {
				return 'videowhisper_picture_import: Path required!';
			}

			if ( ! file_exists( $atts['path'] ) ) {
				return 'videowhisper_picture_import: Path not found!';
			}

			if ( $atts['category'] ) {
				$categories = '<input type="hidden" name="category" id="category" value="' . $atts['category'] . '"/>';
			} else {
				$categories = '<label for="category">' . __( 'Category', 'picture-gallery' ) . ': </label><div class="">' . wp_dropdown_categories( 'show_count=0&echo=0&name=category&hide_empty=0&class=ui+dropdown' ) . '</div>';
			}

			if ( $atts['gallery'] ) {
				$galleries = '<br><label for="gallery">' . __( 'Gallery', 'picture-gallery' ) . ': </label>' . $atts['gallery'] . '<input type="hidden" name="gallery" id="gallery" value="' . $atts['gallery'] . '"/>';
			} elseif ( current_user_can( 'edit_posts' ) ) {
				$galleries = '<br><label for="gallery">Gallery(s): </label> <br> <input size="48" maxlength="64" type="text" name="gallery" id="gallery" value="' . $username . '"/> ' . __( '(comma separated)', 'picture-gallery' );
			} else {
				$galleries = '<br><label for="gallery">' . __( 'gallery', 'picture-gallery' ) . ': </label> ' . $username . ' <input type="hidden" name="gallery" id="gallery" value="' . $username . '"/> ';
			}

			if ( $atts['owner'] ) {
				$owners = '<input type="hidden" name="owner" id="owner" value="' . $atts['owner'] . '"/>';
			} else {
				$owners = '<input type="hidden" name="owner" id="owner" value="' . $current_user->ID . '"/>';
			}

			if ( $atts['tag'] != '_none' ) {
				if ( $atts['tag'] ) {
					$tags = '<br><label for="gallery">' . __( 'Tags', 'picture-gallery' ) . ': </label>' . $atts['tag'] . '<input type="hidden" name="tag" id="tag" value="' . $atts['tag'] . '"/>';
				} else {
					$tags = '<br><label for="tag">' . __( 'Tag(s)', 'picture-gallery' ) . ': </label> <br> <input size="48" maxlength="64" type="text" name="tag" id="tag" value=""/> (comma separated)';
				}
			}

			if ( $atts['description'] != '_none' ) {
				if ( $atts['description'] ) {
					$descriptions = '<br><label for="description">' . __( 'Description', 'picture-gallery' ) . ': </label>' . $atts['description'] . '<input type="hidden" name="description" id="description" value="' . $atts['description'] . '"/>';
				} else {
					$descriptions = '<br><label for="description">' . __( 'Description', 'picture-gallery' ) . ': </label> <br> <input size="48" maxlength="256" type="text" name="description" id="description" value=""/>';
				}
			}

					$url = get_permalink();

				$htmlCode .= '<h3>' . __( 'Import Pictures', 'picture-gallery' ) . '</h3>' . $atts['path'] . $atts['prefix'];

			$htmlCode .= '<form action="' . $url . '" method="post">';

			$htmlCode .= $categories;
			$htmlCode .= $galleries;
			$htmlCode .= $tags;
			$htmlCode .= $descriptions;
			$htmlCode .= $owners;

			$htmlCode .= '<br>' . self::importFilesSelect( $atts['prefix'], self::extensions_picture(), $atts['path'] );

			$htmlCode .= '<INPUT class="button button-primary" TYPE="submit" name="import" id="import" value="Import">';

			$htmlCode .= '<INPUT class="button button-primary" TYPE="submit" name="delete" id="delete" value="Delete">';

			$htmlCode .= '</form>';

			// wp_add_inline_style('vwpg_pictures', html_entity_decode( stripslashes( $options['customCSS']) )  );
			$htmlCode .= '<style type="text/css">' . html_entity_decode( stripslashes( $options['customCSS'] ) ) . '</style>';

			return $htmlCode;
		}

		

		static function videowhisper_picture_upload( $atts ) {

			if ( ! is_user_logged_in() ) {
				return __( 'Login is required to upload pictures!', 'picture-gallery' );
			}

			$options = self::getOptions();
			
			$current_user = wp_get_current_user();
			$userName     = $options['userName'];
			if ( ! $userName ) {
				$userName = 'user_nicename';
			}
			$username = $current_user->$userName;

			if ( ! self::hasPriviledge( $options['shareList'] ) ) {
				return __( 'You do not have permissions to share pictures!', 'picture-gallery' );
			}

			$atts = shortcode_atts(
				array(
					'category'    => '',
					'gallery'     => '',
					'owner'       => '',
					'tag'         => '',
					'description' => '',
				),
				$atts,
				'videowhisper_picture_upload'
			);

			self::enqueueUI();

			$ajaxurl = admin_url() . 'admin-ajax.php?action=vwpg_upload';

			if ( $atts['category'] ) {
				$categories = '<input type="hidden" name="category" id="category" value="' . $atts['category'] . '"/>';
			} else {
				$categories = '<div class="field><label for="category">' . __( 'Category', 'picture-gallery' ) . ' </label> ' . wp_dropdown_categories( 'show_count=0&echo=0&name=category&hide_empty=0&class=ui+dropdown' ) . '</div>';
			}

			if ( $atts['gallery'] ) {
				$galleries = '<label for="gallery">' . __( 'gallery', 'picture-gallery' ) . '</label>' . $atts['gallery'] . '<input type="hidden" name="gallery" id="gallery" value="' . $atts['gallery'] . '"/>';
			} elseif ( current_user_can( 'edit_users' ) ) {
				$galleries = '<br><label for="gallery">' . __( 'Gallery(s)', 'picture-gallery' ) . '</label> <br> <input size="48" maxlength="64" type="text" name="gallery" id="gallery" value="' . $username . '" class="text-input"/> (comma separated)';
			} else {
				$galleries = '<label for="gallery">' . __( 'Gallery', 'picture-gallery' ) . '</label> ' . $username . ' <input type="hidden" name="gallery" id="gallery" value="' . $username . '"/> ';
			}

			if ( $atts['owner'] ) {
				$owners = '<input type="hidden" name="owner" id="owner" value="' . $atts['owner'] . '"/>';
			} else {
				$owners = '<input type="hidden" name="owner" id="owner" value="' . $current_user->ID . '"/>';
			}

			if ( $atts['tag'] != '_none' ) {
				if ( $atts['tag'] ) {
					$tags = '<br><label for="gallery">' . __( 'Tags', 'picture-gallery' ) . '</label>' . $atts['tag'] . '<input type="hidden" name="tag" id="tag" value="' . $atts['tag'] . '"/>';
				} else {
					$tags = '<br><label for="tag">' . __( 'Tag(s)', 'picture-gallery' ) . '</label> <br> <input size="48" maxlength="64" type="text" name="tag" id="tag" value="" class="text-input"/> (comma separated)';
				}
			}

			if ( $atts['description'] != '_none' ) {
				if ( $atts['description'] ) {
					$descriptions = '<br><label for="description">' . __( 'Description', 'picture-gallery' ) . '</label>' . $atts['description'] . '<input type="hidden" name="description" id="description" value="' . $atts['description'] . '"/>';
				} else {
					$descriptions = '<br><label for="description">' . __( 'Description', 'picture-gallery' ) . '</label> <br> <input size="48" maxlength="256" type="text" name="description" id="description" value="" class="text-input"/>';
				}
			}

					$iPod = stripos( $_SERVER['HTTP_USER_AGENT'], 'iPod' );
				$iPhone   = stripos( $_SERVER['HTTP_USER_AGENT'], 'iPhone' );
			$iPad         = stripos( $_SERVER['HTTP_USER_AGENT'], 'iPad' );
			$Android      = stripos( $_SERVER['HTTP_USER_AGENT'], 'Android' );

			if ( $iPhone || $iPad || $iPod || $Android ) {
				$mobile = true;
			} else {
				$mobile = false;
			}

			if ( $mobile ) {
				// https://mobilehtml5.org/ts/?id=23
				// $mobiles = 'capture="camera"';
				$accepts   = 'accept="image/*;capture=camera"';
				$multiples = '';
				$filedrags = '';
			} else {
				$mobiles   = '';
				$accepts   = 'accept="image/jpeg,image/png,image/*;capture=camera"';
				$multiples = 'multiple="multiple"';
				$filedrags = '<div id="filedrag">' . __( 'or Drag & Drop files to this upload area<br>(select rest of options first)', 'picture-gallery' ) . '</div>';
			}

			wp_enqueue_script( 'vwpg-upload', plugin_dir_url( __FILE__ ) . 'upload.js' );

			$submits = '<div id="submitbutton">
	<button class="ui button" type="submit" name="upload" id="upload">' . __( 'Upload Files', 'picture-gallery' ) . '</button>';

			$htmlCode = <<<EOHTML
<form class="ui form" id="upload" action="$ajaxurl" method="POST" enctype="multipart/form-data">

<fieldset>
$categories
$galleries
$tags
$descriptions
$owners
<input type="hidden" id="MAX_FILE_SIZE" name="MAX_FILE_SIZE" value="128000000" />
EOHTML;

			$htmlCode .= '<legend><h3>' . __( 'Picture Upload', 'picture-gallery' ) . '</h3></legend><div> <label for="fileselect">' . __( 'Pictures to upload', 'picture-gallery' ) . '</label>';

			$htmlCode .= <<<EOHTML
	<br><input class="ui button" type="file" id="fileselect" name="fileselect[]" $mobiles $multiples $accepts />
$filedrags
$submits
</div>
EOHTML;

			$htmlCode .= <<<EOHTML
<div id="progress"></div>

</fieldset>
</form>

<script>
jQuery(document).ready(function(){
jQuery(".ui.dropdown:not(.multi,.fpsDropdown)").dropdown();
});
</script>


<STYLE>

#filedrag
{
 height: 100px;
 border: 1px solid #AAA;
 border-radius: 9px;
 color: #333;
 background: #eee;
 padding: 5px;
 margin-top: 5px;
 text-align:center;
}

#progress
{
padding: 4px;
margin: 4px;
}

#progress div {
	position: relative;
	background: #555;
	-moz-border-radius: 9px;
	-webkit-border-radius: 9px;
	border-radius: 9px;

	padding: 4px;
	margin: 4px;

	color: #DDD;

}

#progress div > span {
	display: block;
	height: 20px;

	   -webkit-border-top-right-radius: 4px;
	-webkit-border-bottom-right-radius: 4px;
	       -moz-border-radius-topright: 4px;
	    -moz-border-radius-bottomright: 4px;
	           border-top-right-radius: 4px;
	        border-bottom-right-radius: 4px;
	    -webkit-border-top-left-radius: 4px;
	 -webkit-border-bottom-left-radius: 4px;
	        -moz-border-radius-topleft: 4px;
	     -moz-border-radius-bottomleft: 4px;
	            border-top-left-radius: 4px;
	         border-bottom-left-radius: 4px;

	background-color: rgb(43,194,83);

	background-image:
	   -webkit-gradient(linear, 0 0, 100% 100%,
	      color-stop(.25, rgba(255, 255, 255, .2)),
	      color-stop(.25, transparent), color-stop(.5, transparent),
	      color-stop(.5, rgba(255, 255, 255, .2)),
	      color-stop(.75, rgba(255, 255, 255, .2)),
	      color-stop(.75, transparent), to(transparent)
	   );

	background-image:
		-webkit-linear-gradient(
		  -45deg,
	      rgba(255, 255, 255, .2) 25%,
	      transparent 25%,
	      transparent 50%,
	      rgba(255, 255, 255, .2) 50%,
	      rgba(255, 255, 255, .2) 75%,
	      transparent 75%,
	      transparent
	   );

	background-image:
		-moz-linear-gradient(
		  -45deg,
	      rgba(255, 255, 255, .2) 25%,
	      transparent 25%,
	      transparent 50%,
	      rgba(255, 255, 255, .2) 50%,
	      rgba(255, 255, 255, .2) 75%,
	      transparent 75%,
	      transparent
	   );

	background-image:
		-ms-linear-gradient(
		  -45deg,
	      rgba(255, 255, 255, .2) 25%,
	      transparent 25%,
	      transparent 50%,
	      rgba(255, 255, 255, .2) 50%,
	      rgba(255, 255, 255, .2) 75%,
	      transparent 75%,
	      transparent
	   );

	background-image:
		-o-linear-gradient(
		  -45deg,
	      rgba(255, 255, 255, .2) 25%,
	      transparent 25%,
	      transparent 50%,
	      rgba(255, 255, 255, .2) 50%,
	      rgba(255, 255, 255, .2) 75%,
	      transparent 75%,
	      transparent
	   );

	position: relative;
	overflow: hidden;
}

#progress div.success
{
    color: #DDD;
	background: #3C6243 none 0 0 no-repeat;
}

#progress div.failed
{
 	color: #DDD;
	background: #682C38 none 0 0 no-repeat;
}
</STYLE>
EOHTML;

			// wp_add_inline_style('vwpg_pictures', html_entity_decode( stripslashes( $options['customCSS']) )  );
			$htmlCode .= '<style type="text/css">' . html_entity_decode( stripslashes( $options['customCSS'] ) ) . '</style>';

			return $htmlCode;

		}

		static function generateName( $fn ) {
				$ext = strtolower( pathinfo( $fn, PATHINFO_EXTENSION ) );

				if ( ! in_array( $ext, self::extensions_picture() ) ) {
					echo 'Extension not allowed!';
					exit;
				}

				// unpredictable name
				return md5( uniqid( $fn, true ) ) . '.' . $ext;
			}
			
		static function vwpg_upload() {
			echo 'Upload completed... ';

			// global $current_user;
			$current_user = wp_get_current_user();

			if ( ! is_user_logged_in() ) {
				echo 'Login required!';
				exit;
			}

			$owner = $_SERVER['HTTP_X_OWNER'] ? intval( sanitize_text_field( $_SERVER['HTTP_X_OWNER'] ) ) : intval( sanitize_text_field( $_POST['owner'] ) );

			if ( $owner && ! current_user_can( 'edit_users' ) && $owner != $current_user->ID ) {
				echo 'Only admin can upload for others!';
				exit;
			}
			if ( ! $owner ) {
				$owner = $current_user->ID;
			}

			$gallery = $_SERVER['HTTP_X_GALLERY'] ? sanitize_text_field( $_SERVER['HTTP_X_GALLERY'] ) : sanitize_text_field( $_POST['gallery'] );

			// if csv sanitize as array
			if ( strpos( $gallery, ',' ) !== false ) {
				$galleries = explode( ',', $gallery );
				foreach ( $galleries as $key => $value ) {
					$galleries[ $key ] = sanitize_file_name( trim( $value ) );
				}
				$gallery = $galleries;
			}

			if ( ! $gallery ) {
				echo 'Gallery required!';
				exit;
			}

			$category = $_SERVER['HTTP_X_CATEGORY'] ? sanitize_text_field( $_SERVER['HTTP_X_CATEGORY'] ) : sanitize_text_field( $_POST['category'] );

			$tag = $_SERVER['HTTP_X_TAG'] ? sanitize_text_field( $_SERVER['HTTP_X_TAG'] ) : sanitize_text_field( $_POST['tag'] );

			// if csv sanitize as array
			if ( strpos( $tag, ',' ) !== false ) {
				$tags = explode( ',', $tag );
				foreach ( $tags as $key => $value ) {
					$tags[ $key ] = sanitize_file_name( trim( $value ) );
				}
				$tag = $tags;
			}

			$description = sanitize_text_field( $_SERVER['HTTP_X_DESCRIPTION'] ? sanitize_textarea_field( $_SERVER['HTTP_X_DESCRIPTION'] ) : sanitize_textarea_field( $_POST['description'] ) );

			$options = get_option( 'VWpictureGalleryOptions' );

			$dir = sanitize_text_field( $options['uploadsPath'] );
			if ( ! file_exists( $dir ) ) {
				mkdir( $dir );
			}

			$dir .= '/uploads';
			if ( ! file_exists( $dir ) ) {
				mkdir( $dir );
			}

			$dir .= '/';

			ob_clean();
			$fn = ( isset( $_SERVER['HTTP_X_FILENAME'] ) ? sanitize_file_name( $_SERVER['HTTP_X_FILENAME'] ) : false );

			$path = '';

			if ( $fn ) {

				// AJAX call
				$rawdata = $GLOBALS['HTTP_RAW_POST_DATA'] ?? false;
				if ( ! $rawdata ) {
					$rawdata = file_get_contents( 'php://input' );
				}
				if ( ! $rawdata ) {
					echo 'Raw post data missing!';
					exit;
				}

				file_put_contents( $path = $dir . self::generateName( $fn ), $rawdata );

				$el    = array_shift( explode( '.', $fn ) );
				$title = ucwords( str_replace( '-', ' ', sanitize_file_name( $el ) ) );

				echo sanitize_text_field( $title ) . ' ';

				echo self::importFile( $path, $title, $owner, $gallery, $category, $tag, $description );

			} else {
				// form submit
				$files = isset( $_FILES['fileselect'] ) ? (array) $_FILES['fileselect'] : array();

				if ( $files['error'] ) {
					if ( is_array( $files['error'] ) ) {
						foreach ( $files['error'] as $id => $err ) {
							if ( $err == UPLOAD_ERR_OK ) {
								$fn = sanitize_file_name( $files['name'][ $id ] );
								move_uploaded_file( $files['tmp_name'][ $id ], $path = $dir . self::generateName( $fn ) );
								$title = ucwords( str_replace( '-', ' ', sanitize_file_name( array_shift( explode( '.', $fn ) ) ) ) );

								echo sanitize_text_field( $title ) . ' ';

								echo self::importFile( $path, $title, $owner, $gallery, $category ) . '<br>';
							}
						}
					}
				}
			}

			die;
		}


		static function videowhisper_postpictures( $atts ) {

			$options = get_option( 'VWpictureGalleryOptions' );

			$atts = shortcode_atts(
				array(
					'post'    => '',
					'perpage' => '8',
					'path'    => '',
				),
				$atts,
				'videowhisper_postpictures'
			);
			
			$htmlCode = '';

			if ( ! $atts['post'] ) {
				return 'No post id was specified, to manage post associated pictures.';
			}

			$channel = get_post( $atts['post'] );
			if ( $channel ) {
				$gallery = $channel->post_name;
			}

			if ( isset($_GET['gallery_upload']) ) {

				$htmlCode .= '<br><A class="ui button" href="' . remove_query_arg( 'gallery_upload' ) . '">Done Uploading Pictures</A>';
				// $gallery = sanitize_file_name($_GET['gallery_upload']);
			} else {

				$htmlCode = '<div class="w-actionbox color_alternate"><h3>' . __('Manage Pictures', 'picture-gallery') . '</h3>';

				if ( $atts['path'] ) {
					$htmlCode .= '<p>Available ' . esc_html( $channel->post_title ) . ' pictures: ' . self::importFilesCount( sanitize_text_field( $channel->post_title ), self::extensions_picture(), $atts['path'] ) . '</p>';
				}

				$link  = add_query_arg( array( 'gallery_import' => sanitize_text_field( $channel->post_title ) ), get_permalink() );
				$link2 = add_query_arg( array( 'gallery_upload' => sanitize_text_field( $channel->post_title ) ), get_permalink() );

				if ( $atts['path'] ) {
					$htmlCode .= ' <a class="ui button" href="' . $link . '">' . __('Import', 'picture-gallery') . '</a> ';
				}
				$htmlCode .= ' <a class="ui button" href="' . $link2 . '">' . __('Upload', 'picture-gallery') . '</a> ';
			}

			$htmlCode .= '</div>';

			$htmlCode .= '<h4>' . __('Pictures', 'picture-gallery') . '</h4>';

			$htmlCode .= do_shortcode( '[videowhisper_pictures perpage="' . $atts['perpage'] . '" gallery="' . $gallery . '"]' );

			return $htmlCode;
		}

		static function videowhisper_postpictures_process( $atts ) {

			$atts = shortcode_atts(
				array(
					'post'      => '',
					'post_type' => '',
					'path'      => '',
				),
				$atts,
				'videowhisper_postpictures_process'
			);

			self::importFilesClean();

			$htmlCode = '';

			if ( $channel_upload = sanitize_file_name( $_GET['gallery_upload'] ?? '' ) ) {
				$htmlCode .= do_shortcode( '[videowhisper_picture_upload gallery="' . $channel_upload . '"]' );
			}

			if ( $channel_name = sanitize_file_name( $_GET['gallery_import'] ?? '' ) ) {

				$options = get_option( 'VWpictureGalleryOptions' );

				$url = add_query_arg( array( 'gallery_import' => $channel_name ), get_permalink() );

				$htmlCode .= '<form id="videowhisperImport" name="videowhisperImport" action="' . $url . '" method="post">';

				$htmlCode .= '<h3>Import <b>' . $channel_name . '</b> Pictures to Gallery</h3>';

				$htmlCode .= self::importFilesSelect( $channel_name, self::extensions_picture(), $atts['path'] );

				$htmlCode .= '<input type="hidden" name="gallery" id="gallery" value="' . $channel_name . '">';

				// same category as post
				if ( $atts['post'] ) {
					$postID = $atts['post'];
				} else { // search by name
					global $wpdb;
					if ( $atts['post_type'] ) {
						$cfilter = "AND post_type='" . $atts['post_type'] . "'";
					}
					$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_name = '" . $channel_name . "' $cfilter LIMIT 0,1" );
				}

				if ( $postID ) {
					$cats = wp_get_post_categories( $postID );
					if ( count( $cats ) ) {
						$category = array_pop( $cats );
					}
					$htmlCode .= '<input type="hidden" name="category" id="category" value="' . $category . '">';
				}

				$htmlCode .= '<INPUT class="ui g-btn type_primary button button-primary" TYPE="submit" name="import" id="import" value="Import">';

				$htmlCode .= ' <INPUT class="ui g-btn type_primary button button-primary" TYPE="submit" name="delete" id="delete" value="Delete">';

				$htmlCode .= '</form>';
			}

			return $htmlCode;
		}


		// !permission functions

		// if any key matches any listing
		static function inList( $keys, $data ) {
			if ( ! $keys ) {
				return 0;
			}

			$list = explode( ',', strtolower( trim( $data ) ) );

			foreach ( $keys as $key ) {
				foreach ( $list as $listing ) {
					if ( strtolower( trim( $key ) ) == trim( $listing ) ) {
						return 1;
					}
				}
			}

				   return 0;
		}

		static function hasPriviledge( $csv ) {
			// determines if user is in csv list (role, id, email)

			if ( strpos( $csv, 'Guest' ) !== false ) {
				return 1;
			}

			if ( is_user_logged_in() ) {
				global $current_user;
				get_currentuserinfo();

				// access keys : roles, #id, email
				if ( $current_user ) {
					$userkeys   = $current_user->roles;
					$userkeys[] = $current_user->ID;
					$userkeys[] = $current_user->user_email;
				}

				if ( self::inList( $userkeys, $csv ) ) {
					return 1;
				}
			}

			return 0;
		}

		static function hasRole( $role ) {
			if ( ! is_user_logged_in() ) {
				return false;
			}

			global $current_user;
			get_currentuserinfo();

			$role = strtolower( $role );

			if ( in_array( $role, $current_user->roles ) ) {
				return true;
			} else {
				return false;
			}
		}

		static function getRoles() {
			if ( ! is_user_logged_in() ) {
				return 'None';
			}

			global $current_user;
			get_currentuserinfo();

			return implode( ', ', $current_user->roles );
		}

		static function poweredBy() {
			$options = get_option( 'VWpictureGalleryOptions' );

			$state = 'block';
			if ( ! $options['videowhisper'] ) {
				$state = 'none';
			}

			return '<div id="VideoWhisper" style="display: ' . $state . ';"><p>Published with VideoWhisper <a href="https://videowhisper.com/">Picture Gallery - Frontent Image Uploads with AJAX</a>.</p></div>';
		}


		// ! Custom Post Page

		static function single_template( $single_template ) {

			if ( ! is_single() ) {
				return $single_template;
			}

			$options = get_option( 'VWpictureGalleryOptions' );

			$postID = get_the_ID();
			if ( get_post_type( $postID ) != $options['custom_post'] ) {
				return $single_template;
			}

			if ( $options['postTemplate'] == '+plugin' ) {
				$single_template_new = dirname( __FILE__ ) . '/template-picture.php';
				if ( file_exists( $single_template_new ) ) {
					return $single_template_new;
				}
			}

			$single_template_new = get_template_directory() . '/' . $options['postTemplate'];

			if ( file_exists( $single_template_new ) ) {
				return $single_template_new;
			} else {
				return $single_template;
			}
		}



		static function picture_page( $content ) {
			if ( ! is_single() ) {
				return $content;
			}
			$postID = get_the_ID();

			$options = get_option( 'VWpictureGalleryOptions' );

			if ( get_post_type( $postID ) != $options['custom_post'] ) {
				return $content;
			}

			if ( $options['pictureWidth'] ) {
				$wCode = ' width="' . trim( $options['pictureWidth'] ) . '"';
			} else {
				$wCode = '';
			}

			$addCode = '' . do_shortcode('[videowhisper_picture picture="' . $postID . '" embed="1"' . $wCode . ']');

			// gallery
			global $wpdb;

			$terms = get_the_terms( $postID, $options['custom_taxonomy'] );

			if ( $terms && ! is_wp_error( $terms ) ) {
				
				//$addCode .= '<div class="videowhisper_gallery ui label ' . esc_attr( $options['interfaceClass'] ?? '' ) . '">' . ucwords( $options['custom_taxonomy'] ) . ': '. '</div>';

				foreach ( $terms as $term ) {

					if ( class_exists( 'VWliveStreaming' ) ) {
						if ( $options['vwls_channel'] ) {

							$channelID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_name = '" . $term->slug . "' and post_type='channel' LIMIT 0,1" );

							if ( $channelID ) {
								$addCode .= ' <a title="' . __( 'Channel', 'picture-gallery' ) . ': ' . $term->name . '" class="ui tag label small ' . esc_attr( $options['interfaceClass'] ?? '' ) . '" href="' . get_post_permalink( $channelID ) . '">' . $term->name . ' Channel</a> ';
							}
						}
					}

					$addCode .= ' <a title="' . __( 'Gallery', 'picture-gallery' ) . ': ' . $term->name . '" class="ui tag label small ' . esc_attr( $options['interfaceClass'] ?? '' ) . '" href="' . get_term_link( $term->slug, $options['custom_taxonomy'] ) . '">' . $term->name . '</a> ';

				}

			}

			$views = intval(get_post_meta( $postID, 'picture-views', true ));
			if ( ! $views ) {
				$views = 0;
			}

			if ($views) $addCode .= '<div class="videowhisper_views ui label pointing up' . esc_attr( $options['interfaceClass'] ?? '' ) . '">' . __( 'Picture Views', 'picture-gallery' ) . ': ' . esc_html($views) . '</div>';
			
			$addCode .= '<div class="videowhisper_size ui label pointing up' . esc_attr( $options['interfaceClass'] ?? '' ) . '">' . __( 'Resolution', 'picture-gallery' ) . ': ' . get_post_meta( $postID, 'picture-width', true ) . 'x' . get_post_meta( $postID, 'picture-height', true ) . '</div>';
			

			// ! show reviews
			if ( $options['rateStarReview'] ) {
				// tab : reviews
				if ( shortcode_exists( 'videowhisper_review' ) ) {
					$addCode .= '<h3>' . __( 'My Review', 'picture-gallery' ) . '</h3>' . do_shortcode( '[videowhisper_review content_type="picture" post_id="' . $postID . '" content_id="' . $postID . '"]' );
				} else {
					$addCode .= 'Warning: shortcodes missing. Plugin <a target="_plugin" href="https://wordpress.org/plugins/rate-star-review/">Rate Star Review</a> should be installed and enabled or feature disabled.';
				}

				if ( shortcode_exists( 'videowhisper_reviews' ) ) {
					$addCode .= '<h3>' . __( 'Reviews', 'picture-gallery' ) . '</h3>' . do_shortcode( '[videowhisper_reviews post_id="' . $postID . '"]' );
				}
			}

			return $addCode . $content;
		}

		static function imagecreatefromfile( $filename ) {
			if ( ! file_exists( $filename ) ) {
				throw new InvalidArgumentException( 'File "' . $filename . '" not found.' );
			}

			switch ( strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) ) ) {
				case 'jpeg':
				case 'jpg':
					return $img = @imagecreatefromjpeg( $filename );
				break;

				case 'png':
					return $img = @imagecreatefrompng( $filename );
				break;

				case 'gif':
					return $img = @imagecreatefromgif( $filename );
				break;

				default:
					throw new InvalidArgumentException( 'File "' . $filename . '" is not valid jpg, png or gif image.' );
				break;
			}

			return $img;

		}

		static function generateThumbnail( $src, $dest, $post_id = 0, $verbose = false) {
			if ( ! file_exists( $src ) ) {
				if ($verbose) echo "<br>Missing source file $src";
				return;
			}

			$options = get_option( 'VWpictureGalleryOptions' );

			// generate thumb
			$thumbWidth  = $options['thumbWidth'];
			$thumbHeight = $options['thumbHeight'];

			$srcImage = self::imagecreatefromfile( $src );
			if ( ! $srcImage ) {
				if ($verbose) echo "<br>Failed to imagecreatefromfile $src";
				return;
			}

			list($width, $height) = @getimagesize( $src );
			if ( ! $width ) {
				if ($verbose) echo "<br>Failed to getimagesize $src";
				return;
			}

			$destImage = imagecreatetruecolor( $thumbWidth, $thumbHeight );

			// cut to fit thumb aspect
			
					$Aspect = $width / $height; //source aspect 
					if ( floatval( $options['thumbHeight'] ) ) {
						$newAspect = floatval( $options['thumbWidth'] ) / floatval( $options['thumbHeight'] );
					} else {
						$newAspect = 1.33;
					}

					$newX = 0;
					$newY = 0;

					if ( $newAspect > $Aspect ) { //cut height
						$newWidth  = $width;
						$newHeight = floor( $width / $newAspect );
						$newY      = floor( ( $height - $newHeight ) / 2 );

					} else // cut width
					{
						$newWidth  = floor( $height * $newAspect );
						$newX      = floor( ( $width - $newWidth ) / 2 );
						$newHeight = $height;
					}

			imagecopyresampled( $destImage, $srcImage, 0, 0, $newX, $newY, $thumbWidth, $thumbHeight, $newWidth, $newHeight );

			imagejpeg( $destImage, $dest, 95 );

			if ( $post_id ) {
				update_post_meta( $post_id, 'picture-thumbnail', $dest );
				if ( $width ) {
					update_post_meta( $post_id, 'picture-width', $width );
				}
				if ( $height ) {
					update_post_meta( $post_id, 'picture-height', $height );
				}
			}			

			// return source dimensions
			return array( $width, $height );
		}


		static function updatePostThumbnail( $post_id, $overwrite = false, $verbose = false ) {
			
			$options = self::getOptions();

			// update post image
			$imagePath = get_post_meta( $post_id, 'picture-source-file', true );
			$thumbPath = get_post_meta( $post_id, 'picture-thumbnail', true );

			if ( $verbose ) {
				echo "<br>Updating thumbnail ($post_id, $imagePath,  $thumbPath)";
			}

			if ( ! $imagePath ) {
				if ( $verbose )  echo "<br>Missing image path";
				return;
			}
			if ( ! file_exists( $imagePath ) ) {
				if ( $verbose ) echo "<br>Missing image file";
				return;
			}
			if ( filesize( $imagePath ) < 5 ) {
				if ( $verbose ) echo "<br>Empty image file";
				return; // too small
			}

			if ( $overwrite || ! $thumbPath || ! file_exists( $thumbPath ) ) {
				$path                 = dirname( $imagePath );
				$thumbPath            = $path . '/' . $post_id . '_thumb.jpg';
				list($width, $height) = self::generateThumbnail( $imagePath, $thumbPath, $post_id, $verbose );
				if ( ! $width ) {
					if ( $verbose ) echo "<br>Failed to generate thumbnail generateThumbnail( $imagePath, $thumbPath, $post_id)";
					return;
				}

				$thumbPath = get_post_meta( $post_id, 'picture-thumbnail', true );
			}

			if ( $options['mediaLibraryThumb'] ) {

				if ( ! get_the_post_thumbnail( $post_id ) ) {
					$wp_filetype = wp_check_filetype( basename( $thumbPath ), null );

					$attachment = array(
						'guid'           => $thumbPath,
						'post_mime_type' => $wp_filetype['type'],
						'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $thumbPath, '.jpg' ) ),
						'post_content'   => '',
						'post_status'    => 'inherit',
					);

					// Insert the attachment.
					$attach_id = wp_insert_attachment( $attachment, $thumbPath, $post_id );
					set_post_thumbnail( $post_id, $attach_id );
				} else // just update
					{
					$attach_id = get_post_thumbnail_id( $post_id );
					// $thumbPath = get_attached_file($attach_id);
				}
				
			// BuddyPress Activity	- post when thumbnail is available 
			$post = get_post($post_id);
			if ($post->post_author)
			{

			$activity_id = get_post_meta( $post_id, 'bp_activity_id', true );
			if (!$activity_id) if ( function_exists( 'bp_activity_add' ) ) 
			{	
			$post = get_post($post_id);		
			$user = get_userdata( $post->post_author );

			if ($user)
			{
			$args = array(
				'action'       => '<a href="' . bp_members_get_user_url( $post->post_author ) . '">' . sanitize_text_field( $user->display_name ) . '</a> ' . __( 'posted a new picture', 'paid-membership' ) . ': <a href="' . get_permalink( $post_id ) . '">' . esc_html( $post->post_title )  . '</a>  ',
				'component'    => 'picture_gallery',
				'type'         => 'picture_new',
				'primary_link' => get_permalink( $post_id ),
				'user_id'      => $post->post_author,
				'item_id'      => $post_id,
				'content'      => '<a href="' . get_permalink( $post_id ) . '">' . get_the_post_thumbnail( $post_id, array( 150, 150 ), array( 'class' => 'ui small rounded spaced image' ) ) . '</a>',
			);

			$activity_id = bp_activity_add( $args );			
			update_post_meta( $post_id, 'bp_activity_id', $activity_id );
			
				if ( $verbose ) 
				{
					echo "<br>BP activity post:";
					var_dump($args);
				}
			}
			}
			}


				// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
				require_once ABSPATH . 'wp-admin/includes/image.php';

				if ( file_exists( $thumbPath ) ) {
					if ( filesize( $thumbPath ) > 5 ) {
							// Generate the metadata for the attachment, and update the database record.
							$attach_data = wp_generate_attachment_metadata( $attach_id, $thumbPath );
							wp_update_attachment_metadata( $attach_id, $attach_data );

						if ( $verbose ) {
							var_dump( $attach_data );
						}

							// if ($width) update_post_meta( $post_id, 'picture-width', $width );
							// if ($height) update_post_meta( $post_id, 'picture-height', $height );
					}
				}
			}

		}

		static function updatePicture( $post_id, $overwrite = false, $verbose = false ) {
			// update size and thumb

			if ( $verbose ) {
				echo "updatePicture #$post_id ";
			}

			if ( ! $post_id ) {
				return;
			}

			$options = get_option( 'VWpictureGalleryOptions' );

			$src = get_post_meta( $post_id, 'picture-source-file', true );
			if ( ! $src ) {
				return; // source path missing
			}

			if ( ! file_exists( $src ) ) {
				return; // source file missing
			}

			$post = get_post( $post_id );

						// add to Media Library
						$filetype = wp_check_filetype( $src );

						$attachment_args = array(
							'guid'           => self::path2url( $src ),
							'post_parent'    => $post_id,
							'post_mime_type' => $filetype['type'],
							'post_title'     => sanitize_text_field( $post->post_title ),
							'post_content'   => get_the_excerpt( $post ),
							'post_status'    => 'inherit',
						);

						if ( $verbose ) {
							var_dump( $attachment_args );
						}

						// delete previous, if already present
						$attachments = get_posts( $attachment_args );
						if ( $attachments ) {
							foreach ( $attachments as $attachment ) {
								wp_delete_attachment( $attachment->ID, true );
							}
						}

						if ( $options['mediaLibrary'] ) {
							$attach_id = wp_insert_attachment( $attachment_args, $src );

							if ( $verbose ) {
								echo " mediaLibrary attachment #$attach_id ";
							}

							// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
							require_once ABSPATH . 'wp-admin/includes/image.php';

							// Generate the metadata for the attachment, and update the database record.
							$attach_data = wp_generate_attachment_metadata( $attach_id, $src );
							if ( ! empty( $attach_data ) ) {
								wp_update_attachment_metadata( $attach_id, $attach_data );
							}
							if ( $verbose ) {
								var_dump( $attach_data );
							}
						}

						$srcImage = self::imagecreatefromfile( $src );
						if ( ! $srcImage ) {
							return;
						}

						list($width, $height) = getimagesize( $src );

						if ( $width ) {
							update_post_meta( $post_id, 'picture-width', $width );
						}
						if ( $height ) {
							update_post_meta( $post_id, 'picture-height', $height );
						}

						$thumbPath = get_post_meta( $post_id, 'picture-thumbnail', true );
						if ( ! $thumbPath || $overwrite ) {
							$path      = dirname( $src );
							$thumbPath = $path . '/' . $post_id . '_thumb.jpg';

							self::generateThumbnail( $src, $thumbPath, $post_id );
						}
		}


		static function humanAge( $t ) {
			if ( $t < 30 ) {
				return 'NOW';
			}
			return sprintf( '%d%s%d%s%d%s', floor( $t / 86400 ), 'd ', floor( $t / 3600 ) % 24, 'h ', floor( $t / 60 ) % 60, 'm' );
		}


		static function humanFilesize( $bytes, $decimals = 2 ) {
			$sz     = 'BKMGTP';
			$factor = floor( ( strlen( $bytes ) - 1 ) / 3 );
			return sprintf( "%.{$decimals}f", $bytes / pow( 1024, $factor ) ) . @$sz[ $factor ];
		}


		static function path2url( $file, $Protocol = 'https://' ) {
			if ( is_ssl() && $Protocol == 'http://' ) {
				$Protocol = 'https://';
			}

			$url = $Protocol . $_SERVER['HTTP_HOST'];

			// on godaddy hosting uploads is in different folder like /var/www/clients/ ..
			$upload_dir = wp_upload_dir();
			if ( strstr( $file, $upload_dir['basedir'] ) ) {
				return $upload_dir['baseurl'] . str_replace( $upload_dir['basedir'], '', $file );
			}

			// folder under WP path
			require_once ABSPATH . 'wp-admin/includes/file.php';
			if ( strstr( $file, get_home_path() ) ) {
				return site_url() . '/' . str_replace( get_home_path(), '', $file );
			}

			// under document root
			if ( strstr( $file, $_SERVER['DOCUMENT_ROOT'] ) ) {
				return $url . str_replace( $_SERVER['DOCUMENT_ROOT'], '', $file );
			}

			return $url . $file;
		}


		static function path2stream( $path, $withExtension = true ) {
			$options = get_option( 'VWpictureGalleryOptions' );

			$stream = substr( $path, strlen( $options['streamsPath'] ) );
			if ( $stream[0] == '/' ) {
				$stream = substr( $stream, 1 );
			}

			if ( ! file_exists( $options['streamsPath'] . '/' . $stream ) ) {
				return '';
			} elseif ( $withExtension ) {
				return $stream;
			} else {
				return pathinfo( $stream, PATHINFO_FILENAME );
			}
		}

		// ! Import
		static function importFilesClean() {
			$options = get_option( 'VWpictureGalleryOptions' );

			if ( ! $options['importClean'] ) {
				return;
			}
			if ( ! file_exists( $options['importPath'] ) ) {
				return;
			}
			if ( ! file_exists( $options['uploadsPath'] ) ) {
				return;
			}

			// last cleanup
			$lastFile = $options['uploadsPath'] . '/importCleanLast.txt';
			if ( file_exists( $lastFile ) ) {
				$lastClean = file_get_contents( $lastFile );
			}

			// cleaned recently
			if ( $lastClean > time() - 36000 ) {
				return;
			}

			// start clean

			// save time
			$myfile = fopen( $lastFile, 'w' );
			if ( ! $myfile ) {
				return;
			}
			fwrite( $myfile, time() );
			fclose( $myfile );

			// scan files and clean
			$folder         = $options['importPath'];
			$extensions     = self::extensions_picture();
			$ignored        = array( '.', '..', '.svn', '.htaccess' );
			$expirationTime = time() - $options['importClean'] * 86400;

			$fileList = scandir( $folder );
			foreach ( $fileList as $fileName ) {
				if ( in_array( $fileName, $ignored ) ) {
					continue;
				}
				if ( ! in_array( strtolower( pathinfo( $fileName, PATHINFO_EXTENSION ) ), $extensions ) ) {
					continue;
				}

				if ( filemtime( $folder . $fileName ) < $expirationTime ) {
					unlink( $folder . $fileName );
				}
			}

		}

		static function importFilesSelect( $prefix, $extensions, $folder ) {
			if ( ! file_exists( $folder ) ) {
				return "<div class='error'>Picture folder not found: $folder !</div>";
			}

			self::importFilesClean();

			$htmlCode .= '';

			// import files
			if ( $_POST['import'] ) {
				$importFiles = isset( $_POST['importFiles'] ) ? (array) $_POST['importFiles'] : array();

				if ( count( $importFiles ) ) {

					$owner = intval( $_POST['owner'] );

					global $current_user;
					get_currentuserinfo();

					if ( ! $owner ) {
						$owner = $current_user->ID;
					} elseif ( $owner != $current_user->ID && ! current_user_can( 'edit_users' ) ) {
						return 'Only admin can import for others!';
					}

					// handle one or many galleries
					$gallery = sanitize_text_field( $_POST['gallery'] );

					// if csv sanitize as array
					if ( strpos( $gallery, ',' ) !== false ) {
						$galleries = explode( ',', $gallery );
						foreach ( $galleries as $key => $value ) {
							$galleries[ $key ] = sanitize_file_name( trim( $value ) );
						}
						$gallery = $galleries;
					}

					if ( ! $gallery ) {
						return 'Importing requires a gallery name!';
					}

					// handle one or many tags
					$tag = sanitize_text_field( $_POST['tag'] );

					// if csv sanitize as array
					if ( strpos( $tag, ',' ) !== false ) {
						$tags = explode( ',', $gallery );
						foreach ( $tags as $key => $value ) {
							$tags[ $key ] = sanitize_file_name( trim( $value ) );
						}
						$tag = $tags;
					}

					$description = sanitize_text_field( $_POST['description'] );

					$category = sanitize_file_name( $_POST['category'] );

					foreach ( $importFiles as $fileName ) {
						// $fileName = sanitize_file_name($fileName);
						$ext = pathinfo( $fileName, PATHINFO_EXTENSION );
						if ( ! $ztime = filemtime( $folder . $fileName ) ) {
							$ztime = time();
						}
						$pictureName = basename( $fileName, '.' . $ext ) . ' ' . date( 'M j', $ztime );

						$htmlCode .= self::importFile( $folder . $fileName, $pictureName, $owner, $gallery, $category, $tag, $description );
					}
				} else {
					$htmlCode .= '<div class="warning">No files selected to import!</div>';
				}
			}

			// delete files
			if ( $_POST['delete'] ) {

				$importFiles = isset( $_POST['importFiles'] ) ? (array) $_POST['importFiles'] : array();

				if ( count( $importFiles ) ) {
					foreach ( $importFiles as $fileName ) {
						$htmlCode .= '<BR>Deleting ' . esc_html( $fileName ) . ' ... ';
						$fileName  = sanitize_file_name( $fileName );
						if ( ! unlink( $folder . $fileName ) ) {
							$htmlCode .= 'Removing file failed!';
						} else {
							$htmlCode .= 'Success.';
						}
					}
				} else {
					$htmlCode .= '<div class="warning">No files selected to delete!</div>';
				}
			}

			// preview file
			if ( $preview_name = sanitize_text_field( $_GET['import_preview'] ) ) {
				// $preview_name = sanitize_file_name($preview_name);
				$preview_url = self::path2url( $folder . $preview_name );
				$htmlCode   .= '<h4>Preview ' . esc_html( $preview_name ) . '</h4>';
				$htmlCode   .= '<IMG SRC="' . esc_attr( $preview_url ) . '">';
			}

			// list files
			$fileList = scandir( $folder );

			$ignored = array( '.', '..', '.svn', '.htaccess' );

			$prefixL = strlen( $prefix );

			// list by date
			$files = array();
			foreach ( $fileList as $fileName ) {

				if ( in_array( $fileName, $ignored ) ) {
					continue;
				}
				if ( ! in_array( strtolower( pathinfo( $fileName, PATHINFO_EXTENSION ) ), $extensions ) ) {
					continue;
				}
				if ( $prefixL ) {
					if ( substr( $fileName, 0, $prefixL ) != $prefix ) {
						continue;
					}
				}

					$files[ $fileName ] = filemtime( $folder . $fileName );
			}

			arsort( $files );
			$fileList = array_keys( $files );

			if ( ! $fileList ) {
				$htmlCode .= "<div class='warning'>No matching files found!</div>";
			} else {
				$htmlCode .=
					'<script language="JavaScript">
function toggleImportBoxes(source) {
  var checkboxes = new Array();
  checkboxes = document.getElementsByName(\'importFiles\');
  for (var i = 0; i < checkboxes.length; i++)
    checkboxes[i].checked = source.checked;
}
</script>';
				$htmlCode .= "<table class='widefat videowhisperTable'>";
				$htmlCode .= '<thead class=""><tr><th><input type="checkbox" onClick="toggleImportBoxes(this)" /></th><th>File Name</th><th>Preview</th><th>Size</th><th>Date</th></tr></thead>';

				$tN = 0;
				$tS = 0;

				foreach ( $fileList as $fileName ) {
					$fsize = filesize( $folder . $fileName );
					$tN++;
					$tS += $fsize;

					$htmlCode .= '<tr>';
					$htmlCode .= '<td><input type="checkbox" name="importFiles[]" value="' . $fileName . '"' . ( $fileName == $preview_name ? ' checked' : '' ) . '></td>';
					$htmlCode .= "<td>$fileName</td>";
					$htmlCode .= '<td>';
					$link      = add_query_arg(
						array(
							'gallery_import' => $prefix,
							'import_preview' => $fileName,
						),
						get_permalink()
					);

					$htmlCode .= " <a class='button size_small g-btn type_blue' href='" . esc_url( $link ) . "'>Play</a> ";
					echo '</td>';
					$htmlCode .= '<td>' . self::humanFilesize( $fsize ) . '</td>';
					$htmlCode .= '<td>' . date( 'jS F Y H:i:s', filemtime( $folder . $fileName ) ) . '</td>';
					$htmlCode .= '</tr>';
				}
				$htmlCode .= '<tr><td></td><td>' . esc_html( $tN ) . ' files</td><td></td><td>' . self::humanFilesize( $tS ) . '</td><td></td></tr>';
				$htmlCode .= '</table>';

			}
			return $htmlCode;

		}

		static function importFilesCount( $prefix, $extensions, $folder ) {
			if ( ! file_exists( $folder ) ) {
				return '';
			}

			$kS = $k = 0;

			$fileList = scandir( $folder );

			$ignored = array( '.', '..', '.svn', '.htaccess' );

			$prefixL = strlen( $prefix );

			foreach ( $fileList as $fileName ) {

				if ( in_array( $fileName, $ignored ) ) {
					continue;
				}
				if ( ! in_array( strtolower( pathinfo( $fileName, PATHINFO_EXTENSION ) ), $extensions ) ) {
					continue;
				}
				if ( $prefixL ) {
					if ( substr( $fileName, 0, $prefixL ) != $prefix ) {
						continue;
					}
				}

					$k++;
				$kS += filesize( $folder . $fileName );
			}

			return $k . ' (' . self::humanFilesize( $kS ) . ')';
		}


		static function importFile( $path, $name, $owner, $galleries, $category = '', $tags = '', $description = '', &$post_id = null, $guest = false ) {

			// when using special guest shortcode, visitors can also upload
			if (!$guest)
			{
				if ( ! $owner ) {
					return '<br>Missing owner! Specify owner or use guest mode.';
				}

				if ( ! $galleries ) {
					return '<br>Missing galleries!';
				}
		   }

		    if (!$owner) $owner = 0;

			$options = self::getOptions();

			if (!$guest) if ( ! self::hasPriviledge( $options['shareList'] ) ) {
				return '<br>' . __( 'You do not have permissions to share pictures!', 'picture-gallery' );
			}

			if ( ! file_exists( $path ) ) {
				return "<br>$name: File missing: $path";
			}

			// handle one or many galleries
			if ( is_array( $galleries ) ) {
				$gallery = sanitize_file_name( current( $galleries ) );
			} else {
				$gallery = sanitize_file_name( $galleries );
			}

			if ( ! $gallery ) {
				return '<br>Missing gallery!';
			}

			$htmlCode = 'File import: ';

			// uploads/owner/gallery/src/file
			$dir = $options['uploadsPath'];
			if ( ! file_exists( $dir ) ) {
				mkdir( $dir );
			}

			$dir .= '/' . $owner;
			if ( ! file_exists( $dir ) ) {
				mkdir( $dir );
			}

			$dir .= '/' . $gallery;
			if ( ! file_exists( $dir ) ) {
				mkdir( $dir );
			}

			// $dir .= '/src';
			// if (!file_exists($dir)) mkdir($dir);

			if ( ! $ztime = filemtime( $path ) ) {
				$ztime = time();
			}

			$ext     = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
			$newFile = md5( uniqid( $owner, true ) ) . '.' . $ext;
			$newPath = $dir . '/' . $newFile;

			// $htmlCode .= "<br>Importing $name as $newFile ... ";

			if ( $options['deleteOnImport'] ) {
				if ( ! rename( $path, $newPath ) ) {
					$htmlCode .= 'Rename failed. Trying copy ...';
					if ( ! copy( $path, $newPath ) ) {
						$htmlCode .= 'Copy also failed. Import failed!';
						return $htmlCode;
					}
					// else $htmlCode .= 'Copy success ...';

					if ( ! unlink( $path ) ) {
						$htmlCode .= 'Removing original file failed!';
					}
				}
			} else {
				// just copy
				if ( ! copy( $path, $newPath ) ) {
					$htmlCode .= 'Copy failed. Import failed!';
					return $htmlCode;
				}
			}

			// $htmlCode .= 'Moved source file ...';

			$timeZone = get_option( 'gmt_offset' ) * 3600;
			$postdate = date( 'Y-m-d H:i:s', $ztime + $timeZone );

			$post = array(
				'post_name'    => $name,
				'post_title'   => $name,
				'post_author'  => $owner,
				'post_type'    => $options['custom_post'],
				'post_status'  => 'publish',
				// 'post_date'   => $postdate,
				'post_content' => $description,
			);

			if ( ! self::hasPriviledge( $options['publishList'] ) ) {
				$post['post_status'] = 'pending';
			}

			$post_id = wp_insert_post( $post );
			if ( $post_id ) {
				update_post_meta( $post_id, 'picture-source-file', $newPath );

				wp_set_object_terms( $post_id, $galleries, $options['custom_taxonomy'] );

				if ( $tags ) {
					wp_set_object_terms( $post_id, $tags, 'post_tag' );
				}

				if ( $category ) {
					wp_set_post_categories( $post_id, array( $category ) );
				}

				self::updatePicture( $post_id, true );
				self::updatePostThumbnail( $post_id, true );

				if ( $post['post_status'] == 'pending' ) {
					$htmlCode .= __( 'Picture was submitted and is pending approval.', 'picture-gallery' );
				} else {
					$htmlCode .= '<br>' . __( 'Picture was published', 'picture-gallery' ) . ': <a href=' . get_post_permalink( $post_id ) . '> #' . $post_id . ' ' . $name . '</a>' . __( 'Thumbnail will be processed shortly.', 'picture-gallery' );
				}
			} else {
				$htmlCode .= '<br>Picture post creation failed!';
			}

			return $htmlCode . ' .';
		}


	}

}


// instantiate
if ( class_exists( 'VWpictureGallery' ) ) {
	$pictureGallery = new VWpictureGallery();
}

// Actions and Filters
if ( isset( $pictureGallery ) ) {

	register_activation_hook( __FILE__, array( &$pictureGallery, 'install' ) );
	register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

	add_action( 'init', array( &$pictureGallery, 'picture_post' ), 0 );
	add_action( 'admin_menu', array( &$pictureGallery, 'admin_menu' ) );
	add_action( 'admin_bar_menu', array( &$pictureGallery, 'admin_bar_menu' ), 100 );

	add_action( 'plugins_loaded', array( &$pictureGallery, 'plugins_loaded' ) );

	// archive
	add_filter( 'archive_template', array( 'VWpictureGallery', 'archive_template' ) );

	// page template
	add_filter( 'single_template', array( &$pictureGallery, 'single_template' ) );
}
