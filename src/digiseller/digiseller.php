<?php
/**
 * @package Digiseller
 * @version 0.1
 */
/*
Plugin Name: Digiseller
Plugin URI: https://github.com/digiseller/wordpress-plugin
Description: Easy integration of http://digiseller.ru into you site.
Author: Digiseller
Version: 1.0.0
Author URI: http://digiseller.ru
Text Domain: digiseller
Domain Path: /languages
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


add_action('plugins_loaded', 'digiseller_init');
function digiseller_init() {
	load_plugin_textdomain( 'digiseller', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}

if (!is_admin())
add_shortcode( 'ds', 'digiseller_shortcode' );

function digiseller_shortcode( $atts )
{
	$r = '';

	if (!isset($atts['id']) or empty($atts['id'])) {
		$r.= "\n\n";
		$r.= '<!--[ds]-->';
		$r.= "\n\n";
		$r.= '<span class="error">[ds ' . __('attribute id is not set', 'digiseller') . ']</span>';
		$r.= "\n\n";
		$r.= '<!--[/ds]-->';
		$r.= "\n\n";
		return $r;
	}

	$id = $atts['id'];
	$op = (isset($atts['op'])) ? $atts['op'] : 'logo,search,cart,purchases,lang,cath,menu';


	$options = array(
		'sellerid' => $id,
		'logo' => strpos($op, 'logo') !== false,
		'search' => strpos($op, 'search') !== false,
		'cart' => strpos($op, 'cart') !== false,
		'purchases' => strpos($op, 'purchases') !== false,
		'lang' => strpos($op, 'lang') !== false,
		'cat' => strpos($op, 'catv') !== false ? 'v' : strpos($op, 'cat') !== false ? 'h' : '0',
		'menu' => strpos($op, 'menu') !== false,
	);


	$r.= "\n\n";
	$r.= '<!--[ds id="' . $id . '" op="' . $op . '"]-->';
	$r.= "\n\n";
	$r.= digiseller_generator($options);
	$r.= "\n\n";
	$r.= '<!--[/ds id="' . $id . '" op="' . $op . '"]-->';
	$r.= "\n\n";
	return $r;
}



function digiseller_generator($options) {

	$o = array(
		'sellerid' => $options['sellerid'] ? 1 : 0,
		'logo' => $options['logo'] ? 1 : 0,
		'search' => $options['search'] ? 1 : 0,
		'cart' => $options['cart'] ? 1 : 0,
		'purchases' => $options['purchases'] ? 1 : 0,
		'lang' => $options['lang'] ? 1 : 0,
		'cat' => in_array($options['cat'], array('h', 'v')) ? $options['cat'] : '0',
		'menu' => $options['menu'] ? 1 : 0,
	);

	ob_start();
	?>

	<script>!function(e){var l=function(l){return e.cookie.match(new RegExp("(?:^|; )digiseller-"+l+"=([^;]*)"))},i=l("lang"),s=l("cart_uid"),t=i?"&lang="+i[1]:"",d=s?"&cart_uid="+s[1]:"",r=e.getElementsByTagName("head")[0]||e.documentElement,n=e.createElement("link"),a=e.createElement("script");n.type="text/css",n.rel="stylesheet",n.id="digiseller-css",n.href="//shop.digiseller.ru/xml/store_css.asp?seller_id=<?php echo $options['sellerid']; ?>",a.async=!0,a.id="digiseller-js",a.src="//www.digiseller.ru/store/digiseller-api.js.asp?seller_id=<?php echo $options['sellerid']; ?>"+t+d,!e.getElementById(n.id)&&r.appendChild(n),!e.getElementById(a.id)&&r.appendChild(a)}(document);</script>

	<span class="digiseller-body" id="digiseller-body" 
		data-logo="<?php echo $o['logo']; ?>" 
		data-search="<?php echo $o['search']; ?>"
		data-cart="<?php echo $o['cart']; ?>" 
		data-purchases="<?php echo $o['purchases']; ?>" 
		data-langs="<?php echo $o['lang']; ?>" 
		data-cat="<?php echo $o['cat']; ?>" 
		data-downmenu="<?php echo $o['menu']; ?>"></span>

	<?php
	$r = ob_get_contents();
	ob_end_clean();
	return $r;
}





if (!is_admin())
add_filter( 'the_content', 'digiseller_content' );

function digiseller_content( $content ) {

	$pageid = get_option( 'digiseller_setting_pageid', 0 );
	$sellerid = get_option( 'digiseller_setting_sellerid', 0 );

	if ( !empty($pageid) and !empty($sellerid) and is_page( $pageid ) ) {
		$options = array(
			'sellerid' => $sellerid,
			'logo' => get_option( 'digiseller_setting_logo', 'off' ) == 'on',
			'search' => get_option( 'digiseller_setting_search', 'off' ) == 'on',
			'cart' => get_option( 'digiseller_setting_cart', 'off' ) == 'on',
			'purchases' => get_option( 'digiseller_setting_purchases', 'off' ) == 'on',
			'lang' => get_option( 'digiseller_setting_lang', 'off' ) == 'on',
			'cat' => get_option( 'digiseller_setting_cat', '0' ),
			'menu' => get_option( 'digiseller_setting_menu', 'off' ) == 'on',
		);

		return digiseller_generator($options);
	}
	return $content;
}




if (is_admin())
add_action( 'admin_menu', 'digiseller_settings_admin_menu' );

function digiseller_settings_admin_menu() {
    add_options_page(
        'Digiseller',
        'Digiseller',
        'manage_options',
        'digiseller_settings',
        'digiseller_settings_page'
    );
}

function digiseller_settings_page() {
    //if ( isset( $_REQUEST['settings-updated'] ) ) { echo '<pre>'; print_r($_REQUEST); die; }    
	?>
    <div class="wrap">
 
        <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
           
        <form method="post" action="options.php">
            <?php settings_fields( 'digiseller_settings' ); ?>
            <?php do_settings_sections( 'digiseller_settings' ); ?>

            <div class="info">* <?php _e('Menu items can be configured in', 'digiseller'); ?> <a href="https://my.digiseller.ru/inside/myshop.asp?view=settings"><?php _e('settings', 'digiseller'); ?></a></div>
			<div class="info">** <?php _e("Only your products can be added into cart (not partners' products)", 'digiseller'); ?></div>

            <?php submit_button(); ?>
        </form>
    </div>
	<?php
}





if (is_admin())
add_action( 'admin_init', 'digiseller_settings' );

function digiseller_settings() {

	add_settings_section(
		'digiseller_setting_section',
		__('Select what to show on site', 'digiseller'),
		'digiseller_setting_section_callback',
		'digiseller_settings'
	);
	 
	add_settings_field(
		'digiseller_setting_pageid', __('Page', 'digiseller'),
		'digiseller_setting_pageid_callback', 'digiseller_settings', 'digiseller_setting_section'
	);

	add_settings_field(
		'digiseller_setting_sellerid', __('SellerID', 'digiseller'),
		'digiseller_setting_sellerid_callback', 'digiseller_settings', 'digiseller_setting_section'
	);

	add_settings_field(
		'digiseller_setting_logo', __('Logo', 'digiseller'),
		'digiseller_setting_logo_callback', 'digiseller_settings', 'digiseller_setting_section'
	);

	add_settings_field(
		'digiseller_setting_search', __('Search', 'digiseller'),
		'digiseller_setting_search_callback', 'digiseller_settings', 'digiseller_setting_section'
	);

	add_settings_field(
		'digiseller_setting_cart', __('Cart', 'digiseller') . ' **',
		'digiseller_setting_cart_callback', 'digiseller_settings', 'digiseller_setting_section'
	);

	add_settings_field(
		'digiseller_setting_purchases', __('Purchases', 'digiseller'),
		'digiseller_setting_purchases_callback', 'digiseller_settings', 'digiseller_setting_section'
	);

	add_settings_field(
		'digiseller_setting_lang', __('Language selector', 'digiseller'),
		'digiseller_setting_lang_callback', 'digiseller_settings', 'digiseller_setting_section'
	);

	add_settings_field(
		'digiseller_setting_cat', __('Categories', 'digiseller'),
		'digiseller_setting_cat_callback', 'digiseller_settings', 'digiseller_setting_section'
	);

	add_settings_field(
		'digiseller_setting_menu', __('Bottom menu', 'digiseller') . ' *',
		'digiseller_setting_menu_callback', 'digiseller_settings', 'digiseller_setting_section'
	);

	register_setting( 'digiseller_settings', 'digiseller_setting_pageid' );
	register_setting( 'digiseller_settings', 'digiseller_setting_sellerid' );
	register_setting( 'digiseller_settings', 'digiseller_setting_logo' );
	register_setting( 'digiseller_settings', 'digiseller_setting_search' );
	register_setting( 'digiseller_settings', 'digiseller_setting_cart' );
	register_setting( 'digiseller_settings', 'digiseller_setting_purchases' );
	register_setting( 'digiseller_settings', 'digiseller_setting_lang' );
	register_setting( 'digiseller_settings', 'digiseller_setting_cat' );
	register_setting( 'digiseller_settings', 'digiseller_setting_menu' );
}
 
function digiseller_setting_section_callback() {
	//echo '<p>DigiSeller settings</p>';
}

function digiseller_setting_pageid_callback() {
	$option = 'digiseller_setting_pageid';
	wp_dropdown_pages( array( 
		'id' => $option, 
		'name' => $option, 
		'show_option_none' => __( '&mdash; Select &mdash;' ), 
		'option_none_value' => '0', 
		'selected' => get_option( $option ),
	) );
}

function digiseller_setting_sellerid_callback() {
    $option = 'digiseller_setting_sellerid';
    echo '<input type="text" id="' . $option . '" name="' . $option . '" value="' . esc_attr( get_option( $option ) ) . '" />';
}

function digiseller_setting_logo_callback() { digiseller_setting_checkbox_callback( 'digiseller_setting_logo' ); }
function digiseller_setting_search_callback() { digiseller_setting_checkbox_callback( 'digiseller_setting_search' ); }
function digiseller_setting_cart_callback() { digiseller_setting_checkbox_callback( 'digiseller_setting_cart' ); }
function digiseller_setting_purchases_callback() { digiseller_setting_checkbox_callback( 'digiseller_setting_purchases' ); }
function digiseller_setting_lang_callback() { digiseller_setting_checkbox_callback( 'digiseller_setting_lang' ); }
function digiseller_setting_cat_callback() { 
	$cat = get_option( 'digiseller_setting_cat', '0' );
    echo '<select  id="digiseller_setting_cat" name="digiseller_setting_cat">';
    echo '    <option value="0"' . (($cat == '0') ? ' selected' : '') . '>' . __('Hide', 'digiseller') . '</option>';
    echo '    <option value="h"' . (($cat == 'h') ? ' selected' : '') . '>' . __('Horizontal', 'digiseller') . '</option>';
    echo '    <option value="v"' . (($cat == 'v') ? ' selected' : '') . '>' . __('Vertical', 'digiseller') . '</option>';
    echo '</select>';
}
function digiseller_setting_menu_callback() { digiseller_setting_checkbox_callback( 'digiseller_setting_menu' ); }

function digiseller_setting_checkbox_callback( $option ) {
    echo '<input type="hidden" name="' . $option . '" value="off" />';
    echo '<input type="checkbox" id="' . $option . '" name="' . $option . '"' . ( ( get_option( $option ) == 'on' ) ? ' checked="checked"' : '' ) . ' />';
}






// EOF
