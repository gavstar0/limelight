<?php

wp_localize_script( 'limelight-main-js', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

$current_page  = sanitize_text_field( $_GET['page'] );
$updated       = ( isset( $_GET['settings-updated'] ) && sanitize_text_field( $_GET['settings-updated'] ) == 'true' );
$pages         = array(
	'LimeLight Storefront' => 'admin_index',
	'Error Responses'      => 'admin_responses',
	'Store Settings'       => 'admin_store',
	'LimeLight Products'   => '',
);

foreach ( $pages as $page => $template ) {
	if ( $current_page == $page ) {
		if ( $updated ) {
			if ( $page == 'LimeLight Storefront' ) {
				if ( empty( $this->check_credentials() ) ) {
					$this->get_campaign_data();
					$redirect = str_replace( '&settings-updated=true', '', admin_url() . "admin.php?page=$current_page" );
					echo "<script>location.href = '{$redirect}';</script>";
				}
			}
		} else {
			if ( $page == 'LimeLight Products' ) {
				$redirect = admin_url() . "edit.php?post_type=products";
				echo "<script>location.href = '{$redirect}';</script>";				
			}
		}
		$this->display_tpl( $this->admin_tpl[$template], $this->build_admin_tokens( $current_page ) );
	}
}
