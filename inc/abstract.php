<?php
/**
 * The abstract: a short summary box at the top of an article.
 *
 * An article's abstract states the claim it answers, the points it walks
 * through and where it comes out, in sixty to a hundred words. The text of
 * the seeded articles lives in inc/data/abstracts.json, keyed by slug, so the
 * article bodies (and their sync fingerprints) are untouched. An editor can
 * override it for one post with the Abstract block or the [abstract]
 * shortcode in the body; either one replaces the box at the top.
 *
 * Where it goes. On a single post, at the top of the main text, before the
 * opening paragraph. The box is an <aside>, so the drop cap still falls on
 * the first paragraph of the text (article.css, p:first-of-type).
 *
 * Schema. The same text is the Article node's "abstract" in the theme's
 * graph (inc/schema-graph.php); no second JSON-LD block is printed.
 *
 * Settings. Theme Options, Abstract: on or off, the label, the style and the
 * colour scheme (a preset or a custom accent checked for contrast).
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * The box styles: slug => array( title, description ).
 *
 * @return array<string,array{0:string,1:string}>
 */
function boi_abstract_styles() {
	return array(
		'ruled'    => array( __( 'Ruled', 'bestofislam' ), __( 'A tinted panel with an accent rule down the left edge.', 'bestofislam' ) ),
		'card'     => array( __( 'Card', 'bestofislam' ), __( 'A raised card with a thin border and a soft shadow.', 'bestofislam' ) ),
		'academic' => array( __( 'Academic', 'bestofislam' ), __( 'Rules above and below, a small-caps label, no fill: the abstract of a journal paper.', 'bestofislam' ) ),
		'minimal'  => array( __( 'Minimal', 'bestofislam' ), __( 'The label and the text alone, set a little smaller, with a short accent bar.', 'bestofislam' ) ),
	);
}

/**
 * The colour presets: slug => array( title, accent hex or '' for the theme ).
 * Each preset passes boi_lead_list_accent_ok(): as label text on its own tint
 * it reaches 4.5:1 in the light and the dark appearance (tests/abstract-test.php).
 * Navy and the theme's sky fail in one of the two, so they are not offered.
 *
 * @return array<string,array{0:string,1:string}>
 */
function boi_abstract_schemes() {
	return array(
		'theme'  => array( __( 'Theme azure', 'bestofislam' ), '' ),
		'teal'   => array( __( 'Teal', 'bestofislam' ), '#2a7f8a' ),
		'green'  => array( __( 'Green', 'bestofislam' ), '#2f7d4f' ),
		'amber'  => array( __( 'Amber', 'bestofislam' ), '#8a5a00' ),
		'rose'   => array( __( 'Rose', 'bestofislam' ), '#b0457a' ),
		'violet' => array( __( 'Violet', 'bestofislam' ), '#9b4dca' ),
		'slate'  => array( __( 'Slate', 'bestofislam' ), '#5b6b80' ),
		'custom' => array( __( 'Custom accent', 'bestofislam' ), '' ),
	);
}

/**
 * The bundled abstracts, slug => plain text.
 *
 * @return array<string,string>
 */
function boi_abstract_data() {
	static $data = null;
	if ( null === $data ) {
		$file = BOI_DIR . '/inc/data/abstracts.json';
		$raw  = is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$data = is_array( $raw ) ? array_filter( $raw, 'is_string' ) : array();
	}
	return $data;
}

/**
 * The first Abstract block or [abstract] shortcode in a post's body, as
 * array( title, html ), or null.
 *
 * @param WP_Post $post Post.
 * @return array{0:string,1:string}|null
 */
function boi_abstract_in_body( $post ) {
	$content = (string) $post->post_content;
	if ( has_block( 'bestofislam/abstract', $post ) ) {
		foreach ( parse_blocks( $content ) as $block ) {
			if ( 'bestofislam/abstract' === $block['blockName'] ) {
				$a = isset( $block['attrs'] ) ? $block['attrs'] : array();
				return array( isset( $a['title'] ) ? (string) $a['title'] : '', isset( $a['text'] ) ? (string) $a['text'] : '' );
			}
		}
	}
	if ( false !== strpos( $content, '[abstract' ) && preg_match( '/' . get_shortcode_regex( array( 'abstract' ) ) . '/s', $content, $m ) ) {
		$atts = shortcode_parse_atts( $m[3] );
		return array( is_array( $atts ) && isset( $atts['title'] ) ? (string) $atts['title'] : '', (string) $m[5] );
	}
	return null;
}

/**
 * An article's abstract as plain text: from the body if an editor placed
 * one, else from the bundle. '' when there is none.
 *
 * @param WP_Post|int|null $post Post.
 * @return string
 */
function boi_abstract_text( $post = null ) {
	$post = get_post( $post );
	if ( ! $post instanceof WP_Post || 'post' !== $post->post_type ) {
		return '';
	}
	$own = boi_abstract_in_body( $post );
	if ( $own ) {
		return trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $own[1] ) ) );
	}
	$data = boi_abstract_data();
	return isset( $data[ $post->post_name ] ) ? trim( $data[ $post->post_name ] ) : '';
}

/**
 * The accent of the box, or '' for the theme's own colours.
 *
 * @return string
 */
function boi_abstract_accent() {
	$opts    = boi_get_options();
	$scheme  = isset( $opts['abstract_scheme'] ) ? (string) $opts['abstract_scheme'] : 'theme';
	$schemes = boi_abstract_schemes();
	if ( 'custom' === $scheme ) {
		return (string) $opts['abstract_accent'];
	}
	return isset( $schemes[ $scheme ] ) ? $schemes[ $scheme ][1] : '';
}

/**
 * The box.
 *
 * @param string $html  Abstract text (inline HTML allowed: em, strong, a).
 * @param string $title Label; '' for the saved one.
 * @param string $style Style slug; '' for the saved one.
 * @return string
 */
function boi_abstract_html( $html, $title = '', $style = '' ) {
	$html = trim( (string) $html );
	if ( '' === $html ) {
		return '';
	}
	$opts   = boi_get_options();
	$title  = '' !== trim( $title ) ? $title : (string) $opts['abstract_title'];
	$style  = isset( boi_abstract_styles()[ $style ] ) ? $style : (string) $opts['abstract_style'];
	$style  = isset( boi_abstract_styles()[ $style ] ) ? $style : 'ruled';
	$accent = boi_abstract_accent();
	$attr   = '' !== $accent ? ' style="--boi-abstract-accent:' . esc_attr( $accent ) . '"' : '';
	$allow  = array( 'em' => array(), 'strong' => array(), 'i' => array(), 'b' => array(), 'a' => array( 'href' => true ), 'span' => array( 'lang' => true, 'dir' => true ), 'br' => array() );
	$body   = wpautop( wp_kses( $html, $allow ) );

	return '<aside class="boi-abstract boi-abstract--' . esc_attr( $style ) . '" id="abstract" aria-labelledby="boi-abstract-title"' . $attr . '>'
		. '<p class="boi-abstract__title" id="boi-abstract-title">' . esc_html( $title ) . '</p>'
		. '<div class="boi-abstract__body">' . $body . '</div>'
		. '</aside>';
}

/**
 * Whether the box is shown on this view.
 *
 * @return bool
 */
function boi_abstract_on() {
	$opts = boi_get_options();
	return ! empty( $opts['abstract_enabled'] ) && is_singular( 'post' );
}

/**
 * The [abstract] shortcode: the box where it stands.
 *
 * @param array|string $atts    Attributes: title, style.
 * @param string       $content Text.
 * @return string
 */
function boi_abstract_shortcode( $atts, $content = '' ) {
	$atts = shortcode_atts( array( 'title' => '', 'style' => '' ), $atts, 'abstract' );
	if ( ! boi_abstract_on() ) {
		return '';
	}
	return boi_abstract_html( do_shortcode( (string) $content ), (string) $atts['title'], sanitize_key( $atts['style'] ) );
}
add_shortcode( 'abstract', 'boi_abstract_shortcode' );

/**
 * Renders the Abstract block.
 *
 * @param array $attributes Attributes: title, text, style.
 * @return string
 */
function boi_render_abstract_block( $attributes ) {
	if ( ! boi_abstract_on() ) {
		return '';
	}
	return boi_abstract_html(
		isset( $attributes['text'] ) ? (string) $attributes['text'] : '',
		isset( $attributes['title'] ) ? (string) $attributes['title'] : '',
		isset( $attributes['style'] ) ? sanitize_key( $attributes['style'] ) : ''
	);
}

/** Registers the Abstract block. */
function boi_register_abstract_block() {
	register_block_type( BOI_DIR . '/blocks/abstract', array( 'render_callback' => 'boi_render_abstract_block' ) );
}
add_action( 'init', 'boi_register_abstract_block' );

/**
 * Puts the bundled abstract at the top of the main text, unless the body
 * places its own.
 *
 * @param string $content Content.
 * @return string
 */
function boi_abstract_filter( $content ) {
	if ( ! boi_abstract_on() || ! function_exists( 'boi_anatomy_is_main' ) || ! boi_anatomy_is_main() || false !== strpos( $content, 'class="boi-abstract ' ) ) {
		return $content;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post || boi_abstract_in_body( $post ) ) {
		return $content;
	}
	$data = boi_abstract_data();
	if ( empty( $data[ $post->post_name ] ) ) {
		return $content;
	}
	return boi_abstract_html( wptexturize( esc_html( $data[ $post->post_name ] ) ) ) . $content;
}
add_filter( 'the_content', 'boi_abstract_filter', 30 );

/** The stylesheet, on single posts. */
function boi_abstract_assets() {
	if ( ! boi_abstract_on() ) {
		return;
	}
	wp_enqueue_style( 'boi-abstract', BOI_URI . '/assets/css/abstract.css', array( 'boi-article' ), boi_asset_version( '/assets/css/abstract.css' ) );
}
add_action( 'wp_enqueue_scripts', 'boi_abstract_assets', 20 );

/** The stylesheet in the block editor, so the block looks as it will. */
function boi_abstract_editor_assets() {
	wp_enqueue_style( 'boi-abstract-editor', BOI_URI . '/assets/css/abstract.css', array(), boi_asset_version( '/assets/css/abstract.css' ) );
}
add_action( 'enqueue_block_editor_assets', 'boi_abstract_editor_assets' );
