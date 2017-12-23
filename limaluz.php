<?php
// Kill heartbeat for dev
// add_action( 'init', 'stop_heartbeat', 1 );
// function stop_heartbeat() {
// wp_deregister_script( 'heartbeat' );
// }

/************************************************************************
 * LimeLight Storefront - Wordpress Plugin
 * Copyright (C) 2017 Lime Light CRM, Inc.

 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/************************************************************************
 *
 * @link              https://limelightcrm.com
 * @since             1.1.4.5
 * @package           Limelight
 *
 * @wordpress-plugin
 * Plugin Name:       LimaLuz
 * Plugin URI:         https://limelightcrm.com
 * Description:       A fork of the original LimeLight CRM plugin.
 * Version:           1.1.4.5
 * Author:            Lime Light CRM, Inc.
 * Author URI:        https://limelightcrm.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

function limaluz_remove_admin_bar() {
	if ( ! current_user_can( 'administrator' ) && ! is_admin() ) {
		show_admin_bar( false );
	}
}
add_action('init', 'limaluz_remove_admin_bar');

function limaluz_products() {
	$labels = array(
		'name'                => _x( 'Products', 'Post Type General Name', '' ),
		'singular_name'       => _x( 'Product', 'Post Type Singular Name', '' ),
		'menu_name'           => __( 'Products', '' ),
		'parent_item_colon'   => __( 'Parent Product', '' ),
		'all_items'           => __( 'All Products', '' ),
		'view_item'           => __( 'View Product', '' ),
		'add_new_item'        => __( 'Add New Product', '' ),
		'add_new'             => __( 'Add New', '' ),
		'edit_item'           => __( 'Edit Product', '' ),
		'update_item'         => __( 'Update Product', '' ),
		'search_items'        => __( 'Search Product', '' ),
		'not_found'           => __( 'Not Found', '' ),
		'not_found_in_trash'  => __( 'Not found in Trash', '' ),
	);
	$args = array(
		'label'               => __( 'products', '' ),
		'description'         => __( 'Product news and reviews', '' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
		'taxonomies'          => array( 'limaluz_products' ),
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
		'capability_type'     => 'page',
		'menu_icon'           => 'dashicons-cart',
	);
	register_post_type( 'products', $args );
	flush_rewrite_rules();
}
add_action( 'init', 'limaluz_products', 0 );
add_filter( 'widget_text', 'do_shortcode' );

function limaluz_add_custom_types( $query ) {
	if( is_archive() && ( is_category() || is_tag() ) && empty( $query->query_vars['suppress_filters'] ) ) {
		$query->set( 'post_type', array( 'post', 'products' ) );
		$query->set( 'tax_query', array(
			array(
				'taxonomy' => 'category',
				'field' => 'slug',
				'terms' => array( 'shop' ),
			),
		) );
		return $query;
	}
}
add_filter( 'pre_get_posts', 'limaluz_add_custom_types' );

function limaluz_add_taxonomies_to_products() {
	register_taxonomy_for_object_type( 'post_tag', 'products' );
	register_taxonomy_for_object_type( 'category', 'products' );
}
add_action( 'init', 'limaluz_add_taxonomies_to_products' );

function limaluz_settings_init() {

	//auth
	register_setting( 'limaluz_auth', 'limaluz_options_auth' );

	add_settings_section(
		'limaluz_credential_entry',
		__( 'Connect Wordpress to LimeLight CRM', 'limaluz' ),
		'limaluz_auth_desc',
		'limaluz_auth'
	);

	add_settings_field(
		'username',
		__( 'Username', 'limaluz' ),
		'limaluz_field_username',
		'limaluz_auth',
		'limaluz_credential_entry',
		[
			'label_for' => 'username',
			'class' => 'limaluz_row',
			'limaluz_custom_data' => 'custom',
		]
	);

	add_settings_field(
		'password',
		__( 'Password', 'limaluz' ),
		'limaluz_field_password',
		'limaluz_auth',
		'limaluz_credential_entry',
		[
			'label_for' => 'password',
			'class' => 'limaluz_row',
			'limaluz_custom_data' => 'custom',
		]
	);

	add_settings_field(
		'appkey',
		__( 'Appkey', 'limaluz' ),
		'limaluz_field_appkey',
		'limaluz_auth',
		'limaluz_credential_entry',
		[
			'label_for' => 'appkey',
			'class' => 'limaluz_row',
			'limaluz_custom_data' => 'custom',
		]
	);

	//campaign
	register_setting( 'limaluz_campaigns', 'limaluz_options_campaign' );

	add_settings_section(
		'limaluz_campaign_selection',
		__( 'Select Your LimeLight Campaign', 'limaluz' ),
		'limaluz_campaigns_desc',
		'limaluz_campaigns'
	);

	add_settings_field(
		'campaign',
		__( 'Available Campaigns', 'limaluz' ),
		'limaluz_field_campaign',
		'limaluz_campaigns',
		'limaluz_campaign_selection',
		[
			'label_for' => 'campaign',
			'class' => 'limaluz_row',
			'limaluz_custom_data' => 'custom',
		]
	);

	//shop
	register_setting( 'limaluz_shopinfo', 'limaluz_options_shopinfo' );

	add_settings_section(
		'limaluz_shopinfo_entry',
		__( 'Details About Your Shop', 'limaluz' ),
		'limaluz_shopinfo_desc',
		'limaluz_shopinfo'
	);

	add_settings_field(
		'name',
		__( 'Name', 'limaluz' ),
		'limaluz_field_shop_name',
		'limaluz_shopinfo',
		'limaluz_shopinfo_entry',
		[
			'label_for' => 'name',
			'class' => 'limaluz_row',
			'limaluz_custom_data' => 'custom',
		]
	);
	
	//general options
	register_setting( 'limaluz_general_settings', 'limaluz_options_general_settings' );

	add_settings_section(
		'limaluz_general_entry',
		__( 'Your General LimeLight Settings', 'limaluz' ),
		'limaluz_general_desc',
		'limaluz_general_settings'
	);

	add_settings_field(
		'google',
		__( 'Google Tracking ID', 'limaluz' ),
		'limaluz_field_google',
		'limaluz_general_settings',
		'limaluz_general_entry',
		[
			'label_for' => 'google',
			'class' => 'limaluz_row',
			'limaluz_custom_data' => 'custom',
		]
	);
}
add_action( 'admin_init', 'limaluz_settings_init' );

function limaluz_general_desc( $args ) {
	?>
	<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'General LimeLight Plugin Settings and Options.', 'limaluz' ); ?></p>
	<?php
}

function limaluz_auth_desc( $args ) {
	?>
	<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Enter the API username, password and appkey for your LimeLight CRM.', 'limaluz' ); ?></p>
	<?php
}

function limaluz_campaigns_desc( $args ) {
	?>
	<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Choose the campaign you would like to use.', 'limaluz' ); ?></p>
	<?php
}

function limaluz_shopinfo_desc( $args ) {
	?>
	<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Enter information about your company. Only used withing Wordpress, does not update your CRM', 'limaluz' ); ?></p>
	<?php
}

function limaluz_field_campaign( $args ) {

	$options = get_option( 'limaluz_options_campaign' );
	parse_str( limaluz_campaign_find_active(), $x );

	$campaign_names = explode( ',', $x['campaign_name'] );
	$campaign_ids = explode( ',', $x['campaign_id'] ); ?>
		<select id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['limaluz_custom_data'] ); ?>" name="limaluz_options_campaign[campaign]">
			<?php foreach ( $campaign_ids as $key => $val ) { ?>
				<option value="<?php echo $campaign_ids[$key]; ?>" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'red', false ) ) : ( '' ); ?>>
					<?php echo $campaign_ids[$key] . '. - ' . $campaign_names[$key]; ?>
				</option>
			<?php } ?>
		</select>
	<p class="description">
		<?php esc_html_e( 'This is the campaign that all your shop products will be created from.', 'limaluz' ); ?>
	</p>
	<?php
}

function limaluz_field_google( $args ) {
	$options = get_option( 'limaluz_options_general_settings' );
	?>
	<input id="" name="limaluz_options_general_settings[google]" value="<?php echo $options['google']; ?>">
	<?php
}

function limaluz_field_shop_name( $args ) {
	$options = get_option( 'limaluz_options_shopinfo' );
	?>
	<input id="" name="limaluz_options_shopinfo[username]" value="<?php echo $options['username']; ?>">
	<?php
}

function limaluz_field_username( $args ) {
	$options = get_option( 'limaluz_options_auth' );
	?>
	<input id="" name="limaluz_options_auth[username]" value="<?php echo $options['username']; ?>">
	<?php
}

function limaluz_field_password( $args ) {
	$options = get_option( 'limaluz_options_auth' );
	?>
	<input id="" name="limaluz_options_auth[password]" value="<?php echo $options['password']; ?>">
	<?php
}

function limaluz_field_appkey( $args ) {
	$options = get_option( 'limaluz_options_auth' );
	?>
	<input id="" name="limaluz_options_auth[appkey]" value="<?php echo $options['appkey']; ?>">
	<?php
}

function limaluz_add_options_pages() {
	add_menu_page(
		'LimeLight Options',
		'LimeLight Options',
		'manage_options',
		'LimeLight Options',
		'limaluz_html_form_general',
		plugin_dir_url( __FILE__ ) . 'assets/limelight-small.png'
	);
	add_submenu_page(
		'LimeLight Options',
		'API Credentials',
		'API Credentials',
		'manage_options',
		'API Credentials',
		'limaluz_html_form_auth'
	);
	add_submenu_page(
		'LimeLight Options',
		'Campaign Settings',
		'Campaign Settings',
		'manage_options',
		'Campaign Settings',
		'limaluz_html_form_campaign'
	);	
	add_submenu_page(
		'LimeLight Options',
		'Shop Info',
		'Shop Info',
		'manage_options',
		'Shop Info',
		'limaluz_html_form_shop'
	);
	add_submenu_page(
		'LimeLight Options',
		'Error Responses',
		'Error Responses',
		'manage_options',
		'Error Responses',
		'limaluz_html_form_errors'
	);
	add_submenu_page(
		'LimeLight Options',
		'Demo Import',
		'Demo Import',
		'manage_options',
		'Demo Import',
		'limaluz_html_form_demo'
	);
}
add_action( 'admin_menu', 'limaluz_add_options_pages' );

function limaluz_verify_auth() {

	$options = get_option( 'limaluz_options_auth' );
	parse_str( limaluz_campaign_find_active(), $x );

	if ( $x['response'] == '100' ) {
		$opt = array(
			'verified' => 1,
		);
	} else {
		$opt = array(
			'verified' => 0,
		);
	}

	$options = array_merge( $opt, $options );
	update_option( 'limaluz_options_auth', $options );
}

function limaluz_html_form_general() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	
	if ( isset( $_GET['settings-updated'] ) ) {
		add_settings_error( 'limaluz_messages', 'limaluz_message', __( 'Options Updated.', 'limaluz' ), 'updated' );
	}

	settings_errors( 'limaluz_messages' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'limaluz_general_settings' );
			do_settings_sections( 'limaluz_general_settings' );
			submit_button( 'Update Settings' );
			?>
		</form>
	</div>
	<?php
}

function limaluz_html_form_auth() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_GET['settings-updated'] ) ) {
		add_settings_error( 'limaluz_messages', 'limaluz_message', __( 'Credentials Updated.', 'limaluz' ), 'updated' );
		limaluz_generate_pages();
		limaluz_verify_auth();
	}

	$options = get_option( 'limaluz_options_auth' );

	if ( ! $options['verified'] && ( $options['username'] || $options['password'] || $options['appkey'] ) ) {
		add_settings_error( 'limaluz_messages', 'limaluz_message', __( 'There was a problem connecting to LimeLight CRM. Check your credentials and try again.', 'limaluz' ), 'error' );		
	} elseif ( ! $options['verified'] ) {
		add_settings_error( 'limaluz_messages', 'limaluz_message', __( 'You haven\'t configured your credentials yet. Enter your <a href="admin.php?page=API+Credentials">API Credentials</a> to connect to your LimeLight CRM.', 'limaluz' ), 'error' );		
	} elseif ( $options['verified'] ) {
		add_settings_error( 'limaluz_messages', 'limaluz_message', __( 'You are successfully connected to LimeLight CRM with your API credentials. You can configure your <a href="admin.php?page=Campaign+Settings">Campaign Settings</a>.', 'limaluz' ), 'updated' );		
	}

	settings_errors( 'limaluz_messages' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'limaluz_auth' );
			do_settings_sections( 'limaluz_auth' );
			submit_button( 'Save Settings' );
			?>
		</form>
	</div>
	<?php
}

function limaluz_html_form_campaign() {

	$auth_options = get_option( 'limaluz_options_auth' );
	$campaign_options = get_option( 'limaluz_options_campaign' );

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! $auth_options['verified'] ) {
		add_settings_error( 'limaluz_messages', 'limaluz_message', __( 'You need to enter your <a href="admin.php?page=API+Credentials">API Credentials</a> before selecting a campaign.', 'limaluz' ), 'error' );
	} else {
		if ( isset( $_GET['settings-updated'] ) ) {
			add_settings_error( 'limaluz_messages', 'limaluz_message', __( 'Campaign Updated.', 'limaluz' ), 'updated' );
		}	
		if ( ! $campaign_options['campaign'] ) {
			add_settings_error( 'limaluz_messages', 'limaluz_message', __( 'You haven\'t selected a campaign yet. Choose one now!', 'limaluz' ), 'error' );
		} else {
			add_settings_error( 'limaluz_messages', 'limaluz_message', __( 'The Shop is successfully integrated with your LimeLight CRM Campaign #' . $campaign_options['campaign'] . '. View your <a href="edit.php?post_type=products">Products</a> and <a href="edit-tags.php?taxonomy=category&post_type=products">Categories</a>.', 'limaluz' ), 'updated' );
		}
	}

	settings_errors( 'limaluz_messages' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			if ( $auth_options['verified'] ) {
				settings_fields( 'limaluz_campaigns' );
				do_settings_sections( 'limaluz_campaigns' );
				submit_button( 'Update & Generate Products' );
			}
			?>
		</form>
	</div>
	<?php
	if ( isset( $_GET['settings-updated'] ) ) {
		limaluz_generate_products();
	}
}

function limaluz_html_form_errors() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_GET['settings-updated'] ) ) {
		add_settings_error( 'limaluz_messages', 'limaluz_message', __( 'Settings Saved', 'limaluz' ), 'updated' );
	}

	settings_errors( 'limaluz_messages' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'limaluz' );
			do_settings_sections( 'limaluz' );
			submit_button( 'Save Settings' );
			?>
		</form>
	</div>
	<?php
}

function limaluz_html_form_shop() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_GET['settings-updated'] ) ) {
		add_settings_error( 'limaluz_messages', 'limaluz_message', __( 'Settings Saved', 'limaluz' ), 'updated' );
	}

	settings_errors( 'limaluz_messages' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'limaluz_shopinfo' );
			do_settings_sections( 'limaluz_shopinfo' );
			submit_button( 'Update' );
			?>
		</form>
	</div>
	<?php
}

function limaluz_auth( $more_args, $type = 'membership' ) {

	$options = get_option( 'limaluz_options_auth' );

	$args = array (
		'username' => $options['username'],
		'password' => $options['password'],
	);

	$args = array_merge( $args, $more_args );

	$api_url = 'https://' . $options['appkey'] . '.limelightcrm.com/admin/' . $type . '.php?' . http_build_query( $args );
	$response = wp_remote_retrieve_body( wp_remote_get( $api_url ) );

	return $response;
}

function limaluz_campaign_find_active() {

	$args = array(
		'method' => 'campaign_find_active',
	);

	$response = limaluz_auth( $args );
	return $response;
}

function limaluz_campaign_view( $id ) {

	$args = array(
		'method' => 'campaign_view',
		'campaign_id' => $id,
	);

	$response = limaluz_auth( $args );
	return $response;
}

function limaluz_add_dashboard_widget() {

	wp_add_dashboard_widget( 'limaluz_dashboard_widget', 'LimeLight Dashboard', 'limaluz_dashboard_widget_function' );
	global $wp_meta_boxes;
	$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

	$limaluz_widget_backup = array( 'limaluz_dashboard_widget' => $normal_dashboard['limaluz_dashboard_widget'] );
	unset( $normal_dashboard['limaluz_dashboard_widget'] );

 	$sorted_dashboard = array_merge( $limaluz_widget_backup, $normal_dashboard );

 	$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
} 
add_action( 'wp_dashboard_setup', 'limaluz_add_dashboard_widget' );

function limaluz_dashboard_widget_function() {
	echo '<p><img src="' . plugin_dir_url( __FILE__ ) . 'assets/limelight.png' . '" />Hello World, I\'m a great Dashboard Widget</p>';
}

function limaluz_product_index( $id ) {

	$args = array(
		'method' => 'product_index',
		'product_id' => $id,
	);

	$response = limaluz_auth( $args );
	return $response;
}

function limaluz_generate_products() {

	//check override
	//remove all products
	//remove all categories (shop & under)

	$options = get_option( 'limaluz_options_campaign' );
	parse_str( limaluz_campaign_view( $options['campaign'] ), $x );

	$product_index = limaluz_product_index( $x['product_id'] );
	parse_str( $product_index, $r );
	
	preg_match( "/product_description=([^&]*)/", $product_index, $matches );

	//echo '<pre>';
	//var_dump( $r );
	//echo '</pre>';
	//echo '<hr>';

	$product_ids =				explode( ',', $x['product_id'] );
	$product_names =			explode( ',', $r['product_name'] );
	$product_descriptions =		explode( ',', $matches[1] );
	$product_skus =				explode( ',', $r['product_sku'] );
	$product_prices =			explode( ',', $r['product_price'] );
	$product_category_names =	explode( ',', $r['product_category_name'] );
	$product_max_quantities =	explode( ',', $r['product_max_quantity'] );
	$product_rebill_prices =	explode( ',', $r['product_rebill_price'] );
	$product_rebill_days =		explode( ',', $r['product_rebill_day'] );

	// (1) rebill_prices 
	// (2) rebill_days 
	// (3) descriptions

	//checking success response
	/*
		foreach ( $response_codes as $response_code ) {
			if ( ! in_array( $response_code, $accepted ) ) {
				$success = false;
			}
		}

		if ( $success ) {}
	*/

	$html =
		'<table class="product-import-table">
		<tr>
			<th width="5%">id</th>
			<th width="20%">name</th>
			<th width="25%">description</th>
			<th width="20%">sku</th>
			<th width="5%">price</th>
			<th width="10%">category_name</th>
			<th width="5%">max_quantity</th>
			<th width="5%">rebill_price</th>
			<th width="5%">rebill_days</th>
		</tr>';

	foreach ( $product_ids as $key => $val ) {

		$html .=
			'<tr class="product-import-row">' .
				'<td>' . $product_ids[$key] . '</td>' .
				'<td>' . $product_names[$key]. '</td>' .
				'<td>' . $product_descriptions[$key]. '</td>' .
				'<td>' . $product_skus[$key] . '</td>' .
				'<td>' . $product_prices[$key] . '</td>' .
				'<td>' . $product_category_names[$key] . '</td>' .
				'<td>' . $product_max_quantities[$key]. '</td>' .
				'<td>' . $product_rebill_prices[$key]. '</td>' .
				'<td>' . $product_rebill_days[$key]. '</td>' .
			'</tr>';

		//add product & categorize
		$page_id = limealuz_add_post( $product_names[$key], $product_names[$key], 'products' );
		wp_set_object_terms( $page_id, limaluz_categorize_product( $product_names[$key] ), 'category' );
		
		//grab and add custom meta
	}

	$html .= '</table>';
	echo $html;
}

function limaluz_categorize_product( $product_name ) {
	$exist   = get_cat_ID( 'Shop' );
	$cat_ids = array();

	if ( ! $exist ) {
		$cat_ids[] = wp_insert_category( array(
			'cat_name' => 'Shop',
			'taxonomy' => 'category',
		) );
	} else {
		$cat_ids[] = $exist;
	}

	$exist = get_cat_ID( $product_name );

	if ( ! $exist ) {
		$cat_ids[] = wp_insert_category( array(
			'cat_name' => $product_name,
			'taxonomy' => 'category',
			'category_parent' => $cat_ids[0],
			'category_description' => 'Edit this category to change the description for the category. You can also change the image associated with this particular category from wp-admin &raquo; posts &raquo; categories.'
		) );
	} else {
		$cat_ids[] = $exist;
	}

	return $cat_ids;
}

function limaluz_generate_pages() {

	//check override
	//remove all pages

	if ( 1 ) {
		//storefront
		$pages = array(
			'Home' => 'home',
			'Checkout' => 'checkout',
			'Cart' => 'cart',
			'Thank You' => 'thank-you',
			'My Account' => 'account',
			'Order History' => 'orders',
			'Subscriptions' => 'subscriptions',
			'Blog' => 'blog',
			'Terms' => 'terms',
			'Privacy' => 'privacy',
			'Contact' => 'contact',
		);
	} else {
		//funnel
		$pages = array(
			'Home' => 'home',
			'Checkout' => 'checkout',
			'Cart' => 'cart',
			'Thank You' => 'thank-you',
		);
	}

	$pages_fineprint = array(
		'Terms' => 'terms',
		'Privacy' => 'privacy',
		'Contact' => 'contact',
	);

	$pages = array_merge( $pages, $pages_fineprint );

	foreach ( $pages as $title => $slug ) {
		limealuz_add_post( $title, $slug );
	}
}

function limealuz_add_post( $title, $slug, $type='page' ) {
	$args = array(
		'post_title' => $title,
		'post_name' => $slug,
		'post_content' => 'welcome',
		'post_status' => 'publish',
		'post_type' => $type,
		'ping_status' => 'closed',
		'comment_status' => 'closed',
	);

	$page = get_page_by_title( $title );
	if ( $page->ID ) {
		$args = array_merge( array( 'ID' => $page->ID ), $args );
		$new_id = wp_update_post(
			$args
		);
	} else {
		$new_id = wp_insert_post(
			$args
		);
	}
	return $new_id;
}

function limaluz_comments_to_reviews( $translated_text, $untranslated_text, $domain ) {
    if( FALSE !== stripos( $untranslated_text, 'comment' ) ) {
		$translated_text = str_ireplace( 'Comment', 'Review', $untranslated_text );
	}
    return $translated_text;
}
is_admin() && add_filter( 'gettext', 'limaluz_comments_to_reviews', 99, 3 );

function limaluz_form_handling() {

	if ( $_POST['cart'] ) {
		$x = limaluz_form_cart();
	} elseif ( $_POST['checkout']['form'] ) {
		$x = limaluz_form_checkout();
	} elseif ( $_POST['product']['form'] ) {
		$x = limaluz_form_product();
	} elseif ( $_POST['subscriptions']['form'] ) {
		$x = limaluz_form_subscriptions();
	} elseif ( $_POST['orders']['form'] ) {
		$x = limaluz_form_orders();
	} elseif ( $_POST['account']['form'] ) {
		$x = limaluz_form_account();
	}
}
add_action('wp_head', 'limaluz_form_handling');

function limaluz_form_cart() {

}

function limaluz_form_checkout() {

}

//adding product
function limaluz_form_product() {
	$cart_old = $_SESSION['cart'];
	$cart_new = $cart_old . json_encode( $_POST['product'] );
	$_SESSION['cart'] = $cart_new;
	
	echo '<pre>';
	var_dump( $_SESSION['cart'] );
	echo '</pre>';
}

function limaluz_form_subscriptions() {

}

function limaluz_form_orders() {

}

function limaluz_form_account() {

}

function limaluz_product_button( $content ) {

    if( is_single() && 'products' == get_post_type() ) {

		//get product meta and fill form
		$product = '';

		$content = <<<HTML
		<form id="product" class="form-product" method="post">
			<input type="hidden" name="product[form]" value="1">
			<input type="hidden" name="product[sku]" value="heloo-sku">
			<input type="hidden" name="product[name]" value="Helloooo">
			<input type="number" name="product[quantity]" value="10">
			<input type="submit" value="Add To Cart">
		</form>
HTML;
	}
    return $content;
}
add_filter( 'the_content', 'limaluz_product_button' );