<?php
/**
 * Glossary terms.
 *
 * Each entry: slug, title, Arabic script, transliteration, a one-line
 * definition (also the page excerpt), the explanation, the common
 * misunderstanding it answers, what the sources say, and links to related
 * terms and articles. References are to the Quran by chapter and verse, and to
 * hadith by collection. Written for this site; not drawn from any other.
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

/**
 * The glossary's terms.
 *
 * @return array
 */
function boi_glossary_terms() {
	return array(
		array(
			'slug'     => 'allah',
			'title'    => 'Allah',
			'ar'       => 'الله',
			'tr'       => 'Allāh',
			'def'      => 'The Arabic word for God, used by Arabic-speaking Muslims, Christians and Jews alike.',
			'what'     => array(
				'Allah is the Arabic name for the one God, the Creator of everything. It is not the name of a separate Arab deity. Arabic-speaking Christians and Jews used the same word for God before Islam and use it today, and Arabic translations of the Bible render “God” as Allah.',
				'The Quran describes God by many names, “the most beautiful names” (7:180): the Compassionate, the Merciful, the Creator, the Forgiving. Its shortest statement of who He is comes in chapter 112: He is Allah, One; the Eternal Refuge; He neither begets nor is begotten; and there is none comparable to Him.',
			),
			'misread'  => 'That Allah is the name of a pagan moon god, or a different god from the God of the Bible.',
			'sources'  => array(
				'The pre-Islamic Arabs knew Allah as the creator, though they joined partners to Him: asked who created the heavens and the earth, they would say “Allah” (Quran 29:61, 31:25). The Quran does not introduce a new god. It calls people back to worshipping Him alone, and tells Muslims to say to Jews and Christians, “our God and your God is one” (29:46).',
			),
			'terms'    => array( 'tawhid', 'shirk', 'ahl-al-kitab' ),
			'articles' => array( 'who-is-allah', 'same-god' ),
		),
		array(
			'slug'     => 'tawhid',
			'title'    => 'Tawhid',
			'ar'       => 'توحيد',
			'tr'       => 'Tawḥīd',
			'def'      => 'The oneness of God: one Creator and Sustainer, to whom worship belongs alone.',
			'what'     => array(
				'From the root <em>w-ḥ-d</em>, “to make one”. Tawhid is the centre of Islam and the meaning of the first half of the shahada, “there is no god but God”. Scholars often describe three aspects: God’s oneness as Lord and Creator, His oneness in worship, and the oneness of His names and attributes, for “there is nothing like Him” (42:11).',
				'The Quran argues for it rather than only asserting it. Had there been gods besides Allah in the heavens and the earth, both would have been ruined (21:22), and each god would have gone off with what he created (23:91).',
			),
			'misread'  => 'A bare slogan, or a claim made only to oppose the Trinity.',
			'sources'  => array(
				'A hadith in Sahih al-Bukhari says chapter 112, which states God’s oneness, equals a third of the Quran in weight. Islam’s disagreement with Trinitarian doctrine is a disagreement about God’s nature, which Muslims argue from the Quran and from the words of Jesus as they understand them. It is not a denial of the love and honour Muslims hold for him.',
			),
			'terms'    => array( 'allah', 'shirk', 'isa' ),
			'articles' => array( 'jesus-not-god' ),
		),
		array(
			'slug'     => 'shirk',
			'title'    => 'Shirk',
			'ar'       => 'شرك',
			'tr'       => 'Shirk',
			'def'      => 'Associating partners with God: giving to anything in creation what belongs to God alone.',
			'what'     => array(
				'Shirk means “partnership”. It is the one sin the Quran says God does not forgive without repentance (4:48), and Luqman tells his son that it is “a great injustice” (31:13). The Prophet listed it first among the gravest sins (Sahih al-Bukhari).',
				'The word covers worshipping idols, but also treating wealth, status or one’s own desires as a rival to God in the heart.',
			),
			'misread'  => 'That every non-Muslim is condemned as a pagan.',
			'sources'  => array(
				'The Quran itself separates the categories. It speaks of “those who disbelieved among the People of the Book and the polytheists” as two groups (98:1), and it calls Jews and Christians by their own name, People of the Book. Classical scholars discussed at length whether and how Trinitarian belief falls under shirk; there was no single ruling that lumped everyone together.',
			),
			'terms'    => array( 'tawhid', 'kafir', 'ahl-al-kitab' ),
			'articles' => array( 'same-god' ),
		),
		array(
			'slug'     => 'wahy',
			'title'    => 'Wahy',
			'ar'       => 'وحي',
			'tr'       => 'Waḥy',
			'def'      => 'Revelation: God’s communication to His prophets.',
			'what'     => array(
				'The Quran names three ways God speaks to a human being: by inspiration, from behind a veil, or by sending a messenger who conveys by His leave what He wills (42:51). Muslims hold that the Quran reached Muhammad through the angel Gabriel (26:192–195), in portions over about twenty-three years (17:106).',
				'The first revelation came in the cave of Hira, as Aisha related (Sahih al-Bukhari, the first hadith in the collection).',
			),
			'misread'  => 'That Muhammad composed the Quran himself, perhaps from what a teacher told him.',
			'sources'  => array(
				'The accusation is old, and the Quran records it: “It is only a human being who teaches him” (16:103). The reply is in the same verse: the language of the man they point to is foreign, while this is clear Arabic. The Quran also says Muhammad had not been reciting any book before it, nor writing one with his own hand (29:48).',
			),
			'terms'    => array( 'quran', 'sunnah', 'kutub' ),
			'articles' => array( 'who-saw-gabriel' ),
		),
		array(
			'slug'     => 'quran',
			'title'    => 'Quran',
			'ar'       => 'القرآن',
			'tr'       => 'al-Qur’ān',
			'def'      => 'The recited scripture of Islam, revealed to Muhammad and kept in memory and in writing.',
			'what'     => array(
				'“Quran” means “recitation”. It has 114 chapters (<em>suras</em>) and is recited in Arabic in prayer. Its first revelation was the command “Read” (96:1). Muslims believe it is God’s word, preserved: “We have sent down the Reminder, and We will guard it” (15:9).',
				'From the beginning it was kept in two ways at once: memorised by the community and written down. Under Abu Bakr the written leaves were gathered in one place, and under Uthman standard copies were prepared and sent to the main cities.',
			),
			'misread'  => 'That the Quran was edited, lost in part or rewritten by Uthman.',
			'sources'  => array(
				'Reports of the collection are in Sahih al-Bukhari, which names Zayd ibn Thabit as the scribe. On the manuscripts, a Quran leaf in the University of Birmingham’s collection was radiocarbon dated to between 568 and 645 CE, with a probability of 95.4 per cent, which is within or very near the Prophet’s lifetime. Muslims also point to the unbroken chain of recitation, taught from teacher to student to this day.',
			),
			'terms'    => array( 'mushaf', 'qiraat', 'tafsir' ),
			'articles' => array( 'manuscript-transmission', 'uthman-copies' ),
		),
		array(
			'slug'     => 'mushaf',
			'title'    => 'Mushaf',
			'ar'       => 'مصحف',
			'tr'       => 'Muṣḥaf',
			'def'      => 'A written copy of the Quran bound between two covers.',
			'what'     => array(
				'From <em>ṣaḥīfa</em>, “a leaf”. In the Prophet’s lifetime the Quran was written on whatever was at hand and memorised in full by many. After his death the leaves were collected, and in the time of Uthman copies in one agreed spelling were sent to the main centres. The standard copy is often called the Uthmanic <em>mushaf</em>.',
				'A <em>mushaf</em> is handled with respect, and Muslims traditionally wash before touching it.',
			),
			'misread'  => 'That Uthman “burned the rival Qurans”, destroying the true text.',
			'sources'  => array(
				'Sahih al-Bukhari records that Uthman had the leaves copied, ordered the standard copies sent out, and asked that other individual leaves be destroyed to avoid disputes. Muslims read this as protecting one text for the whole community, and note that no major dispute over it was recorded in the early centuries.',
			),
			'terms'    => array( 'quran', 'qiraat' ),
			'articles' => array( 'uthman-copies' ),
		),
		array(
			'slug'     => 'qiraat',
			'title'    => 'Qira’at',
			'ar'       => 'قراءات',
			'tr'       => 'Qirā’āt',
			'def'      => 'The recognised modes of reciting the Quran, taught by an unbroken chain of teachers.',
			'what'     => array(
				'Ten readings are recognised as authentic, each traced through a named teacher back to the Prophet. They share the same written skeleton and differ mostly in vowelling and pronunciation. Two are common today: Hafs, from Asim, in most of the Muslim world, and Warsh, from Nafi, in North and West Africa.',
				'A hadith in Sahih al-Bukhari relates that the Quran was “sent down in seven <em>aḥruf</em>”: Umar objected to how Hisham ibn Hakim was reciting, and the Prophet approved both recitations.',
			),
			'misread'  => 'That there are different Qurans, or that the text is unstable.',
			'sources'  => array(
				'The readings are variants within one text, and the great majority make no difference to the meaning. A few change a word in a way that adds a shade of meaning, and scholars treat these as additions to understanding rather than contradictions. The same standard text, in Hafs or Warsh, is in every mosque.',
			),
			'terms'    => array( 'quran', 'mushaf', 'tafsir' ),
			'articles' => array( 'manuscript-transmission' ),
		),
		array(
			'slug'     => 'tafsir',
			'title'    => 'Tafsir',
			'ar'       => 'تفسير',
			'tr'       => 'Tafsīr',
			'def'      => 'Commentary on the Quran: explaining what its verses mean and how they were understood.',
			'what'     => array(
				'Classical commentators explained a verse first from the rest of the Quran, then from the Prophet’s explanations, then from the Companions, then from the Arabic language itself. The great early works include al-Tabari (d. 923) and Ibn Kathir (d. 1373), and each gathers earlier views rather than offering a single opinion.',
				'A verse read alone, in English translation, may be read very differently from how its first audience and its scholars understood it.',
			),
			'misread'  => 'That any reader can lift a verse out and say what “Islam teaches”.',
			'sources'  => array(
				'Muslims do not read the Quran as a list of separate sentences. The commentary tradition records the context, the grammar and the range of readings, and often shows that the apparently harsh or strange reading is not the one the scholars settled on. This is the reason this site sets objections beside the explanations that exist for them.',
			),
			'terms'    => array( 'quran', 'asbab-al-nuzul', 'naskh' ),
			'articles' => array( 'verses-read-differently' ),
		),
		array(
			'slug'     => 'asbab-al-nuzul',
			'title'    => 'Asbab al-Nuzul',
			'ar'       => 'أسباب النزول',
			'tr'       => 'Asbāb al-nuzūl',
			'def'      => 'The occasions of revelation: the circumstances in which a verse was revealed.',
			'what'     => array(
				'Many verses answer a question, a dispute or an event, and knowing the occasion helps to see what is being said. Al-Wahidi (d. 1076) wrote a well-known book collecting them. Scholars add a principle: the lesson is taken from the general wording of a verse, not only from its occasion.',
			),
			'misread'  => 'That verses about fighting are timeless orders to attack anyone who disbelieves.',
			'sources'  => array(
				'The “sword verse” (9:5) orders fighting those who broke their treaties. The verse just before it exempts the polytheists who kept their treaty (9:4), and the verse just after it says that if any of them asks for protection it must be granted, and he must be escorted to safety (9:6). Read with its context, it is a rule about a war already under way, not a general licence.',
			),
			'terms'    => array( 'tafsir', 'jihad', 'naskh' ),
			'articles' => array( 'verses-read-differently', 'islam-religion-of-peace' ),
		),
		array(
			'slug'     => 'naskh',
			'title'    => 'Naskh',
			'ar'       => 'نسخ',
			'tr'       => 'Naskh',
			'def'      => 'Abrogation: the replacing of one ruling by a later one, as God wills.',
			'what'     => array(
				'The Quran says that whatever verse God abrogates or causes to be forgotten, He brings one better or similar (2:106). Muslims point to clear cases: the prayer direction changed from Jerusalem to Mecca (2:142–150), and the prohibition of wine came in stages (4:43, then 5:90–91).',
				'The term is mainly about rulings of law. Later scholars cut the number of cases drastically: al-Suyuti kept about twenty, and Shah Waliullah five. Some, such as Abu Muslim al-Isfahani, denied abrogation within the Quran altogether.',
			),
			'misread'  => 'That Muhammad cancelled the peaceful verses with violent ones, so Islam is violent.',
			'sources'  => array(
				'Some early exegetes made sweeping claims that one verse cancelled many. Many scholars rejected this, and the principles the Quran repeats are not treated as cancelled: no compulsion in religion (2:256), kindness and justice to those who have not fought you over your religion (60:8), and the command to deal fairly even with those one dislikes (5:8).',
			),
			'terms'    => array( 'tafsir', 'asbab-al-nuzul', 'jihad' ),
			'articles' => array( 'verses-read-differently' ),
		),
		array(
			'slug'     => 'hadith',
			'title'    => 'Hadith',
			'ar'       => 'حديث',
			'tr'       => 'Ḥadīth',
			'def'      => 'A report of the sayings, actions or approvals of the Prophet Muhammad.',
			'what'     => array(
				'Each hadith has two parts: the <em>matn</em>, the text, and the <em>isnād</em>, the chain of people who passed it on. Collectors graded reports by the reliability of the narrators and by whether the text agreed with better-attested reports and with the Quran. The best known collections are Sahih al-Bukhari and Sahih Muslim, with four others, Abu Dawud, al-Tirmidhi, al-Nasa’i and Ibn Majah, usually counted with them.',
				'Scholars themselves wrote works listing fabricated reports, and graded many as weak or false.',
			),
			'misread'  => 'That hadith are late forgeries, and all of them are treated as equally binding.',
			'sources'  => array(
				'Bukhari is traditionally said to have chosen roughly seven thousand reports, counting repetitions, from several hundred thousand he examined. The criticism that some reports are weak is one Muslim scholars made first, in their own books. For the Muslim, the issue is not whether a report exists but how well it is attested.',
			),
			'terms'    => array( 'isnad', 'sunnah', 'sira' ),
			'articles' => array(),
		),
		array(
			'slug'     => 'isnad',
			'title'    => 'Isnad',
			'ar'       => 'إسناد',
			'tr'       => 'Isnād',
			'def'      => 'The chain of narrators by which a report is traced back to its source.',
			'what'     => array(
				'“The chain is part of the religion,” said Abd Allah ibn al-Mubarak, “and without the chain anyone could say what he liked” (quoted in the introduction to Sahih Muslim). Ibn Sirin said that at first people did not ask about chains, but after the civil strife of the first century they said, “Name your men.”',
				'This produced a large literature on the narrators: who they were, where they lived, whom they met, how good their memory was and whether they were honest.',
			),
			'misread'  => 'That Muslims accepted reports without any checking.',
			'sources'  => array(
				'Whatever one concludes about the method, it was a deliberate attempt to test reports by their sources, and it was applied to the most revered figures as well as to unknowns. A report might be rejected because one narrator in the chain was unreliable, however attractive its content.',
			),
			'terms'    => array( 'hadith', 'sunnah' ),
			'articles' => array(),
		),
		array(
			'slug'     => 'sunnah',
			'title'    => 'Sunnah',
			'ar'       => 'سنة',
			'tr'       => 'Sunna',
			'def'      => 'The Prophet’s example: his way of acting, which Muslims follow beside the Quran.',
			'what'     => array(
				'The Quran says that in the Messenger of God there is a good example (33:21), and commands obedience to him (4:59, 59:7). Much of how Muslims practise comes from this: the Quran commands prayer and charity, and the sunnah shows how the prayer is performed and what share is due as zakat.',
				'The sunnah is known mostly through hadith, but the two are not the same: the sunnah is the practice, and the hadith are the reports of it.',
			),
			'misread'  => 'That Muslims follow hadith in place of the Quran.',
			'sources'  => array(
				'The Quran is the first source, and the sunnah follows it; no sound report can overrule a clear statement of the Quran. The Quran itself directs readers to the Prophet to explain it, and a body of practice kept by the whole community, such as the number of daily prayers, is older and more widely attested than any individual report.',
			),
			'terms'    => array( 'hadith', 'sharia', 'sira' ),
			'articles' => array( 'why-five-prayers' ),
		),
		array(
			'slug'     => 'sira',
			'title'    => 'Sira',
			'ar'       => 'سيرة',
			'tr'       => 'Sīra',
			'def'      => 'The biography of the Prophet Muhammad, as written by early Muslim historians.',
			'what'     => array(
				'The earliest full life is that of Ibn Ishaq (d. about 767), which survives through the edition of Ibn Hisham (d. 833). Others include al-Waqidi and Ibn Sa’d. These are narrative histories, which draw on hadith but are not collected to the same standard.',
			),
			'misread'  => 'That every story in the biography has the same authority as the Quran or a sound hadith.',
			'sources'  => array(
				'Classical scholars distinguished between what they accepted in law and creed, where the standard was strict, and what they accepted in historical narrative, where it was looser. A claim about the Prophet that rests on a weak or late report should be weighed as such. This is a method Muslim scholars used, and one that readers on this site are invited to apply.',
			),
			'terms'    => array( 'hadith', 'sunnah', 'hijra' ),
			'articles' => array( 'prophet-died-poisoning' ),
		),
		array(
			'slug'     => 'sharia',
			'title'    => 'Sharia',
			'ar'       => 'شريعة',
			'tr'       => 'Sharīʿa',
			'def'      => 'The path of God: the ethical and legal way set out in the Quran and the Prophet’s example.',
			'what'     => array(
				'The word first meant a path to water, and the Quran uses it for “a clear way” (45:18). Most of it concerns worship, honesty, family and contracts. Scholars also describe its aims: protecting faith, life, reason, family and property.',
				'It is not a single written code. Rulings are worked out by jurists (see <em>fiqh</em>), and the schools differ on many points.',
			),
			'misread'  => 'That sharia means a fixed list of harsh punishments imposed on everyone.',
			'sources'  => array(
				'The fixed punishments (<em>ḥudūd</em>) are a small part of the whole and are hedged by strict proof. For adultery the Quran demands four eye-witnesses (24:4, 24:13), and false accusation is itself punished. Jurists held that such penalties are to be avoided where there is doubt. Muslims also differ widely on whether and how a modern state should apply any of it.',
			),
			'terms'    => array( 'fiqh', 'sunnah', 'naskh' ),
			'articles' => array(),
		),
		array(
			'slug'     => 'fiqh',
			'title'    => 'Fiqh',
			'ar'       => 'فقه',
			'tr'       => 'Fiqh',
			'def'      => 'Islamic jurisprudence: the scholars’ work of understanding and applying the sharia.',
			'what'     => array(
				'Fiqh means “understanding”, and the Quran uses the idea of seeking understanding in the religion (9:122). The four main Sunni schools, Hanafi, Maliki, Shafi’i and Hanbali, and other schools such as the Ja’fari and Ibadi, each reached rulings by a method of reading the sources, and they often disagree.',
			),
			'misread'  => 'That Islamic law is one rulebook with one answer on each question.',
			'sources'  => array(
				'Disagreement among the schools is normal and long recognised. A ruling is a scholar’s judgement of what the sources require, offered with its evidence, and a Muslim chooses a qualified scholar to follow. Many things people call “Islamic law” in the news are a particular government’s choice among such opinions, or custom, not the sources themselves.',
			),
			'terms'    => array( 'sharia', 'sunnah', 'tafsir' ),
			'articles' => array(),
		),
		array(
			'slug'     => 'jihad',
			'title'    => 'Jihad',
			'ar'       => 'جهاد',
			'tr'       => 'Jihād',
			'def'      => 'Striving in the cause of God, which includes the moral struggle, and, under strict rules, defensive fighting.',
			'what'     => array(
				'The word means “striving”. The Quran commands Muslims to “strive for God as He deserves” (22:78) and, in an early Meccan verse, to strive against the unbelievers with the Quran itself (25:52). The word for armed fighting is a separate one, <em>qitāl</em>.',
				'Permission to fight came after years of persecution, to “those who are fought, because they were wronged” (22:39). The same passage names monasteries, churches, synagogues and mosques as places that fighting is meant to protect (22:40).',
			),
			'misread'  => 'That jihad is holy war to convert or kill non-Muslims everywhere.',
			'sources'  => array(
				'The Quran limits fighting to those who fight, and forbids exceeding the limit (2:190). The Prophet is reported to have forbidden killing women and children (Sahih al-Bukhari), and Abu Bakr’s instructions to an army, recorded in the Muwatta of Malik, forbid killing the elderly, monks and non-combatants and cutting down fruit trees. A saying reported in Abu Dawud and al-Tirmidhi calls the best jihad “a word of truth before an unjust ruler”.',
			),
			'terms'    => array( 'fitna', 'asbab-al-nuzul', 'naskh' ),
			'articles' => array( 'islam-religion-of-peace' ),
		),
		array(
			'slug'     => 'fitna',
			'title'    => 'Fitna',
			'ar'       => 'فتنة',
			'tr'       => 'Fitna',
			'def'      => 'Trial, persecution or turmoil; in the Quran, mainly the persecution of believers for their faith.',
			'what'     => array(
				'The root means to test, as gold is tried in fire. The Quran says God tests people with good and evil as a trial (21:35). In the verses on fighting it means persecution: “fitna is worse than killing” (2:191, also 2:217).',
			),
			'misread'  => 'That “fight them until there is no fitna” (2:193) means fighting until everyone converts.',
			'sources'  => array(
				'The verse continues: “if they cease, there is no hostility except against the wrongdoers” (2:193). The fighting is to stop persecution, and the Muslims in Mecca had been tortured and driven out for their belief. It sits in the same chapter that says there is “no compulsion in religion” (2:256) and is read together with it.',
			),
			'terms'    => array( 'jihad', 'asbab-al-nuzul' ),
			'articles' => array( 'islam-religion-of-peace', 'apostasy-in-islam' ),
		),
		array(
			'slug'     => 'jizya',
			'title'    => 'Jizya',
			'ar'       => 'جزية',
			'tr'       => 'Jizya',
			'def'      => 'A tax paid by non-Muslim adult men under Muslim rule, in return for protection and exemption from military service.',
			'what'     => array(
				'The Quran mentions it once (9:29). In the classical law, women, children, the elderly, the poor and monks were exempt, and the tax was a due in place of the military service and the zakat that Muslims owed. In return the state owed protection of life, property and worship.',
				'Abu Yusuf’s <em>Kitab al-Kharaj</em> reports that when Muslim forces in Syria could not protect a town, Abu Ubayda returned the tax they had collected, saying that without protection it was not owed.',
			),
			'misread'  => 'That it was a ransom or extortion, “convert or pay”.',
			'sources'  => array(
				'Treaties of the early period, such as that with the Christians of Najran, set a fixed sum and promise safety for their churches and clergy. The Quran’s phrase “while they are subdued” was read by some jurists, among them al-Shafi’i, as accepting the authority of the state’s law. Practice varied by time and ruler, and Muslims do not claim that every ruler honoured the ideal. Today the tax has no place in most states, where non-Muslims are citizens.',
			),
			'terms'    => array( 'dhimmi', 'ahl-al-kitab', 'zakat' ),
			'articles' => array( 'jizya' ),
		),
		array(
			'slug'     => 'dhimmi',
			'title'    => 'Dhimmi',
			'ar'       => 'ذمّي',
			'tr'       => 'Dhimmī',
			'def'      => 'A non-Muslim living under Muslim rule with the state’s pledge of protection.',
			'what'     => array(
				'<em>Dhimma</em> means a pledge or covenant. A dhimmi’s life, property and place of worship were protected by it, and the community ran its own courts and affairs by its own law. The Prophet is reported to have said that whoever kills a person under a treaty will not smell the fragrance of Paradise (Sahih al-Bukhari).',
				'The Maliki jurist al-Qarafi (d. 1285) wrote that the pledge puts duties on Muslims: to treat them as neighbours under God’s protection, to defend them and to prevent their being wronged.',
			),
			'misread'  => 'That dhimmis were slaves, or hunted second-class people.',
			'sources'  => array(
				'The classical law did place distinctions and restrictions, and Muslims do not need to deny this. But the status was a legal one, with rights, and it compares well with how religious minorities were treated elsewhere in the same centuries. When the Jews were expelled from Spain in 1492, the Ottoman sultan Bayezid II received many of them. Nobody on this site claims the record was perfect, only that it was not what the caricature suggests.',
			),
			'terms'    => array( 'jizya', 'ahl-al-kitab', 'ummah' ),
			'articles' => array( 'jizya' ),
		),
		array(
			'slug'     => 'kafir',
			'title'    => 'Kafir',
			'ar'       => 'كافر',
			'tr'       => 'Kāfir',
			'def'      => 'One who rejects or denies the truth; literally, one who covers it up.',
			'what'     => array(
				'The root <em>k-f-r</em> means “to cover”. The Quran uses a related word for farmers, who cover seed with soil (57:20). As a religious term it describes a person who knows the truth and hides or rejects it, and it is a statement about belief, not a name for a people.',
				'Chapter 109 ends “to you your religion, and to me mine”. The Quran does not forbid kindness and justice to those who do not fight Muslims over religion, and it says God loves those who act justly (60:8).',
			),
			'misread'  => 'That it is a slur, or a licence to kill anyone who is not a Muslim.',
			'sources'  => array(
				'The decision about who has rejected God is left to God. A hadith in Sahih al-Bukhari warns that if a man calls his brother “kafir”, one of them has earned the word, and jurists accordingly forbade accusing Muslims of disbelief lightly. The Quran also gives separate words for the People of the Book and for polytheists, and does not use one label for all.',
			),
			'terms'    => array( 'shirk', 'ahl-al-kitab', 'jihad' ),
			'articles' => array(),
		),
		array(
			'slug'     => 'taqiyya',
			'title'    => 'Taqiyya',
			'ar'       => 'تقية',
			'tr'       => 'Taqiyya',
			'def'      => 'Concealing one’s belief to escape persecution or grave harm; a concession, not a licence to deceive.',
			'what'     => array(
				'The Quran allows a believer under compulsion to speak words of disbelief while his heart is at peace in faith (16:106), and refers to taking precaution against those who threaten one’s safety (3:28). The best-known case is Ammar ibn Yasir, whom the Meccans tortured until he said what they wished.',
				'The idea is stressed most in Shia thought, as the practice of a persecuted minority. Sunni scholars discuss the same permission under the name <em>ruḵṣa</em>, “a concession”.',
			),
			'misread'  => 'That Muslims may lie to non-Muslims to advance Islam.',
			'sources'  => array(
				'The permission applies to preserving life under real threat, not to ordinary dealings. The Quran commands keeping covenants and oaths (16:91), and a hadith in Sahih al-Bukhari names lying in speech as one of the marks of a hypocrite. Claims that taqiyya is a general doctrine of deceiving unbelievers come chiefly from outsiders, not from the sources.',
			),
			'terms'    => array( 'kafir', 'sharia', 'hadith' ),
			'articles' => array(),
		),
		array(
			'slug'     => 'hijab',
			'title'    => 'Hijab',
			'ar'       => 'حجاب',
			'tr'       => 'Ḥijāb',
			'def'      => 'Modest dress, and in particular the head covering worn by many Muslim women.',
			'what'     => array(
				'In the Quran <em>ḥijāb</em> means a screen or barrier, as in the verse about speaking to the Prophet’s wives from behind a screen (33:53). The covering of the head and chest is described by other words: <em>khimār</em> (24:31) and <em>jilbāb</em> (33:59). Scholars agree that modest dress is required of men and of women, and they differ on the details, including the face.',
			),
			'misread'  => 'That the hijab is a sign of oppression imposed on Muslim women.',
			'sources'  => array(
				'The Quran says that there is “no compulsion in religion” (2:256), and many Muslim scholars hold that forcing a woman to veil, as some states do, has no basis in the sources. Many Muslim women who wear it describe it as their own choice and an expression of faith, and many others do not wear it. The issue for the Muslim is consent: being made to veil and being forbidden to veil are both an infringement.',
			),
			'terms'    => array( 'sharia', 'ummah' ),
			'articles' => array(),
		),
		array(
			'slug'     => 'hijra',
			'title'    => 'Hijra',
			'ar'       => 'هجرة',
			'tr'       => 'Hijra',
			'def'      => 'Emigration for the sake of faith, above all the Prophet’s move from Mecca to Medina in 622 CE.',
			'what'     => array(
				'After thirteen years of persecution in Mecca, the Prophet and his followers left for Medina in 622 CE. Under Umar the Islamic calendar was counted from this year. Hijra also means leaving what God forbids, as in the hadith “the emigrant is the one who abandons what God has forbidden” (Sahih al-Bukhari).',
				'An earlier emigration, around 615, took a group of Muslims across the Red Sea to Abyssinia, where a Christian king, the Negus, gave them refuge.',
			),
			'misread'  => 'That the move was the start of a policy of conquest.',
			'sources'  => array(
				'Ibn Ishaq relates that Ja’far ibn Abi Talib recited from chapter 19, on Mary, before the Negus, who wept and said that this and what Jesus brought came from the same lamp. The first Muslims were refugees from persecution who were sheltered by a Christian ruler. Medina’s early constitution, preserved in Ibn Ishaq’s biography, treats the Jews of the city as a community alongside the Muslims.',
			),
			'terms'    => array( 'ummah', 'sira', 'maryam' ),
			'articles' => array( 'qibla-change' ),
		),
		array(
			'slug'     => 'ummah',
			'title'    => 'Ummah',
			'ar'       => 'أمة',
			'tr'       => 'Umma',
			'def'      => 'The community of Muslims, united by faith across nations and cultures.',
			'what'     => array(
				'The Quran calls the Muslims a “middle” or balanced community (2:143), and says that “this community of yours is one community” (21:92). A hadith in Sahih al-Bukhari compares the believers, in their mutual love and mercy, to one body: if one part suffers, the rest respond with sleeplessness and fever.',
				'At the same time the Quran affirms diversity: peoples and tribes were made so that they should come to know one another, and the most honoured is the most God-conscious (49:13).',
			),
			'misread'  => 'That the ummah is a political bloc seeking to dominate the world.',
			'sources'  => array(
				'The ummah is a community of faith and ethics. Muslims live in states of every kind and have fought and allied with one another and with non-Muslims for every reason. The word is also used in the Quran for other peoples with their own prophets (10:47), which shows it is not a political claim.',
			),
			'terms'    => array( 'taqwa', 'dhimmi', 'hijra' ),
			'articles' => array(),
		),
		array(
			'slug'     => 'ahl-al-kitab',
			'title'    => 'Ahl al-Kitab',
			'ar'       => 'أهل الكتاب',
			'tr'       => 'Ahl al-Kitāb',
			'def'      => 'The People of the Book: Jews and Christians, who received earlier scriptures.',
			'what'     => array(
				'The Quran gives them a place of their own. It invites them to “a common word” of worshipping God alone (3:64), tells Muslims to argue with them only in the best manner (29:46), permits eating their food and marrying their chaste women (5:5), and says that some of them are upright and pray through the night (3:113–115).',
				'It says that the people nearest in affection to believers are those who say “we are Christians”, because among them are priests and monks who are not proud (5:82).',
			),
			'misread'  => 'That the Quran declares Jews and Christians enemies.',
			'sources'  => array(
				'The Quran criticises specific beliefs and acts, and in specific situations, and it does so in the same chapters where it praises others. A Muslim’s relation to the People of the Book is one of respect, of disagreement on theology and, as the Quran says, of competing in good deeds (5:48).',
			),
			'terms'    => array( 'kutub', 'tahrif', 'dhimmi' ),
			'articles' => array( 'same-god', 'shared-prophets' ),
		),
		array(
			'slug'     => 'tahrif',
			'title'    => 'Tahrif',
			'ar'       => 'تحريف',
			'tr'       => 'Taḥrīf',
			'def'      => 'The Quranic charge that some of the earlier scriptures were altered or misread by their custodians.',
			'what'     => array(
				'The Quran says that some among the Jews “distort the words from their places” (4:46, 5:13; compare 2:75). Muslim scholars have long differed on what this means. Some hold the main distortion was in <em>meaning</em>, by wrong interpretation (<em>taḥrīf al-ma’nā</em>). Others hold there was also change to the wording (<em>taḥrīf al-lafẓ</em>).',
				'A statement of Ibn Abbas is quoted in Sahih al-Bukhari: no one can alter a word of God’s books, but they twist them by misinterpretation.',
			),
			'misread'  => 'That Muslims simply say the Bible is corrupt, to avoid reading it.',
			'sources'  => array(
				'The Quran says that the Torah contains guidance and light (5:44) and that the Gospel does (5:46). It tells the Prophet, in case of doubt, to ask those who had been reading the Book before him (10:94), which assumes the earlier scriptures were in people’s hands. Muslims who hold that the texts also changed point to differences among surviving manuscripts, a field that Biblical scholars themselves study and debate.',
			),
			'terms'    => array( 'kutub', 'injil', 'ahl-al-kitab' ),
			'articles' => array( 'where-is-the-injil', 'manuscript-transmission' ),
		),
		array(
			'slug'     => 'isa',
			'title'    => 'Isa',
			'ar'       => 'عيسى',
			'tr'       => 'ʿĪsā',
			'def'      => 'Jesus, son of Mary: the Messiah and a mighty messenger of God in Islam.',
			'what'     => array(
				'The Quran calls him the Messiah, “honoured in this world and the next” (3:45), and says he was born of the virgin Mary (3:47, 19:16–21), spoke as an infant (19:30–33), and, by God’s leave, healed the sick, raised the dead and gave life to clay birds (3:49, 5:110). It names him “a Word from God” and “a spirit from Him” (4:171).',
				'Muslims love and honour him, and believe he will return before the end of time (Sahih al-Bukhari).',
			),
			'misread'  => 'That Islam denies or insults Jesus.',
			'sources'  => array(
				'The Quran does not accept that he is God or God’s son (5:72–75, 5:116–117), and says that his creation was like Adam’s, from dust (3:59). The disagreement is about what Jesus said of himself, and about the crucifixion (4:157). Both are taken up in the articles linked here.',
			),
			'terms'    => array( 'maryam', 'injil', 'tawhid' ),
			'articles' => array( 'jesus-not-god', 'crucifixion-quran', 'shared-prophets' ),
		),
		array(
			'slug'     => 'maryam',
			'title'    => 'Maryam',
			'ar'       => 'مريم',
			'tr'       => 'Maryam',
			'def'      => 'Mary, mother of Jesus: the only woman named in the Quran, which gives her a chapter.',
			'what'     => array(
				'The Quran says that the angels told her God had chosen and purified her above the women of the worlds (3:42). Chapter 19 is named after her, and chapter 66 holds her up as a model of faith and chastity (66:12). The Prophet is reported to have said that the best of women were Mary, daughter of Imran, and Khadija (Sahih al-Bukhari).',
			),
			'misread'  => 'That the Quran confuses Mary with Miriam, Aaron’s sister, because it calls her “sister of Aaron” (19:28).',
			'sources'  => array(
				'A man once raised this with the Prophet’s Companion al-Mughira ibn Shu’ba, who asked the Prophet and was told that the people of that time used to take names from the prophets and righteous people who came before them (Sahih Muslim). “Sister of Aaron” can mean one of his line or his kind, as the Arabic <em>akh</em> and <em>ukht</em> often do. The Quran also gives her father’s name as Imran, as the Bible gives Amram for Aaron’s father.',
			),
			'terms'    => array( 'isa', 'injil' ),
			'articles' => array( 'ezra-and-mary' ),
		),
		array(
			'slug'     => 'injil',
			'title'    => 'Injil',
			'ar'       => 'إنجيل',
			'tr'       => 'Injīl',
			'def'      => 'The Gospel: the revelation God gave to Jesus, which the Quran says confirmed the Torah.',
			'what'     => array(
				'The Quran says God gave Jesus the Gospel, “in it guidance and light, confirming the Torah” (5:46), and tells “the people of the Gospel” to judge by what God revealed in it (5:47). Muslims understand the <em>Injīl</em> as the message revealed to Jesus himself, not as the four Gospels, which are accounts about him by his followers.',
			),
			'misread'  => 'That Muslims have no use for the Gospels, or say they simply contradict the Quran.',
			'sources'  => array(
				'Muslim scholars through the centuries have quoted the Gospels, for instance on the sayings of Jesus, and many see in them a record that carries much true teaching. They differ with the Christian view on the cross and on Jesus’ nature. The question of what became of the original Gospel is taken up in the article linked here.',
			),
			'terms'    => array( 'kutub', 'tahrif', 'isa' ),
			'articles' => array( 'where-is-the-injil' ),
		),
		array(
			'slug'     => 'kutub',
			'title'    => 'Kutub',
			'ar'       => 'كتب',
			'tr'       => 'Kutub',
			'def'      => 'The scriptures God revealed: the Torah, the Psalms, the Gospel and the Quran.',
			'what'     => array(
				'Belief in the scriptures is one of the six articles of faith, in the hadith of Gabriel (Sahih Muslim). The Quran names the Torah given to Moses (5:44), the Psalms (<em>Zabūr</em>) given to David (4:163), the Gospel given to Jesus, and the scrolls of Abraham and Moses (87:18–19). “We make no distinction between any of His messengers” (2:285).',
			),
			'misread'  => 'That Islam is a rival religion that sets out to cancel Judaism and Christianity.',
			'sources'  => array(
				'The Quran presents itself as confirming what came before it and guarding it (5:48), and as the same message from the same God. Muslims do not hold that the earlier prophets taught something else. Their claim is that the message was one, was corrupted or misunderstood in places, and was finally given in a form kept intact.',
			),
			'terms'    => array( 'injil', 'tahrif', 'ahl-al-kitab' ),
			'articles' => array( 'shared-prophets' ),
		),
		array(
			'slug'     => 'taqwa',
			'title'    => 'Taqwa',
			'ar'       => 'تقوى',
			'tr'       => 'Taqwā',
			'def'      => 'God-consciousness: living mindful of God, as one who guards oneself against wrongdoing.',
			'what'     => array(
				'From a root meaning “to shield”. It is often translated “fear of God”, but it is closer to awareness and care: acting well because one knows God sees. The Quran opens by calling itself guidance for the <em>muttaqīn</em>, the God-conscious (2:2), and says that the most honoured before God are the most God-conscious (49:13).',
			),
			'misread'  => 'That Islam is a religion of fear.',
			'sources'  => array(
				'The Quran joins taqwa to hope and love: “those who believe are stronger in love for God” (2:165). The Prophet taught his companions to fear God wherever they were, to follow a bad deed with a good one so that it wipes it out, and to treat people with good character (al-Tirmidhi).',
			),
			'terms'    => array( 'ihsan', 'ummah' ),
			'articles' => array(),
		),
		array(
			'slug'     => 'ihsan',
			'title'    => 'Ihsan',
			'ar'       => 'إحسان',
			'tr'       => 'Iḥsān',
			'def'      => 'Excellence in faith and conduct: to worship God as though you see Him.',
			'what'     => array(
				'In the hadith of Gabriel, the Prophet defines <em>iḥsān</em> as worshipping God as though you see Him, for if you do not see Him, He sees you (Sahih al-Bukhari and Sahih Muslim). The Quran says God commands justice and ihsan (16:90), and asks: “Is the reward for ihsan anything but ihsan?” (55:60).',
			),
			'misread'  => 'That Islam is mostly rules and legal duties.',
			'sources'  => array(
				'The hadith of Gabriel sets out three levels: <em>islām</em>, the acts of worship; <em>īmān</em>, belief; and <em>iḥsān</em>, the inward excellence behind both. The long tradition of Muslim spiritual writing, from al-Ghazali to Rumi, grew around this third level.',
			),
			'terms'    => array( 'taqwa', 'sharia' ),
			'articles' => array( 'five-pillars-explained' ),
		),
		array(
			'slug'     => 'zakat',
			'title'    => 'Zakat',
			'ar'       => 'زكاة',
			'tr'       => 'Zakāh',
			'def'      => 'Obligatory almsgiving: a fixed share of one’s savings, given to those in need each year.',
			'what'     => array(
				'The word means “purification” and “growth”: the Quran says that by it believers are purified (9:103). It is one of the five pillars. In general it is 2.5 per cent of savings above a minimum (<em>niṣāb</em>) held for a lunar year. The Quran names eight kinds of recipient, first the poor and the needy (9:60).',
			),
			'misread'  => 'That it is a tax to fund war, or that it only helps Muslims.',
			'sources'  => array(
				'The first two categories are the poor and the needy. Charity beyond the obligation, <em>ṣadaqa</em>, can go to anyone in need, and the Quran says that what you spend on good is for your own benefit (2:272). Abu Yusuf’s <em>Kitab al-Kharaj</em> reports that Umar ordered a stipend from the public treasury for an elderly Jewish man he saw begging, saying it would be unjust to take tax from him in his youth and abandon him in old age.',
			),
			'terms'    => array( 'jizya', 'riba', 'ihsan' ),
			'articles' => array( 'five-pillars-explained' ),
		),
		array(
			'slug'     => 'riba',
			'title'    => 'Riba',
			'ar'       => 'ربا',
			'tr'       => 'Ribā',
			'def'      => 'Usury: gain taken from a loan at the borrower’s expense, which the Quran forbids.',
			'what'     => array(
				'The Quran says that God has permitted trade and forbidden <em>ribā</em> (2:275), and warns of a war from God against those who continue it (2:278–279). It adds that if the debtor is in difficulty, there should be time for him, and that to forgive the debt is better (2:280). A hadith in Sahih Muslim says the Prophet cursed the one who takes it, the one who gives it, the one who records it and the two who witness it.',
				'Muslims have developed finance on other principles, such as profit-sharing, sale at an agreed mark-up and leasing.',
			),
			'misread'  => 'That Islam is simply against business.',
			'sources'  => array(
				'The same teaching is found in the other scriptures: the Torah forbids charging interest to the poor (Exodus 22:25), and the medieval Church banned usury for centuries. Islam is a religion in which trade is praised, and the Prophet was himself a merchant. The prohibition is aimed at gain from the weakness of a debtor, and not at commerce.',
			),
			'terms'    => array( 'zakat', 'sharia', 'fiqh' ),
			'articles' => array(),
		),
	);
}
