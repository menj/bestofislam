<?php
/**
 * Content auto-population.
 *
 * Runs once on activation and creates the topic taxonomy terms, four listicles
 * built from the block set, a front page, and the primary navigation menu. The
 * listicle bodies are structural skeletons: titles and objections are in place
 * so the layout is populated, while the responses are left for the author.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns the topic terms created on activation.
 *
 * @return array
 */
function boi_seed_topics() {
	return array(
		'belief-and-practices' => array(
			'name'        => __( 'Belief & Practices', 'bestofislam' ),
			'parent'      => '',
			'description' => __( 'Explanatory treatments of doctrine, ritual, and observance.', 'bestofislam' ),
		),
		'faith-and-reason'     => array(
			'name'        => __( 'Faith & Reason', 'bestofislam' ),
			'parent'      => '',
			'description' => __( 'Arguments for belief, and answers to the objections raised against it.', 'bestofislam' ),
		),
		'comparative-religion' => array(
			'name'        => __( 'Comparative Religion', 'bestofislam' ),
			'parent'      => '',
			'description' => __( 'Where the Quran and the Bible meet, and where they part.', 'bestofislam' ),
		),
		'science'              => array(
			'name'        => __( 'Science', 'bestofislam' ),
			'parent'      => '',
			'description' => __( 'The scientific tradition, and the question of its relation to revelation.', 'bestofislam' ),
		),
		'history'              => array(
			'name'        => __( 'History', 'bestofislam' ),
			'parent'      => '',
			'description' => __( 'Fourteen centuries of political, intellectual, and institutional history.', 'bestofislam' ),
		),
		'arts-and-culture'     => array(
			'name'        => __( 'Arts & Culture', 'bestofislam' ),
			'parent'      => 'history',
			'description' => __( 'Architecture, calligraphy, literature, music, and cuisine.', 'bestofislam' ),
		),
		'the-muslim-world'     => array(
			'name'        => __( 'The Muslim World', 'bestofislam' ),
			'parent'      => '',
			'description' => __( 'Contemporary Muslim societies and the conditions they face.', 'bestofislam' ),
		),
		'islamophobia'         => array(
			'name'        => __( 'Islamophobia', 'bestofislam' ),
			'parent'      => 'the-muslim-world',
			'description' => __( 'Prejudice against Muslims: its forms, its sources, and its consequences.', 'bestofislam' ),
		),
	);
}

/**
 * Returns the listicles created on activation.
 *
 * Each entry names a file in /content/, which holds the finished article as
 * block markup. Keeping the prose out of PHP means an editor can revise an
 * article without touching code, and the seed stays readable.
 *
 * @return array
 */
function boi_seed_listicles() {
	return array(
		array(
			'slug'    => 'islam-religion-of-peace',
			'title'   => __( '5 reasons why Islam is the religion of peace', 'bestofislam' ),
			'excerpt' => __( '5 reasons Islam is called the religion of peace, plus an honest look at the violence done in its name. Read the case.', 'bestofislam' ),
			'topic'   => 'the-muslim-world',
		),
		array(
			'slug'    => 'prophet-died-poisoning',
			'title'   => __( '6 answers to the claim that the Prophet died from poisoning', 'bestofislam' ),
			'excerpt' => __( 'Polemicists say the poisoning at Khaybar disproves Muhammad\'s prophethood. 6 answers from the sources. Read them.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'al-zutt-allegation',
			'title'   => __( '5 things wrong with the al-Zutt allegation', 'bestofislam' ),
			'excerpt' => __( 'A recent polemic reads a hadith about al-Zutt as sexual assault. 5 reasons the reading collapses. Read the philology.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'islamic-dilemma',
			'title'   => __( 'The Islamic Dilemma: 5 assumptions the argument needs', 'bestofislam' ),
			'excerpt' => __( 'The Islamic Dilemma claims the Quran traps Muslims on scripture. 5 assumptions it needs, and why each fails. Read on.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'five-pillars-explained',
			'title'   => __( 'The 5 pillars, explained without the jargon', 'bestofislam' ),
			'excerpt' => __( 'The 5 pillars of Islam explained plainly: what each one is, what it asks, and why it sits where it does. Read the guide.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'who-is-allah',
			'title'   => __( 'Who is Allah? 5 misunderstandings about the name itself', 'bestofislam' ),
			'excerpt' => __( 'Who is Allah? 5 common misunderstandings about the name, from "a different god" to "the moon god", answered plainly.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'universe-demands-creator',
			'title'   => __( '5 reasons why the universe demands a creator', 'bestofislam' ),
			'excerpt' => __( '5 reasons the universe points to a creator, each stated against the objection it answers. Read the case and test it.', 'bestofislam' ),
			'topic'   => 'faith-and-reason',
		),
		array(
			'slug'    => 'morality-without-god',
			'title'   => __( 'Why morality is impossible without God: 4 fatal flaws in secular humanism', 'bestofislam' ),
			'excerpt' => __( '4 places the secular account of morality gives way, each stated in the humanist\'s own terms before it\'s answered. Read on.', 'bestofislam' ),
			'topic'   => 'faith-and-reason',
		),
		array(
			'slug'    => 'instruments-islamic-world',
			'title'   => __( '6 instruments invented in the Islamic world that science still uses', 'bestofislam' ),
			'excerpt' => __( '6 scientific instruments from the Islamic world that laboratories still use, with what each one changed. Read the list.', 'bestofislam' ),
			'topic'   => 'science',
		),
		array(
			'slug'    => 'seven-libraries',
			'title'   => __( '7 libraries of the Islamic world, and what happened to each', 'bestofislam' ),
			'excerpt' => __( '7 great libraries of the Islamic world, from Baghdad to Timbuktu, and the fate of each. Read what survived and what burned.', 'bestofislam' ),
			'topic'   => 'history',
		),
		array(
			'slug'    => 'same-god',
			'title'   => __( '6 reasons why Allah of the Quran is the same God as the God of the Bible', 'bestofislam' ),
			'excerpt' => __( '6 reasons Muslims say the God of the Quran and the God of the Bible are one, each answered against the objection. Read them.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'jesus-not-god',
			'title'   => __( '5 reasons why Muslims believe that Jesus is not God', 'bestofislam' ),
			'excerpt' => __( '5 reasons Muslims hold that Jesus was a prophet and not God, argued from the gospels as much as from the Quran. Read them.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'manuscript-transmission',
			'title'   => __( 'Manuscript transmission: 5 differences between the Quran and the New Testament', 'bestofislam' ),
			'excerpt' => __( 'How the Quran and the New Testament reached us: 5 differences in transmission that decide the reliability debate. Read them.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'shared-prophets',
			'title'   => __( '6 prophets both books share, and where the accounts diverge', 'bestofislam' ),
			'excerpt' => __( '6 prophets the Bible and the Quran share, with where the two accounts agree and where they part. Read the comparison.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'verses-read-differently',
			'title'   => __( '7 verses Christians and Muslims read differently, and why', 'bestofislam' ),
			'excerpt' => __( '7 Bible verses that Christians and Muslims read in opposite ways, with the reasoning on each side laid out plainly. Read them.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'haman-anachronism',
			'title'   => __( '5 answers to the claim that Haman is a Quranic mistake', 'bestofislam' ),
			'excerpt' => __( 'Orientalists say the Quran\'s Haman is borrowed from Esther, 1,000 years too early. 5 answers, including the Egyptian evidence.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
	);
}

/**
 * Loads an article's block markup from the content directory.
 *
 * @param string $slug Article slug.
 * @return string Block markup, or an empty string when the file is missing.
 */
function boi_load_article( $slug ) {
	$path = BOI_DIR . '/content/' . sanitize_file_name( $slug ) . '.html';

	if ( ! file_exists( $path ) ) {
		return '';
	}

	return (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
}

/**
 * Creates the topic terms.
 *
 * @return array Map of slug to term identifier.
 */
function boi_create_topics() {
	$map = array();

	if ( isset( $pages['blog'] ) ) {
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => __( 'Reflections', 'bestofislam' ),
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $pages['blog'],
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			)
		);
	}

	foreach ( boi_seed_topics() as $slug => $topic ) {
		$existing = term_exists( $slug, 'listicle-topic' );

		if ( $existing ) {
			$map[ $slug ] = (int) $existing['term_id'];
			continue;
		}

		$args = array(
			'slug'        => $slug,
			'description' => $topic['description'],
		);

		// Parents are declared before their children in the seed array, so the
		// parent identifier is always present by the time a child is reached.
		if ( '' !== $topic['parent'] && isset( $map[ $topic['parent'] ] ) ) {
			$args['parent'] = $map[ $topic['parent'] ];
		}

		$term = wp_insert_term( $topic['name'], 'listicle-topic', $args );

		if ( ! is_wp_error( $term ) ) {
			$map[ $slug ] = (int) $term['term_id'];
		}
	}

	return $map;
}

/**
 * Creates the seed listicles.
 *
 * @param array $topics Map of topic slug to term identifier.
 * @return array Created post identifiers.
 */
function boi_create_listicles( $topics ) {
	$created = array();

	foreach ( boi_seed_listicles() as $listicle ) {
		if ( get_page_by_path( $listicle['slug'], OBJECT, 'post' ) ) {
			continue;
		}

		$content = boi_load_article( $listicle['slug'] );

		if ( '' === $content ) {
			continue;
		}

		$post_id = wp_insert_post(
			array(
				'post_title'   => $listicle['title'],
				'post_name'    => $listicle['slug'],
				'post_excerpt' => $listicle['excerpt'],
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_type'    => 'post',
			)
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		if ( isset( $topics[ $listicle['topic'] ] ) ) {
			wp_set_object_terms( $post_id, array( $topics[ $listicle['topic'] ] ), 'listicle-topic' );
		}

		$created[] = $post_id;
	}

	return $created;
}

/**
 * Creates the front page and the about page.
 *
 * @return array Map of key to post identifier.
 */
function boi_create_pages() {
	$p = function ( $text ) {
		return "<!-- wp:paragraph -->\n<p>" . esc_html( $text ) . "</p>\n<!-- /wp:paragraph -->\n\n";
	};
	$h = function ( $text ) {
		return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">" . esc_html( $text ) . "</h2>\n<!-- /wp:heading -->\n\n";
	};

	$pages = array(
		'home'      => array(
			'title'   => __( 'Home', 'bestofislam' ),
			'content' => '',
		),
		'blog'      => array(
			'title'   => __( 'Reflections', 'bestofislam' ),
			'content' => '',
		),
		'about'     => array(
			'title'   => __( 'About', 'bestofislam' ),
			'content' => $p( __( 'Best of Islam publishes ranked arguments on the questions that decide the matter: whether God exists, what Islam teaches, what its history was, and what its critics get wrong.', 'bestofislam' ) )
				. $p( __( 'Each piece takes the same form. An objection is stated in its own terms, then answered from the Quran, the hadith, the historical record and the scholarly tradition, with the sources named. Where a critic has a point, the piece says so. Where the answer is contested among Muslim scholars, the piece says that too.', 'bestofislam' ) )
				. $h( __( 'Who writes it', 'bestofislam' ) )
				. $p( __( 'The site is written and edited by Mohd Elfie Nieshaem Juferi, a Muslim apologist who has written on Christian and Muslim polemic since the early 2000s. Longer treatments of many of the arguments here appear on Bismika Allahuma, the companion site.', 'bestofislam' ) )
				. $h( __( 'What it is not', 'bestofislam' ) )
				. $p( __( 'It is not a site for converting anyone. It is a site for arguing well. Readers who leave unconvinced but better informed about what Muslims think and why have got what the site is for.', 'bestofislam' ) ),
		),
		'faq'       => array(
			'title'   => __( 'Frequently asked questions', 'bestofislam' ),
			'content' => $h( __( 'Why are the articles numbered lists?', 'bestofislam' ) )
				. $p( __( 'Because an argument has parts, and a list makes the parts visible. A reader can see what is being claimed, jump to the entry that concerns them, and judge each answer on its own.', 'bestofislam' ) )
				. $h( __( 'Do you quote critics accurately?', 'bestofislam' ) )
				. $p( __( 'That is the rule the site is built on. Every objection is stated in the terms its defenders use before it is answered. If you find one misrepresented, write in and it will be corrected.', 'bestofislam' ) )
				. $h( __( 'Which translation of the Quran do you use?', 'bestofislam' ) )
				. $p( __( 'Verses are given in the author\'s own rendering, checked against the standard translations, and cited by chapter and verse so you can compare.', 'bestofislam' ) )
				. $h( __( 'Can I republish an article?', 'bestofislam' ) )
				. $p( __( 'Quotation with attribution and a link is welcome. Republishing a whole piece is not, unless agreed in advance. Use the contact page.', 'bestofislam' ) )
				. $h( __( 'How do I suggest a topic?', 'bestofislam' ) )
				. $p( __( 'Through the contact page. Objections you have met and not seen answered are the most useful suggestions.', 'bestofislam' ) )
				. $h( __( 'Are comments open?', 'bestofislam' ) )
				. $p( __( 'Yes, and moderated. Argument is welcome. Abuse is deleted.', 'bestofislam' ) ),
		),
		'contact'   => array(
			'title'   => __( 'Contact', 'bestofislam' ),
			'content' => $p( __( 'Corrections, objections and topic suggestions are all welcome. Messages go to the editor and are read, though not every one gets a reply.', 'bestofislam' ) )
				. "<!-- wp:bestofislam/contact-form /-->\n",
		),
		'sources'   => array(
			'title'   => __( 'Sources and standards', 'bestofislam' ),
			'content' => $h( __( 'What gets cited', 'bestofislam' ) )
				. $p( __( 'The Quran by chapter and verse. Hadith by collection and, where it matters, by narrator and grading. Historical claims by the primary source where one exists and by a named modern historian where it does not. Statistics by the body that published them, with the year.', 'bestofislam' ) )
				. $h( __( 'What gets conceded', 'bestofislam' ) )
				. $p( __( 'Where a critic is right, the article says so. Where Muslim scholars disagree, the article names the disagreement. A site that concedes nothing cannot be trusted when it concedes nothing.', 'bestofislam' ) )
				. $h( __( 'Corrections', 'bestofislam' ) )
				. $p( __( 'Errors of fact are corrected in the article and noted at the foot of it. Errors of argument are answered, if at all, in a new piece.', 'bestofislam' ) ),
		),
		'privacy'   => array(
			'title'   => __( 'Privacy', 'bestofislam' ),
			'content' => $p( __( 'This site keeps as little as it can.', 'bestofislam' ) )
				. $h( __( 'What is collected', 'bestofislam' ) )
				. $p( __( 'The contact form sends your name, email address and message to the editor by email and stores nothing on the site. Comments store the name, email and content you submit, as WordPress does. Voting, where enabled, stores a one-way hash of your address so a vote cannot be cast twice; it cannot be reversed into an address.', 'bestofislam' ) )
				. $h( __( 'What is not collected', 'bestofislam' ) )
				. $p( __( 'No advertising, no tracking scripts, no third-party analytics. The share links are plain links; nothing loads from those services until you click.', 'bestofislam' ) )
				. $h( __( 'Cookies', 'bestofislam' ) )
				. $p( __( 'One setting, your choice of light or dark appearance, is kept in your browser and never sent anywhere. WordPress sets its own cookies if you log in or comment.', 'bestofislam' ) ),
		),
	);

	$map = array();

	foreach ( $pages as $key => $page ) {
		$existing = get_page_by_path( sanitize_title( $page['title'] ), OBJECT, 'page' );

		if ( $existing ) {
			$map[ $key ] = (int) $existing->ID;
			continue;
		}

		$post_id = wp_insert_post(
			array(
				'post_title'   => $page['title'],
				'post_content' => (string) $page['content'],
				'post_status'  => 'publish',
				'post_type'    => 'page',
			)
		);

		if ( ! is_wp_error( $post_id ) && $post_id ) {
			$map[ $key ] = (int) $post_id;
		}
	}

	return $map;
}

/**
 * Creates the navigation as a wp_navigation post.
 *
 * Block themes read navigation from this post type, not from classic menus,
 * so this is what the header's navigation block resolves to when an editor
 * picks it in the Site Editor. The header part ships static links as well,
 * so the menu is present on activation with no selection required.
 *
 * @param array $topics Map of topic slug to term identifier.
 * @param array $pages  Map of page key to post identifier.
 * @return int Navigation post identifier, or 0.
 */
function boi_create_navigation( $topics, $pages ) {
	$existing = get_posts(
		array(
			'post_type'      => 'wp_navigation',
			'name'           => 'primary',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);

	if ( $existing ) {
		return (int) $existing[0];
	}

	$links = '';

	if ( isset( $pages['blog'] ) ) {
		$links .= boi_nav_link_block( __( 'Reflections', 'bestofislam' ), get_permalink( $pages['blog'] ), $pages['blog'], 'page' );
	}

	foreach ( boi_seed_topics() as $slug => $topic ) {
		if ( ! isset( $topics[ $slug ] ) || '' !== $topic['parent'] ) {
			continue;
		}

		$links .= boi_nav_link_block( $topic['name'], get_term_link( (int) $topics[ $slug ], 'listicle-topic' ), $topics[ $slug ], 'listicle-topic' );
	}

	foreach ( array( 'about' => __( 'About', 'bestofislam' ), 'faq' => __( 'FAQ', 'bestofislam' ), 'contact' => __( 'Contact', 'bestofislam' ) ) as $key => $label ) {
		if ( isset( $pages[ $key ] ) ) {
			$links .= boi_nav_link_block( $label, get_permalink( $pages[ $key ] ), $pages[ $key ], 'page' );
		}
	}

	$id = wp_insert_post(
		array(
			'post_type'    => 'wp_navigation',
			'post_title'   => __( 'Primary', 'bestofislam' ),
			'post_name'    => 'primary',
			'post_status'  => 'publish',
			'post_content' => $links,
		)
	);

	return is_wp_error( $id ) ? 0 : (int) $id;
}

/**
 * Builds one navigation-link block.
 *
 * @param string     $label Link text.
 * @param string     $url   Destination.
 * @param int        $id    Object identifier.
 * @param string     $kind  page or taxonomy slug.
 * @return string
 */
function boi_nav_link_block( $label, $url, $id, $kind ) {
	$attrs = array(
		'label' => $label,
		'url'   => $url,
		'id'    => (int) $id,
		'kind'  => 'page' === $kind ? 'post-type' : 'taxonomy',
		'type'  => $kind,
	);

	return '<!-- wp:navigation-link ' . wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . ' /-->' . "\n";
}

/**
 * Removes the WordPress sample content.
 *
 * @return void
 */
function boi_remove_sample_content() {
	$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );

	if ( $hello ) {
		wp_trash_post( $hello->ID );
	}

	$sample = get_page_by_path( 'sample-page', OBJECT, 'page' );

	if ( $sample ) {
		wp_trash_post( $sample->ID );
	}
}

/**
 * Populates the site. Idempotent: nothing is duplicated on a second run.
 *
 * @return array Summary counts.
 */
function boi_populate_content() {
	$topics    = boi_create_topics();
	$pages     = boi_create_pages();
	$listicles = boi_create_listicles( $topics );

	boi_create_navigation( $topics, $pages );
	boi_remove_sample_content();

	if ( isset( $pages['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $pages['home'] );
	}

	if ( isset( $pages['blog'] ) ) {
		update_option( 'page_for_posts', $pages['blog'] );
	}

	boi_backfill_listicle_flags();
	boi_migrate_seed( $topics );

	update_option( 'boi_content_populated', gmdate( 'c' ) );

	return array(
		'topics'    => count( $topics ),
		'listicles' => count( $listicles ),
		'pages'     => count( $pages ),
	);
}

/**
 * Runs population once, on first activation.
 *
 * @return void
 */
function boi_populate_on_activation() {
	if ( get_option( 'boi_content_populated' ) ) {
		return;
	}

	boi_register_taxonomy();
	boi_populate_content();
}
add_action( 'after_switch_theme', 'boi_populate_on_activation', 20 );

/**
 * Handles a manual re-run from the settings screen.
 *
 * @return void
 */
function boi_handle_populate_request() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not permitted to do that.', 'bestofislam' ) );
	}

	check_admin_referer( 'boi_populate' );

	$summary = boi_populate_content();

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'          => 'boi-settings',
				'tab'           => 'content',
				'boi_populated' => (int) $summary['listicles'],
			),
			admin_url( 'themes.php' )
		)
	);
	exit;
}
add_action( 'admin_post_boi_populate', 'boi_handle_populate_request' );

/**
 * Corrects records left by earlier versions of the seed.
 *
 * Population never overwrites, so a term created at the top level by an old
 * seed stays there, and a post created with an old title keeps it. This pass
 * moves terms to the parent the current seed declares, and updates the title
 * and excerpt of any seeded post that still carries an earlier version.
 *
 * @param array $topics Map of topic slug to term identifier.
 * @return void
 */
function boi_migrate_seed( $topics ) {
	foreach ( boi_seed_topics() as $slug => $topic ) {
		if ( ! isset( $topics[ $slug ] ) ) {
			continue;
		}

		$term = get_term( $topics[ $slug ], 'listicle-topic' );

		if ( ! $term || is_wp_error( $term ) ) {
			continue;
		}

		$parent = '' !== $topic['parent'] && isset( $topics[ $topic['parent'] ] ) ? (int) $topics[ $topic['parent'] ] : 0;
		$args   = array();

		if ( (int) $term->parent !== $parent ) {
			$args['parent'] = $parent;
		}

		if ( $term->description !== $topic['description'] ) {
			$args['description'] = $topic['description'];
		}

		if ( $args ) {
			wp_update_term( $term->term_id, 'listicle-topic', $args );
		}
	}

	foreach ( boi_seed_listicles() as $listicle ) {
		$post = get_page_by_path( $listicle['slug'], OBJECT, 'post' );

		if ( ! $post ) {
			continue;
		}

		$args = array();

		if ( $post->post_title !== $listicle['title'] ) {
			$args['post_title'] = $listicle['title'];
		}

		if ( $post->post_excerpt !== $listicle['excerpt'] ) {
			$args['post_excerpt'] = $listicle['excerpt'];
		}

		if ( isset( $topics[ $listicle['topic'] ] ) ) {
			wp_set_object_terms( $post->ID, array( $topics[ $listicle['topic'] ] ), 'listicle-topic' );
		}

		if ( $args ) {
			$args['ID'] = $post->ID;
			wp_update_post( $args );
		}
	}
}
