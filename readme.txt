=== Picture Gallery - Frontend Image Uploads, AJAX Photo List ===
Contributors: videowhisper, VideoWhisper.com
Author: VideoWhisper.com
Author URI: https://videowhisper.com
Plugin Name: Picture Gallery
Plugin URI: https://videochat-scripts.com/picture-gallery-plugin/
Donate link: https://videowhisper.com/?p=Invest
Tags: picture, gallery, image, photo, upload
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.1
Tested up to: 6.6
Stable tag: trunk

Streamline photo sharing with AJAX-powered galleries, frontend uploads, and integrated monetization.


== Description ==

Elevate your WordPress site with the Picture Gallery plugin, enabling users to easily upload and manage images through a frontend interface. This powerful plugin supports guest uploads with CAPTCHA, generates thumbnails, and integrates seamlessly into your WordPress Media Library. Whether you're looking to display image portfolios or sell digital photos, this tool is equipped with AJAX updates for live listing, drag-and-drop uploads, and extensive customization options to meet all your photo gallery needs.

= Benefits = 
* Frontend & Backend Uploads: Allows both visitors and administrators to upload images conveniently.
* Advanced Security Features: Includes Google reCAPTCHA v3 to prevent spam and unauthorized uploads.
* Dynamic AJAX Photo Lists: Updates the gallery live without page reloads, enhancing user experience.
* Comprehensive Integration: Adds pictures and thumbnails to the WordPress Media Library for easy management.
* Customizable Access Controls: Set permissions for uploads and gallery views, ensuring content security.
* Monetization Opportunities: Integrates with plugins like "MicroPayments/FansPaysite - Creator Subscriptions, Digital Content Monetization" to enable photo sales directly from your gallery.
* Multi-Device Compatibility: Supports uploads from mobile devices, including direct camera uploads on iOS and Android.
* Enhanced Engagement: Features like the "Rate Star Review" allow visitors to rate and review images, fostering community interaction.
* Bulk Upload Capabilities: Simplifies the process of adding large volumes of images, saving time and effort.

= Key Features =
* adds picture post type to WordPress site with gallery taxonomy
* allows upload and import of pictures from frontend and backend
* guest picture upload with Google reCAPTCHA v3 integration, moderator notification
* generates thumbnail, generates feature image
* AJAX display and update of picture list
* shortcodes for listing pictures, upload form, import form
* mass picture upload
* mass picture import (from server)
* setup user types that can share pictures
* pending picture / approval for user types that can't publish directly
* integrates [Rate Star Review - AJAX Reviews for Content, with Star Ratings](https://wordpress.org/plugins/rate-star-review/ "Rate Star Review - AJAX Reviews for Content, with Star Ratings")
* filter pictures by category, tag, name
* sort pictures by date, views, rating
* include pictures and thumbs in Media Library (setting)

= Guest Picture Upload =
* special shortcode for guest (visitor) picture upload
* Google reCAPTCHA v3 integration
* limit uploads per IP 
* moderator notification by email (custom)
* custom message for upload success
* persistent form fields in case of error

= Access Control: Membership, Sales =
* define global picture access list (roles, user emails & ids)
* role galleries: assign pictures as accessible by certain roles
* exception galleries: free, registered, unpublished
* show preview and custom message when inaccessible
* integrates [MicroPayments/FansPaysite - Creator Subscriptions, Digital Content Monetization](https://wordpress.org/plugins/paid-membership/ "MicroPayments/FansPaysite - Creator Subscriptions, Digital Content Monetization") plugin to allow selling items

= HTML5 Picture Uploader =
* Drag & Drop
* AJAX (no Submit, page reload required to upload more pictures)
* multi picture support
* status / progress bar for each upload
* unpredictable secure upload file names
* fallback to standard upload for older browsers
* mobile camera upload (iOS6+, Android 3+)
* backend multi upload menu

= Recommended for use with these solutions =
* [FansPaysite - Creator Subscriptions, MicroPayments, Digital Content](https:/fanspaysite.com/ "FansPaysite - Frontend Content Management and Monetization") - manage content posts including pictures from frontend
* [Paid VideoChat](https://paidvideochat.com/ "PaidVideoChat Turnkey Webcams Site Plugin")  - integrate pictures in performer profiles
* [Video Share VOD](https://wordpress.org/plugins/video-share-vod/  "Video Share / Video On Demand Turnkey Site Plugin") - add pictures in addition to videos
* [Broadcast Live Video](https://broadcastlivevideo.com/ "Broadcast Live Video Camera Site Plugin") - add pictures in addition to live channels

If you find this plugin idea useful or interesting, [Leave a Review](https://wordpress.org/support/plugin/picture-gallery/reviews/#new-post) to help us drive more resources into further development and improvements.

If you need custom development or support, [Consult VideoWhisper](https://consult.videowhisper.com/ "Consult VideoWhisper for WP Plugin Development"): professional installation, configuration, troubleshooting, compatible hosting, custom development for new options and features. 


== Screenshots ==
1. AJAX picture listings (updates live as pictures are added)
2. Rate Star Review - picture reviews integration

== Support ==
This is a free open source plugin provided as is. If you need further assistance, troubleshooting, custom development to integrate with your site, [Consult VideoWhisper](https://consult.videowhisper.com/).

== Demos ==
Pictures page on various themes:
* [Fans Paysite Demo](https://demo.fanspaysite.com/pictures/)
* [Video Share VOD Demo](https://demo.videosharevod.com/pictures/)
* [Paid Videochat Demo](https://demo.paidvideochat.com/pictures/)


== Frequently Asked Questions ==
* Q: How much does this plugin cost?
A: This plugin is FREE.

* Q: Does upload work on mobiles?
A: Uploading pictures works on latest mobiles. In example on iOS6+ user will be prompted to take picture or select on from camera roll when pressing Choose Files button.

* Q: How can I add pictures to my site?
A: Go to VideoWhisper / Picture Gallery / Settings from top admin menu and click Save. This will add the main feature pages to your site and menu. You can disable Setup Pages and manually add the shortcodes as described in Documentation page. 

== Changelog ==

= 1.5 =
* Guest Picture Upload:
* Google reCAPTCHA v3 integration
* Limit uploads per IP
* Moderator notification by email (custom)
* Custom message for upload success
* Persistent form fields in case of error

= 1.4 =
* Media Library integration
* Menu in listings with categories, order
* PHP 8 support 

= 1.3 = 
* Integrates Rate Star Review plugin for star reviews
* Review form and list on picture page
* Rating stars in listings
* Integrate Semantic UI interface
* Filter by picture tag, name
* Sort by rating, number of ratings, points

= 1.2 =
* Integrates Paid Membership and Content plugin to allow selling items

= 1.1 =
* First public release.