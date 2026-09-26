<?php
/**
 * Featured images.
 *
 * Every seeded article ships with an image drawn from Wikimedia Commons.
 * Each file was checked against its own Commons metadata and is in the
 * public domain or dedicated under CC0. None depicts a prophet: pieces about
 * Jesus, Abraham, Moses and Muhammad are illustrated with manuscripts, places
 * and objects. Credit is shown beneath each image regardless of licence.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * The image manifest, keyed by article slug.
 *
 * @return array
 */
function boi_image_manifest() {
	return array(
		'universe-demands-creator' => array(
			'file'    => 'universe-demands-creator.jpg',
			'alt'     => __( 'The Hubble Ultra Deep Field: thousands of distant galaxies in a small patch of sky', 'bestofislam' ),
			'artist'  => 'NASA and the European Space Agency',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Hubble_ultra_deep_field.jpg',
		),
		'manuscript-transmission' => array(
			'file'    => 'manuscript-transmission.jpg',
			'alt'     => __( 'Two leaves of the Birmingham Quran manuscript in early Hijazi script, dated to the first century of Islam', 'bestofislam' ),
			'artist'  => 'Cadbury Research Library, University of Birmingham',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Birmingham_Quran_manuscript.jpg',
		),
		'verses-read-differently' => array(
			'file'    => 'verses-read-differently.jpg',
			'alt'     => __( 'Papyrus 66, an early codex of the Gospel of John, around AD 200', 'bestofislam' ),
			'artist'  => 'Bodmer Lab, University of Geneva',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Geneva,_Bodmer_Lab_Ms_Papyrus_66_(P.Bodmer_II_-_PB_2)_fol._1r.jpg',
		),
		'islamic-dilemma' => array(
			'file'    => 'islamic-dilemma.jpg',
			'alt'     => __( 'Codex Sinaiticus open in its display case at the British Library', 'bestofislam' ),
			'artist'  => 'PotatoCow25',
			'licence' => 'CC0',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Codex_Sinaiticus.jpg',
		),
		'instruments-islamic-world' => array(
			'file'    => 'instruments-islamic-world.jpg',
			'alt'     => __( 'A brass planispheric astrolabe by Muhammad Zaman al-Munajjim al-Asturlabi', 'bestofislam' ),
			'artist'  => 'The Metropolitan Museum of Art; instrument by Muhammad Zaman al-Munajjim al-Asturlabi',
			'licence' => 'CC0',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Planispheric_Astrolabe_MET_DP105325.jpg',
		),
		'haman-anachronism' => array(
			'file'    => 'haman-anachronism.jpg',
			'alt'     => __( 'Cartouches of Ramesses II carved at Karnak', 'bestofislam' ),
			'artist'  => 'Lord-of-the-Light and JMCC1',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Karnak_titul_ramses_2c.jpg',
		),
		'five-pillars-explained' => array(
			'file'    => 'five-pillars-explained.jpg',
			'alt'     => __( 'The Kaaba in the Sacred Mosque at Mecca, 19th-century photograph', 'bestofislam' ),
			'artist'  => 'Khalili Collections',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Khalili_Collection_Hajj_and_Arts_of_Pilgrimage_arc.pp_0211.04_CROP.jpg',
		),
		'who-is-allah' => array(
			'file'    => 'who-is-allah.jpg',
			'alt'     => __( 'A page of Ottoman calligraphy by Sheikh Hamdullah', 'bestofislam' ),
			'artist'  => 'Sheikh Hamdullah',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Seyh_Hamdullah_-_Page_of_Ottoman_Calligraphy_-_Walters_W6725A_-_Full_Page.jpg',
		),
		'morality-without-god' => array(
			'file'    => 'morality-without-god.jpg',
			'alt'     => __( 'The School of Athens by Raphael, with Plato and Aristotle at its centre', 'bestofislam' ),
			'artist'  => 'Raphael',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:%22The_School_of_Athens%22_by_Raffaello_Sanzio_da_Urbino.jpg',
		),
		'jesus-not-god' => array(
			'file'    => 'jesus-not-god.jpg',
			'alt'     => __( 'The Garden of Gethsemane and a view of Jerusalem, 19th-century photograph', 'bestofislam' ),
			'artist'  => 'John Anthony, via The Metropolitan Museum of Art',
			'licence' => 'CC0',
			'source'  => 'https://commons.wikimedia.org/wiki/File:-Garden_of_Gethsemane_and_View_of_Jerusalem-_MET_DP111356.jpg',
		),
		'shared-prophets' => array(
			'file'    => 'shared-prophets.jpg',
			'alt'     => __( 'The summit of Jebel Musa, Mount Sinai', 'bestofislam' ),
			'artist'  => 'Matson Collection',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Sinai._The_Summit_of_Jebel_Mousa._LOC_matpc.07263.jpg',
		),
		'prophet-died-poisoning' => array(
			'file'    => 'prophet-died-poisoning.jpg',
			'alt'     => __( 'Harrat Khaybar volcanic field seen from the International Space Station', 'bestofislam' ),
			'artist'  => 'Expedition 16 Crew Member on the International Space Station, NASA',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Harrat_Khaybar_Space.jpg',
		),
		'al-zutt-allegation' => array(
			'file'    => 'al-zutt-allegation.jpg',
			'alt'     => __( 'A manuscript of Sahih al-Bukhari', 'bestofislam' ),
			'artist'  => 'Unknown photographer, Mohammed V University library',
			'licence' => 'CC0',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Manuscript_of_Sahih_Bukhari.jpg',
		),
		'islam-religion-of-peace' => array(
			'file'    => 'islam-religion-of-peace.jpg',
			'alt'     => __( 'The interior domes of the Sultan Ahmed Mosque, Istanbul', 'bestofislam' ),
			'artist'  => 'Julian Lupyan',
			'licence' => 'CC0',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Sultan_Ahmed_Mosque_Interior,_2024.jpg',
		),
		'seven-libraries' => array(
			'file'    => 'seven-libraries.jpg',
			'alt'     => __( 'A scholar reading in the library of al-Qarawiyyin, Fez', 'bestofislam' ),
			'artist'  => 'Istiqlal (Independence) Party of Morocco, Moroccan Office of Information and Documentation',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Scholar_at_the_University_of_al-Qarawiyyin%27s_library.png',
		),
		'same-god' => array(
			'file'    => 'same-god.jpg',
			'alt'     => __( 'Floral illumination opening the Gospel of John in an Arabic Gospel book, 1684', 'bestofislam' ),
			'artist'  => 'Ilyas Basim Khuri Bazzi Rahib',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Ilyas_Basim_Khuri_Bazzi_Rahib_-_Full-page_Floral_Composition_Marking_the_Beginning_of_the_Gospel_of_John_(Yuhanna)_-_Walters_W592210A_-_Full_Page.jpg',
		),
		'where-is-the-injil' => array(
			'file'    => 'where-is-the-injil.jpg',
			'alt'     => __( 'A page of the Gospel of Mark in Codex Alexandrinus, 5th century', 'bestofislam' ),
			'artist'  => 'British Library, Codex Alexandrinus',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Codex_Alexandrinus_013a_Mc_6,27-54.JPG',
		),
		'paul-and-the-law' => array(
			'file'    => 'paul-and-the-law.jpg',
			'alt'     => __( 'Papyrus 46, the end of Ephesians and the opening of Galatians, around AD 200', 'bestofislam' ),
			'artist'  => 'University of Michigan Library, Papyrus 46',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Ann_Arbor,_University_of_Michigan_P.Mich.inv._6238_(Papyrus_46)_fol._158r_Eph_6,_20%E2%80%93Gal_1,_8.jpg',
		),
		'ezra-and-mary' => array(
			'file'    => 'ezra-and-mary.jpg',
			'alt'     => __( 'A page of the Leningrad Codex, the oldest complete Hebrew Bible, 1008', 'bestofislam' ),
			'artist'  => 'National Library of Russia, Leningrad Codex',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Leningrad_Codex_Folio_006a.jpg',
		),
		'uthman-copies' => array(
			'file'    => 'uthman-copies.jpg',
			'alt'     => __( 'The first leaf of the Codex Parisino-Petropolitanus, first Islamic century', 'bestofislam' ),
			'artist'  => 'Bibliothèque nationale de France, Arabe 328',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Codex_Parisino-petropolitanus,_first_leaf_recto.jpg',
		),
		'qibla-change' => array(
			'file'    => 'qibla-change.jpg',
			'alt'     => __( 'The Dome of the Rock on the Temple Mount, Jerusalem, 19th-century photograph', 'bestofislam' ),
			'artist'  => 'Library of Congress, Prints and Photographs Division',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:The_Mosque_of_Omar;_Es-Sakra,_Jerusalem_LCCN2004672198.jpg',
		),
		'why-five-prayers' => array(
			'file'    => 'why-five-prayers.jpg',
			'alt'     => __( 'Congregational prayer in the Sacred Mosque at Mecca, 1880s photograph', 'bestofislam' ),
			'artist'  => 'al-Sayyid Abd al-Ghaffar, physician of Mecca, via Library of Congress',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Ansicht_de_Moschee,_w%C3%A4hrend_darin_ein_gemeinschaftliches_%C3%87al%C3%A4t_abgehalten_wird_LCCN2013646214.jpg',
		),
		'who-saw-gabriel' => array(
			'file'    => 'who-saw-gabriel.jpg',
			'alt'     => __( 'Jabal al-Nour near Mecca, the mountain of the cave of Hira', 'bestofislam' ),
			'artist'  => 'Adiput',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Jabal_Nur.JPG',
		),
		'fastest-growing-religion' => array(
			'file'    => 'fastest-growing-religion.jpg',
			'alt'     => __( 'Eid al-Fitr prayer congregation at the South Plaza of the National Parliament, Dhaka, 2016', 'bestofislam' ),
			'artist'  => 'Press Information Department, Government of Bangladesh',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Eid_al-Fitr_prayer_congregation,_National_Parliament_South_Plaza,_Bangladesh,_2016-07-07.jpg',
		),
		'arabic-words-science' => array(
			'file'    => 'arabic-words-science.jpg',
			'alt'     => __( 'A page from the oldest surviving copy of al-Khwarizmi\'s treatise on algebra, Bodleian Library', 'bestofislam' ),
			'artist'  => 'Bodleian Libraries, University of Oxford, MS. Huntington 214',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Bodleian_MS._Huntington_214_roll332_frame36.jpg',
		),
		'second-peter' => array(
			'file'    => 'second-peter.jpg',
			'alt'     => __( 'Papyrus 72, the leaf where the First Letter of Peter ends and the Second begins', 'bestofislam' ),
			'artist'  => 'Vatican Apostolic Library, Papyrus Bodmer VIII',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Vatican,_Apostolic_Library_Ms_Papyrus_Bodmer_VIII_2_(Papyrus_72)_Ending_of_1_Peter_-_Beginning_of_2_Peter.jpg',
		),
		'moon-craters' => array(
			'file'    => 'moon-craters.jpg',
			'alt'     => __( 'The near side of the Moon, mosaic from NASA\'s Lunar Reconnaissance Orbiter', 'bestofislam' ),
			'artist'  => 'NASA, Goddard Space Flight Center and Arizona State University',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:LRO_WAC_Nearside_Mosaic.jpg',
		),
		'indonesia' => array(
			'file'    => 'indonesia.jpg',
			'alt'     => __( 'A mosque in the Minangkabau highlands of Sumatra, 19th-century photograph', 'bestofislam' ),
			'artist'  => 'Rijksmuseum, Amsterdam',
			'licence' => 'CC0',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Moskee_in_de_Padangse_Bovenlanden_op_Sumatra,_RP-F-F01149-DF.jpg',
		),
		'ex-muslim-blog-study' => array(
			'file'    => 'ex-muslim-blog-study.jpg',
			'alt'     => __( 'A mosque at Kuala Lumpur, around 1900, photographed by G. R. Lambert and Co.', 'bestofislam' ),
			'artist'  => 'G. R. Lambert and Co., via KITLV, Leiden',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:KITLV_-_105897_-_Lambert_%26_Co.,_G.R._-_Singapore_-_Mosque_at_Kuala_Lumpur_-_circa_1900_-_small.jpg',
		),
		'kaaba-witnesses' => array(
			'file'    => 'kaaba-witnesses.jpg',
			'alt'     => __( 'A 1482 printing of Ptolemy\'s map of the Arabian peninsula, Ulm edition', 'bestofislam' ),
			'artist'  => 'Claudius Ptolemy, Ulm edition of 1482',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:1482_Ptolemaic_map_of_the_Arabian_peninsula_and_the_Persian_Gulf.jpg',
		),
		'dome-inscriptions' => array(
			'file'    => 'dome-inscriptions.jpg',
			'alt'     => __( 'The interior of the Dome of the Rock, 19th-century photograph', 'bestofislam' ),
			'artist'  => 'From the collection Holy Land Photographed, Daniel B. Shepp',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Jerusalem,_Mosque_of_Omar_(interior_view,_Dom_of_Rock)._014.Holy_land_photographed._Daniel_B._Shepp._1894.jpg',
		),
		'library-of-alexandria' => array(
			'file'    => 'library-of-alexandria.jpg',
			'alt'     => __( 'An artist\'s impression of the ancient Library of Alexandria, 19th-century engraving', 'bestofislam' ),
			'artist'  => 'O. Von Corven, 19th century',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Ancientlibraryalex.jpg',
		),
		'jizya' => array(
			'file'    => 'jizya.jpg',
			'alt'     => __( 'A manuscript of Abu Yusuf\'s treatise on taxation, copied in 961 AH', 'bestofislam' ),
			'artist'  => 'Abu Yusuf, manuscript copied in 961 AH',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:961_AH_manuscripts_of_Kitab_al-Kharaj.jpg',
		),
		'quran-preservation-reddit' => array(
			'file'    => 'quran-preservation-reddit.jpg',
			'alt'     => __( 'A leaf of the Sanaa palimpsest, an early Quran manuscript', 'bestofislam' ),
			'artist'  => 'Sanaa manuscript, photographed for Stanford University',
			'licence' => 'Public domain',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Sana%27a1_Stanford_%2707_recto.jpg',
		),
		'apostasy-in-islam' => array(
			'file'    => 'apostasy-in-islam.jpg',
			'alt'     => __( 'Illuminated opening pages of a Quran by the calligrapher Khayr al-Din al-Marashi', 'bestofislam' ),
			'artist'  => 'Khayr al-Din al-Marashi',
			'licence' => 'CC0',
			'source'  => 'https://commons.wikimedia.org/wiki/File:Opening_pages_from_the_Qur%27an_by_Khayr_al-Din_al-Mar%E2%80%98ashi.jpg',
		),
	);
}

/**
 * Sideloads bundled images into the media library and sets each as the
 * featured image of its article. Idempotent: an article that already has a
 * featured image, whether ours or one the owner chose, is left alone.
 *
 * Works in small batches. WordPress generates several resized copies of each
 * image, so loading all of them in one request can outrun a host's time
 * limit. Each call attaches at most $limit images and records its progress
 * after every one, and the theme keeps calling it on later requests until
 * nothing is left to attach.
 *
 * @param int $limit Most images to attach in this call.
 * @return int Number of images attached.
 */
function boi_attach_featured_images( $limit = 3 ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$attached = 0;
	$map      = (array) get_option( 'boi_image_attachments', array() );

	$pending = false;

	foreach ( boi_image_manifest() as $slug => $image ) {
		$post = get_page_by_path( $slug, OBJECT, 'post' );

		if ( ! $post || has_post_thumbnail( $post ) ) {
			continue;
		}

		if ( $attached >= $limit ) {
			$pending = true;
			break;
		}

		// Reuse an attachment created on an earlier run.
		if ( ! empty( $map[ $slug ] ) && get_post( (int) $map[ $slug ] ) ) {
			set_post_thumbnail( $post, (int) $map[ $slug ] );
			++$attached;
			continue;
		}

		$source = BOI_DIR . '/assets/images/featured/' . $image['file'];

		if ( ! file_exists( $source ) ) {
			continue;
		}

		$tmp = wp_tempnam( $image['file'] );

		if ( ! $tmp || ! copy( $source, $tmp ) ) {
			continue;
		}

		$id = media_handle_sideload(
			array(
				'name'     => $image['file'],
				'tmp_name' => $tmp,
			),
			$post->ID,
			$image['alt']
		);

		if ( is_wp_error( $id ) ) {
			wp_delete_file( $tmp );
			continue;
		}

		update_post_meta( $id, '_wp_attachment_image_alt', $image['alt'] );
		update_post_meta( $id, '_boi_credit', array( 'artist' => $image['artist'], 'licence' => $image['licence'], 'source' => $image['source'] ) );
		wp_update_post(
			array(
				'ID'           => $id,
				'post_excerpt' => boi_credit_text( $image ),
			)
		);

		set_post_thumbnail( $post, $id );
		$map[ $slug ] = $id;
		++$attached;
		update_option( 'boi_image_attachments', $map, false );
	}

	update_option( 'boi_images_pending', $pending ? 1 : 0, false );

	return $attached;
}

/**
 * Continues attaching images on later requests while any remain, one small
 * batch per request, then stops checking.
 *
 * @return void
 */
function boi_continue_featured_images() {
	if ( ! get_option( 'boi_images_pending' ) || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}

	// One request at a time: a short lock keeps concurrent visitors from
	// sideloading the same image twice.
	if ( get_transient( 'boi_images_lock' ) ) {
		return;
	}

	set_transient( 'boi_images_lock', 1, MINUTE_IN_SECONDS );
	boi_attach_featured_images();
	delete_transient( 'boi_images_lock' );
}
add_action( 'init', 'boi_continue_featured_images', 30 );

/**
 * Plain-text credit line.
 *
 * @param array $image Manifest entry.
 * @return string
 */
function boi_credit_text( $image ) {
	return sprintf(
		/* translators: 1: artist, 2: licence. */
		__( 'Image: %1$s. %2$s, via Wikimedia Commons.', 'bestofislam' ),
		$image['artist'],
		$image['licence']
	);
}

/**
 * Registers the credit blocks.
 *
 * @return void
 */
function boi_register_image_blocks() {
	register_block_type( 'bestofislam/image-credit', array( 'render_callback' => 'boi_render_image_credit' ) );
	register_block_type( 'bestofislam/image-credits', array( 'render_callback' => 'boi_render_image_credits' ) );
}
add_action( 'init', 'boi_register_image_blocks' );

/**
 * Credit beneath the featured image on a single post.
 *
 * @return string
 */
function boi_render_image_credit() {
	$id = get_post_thumbnail_id();

	if ( ! $id ) {
		$bundled = boi_bundled_image( get_post() );

		if ( ! $bundled ) {
			return '';
		}

		return sprintf(
			'<p class="boi-credit">%1$s <a href="%2$s" rel="noopener">%3$s</a></p>',
			esc_html( sprintf( /* translators: 1: artist, 2: licence. */ __( 'Image: %1$s. %2$s,', 'bestofislam' ), $bundled['artist'], $bundled['licence'] ) ),
			esc_url( $bundled['source'] ),
			esc_html__( 'Wikimedia Commons', 'bestofislam' )
		);
	}

	$credit = get_post_meta( $id, '_boi_credit', true );

	if ( ! is_array( $credit ) || empty( $credit['source'] ) ) {
		$caption = wp_get_attachment_caption( $id );
		return '' !== (string) $caption ? '<p class="boi-credit">' . esc_html( $caption ) . '</p>' : '';
	}

	return sprintf(
		'<p class="boi-credit">%1$s <a href="%2$s" rel="noopener">%3$s</a></p>',
		esc_html( sprintf( /* translators: 1: artist, 2: licence. */ __( 'Image: %1$s. %2$s,', 'bestofislam' ), $credit['artist'], $credit['licence'] ) ),
		esc_url( $credit['source'] ),
		esc_html__( 'Wikimedia Commons', 'bestofislam' )
	);
}

/**
 * Full credits list, for the Sources and standards page.
 *
 * @return string
 */
function boi_render_image_credits() {
	$items = '';

	foreach ( boi_image_manifest() as $slug => $image ) {
		$post = get_page_by_path( $slug, OBJECT, 'post' );

		$items .= sprintf(
			'<li><strong>%1$s</strong>: %2$s. %3$s. <a href="%4$s" rel="noopener">%5$s</a></li>',
			esc_html( $post ? get_the_title( $post ) : $slug ),
			esc_html( $image['artist'] ),
			esc_html( $image['licence'] ),
			esc_url( $image['source'] ),
			esc_html__( 'Source', 'bestofislam' )
		);
	}

	return '<ul class="boi-credits">' . $items . '</ul>';
}

/**
 * The image bundled with the theme for a seeded article, whether or not it
 * has been copied into the media library yet.
 *
 * Every seeded article ships with its image in assets/images/featured/. The
 * media library copy is made in the background and can lag, or fail on hosts
 * that restrict uploads. Wherever an attached image is missing, this bundled
 * file stands in, so no seeded article is ever shown without its picture.
 *
 * @param int|WP_Post $post Post or identifier.
 * @return array|null Manifest entry plus url, width and height, or null.
 */
function boi_bundled_image( $post ) {
	static $cache = array();

	$post = get_post( $post );

	if ( ! $post ) {
		return null;
	}

	if ( array_key_exists( $post->ID, $cache ) ) {
		return $cache[ $post->ID ];
	}

	$manifest = boi_image_manifest();
	$entry    = isset( $manifest[ $post->post_name ] ) ? $manifest[ $post->post_name ] : null;
	$file     = $entry ? BOI_DIR . '/assets/images/featured/' . $entry['file'] : '';

	if ( ! $entry || ! file_exists( $file ) ) {
		$cache[ $post->ID ] = null;
		return null;
	}

	$size = function_exists( 'wp_getimagesize' ) ? wp_getimagesize( $file ) : getimagesize( $file );

	$entry['url']    = BOI_URI . '/assets/images/featured/' . $entry['file'];
	$entry['width']  = $size ? (int) $size[0] : 0;
	$entry['height'] = $size ? (int) $size[1] : 0;

	$cache[ $post->ID ] = $entry;

	return $entry;
}

/**
 * The bundled image as an img element, carrying the class and inline style
 * the requesting block passed, so it sits in the block's frame exactly as an
 * attached image would.
 *
 * @param array $image Entry from boi_bundled_image().
 * @param array $attr  Attributes requested by the caller.
 * @return string
 */
function boi_bundled_image_html( $image, $attr = array() ) {
	$attr  = is_array( $attr ) ? $attr : wp_parse_args( $attr );
	$class = 'wp-post-image boi-bundled-image' . ( ! empty( $attr['class'] ) ? ' ' . $attr['class'] : '' );

	return sprintf(
		'<img src="%1$s" alt="%2$s" class="%3$s"%4$s%5$s loading="lazy" decoding="async"%6$s />',
		esc_url( $image['url'] ),
		esc_attr( $image['alt'] ),
		esc_attr( $class ),
		$image['width'] ? ' width="' . (int) $image['width'] . '"' : '',
		$image['height'] ? ' height="' . (int) $image['height'] . '"' : '',
		! empty( $attr['style'] ) ? ' style="' . esc_attr( $attr['style'] ) . '"' : ''
	);
}

/**
 * Counts seeded articles whose image is attached in the media library, for
 * the Content tab.
 *
 * @return array Array( attached, total ).
 */
function boi_image_status() {
	$total    = 0;
	$attached = 0;

	foreach ( array_keys( boi_image_manifest() ) as $slug ) {
		$post = get_page_by_path( $slug, OBJECT, 'post' );

		if ( ! $post ) {
			continue;
		}

		++$total;

		if ( has_post_thumbnail( $post ) ) {
			++$attached;
		}
	}

	return array( $attached, $total );
}
