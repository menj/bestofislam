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
 * Saves a new post. WordPress removes one level of backslashes from what it
 * is given, so the data is slashed first; without this, escaped quotation
 * marks inside block settings were stripped and the settings became
 * unreadable.
 *
 * @param array $postarr Post data.
 * @param bool  $wp_error Whether to return a WP_Error on failure.
 * @return int|WP_Error
 */
function boi_insert_post( $postarr, $wp_error = false ) {
	return wp_insert_post( wp_slash( $postarr ), $wp_error );
}

/**
 * Updates a post, slashing the data for the same reason.
 *
 * @param array $postarr Post data, including ID.
 * @return int|WP_Error
 */
function boi_update_post( $postarr ) {
	return wp_update_post( wp_slash( $postarr ) );
}

/**
 * A fingerprint of block content in the form WordPress stores it.
 * WordPress rewrites the formatting of block settings on saving, so the
 * content is passed through its own parser and serialiser before hashing,
 * and a file and its saved copy produce the same fingerprint.
 *
 * @param string $content Block content.
 * @return string
 */
function boi_content_fingerprint( $content ) {
	return md5( serialize_blocks( parse_blocks( (string) $content ) ) );
}

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
			'parent'      => '',
			'description' => __( 'Architecture, calligraphy, literature, music, and cuisine.', 'bestofislam' ),
		),
		'the-muslim-world'     => array(
			'name'        => __( 'The Muslim World', 'bestofislam' ),
			'parent'      => '',
			'description' => __( 'Contemporary Muslim societies and the conditions they face.', 'bestofislam' ),
		),
		'islamophobia'         => array(
			'name'        => __( 'Islamophobia', 'bestofislam' ),
			'parent'      => '',
			'description' => __( 'Prejudice against Muslims: its forms, its sources, and its consequences.', 'bestofislam' ),
		),
	);
}

/**
 * Returns the listicles created on activation.
 *
 * Each entry names a file in /content/, which holds the finished article as
 * block markup inside a JSON file. Keeping the prose out of PHP means an
 * editor can revise an article without touching code, and the seed stays
 * readable.
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
			'slug'    => 'muhammad-false-prophet-claim',
			'title'   => __( 'Did Muhammad fail as a prophet? 5 answers on miracles, signs and the Al-Rum prophecy', 'bestofislam' ),
			'excerpt' => __( 'Critics say Muhammad showed no miracles, made a failed prophecy and might have been epileptic. 5 answers from the record. Read them.', 'bestofislam' ),
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
			'excerpt' => __( 'The Islamic dilemma says the Quran traps Muslims on the Torah and Gospel. 5 assumptions it needs, and why each fails, from the classical tafsir.', 'bestofislam' ),
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
			'excerpt' => __( '4 places the secular account of morality gives way, each stated in the humanist\'s own terms before it is answered. Read on.', 'bestofislam' ),
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
			'topic'   => 'arts-and-culture',
		),
		array(
			'slug'    => 'deuteronomy-18-and-the-paraclete',
			'title'   => __( 'Deuteronomy 18 and the Paraclete: 4 points on Muhammad in the Bible', 'bestofislam' ),
			'excerpt' => __( 'The prophet like Moses, the Comforter in John and Ibn Ishaq\'s Syriac source: the Muslim case and the Christian reply. 4 points.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'same-god',
			'title'   => __( '7 reasons why Allah of the Quran is the same God as the God of the Bible', 'bestofislam' ),
			'excerpt' => __( '7 reasons Muslims say the God of the Quran and the God of the Bible are one, each answered against the objection. Read them.', 'bestofislam' ),
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
		array(
			'slug'    => 'where-is-the-injil',
			'title'   => __( 'Where is the Injil? 5 answers to the question critics ask most', 'bestofislam' ),
			'excerpt' => __( 'Critics say: show us the Injil, or admit it never existed. 5 answers to what the question assumes. Read the case.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'paul-and-the-law',
			'title'   => __( 'Paul and the Law: 6 verses that sit badly with Matthew 5:17', 'bestofislam' ),
			'excerpt' => __( 'Did Paul abolish the Law, or only explain it? 6 of his own verses set beside Jesus in Matthew 5. Read them and judge.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'paul-claim-to-apostleship',
			'title'   => __( 'Was Paul an apostle? 6 questions about his claim to speak for Jesus', 'bestofislam' ),
			'excerpt' => __( 'Paul never met Jesus and said so. 6 questions about a vision, three conflicting accounts of it, and the apostles who did not accept him. Read them.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'pauline-letters-authorship',
			'title'   => __( 'Which letters did Paul write? 6 facts about the 13 epistles that carry his name', 'bestofislam' ),
			'excerpt' => __( 'Thirteen New Testament letters say they are from Paul. Critical scholars accept 7. 6 facts on how the rest were judged, and why it matters. Read them.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'ezra-and-mary',
			'title'   => __( 'Ezra and Mary in the Quran: 5 points on two contested verses', 'bestofislam' ),
			'excerpt' => __( 'Critics say no Jews called Ezra son of God and no Christians put Mary in the Trinity. 5 points on both verses. Read them.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'aisha-sheep-report-stoning-and-suckling-verses',
			'title'   => __( 'The sheep that ate the verse: 5 points on A\'isha\'s report', 'bestofislam' ),
			'excerpt' => __( 'One narrator, a disputed grade and two classical replies: 5 points on the report of the sheet under A\'isha\'s pillow.', 'bestofislam' ),
			'topic'   => 'faith-and-reason',
		),
		array(
			'slug'    => 'seven-ahruf-cairo-edition-stoning-verse',
			'title'   => __( 'The seven ahruf, the 1924 Cairo edition and the stoning verse: 10 points', 'bestofislam' ),
			'excerpt' => __( 'Seven modes versus seven readings, the ten readings, the personal codices, the conditions for an accepted reading, the Cairo printing and the stoning verse. 10 points.', 'bestofislam' ),
			'topic'   => 'faith-and-reason',
		),
		array(
			'slug'    => 'uthman-copies',
			'title'   => __( 'Uthman\'s copies: 5 facts about the Quran\'s earliest manuscripts', 'bestofislam' ),
			'excerpt' => __( 'Where are Uthman\'s copies of the Quran? 5 facts on the earliest manuscripts, from Birmingham to Samarkand. Read them.', 'bestofislam' ),
			'topic'   => 'history',
		),
		array(
			'slug'    => 'qibla-change',
			'title'   => __( 'Jerusalem, then Mecca: 5 facts about the change of qibla', 'bestofislam' ),
			'excerpt' => __( 'The first Muslims prayed towards Jerusalem. Critics call that a problem. 5 facts about the change of qibla. Read them.', 'bestofislam' ),
			'topic'   => 'history',
		),
		array(
			'slug'    => 'why-five-prayers',
			'title'   => __( 'Why five prayers? 5 answers on what the Quran says and what it leaves to the Prophet', 'bestofislam' ),
			'excerpt' => __( 'Show me five daily prayers from the Quran alone, critics say. 5 answers on the Quran, the Prophet and practice. Read on.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'who-saw-gabriel',
			'title'   => __( 'Who saw Gabriel? 5 answers on witnesses to revelation', 'bestofislam' ),
			'excerpt' => __( 'Nobody was in the cave with Muhammad, critics say, so why believe it was Gabriel? 5 answers on witnesses. Read the case.', 'bestofislam' ),
			'topic'   => 'faith-and-reason',
		),
		array(
			'slug'    => 'fastest-growing-religion',
			'title'   => __( 'Islam is the world\'s fastest-growing religion: 6 figures from the latest data', 'bestofislam' ),
			'excerpt' => __( 'Islam grew faster than any other religion from 2010 to 2020. 6 figures from Pew\'s latest data, and what drives them. Read on.', 'bestofislam' ),
			'topic'   => 'the-muslim-world',
		),
		array(
			'slug'    => 'arabic-words-science',
			'title'   => __( '6 words science still uses that came from Arabic', 'bestofislam' ),
			'excerpt' => __( 'Algebra, algorithm, zenith, azimuth, nadir, alcohol: 6 scientific words from Arabic, and how each crossed over. Read on.', 'bestofislam' ),
			'topic'   => 'science',
		),
		array(
			'slug'    => 'second-peter',
			'title'   => __( 'Written in Peter\'s name: 5 things a Catholic Bible says about 2 Peter', 'bestofislam' ),
			'excerpt' => __( 'The Catholic bishops\' own Bible says 2 Peter was likely written in Peter\'s name by a later author. 5 admissions. Read them.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'moon-craters',
			'title'   => __( '6 Muslim astronomers whose names are on the Moon', 'bestofislam' ),
			'excerpt' => __( 'Albategnius, Azophi, Arzachel: 6 lunar craters named for Muslim astronomers, and the work that earned each one. Read on.', 'bestofislam' ),
			'topic'   => 'science',
		),
		array(
			'slug'    => 'indonesia',
			'title'   => __( '6 facts about Indonesia, the country with the most Muslims', 'bestofislam' ),
			'excerpt' => __( 'Indonesia has more Muslims than any other country, and it is not an Islamic state. 6 facts about the nation. Read on.', 'bestofislam' ),
			'topic'   => 'the-muslim-world',
		),
		array(
			'slug'    => 'ex-muslim-blog-study',
			'title'   => __( 'How an ex-Muslim blog persuades: 5 findings from a Malaysian study', 'bestofislam' ),
			'excerpt' => __( 'A Malaysian study analysed how an ex-Muslim blog won readers, and which replies failed. 5 findings for Muslims. Read on.', 'bestofislam' ),
			'topic'   => 'the-muslim-world',
		),
		array(
			'slug'    => 'kaaba-witnesses',
			'title'   => __( 'The Kaaba before Islam: 5 ancient witnesses, weighed', 'bestofislam' ),
			'excerpt' => __( 'Diodorus, Ptolemy, a new inscription, a Psalm and the Quran: 5 witnesses to the Kaaba, each weighed. Read on.', 'bestofislam' ),
			'topic'   => 'history',
		),
		array(
			'slug'    => 'dome-inscriptions',
			'title'   => __( 'The Dome of the Rock: 6 things its inscriptions tell us about the Quran', 'bestofislam' ),
			'excerpt' => __( 'Dated 72 AH, the Dome of the Rock quotes the Quran Muslims recite today. 6 things its inscriptions show. Read on.', 'bestofislam' ),
			'topic'   => 'arts-and-culture',
		),
		array(
			'slug'    => 'library-of-alexandria',
			'title'   => __( 'Who burned the Library of Alexandria? 5 facts behind the story', 'bestofislam' ),
			'excerpt' => __( 'Did the caliph ʿUmar order the Library of Alexandria burned? 5 facts from the sources behind the story. Read on.', 'bestofislam' ),
			'topic'   => 'history',
		),
		array(
			'slug'    => 'jizya',
			'title'   => __( 'What was the jizya? 6 facts about the tax', 'bestofislam' ),
			'excerpt' => __( 'What was the jizya tax? Who paid it, how much, why, and what its critics say. 6 facts from the sources. Read on.', 'bestofislam' ),
			'topic'   => 'history',
		),
		array(
			'slug'    => 'quran-preservation-reddit',
			'title'   => __( 'Quran preservation on Reddit: 5 points from the scholars\' debate', 'bestofislam' ),
			'excerpt' => __( 'Was the Quran preserved? What Reddit\'s academic Quran forum concluded, and where Muslims should agree. Read on.', 'bestofislam' ),
			'topic'   => 'history',
		),
		array(
			'slug'    => 'apostasy-in-islam',
			'title'   => __( 'Apostasy in Islam: 6 facts about the death penalty debate', 'bestofislam' ),
			'excerpt' => __( 'Does Islam demand death for leaving it? What the Quran, the hadith, the jurists and the surveys say. 6 facts. Read on.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'crucifixion-quran',
			'title'   => __( 'The crucifixion and the Quran: 6 facts about Quran 4:157', 'bestofislam' ),
			'excerpt' => __( 'Was Jesus crucified? What historians hold, what Quran 4:157 says, and why Islam rejects atonement by proxy. Read on.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'six-articles-of-faith',
			'title'   => __( 'The 6 articles of faith, explained without the jargon', 'bestofislam' ),
			'excerpt' => __( 'The 6 beliefs every Muslim shares, from one God to the Last Day, each explained plainly and traced to its source. Read the list.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'fate-and-free-will',
			'title'   => __( 'Fate and free will in Islam: 6 questions answered', 'bestofislam' ),
			'excerpt' => __( 'If God knows everything, are we free? Six questions about qadar, answered from the Quran, the hadith and the scholars. Read on.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'end-of-time',
			'title'   => __( '5 things Muslims believe about the end of time', 'bestofislam' ),
			'excerpt' => __( 'The Hour, its signs, the Dajjal, the Mahdi and intercession: 5 things to know about the Islamic end times, and what the Quran leaves out.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'blasphemy-and-insults-to-the-prophet',
			'title'   => __( 'Blasphemy and insults to the Prophet: 4 facts', 'bestofislam' ),
			'excerpt' => __( 'What the Quran says about mockery, how the Prophet answered insults, what Hanafi law held and where Pakistan\'s law began. 4 facts.', 'bestofislam' ),
			'topic'   => 'islamophobia',
		),
		array(
			'slug'    => 'sharia-and-hudud',
			'title'   => __( '5 myths about sharia and the hudud punishments', 'bestofislam' ),
			'excerpt' => __( 'Is sharia a fixed code and the hudud its core? 5 myths about Islamic law, answered from the sources and the jurists. Read on.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'quranic-ethics',
			'title'   => __( '7 Quranic ethics that still hold today', 'bestofislam' ),
			'excerpt' => __( 'Justice, honest dealing, kindness to parents, keeping promises: 7 ethical commands from the Quran and what they ask. Read the list.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'why-no-interest',
			'title'   => __( '5 reasons Islam forbids interest', 'bestofislam' ),
			'excerpt' => __( 'Why does the Quran forbid riba? 5 reasons given by the sources and the scholars, and what Islamic finance does instead. Read on.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'women-in-the-prophets-life',
			'title'   => __( '7 women in the Prophet\'s life who shaped Islam', 'bestofislam' ),
			'excerpt' => __( 'Aminah, Khadija, Fatima, Umm Salama, Aisha, Hafsa and Mariya: 7 women in the Prophet\'s life and what each one did. Read their stories.', 'bestofislam' ),
			'topic'   => 'history',
		),
		array(
			'slug'    => 'companions-who-started-with-nothing',
			'title'   => __( '6 Companions of the Prophet who started with nothing', 'bestofislam' ),
			'excerpt' => __( 'Bilal, Sumayya and Ammar, Salman, Abu Dharr, Abu Hurayra and Abdul Rahman ibn Awf: 6 early Muslims who began with nothing. Read their stories.', 'bestofislam' ),
			'topic'   => 'history',
		),
		array(
			'slug'    => 'questions-about-aisha',
			'title'   => __( '5 questions about Aisha, answered from the sources', 'bestofislam' ),
			'excerpt' => __( 'How old was Aisha, did she consent, and what else do the sources say about her? 5 questions answered, with the evidence on each side. Read on.', 'bestofislam' ),
			'topic'   => 'history',
		),
		array(
			'slug'    => 'child-marriage-in-islam',
			'title'   => __( 'Child marriage and Islam: 5 facts', 'bestofislam' ),
			'excerpt' => __( 'Does the Quran allow child marriage? 5 facts about what the sources say, what the jurists held, and what Muslim countries do today.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'does-the-quran-allow-wife-beating',
			'title'   => __( 'Quran 4:34: 5 facts about the verse on striking wives', 'bestofislam' ),
			'excerpt' => __( 'Does the Quran tell men to beat their wives? 5 facts on Quran 4:34, the Prophet\'s practice and how scholars read it. Read on.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'slavery-in-islam',
			'title'   => __( 'Slavery in Islam: 6 facts', 'bestofislam' ),
			'excerpt' => __( 'Did Islam ban slavery? 6 facts on what the Quran, the Prophet and the jurists said, and what scholars say now. Read on.', 'bestofislam' ),
			'topic'   => 'history',
		),
		array(
			'slug'    => 'homosexuality-in-islam',
			'title'   => __( 'Homosexuality in Islam: 5 facts', 'bestofislam' ),
			'excerpt' => __( 'What does Islam teach about homosexuality? 5 facts on the story of Lot, the law, the proof required and the line between act and orientation.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'hijab-and-modesty',
			'title'   => __( 'Hijab and modesty in Islam: 5 facts', 'bestofislam' ),
			'excerpt' => __( 'Is hijab commanded, and who decides? 5 facts on Quran 24:31 and 33:59, what scholars say, and the principle of no compulsion.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'houris-and-paradise',
			'title'   => __( 'The houris and the Quran\'s Paradise: 5 facts', 'bestofislam' ),
			'excerpt' => __( 'Do 72 virgins await martyrs? 5 facts about the houris, what the Quran says about Paradise and what no eye has seen. Read on.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'war-and-killing-in-islam',
			'title'   => __( 'War, killing and execution in Islam: 5 facts', 'bestofislam' ),
			'excerpt' => __( 'What does Islam say about killing, beheading and genocide? 5 facts on Quran 5:32 and 47:4, the rules of war and what scholars have condemned.', 'bestofislam' ),
			'topic'   => 'islamophobia',
		),
		array(
			'slug'    => 'quran-science-claims-graded',
			'title'   => __( '7 scientific claims about the Quran, graded on the evidence', 'bestofislam' ),
			'excerpt' => __( 'Embryology, the water cycle, iron, fingerprints and more: 7 popular scientific claims about the Quran, each graded for how well it holds up.', 'bestofislam' ),
			'topic'   => 'science',
		),
		array(
			'slug'    => 'natural-world-verses',
			'title'   => __( '5 verses about the natural world and how scholars read them', 'bestofislam' ),
			'excerpt' => __( 'Shooting stars, mountains, figs, earthquakes and the meeting of two seas: 5 Quranic verses on nature and how scholars have read them.', 'bestofislam' ),
			'topic'   => 'science',
		),
		array(
			'slug'    => 'quranic-stories-and-history',
			'title'   => __( '6 Quranic stories and what other sources say', 'bestofislam' ),
			'excerpt' => __( 'Thamud, Ad, the Sleepers, Dhu al-Qarnayn, the Elephant and Jonah: 6 Quranic stories checked against other sources. Read what survives.', 'bestofislam' ),
			'topic'   => 'history',
		),
		array(
			'slug'    => 'prophets-you-may-not-know',
			'title'   => __( '5 prophets in the Quran you may not know', 'bestofislam' ),
			'excerpt' => __( 'Idris, Hud, Dhul-Kifl, Elisha and Elijah: 5 lesser-known prophets of the Quran and what the text says about each. Read on.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'quran-mysteries-in-the-stories',
			'title'   => __( '5 unsolved puzzles in the Quran\'s stories', 'bestofislam' ),
			'excerpt' => __( 'Khidr, Gog and Magog, the Companions of the Cave, Harut and Marut and the People of the Rass: 5 puzzles in the Quran\'s stories, explained.', 'bestofislam' ),
			'topic'   => 'history',
		),
		array(
			'slug'    => 'gods-attributes-and-anthropomorphism',
			'title'   => __( 'God\'s hand, face and throne in the Quran: 7 points', 'bestofislam' ),
			'excerpt' => __( 'Does the Quran describe a God with a body? 7 points on 42:11, the verses of hand and face, Malik\'s reply and the classical schools. Read on.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'temporary-marriage-mutah',
			'title'   => __( 'Mutah, temporary marriage: 6 points from the sources', 'bestofislam' ),
			'excerpt' => __( 'Was mutah ever allowed, who forbade it, and what does Quran 4:24 say? 6 points from the Quran, hadith and Ibn Hajar\'s survey. Read on.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'do-muslims-worship-muhammad',
			'title'   => __( 'Do Muslims worship Muhammad? 7 points from the sources', 'bestofislam' ),
			'excerpt' => __( 'The Quran calls him a servant, the Prophet refused exaggerated praise, and Abu Bakr answered the charge on the day he died. 7 points. Read on.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'crusades-5-facts',
			'title'   => __( '6 facts about the Crusades that get lost', 'bestofislam' ),
			'excerpt' => __( 'Who started the Crusades, what happened in Jerusalem in 1099 and 1187, and why Eastern Christians suffered too: 6 facts. Read on.', 'bestofislam' ),
			'topic'   => 'history',
		),
		array(
			'slug'    => 'muslims-by-the-numbers-2020',
			'title'   => __( 'Muslims by the numbers: 7 facts from the 2020 count', 'bestofislam' ),
			'excerpt' => __( 'How many Muslims are there, where do they live, and what share of Europe do they make up? 7 facts from Pew\'s 2020 count. Read on.', 'bestofislam' ),
			'topic'   => 'the-muslim-world',
		),
		array(
			'slug'    => 'islam-in-china',
			'title'   => __( 'Islam in China: 5 facts about a 1,300-year-old community', 'bestofislam' ),
			'excerpt' => __( 'Where did Chinese Muslims come from, what are the Hui, and what was the Panthay rebellion? 5 facts on Islam in China. Read the list.', 'bestofislam' ),
			'topic'   => 'the-muslim-world',
		),
		array(
			'slug'    => 'kharijites-and-takfir',
			'title'   => __( 'The Kharijites and takfir: 6 facts', 'bestofislam' ),
			'excerpt' => __( 'Who were the Kharijites, what did the Prophet say about them, and why do mainstream scholars refuse takfir? 6 facts. Read on.', 'bestofislam' ),
			'topic'   => 'history',
		),
		array(
			'slug'    => 'taqiyya-5-myths',
			'title'   => __( '5 myths about taqiyya', 'bestofislam' ),
			'excerpt' => __( 'Does Islam permit Muslims to lie to unbelievers? 5 myths about taqiyya, answered from the Quran and the hadith. Read on.', 'bestofislam' ),
			'topic'   => 'islamophobia',
		),
		array(
			'slug'    => 'before-your-first-friday-prayer',
			'title'   => __( '7 things to know before your first Friday prayer', 'bestofislam' ),
			'excerpt' => __( 'Visiting a mosque on Friday for the first time? 7 things to know, from the call to prayer to the sermon, the washing and where to stand.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'hajj-rites-explained',
			'title'   => __( '6 rites of Hajj and what they mean', 'bestofislam' ),
			'excerpt' => __( 'Niyya, ihram, tawaf, the standing at Arafat, sacrifice and stoning: 6 rites of the pilgrimage to Mecca and the story behind each.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'ramadan-last-ten-nights',
			'title'   => __( 'The last ten nights of Ramadan: 5 things to know', 'bestofislam' ),
			'excerpt' => __( 'Laylat al-Qadr, itikaf, the odd nights and the Prophet\'s own practice: 5 things to know about the last ten nights of Ramadan.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'funeral-dhikr-dua',
			'title'   => __( '6 practices of remembrance, supplication and farewell', 'bestofislam' ),
			'excerpt' => __( 'The Muslim funeral, dhikr and dua: 6 practices Muslims turn to at the end of life and in daily life, and what each one asks. Read on.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'scholars-who-shaped-islamic-study',
			'title'   => __( '7 scholars who shaped how Islam is studied', 'bestofislam' ),
			'excerpt' => __( 'Ibn Abbas, Ibn Ishaq, Abu Dawud, Hunayn ibn Ishaq, al-Kindi, al-Ash\'ari and al-Ghazali: 7 scholars whose work still frames Islamic learning.', 'bestofislam' ),
			'topic'   => 'history',
		),
		array(
			'slug'    => 'western-scholars-of-islam',
			'title'   => __( '5 Western scholars of Islam and where Muslims differ with them', 'bestofislam' ),
			'excerpt' => __( 'Geiger, Caetani, Jeffery, Goitein and Armstrong: 5 Western scholars who shaped the study of Islam, what they argued and where Muslims disagree.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'islamic-art-and-the-one-god',
			'title'   => __( '5 ideas behind Islamic art, and what the arabesque is for', 'bestofislam' ),
			'excerpt' => __( 'Why Islamic art avoids images of God, what stylisation and geometry do, and what the arabesque is for. 5 ideas that explain the look of a mosque.', 'bestofislam' ),
			'topic'   => 'arts-and-culture',
		),
		array(
			'slug'    => 'mosques-adapt-to-place',
			'title'   => __( '5 mosques that show how Islamic architecture adapts to place', 'bestofislam' ),
			'excerpt' => __( 'Medina, Cordoba, Xi\'an, Djenne and Istanbul: 5 mosques that show how one simple idea of a house of prayer takes the shape of each land.', 'bestofislam' ),
			'topic'   => 'arts-and-culture',
		),
		array(
			'slug'    => 'islamophobia-5-facts',
			'title'   => __( 'Islamophobia: 5 facts about the word and the harm', 'bestofislam' ),
			'excerpt' => __( 'What does Islamophobia mean, how is it different from criticism, and what harm does it do? 5 facts, and what the Quran asks of Muslims in return.', 'bestofislam' ),
			'topic'   => 'islamophobia',
		),
		array(
			'slug'    => 'islamisation-of-the-west',
			'title'   => __( '6 facts behind the "Islamisation of the West" claim', 'bestofislam' ),
			'excerpt' => __( 'Sharia in Dearborn, the Ground Zero mosque, CAIR as a front group, the demographic numbers, and a school prayer room: 6 claims checked against the record, and the theory tying them together.', 'bestofislam' ),
			'topic'   => 'islamophobia',
		),
		array(
			'slug'    => 'muslim-societies-backwardness-claim',
			'title'   => __( '5 facts behind the claim that Muslim-majority countries are "backward"', 'bestofislam' ),
			'excerpt' => __( 'Low scores on development, freedom and women\'s rights get pinned on Islam itself. 5 facts about what actually predicts them, from oil rents to colonial timing, and what does not.', 'bestofislam' ),
			'topic'   => 'the-muslim-world',
		),
		array(
			'slug'    => 'black-stone-and-kissing',
			'title'   => __( '5 facts about the Black Stone and why Muslims touch it', 'bestofislam' ),
			'excerpt' => __( 'What the Black Stone is, what the rite involves, what Umar said and what Islam counts as idolatry: 5 facts about the stone at the Kaaba.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'women-in-islamic-law-testimony-inheritance',
			'title'   => __( '6 facts about women, testimony, inheritance and marriage in Islamic law', 'bestofislam' ),
			'excerpt' => __( 'Two women witnesses, the half share, polygamy, honour killing, genital cutting and consent: what the texts say.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'fly-hadith-and-adult-suckling',
			'title'   => __( 'The fly hadith and the adult suckling report: 4 points', 'bestofislam' ),
			'excerpt' => __( 'The fly in the drink and Sahla\'s nursing of Salim, read in full with what the Prophet\'s wives and the jurists made of them. 4 points.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'camel-urine-hadith',
			'title'   => __( 'The camel urine hadith: 5 things the report says', 'bestofislam' ),
			'excerpt' => __( 'The hadith of the men of Ukl and Uraynah, read in full: what it says, what it does not command and what scholars make of it. 5 points.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'quran-grammar-errors-claim',
			'title'   => __( '4 facts about the claim of grammatical errors in the Quran', 'bestofislam' ),
			'excerpt' => __( 'The challenge to match the Quran, the report from A\'isha and the verses critics cite, 4:162 and 20:63, read with al-Tabari.', 'bestofislam' ),
			'topic'   => 'faith-and-reason',
		),
		array(
			'slug'    => 'who-wrote-the-quran',
			'title'   => __( 'Who wrote the Quran? 5 answers to the authorship claim', 'bestofislam' ),
			'excerpt' => __( 'Revelation or composition? How the claim was argued from the start, how the text was fixed and what each side can and cannot show. 5 answers.', 'bestofislam' ),
			'topic'   => 'faith-and-reason',
		),
		array(
			'slug'    => 'quran-contradictions-claims',
			'title'   => __( 'Contradictions in the Quran? 4 points on the cases critics cite', 'bestofislam' ),
			'excerpt' => __( 'Six days or eight, a thousand years or fifty thousand, and who takes the soul: the classical answers. 4 points. Read on.', 'bestofislam' ),
			'topic'   => 'faith-and-reason',
		),
		array(
			'slug'    => 'preservation-and-truth',
			'title'   => __( 'Does a preserved text prove it is true? 5 answers', 'bestofislam' ),
			'excerpt' => __( 'Accurate copying shows continuity, not truth. 5 answers on what preservation establishes for the Quran and for other scriptures.', 'bestofislam' ),
			'topic'   => 'faith-and-reason',
		),
		array(
			'slug'    => 'relationship-with-god',
			'title'   => __( 'Do Muslims have a relationship with God? 5 answers', 'bestofislam' ),
			'excerpt' => __( 'Nearness, forgiveness without a mediator, the names of God and what an experience can prove: 6 answers on how Muslims relate to God.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'buraq-and-miracle-stories',
			'title'   => __( '5 miracle stories critics call a fairy tale, and what the sources say', 'bestofislam' ),
			'excerpt' => __( 'The Night Journey, the Buraq, a talking donkey and the Bible\'s own miracles: 5 miracle stories critics mock, and what the sources say.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'raids-badr-and-the-conquest-of-mecca',
			'title'   => __( 'Muhammad\'s raids, Badr and the conquest of Mecca: 6 facts', 'bestofislam' ),
			'excerpt' => __( 'The caravan raids, the battle at Badr and the entry into Mecca: 6 facts from the early sources, with the critics\' strongest points conceded.', 'bestofislam' ),
			'topic'   => 'history',
		),
		array(
			'slug'    => 'revisionist-case-weighed',
			'title'   => __( 'The revisionist case: 6 answers on manuscripts, coins and Mecca', 'bestofislam' ),
			'excerpt' => __( 'Early manuscripts, coins, inscriptions and the location of Mecca: 6 answers on what the revisionist case shows and what it does not.', 'bestofislam' ),
			'topic'   => 'faith-and-reason',
		),
		array(
			'slug'    => 'medina-jewish-tribes-and-qurayza',
			'title'   => __( '6 facts about Medina\'s Jewish tribes and Banu Qurayza', 'bestofislam' ),
			'excerpt' => __( 'The Constitution of Medina, the exile of two tribes and the fate of Banu Qurayza: 6 facts from the earliest reports.', 'bestofislam' ),
			'topic'   => 'history',
		),
		array(
			'slug'    => 'first-revelation-demonic-claim',
			'title'   => __( 'Was the first revelation demonic? 6 answers from the sources', 'bestofislam' ),
			'excerpt' => __( 'The cave of Hira, Khadija and Waraqa, and the report of fear: 6 answers on the claims of demonic origin and epilepsy.', 'bestofislam' ),
			'topic'   => 'faith-and-reason',
		),
		array(
			'slug'    => 'islamic-rites-before-islam',
			'title'   => __( 'Which rites existed before Islam? 6 answers from the sources', 'bestofislam' ),
			'excerpt' => __( 'Pilgrimage, the Black Stone, fasting and prayer: 6 answers on which rites pre-date Islam and what the Quran says about them.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'terrorism-and-muslims-the-data',
			'title'   => __( 'Terrorism and Muslims: 5 facts about the data and the texts', 'bestofislam' ),
			'excerpt' => __( 'What the casualty data, the polling and the texts show about terrorism and Muslims: 5 facts, with the limits of each source.', 'bestofislam' ),
			'topic'   => 'islamophobia',
		),
		array(
			'slug'    => 'magic-jinn-and-satan-hadiths',
			'title'   => __( 'Magic, jinn and Satan hadiths: 6 reports that critics cite', 'bestofislam' ),
			'excerpt' => __( 'The spell on the Prophet, the jinn and the devil\'s touch: 6 reports that critics cite, read with their grading and context.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'sira-ibn-ishaq-hisham-tabari',
			'title'   => __( 'Ibn Ishaq, Ibn Hisham, Tabari: how reliable is the Sira? 5 facts', 'bestofislam' ),
			'excerpt' => __( 'How the biography of the Prophet reached us, what each compiler did and how historians weigh it: 5 facts.', 'bestofislam' ),
			'topic'   => 'faith-and-reason',
		),
		array(
			'slug'    => 'prophets-marriages-zaynab-and-others',
			'title'   => __( '6 facts about Muhammad\'s marriages: Zaynab, Safiyya and others', 'bestofislam' ),
			'excerpt' => __( 'Zaynab, Safiyya, Juwayriyah, Rayhana, Mariya and verse 33:50: 6 facts from the Quran, hadith and early biographies.', 'bestofislam' ),
			'topic'   => 'history',
		),
		array(
			'slug'    => 'apes-and-pigs-and-the-jews',
			'title'   => __( 'Apes and pigs and the Jews in the Quran and hadith: 6 facts', 'bestofislam' ),
			'excerpt' => __( 'What the verses on apes and pigs say, who they address, and how commentators read them: 6 facts.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'satanic-verses-report',
			'title'   => __( 'The Satanic Verses report: 5 facts about a disputed story', 'bestofislam' ),
			'excerpt' => __( 'The chains, the grading and the Quranic text: 5 facts about the report of the Satanic Verses.', 'bestofislam' ),
			'topic'   => 'faith-and-reason',
		),
		array(
			'slug'    => 'abrogation-and-the-sword-verse',
			'title'   => __( 'Abrogation (naskh) and the Sword Verse: 5 facts to know', 'bestofislam' ),
			'excerpt' => __( 'What naskh means, how many verses scholars say it covers and how the Sword Verse is read: 5 facts.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'end-of-the-world-1110-claim',
			'title'   => __( 'Did Muhammad predict the end in 1110? 5 facts on the reports', 'bestofislam' ),
			'excerpt' => __( 'The reports about the end of the world and the 1110 claim, traced to their sources: 5 facts.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'race-in-quran-and-hadith',
			'title'   => __( 'Five answers on race in the Quran and the hadith reports critics cite', 'bestofislam' ),
			'excerpt' => __( 'What the Quran says about colour and descent, and how the reports critics cite are graded: 5 answers.', 'bestofislam' ),
			'topic'   => 'islamophobia',
		),
		array(
			'slug'    => 'quran-on-christians',
			'title'   => __( 'What the Quran says about Christians and Christianity: 7 answers', 'bestofislam' ),
			'excerpt' => __( 'Praise, disagreement and the command to dialogue: 7 answers on what the Quran says about Christians.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'fighting-verses-in-context',
			'title'   => __( 'The fighting verses in context: 6 facts from the sources', 'bestofislam' ),
			'excerpt' => __( 'Nine war verses are quoted against Islam. The setting, the classical commentary and the limits each verse carries, with fair concessions.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'first-muslims-kept-the-faith',
			'title'   => __( '5 facts about why the first Muslims kept the faith in Mecca', 'bestofislam' ),
			'excerpt' => __( 'Refused offers of wealth, a small but growing community, boycott and exile: 5 facts that fit poorly with an invented scheme.', 'bestofislam' ),
			'topic'   => 'history',
		),
		array(
			'slug'    => 'quran-anachronism-claims',
			'title'   => __( '6 answers to the claim that the Quran gets its history wrong', 'bestofislam' ),
			'excerpt' => __( 'Six alleged historical slips, from Nimrod and Talut to Job and Pharaoh\'s punishment, checked against what the verses say and what commentators added.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'jihad-meaning-quran-hadith-jurists',
			'title'   => __( '5 facts about jihad in the Quran, the hadith and the jurists', 'bestofislam' ),
			'excerpt' => __( 'Jihad means effort, and in Medinan verses and the law books often fighting; 5 facts on the ranking hadith and the jurists.', 'bestofislam' ),
			'topic'   => 'belief-and-practices',
		),
		array(
			'slug'    => 'talmud-midrash-apocrypha-borrowing',
			'title'   => __( 'Talmud, midrash and apocrypha: 6 answers on borrowing claims', 'bestofislam' ),
			'excerpt' => __( 'Six answers on the claimed Jewish and Christian sources of Quranic stories: dates, direction of influence and the Muslim response.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'is-yahweh-satan',
			'title'   => __( 'Is Yahweh Satan? 7 troubling biblical parallels that raise serious questions', 'bestofislam' ),
			'excerpt' => __( 'Is Yahweh Satan? 7 biblical parallels, from David\'s census to the lying spirit, weighed against the text. Read them.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'who-was-yahweh-archaeological-origins',
			'title'   => __( 'Who was Yahweh? 7 archaeological discoveries that challenge traditional beliefs', 'bestofislam' ),
			'excerpt' => __( 'The origin of Yahweh in 7 finds, from the Shasu of Yhw to Kuntillet Ajrud and the Mesha Stele, and their limits. Read them.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'was-yahweh-one-of-many-gods',
			'title'   => __( 'Was Yahweh originally one of many gods? 7 clues from ancient Israelite religion', 'bestofislam' ),
			'excerpt' => __( 'Ancient Israelite polytheism in 7 clues: El, Ugarit, Deuteronomy 32, Psalm 82 and the rise of monotheism. Read them.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'early-christians-yahweh-evil-gnosticism-marcion',
			'title'   => __( 'Did early Christians believe Yahweh was evil? 7 revelations from Gnosticism and Marcion', 'bestofislam' ),
			'excerpt' => __( 'Gnosticism and Yahweh: Marcion, Yaldabaoth and the Demiurge, and who, if anyone, made the creator Satan. Read them.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'is-yahweh-the-same-as-allah',
			'title'   => __( 'Is Yahweh the same as Allah? 7 questions from the Quran, the Bible and history', 'bestofislam' ),
			'excerpt' => __( 'Yahweh vs Allah: 7 questions on the name, tawhid, mercy, Iblis and Jesus, and why a new portrait is not a new God. Read them.', 'bestofislam' ),
			'topic'   => 'comparative-religion',
		),
		array(
			'slug'    => 'hanif-poets-and-musaylima',
			'title'   => __( '5 facts about the Hanif poets, Musaylima and the Quran', 'bestofislam' ),
			'excerpt' => __( 'Zayd ibn Amr, Waraqa and Musaylima are said to have supplied the Quran\'s early content; the dates and surviving texts show otherwise.', 'bestofislam' ),
			'topic'   => 'history',
		),
		array(
			'slug'    => 'schacht-isnads-dating-hadith',
			'title'   => __( 'Schacht, isnads and the dating of hadith: 8 answers', 'bestofislam' ),
			'excerpt' => __( 'Hadith were forged and Muslim scholars said so first, but writing began early, chains were tested and the quoted figures mislead.', 'bestofislam' ),
			'topic'   => 'faith-and-reason',
		),
		array(
			'slug'    => 'was-muhammad-illiterate-ummi',
			'title'   => __( 'Was Muhammad illiterate? 5 answers on ummi, Iqra and clots', 'bestofislam' ),
			'excerpt' => __( 'What ummi can mean, what Quran 29:48 claims, what Iqra asked, what alaq means and how the reports of his writing are weighed.', 'bestofislam' ),
			'topic'   => 'faith-and-reason',
		),
		array(
			'slug'    => 'ridda-wars-abu-bakr-fadak',
			'title'   => __( '5 facts about Abu Bakr, the Ridda wars and the Fadak dispute', 'bestofislam' ),
			'excerpt' => __( 'Zakah, rival prophets and tribal secession all fed the Ridda wars, and Sunni and Shia sources tell the Fadak story differently.', 'bestofislam' ),
			'topic'   => 'history',
		),
	);
}

/**
 * Loads an article's block markup from the content directory.
 *
 * Articles are stored as JSON, each holding its block markup under a
 * "content" key, rather than as raw .html files.
 *
 * @param string $slug Article slug.
 * @return string Block markup, or an empty string when the file is missing
 *                or malformed.
 */
function boi_load_article( $slug ) {
	$path = BOI_DIR . '/content/' . sanitize_file_name( $slug ) . '.json';

	if ( ! file_exists( $path ) ) {
		return '';
	}

	$raw  = (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$data = json_decode( $raw, true );

	if ( ! is_array( $data ) || ! isset( $data['content'] ) || ! is_string( $data['content'] ) ) {
		return '';
	}

	return $data['content'];
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

			// A section that was once nested under another is promoted when the seed says so.
			$current = get_term( $map[ $slug ], 'listicle-topic' );
			$parent  = ( '' !== $topic['parent'] && isset( $map[ $topic['parent'] ] ) ) ? $map[ $topic['parent'] ] : 0;

			if ( $current && ! is_wp_error( $current ) && (int) $current->parent !== $parent && '' === $topic['parent'] ) {
				wp_update_term( $map[ $slug ], 'listicle-topic', array( 'parent' => 0 ) );
			}
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
 * Seeded article slugs that have changed, old => current. Addresses carry
 * the subject, never a verse number or a collision suffix.
 *
 * @return array
 */
function boi_seed_renames() {
	return (array) apply_filters(
		'boi_seed_renames',
		array(
			'quran-4-34' => 'does-the-quran-allow-wife-beating',
		)
	);
}

/**
 * Renames a published article's slug in place when the seed list has moved
 * on, so the post keeps its identity, comments and attachments. The theme
 * does not redirect the old address, so the note WordPress keeps of the old
 * slug for its own redirect is removed; the fingerprint that tells an
 * untouched article from an edited one moves with the post.
 *
 * @return void
 */
function boi_rename_seeded_posts() {
	foreach ( boi_seed_renames() as $old => $new ) {
		$post    = get_page_by_path( $old, OBJECT, 'post' );
		$renamed = get_page_by_path( $new, OBJECT, 'post' );

		// A post renamed by an earlier release still carries the old-slug note
		// WordPress keeps for its own redirect; the theme does not redirect.
		if ( $renamed ) {
			delete_post_meta( $renamed->ID, '_wp_old_slug', $old );
		}

		if ( ! $post || $renamed ) {
			continue;
		}

		boi_update_post(
			array(
				'ID'        => $post->ID,
				'post_name' => $new,
			)
		);
		delete_post_meta( $post->ID, '_wp_old_slug' );

		$hashes = (array) get_option( 'boi_article_hashes', array() );

		if ( isset( $hashes[ $old ] ) ) {
			$hashes[ $new ] = $hashes[ $old ];
			unset( $hashes[ $old ] );
			update_option( 'boi_article_hashes', $hashes, false );
		}
	}
}

/**
 * Creates the seed listicles.
 *
 * @param array $topics Map of topic slug to term identifier.
 * @return array Created post identifiers.
 */
function boi_create_listicles( $topics ) {
	$created = array();

	boi_rename_seeded_posts();

	foreach ( boi_seed_listicles() as $listicle ) {
		if ( get_page_by_path( $listicle['slug'], OBJECT, 'post' ) ) {
			continue;
		}

		$content = boi_load_article( $listicle['slug'] );

		if ( '' === $content ) {
			continue;
		}

		$post_id = boi_insert_post(
			array(
				'post_title'   => $listicle['title'],
				'post_name'    => $listicle['slug'],
				'post_excerpt' => $listicle['excerpt'],
				'post_content' => $content,
				'post_status'  => isset( $listicle['status'] ) ? $listicle['status'] : 'publish',
				'post_author'  => boi_seed_author(),
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
 * Standing page definitions: title and content, keyed by role.
 *
 * @return array
 */
function boi_seed_pages() {
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
				. $p( __( 'The site is written and edited by Mohd Elfie Nieshaem Juferi, who has written on Islam and Christianity since the early 2000s. Longer treatments of many of the arguments here appear on Bismika Allahuma, the companion site.', 'bestofislam' ) )
				. $h( __( 'What it is for', 'bestofislam' ) )
				. $p( __( 'The site exists to argue well. Readers who leave unconvinced, yet better informed about what Muslims think and why, have got what it is for.', 'bestofislam' ) ),
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
				. $h( __( 'Images', 'bestofislam' ) )
				. $p( __( 'Every image on the site comes from Wikimedia Commons, Pexels or Pixabay. The Commons images are in the public domain or dedicated under CC0; the Pexels and Pixabay images are used under the free licences of those sites. No image depicts a prophet. Each is credited beneath it, and in full here.', 'bestofislam' ) )
				. "<!-- wp:bestofislam/image-credits /-->\n\n"
				. $h( __( 'Corrections', 'bestofislam' ) )
				. $p( __( 'Errors of fact are corrected in the article and noted at the foot of it. Errors of argument are answered, if at all, in a new piece.', 'bestofislam' ) ),
		),
		'sitemap'   => array(
			'title'   => __( 'Sitemap', 'bestofislam' ),
			'slug'    => 'sitemap',
			'legacy'  => array(
				'slug'  => 'site-map',
				'title' => 'Site map',
			),
			'content' => $p( __( 'Every section and every piece on the site, in one place.', 'bestofislam' ) ) . "<!-- wp:bestofislam/html-sitemap /-->\n",
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

	return $pages;
}

/**
 * Content the theme would seed for one standing page.
 *
 * @param string $key Page key.
 * @return string
 */
function boi_seed_page_content( $key ) {
	$pages = boi_seed_pages();

	return isset( $pages[ $key ] ) ? (string) $pages[ $key ]['content'] : '';
}

/**
 * Creates the front page and the about page.
 *
 * @return array Map of key to post identifier.
 */
function boi_create_pages() {
	$pages = boi_seed_pages();

	$map = array();

	foreach ( $pages as $key => $page ) {
		$title_slug = sanitize_title( $page['title'] );
		$slug       = isset( $page['slug'] ) ? $page['slug'] : $title_slug;
		$existing   = get_page_by_path( $slug, OBJECT, 'page' );

		// A page the theme made under an earlier name (Site map at /site-map/) is
		// deleted outright, without a redirect, and the page is made again at the
		// address the seed now names. A page already at the new address that still
		// carries the earlier title is renamed.
		if ( ! empty( $page['legacy']['slug'] ) && $page['legacy']['slug'] !== $slug ) {
			$old = get_page_by_path( $page['legacy']['slug'], OBJECT, 'page' );

			if ( $old ) {
				wp_delete_post( $old->ID, true );
			}
		}

		if ( $existing && ! empty( $page['legacy']['title'] ) && $existing->post_title === $page['legacy']['title'] ) {
			boi_update_post(
				array(
					'ID'         => $existing->ID,
					'post_title' => $page['title'],
				)
			);
		}

		if ( $existing ) {
			$map[ $key ] = (int) $existing->ID;
			continue;
		}

		$post_id = boi_insert_post(
			array(
				'post_title'   => $page['title'],
				'post_name'    => $slug,
				'post_content' => (string) $page['content'],
				'post_status'  => 'publish',
				'post_author'  => boi_seed_author(),
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

	$id = boi_insert_post(
		array(
			'post_type'    => 'wp_navigation',
			'post_title'   => __( 'Primary', 'bestofislam' ),
			'post_name'    => 'primary',
			'post_status'  => 'publish',
			'post_author'  => boi_seed_author(),
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

	// Set the front page and posts page only when the site has not chosen
	// its own: population runs again on updates, and must not undo a choice
	// the owner made after activation.
	// "Chosen" means a published page: a draft or trashed one is as good as none.
	$usable = function ( $id ) {
		$page = $id ? get_post( $id ) : null;
		return $page && 'page' === $page->post_type && 'publish' === $page->post_status;
	};

	$front = (int) get_option( 'page_on_front' );

	if ( isset( $pages['home'] ) && ( 'page' !== get_option( 'show_on_front' ) || ! $usable( $front ) ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $pages['home'] );
	}

	$posts_page = (int) get_option( 'page_for_posts' );

	if ( isset( $pages['blog'] ) && ! $usable( $posts_page ) ) {
		update_option( 'page_for_posts', $pages['blog'] );
	}

	boi_backfill_listicle_flags();
	boi_migrate_seed( $topics );

	// The same atomic lock the front-end image import uses, so the two cannot
	// sideload an image at once.
	if ( function_exists( 'boi_attach_featured_images' ) && boi_images_lock_take() ) {
		boi_attach_featured_images();
		boi_images_lock_release();
	}

	boi_migrate_pages( $pages );
	boi_retire_questions_page();
	boi_repair_seed_authors();

	if ( function_exists( 'boi_stagger_seed_dates' ) ) {
		boi_stagger_seed_dates();
	}

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

		// Refresh the body only while the owner has not edited it. Four cases:
		// already current; damaged by the old unslashed save and otherwise
		// untouched, which is repaired; recorded as written by the theme,
		// which is refreshed; anything else is left for the owner to decide
		// from the Content tab.
		$fresh   = boi_load_article( $listicle['slug'] );
		$hashes  = (array) get_option( 'boi_article_hashes', array() );
		$slug    = $listicle['slug'];
		$current = boi_content_fingerprint( $post->post_content );

		// The theme's own text may be refreshed only while the owner has not
		// touched the article. An edited body means the title and excerpt may
		// have been edited too, so they are left alone as well.
		$untouched = false;

		if ( '' !== $fresh ) {
			$target  = boi_content_fingerprint( $fresh );
			$damaged = boi_content_fingerprint( wp_unslash( $fresh ) );

			if ( $current === $target ) {
				$hashes[ $slug ] = $current;
				update_option( 'boi_article_hashes', $hashes, false );
				$untouched = true;
			} elseif ( ( isset( $hashes[ $slug ] ) && $hashes[ $slug ] === $current )
				|| $current === $damaged
				|| ( ! isset( $hashes[ $slug ] ) && $post->post_modified_gmt === $post->post_date_gmt ) ) {
				$args['post_content'] = $fresh;
				$untouched            = true;
			}
		}

		// Title and excerpt are judged on their own record: a body left alone
		// says nothing about whether the owner reworded the title.
		$text_hashes = (array) get_option( 'boi_article_text_hashes', array() );
		$text_now    = md5( $post->post_title . "\n" . $post->post_excerpt );
		$text_seed   = md5( $listicle['title'] . "\n" . $listicle['excerpt'] );
		$text_free   = $text_now === $text_seed
			|| ( isset( $text_hashes[ $slug ] ) && $text_hashes[ $slug ] === $text_now )
			|| ( ! isset( $text_hashes[ $slug ] ) && $post->post_modified_gmt === $post->post_date_gmt );

		if ( $text_free ) {
			if ( $post->post_title !== $listicle['title'] ) {
				$args['post_title'] = $listicle['title'];
			}

			if ( $post->post_excerpt !== $listicle['excerpt'] ) {
				$args['post_excerpt'] = $listicle['excerpt'];
			}

			$text_hashes[ $slug ] = $text_seed;
			update_option( 'boi_article_text_hashes', $text_hashes, false );
		}

		if ( isset( $topics[ $listicle['topic'] ] ) ) {
			wp_set_object_terms( $post->ID, array( $topics[ $listicle['topic'] ] ), 'listicle-topic' );
		}

		if ( $args ) {
			$args['ID'] = $post->ID;
			boi_update_post( $args );

			if ( isset( $args['post_content'] ) ) {
				$saved           = get_post( $post->ID );
				$hashes          = (array) get_option( 'boi_article_hashes', array() );
				$hashes[ $slug ] = boi_content_fingerprint( $saved->post_content );
				update_option( 'boi_article_hashes', $hashes, false );
			}
		}
	}
}

/**
 * Brings the content of seeded standing pages up to date, but only while the
 * owner has not edited them, so a customised page is never overwritten.
 *
 * @param array $pages Map of page key to post identifier.
 * @return void
 */
function boi_migrate_pages( $pages ) {
	$hashes = (array) get_option( 'boi_page_hashes', array() );

	foreach ( array( 'about', 'faq', 'sources', 'sitemap' ) as $key ) {
		if ( empty( $pages[ $key ] ) ) {
			continue;
		}

		$post = get_post( $pages[ $key ] );

		if ( ! $post ) {
			continue;
		}

		$current = boi_content_fingerprint( $post->post_content );
		$fresh   = boi_seed_page_content( $key );

		// Unedited means the stored content still matches what the theme last
		// wrote. With no record, the page counts as unedited only if it already
		// holds the theme's text (a page the theme has just created); otherwise
		// an edit cannot be ruled out and the page is left as it is.
		if ( isset( $hashes[ $key ] ) ) {
			if ( $hashes[ $key ] !== $current ) {
				continue;
			}
		} elseif ( '' === $fresh || boi_content_fingerprint( $fresh ) !== $current ) {
			continue;
		}

		if ( '' !== $fresh && boi_content_fingerprint( $fresh ) !== $current ) {
			boi_update_post( array( 'ID' => $post->ID, 'post_content' => $fresh ) );
			$current = boi_content_fingerprint( get_post( $post->ID )->post_content );
		}

		$hashes[ $key ] = $current;
	}

	update_option( 'boi_page_hashes', $hashes, false );
}

/**
 * Removes the Questions answered page seeded in 1.34.0, now that the answers
 * live on the front page. The page is moved to the bin, so it can be
 * restored, and only while its content is still what the theme wrote.
 *
 * @return void
 */
function boi_retire_questions_page() {
	$page = get_page_by_path( 'questions-answered', OBJECT, 'page' );

	if ( ! $page || 'trash' === $page->post_status ) {
		return;
	}

	if ( false === strpos( $page->post_content, '<!-- wp:bestofislam/questions /-->' ) ) {
		return;
	}

	$hashes = (array) get_option( 'boi_page_hashes', array() );

	// Trash it only when the record proves the owner has not edited it.
	if ( ! isset( $hashes['questions'] ) || ( md5( $page->post_content ) !== $hashes['questions'] && boi_content_fingerprint( $page->post_content ) !== $hashes['questions'] ) ) {
		return;
	}

	wp_trash_post( $page->ID );
	unset( $hashes['questions'] );
	update_option( 'boi_page_hashes', $hashes, false );
}

/**
 * The user seeded content belongs to: the site's first administrator.
 *
 * Population can run on a front-end request, where nobody is logged in, and
 * WordPress assigns new posts to the current user. Without an explicit author
 * the seeded articles belonged to no one: no author card, an empty author
 * archive, and no author in the Article structured data.
 *
 * @return int User identifier, or 0 when the site has no administrator.
 */
function boi_seed_author() {
	static $id = null;

	if ( null === $id ) {
		$admins = get_users(
			array(
				'role'    => 'administrator',
				'orderby' => 'ID',
				'order'   => 'ASC',
				'number'  => 1,
				'fields'  => 'ID',
			)
		);

		$id = $admins ? (int) $admins[0] : 0;
	}

	return $id;
}

/**
 * Gives an author to seeded articles and pages that were created without
 * one. Posts that already have an author, including any the owner has
 * reassigned, are left alone.
 *
 * @return int Number of posts repaired.
 */
function boi_repair_seed_authors() {
	$author = boi_seed_author();

	if ( ! $author ) {
		return 0;
	}

	$slugs = array();

	foreach ( boi_seed_listicles() as $listicle ) {
		$slugs[] = $listicle['slug'];
	}

	$fixed = 0;

	foreach ( get_posts(
		array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'author'         => 0,
		)
	) as $post ) {
		if ( 0 !== (int) $post->post_author ) {
			continue;
		}

		if ( 'post' === $post->post_type && ! in_array( $post->post_name, $slugs, true ) ) {
			continue;
		}

		boi_update_post(
			array(
				'ID'          => $post->ID,
				'post_author' => $author,
			)
		);
		++$fixed;
	}

	return $fixed;
}

/**
 * Seeded articles whose text differs from this release's, and which the
 * update therefore left alone because it cannot tell whether the owner
 * edited them.
 *
 * @return WP_Post[] Keyed by slug.
 */
function boi_differing_articles() {
	$out = array();

	foreach ( boi_seed_listicles() as $listicle ) {
		$post  = get_page_by_path( $listicle['slug'], OBJECT, 'post' );
		$fresh = boi_load_article( $listicle['slug'] );

		if ( ! $post || '' === $fresh ) {
			continue;
		}

		if ( boi_content_fingerprint( $post->post_content ) !== boi_content_fingerprint( $fresh ) ) {
			$out[ $listicle['slug'] ] = $post;
		}
	}

	return $out;
}

/**
 * Brings the articles the owner selects on the Content tab up to this
 * release's text, replacing whatever they currently hold.
 *
 * @return void
 */
function boi_handle_refresh_articles() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'bestofislam' ) );
	}

	check_admin_referer( 'boi_refresh_articles' );

	$chosen  = isset( $_POST['boi_articles'] ) ? array_map( 'sanitize_title', (array) wp_unslash( $_POST['boi_articles'] ) ) : array();
	$allowed = boi_differing_articles();
	$hashes  = (array) get_option( 'boi_article_hashes', array() );
	$done    = 0;

	foreach ( $chosen as $slug ) {
		if ( ! isset( $allowed[ $slug ] ) ) {
			continue;
		}

		boi_update_post(
			array(
				'ID'           => $allowed[ $slug ]->ID,
				'post_content' => boi_load_article( $slug ),
			)
		);

		$hashes[ $slug ] = boi_content_fingerprint( get_post( $allowed[ $slug ]->ID )->post_content );
		++$done;
	}

	update_option( 'boi_article_hashes', $hashes, false );

	wp_safe_redirect( add_query_arg( array( 'page' => 'boi-settings', 'tab' => 'content', 'boi_refreshed' => $done ), admin_url( 'themes.php' ) ) );
	exit;
}
add_action( 'admin_post_boi_refresh_articles', 'boi_handle_refresh_articles' );
