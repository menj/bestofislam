<?php
/**
 * Best of Islam child theme bootstrap.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

define( 'BOI_VERSION', '1.56.0' );
define( 'BOI_DIR', get_stylesheet_directory() );
define( 'BOI_URI', get_stylesheet_directory_uri() );

require_once BOI_DIR . '/inc/taxonomy.php';
require_once BOI_DIR . '/inc/blocks.php';
require_once BOI_DIR . '/inc/schema.php';
require_once BOI_DIR . '/inc/voting.php';
require_once BOI_DIR . '/inc/settings.php';
require_once BOI_DIR . '/inc/setup.php';
require_once BOI_DIR . '/inc/appearance.php';
require_once BOI_DIR . '/inc/reflections.php';
require_once BOI_DIR . '/inc/search.php';
require_once BOI_DIR . '/inc/sections.php';
require_once BOI_DIR . '/inc/seo.php';
require_once BOI_DIR . '/inc/icons.php';
require_once BOI_DIR . '/inc/features.php';
require_once BOI_DIR . '/inc/contact.php';
require_once BOI_DIR . '/inc/front.php';
require_once BOI_DIR . '/inc/images.php';
require_once BOI_DIR . '/inc/questions.php';
require_once BOI_DIR . '/inc/reading.php';
require_once BOI_DIR . '/inc/structured-data.php';
require_once BOI_DIR . '/inc/unlist.php';
require_once BOI_DIR . '/inc/search-permalinks.php';
require_once BOI_DIR . '/inc/login.php';
require_once BOI_DIR . '/inc/login-screen.php';

/**
 * Enqueues front-end assets, conditionally where possible.
 *
 * The parent theme stylesheet is loaded by Twenty Twenty-Five itself, so the
 * child only registers its own files.
 *
 * @return void
 */
function boi_enqueue_assets() {
	wp_enqueue_style(
		'boi-theme-toggle',
		BOI_URI . '/assets/css/theme-toggle.css',
		array(),
		BOI_VERSION
	);

	wp_enqueue_style(
		'boi-navigation',
		BOI_URI . '/assets/css/navigation.css',
		array(),
		BOI_VERSION
	);

	wp_enqueue_style(
		'boi-front',
		BOI_URI . '/assets/css/front.css',
		array( 'boi-navigation' ),
		BOI_VERSION
	);

	if ( is_front_page() ) {
		wp_enqueue_script(
			'boi-questions',
			BOI_URI . '/assets/js/questions.js',
			array(),
			BOI_VERSION,
			array( 'in_footer' => true, 'strategy' => 'defer' )
		);
	}

	wp_enqueue_style(
		'boi-footer',
		BOI_URI . '/assets/css/footer.css',
		array(),
		BOI_VERSION
	);

	wp_enqueue_style(
		'boi-print',
		BOI_URI . '/assets/css/print.css',
		array(),
		BOI_VERSION,
		'print'
	);

	wp_enqueue_script(
		'boi-theme-toggle',
		BOI_URI . '/assets/js/theme-toggle.js',
		array(),
		BOI_VERSION,
		true
	);

	if ( is_search() ) {
		wp_enqueue_style(
			'boi-search',
			BOI_URI . '/assets/css/search.css',
			array(),
			BOI_VERSION
		);
	}

	// The header and footer parts use these classes, so the layout stylesheet
	// is needed on every view rather than only where a listicle appears.
	$has_listicle = is_singular() && has_block( 'bestofislam/listicle' );

	wp_enqueue_style(
		'boi-scripts',
		BOI_URI . '/assets/css/scripts.css',
		array(),
		BOI_VERSION
	);

	wp_enqueue_style(
		'boi-listicle',
		BOI_URI . '/assets/css/listicle.css',
		array( 'boi-scripts' ),
		BOI_VERSION
	);

	if ( ! $has_listicle ) {
		return;
	}

	if ( ! boi_voting_enabled() ) {
		return;
	}

	wp_enqueue_style(
		'boi-voting',
		BOI_URI . '/assets/css/voting.css',
		array( 'boi-listicle' ),
		BOI_VERSION
	);

	wp_enqueue_script(
		'boi-voting',
		BOI_URI . '/assets/js/voting.js',
		array(),
		BOI_VERSION,
		true
	);

	wp_localize_script(
		'boi-voting',
		'boiVoting',
		array(
			'endpoint'  => esc_url_raw( rest_url( 'bestofislam/v1/vote' ) ),
			'nonce'     => wp_create_nonce( 'wp_rest' ),
			'postId'    => get_the_ID(),
			'loginOnly' => boi_voting_requires_login(),
			'loggedIn'  => is_user_logged_in(),
			'strings'   => array(
				'error'     => __( 'Your vote could not be recorded. Please try again.', 'bestofislam' ),
				'duplicate' => __( 'You have already voted on this entry.', 'bestofislam' ),
				'loginOnly' => __( 'Please log in to vote.', 'bestofislam' ),
				'throttled' => __( 'Too many votes in a short period. Please wait a moment.', 'bestofislam' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'boi_enqueue_assets' );

/**
 * Adds the listicle editor stylesheet.
 *
 * @return void
 */
function boi_editor_style() {
	add_editor_style( array( 'assets/css/scripts.css', 'assets/css/theme-toggle.css', 'assets/css/navigation.css', 'assets/css/editor-listicle.css', 'assets/css/editor-typography.css' ) );
}
add_action( 'after_setup_theme', 'boi_editor_style' );

/**
 * Registers the block pattern category used by the listicle starter pattern.
 *
 * @return void
 */
function boi_register_pattern_category() {
	if ( ! function_exists( 'register_block_pattern_category' ) ) {
		return;
	}

	register_block_pattern_category(
		'bestofislam-listicles',
		array( 'label' => __( 'Listicles', 'bestofislam' ) )
	);
}
add_action( 'init', 'boi_register_pattern_category' );

/**
 * Creates or updates the votes table on activation and on version change.
 *
 * @return void
 */
function boi_maybe_install() {
	if ( get_option( 'boi_db_version' ) === BOI_VERSION ) {
		return;
	}

	boi_install_votes_table();

	// Population is idempotent and migrates its own earlier records, so an
	// update carries every seed fix to an existing site without a reinstall.
	if ( get_option( 'boi_content_populated' ) && function_exists( 'boi_populate_content' ) ) {
		boi_populate_content();
	}

	update_option( 'boi_db_version', BOI_VERSION );
}
add_action( 'after_switch_theme', 'boi_maybe_install' );
add_action( 'admin_init', 'boi_maybe_install' );
add_action( 'init', 'boi_maybe_install', 20 );

/**
 * Enqueues the typography rules last, after every other stylesheet, so that
 * justified running text and 1.5 line spacing hold throughout.
 *
 * @return void
 */
function boi_enqueue_typography() {
	wp_enqueue_style(
		'boi-typography',
		BOI_URI . '/assets/css/typography.css',
		array(),
		BOI_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'boi_enqueue_typography', 100 );

/* Contextual in-content links. */
require_once get_stylesheet_directory() . '/inc/contextual-links.php';
