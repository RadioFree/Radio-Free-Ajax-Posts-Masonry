<?php
/*
Plugin Name: Ajax Posts Masonry
Plugin URI: http://www.peoplenotprofits.co
Description: Plugin which makes masonry grid of posts.
Author: Sufyan Shaikh
Version: 1.0
Author URI: http://www.peoplenotprofits.co
Text Domain:  ajax_post_masonry
*/

add_shortcode( 'ajax-posts-masonry', 'render_ajax_posts_masonry' );

function render_ajax_posts_masonry( $atts ) {
	$num_of_row = $atts['cols'] ? $atts['cols'] : 4;
	$num_of_col = $atts['cols'] ? $atts['cols'] : 3;
	
	if( (int)$num_of_row && (int)$num_of_col )
		$num_of_post = (int)$num_of_col * (int)$num_of_row ;
	else 
		$num_of_post = 12;
	
	$i = 1;
	$row = 1;
	
	$args = array(
	  'numberposts' => $num_of_post
	);
	 
	$posts = get_posts( $args );
	
	ob_start();
	
	echo '<div class="ajax-post-grid" data-posts="' .$num_of_post. '" data-col="' .$num_of_col. '">';
	
	foreach( $posts as $post ) {
		echo '<div class="ap-grid-item ap-col-' .$num_of_col. '" data-item=' .$post->ID. ' data-row="' .$row. '">';
			echo '<div class="ap-item-inner" data-url="' .get_permalink($post). '">';
				if( has_post_thumbnail($post->ID) ) {
					echo get_the_post_thumbnail($post->ID, array(400,400) );
				} else {
					echo '<img src="' . plugin_dir_url(__FILE__). '/assets/no-img.png" class="ap-thumb"/>';
				}
				
				echo '<div class="ap-title">' .$post->post_title. '</div>';
				echo '<div class="ap-exceprt">' . excerpt_with_length( $post->ID, 100 ) .'</div>';
			echo '</div>';
		echo '</div>';
		if( $i++ % $num_of_col == 0 ) {
			echo '<div class="ap-grid-item ap_item_row" id="ap_row_' .$row++. '" style="display: none"></div>';
		}
	}
	
		echo '<div class="ap-grid-widgets">';
			echo '<div class="wdg-top">';
				if( is_active_sidebar( 'ajax_masonry_top' ) ) dynamic_sidebar( 'ajax_masonry_top' );
			echo '</div>';
			echo '<div class="wdg-bottom">';
				if( is_active_sidebar( 'ajax_masonry_bottom' ) ) dynamic_sidebar( 'ajax_masonry_bottom' );
			echo '</div>';
		echo '</div>';
	
	echo '</div>';
	
	$output = ob_get_clean();
	return $output;
}

function excerpt_with_length( $post_id, $num_of_char ) {
	$excerpt = get_the_excerpt($post_id);
	$num_of_char++;
	$html = '';
	
	if ( mb_strlen( $excerpt ) > $num_of_char ) {
		$subex = mb_substr( $excerpt, 0, $num_of_char - 5 );
		$exwords = explode( ' ', $subex );
		$excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
		if ( $excut < 0 ) {
			$html .= mb_substr( $subex, 0, $excut );
		} else {
			$html .= $subex;
		}
		$html .= '[...]';
	} else {
		$html .= $excerpt;
	}
	
	return $html;
}

function enqueue_ap_grid_script(){
    wp_enqueue_script( 'ap_grid_js', plugin_dir_url(__FILE__) . '/assets/js/ap_grid.js', array( 'jquery' ) );
	wp_localize_script( 'ap_grid_js', 'ap_grid_js', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	wp_enqueue_style( 'ap_grid_css', plugin_dir_url(__FILE__) . '/assets/css/ap_grid.css' );
	
	wp_enqueue_style( 'wp-mediaelement' );
    wp_enqueue_script( 'app', get_stylesheet_directory_uri() . '/js/app.js', array( 'jquery', 'wp-mediaelement' ));
}

add_action( 'wp_enqueue_scripts', 'enqueue_ap_grid_script' );

function ap_grid_post() {
	if( $_POST && $_POST['item'] ) {
		$item_id = (int)$_POST['item'];
		$post = get_post($item_id);
		echo file_get_contents( get_permalink($post) );
		wp_die();
	}		
}

add_action( 'wp_ajax_ap_grid_get_post', 'ap_grid_post' );
add_action( 'wp_ajax_nopriv_ap_grid_get_post', 'ap_grid_post' );



function ap_grid_single_post_widgets_init() {
    register_sidebar( array(
        'name' => __( 'Ajax Masonry Post Top', 'ajax_post_masonry' ),
        'id' => 'ajax_masonry_top',
        'description' => __( 'Widgets at top in Single Post Template in Ajax Masonry.', 'ajax_post_masonry' ),
        'before_widget' => '<div id="%1$s" class="ap-grid-widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h2 class="widgettitle">',
		'after_title'   => '</h2>',
    ) );
	
	register_sidebar( array(
        'name' => __( 'Ajax Masonry Post Bottom', 'ajax_post_masonry' ),
        'id' => 'ajax_masonry_bottom',
        'description' => __( 'Widgets at bottom in Single Post Template in Ajax Masonry.', 'ajax_post_masonry' ),
        'before_widget' => '<div id="%1$s" class="ap-grid-widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h2 class="widgettitle">',
		'after_title'   => '</h2>',
    ) );
}

add_action( 'widgets_init', 'ap_grid_single_post_widgets_init' );


function post_audio_shortocde_filter( $contents ) {
	$contents = str_replace( 'class="wp-audio-shortcode"', 'class="wp-audio-shortcode wp-block-audio"', $contents );
	return $contents;
}

add_filter( 'the_content', 'post_audio_shortocde_filter', 1000000 );