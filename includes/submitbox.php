<?php
global $post;
$post_type = ( post_type_exists( $this->get_option( 'post_type' ) ) ? $this->get_option( 'post_type' ) : 'post' );
if ( $post->post_type == $post_type || $post->post_type == 'bulkpost' ) :
	?>
    <div class="misc-pub-section misc-pub-section-last">
		<input type="checkbox" id="mainwp_respin_post" name="mainwp_respin_post" value="<?php echo wp_create_nonce( $this->plugin_handle . '-respin' ) ?>" /> 
        <label for="mainwp_respin_post">Re-spin the article</label>
    </div>
	<?php
endif;
?>
