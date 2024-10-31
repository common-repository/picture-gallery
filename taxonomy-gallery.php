<?php get_header(); ?>

<div class="container">
<div class="container_inner clearfix">
<?php
$tax = $wp_query->get_queried_object();
echo '<h3>' . esc_html( $tax->name ) . '</h3>';
echo do_shortcode( '[videowhisper_pictures gallery="' . esc_attr( $tax->name ) . '"]' );
echo '<div class="clear"></div>';
?>
</div></div>

<?php get_footer(); ?>
