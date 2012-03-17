<?php
/*
Plugin Name: Just the Page
Plugin URI:
Description: Displays page content with no additional formatting (e.g., no header, footer, etc.)
Version: 1.0
Author: Benjamin J. Balter
Author URI: http://ben.balter.com
License: GPL2
*/

class Just_The_Page {

	public $meta_key = 'just_the_page'; //key to store plugin's toggle for a given page (bool)

	function __construct() { 
		add_action( 'template_redirect', array( &$this, 'template_filter' ) );
		add_action( 'add_meta_boxes', array( &$this, 'register_metabox' ) );
		add_action( 'save_post', array( &$this, 'metabox_save' ) );
	}

	/**
	 * Hook to check for meta and call template filter
	 */
	function template_filter() {

		//if not a page or single post, kick
		if ( !is_single() && !is_page() )
			return;
	
		//get current post ID
		global $wp_query;
		$post_id = $wp_query->post->ID;
	
		//Look for a "no_formatting" page meta
		$toggle = get_post_meta( $post_id, $this->meta_key, true );

		//if the meta is set, call our template filter
		if ( !$toggle )
			return;
			
		remove_filter( 'the_content', 'wpautop' );
		add_filter('template_include', array( &$this, 'template_callback' ), 100);
	
	}
	
	/**
	 * Callback to replace the current template with our blank template
	 * @return string the path to the plugin's template.php
	 */
	function template_callback( $template ) {
		return dirname(__FILE__) . '/template.php';
	}
	
	/**
	 * Registers plugin's toggle metabox with metabox API
	 */
	function register_metabox() {
		add_meta_box( 'just-the-page', 'Custom Template', array( &$this, 'metabox_callback' ), 'page', 'side', 'low' );
	}
	
	/**
	 * Callback to display toggle metabox
	 */
	function metabox_callback( $post ) { 
		wp_nonce_field( 'just-the-page', '_jtp_nonce' )
	?>
		<p>
			<input type="checkbox" name="just_the_page" id="just_the_page" <?php checked( get_post_meta( $post->ID, $this->meta_key, true ) ); ?>/><label for="just_the_page"> <?php _e( 'Display only page content', 'just-the-page' ); ?></label><br />
			<span class="description"><?php _e( '<em>e.g.,</em> hide page header, footer, etc.', 'just-the-page' ); ?></span>
		</p>
	<?php
	}
	
	/**
	 * Saves metabox toggle when page is saved
	 */
	function metabox_save( $post_id ) {
	
		if ( $_POST['post_type'] != 'page' )
			return;
	
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      		return;
	
		if ( !wp_verify_nonce( $_POST['_jtp_nonce'], 'just-the-page' ) )
			return;
		
		if ( !current_user_can( 'edit_page', $post_id ) )
			return;
		
		update_post_meta( $post_id, $this->meta_key, (bool) $_POST['just_the_page'] );
	
	}
	
}

$jtp = new Just_The_Page();