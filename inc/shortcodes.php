<?php
namespace VideoWhisper\PictureGallery;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

trait Shortcodes {


		static function videowhisper_pictures( $atts ) {

			$options = get_option( 'VWpictureGalleryOptions' );

			$atts = shortcode_atts(
				array(
					'menu'            => sanitize_text_field( $options['listingsMenu'] ),					
					'perpage'         => $options['perPage'],
					'perrow'          => '',
					'gallery'         => '',
					'order_by'        => '',
					'category_id'     => '',
					'select_category' => '1',
					'select_order'    => '1',
					'select_page'     => '1',   // pagination
					'select_tags'     => '1',
					'select_name'     => '1',
					'include_css'     => '1',
					'tags'            => '',
					'name'            => '',
					'id'              => '',
				),
				$atts,
				'videowhisper_pictures'
			);

			$id = $atts['id'];
			if ( ! $id ) {
				$id = 'vwp' . uniqid();
			}

			self::enqueueUI();

			$ajaxurl = admin_url() . 'admin-ajax.php?action=vwpg_pictures&menu=' . $atts['menu'] . '&pp=' . $atts['perpage'] . '&pr=' . $atts['perrow'] . '&gallery=' . urlencode( $atts['gallery'] ) . '&ob=' . $atts['order_by'] . '&cat=' . $atts['category_id'] . '&sc=' . $atts['select_category'] . '&so=' . $atts['select_order'] . '&sp=' . $atts['select_page'] . '&sn=' . $atts['select_name'] . '&sg=' . $atts['select_tags'] . '&id=' . $id . '&tags=' . urlencode( $atts['tags'] ) . '&name=' . urlencode( $atts['name'] );

			$htmlCode = <<<HTMLCODE
<script type="text/javascript">

var aurl$id = '$ajaxurl';
var loader$id;


	function loadPictures$id(message){

	if (message)
	if (message.length > 0)
	{
	  jQuery("#videowhisperPictures$id").html(message);
	}

		if (loader$id) loader$id.abort();

		loader$id = jQuery.ajax({
			url: aurl$id,
			success: function(data) {
				jQuery("#videowhisperPictures$id").html(data);
				
				try{
				jQuery(".ui.dropdown:not(.multi,.fpsDropdown)").dropdown();
				jQuery(".ui.rating.readonly").rating("disable");
				}
				catch(error) {console.log("Interface error loadPictures", error );}	 
							
			}
		});
	}


	jQuery(function(){
		loadPictures$id();
		setInterval("loadPictures$id('')", 60000);
	});


</script>

<div id="videowhisperPictures$id">
    Loading Pictures...
</div>

HTMLCODE;

			// if ($atts['include_css']) wp_add_inline_style('vwpg_pictures', html_entity_decode( stripslashes( $options['customCSS']) )  );
			if ( $atts['include_css'] ) {
				$htmlCode .= '<style type="text/css">' . html_entity_decode( stripslashes( $options['customCSS'] ) ) . '</style>';
			}

			// $htmlCode .= '<style type="text/css">' . . '</style>';

			return $htmlCode;
		}

        		// ! AJAX implementation
		static function scripts() {
            wp_enqueue_script( 'jquery' );
       }

       static function enqueueUI() {
           wp_enqueue_script( 'jquery' );

           // wp_enqueue_style( 'semantic', '//cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css');
           // wp_enqueue_script( 'semantic', '//cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.js', array('jquery'));

           wp_enqueue_style( 'semantic', plugin_dir_url( __FILE__ ) . '/scripts/semantic/semantic.min.css' );
           wp_enqueue_script( 'semantic', plugin_dir_url( __FILE__ ) . '/scripts/semantic/semantic.min.js', array( 'jquery' ) );
       }

       static function vwpg_pictures() {
           $options = get_option( 'VWpictureGalleryOptions' );

           $perPage = (int) $_GET['pp'];
           if ( ! $perPage ) {
               $perPage = $options['perPage'];
           }

           $gallery = sanitize_file_name( $_GET['gallery'] );

           $id = sanitize_file_name( $_GET['id'] );

           $menu             = boolval( $_GET['menu'] ?? false );

           $category = intval( $_GET['cat'] ?? 0 );

           $page   = intval( $_GET['p'] ?? 0 );
           $offset = $page * $perPage;

           $perRow = intval( $_GET['pr'] ?? 0 ) ;

           // order
           $order_by = sanitize_file_name( $_GET['ob'] );
           if ( ! $order_by ) {
               $order_by = 'post_date';
           }

           // options
           $selectCategory = (int) $_GET['sc'];
           $selectOrder    = (int) $_GET['so'];
           $selectPage     = (int) $_GET['sp'];

           $selectName = (int) $_GET['sn'];
           $selectTags = (int) $_GET['sg'];

           $author_id = intval( $_GET['author_id'] ?? 0 );

           // tags,name search
           $tags = sanitize_text_field( $_GET['tags'] ?? '' );
           $name = sanitize_file_name( $_GET['name'] ?? '' );
           if ( $name == 'undefined' ) {
               $name = '';
           }
           if ( $tags == 'undefined' ) {
               $tags = '';
           }

           // query
           $args = array(
               'post_type'      => $options['custom_post'],
               'post_status'    => 'publish',
               'posts_per_page' => $perPage,
               'offset'         => $offset,
               'order'          => 'DESC',
           );

           switch ( $order_by ) {
               case 'post_date':
                   $args['orderby'] = 'post_date';
                   break;

               case 'rand':
                   $args['orderby'] = 'rand';
                   break;

               default:
                   $args['orderby']  = 'meta_value_num';
                   $args['meta_key'] = $order_by;
                   break;
           }

           if ( $gallery ) {
               $args['gallery'] = $gallery;
           }

           if ( $category ) {
               $args['category'] = $category;
           }

           if ( $tags ) {
               $tagList = explode( ',', $tags );
               foreach ( $tagList as $key => $value ) {
                   $tagList[ $key ] = trim( $tagList[ $key ] );
               }

               $args['tax_query'] = array(
                   array(
                       'taxonomy' => 'post_tag',
                       'field'    => 'slug',
                       'operator' => 'AND',
                       'terms'    => $tagList,
                   ),
               );
           }

           if ( $name ) {
               $args['s'] = $name;
           }

           // user permissions
           $pmEnabled = 0;
           if ( is_user_logged_in() ) {
               $current_user = wp_get_current_user();
               if ( in_array( 'administrator', $current_user->roles ) ) {
                   $isAdministrator = 1;
               }
               $isID = $current_user->ID;

               if ( is_plugin_active( 'paid-membership/paid-membership.php' ) ) {
                   $pmEnabled = 1;
               }
           }

           // get items

           $postslist = get_posts( $args );

           ob_clean();
           // output

           $ajaxurl = admin_url() . 'admin-ajax.php?action=vwpg_pictures&menu=' . $menu . '&pp=' . $perPage . '&pr=' . $perRow . '&gallery=' . urlencode( $gallery ) . '&sc=' . $selectCategory . '&so=' . $selectOrder . '&sn=' . $selectName . '&sg=' . $selectTags . '&sp=' . $selectPage . '&id=' . $id;

           // without page: changing goes to page 1 but selection persists
           $ajaxurlC = $ajaxurl . '&cat=' . $category . '&ob=' . $order_by . '&tags=' . urlencode( $tags ) . '&name=' . urlencode( $name ); // sel ord
           $ajaxurlO = $ajaxurl . '&ob=' . $order_by . '&ob=' . $order_by . '&tags=' . urlencode( $tags ) . '&name=' . urlencode( $name ); // sel cat

           $ajaxurlCO = $ajaxurl . '&cat=' . $category . '&ob=' . $order_by; // select tag name

           $ajaxurlA = $ajaxurl . '&cat=' . $category . '&ob=' . $order_by . '&tags=' . urlencode( $tags ) . '&name=' . urlencode( $name );


//start menu 
if ( $menu ) {
           echo '
<style>
   .vwItemsSidebar {
   grid-area: sidebar;
 }

 .vwItemsContent {
   grid-area: content;
 }

.vwItemsWrapper {
   display: grid;
   grid-gap: 4px;
   grid-template-columns: 120px  auto;
   grid-template-areas: "sidebar content";
   color: #444;
 }

 .ui .title { height: auto !important; background-color: inherit !important}
 .ui .content {margin: 0 !important; }
 .vwItemsSidebar .menu { max-width: 120px !important;}

</style>
<div class="vwItemsWrapper">
<div class="vwItemsSidebar">';

           if ( $selectCategory ) {
               echo '
<div class="ui ' . esc_attr( $options['interfaceClass'] ?? '' ) . ' accordion small">

 <div class="active title">
   <i class="dropdown icon"></i>
   ' . __( 'Category', 'picture-gallery' ) . ' ' . ( esc_html( $category ) ? '<i class="check icon small"></i>' : '' ) . '
 </div>
 <div class="active content">
 <div class="ui ' . esc_attr( $options['interfaceClass'] ?? '' ) . ' vertical menu small">
 ';
               echo '  <a class="' . ( $category == 0 ? 'active' : '' ) . ' item" onclick="aurl' . esc_attr( $id ) . '=\'' . esc_url( $ajaxurlO ) . '&cat=0\'; loadPictures' . esc_attr( $id ) . '(\'<div class=\\\'ui active inline text large loader\\\'>' . __( 'Loading category', 'picture-gallery' ) . '...</div>\')">' . __( 'All Categories', 'picture-gallery' ) . '</a> ';

               $categories = get_categories( array( 'taxonomy' => 'category' ) );
               foreach ( $categories as $cat ) {
                   echo '  <a class="' . ( $category == $cat->term_id ? 'active' : '' ) . ' item" onclick="aurl' . esc_attr( $id ) . '=\'' . esc_html( $ajaxurlO ) . '&cat=' . esc_attr( $cat->term_id ) . '\'; loadPictures' . esc_attr( $id ) . '(\'<div class=\\\'ui active inline text large loader\\\'>' . __( 'Loading category', 'picture-gallery' ) . '...</div>\')">' . esc_html( $cat->name ) . '</a> ';
               }

               echo '</div>

 </div>
</div>';
           }
           
           if ( $selectOrder ) {

               $optionsOrders = array(
                   'post_date'  => __( 'Added Recently', 'picture-gallery' ),
                   'picture-views' => __( 'Views', 'picture-gallery' ),
                   'picture-lastview' => __( 'Watched Recently', 'picture-gallery' ),
                   'rand'       => __( 'Random', 'picture-gallery' ),
               
               );

               if ( $options['rateStarReview'] ) {
                   $optionsOrders['rateStarReview_rating']       = __( 'Rating', 'picture-gallery' );
                   $optionsOrders['rateStarReview_ratingNumber'] = __( 'Ratings Number', 'picture-gallery' );
                   $optionsOrders['rateStarReview_ratingPoints'] = __( 'Rate Popularity', 'picture-gallery' );

                   if ( $category ) {
                       $optionsOrders[ 'rateStarReview_rating_category' . $category ]       = __( 'Rating', 'picture-gallery' ) . ' ' . __( 'in Category', 'picture-gallery' );
                       $optionsOrders[ 'rateStarReview_ratingNumber_category' . $category ] = __( 'Ratings Number', 'picture-gallery' ) . ' ' . __( 'in Category', 'picture-gallery' );
                       $optionsOrders[ 'rateStarReview_ratingPoints_category' . $category ] = __( 'Rate Popularity', 'picture-gallery' ) . ' ' . __( 'in Category', 'picture-gallery' );
                   }
               }

               echo '
<div class="ui ' . esc_attr( $options['interfaceClass'] ?? '' ) . ' accordion small">

 <div class="title">
   <i class="dropdown icon"></i>
   ' . __( 'Order By', 'picture-gallery' ) . ' ' . ( $order_by != 'default' ? '<i class="check icon small"></i>' : '' ) . '
 </div>
 <div class="' . ( $order_by != 'default' ? 'active' : '' ) . ' content">
 <div class="ui ' . esc_attr( $options['interfaceClass'] ?? '' ) . ' vertical menu small">
 ';

               foreach ( $optionsOrders as $key => $value ) {
                   echo '  <a class="' . ( $order_by == $key ? 'active' : '' ) . ' item" onclick="aurl' . esc_attr( $id ) . '=\'' . esc_url( $ajaxurlC ) . '&ob=' . esc_attr( $key ) . '\'; loadPictures' . esc_attr( $id ) . '(\'<div class=\\\'ui active inline text large loader\\\'>' . __( 'Ordering Rooms', 'picture-gallery' ) . '...</div>\')">' . esc_html( $value ) . '</a> ';
               }

               echo '</div>

 </div>
</div>';

           }

           echo '
<PRE style="display: none"><SCRIPT language="JavaScript">
jQuery(document).ready(function()
{
jQuery(".ui.accordion").accordion({exclusive:false});
});
</SCRIPT></PRE>
';
           echo '</div><div class="vwItemsContent">';
       }
       
       //end menu
       
           // options
           // echo '<div class="videowhisperListOptions">';

           // $htmlCode .= '<div class="ui form"><div class="inline fields">';
           echo '<div class="ui ' . esc_attr( $options['interfaceClass'] ?? '' ) . ' tiny equal width form"><div class="inline fields">';

           if ( $selectCategory && ! $menu ) {
               echo '<div class="field">' . wp_dropdown_categories( 'show_count=0&echo=0&name=category' . esc_attr( $id ) . '&hide_empty=1&class=ui+dropdown&show_option_all=' . __( 'All', 'picture-gallery' ) . '&selected=' . $category ) . '</div>';
               echo '<script>var category' . esc_attr( $id ) . ' = document.getElementById("category' . esc_attr( $id ) . '"); 			category' . esc_attr( $id ) . '.onchange = function(){aurl' . esc_attr( $id ) . '=\'' . esc_url( $ajaxurlO ) . '&cat=\'+ this.value; loadPictures' . esc_attr( $id ) . '(\'<div class=\\\'ui active inline text large loader\\\'>Loading category...</div>\')}
           </script>';
           }

           if ( $selectOrder && ! $menu ) {
               echo '<div class="field"><select class="ui dropdown" id="order_by' . esc_attr( $id ) . '" name="order_by' . esc_attr( $id ) . '" onchange="aurl' . esc_attr( $id ) . '=\'' . esc_url( $ajaxurlC ) . '&ob=\'+ this.value; loadPictures' . esc_attr( $id ) . '(\'<div class=\\\'ui active inline text large loader\\\'>Ordering pictures...</div>\')">';
               echo '<option value="">' . __( 'Order By', 'picture-gallery' ) . ':</option>';
               echo '<option value="post_date"' . ( $order_by == 'post_date' ? ' selected' : '' ) . '>' . __( 'Date Added', 'picture-gallery' ) . '</option>';
               echo '<option value="picture-views"' . ( $order_by == 'picture-views' ? ' selected' : '' ) . '>' . __( 'Views', 'picture-gallery' ) . '</option>';
               echo '<option value="picture-lastview"' . ( $order_by == 'picture-lastview' ? ' selected' : '' ) . '>' . __( 'Viewed Recently', 'picture-gallery' ) . '</option>';

               if ( $options['rateStarReview'] ) {

                   echo '<option value="rateStarReview_rating"' . ( $order_by == 'rateStarReview_rating' ? ' selected' : '' ) . '>' . __( 'Rating', 'picture-gallery' ) . '</option>';
                   echo '<option value="rateStarReview_ratingNumber"' . ( $order_by == 'rateStarReview_ratingNumber' ? ' selected' : '' ) . '>' . __( 'Ratings Number', 'picture-gallery' ) . '</option>';
                   echo '<option value="rateStarReview_ratingPoints"' . ( $order_by == 'rateStarReview_ratingPoints' ? ' selected' : '' ) . '>' . __( 'Rate Popularity', 'picture-gallery' ) . '</option>';

               }

               echo '<option value="rand"' . ( $order_by == 'rand' ? ' selected' : '' ) . '>' . __( 'Random', 'picture-gallery' ) . '</option>';

               echo '</select></div>';
           }

           if ( $selectTags || $selectName ) {

               echo '<div class="field"></div>'; // separator

               if ( $selectTags ) {
                   echo '<div class="field" data-tooltip="Tags, Comma Separated"><div class="ui left icon input"><i class="tags icon"></i><INPUT class="videowhisperInput" type="text" size="12" name="tags" id="tags" placeholder="' . __( 'Tags', 'picture-gallery' ) . '" value="' . htmlspecialchars( $tags ) . '">
                   </div></div>';
               }

               if ( $selectName ) {
                   echo '<div class="field"><div class="ui left corner labeled input"><INPUT class="videowhisperInput" type="text" size="12" name="name" id="name" placeholder="' . __( 'Name', 'picture-gallery' ) . '" value="' . htmlspecialchars( $name ) . '">
 <div class="ui left corner label">
   <i class="asterisk icon"></i>
 </div>
                   </div></div>';
               }

               // search button
               echo '<div class="field" data-tooltip="Search by Tags and/or Name"><button class="ui icon button" type="submit" name="submit" id="submit" value="' . __( 'Search', 'picture-gallery' ) . '" onclick="aurl' . esc_attr( $id ) . '=\'' . esc_url( $ajaxurlCO ) . '&tags=\' + document.getElementById(\'tags\').value +\'&name=\' + document.getElementById(\'name\').value; loadPictures' . esc_attr( $id ) . '(\'<div class=\\\'ui active inline text large loader\\\'>Searching Pictures...</div>\')"><i class="search icon"></i></button></div>';

           }

           // reload button
           if ( $selectCategory || $selectOrder || $selectTags || $selectName ) {
               echo '<div class="field"></div> <div class="field" data-tooltip="Reload"><button class="ui icon button" type="submit" name="reload" id="reload" value="' . __( 'Reload', 'picture-gallery' ) . '" onclick="aurl' . esc_attr( $id ) . '=\'' . esc_url( $ajaxurlA ) . '\'; loadPictures' . esc_attr( $id ) . '(\'<div class=\\\'ui active inline text large loader\\\'>Reloading Pictures...</div>\')"><i class="sync icon"></i></button></div>';
           }

           echo '</div></div>';

           // list
           if ( count( $postslist ) > 0 ) {
               $k = 0;
               foreach ( $postslist as $item ) {
                   if ( $perRow ) {
                       if ( $k ) {
                           if ( $k % $perRow == 0 ) {
                                                       echo '<br>';
                           }
                       }
                   }

                           $imagePath = get_post_meta( $item->ID, 'picture-thumbnail', true );

                       $views = intval(get_post_meta( $item->ID, 'picture-views', true ));

                   if ( ! $views ) {
                       $views = 0;
                   }

                   $age = self::humanAge( time() - strtotime( $item->post_date ) );

                   $info   = '' . __( 'Title', 'picture-gallery' ) . ': ' . esc_html( $item->post_title ) . "\r\n" . __( 'Age', 'picture-gallery' ) . ': ' . esc_html( $age ) . "\r\n" . __( 'Views', 'picture-gallery' ) . ': ' . esc_html( $views );
                   $views .= ' ' . __( 'views', 'picture-gallery' );

                   $canEdit = 0;
                   if ( $options['editContent'] ) {
                       if ( ( $isAdministrator ?? false) || ( isset( $isID ) && $item->post_author == $isID ) ) {
                           $canEdit = 1;
                       }
                   }

                   echo '<div class="videowhisperPicture">';
                   echo '<a href="' . get_permalink( $item->ID ) . '" title="' . esc_attr( $info ) . '"><div class="videowhisperPictureTitle">' . esc_html( $item->post_title ) . '</div></a>';
                   echo '<div class="videowhisperPictureDate">' . esc_html( $age ) . '</div>';
                   echo '<div class="videowhisperPictureViews">' . esc_html( $views ) . '</div>';

                   $ratingCode = '';
                   if ( $options['rateStarReview'] ) {
                       $rating = floatval( get_post_meta( $item->ID, 'rateStarReview_rating', true ) );
                       $max    = 5;
                       if ( $rating > 0 ) {
                           $ratingCode = '<div class="ui star rating yellow readonly" data-rating="' . round( $rating * $max ) . '" data-max-rating="' . $max . '"></div>'; // . number_format($rating * $max,1)  . ' / ' . $max
                       }
                       echo '<div class="videowhisperPictureRating">' . $ratingCode . '</div>';
                   }

                   if ( isset($pmEnabled ) && $pmEnabled && $canEdit ) {
                       echo '<a href="' . esc_url( $options['editURL'] ) . intval( $item->ID ) . '"><div class="videowhisperPictureEdit">' . __( 'EDIT', 'paid-membership' ) . '</div></a>';
                   }

                   if ( ! $imagePath || ! file_exists( $imagePath ) ) {
                       $imagePath = plugin_dir_path( __FILE__ ) . 'no_video.png';
                       self::updatePostThumbnail( $item->ID );
                   } elseif ( $options['mediaLibraryThumb'] ) {

                       $post_thumbnail_id = get_post_thumbnail_id( $item->ID );
                       if ( $post_thumbnail_id ) {
                           $post_featured_image = wp_get_attachment_image_src( $post_thumbnail_id, 'featured_preview' );
                       }

                       if ( ! $post_featured_image ) {
                           self::updatePostThumbnail( $item->ID );
                       }
                   }

                   echo '<a href="' . get_permalink( $item->ID ) . '" title="' . esc_attr( $info ) . '"><IMG src="' . self::path2url( $imagePath ) . '" width="' . esc_attr( $options['thumbWidth'] ) . 'px" height="' . esc_attr( $options['thumbHeight'] ) . 'px" ALT="' . esc_attr( $info ) . '"></a>';

                   echo '</div>
                   ';

                   $k++;
               }
           } else {
               echo __( 'No pictures.', 'picture-gallery' );
           }

           // pagination
           if ( $selectPage ) {
               echo '<BR style="clear:both"><div class="ui form"><div class="inline fields">';

               if ( $page > 0 ) {
                   echo ' <a class="ui labeled icon button" href="JavaScript: void()" onclick="aurl' . esc_attr( $id ) . '=\'' . esc_url( $ajaxurlA ) . '&p=' . intval( $page - 1 ) . '\'; loadPictures' . esc_attr( $id ) . '(\'<div class=\\\'ui active inline text large loader\\\'>Loading previous page...</div>\');"><i class="left arrow icon"></i> ' . __( 'Previous', 'picture-gallery' ) . '</a> ';
               }

               echo '<a class="ui labeled button" href="#"> ' . __( 'Page', 'picture-gallery' ) . ' ' . intval( $page + 1 ) . ' </a>';

               if ( count( $postslist ) >= $perPage ) {
                   echo ' <a class="ui right labeled icon button" href="JavaScript: void()" onclick="aurl' . esc_attr( $id ) . '=\'' . esc_url( $ajaxurlA ) . '&p=' . intval( $page + 1 ) . '\'; loadPictures' . esc_attr( $id ) . '(\'<div class=\\\'ui active inline text large loader\\\'>Loading next page...</div>\');">' . __( 'Next', 'picture-gallery' ) . ' <i class="right arrow icon"></i></a> ';
               }
           }

           //close layout with menu
           if ( $menu ) echo '</div></div>';

           echo self::scriptThemeMode($options);

           // output end
           die;

       }


static function scriptThemeMode($options)
{
	$theme_mode = '';
	
	//check if using the FansPaysite theme and apply the dynamic theme mode
	if (function_exists('fanspaysite_get_current_theme_mode')) $theme_mode = fanspaysite_get_current_theme_mode();
	else $theme_mode = '';

	if (!$theme_mode) $theme_mode = $options['themeMode'] ?? '';

	if (!$theme_mode) return '<!-- No theme mode -->';

	// JavaScript function to apply the theme mode
	return '<script>
	if (typeof setConfiguredTheme !== "function")  // Check if the function is already defined
	{ 

		function setConfiguredTheme(theme) {
			if (theme === "auto") {
				if (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
					document.body.dataset.theme = "dark";
				} else {
					document.body.dataset.theme = "";
				}
			} else {
				document.body.dataset.theme = theme;
			}

			if (document.body.dataset.theme == "dark")
			{
			jQuery("body").find(".ui").addClass("inverted");
			jQuery("body").addClass("inverted");
			}else
			{
				jQuery("body").find(".ui").removeClass("inverted");
				jQuery("body").removeClass("inverted");
			}

			console.log("PictureGallery/setConfiguredTheme:", theme);
		}
	}	

	setConfiguredTheme("' . esc_js($theme_mode) . '");

	</script>';
}


		static function videowhisper_picture( $atts ) {
			$atts = shortcode_atts( array( 'picture' => '0' ), $atts, 'videowhisper_picture' );

			$picture_id = intval( $atts['picture'] );
			if ( ! $picture_id ) {
				return 'shortcode_preview: Missing picture id!';
			}

			$picture = get_post( $picture_id );
			if ( ! $picture ) {
				return 'shortcode_preview: picture #' . $picture_id . ' not found!';
			}

			$options = get_option( 'VWpictureGalleryOptions' );

			// Access Control
			$deny = '';

			// global
			if ( ! self::hasPriviledge( $options['watchList'] ) ) {
				$deny = 'Your current membership does not allow watching pictures.';
			}

			// by galleries
			$lists = wp_get_post_terms( $picture_id, $options['custom_taxonomy'], array( 'fields' => 'names' ) );

			if ( ! is_array( $lists ) ) {
				if ( is_wp_error( $lists ) ) {
					echo 'Error: Can not retrieve "' . esc_html( $options['custom_taxonomy'] ) . '" terms for video post: ' . esc_html( $lists->get_error_message() );
				}

				$lists = array();
			}

			// gallery role required?
			if ( $options['role_gallery'] ) {
				foreach ( $lists as $key => $gallery ) {
					$lists[ $key ] = $gallery = strtolower( trim( $gallery ) );

					// is role
					if ( get_role( $gallery ) ) {
						$deny = 'This picture requires special membership. Your current membership: ' . self::getRoles() . '.';
						if ( self::hasRole( $gallery ) ) {
							$deny = '';
							break;
						}
					}
				}
			}

			// exceptions
			if ( in_array( 'free', $lists ) ) {
				$deny = '';
			}

			if ( in_array( 'registered', $lists ) ) {
				if ( is_user_logged_in() ) {
					$deny = '';
				} else {
					$deny = 'Only registered users can watch this picture. Please login first.';
				}
			}

			if ( in_array( 'unpublished', $lists ) ) {
				$deny = 'This picture has been unpublished.';
			}

			if ( $deny ) {
				$htmlCode .= str_replace( '#info#', $deny, html_entity_decode( stripslashes( $options['accessDenied'] ) ) );
				$htmlCode .= '<br>';
				$htmlCode .= do_shortcode( '[videowhisper_picture_preview picture="' . $picture_id . '"]' ) . self::poweredBy();
				return $htmlCode;
			}

			// update stats
			$views = intval(get_post_meta( $picture_id, 'picture-views', true ));
			if ( ! $views ) {
				$views = 0;
			}
			$views++;
			update_post_meta( $picture_id, 'picture-views', $views );
			update_post_meta( $picture_id, 'picture-lastview', time() );

			// display picture:

			// res
			$vWidth = get_post_meta( $picture_id, 'picture-width', true );
			if ( ! $vWidth ) {
				self::updatePostThumbnail( $picture_id, true );
				$vWidth = get_post_meta( $picture_id, 'picture-width', true );
			}
			$vHeight = get_post_meta( $picture_id, 'picture-height', true );

			// picture
			$imagePath = get_post_meta( $picture_id, 'picture-source-file', true );
			if ( $imagePath ) {
				if ( file_exists( $imagePath ) ) {
					$imageURL = self::path2url( $imagePath );
				}
			}
			// width='$vWidth' height='$vHeight'
				$htmlCode = "<IMG class='ui fluid image rounded vwPictureIMG' SRC='$imageURL'>";

			$htmlCode .= '<style type="text/css">' . html_entity_decode( stripslashes( $options['customCSSpicture'] ) ) . '</style>';

			return $htmlCode;
		}


		// ! update this
		static function videowhisper_picture_preview( $atts ) {
			$atts = shortcode_atts( array( 'picture' => '0' ), $atts, 'shortcode_preview' );

			$picture_id = intval( $atts['picture'] );
			if ( ! $picture_id ) {
				return 'shortcode_preview: Missing picture id!';
			}

			$picture = get_post( $picture_id );
			if ( ! $picture ) {
				return 'shortcode_preview: picture #' . $picture_id . ' not found!';
			}

			$options = get_option( 'VWpictureGalleryOptions' );

			// res
			$vWidth  = $options['thumbWidth'];
			$vHeight = $options['thumbHeight'];

			// snap
			$imagePath = get_post_meta( $picture_id, 'picture-snapshot', true );
			if ( $imagePath ) {
				if ( file_exists( $imagePath ) ) {
					$imageURL = self::path2url( $imagePath );
				} else {
					self::updatePostThumbnail( $update_id );
				}
			}

			if ( ! $imagePath ) {
				$imageURL = self::path2url( plugin_dir_path( __FILE__ ) . 'no_picture.png' );
			}
				$picture_url = get_permalink( $picture_id );

			$htmlCode = "<a href='$picture_url'><IMG class='ui fluid image' SRC='$imageURL' width='$vWidth' height='$vHeight'></a>";

			return $htmlCode;
		}




    static function videowhisper_picture_upload_guest( $atts ) {
		
        //visitor picture upload form shortcode (secure & simple)
        $options = self::getOptions();

        $atts = shortcode_atts(
            array(
                'category'    => '',
                'gallery'     => '',
                'owner'       => '',
                'tag'         => '',
                'description' => '',
                'terms'   => get_permalink( $options['termsPage'] ?? 0 ),
                'email' => '',
            ),
            $atts,
            'videowhisper_picture_upload_guest'
        );

        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $username = 'Guest';
        $useremail = '';

        if ($userID)
        {
        $userName     = $options['userName'];
        if ( ! $userName ) {
            $userName = 'user_nicename';
        }
        $username = $current_user->$userName;
        $useremail = $current_user->user_email;
        }

        self::enqueueUI();
        $htmlCode  = '';
		$error     = '';
        $status     = ''; 


//process upload form
if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {

    $email = sanitize_email( $_POST['email'] ?? '' );

    $terms = sanitize_text_field( $_POST['terms'] ?? '' );
    if ( ! $terms ) {
        $error .= '<li>' . __( 'Accepting Terms of Use is required.', 'picture-gallery' ) . '</li>';
    }

	if ( ! wp_verify_nonce( $_GET['videowhisper'], 'vw_upload' ) ) {
        $error .= '<li>' . __( 'Nonce incorrect for picture upload.', 'picture-gallery' ) . '</li>';
    }

    if ( $options['uploadsIPlimit'] ) {
        $users = get_posts(
            array(
                'meta_key'     => 'ip_uploader',
                'meta_value'   => self::get_ip_address(),
                'meta_compare' => '=',
            )
        );
        if ( count( $users ) >= intval( $options['uploadsIPlimit'] ) ) {
            $error .= '<li>' . __( 'Uploads per IP limit reached.', 'picture-gallery' ) . ' #' . count( $users ) . '</li>';
        }
    }

    if ( !$email) $error .= '<li>Please specify an email address for this submission!</li>';
   
//recaptcha
if ( $options['recaptchaSite'] ) {

    if ( isset( $_POST['recaptcha_response'] ) ) {
        if ( $_POST['recaptcha_response'] ) {
            // Build POST request:
            $recaptcha_response = sanitize_text_field( $_POST['recaptcha_response'] );

            // Make and decode POST request:
            // $recaptcha = file_get_contents('https://www.google.com/recaptcha/api/siteverify' . '?secret=' . $options['recaptchaSecret'] . '&response=' . $recaptcha_response);
            // $recaptchaD = json_decode($recaptcha);
            $response   = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify' . '?secret=' . $options['recaptchaSecret'] . '&response=' . $recaptcha_response );
            $body       = wp_remote_retrieve_body( $response );
            $recaptchaD = json_decode( $body );

            // Take action based on the score returned:
            if ( $recaptchaD->score >= 0.3 ) {
                // Verified
                $htmlCode .= '<!-- VideoWhisper reCAPTCHA v3 score: ' . $recaptchaD->score . '-->';

            } else {
                // Not verified - show form error
                $error .= '<li>Google reCAPTCHA v3 Failed. Score: ' . $recaptchaD->score . ' . Try again or using a different browser!</li>';
            }
        } else {
            $error .= '<li>Google reCAPTCHA v3 empty. Make sure you have JavaScript enabled or try a different browser!</li>';
        }
    } else {
        $error .= '<li>Google reCAPTCHA v3 missing. Make sure you have JavaScript enabled or try a different browser!</li>';
    }
}

if ( !isset($_FILES[ 'fileselect' ]) || !$_FILES[ 'fileselect']['name']) $error .= '<li>Please select a file to upload!</li>';

//process upload if no error
if (!$error)
{
    $dir = sanitize_text_field( $options['uploadsPath'] );
    if ( ! file_exists( $dir ) ) {
        mkdir( $dir );
    }
    $dir .= '/uploads';
    if ( ! file_exists( $dir ) ) {
        mkdir( $dir );
    }
    $dir .= '/_guest';
    if ( ! file_exists( $dir ) ) {
        mkdir( $dir );
    }

    $filename = sanitize_file_name( $_FILES[ 'fileselect' ]['name'] );
    $ext     = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

    if ( ! in_array( $ext , self::extensions_picture() ) )  $error .= '<li>' . 'Unsupported extension: ' . esc_html( $ext . ' / ' . implode( ',', self::extensions_picture()) ) . '</li>';

    if (!$error)
    {
    $newpath = $dir . self::generateName( $filename );

    $errorUp = self::handle_upload( $_FILES[ 'fileselect' ], $newpath ); // handle trough wp_handle_upload()
	if ( $errorUp ) $error .= '<li>' . 'Error uploading ' . esc_html( $filename . ':' . $errorUp ) . '</li>';
													

    if (!$error)
    {
    //fields
    $title = sanitize_text_field( $_POST['title'] );
    if (!$title) $title = sanitize_title( $filename );
    $owner = intval( sanitize_text_field( $_POST['owner'] ) );
    $category =  sanitize_text_field( $_POST['category'] );
    $tag =  sanitize_text_field( $_POST['tag'] );

    $gallery = sanitize_text_field( $_POST['gallery'] );
 		// if csv sanitize as array
        if ( strpos( $gallery, ',' ) !== false ) {
            $galleries = explode( ',', $gallery );
            foreach ( $galleries as $key => $value ) {
                $galleries[ $key ] = sanitize_file_name( trim( $value ) );
            }
            $gallery = $galleries;
        }
        if ( ! $gallery ) $gallery = 'Guest';

        $description =  sanitize_textarea_field( $_POST['description'] );

    $postID = 0;
    self::importFile( $newpath, $title, $owner, $gallery, $category, $tag, $description, $postID, true );

    if ($postID)
    {
        update_post_meta( $postID, 'ip_uploader', self::get_ip_address() );
        update_post_meta( $postID, 'email_uploader', $email );

        $post = array( 'ID' => $postID, 'post_status' => 'pending' );
        wp_update_post($post);

       // $link = get_edit_post_link( $postID );
        $link = get_admin_url() . 'post.php?post=' . $postID . '&action=edit&classic-editor';

        if ($options['moderatorEmail']) wp_mail( $options['moderatorEmail'], $options['guestSubject'] , $options['guestText'] . ' ' . $link );
	
        //show success message
        $htmlCode .= '<div class="ui segment">';
        $htmlCode .= $options['guestMessage'];
        $htmlCode .= '</div>';
    
    }
    else
    {
        $error .= '<li>' . 'Error importing (no post ID returned): ' . esc_html( $filename . " / $newpath, $title, $owner, $gallery, $category, $tag, $description ") . '</li>';
    }
    
    }
    }
}

//end upload process
}

//upload form

if ( $_SERVER['REQUEST_METHOD'] != 'POST' || $error != '' ) {

    $this_page = get_permalink();
    $recaptchaInput = '';
    $recaptchaCode = '';

    if ( $options['recaptchaSite'] ) {
        wp_enqueue_script( 'google-recaptcha-v3', 'https://www.google.com/recaptcha/api.js?render=' . $options['recaptchaSite'], array() );

        $recaptchaInput = '<input type="hidden" name="recaptcha_response" id="recaptchaResponse">';

        $recaptchaCode = '<script>
function onSubmitClick(e) {
grecaptcha.ready(function() {
  grecaptcha.execute("' . $options['recaptchaSite'] . '", {action: "register"}).then(function(token) {
  var recaptchaResponse = document.getElementById("recaptchaResponse");
  recaptchaResponse.value = token;
  console.log("VideoWhisper Upload: Google reCaptcha v3 updated", token);
  var videowhisperUploadForm = document.getElementById("videowhisperUploadForm");
  videowhisperUploadForm.submit();
  });
});
}
</script>
<noscript>JavaScript is required to use <a href="https://videowhisper.com/">VideoWhisper Picture Uploader</a>. Contact <a href="https://consult.videowhisper.com/">VideoWhisper</a> for clarifications.</noscript>
';
    } else {
        // recaptcha disabled
        $recaptchaCode.= '<script>
function onSubmitClick(e) {
console.log("VideoWhisper Upload: Google reCaptcha v3 disabled");
  var videowhisperUploadForm = document.getElementById("videowhisperUploadForm");
  videowhisperUploadForm.submit();

}
</script>
<noscript>JavaScript is required to use <a href="https://videowhisper.com/">VideoWhisper Picture Uploader</a>. Contact <a href="https://consult.videowhisper.com/">VideoWhisper</a> for clarifications.</noscript>
';
    }

        //prefill or input fields
        if ( $atts['category'] ) {
            $categories = '<input type="hidden" name="category" id="category" value="' . $atts['category'] . '"/>';
        } else {
            $categories = '<div class="field><label for="category">' . __( 'Category', 'picture-gallery' ) . ' </label> ' . wp_dropdown_categories( 'show_count=0&echo=0&name=category&hide_empty=0&class=ui+dropdown&selected=' . ( isset($_POST['category']) ? intval($_POST['category']) : 0 ) ) . '</div>';
        }

        $title = '<br><label for="title">' . __( 'Title', 'picture-gallery' ) . '</label> <br> <input size="48" maxlength="64" type="text" name="title" id="title" value="' . (  isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '' ) . '" placeholder="' . __( 'Title', 'picture-gallery' ) . '" class="text-input"/>';


        if ( $atts['gallery'] ) {
            $galleries = '<input type="hidden" name="gallery" id="gallery" value="' . $atts['gallery'] . '"/>';
        } elseif ( current_user_can( 'edit_users' ) ) {
            $galleries = '<br><label for="gallery">' . __( 'Gallery(s)', 'picture-gallery' ) . '</label> <br> <input size="48" maxlength="64" type="text" name="gallery" id="gallery" value="' . $username . '" class="text-input" placeholder="' . __( 'Gallery(s)', 'picture-gallery' ) . ' ('. __( 'comma separated', 'picture-gallery' ) . ')"/>';
        } else {
            $galleries = '<input type="hidden" name="gallery" id="gallery" value="' . $username . '"/>';
        }

        if ( $atts['owner'] ) {
            $owners = '<input type="hidden" name="owner" id="owner" value="' . $atts['owner'] . '"/>';
        } else {
            $owners = '<input type="hidden" name="owner" id="owner" value="' . $userID . '"/>';
        }

        if ( $atts['email'] ) {
            $emails = '<input type="hidden" name="email" id="owner" value="' . $atts['email'] . '"/>';
        } else {
            $emails = '<label for="email">' . __( 'Email', 'picture-gallery' ) . '</label><input input size="48" maxlength="64" type="text" name="email" id="email" value="' .(  isset($_POST['email']) ? sanitize_email( $_POST['email'] ) : $useremail ). '" placeholder="' . __( 'Email', 'picture-gallery' ) . '"/> ';        
        }

        if ( $atts['tag'] != '_none' ) {
            if ( $atts['tag'] ) {
                $tags = '<input type="hidden" name="tag" id="tag" value="' . $atts['tag'] . '"/>';
            } else {
                $tags = '<br><label for="tag">' . __( 'Tag(s)', 'picture-gallery' ) . '</label> <br> <input size="48" maxlength="64" type="text" name="tag" id="tag" value="' . (  isset( $_POST['tag'] ) ? sanitize_text_field( $_POST['tag'] ) : '' ) . '" class="text-input" placeholder="' . __( 'Tag(s)', 'picture-gallery' ) .' (' . __( 'comma separated', 'picture-gallery' ) . ')"/>';
            }
        }

        if ( $atts['description'] != '_none' ) {
            if ( $atts['description'] ) {
                $descriptions = '<input type="hidden" name="description" id="description" value="' . $atts['description'] . '"/>';
            } else {
                $descriptions = '<div class="field><label for="description">' . __( 'Description', 'picture-gallery' ) . '</label><textarea name="description" id="description" cols="72" rows="3" placeholder="' . __( 'Description', 'picture-gallery' ) . '">' . (  isset($_POST['description']) ? sanitize_textarea_field( $_POST['description'] ) : '' ) . '</textarea></div>';
            }
        }

    $iPod 	 = stripos( $_SERVER['HTTP_USER_AGENT'], 'iPod' );
    $iPhone  = stripos( $_SERVER['HTTP_USER_AGENT'], 'iPhone' );
    $iPad    = stripos( $_SERVER['HTTP_USER_AGENT'], 'iPad' );
    $Android = stripos( $_SERVER['HTTP_USER_AGENT'], 'Android' );

    if ( $iPhone || $iPad || $iPod || $Android ) {
        $mobile = true;
    } else {
        $mobile = false;
    }

    if ( $mobile ) {
        // https://mobilehtml5.org/ts/?id=23
        // $mobiles = 'capture="camera"';
        $accepts   = 'accept="image/*;capture=camera"';
        $filedrags = '';
    } else {
        $mobiles   = '';
        $accepts   = 'accept="image/jpeg,image/png,image/*;capture=camera"';
    }

        $actionURL =  wp_nonce_url( $this_page, 'vw_upload', 'videowhisper' ) ;

        $htmlCode .= '<form class="ui ' . $options['interfaceClass'] . ' form ' . $status . '" method="post" enctype="multipart/form-data" action="' .  $actionURL  . '" id="videowhisperUploadForm" name="videowhisperUploadForm">';

        if ( $error ) {
            $htmlCode .= '<div class="ui message">
    <div class="header">' . __( 'Could not submit upload', 'picture-gallery' ) . ':</div>
    <ul class="list">
    ' . $error . '
    </ul>
    </div>';
        }



    //    $htmlCode .= '<fieldset>';

$htmlCode .= <<<HTMLCODE
        $title
        $categories
        $galleries
        $tags
        $descriptions
        $owners
        $emails
        $recaptchaInput
        </fieldset>
        HTMLCODE;

   
        $htmlCode .= '<div class="field"> <label for="fileselect">' . __( 'Picture to upload', 'picture-gallery' ) . '</label><input class="ui button" type="file" id="fileselect" name="fileselect" $mobiles $accepts /></div>';

         $htmlCode .= '<div class="field">
         <div class="ui toggle checkbox">
           <input type="checkbox" name="terms" ' . ( isset( $terms ) && $terms ? 'checked' : '' ) . ' tabindex="0" class="hidden">
           <label>' . __( 'I accept the Terms of Use', 'picture-gallery' ) . ' </label>
         </div>
         <a class="ui tiny button" target="terms" href="' . $atts['terms'] . '"> <i class="clipboard list icon"></i> ' . __( 'Review', 'picture-gallery' ) . '</a>
       </div>';

         $htmlCode .= '<div class="field">
         <input type="button" id="submitButton" name="submitButton" onclick="onSubmitClick()" class="ui submit button" value="' . __( 'Upload', 'picture-gallery' ) . '" />
         </div>';
         
         //end form
         $htmlCode .= '</div>';

       // $htmlCode .= '</fieldset>';

       //terms checkbox
       $htmlCode .= '</form> <script>
			jQuery(document).ready(function(){
jQuery(".ui.checkbox").checkbox();
});
			</script>';

        $htmlCode .= $recaptchaCode;
     }

        //$htmlCode .= '<style type="text/css">' . html_entity_decode( stripslashes( $options['customCSS'] ) ) . '</style>';

        return $htmlCode;

    }


        //! frontend shortcode toolbox

    	/**
		 * Retrieves the best guess of the client's actual IP address.
		 * Takes into account numerous HTTP proxy headers due to variations
		 * in how different ISPs handle IP addresses in headers between hops.
		 */
		static function get_ip_address() {
			$ip_keys = array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' );
			foreach ( $ip_keys as $key ) {
				if ( array_key_exists( $key, $_SERVER ) === true ) {
					foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) {
						// trim for safety measures
						$ip = trim( $ip );
						// attempt to validate IP
						if ( self::validate_ip( $ip ) ) {
							return $ip;
						}
					}
				}
			}

			return isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : false;
		}

        /**
		 * Ensures an ip address is both a valid IP and does not fall within
		 * a private network range.
		 */
		static function validate_ip( $ip ) {
			if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) === false ) {
				return false;
			}
			return true;
		}

        static function handle_upload( $file, $destination ) {
			// ex $_FILE['myfile']

			if ( ! function_exists( 'wp_handle_upload' ) ) {
			    require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}

			$movefile = wp_handle_upload( $file, array( 'test_form' => false ) );

			if ( $movefile && ! isset( $movefile['error'] ) ) {
				if ( ! $destination ) {
					return 0;
				}
				rename( $movefile['file'], $destination ); // $movefile[file, url, type]
				return 0;
			} else {
				/*
				 * Error generated by _wp_handle_upload()
				 * @see _wp_handle_upload() in wp-admin/includes/file.php
				 */
				return $movefile['error']; // return error
			}

		}
    
}
