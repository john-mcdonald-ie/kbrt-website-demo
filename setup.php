<?php
/**
 * KBRT website demo: populates a fresh WordPress Playground with the KBRT theme,
 * pages, news and a working WooCommerce shop (demo payment, no money taken).
 * Run after WooCommerce and the KBRT theme are installed and active.
 */
if ( ! defined( 'ABSPATH' ) ) {
	require_once '/wordpress/wp-load.php';
}
require_once ABSPATH . 'wp-admin/includes/admin.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

// ---------- Site settings ----------
update_option( 'blogname', 'Kevin Bell Repatriation Trust' );
update_option( 'blogdescription', 'We help bereaved families in all 32 counties of Ireland bring home loved ones who die abroad in sudden or tragic circumstances.' );
update_option( 'timezone_string', 'Europe/London' );
update_option( 'date_format', 'j F Y' );
update_option( 'permalink_structure', '/%postname%/' );
update_option( 'blog_public', 0 );

if ( function_exists( 'kbrt_import_brand_assets' ) ) {
	kbrt_import_brand_assets();
}

// ---------- Helpers ----------
function kbrt_demo_page( $slug, $title, $content, $excerpt = '', $template = 'page-designed' ) {
	$existing = get_page_by_path( $slug );
	$id       = wp_insert_post(
		array(
			'ID'           => $existing ? $existing->ID : 0,
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $title,
			'post_content' => $content,
			'post_excerpt' => $excerpt,
		)
	);
	if ( $template ) {
		update_post_meta( $id, '_wp_page_template', $template );
	}
	return $id;
}
function kbrt_demo_image( $file, $title ) {
	$path = get_theme_file_path( 'assets/images/' . $file );
	if ( ! file_exists( $path ) ) {
		return 0;
	}
	$tmp = wp_tempnam( $file );
	copy( $path, $tmp );
	$id = media_handle_sideload( array( 'name' => $file, 'tmp_name' => $tmp ), 0, $title );
	return is_wp_error( $id ) ? 0 : (int) $id;
}
function kbrt_demo_pattern( $slug ) {
	return '<!-- wp:pattern {"slug":"' . $slug . '"} /-->';
}

// ---------- Pages ----------
kbrt_demo_page( 'get-help', 'Get help now', kbrt_demo_pattern( 'kbrt/page-get-help' ), 'Someone has died abroad? The Kevin Bell Repatriation Trust helps families in all 32 counties of Ireland bring their loved one home. Call +44 (0)28 3083 3311.' );
kbrt_demo_page( 'donate', 'Donate', kbrt_demo_pattern( 'kbrt/page-donate' ), 'Donate to the Kevin Bell Repatriation Trust in euro through iDonate or in sterling by card, and help bring the next family’s loved one home.' );
kbrt_demo_page( 'our-story', 'Our story', kbrt_demo_pattern( 'kbrt/page-our-story' ), 'Kevin Bell’s story, the Trust’s history since 2013, its impact across 88 countries and its governance.' );
kbrt_demo_page( 'contact', 'Contact', kbrt_demo_pattern( 'kbrt/page-contact' ), 'Contact the Kevin Bell Repatriation Trust, Unit 7 Whitegates Business Park, Newry. Phone +44 (0)28 3083 3311.' );
$news = kbrt_demo_page( 'news', 'News and stories', '', 'News from the Kevin Bell Repatriation Trust.', '' );
$home = kbrt_demo_page( 'home', 'Home', '', 'The Kevin Bell Repatriation Trust helps bereaved families in all 32 counties of Ireland bring home loved ones who die abroad.', '' );
update_option( 'page_on_front', $home );
update_option( 'page_for_posts', $news );
update_option( 'show_on_front', 'page' );

$placeholder = '<!-- wp:paragraph --><p>[This page will be supplied by the Trust before launch.]</p><!-- /wp:paragraph -->';
foreach ( array( 'privacy-policy' => 'Privacy notice', 'cookie-policy' => 'Cookie policy', 'accessibility' => 'Accessibility statement', 'shop-terms' => 'Shop terms, delivery and returns' ) as $slug => $title ) {
	kbrt_demo_page( $slug, $title, $placeholder, '', '' );
}

// ---------- News (short summaries; originals are press articles) ----------
$posts = array(
	array( '2026-05-24', 'The Irish Times tells the story of Kevin’s legacy', 'The Irish Times reports how a son’s death in New York in 2013 has led to almost 2,500 grieving families being helped, and how the Trust now handles 30 to 35 repatriations a month.', 'https://www.irishtimes.com/ireland/2026/05/24/this-could-only-happen-in-ireland-how-a-sons-death-in-new-york-has-led-to-almost-2500-grieving-families-being-helped/' ),
	array( '2026-05-11', 'Founders step back after 13 years', 'Colin and Eithne Bell are stepping back from the day-to-day running of the Trust after 13 years and around 2,500 loved ones brought home. Director of Operations Diarmaid McAuley now leads the team.', 'https://www.irishnews.com/news/northern-ireland/keven-bell-trust-founders-to-take-step-back-after-13-years-and-2500-loved-ones-returned-home-VE2GDTMGZJB7PD76LV7JJI6JPE/' ),
	array( '2020-06-02', 'Trust opens a new centre in Newry', 'The family-run Trust opened a new centre in Newry.', '' ),
	array( '2018-08-22', 'Colin Bell receives a Points of Light award from the Prime Minister', 'The Newry man who founded the Trust after his son Kevin died in New York received a Points of Light award from the Prime Minister.', 'https://www.pointsoflight.gov.uk/the-kevin-bell-repatriation-trust' ),
	array( '2019-06-04', 'Trust has helped bring home almost 500 people', 'The Co Down charity had helped bring home almost 500 people who died abroad.', '' ),
	array( '2019-05-12', '“Your creed or colour doesn’t matter, we’ll help any family”', 'In an interview, Colin Bell explained that the Trust helps any family in Ireland, whatever their background or where they live.', '' ),
);
$photos = array( 'photo-dublin-liffey.webp', 'photo-carrick-a-rede.webp', 'photo-beach.webp', 'photo-giants-causeway.webp', 'photo-cliffs-of-moher.webp', 'photo-beach.webp' );
$photo_ids = array();
foreach ( $posts as $i => $p ) {
	$found = get_posts( array( 'post_type' => 'post', 'title' => $p[1], 'post_status' => 'any', 'numberposts' => 1 ) );
	if ( $found ) {
		continue;
	}
	$id = wp_insert_post(
		array(
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_title'   => $p[1],
			'post_date'    => $p[0] . ' 10:00:00',
			'post_excerpt' => $p[2],
			'post_content' => '<!-- wp:paragraph --><p>' . esc_html( $p[2] ) . '</p><!-- /wp:paragraph -->'
				. ( $p[3] ? '<!-- wp:paragraph --><p><a href="' . esc_url( $p[3] ) . '">Read the full article</a></p><!-- /wp:paragraph -->' : '' ),
		)
	);
	$file = $photos[ $i ];
	if ( empty( $photo_ids[ $file ] ) ) {
		$photo_ids[ $file ] = kbrt_demo_image( $file, 'Irish landscape' );
	}
	if ( $photo_ids[ $file ] ) {
		set_post_thumbnail( $id, $photo_ids[ $file ] );
	}
}
foreach ( get_posts( array( 'post_type' => 'post', 'title' => 'Hello world!', 'numberposts' => 1 ) ) as $hello ) {
	wp_delete_post( $hello->ID, true );
}
foreach ( array( 'sample-page' ) as $slug ) {
	$pg = get_page_by_path( $slug );
	if ( $pg ) {
		wp_delete_post( $pg->ID, true );
	}
}

// ---------- WooCommerce ----------
if ( class_exists( 'WooCommerce' ) ) {
	if ( class_exists( 'WC_Install' ) ) {
		if ( ! get_option( 'woocommerce_version' ) ) {
			WC_Install::install();
		}
		WC_Install::create_pages();
	}
	foreach ( array( 'cart' => 'Basket', 'checkout' => 'Checkout' ) as $page_key => $page_title ) {
		$page_id = function_exists( 'wc_get_page_id' ) ? (int) wc_get_page_id( $page_key ) : 0;
		if ( $page_id > 0 && get_the_title( $page_id ) !== $page_title ) {
			wp_update_post( array( 'ID' => $page_id, 'post_title' => $page_title ) );
		}
	}
	update_option( 'woocommerce_currency', 'GBP' );
	update_option( 'woocommerce_default_country', 'GB' );
	update_option( 'woocommerce_allowed_countries', 'specific' );
	update_option( 'woocommerce_specific_allowed_countries', array( 'GB', 'IE' ) );
	update_option( 'woocommerce_calc_taxes', 'no' );
	update_option( 'woocommerce_coming_soon', 'no' );
	update_option( 'woocommerce_store_pages_only', 'no' );
	update_option( 'woocommerce_onboarding_profile', array( 'skipped' => true ) );
	update_option( 'woocommerce_task_list_hidden', 'yes' );
	update_option( 'woocommerce_demo_store', 'yes' );
	update_option( 'woocommerce_demo_store_notice', 'Demonstration shop: orders are not real and no payment is taken.' );

	// Demo payment: Cash on delivery, relabelled.
	update_option(
		'woocommerce_cod_settings',
		array(
			'enabled'            => 'yes',
			'title'              => 'Demo payment (no money is taken)',
			'description'        => 'This is a demonstration of the new KBRT shop. Placing an order here does not charge you.',
			'instructions'       => 'Thank you. This was a demonstration order.',
			'enable_for_methods' => array(),
			'enable_for_virtual' => 'yes',
		)
	);

	// Delivery zone with a free demo method.
	if ( class_exists( 'WC_Shipping_Zone' ) ) {
		$zones = WC_Shipping_Zones::get_zones();
		if ( ! $zones ) {
			$zone = new WC_Shipping_Zone();
			$zone->set_zone_name( 'United Kingdom and Ireland' );
			$zone->add_location( 'GB', 'country' );
			$zone->add_location( 'IE', 'country' );
			$zone->save();
			$zone->add_shipping_method( 'free_shipping' );
		}
	}

	// Import the product photographs only when at least one product still has to be created.
	$front   = 0;
	$back    = 0;
	$polo    = 0;
	$missing = false;
	foreach ( array( 'kbrt-jersey-mens', 'kbrt-jersey-womens', 'kbrt-jersey-kids', 'kbrt-polo-mens', 'kbrt-polo-womens' ) as $product_slug ) {
		if ( ! get_page_by_path( $product_slug, OBJECT, 'product' ) ) {
			$missing = true;
		}
	}
	if ( $missing ) {
		$front = kbrt_demo_image( 'product-jersey-front.webp', 'KBRT jersey, front' );
		$back  = kbrt_demo_image( 'product-jersey-back.webp', 'KBRT jersey, back' );
		$polo  = kbrt_demo_image( 'product-polo.webp', 'KBRT polo' );
	}

	$mens    = array( 'Small', 'Medium', 'Large', 'X Large', 'XX Large', '1X Large', '3X Large', '4X Large', '5X Large', 'Small Tall Fit', 'Medium Tall Fit', 'Large Tall Fit', 'X Large Tall Fit' );
	$polom   = array( 'Small', 'Medium', 'Large', 'X Large', 'XX Large', '1X Large', '3X Large', '4X Large', '5X Large' );
	$womens  = array( '8', '10', '12', '14', '16', '18' );
	$kids    = array( '0/6 Months', '6/12 Months', '1/2 Years', '3/4 Years', '5/6 Years', '7/8 Years', '9/10 Years', '10/11 Years', '11/12 Years', 'Age 13' );
	$catalog = array(
		array( 'kbrt-jersey-mens', 'KBRT Jersey, Men’s', '40', $mens, array( $front, $back ), 'Men’s KBRT jersey by O’Neills, in green with the gold band, the goldfinch and a map of the world. Sizes Small to 5X Large, plus tall fit Small to X Large.', 'Jerseys' ),
		array( 'kbrt-jersey-womens', 'KBRT Jersey, Women’s', '40', $womens, array( $front, $back ), 'Women’s KBRT jersey by O’Neills, in green with the gold band. Sizes 8 to 18.', 'Jerseys' ),
		array( 'kbrt-jersey-kids', 'KBRT Jersey, Kids’', '30', $kids, array( $front, $back ), 'Kids’ KBRT jersey by O’Neills. Sizes 0 to 6 months up to age 13.', 'Jerseys' ),
		array( 'kbrt-polo-mens', 'KBRT Polo, Men’s', '40', $polom, array( $polo ), 'Men’s KBRT polo by O’Neills, in green with navy shoulders and gold piping. Sizes Small to 5X Large.', 'Polos' ),
		array( 'kbrt-polo-womens', 'KBRT Polo, Women’s', '40', $womens, array( $polo ), 'Women’s KBRT polo by O’Neills, in green with navy shoulders and gold piping. Sizes 8 to 18.', 'Polos' ),
	);
	foreach ( $catalog as $c ) {
		list( $slug, $name, $price, $sizes, $images, $desc, $cat ) = $c;
		if ( get_page_by_path( $slug, OBJECT, 'product' ) ) {
			continue;
		}
		$term = term_exists( $cat, 'product_cat' );
		if ( ! $term ) {
			$term = wp_insert_term( $cat, 'product_cat' );
		}
		$product = new WC_Product_Variable();
		$product->set_name( $name );
		$product->set_slug( $slug );
		$product->set_status( 'publish' );
		$product->set_description( $desc );
		$product->set_short_description( $desc );
		$product->set_category_ids( array( (int) ( is_array( $term ) ? $term['term_id'] : $term ) ) );
		$images = array_values( array_filter( $images ) );
		if ( $images ) {
			$product->set_image_id( $images[0] );
			$product->set_gallery_image_ids( array_slice( $images, 1 ) );
		}
		$attribute = new WC_Product_Attribute();
		$attribute->set_name( 'Size' );
		$attribute->set_options( $sizes );
		$attribute->set_visible( true );
		$attribute->set_variation( true );
		$product->set_attributes( array( $attribute ) );
		$product_id = $product->save();
		foreach ( $sizes as $size ) {
			$variation = new WC_Product_Variation();
			$variation->set_parent_id( $product_id );
			$variation->set_attributes( array( 'size' => $size ) );
			$variation->set_regular_price( $price );
			$variation->set_stock_status( 'instock' );
			$variation->save();
		}
		WC_Product_Variable::sync( $product_id );
	}
}

flush_rewrite_rules();
echo "KBRT demo ready\n";
