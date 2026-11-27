<?php
/**
 * Reading order.
 *
 * The site's articles, arranged as one path in seven parts: what Islam
 * teaches, whether belief is reasonable, the Bible and the Quran, the
 * transmission of scripture, objections about the Prophet, history, and the
 * Muslim world now. The order drives four things: the "Read next" card at the
 * foot of each article, the Start here path, the hero on the front page, and
 * the publish dates the seed assigns, so that the archive reads in sequence.
 *
 * An article missing from the order simply has no "Read next" card.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * The reading order, by part.
 *
 * @return array
 */
function boi_reading_order() {
	return array(
		array(
			'label' => __( 'What Islam teaches', 'bestofislam' ),
			'slugs' => array( 'who-is-allah', 'is-allah-a-deceiver-makr', 'what-the-pagans-of-mecca-said-about-allah', 'gods-attributes-and-anthropomorphism', 'uncreated-quran-and-the-logos', 'five-pillars-explained', 'before-you-convert-to-islam', 'do-muslims-worship-muhammad', 'did-the-quran-say-muhammad-sinned', 'temporary-marriage-mutah', 'why-five-prayers', 'authority-of-the-sunnah-quran-alone', 'six-articles-of-faith', 'fate-and-free-will', 'sadness-and-depression-in-islam', 'end-of-time', 'prophets-you-may-not-know', 'quranic-ethics', 'before-your-first-friday-prayer', 'hajj-rites-explained', 'ramadan-last-ten-nights', 'funeral-dhikr-dua', 'relationship-with-god', 'black-stone-and-kissing' ),
		),
		array(
			'label' => __( 'Whether belief is reasonable', 'bestofislam' ),
			'slugs' => array( 'universe-demands-creator', 'from-atheism-to-islam', 'why-does-god-allow-suffering', 'morality-without-god', 'who-saw-gabriel', 'quran-science-claims-graded', 'islam-and-evolution', 'sun-muddy-spring-and-fixed-earth', 'natural-world-verses' ),
		),
		array(
			'label' => __( 'The Bible and the Quran', 'bestofislam' ),
			'slugs' => array( 'same-god', 'deuteronomy-prophecy-and-the-paraclete', 'shared-prophets', 'adam-and-eve-in-genesis-and-the-quran', 'slander-against-mary', 'birth-of-jesus-in-surah-maryam', 'christmas-quran-jesus-birth', 'nabi-and-rasul', 'messenger-to-every-nation', 'muhammad-and-the-hebrew-prophets', 'ishmael-in-the-quran-and-genesis', 'scrolls-of-abraham', 'jesus-not-god', 'where-did-jesus-say-he-is-god', 'did-jesus-pray-to-whom', 'jesus-prostrated-in-prayer', 'jesus-prayed-at-night-and-before-dawn', 'did-jesus-fast', 'jesus-and-the-sabbath', 'religious-practices-of-jesus-muslims-recognise', 'jesus-the-word-of-god-in-the-quran', 'does-the-virgin-birth-make-jesus-divine', 'jesus-sign-of-the-hour', 'sons-of-god-in-the-bible', 'crucifixion-quran', 'is-the-cross-a-pagan-symbol', 'can-god-die', 'first-and-the-last', 'islam-arian-heresy-claim', 'ransom-bible-quran', 'original-sin', 'are-children-born-sinful', 'does-god-require-blood-to-forgive', 'islamic-repentance-and-christian-atonement', 'verses-read-differently', 'genealogies-of-jesus', 'was-jesus-born-on-christmas-day', 'holy-war-in-the-bible', 'saul-of-tarsus-before-conversion', 'paul-damascus-road-contradictions', 'paul-and-the-law', 'paul-and-circumcision', 'paul-and-food-offered-to-idols', 'paul-claim-to-apostleship', 'did-paul-receive-his-gospel-from-jesus', 'paul-peter-antioch-confrontation', 'paul-james-faith-and-works', 'pauline-letters-authorship', 'pauline-teachings-that-shaped-christian-theology', 'epimenides-paradox-in-titus', 'where-is-the-injil', 'islamic-dilemma', 'does-islam-teach-the-bible-was-corrupted', 'christian-twin-of-the-islamic-dilemma', 'nine-questions-from-the-floor-islamic-dilemma-debate', 'revelatory-exegesis', 'seal-of-the-prophets', 'quran-verses-on-torah-and-gospel', 'covenant-of-the-prophets', 'jeremiah-new-covenant-hebrews-and-the-quran', 'ask-those-who-read-the-scripture', 'camel-method', 'quran-passages-in-christian-missions', 'torah-on-the-cushion-hadith', 'early-christians-charged-others-with-corrupting-scripture', 'quran-word-kitab-meanings', 'trinity-in-the-hebrew-bible-tested', 'who-is-the-holy-spirit', 'matthew-fulfilment-quotations-checked', 'ezekiel-twenty-three-allegory', 'ezekiel-twenty-three-in-malay-and-indonesian-bibles', 'ezra-and-mary', 'haman-anachronism', 'quranic-stories-and-history', 'quran-mysteries-in-the-stories', 'buraq-and-miracle-stories', 'apes-and-pigs-and-the-jews', 'jews-behind-the-stone-hadith', 'quran-on-christians', 'can-a-muslim-befriend-a-non-muslim', 'can-muslims-say-merry-christmas', 'quran-anachronism-claims', 'talmud-midrash-apocrypha-borrowing', 'quran-iblis-fallen-angels-borrowing', 'iblis-and-the-command-to-prostrate', 'is-yahweh-satan', 'who-was-yahweh-archaeological-origins', 'was-yahweh-one-of-many-gods', 'early-christians-yahweh-evil-gnosticism-marcion', 'is-yahweh-the-same-as-allah', 'biblical-anthropomorphism' ),
		),
		array(
			'label' => __( 'How scripture was transmitted', 'bestofislam' ),
			'slugs' => array( 'manuscript-transmission', 'septuagint', 'old-testament-variant-readings', 'kings-and-chronicles-numbers', 'oldest-bible-codices', 'comma-johanneum', 'bible-alteration-claim-before-islam', 'who-changed-the-bible', 'second-peter', 'jamnia-and-the-hebrew-canon', 'earliest-new-testament-lists', 'disputed-books-of-the-new-testament', 'revelation-warning-and-the-canon', 'uthman-copies', 'muhammad-never-wrote-it-down', 'seven-ahruf-cairo-edition-stoning-verse', 'hafs-warsh-one-quran', 'aisha-sheep-report-stoning-and-suckling-verses', 'quran-preservation-reddit', 'who-wrote-the-quran', 'quran-grammar-errors-claim', 'quran-challenge-met-claim', 'quran-pronoun-shifts-iltifat', 'oaths-in-the-quran', 'preservation-and-truth', 'quran-contradictions-claims', 'revisionist-case-weighed', 'sira-ibn-ishaq-hisham-tabari', 'schacht-isnads-dating-hadith' ),
		),
		array(
			'label' => __( 'The Prophet and the hard questions', 'bestofislam' ),
			'slugs' => array( 'prophet-died-poisoning', 'al-zutt-allegation', 'muhammad-false-prophet-claim', 'did-muhammad-perform-miracles', 'was-muhammad-a-narcissist', 'questions-about-aisha', 'women-in-the-prophets-life', 'companions-who-started-with-nothing', 'does-the-quran-allow-wife-beating', 'a-father-says-this-is-islam', 'honour-killing-and-islamic-law', 'women-in-hell-and-deficient-intelligence-hadith', 'three-divorces-and-the-one-night-marriage', 'women-in-islamic-law-testimony-inheritance', 'women-leading-prayer', 'child-marriage-in-islam', 'before-you-convert-women-and-marriage', 'slavery-in-islam', 'houris-and-paradise', 'will-every-non-muslim-go-to-hell', 'religious-pluralism-and-islam', 'camel-urine-hadith', 'fly-hadith-and-adult-suckling', 'first-revelation-demonic-claim', 'satanic-verses-report', 'prophets-marriages-zaynab-and-others', 'safiyya-and-the-women-captured-in-war', 'magic-jinn-and-satan-hadiths', 'islamic-rites-before-islam', 'abrogation-and-the-sword-verse', 'end-of-the-world-prediction', 'was-muhammad-illiterate-ummi', 'hanif-poets-and-musaylima', 'first-muslims-kept-the-faith', 'i-read-the-entire-quran-reply' ),
		),
		array(
			'label' => __( 'History and civilisation', 'bestofislam' ),
			'slugs' => array( 'raids-badr-and-the-conquest-of-mecca', 'medina-jewish-tribes-and-qurayza', 'kaab-ibn-al-ashraf-and-the-poets', 'qibla-change', 'kaaba-witnesses', 'dome-inscriptions', 'jizya', 'library-of-alexandria', 'seven-libraries', 'instruments-islamic-world', 'arabic-words-science', 'moon-craters', 'crusades-facts', 'kharijites-and-takfir', 'scholars-who-shaped-islamic-study', 'western-scholars-of-islam', 'islamic-art-and-the-one-god', 'mosques-adapt-to-place', 'christmas-traditions-historical-origins', 'maria-hertogh-natrah-case' ),
		),
		array(
			'label' => __( 'The Muslim world today', 'bestofislam' ),
			'slugs' => array( 'muslims-by-the-numbers', 'islam-religion-of-peace', 'apostasy-in-islam', 'before-you-convert-apostates-and-lgbt-friends', 'fastest-growing-religion', 'former-christian-ministers-who-embraced-islam', 'why-people-convert-to-islam', 'indonesian-conversion-stories', 'indonesia', 'ex-muslim-blog-study', 'what-former-muslims-testimonies-show', 'islam-in-china', 'islamophobia-facts', 'opposition-to-islam-to-conversion', 'when-a-stranger-online-calls-islam-a-cult', 'islamisation-of-the-west', 'muslim-societies-backwardness-claim', 'taqiyya-myths', 'war-and-killing-in-islam', 'hijab-and-modesty', 'homosexuality-in-islam', 'sharia-and-hudud', 'blasphemy-and-insults-to-the-prophet', 'why-no-interest', 'terrorism-and-muslims-the-data', 'race-in-quran-and-hadith', 'fighting-verses-in-context', 'no-compulsion-in-religion', 'principles-of-dawah-in-the-quran', 'jihad-meaning-quran-hadith-jurists', 'ridda-wars-abu-bakr-fadak' ),
		),
	);
}

/**
 * The order flattened: slug => array( position, part index, part label ).
 *
 * @return array
 */
function boi_reading_positions() {
	static $map = null;

	if ( null !== $map ) {
		return $map;
	}

	$map = array();
	$i   = 0;

	foreach ( boi_reading_order() as $p => $part ) {
		foreach ( $part['slugs'] as $slug ) {
			$map[ $slug ] = array( $i++, $p, $part['label'] );
		}
	}

	return $map;
}

/**
 * The published article that follows a slug in the reading order, skipping
 * any that are not published.
 *
 * @param string $slug Current article slug.
 * @return array|null Array( post, part label, part number ) or null.
 */
function boi_next_in_order( $slug ) {
	$map = boi_reading_positions();

	if ( ! isset( $map[ $slug ] ) ) {
		return null;
	}

	$slugs = array_keys( $map );

	for ( $i = $map[ $slug ][0] + 1; $i < count( $slugs ); $i++ ) {
		$post = get_page_by_path( $slugs[ $i ], OBJECT, 'post' );

		if ( $post && 'publish' === $post->post_status && ! boi_is_unlisted( $post ) ) {
			return array( $post, $map[ $slugs[ $i ] ][2], $map[ $slugs[ $i ] ][1] + 1 );
		}
	}

	return null;
}

/**
 * Registers the Read next block.
 *
 * @return void
 */
function boi_register_reading_blocks() {
	register_block_type( 'bestofislam/read-next', array( 'render_callback' => 'boi_render_read_next' ) );
}
add_action( 'init', 'boi_register_reading_blocks' );

/**
 * The Read next card at the foot of an article. At the end of the order it
 * points the reader to the questions answered on the front page instead.
 *
 * @return string
 */
function boi_render_read_next() {
	$post = get_post();

	if ( ! $post || ! isset( boi_reading_positions()[ $post->post_name ] ) ) {
		return '';
	}

	$next = boi_next_in_order( $post->post_name );

	if ( ! $next ) {
		return sprintf(
			'<nav class="boi-next is-end" aria-label="%1$s"><p class="boi-next__label">%2$s</p><a class="boi-next__title" href="%3$s">%4$s</a></nav>',
			esc_attr__( 'Reading order', 'bestofislam' ),
			esc_html__( 'You have reached the end of the reading order', 'bestofislam' ),
			esc_url( function_exists( 'boi_questions_url' ) ? boi_questions_url() : home_url( '/#objections' ) ),
			esc_html__( 'See every question answered', 'bestofislam' )
		);
	}

	list( $next_post, $part_label, $part_number ) = $next;

	return sprintf(
		'<nav class="boi-next" aria-label="%1$s"><p class="boi-next__label">%2$s</p><a class="boi-next__title" href="%3$s">%4$s</a><p class="boi-next__part">%5$s</p></nav>',
		esc_attr__( 'Reading order', 'bestofislam' ),
		esc_html__( 'Read next', 'bestofislam' ),
		esc_url( get_permalink( $next_post ) ),
		esc_html( get_the_title( $next_post ) ),
		esc_html( sprintf( /* translators: 1: part number, 2: part name. */ __( 'Part %1$d: %2$s', 'bestofislam' ), $part_number, $part_label ) )
	);
}

/**
 * The articles added since 1.125.0 that an earlier version dated by their
 * place in the reading order, months back. On a site upgraded from such a
 * version they are moved to today, once, so they lead the front page.
 *
 * @return string[]
 */
function boi_dates_recent_slugs() {
	return array( 'before-you-convert-to-islam', 'before-you-convert-women-and-marriage', 'before-you-convert-apostates-and-lgbt-friends', 'scrolls-of-abraham', 'is-the-cross-a-pagan-symbol' );
}

/**
 * A seeded article the theme may redate: published, and with content still
 * exactly what the theme wrote, so anything the owner has edited keeps its
 * date.
 *
 * @param string $slug   Article slug.
 * @param array  $hashes Stored fingerprints, by slug.
 * @return WP_Post|null
 */
function boi_dates_article( $slug, $hashes ) {
	$post = get_page_by_path( $slug, OBJECT, 'post' );
	if ( ! $post || 'publish' !== $post->post_status ) {
		return null;
	}
	// The content sync's own record (inc/content-sync.php), which a refresh
	// keeps current; the older option record where the sync is absent.
	if ( function_exists( 'boi_sync_state' ) ) {
		$state = boi_sync_state( $post, $slug );
		return '' !== $state['recorded'] && $state['stored'] === $state['recorded'] ? $post : null;
	}
	if ( ! isset( $hashes[ $slug ] ) || boi_content_fingerprint( $post->post_content ) !== $hashes[ $slug ] ) {
		return null;
	}
	return $post;
}

/**
 * Sets an article's publish date.
 *
 * @param int $id        Post ID.
 * @param int $timestamp Unix time.
 * @return void
 */
function boi_dates_set( $id, $timestamp ) {
	$gmt = gmdate( 'Y-m-d H:i:s', $timestamp );
	boi_update_post(
		array(
			'ID'            => $id,
			'post_date'     => get_date_from_gmt( $gmt ),
			'post_date_gmt' => $gmt,
		)
	);
}

/**
 * Dates the seeded articles once, then leaves them alone.
 *
 * On a new site every article arrives at once, so their dates follow the
 * reading order, one day apart, ending now: the archive, the featured lead
 * and the Latest grid then read in that order. The dates are then frozen
 * (`boi_dates_frozen`): an article added by a later release keeps the date
 * it was created, so the newest article leads the front page.
 *
 * A site whose dates an earlier version staggered (`boi_dates_signature`,
 * redone on every change to the reading order, which put each new article
 * months back) keeps the dates it has, except the articles in
 * boi_dates_recent_slugs(), which move to today in the order they were
 * created. Only articles still exactly as the theme wrote them are redated.
 *
 * @return void
 */
function boi_stagger_seed_dates() {
	if ( false !== get_option( 'boi_dates_frozen' ) ) {
		return;
	}

	$hashes = (array) get_option( 'boi_article_hashes', array() );
	$slugs  = array_keys( boi_reading_positions() );
	$now    = time();

	if ( false === get_option( 'boi_dates_signature' ) ) {
		// A scheduled article keeps its own date (SSOT D71).
		$scheduled = function_exists( 'boi_seed_schedule' ) ? boi_seed_schedule() : array();
		$slugs     = array_values( array_diff( $slugs, array_keys( $scheduled ) ) );
		$total     = count( $slugs );
		foreach ( $slugs as $i => $slug ) {
			$post = boi_dates_article( $slug, $hashes );
			if ( $post ) {
				boi_dates_set( $post->ID, $now - ( $total - 1 - $i ) * DAY_IN_SECONDS );
			}
		}
	} else {
		$recent = array();
		foreach ( boi_dates_recent_slugs() as $slug ) {
			$post = boi_dates_article( $slug, $hashes );
			if ( $post ) {
				$recent[] = $post;
			}
		}
		// Oldest first, by ID, a minute apart, the newest ending now.
		usort(
			$recent,
			function ( $a, $b ) {
				return $a->ID <=> $b->ID;
			}
		);
		$n = count( $recent );
		foreach ( $recent as $k => $post ) {
			boi_dates_set( $post->ID, $now - ( $n - 1 - $k ) * MINUTE_IN_SECONDS );
		}
		delete_option( 'boi_dates_signature' );
	}

	update_option( 'boi_dates_frozen', gmdate( 'c', $now ), false );
}

/**
 * Registers the reading-order list used in the footer.
 *
 * @return void
 */
function boi_register_reading_parts() {
	register_block_type( 'bestofislam/reading-parts', array( 'render_callback' => 'boi_render_reading_parts' ) );
}
add_action( 'init', 'boi_register_reading_parts' );

/**
 * The seven parts of the reading order as a numbered list, each linking to
 * the first published article of its part. The numbering is the site's
 * countdown motif; the first numeral is gold, as on the mark.
 *
 * @return string
 */
function boi_render_reading_parts() {
	$items = '';
	$urls  = get_transient( 'boi_reading_urls' );

	// One lookup per part would run seven queries on every render, so the
	// resolved addresses are kept until an article is saved or unlisted.
	if ( ! is_array( $urls ) ) {
		$urls = array();

		foreach ( boi_reading_order() as $i => $part ) {
			$urls[ $i ] = '';

			foreach ( $part['slugs'] as $slug ) {
				$post = get_page_by_path( $slug, OBJECT, 'post' );

				if ( $post && 'publish' === $post->post_status && ! boi_is_unlisted( $post ) ) {
					$urls[ $i ] = get_permalink( $post );
					break;
				}
			}
		}

		set_transient( 'boi_reading_urls', $urls, 12 * HOUR_IN_SECONDS );
	}

	foreach ( boi_reading_order() as $i => $part ) {
		$url = isset( $urls[ $i ] ) ? $urls[ $i ] : '';

		if ( '' === $url ) {
			continue;
		}

		$items .= sprintf(
			'<li><a href="%1$s"><span class="boi-parts__num">%2$s</span><span class="boi-parts__label">%3$s</span></a></li>',
			esc_url( $url ),
			esc_html( number_format_i18n( $i + 1 ) ),
			esc_html( $part['label'] )
		);
	}

	return '' === $items ? '' : '<ol class="boi-parts">' . $items . '</ol>';
}
