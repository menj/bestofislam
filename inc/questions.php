<?php
/**
 * Questions answered.
 *
 * One list of the questions put to Muslims, each with a short answer and the
 * article that argues it in full. The list feeds two places: the questions
 * band on the front page, and a panel at the head of each article naming
 * the questions it answers. Editing a question here changes it everywhere.
 *
 * Questions are phrased the way they are asked, attributed to no one.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * The questions, grouped by theme.
 *
 * Each item: question, answer, answering article slug, and whether it is
 * featured on the front page.
 *
 * @return array
 */
function boi_questions() {
	return array(
		'text'      => array(
			'label' => __( 'The Quran\'s text', 'bestofislam' ),
			'items' => array(
				array(
					'q'    => __( 'Where are Uthman\'s copies of the Quran?', 'bestofislam' ),
					'a'    => __( 'The Samarkand codex, now in Tashkent, is the copy tradition has attributed to Uthman for centuries, and its dating is argued. The Birmingham leaves are settled: radiocarbon dated to 568 to 645, in the Prophet\'s own generation. Both carry the text Muslims recite today.', 'bestofislam' ),
					'slug' => 'uthman-copies',
					'feat' => true,
				),
				array(
					'q'    => __( 'Why did Uthman burn copies of the Quran?', 'bestofislam' ),
					'a'    => __( 'Bukhari records the order and its reason in the same report: soldiers from Syria and Iraq were quarrelling over recitation, and Uthman acted to stop the community splitting over its Book. Burning a superseded copy is how Islamic law retires one respectfully.', 'bestofislam' ),
					'slug' => 'uthman-copies',
					'feat' => false,
				),
				array(
					'q'    => __( 'Is there a complete Quran from the seventh century?', 'bestofislam' ),
					'a'    => __( 'The Codex Parisino-Petropolitanus, from the first Islamic century, preserves close to half the text in one manuscript, and early leaves cover much of the rest. The complete text was carried from the start by recitation, memorised in full and recited daily, with writing as its check.', 'bestofislam' ),
					'slug' => 'uthman-copies',
					'feat' => false,
				),
				array(
					'q'    => __( 'Do the Bible\'s manuscripts match today\'s text exactly?', 'bestofislam' ),
					'a'    => __( 'Textual critics, Christian ones included, estimate around 400,000 variants among New Testament manuscripts. Most are trivial. Some are whole passages that modern Bibles bracket, such as the longer ending of Mark.', 'bestofislam' ),
					'slug' => 'manuscript-transmission',
					'feat' => false,
				),
			),
		),
		'scripture' => array(
			'label' => __( 'Earlier scripture', 'bestofislam' ),
			'items' => array(
				array(
					'q'    => __( 'Why trust hadith written down after the Prophet, when Muslims question the gospels?', 'bestofislam' ),
					'a'    => __( 'A hadith carries a named chain of transmitters, which Muslim scholars graded, rejecting many reports. The Catholic bishops\' own Bible says 2 Peter was probably written in Peter\'s name by a later author, perhaps a century on. The standard is the chain, applied evenly.', 'bestofislam' ),
					'slug' => 'second-peter',
					'feat' => true,
				),
				array(
					'q'    => __( 'Where is the Injil?', 'bestofislam' ),
					'a'    => __( 'The Quran describes a gospel given to Jesus and preached by him, and says nothing of a book in his hand. Mark agrees in its first chapter, where Jesus comes proclaiming the gospel of God.', 'bestofislam' ),
					'slug' => 'where-is-the-injil',
					'feat' => true,
				),
				array(
					'q'    => __( 'Why did God not preserve the Injil?', 'bestofislam' ),
					'a'    => __( 'The Quran attaches its promise of preservation to the Quran. Each earlier message was entrusted to its community, and the last was guarded directly. Jesus wrote nothing that survives, and Christians do not conclude from that that his teaching was lost.', 'bestofislam' ),
					'slug' => 'where-is-the-injil',
					'feat' => false,
				),
				array(
					'q'    => __( 'Does the Quran affirm a Bible it says is corrupt?', 'bestofislam' ),
					'a'    => __( 'The dilemma needs five assumptions, among them that the Injil is the four gospels and that corruption must be total. Each is contestable from the Quran\'s own text.', 'bestofislam' ),
					'slug' => 'islamic-dilemma',
					'feat' => true,
				),
				array(
					'q'    => __( 'How could Jews and Christians everywhere corrupt scripture in the same way?', 'bestofislam' ),
					'a'    => __( 'The Quran describes concealment and distortion of meaning within particular communities. Many classical scholars held that the distortion lay mainly in interpretation, with the words left standing. Neither requires a worldwide conspiracy.', 'bestofislam' ),
					'slug' => 'islamic-dilemma',
					'feat' => false,
				),
			),
		),
		'jesus'     => array(
			'label' => __( 'Jesus, Paul and Christian doctrine', 'bestofislam' ),
			'items' => array(
				array(
					'q'    => __( 'Did Paul keep the Law, or abolish it?', 'bestofislam' ),
					'a'    => __( 'Paul told his churches they were released from the Law and that the Sabbath no longer bound them. Set that beside Matthew 5:19, where relaxing the least commandment makes a man least in the kingdom.', 'bestofislam' ),
					'slug' => 'paul-and-the-law',
					'feat' => true,
				),
				array(
					'q'    => __( 'Did Jesus claim to be God?', 'bestofislam' ),
					'a'    => __( 'He prayed to God, said the Father was greater, did not know the hour, and never said "I am God" in any gospel. The verses cited for the claim all need a later doctrine to read that way.', 'bestofislam' ),
					'slug' => 'jesus-not-god',
					'feat' => true,
				),
				array(
					'q'    => __( 'Does the Quran say Mary is part of the Trinity?', 'bestofislam' ),
					'a'    => __( 'The verse asks whether Jesus told people to take him and his mother as gods. It says nothing about the Trinity, which the Quran addresses in a different verse.', 'bestofislam' ),
					'slug' => 'ezra-and-mary',
					'feat' => false,
				),
				array(
					'q'    => __( 'Which Jews called Ezra the son of God?', 'bestofislam' ),
					'a'    => __( 'No surviving Jewish text says so, and that should be admitted. The tradition names Jews of Medina, and the Quran is very nearly the only record of what Arabian Jews believed. Jewish memory raised Ezra close to Moses, and one early text has him taken up to heaven.', 'bestofislam' ),
					'slug' => 'ezra-and-mary',
					'feat' => false,
				),
				array(
					'q'    => __( 'Is Allah a different god from the God of the Bible?', 'bestofislam' ),
					'a'    => __( 'Allah is the Arabic word for God, used by Arab Christians before Islam existed and in Arabic Bibles today. The Quran tells Muslims to say to Jews and Christians that their God is one.', 'bestofislam' ),
					'slug' => 'same-god',
					'feat' => true,
				),
			),
		),
		'prophecy'  => array(
			'label' => __( 'Revelation and prophethood', 'bestofislam' ),
			'items' => array(
				array(
					'q'    => __( 'Who saw Gabriel in the cave?', 'bestofislam' ),
					'a'    => __( 'No one, as no one saw the burning bush or the light on the Damascus road. Every prophetic call is private. Islam rests its case on the message, which anyone can read and test.', 'bestofislam' ),
					'slug' => 'who-saw-gabriel',
					'feat' => false,
				),
				array(
					'q'    => __( 'Did Muhammad die of poison, and does that make him a false prophet?', 'bestofislam' ),
					'a'    => __( 'He lived four more years after Khaybar, while the man who ate beside him died within days. The immunity test being applied comes from the longer ending of Mark, which the oldest manuscripts lack.', 'bestofislam' ),
					'slug' => 'prophet-died-poisoning',
					'feat' => true,
				),
				array(
					'q'    => __( 'Did the Quran borrow Haman from the Book of Esther?', 'bestofislam' ),
					'a'    => __( 'A shared name proves nothing by itself. An Egyptian title for the overseer of quarry workers, attested in the reign of Ramesses II, matches the office the Quran gives Haman.', 'bestofislam' ),
					'slug' => 'haman-anachronism',
					'feat' => true,
				),
				array(
					'q'    => __( 'Is there evidence a prophet before Muhammad prayed at the Kaaba?', 'bestofislam' ),
					'a'    => __( 'The account of Abraham and Ishmael raising its foundations comes from the Quran, and Muslims accept it on that basis. There is no archaeological record of Abraham anywhere, Hebron included, so the demand applies to Genesis as much as to the Quran.', 'bestofislam' ),
					'slug' => 'qibla-change',
					'feat' => false,
				),
			),
		),
		'practice'  => array(
			'label' => __( 'Practice and history', 'bestofislam' ),
			'items' => array(
				array(
					'q'    => __( 'Why did the first Muslims pray towards Jerusalem?', 'bestofislam' ),
					'a'    => __( 'The Quran records the first direction itself, gives its reason as a test of obedience, and records the change to Mecca. The objection is quoted and answered in the text it is aimed at.', 'bestofislam' ),
					'slug' => 'qibla-change',
					'feat' => true,
				),
				array(
					'q'    => __( 'Where does the Quran say to pray five times a day?', 'bestofislam' ),
					'a'    => __( 'The Quran gives the prayer times and tells believers to follow the Messenger for the rest. The five daily prayers have been performed publicly by every Muslim community since the first generation.', 'bestofislam' ),
					'slug' => 'why-five-prayers',
					'feat' => true,
				),
				array(
					'q'    => __( 'Is Islam really the fastest-growing religion?', 'bestofislam' ),
					'a'    => __( 'Yes. Pew measured 347 million more Muslims between 2010 and 2020, more than every other religion combined. The drivers are births, youth and retention: 99 percent of people raised Muslim stay Muslim. Growth describes the world. It proves nothing about truth.', 'bestofislam' ),
					'slug' => 'fastest-growing-religion',
					'feat' => true,
				),
				array(
					'q'    => __( 'Is Islam a Middle Eastern religion?', 'bestofislam' ),
					'a'    => __( 'The country with the most Muslims is Indonesia, with about 239 million, and it is not an Islamic state: its constitution names one God and recognises six religions. Its national mosque stands across the street from Jakarta Cathedral.', 'bestofislam' ),
					'slug' => 'indonesia',
					'feat' => false,
				),
				array(
					'q'    => __( 'Why do ex-Muslim stories persuade so many readers online?', 'bestofislam' ),
					'a'    => __( 'A Malaysian study of one ex-Muslim blog found that storytelling and sympathy carried much of the persuasion, and that accounts of the insults she received drew readers to her. The replies researchers thought could reach her answered her reasons with respect.', 'bestofislam' ),
					'slug' => 'ex-muslim-blog-study',
					'feat' => false,
				),
				array(
					'q'    => __( 'Did any ancient writer mention the Kaaba before Islam?', 'bestofislam' ),
					'a'    => __( 'Possibly. Diodorus of Sicily described a temple revered by all Arabians, and Ptolemy listed a town called Macoraba, but scholars dispute both identifications. The Quran makes the claim on its own authority.', 'bestofislam' ),
					'slug' => 'kaaba-witnesses',
					'feat' => false,
				),
				array(
					'q'    => __( 'What do the Dome of the Rock inscriptions show about the Quran?', 'bestofislam' ),
					'a'    => __( 'Dated 72 AH, about sixty years after the Prophet, they quote passages of the Quran that match the text recited today, and they allude to it in ways that assume their readers already knew it.', 'bestofislam' ),
					'slug' => 'dome-inscriptions',
					'feat' => false,
				),
				array(
					'q'    => __( 'Did Muslims burn the Library of Alexandria?', 'bestofislam' ),
					'a'    => __( 'No. The story of the caliph Umar ordering it burned first appears nearly six centuries after the conquest, and the library had largely vanished centuries before the Arabs arrived. Historians, Muslim and Western, regard the story as a legend.', 'bestofislam' ),
					'slug' => 'library-of-alexandria',
					'feat' => false,
				),
				array(
					'q'    => __( 'What was the jizya?', 'bestofislam' ),
					'a'    => __( 'A graduated annual tax on adult non-Muslim men under Muslim rule, with exemptions for women, children, monks and those unable to work. It was tied to protection and military service, and it also marked a difference of religion.', 'bestofislam' ),
					'slug' => 'jizya',
					'feat' => false,
				),
				array(
					'q'    => __( 'Was the Quran perfectly preserved?', 'bestofislam' ),
					'a'    => __( 'Academic scholarship finds a standard text fixed under Uthman within two decades of the Prophet, followed by every later manuscript but one, with modest variation in matters the Islamic tradition itself recorded, such as surah order and verse numbering.', 'bestofislam' ),
					'slug' => 'quran-preservation-reddit',
					'feat' => false,
				),
				array(
					'q'    => __( 'Does Islam punish leaving it with death?', 'bestofislam' ),
					'a'    => __( 'Mainstream Islamic law prescribes it, on the strength of the hadith, while the Quran condemns apostasy without naming a worldly penalty. Some senior scholars confine the penalty to treason; the question is debated among Muslims today.', 'bestofislam' ),
					'slug' => 'apostasy-in-islam',
					'feat' => false,
				),
				array(
					'q'    => __( 'Does the Quran deny the crucifixion?', 'bestofislam' ),
					'a'    => __( 'It denies that Jesus was killed or crucified by his enemies, and says God raised him to Himself. Historians treat the crucifixion as well attested; Muslims hold to the Quran on the authority of revelation, and reject atonement by proxy on the Hebrew prophets\' own principle that each soul bears its own sin.', 'bestofislam' ),
					'slug' => 'crucifixion-quran',
					'feat' => false,
				),
				array(
					'q'    => __( 'Did Islam spread by the sword?', 'bestofislam' ),
					'a'    => __( 'The Quran forbids compulsion in belief and bounds fighting with a prohibition on transgression. Current data put jihadist violence far below the impression the coverage gives.', 'bestofislam' ),
					'slug' => 'islam-religion-of-peace',
					'feat' => true,
				),
			),
		),
		'reason'    => array(
			'label' => __( 'God and reason', 'bestofislam' ),
			'items' => array(
				array(
					'q'    => __( 'Is chance enough to explain the universe?', 'bestofislam' ),
					'a'    => __( 'Chance produces noise. The universe has a beginning, finely balanced constants and law, and a multiverse moves the question back a step without answering it.', 'bestofislam' ),
					'slug' => 'universe-demands-creator',
					'feat' => false,
				),
				array(
					'q'    => __( 'Can there be morality without God?', 'bestofislam' ),
					'a'    => __( 'Atheists can plainly be good. The question is what grounds the duty, and flourishing, consensus and evolution each explain a feeling without making it binding.', 'bestofislam' ),
					'slug' => 'morality-without-god',
					'feat' => false,
				),
			),
		),
	);
}

/**
 * Stable anchor for a question.
 *
 * @param string $question Question text.
 * @return string
 */
function boi_question_anchor( $question ) {
	return 'q-' . sanitize_title( $question );
}

/**
 * Resolves an item's answering article, published only.
 *
 * @param array $item Question item.
 * @return WP_Post|null
 */
function boi_question_post( $item ) {
	static $cache = array();

	if ( ! array_key_exists( $item['slug'], $cache ) ) {
		$post                   = get_page_by_path( $item['slug'], OBJECT, 'post' );
		$cache[ $item['slug'] ] = ( $post && 'publish' === $post->post_status && ! boi_is_unlisted( $post ) ) ? $post : null;
	}

	return $cache[ $item['slug'] ];
}

/**
 * Where the questions live: the band on the front page.
 *
 * @return string
 */
function boi_questions_url() {
	return home_url( '/#objections' );
}

/**
 * Registers the question blocks.
 *
 * @return void
 */
function boi_register_question_blocks() {
	register_block_type( 'bestofislam/article-questions', array( 'render_callback' => 'boi_render_article_questions' ) );
}
add_action( 'init', 'boi_register_question_blocks' );

/**
 * One question as an accordion item.
 *
 * @param array   $item Question item.
 * @param WP_Post $post Answering article.
 * @param bool    $id   Whether to give the item an anchor.
 * @return string
 */
function boi_question_item( $item, $post, $id = true ) {
	return sprintf(
		'<details class="boi-objection"%1$s><summary><span class="boi-objection__label">%2$s</span><span class="boi-objection__claim">%3$s</span></summary><div class="boi-objection__answer"><p>%4$s</p><a href="%5$s">%6$s</a></div></details>',
		$id ? ' id="' . esc_attr( boi_question_anchor( $item['q'] ) ) . '"' : '',
		esc_html__( 'The question', 'bestofislam' ),
		esc_html( $item['q'] ),
		esc_html( $item['a'] ),
		esc_url( get_permalink( $post ) ),
		esc_html( sprintf( /* translators: %s: title. */ __( 'Full answer: %s', 'bestofislam' ), get_the_title( $post ) ) )
	);
}

/**
 * The front-page band: the featured questions, then the rest grouped by
 * theme behind a single disclosure. Every question carries an anchor, so an
 * article can link straight to its answer.
 *
 * @return string
 */
function boi_render_objections() {
	$featured = '';
	$groups   = '';
	$rest     = 0;

	foreach ( boi_questions() as $key => $group ) {
		$items = '';

		foreach ( $group['items'] as $item ) {
			$post = boi_question_post( $item );

			if ( ! $post ) {
				continue;
			}

			if ( ! empty( $item['feat'] ) ) {
				$featured .= boi_question_item( $item, $post );
			} else {
				$items .= boi_question_item( $item, $post );
				++$rest;
			}
		}

		if ( '' !== $items ) {
			$groups .= sprintf(
				'<section class="boi-qgroup" id="qg-%1$s"><h3 class="boi-qgroup__title">%2$s</h3><div class="boi-objections boi-objections--list">%3$s</div></section>',
				esc_attr( $key ),
				esc_html( $group['label'] ),
				$items
			);
		}
	}

	if ( '' === $featured && '' === $groups ) {
		return '';
	}

	$more = '' === $groups ? '' : sprintf(
		'<details class="boi-qmore"><summary class="boi-objections__all">%1$s</summary><div class="boi-qmore__body">%2$s</div></details>',
		esc_html( sprintf( /* translators: %d: number of further questions. */ _n( 'Show %d more question', 'Show %d more questions', $rest, 'bestofislam' ), $rest ) ),
		$groups
	);

	return '<div class="boi-objections">' . $featured . '</div>' . $more;
}

/**
 * The panel at the head of an article, naming the questions it answers and
 * linking each to its answer on the front page.
 *
 * @return string
 */
function boi_render_article_questions() {
	$post = get_post();

	if ( ! $post ) {
		return '';
	}

	$links = '';

	foreach ( boi_questions() as $group ) {
		foreach ( $group['items'] as $item ) {
			if ( $item['slug'] === $post->post_name ) {
				$links .= sprintf(
					'<li><a href="%1$s">%2$s</a></li>',
					esc_url( home_url( '/#' . boi_question_anchor( $item['q'] ) ) ),
					esc_html( $item['q'] )
				);
			}
		}
	}

	if ( '' === $links ) {
		return '';
	}

	return sprintf(
		'<aside class="boi-answers"><p class="boi-answers__label">%1$s</p><ul>%2$s</ul><a class="boi-answers__more" href="%3$s">%4$s</a></aside>',
		esc_html__( 'Questions this piece answers', 'bestofislam' ),
		$links,
		esc_url( boi_questions_url() ),
		esc_html__( 'See every question answered', 'bestofislam' )
	);
}

/**
 * FAQPage structured data on the front page, where the answers are.
 *
 * @return void
 */
function boi_print_questions_schema() {
	if ( ! is_front_page() ) {
		return;
	}

	$entities = array();

	foreach ( boi_questions() as $group ) {
		foreach ( $group['items'] as $item ) {
			$post = boi_question_post( $item );

			if ( ! $post ) {
				continue;
			}

			$entities[] = array(
				'@type'          => 'Question',
				'name'           => $item['q'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $item['a'] . ' ' . get_permalink( $post ),
				),
			);
		}
	}

	if ( empty( $entities ) ) {
		return;
	}

	echo "\n<script type=\"application/ld+json\">" . wp_json_encode(
		array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $entities,
		),
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
	) . "</script>\n";
}
add_action( 'wp_head', 'boi_print_questions_schema', 21 );
