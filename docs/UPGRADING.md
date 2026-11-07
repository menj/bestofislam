# Upgrading

## Before any upgrade

1. Back up the database. The theme owns the `{prefix}_boi_votes` table and
   keeps its settings in the `boi_settings` option.
2. Back up the theme directory if it has been modified in place.
3. Confirm Twenty Twenty-Five remains installed at 1.5 or later.
4. If the custom login address is on, note it down before upgrading.

## Procedure

1. Upload the new package under Appearance, Themes, Add New, and replace the
   existing installation. There is no need to switch themes. By FTP instead:
   turn on hidden files in the FTP program, so the `.htaccess` files go up;
   upload the folder into `wp-content/themes/` as `bestofislam-new`, its
   `.htaccess` files first (the theme folder's, then `docs/`, and `bin/` and
   `tests/` when the copy has them), so on Apache their rules apply while the rest arrives; on
   Nginx, which ignores them, add the server rules in `README.md` before the
   first FTP update; then rename `bestofislam` to
   `bestofislam-old` and, straight after, `bestofislam-new` to `bestofislam`,
   with the admin closed in between. Delete `bestofislam-old` once the site
   checks out. Never upload WordPress itself without keeping `wp-config.php`.
2. Load any page of the site, in the admin or on the front end. The theme
   compares its stored version with the new one and migrates on that first
   load: it updates the votes table, adds any missing seeded content,
   refreshes seeded pages and articles that have not been edited since the
   theme wrote them, repairs seeded content without an author, and repairs
   articles whose entry titles were lost to the saving fault fixed in 1.52.0.
3. Open the Content tab under Appearance, Theme Options. If it lists seeded
   articles that differ from this version, the update could not tell whether
   you edited them and has left them alone. Tick the ones you have not edited
   and choose Update the ticked articles; anything ticked is replaced with this
   version's text.
4. Featured images are copied into the media library three at a time as pages
   load. The Content tab reports how many have arrived. Until an image
   arrives, the copy bundled with the theme is shown, so no article goes
   without its picture.

## After an upgrade

- **Customised template parts.** If the header or footer was ever edited in
  the Site Editor, WordPress keeps showing the saved copy and ignores the
  theme's new one. Reset it under Appearance, Editor, Patterns, Template
  parts, then choose the part and Reset.
- **Addresses returning a 404.** An update does not rebuild WordPress's
  rewrite rules. If a section or search address returns a 404 after an
  update, save Settings, Permalinks once. Changing the search base word on
  the Search engines tab rebuilds them automatically on the next page load.

## Moving from the plugins the theme replaces

The theme rebuilds Unlist Posts & Pages, Pretty Search Permalinks and WPS Hide
Login. While any of these plugins is active, the theme's version of that
feature stands aside, so nothing runs twice.

1. Deactivate the plugin.
2. Open Appearance, Theme Options. The theme imports the plugin's saved settings:
   the list of unlisted items, the search base word, or the login address.
3. For the login address, confirm the address on the Access tab and switch it
   on. It is off until switched on, even when an address was imported.
4. Delete the plugin once the theme's version is working.

## Upgrading to 1.100.12

Documentation only, plus the article count on the Content tab. Replace the theme files; nothing else to do.

## Upgrading to 1.110.0

Replace the theme files. The two new articles are created on existing sites by `boi_create_listicles()` when the version changes; their images are in `assets/images/`.

## Upgrading to 1.109.1

Replace the theme files. The Glossary sync rebuilds once on the version change (`BOI_GLOSSARY_VERSION` 18) to show the five new dictionary notes. Nothing else changes.

## Upgrading to 1.109.0

Replace the theme files. The new article is created on existing sites by `boi_create_listicles()` when the version changes, and the changed al-Zutt article is refreshed by `boi_migrate_seed()` where the owner has not edited it; the new images are in `assets/images/`.

## Upgrading to 1.108.0

Replace the theme files. The new article reaches existing sites through `boi_migrate_seed()` on this version; its images are in `assets/images/`.

## Upgrading to 1.107.0

Replace the theme files. The Glossary sync re-runs (`BOI_GLOSSARY_VERSION` 17) and rebuilds the pages that gained notes; entries the owner has edited are left alone.

## Upgrading to 1.106.1

Replace the theme files. Nothing else changes: `bin/check-content.py` gains a check, with the names already shared listed, with their entries, in `bin/glossary-name-baseline.json`.

## Upgrading to 1.106.0

Replace the theme files. The Glossary sync re-runs (`BOI_GLOSSARY_VERSION` 16) and creates the new entry, Ruh; entries the owner has edited are left alone.

## Upgrading to 1.105.0

Replace the theme files. The new Glossary notes reach existing sites through the Glossary sync, which re-runs because `BOI_GLOSSARY_VERSION` is now 15; entries the owner has edited are left alone.

## Upgrading to 1.104.0

Replace the theme files. The Images tab appears in Theme Options; the setting `pexels_key` is added to `boi_settings` only when you save a key.

## Upgrading to 1.103.0

Replace the theme files. Glossary entries gain an optional "In the classical dictionaries" section from `inc/glossary-data/*.json` (field `lexicon`), styled in `assets/css/glossary.css`; it reaches existing sites through the Glossary sync (`BOI_GLOSSARY_VERSION` 15).

## Upgrading to 1.102.0

Replace the theme files. The new articles reach existing sites through `boi_migrate_seed()` on this version; their images are in `assets/images/`, and the credits are in `inc/images-data.json`.

## Upgrading to 1.100.21

Replace the theme files. Nothing else changes: image files are added in `assets/images/inline/`, and the credits are in `inc/images-data.json`.

## Upgrading to 1.100.20

Replace the theme files. The new articles reach existing sites through `boi_migrate_seed()` on this version; their images are added in `assets/images/`.

## Upgrading to 1.100.19

Replace the theme files. Nothing else changes: thirteen image files are added in `assets/images/inline/`, and the credits are in `inc/images-data.json`.

## Upgrading to 1.100.18

Replace the theme files. Nothing else changes: eleven articles gain an image file each in `assets/images/inline/`, and the credits are in `inc/images-data.json`.

## Upgrading to 1.100.17

Replace the theme files. Nothing else changes. On a site in a subfolder, put a copy of `assets/images/favicon.ico` in the domain root, since browsers ask for `/favicon.ico` there.

## Upgrading to 1.100.16

Replace the theme files. The new files are `inc/lead-list.php`, `assets/css/lead-list.css` and `tests/lead-list-test.php`. If you edited the Reflections, section or archive template in the Site Editor, your copy wins and shows no lead and list; reset it, or add the Lead and list block above the query. Nothing else to do.

## Upgrading to 1.100.15

Replace the theme files. The new files are in `assets/images/`: `favicon.ico`, `icon-192.png`, `icon-512.png`, `icon-maskable-512.png` and `site.webmanifest`. The next admin page rewrites the error pages with the icon. If a `favicon.ico` file sits in the site root, the web server sends it and the theme never answers; delete it to use the theme's.

## Upgrading to 1.100.14

Documentation only: the FTP steps send the `.htaccess` files first. Replace the theme files; nothing else to do.

## Upgrading to 1.100.13

Replace the theme files. The new files are `LICENSE`, `inc/anatomy.php`, `assets/css/anatomy.css`, `patterns/faq.php` and `tests/anatomy-test.php`. Two switches join the Display tab, both on: Contents and Next step. Key points and a review date are typed per article, in the Key points and review box; until then the head of an article looks as before, with the questions panel.

## Upgrading to 1.100.9, 1.100.10 or 1.100.11

Replace the theme files and load one page. Visitors who are not logged in lose the `/wp/v2/users` routes and `?author=N` (both answer 404). Nothing to set.

## Upgrading to 1.100.8

Replace the theme files. On the first admin page load the theme deletes its development files on a production server: `.git`, `.claude`, `tests`, `bin`, `docs` and the editor files (D52). To keep them on a server where you work on the theme, set `WP_ENVIRONMENT_TYPE` to `local` or `development`, or define `BOI_KEEP_DEV_FILES` as true, in `wp-config.php` before you upload. To keep only `.git`, define `BOI_REMOVE_GIT_DIR` as false.

On Apache the same page load adds a "Best of Islam: block direct file access" section to `.htaccess`. If anything that used to load now answers 403, define `BOI_BLOCK_DIRECT_FILES` as false and tell the theme's maintainer which address it was.

## Upgrading from 1.78.0 to 1.78.1

Replace the theme files. On Apache, with "Turn off XML-RPC" on (the default), the theme adds a "Block xmlrpc.php" section to .htaccess on the next admin page load, so the manual step 7 below is no longer needed. Switching it off removes the section. If the Security tab says .htaccess could not be written, ask the host to deny xmlrpc.php at the server.

## Upgrading from 1.77.1 to 1.78.0

Replace the theme files. The Security tab (Appearance, Theme Options, Security) is on by default: XML-RPC, application passwords and REST writes from outside the browser stop at once. XML-RPC was already off in this theme, so only the last two are new.

After the October 2026 attack on another site, the owner checklist:

1. Change the password of every administrator to a long random one used nowhere else, then Log Out Everywhere Else (Users, Profile), or press "Log out all sessions" on the Security tab.
2. Create a new administrator whose name is not the site name, log in as it, and delete the old account, attributing its content to the new one.
3. Press "Revoke all application passwords".
4. Check Plugins for anything not installed by you (auto-posting, AI writers, remote management) and remove it. Check Users for unknown accounts.
5. Run the spam scan, select the results, move them to the trash, then delete the empty spam categories.
6. Purge any page cache, then resubmit the sitemap in Google Search Console.
7. Optional, at the server: deny xmlrpc.php in .htaccess as well, so the requests never reach PHP. Jetpack needs XML-RPC; if Jetpack is used, switch "Turn off XML-RPC" off instead (password logins through XML-RPC stay refused).

If a lock breaks a tool you rely on, add this line to `wp-config.php`. It turns the three locks off without changing the options:

```php
define( 'BOI_SECURITY_LOCK', false );
```

The spam gate holds anything published through XML-RPC or a REST application password as Pending, whatever it says. Seeded articles, pages and Glossary entries are exempt from the gate and the scan.

## If you are locked out

The custom login address can be switched off without logging in. Add this line
to `wp-config.php`, above the comment that marks the end of the
settings you may edit:

```php
define( 'BOI_HIDE_LOGIN', false );
```

The standard `wp-login.php` works again at once. Log in, correct the address
on the Access tab, then remove the line. Activating another theme also
restores `wp-login.php`, since the feature belongs to this theme.

## Data notes

- Vote records survive upgrades. The unique key prevents duplicate records.
- Vote tallies are cached for one hour; clear transients if counts look stale
  after a direct database edit.
- Settings live in the single `boi_settings` option and are merged with the
  defaults on read, so new settings appear without a migration.
- The list of unlisted items lives in the `boi_unlisted` option.

## Rollback

Reinstall the previous package. The votes table is additive across 1.x, so no
schema reversal is needed. Settings added in a later version are ignored by an
earlier one and return when the later version is reinstalled. Should a future
release alter the database schema, the change will be documented here first.

## Roadmap

These are plans, not promises. Each item rests on something on record: the code, the server logs of November 2026, a documented limit, or a feature another of the owner's themes already has. Items leave this list when they ship and go into `CHANGELOG.md`. GitHub had no open issues or pull requests when the list was last checked (2026-11-05), and the code carries no TODO markers.

### Next

- Export, import and reset for Theme Options. The abrahamic theme has all three on its Tools tab; here a move to a new site means setting every tab again by hand.
- Error pages on a network. The admin-page repair runs on the main site only (`boi_error_maybe_sync()`), so the shared drop-ins stop updating once it leaves the theme, while activation and a settings save on any subsite run `boi_error_sync()` unguarded and can rewrite them with that subsite's address. The abrahamic theme hands the job to one site, the lowest ID that runs it.
- Keep the root `.htaccess` sections on a network when one site leaves the theme. `boi_sec_remove_htaccess()` runs on any site's `switch_theme`, and the shared file then lacks both sections until another site's next sync.
- One limit for meta descriptions. `docs/SSOT.md` says excerpts stay within 130 characters, while `docs/SEO.md` and `boi_print_meta_description()` allow 160, and 53 seeded excerpts run between 131 and 160. The owner decides which limit stands; then either those excerpts are shortened or the SSOT is changed to 160.
- A stricter test before the seed migration refreshes an article. `boi_migrate_seed()` treats an article with no stored fingerprint as untouched when its modified time equals its creation time, so an edit made directly in the database, or by a plugin that keeps the old modified date, would be replaced on the next update. Requiring a stored fingerprint before any refresh would close that path (raised in the review of pull request 88).
- The theme's PHP checked against the owner's `php-maintainable-code` skill, for readable, secure and maintainable conventions.

### Later

- A preview of the `bestofislam/lead-list` block in the Site Editor. It renders on the server only, so the editor shows a placeholder. Waits on an editor script for the theme's server-rendered blocks.
- A translation template, `languages/bestofislam.pot`. The FAQ in `readme.txt` tells owners where to put a `.mo` file, but the theme ships no template to make one from. The injil.help and compelling-evidence themes ship a `languages/` folder. Waits on a string freeze after the current round of article work.
- A full Content-Security-Policy. `inc/security-headers.php` sends `frame-ancestors` only, because WordPress and its plugins print inline scripts and styles an enforcing policy would block. Waits on WordPress core.

### Ideas

- A sticky contents variant for long articles, as the compelling-evidence theme has (`#ce-toc`). The contents here are inline only.
- Key points for the 164 seeded articles. The box is built, but no seeded article carries key points yet, so it shows only the questions panel.
- A crawl rule for the heaviest bots. In the November 2026 log QlyzeBot made 1,014 requests and Amazonbot 825, against 378 from Googlebot.
- Fixed publication dates for seeded articles, as the abrahamic seeder has (`post_date`, published or Scheduled), so new articles can go out on a plan.
- An image sitemap that stays on beside Rank Math. `boi_image_sitemap_enabled()` turns it off whenever Rank Math is active, and leaves the images to Rank Math's own sitemap; weigh whether the theme's licence data should reach search engines there too.

## Content review

Three working lists for the editors. "Citations to check" names, article by article, the details still to be checked against the printed sources. "Shamela and sunnah.com review" records the check of classical and hadith citations. "Objections catalogue" lists the public objections the articles answer, with the article for each.

### Citations to check

The 70 listicles were expanded in version 1.60.0 (about 39,000 words became about 90,000). Citations were added only where the writer was confident of them, but the details below were written from memory and need a check against the sources before they are relied on. Each line names the article and the claim. Quran references and the main hadith collections were not listed unless a detail was uncertain.

Also corrected in the old text during the expansion: the Hajj article said everyone wears two white cloths (men only); the Prophet's illness article placed Hudaybiyyah after Khaybar; the Paul article said two Greek verbs were the same (they differ); five places became four in the universe article; the Timbuktu burning date, the Süleymaniye claim and Ibn Abbas's age at the Prophet's death were fixed.

#### Apologetics and Quran
- **al-zutt-allegation**: wording of the Ahmad narration (comparison with al-Zuṭṭ, the "no genitals" clause); Lisan al-Arab on r-k-b; hostile critics writing in Arabic, Syriac and Greek in the early centuries.
- **crucifixion-quran**: Ibn Abbas on *mutawaffika* (al-Bukhari, Commentary, chapter reference); Abu Dawud on Jesus dying after his return; Irenaeus, Against Heresies 1.24.4; Josephus, Antiquities 18.3.3 (about AD 93).
- **ezra-and-mary**: Sanhedrin 21b on Ezra; translation of "son" in 4 Ezra 14:9.
- **jesus-not-god**: Matthew 24:36 manuscript variants; church fathers on John 14:28 and Mark 13:32; Constantinople 680 to 681.
- **verses-read-differently**: Tertullian on John 10:30; Athanasius on John 14:28; Origen on "the God" and "God"; the claim that Matthew softened Mark.
- **paul-and-the-law**: context of "dividing wall of hostility", Ephesians 2:14.
- **second-peter**: Eusebius 3.3 and Jerome on doubts about 2 Peter; the Syriac Bible omitting it; Azami's 1978 edition.
- **where-is-the-injil**: Ibn Abbas report in al-Bukhari, Tawhid, on altered meaning; Ibn Hazm's al-Fisal.
- **islamic-dilemma**: Ibn Abbas report (al-Bukhari, Tawhid); Tafsir and Hudud chapter headings; Raymond Brown on dating the Gospels; Ibn Taymiyya on both kinds of distortion.
- **six-articles-of-faith**: Ibn Hazm on textual alteration; Hasan al-Basri and early commentators on whether Iblis was an angel (al-Tabari).
- **same-god**, **shared-prophets**: al-Tabari on Joseph against Ibn Taymiyya and Ibn Kathir on 12:53; al-Tabari on Isaac and Ibn Kathir on Ishmael; the hadith identifying Khidr (al-Bukhari, Knowledge).
- **quran-mysteries-in-the-stories**: the *malikayn* reading attributed to Ibn Abbas and al-Hasan al-Basri; *mā* in 2:102 as a negation, and the weak chains for the angels story; Ibn Abbas "among the few" on the Sleepers; Zaynab bint Jahsh on the wall of Gog and Magog; Khidr having died.
- **quran-4-34**: Maliki dissolution for harm (*darar*); jurists on abstaining as better; Bakhtiar's rendering; Abu Dawud on the face (Muawiya al-Qushayri); Umar and Abu Dawud on permission to strike.
- **quran-preservation-reddit**, **manuscript-transmission**, **uthman-copies**: Sanaa palimpsest details (Sadeghi and Goudarzi 2012); Birmingham leaves (Suras 18 to 20, Mingana 1572a); al-Bukhari on Yamama, the two witnesses and the Quraysh dialect; the lifted stoning verse; the Hudhayfa and Uthman account; Ibn Masud codex reports; "hundreds of thousands of variants"; Bukhari 4987; Ali approving the burning; Tashkent radiocarbon range 595 to 855; Déroche's date for the Paris manuscript; Samarkand codex attribution.
- **quran-science-claims-graded**: Moore's edition "with al-Zindani" (1982); Galton 1892 and Aristotle, Meteorologica 1; Ibn Rushd, Fasl al-Maqal.
- **quranic-ethics**: Hudhayfa's promise before Badr (Muslim); al-Nawawi's six permitted cases of backbiting; the zakah collector and the gift; Asma and her polytheist mother.
- **quranic-stories-and-history**: dating of the Hegra tombs; Murayghan inscription (Ry 506, 552 CE); Clapp and Ubar; Azad on Cyrus; Nöldeke on the Syriac Alexander legend; Jonah 4:11; Nineveh 612 BC.
- **haman-anachronism**: Vienna door-jamb title and inventory numbers 5821 and 5822 (now presented as disputed); Reland 1705; Nöldeke, Mingana, Lammens, EI1 and EQ attributions; "about eight centuries" from Moses to Esther.
- **natural-world-verses**: Sahih Muslim chapter and the al-Zuhri remark; Qatada on the stars (al-Bukhari chapter heading); Ibn Abbas on the fig and olive; "seas of Persia and Rome".
- **universe-demands-creator**: Weinberg 1987; the 10^500 landscape figure; "120 orders of magnitude".
- **morality-without-god**: Parker as source of the "arc" line; Huxley's 1893 lecture; Nuremberg Charter Article 6(c).
- **moon-craters**, **arabic-words-science**, **instruments-islamic-world**: Latin translation dates of al-Battani and al-Khwarizmi; Riccioli 1651; al-Battani's year-length error; al-Zarqali; al-Biruni's earth radius; al-Ma'mun's survey; the "Algoritmi" text; the *al-jabr* gloss; "zenith" misreading and "600 years"; "alcohol" in sixteenth-century Europe; torquetum attribution; al-Razi and petroleum; Gerard of Cremona and al-Zahrawi; Ibn al-Haytham's experiment; Kraus 1942/43.
- **end-of-time**: that neither Bukhari nor Muslim includes Mahdi reports under that name; Ibn Taymiyya and Ibn Khaldun on them; sunrise from the west and 6:158; the refuge from the Dajjal after the final testimony.

#### Law and sensitive questions
- **apostasy-in-islam**: Hanafis and female apostates; the Bedouin who asked to leave his pledge (al-Bukhari, Judgments); Abu Bakr's wars as precedent.
- **child-marriage-in-islam**, **questions-about-aisha**: Aisha's age reports (al-Bukhari); Quran 65:4 as used by jurists; *khiyar al-bulugh*; Ibn Hajar on Hisham ibn Urwa in Iraq; Tirmidhi report from Abu Musa; her regret after the Camel; the 2,210 hadith count; minimum-age laws of Egypt, Tunisia and Turkey.
- **hijab-and-modesty**: schools' positions on face and hands; the Abu Dawud Asma report and its broken chain; ECtHR and CJEU case descriptions.
- **homosexuality-in-islam**: Kugle's summary; El-Rouayheb on rare prosecutions; early commentators applying 4:16 to men; section 377 as source of some modern statutes.
- **houris-and-paradise**: gradings of the seventy-two report (Tirmidhi, Ibn Majah, Ahmad); book headings and al-Miqdam; Ibn Abbas "only the names"; Fiqh Council of North America 2005.
- **jizya**: Muadh dinar hadith (no chapter heading in the note); Shafii and Maliki positions; *saghirun*; Khalid's letter to al-Hira and Umar's aged dhimmi (Abu Yusuf); al-Baladhuri on Homs; treaties exempting jizya for military service (no source named); Umar II and converts (Dennett).
- **kharijites-and-takfir**: the three-assassin plot (al-Tabari); Ali's reported words; Ibn Khabbab's killing; Ibn Abbas on *kufr duna kufr*; al-Ghazali's Faysal al-Tafriqa; Amman Message details; Ibadis rejecting the label.
- **sharia-and-hudud**: Tirmidhi's grading of "avert the hudud"; Safwan's cloak; Hanafi exemption for women apostates; date of Pakistan's 2006 amendment.
- **slavery-in-islam**: Salman's contract (Ahmad); the Zahiri and minority view that the contract binds the owner; abolition dates (Saudi Arabia and Yemen 1962, Oman 1970, Mauritania 1981); Abduh and Rida; Mu'ta and Usama (Bukhari, Military Expeditions).
- **taqiyya-myths**: Hasan al-Basri quoted in Bukhari, Coercion; details of Ahmad ibn Hanbal's trial.
- **war-and-killing-in-islam**: Ottoman officials refusing the 1915 orders; Srebrenica figure; Marrakesh Declaration details.
- **islam-religion-of-peace**: Abu Bakr's instructions (Muwatta, Jihad); Bukhari and Muslim Jihad chapters; Bukhari Adab; Muslim, Zakat, on the Khawarij.
- **islamophobia-facts**: French "islamophobie" in the early twentieth century; Moriscos expulsion dates; Home Office finding on victims; the 2019 APPG decision; titles of the Christchurch Royal Commission report and UN resolution 76/254.
- **crusades-facts**: ransom rates (10, 5 and 1 dinars); Fatimid retaking of Jerusalem 1098; Saladin's executions after Hattin; al-Sulami's Kitab al-Jihad.
- **fate-and-free-will**: death dates of Jahm ibn Safwan (746) and al-Maturidi (944).

#### Practice, history and people
- **before-your-first-friday-prayer**: Uthman's added call (Bukhari); bathing and scent; women, travellers and the sick; the rows hadith.
- **funeral-dhikr-dua**: women's shroud and spouse washing spouse by school; the Muslim report of forty people at a funeral.
- **hajj-rites-explained**: Ahmad report from Ibn Abbas linking the stoning to Abraham; Ibn Kathir preferring Ishmael.
- **kaaba-witnesses**: Bukhari on Dhu al-Khalasa; Aisha on rebuilding the Kaaba ("Book of Hajj").
- **qibla-change**: Ibn Umar and the Quba dawn prayer; Abu Dharr's forty years between the mosques; Tirmidhi "between east and west"; praying toward both in Mecca.
- **ramadan-last-ten-nights**: Ibn Abbas on generosity in Ramadan (Bukhari, Revelation); the quarrel report (Ubadah, Bukhari, Faith); Ubayy's oath for the twenty-seventh (Muslim).
- **why-five-prayers**: Bukhari headings for the man of Najd, Gabriel leading the prayer and the night journey.
- **who-saw-gabriel**: Gabriel appearing as Dihya al-Kalbi (Bukhari and Muslim).
- **who-is-allah**: that many hadith scholars treat the list of 99 names as a narrator's insertion (Tirmidhi); headings for "My mercy overcomes My wrath".
- **why-no-interest**: Fiqh Academy resolution (dated only to the 1980s); grading of *al-kharaj bi'l-daman*.
- **prophet-died-poisoning**: Abu Dawud, Blood-Wit, on the woman handed over; Anas's narration headings; death as martyrdom; length of the illness.
- **prophets-you-may-not-know**: Dhul-Kifl as not a prophet; Bukhari, Tafsir 2:136.
- **women-in-the-prophets-life**: Abu Musa on Aisha (Tirmidhi); Shifa bint Abdullah (Abu Dawud); the Zarkashi title; Mariya's status; headings for the Fatima reports.
- **companions-who-started-with-nothing**: Abu Dharr and Muawiya on 9:34 (Bukhari); Ammar's family as allies of a Meccan clan; Abu Hurayra's nickname and late conversion; Abdul Rahman among the ten promised Paradise; the Salman "ahl al-bayt" report (flagged weak); generic notes for the Abu Dharr and Abu Hurayra reports.
- **scholars-who-shaped-islamic-study**: *Tanwir al-Miqbas* attribution; Abu Dawud's letter to Mecca; the "several hundred thousand" figure; al-Ghazali's lost speech and three condemned positions.
- **western-scholars-of-islam**: Annali dell'Islam 1905 to 1926; Goldziher's Muhammedanische Studien 1889 to 1890; Goitein's six volumes and teaching posts; al-Shafii on non-Arabic words; al-Suyuti, al-Itqan.
- **seven-libraries**: Qarawiyyin library under Abu Inan (fourteenth century); Ahmad Baba anecdote; Said al-Andalusi on al-Mansur's purge; Makdisi's thesis.
- **library-of-alexandria**: John the Grammarian as Philoponus (died about 570); John of Nikiu's silence.
- **indonesia**: Silaban and the Istiqlal foundation stone 1961; the 2024 declaration at Istiqlal; communal violence in Maluku and Sulawesi 1999 to 2002; Ricklefs; Bukhari on fasting at the crescent (no chapter number).
- **islam-in-china**: Arab troops and An Lushan; Zheng He's forebears as Hajji; Xi'an mosque axis; Zheng He's Buddhist name; Du Wenxiu in Britain 1872; who graded "seek knowledge even as far as China" weak.
- **islamic-art-and-the-one-god**: Lu and Steinhardt 2007; Necipoglu on Abu al-Wafa; Bukhari, Clothing, on image-makers.
- **dome-inscriptions**: Grabar, *The Shape of the Holy*, on the Dome and the Holy Sepulchre.
- **fastest-growing-religion**: Pew 2025 on the unaffiliated; Ahmad wording of the farewell sermon.
- **mosques-adapt-to-place**: no flags raised.

#### New articles (1.61.0)
- **black-stone-and-kissing**: the Tirmidhi report that the stone came from Paradise and was darkened by sins (cited generally; gradings differ); the Qarmatian raid in 930 CE and the return about twenty years later (cited to Ibn al-Athir, al-Kamil; check the source and the dates); Ibn Ishaq via Ibn Hisham on the Quraysh cloak story; Bukhari chapter headings (pointing at the corner from a camel, kissing the stone, the intentions hadith); Bukhari, Maghazi, on the 360 idols; Quran 5:76, 10:18, 39:3, 22:26 to 29, 22:31; the attribution of Umar's saying to Ibn Hajar's Fath al-Bari; Wellhausen, Reste arabischen Heidentums (1887) and Hawting, The Idea of Idolatry (1999), and the simplification of Wellhausen's view; the comparison with the Western Wall and the Stone of Anointing (Exodus Rabbah 2:2 on the Divine Presence and the Wall; the pilgrim practices cite no specific source).
- **camel-urine-hadith**: the book headings in Bukhari and Muslim for the Ukl and Uraynah report; the schools' positions on the purity of the urine of edible animals; the reading of Quran 5:33 to 34 as hirabah; the reports against mutilation; the WHO fact sheet on MERS-CoV and raw camel milk and urine.
- **who-wrote-the-quran**: Sadeghi and Bergmann (Arabica 57, 2010) and the Sanaa palimpsest summary; Azami and Wansbrough as characterised, and the claim that few scholars hold Wansbrough's late dating today; the Encyclopaedia of the Quran article "Illiteracy" and the "of the gentiles" reading of ummi; the Sahih Muslim book title for the report on writing nothing but the Quran, and Bukhari's reports permitting writing; Aisha's report in Bukhari (Revelation) and the commentators' literal reading of "I am not a reader"; Neuwirth, Studien zur Komposition der mekkanischen Suren (1981); Irenaeus 3.1, Eusebius 3.39, Brown (1997), Bauckham (2006) and the claim that Gospel headings appear in the manuscripts; the order of Yamama, Abu Bakr and the Uthman committee.
- **preservation-and-truth**: the manuscript-evidence comparison for the Gospels and the Hebrew Bible (Metzger and Ehrman, Tov); the list of alleged errors and the answers given. No manuscript count is stated and none should be added without a source.
- **relationship-with-god**: Quran 13:11, 20:82, 11:61, 6:103, 42:25, 3:135, 2:37, 25:63 and the paraphrase of 19:88 to 93; the Sahih Muslim report that God divided the prayer between Himself and His servant; Muslim, Book of Repentance; the wording of the hand's-span hadith and the Bukhari heading for the ninety-nine names; William James, The Varieties of Religious Experience (1902).
- **buraq-and-miracle-stories**: the minority "spiritual journey" reports attributed to Aisha and Muawiya (Ibn Ishaq, al-Tabari; chains disputed); the grade of the speaking-donkey narration as fabricated (no critic or book is named; add a source); the names Ufayr and Yafur in Bukhari (Jihad) and Muslim (Faith) and Ibn Hajar's discussion in Fath al-Bari; the Buraq wording in Bukhari (Beginning of Creation, Malik ibn Sasaa) and Muslim (Faith); the claim that wings and a human face come from later art and popular tradition (unsourced); that many Jewish and Christian readers treat Jonah as a parable (uncited); commentators' differences on whom the Prophet saw in Quran 53; Jonathan Brown, Hadith: Muhammad's Legacy in the Medieval and Modern World (2009).

#### Glossary entries from Toward Islamic English
The Glossary now takes its terms from Ismail al Faruqi's lists (391 entries, 332 of them new, the rest matching entries that already existed). The terms and Arabic were read from a scan, and the explanations were written from memory. Check these first:
- **Arabic read from the scan** may carry small errors, above all in long phrases and in entries on the upside-down page (printed p. 36): the entries for the letters ṣād and ḍād, among them ṢALLĀ, AL ḌUḤĀ, AL MUSTAḌʿAFŪN, ISTAḌʿAFA, ḌALLA, ḌALĀL, ḌĀLL, SHARAʿA and AL SHARĪʿAH. ṬAGHĪYAH's Arabic is a guess.
- **Where the writer changed the book's form:** `immah` (the book's عمة read as عمامة, turban), `hamid-al` (changed to al-Ḥamīd), `tartil`, `tazir-al`, `ya-arham`, `rida-al`, `al-uswah-al-hasanah`.
- **Where the book's definition looks wrong and the standard meaning was used:** `salah-al-fard` and `salah-al-sunnah` (the book's definitions look swapped), `miraj-al` (the book's "3 A.H." cannot be right), `musnad-al` ("Bayhaqiy ibn Mukhlad" dropped as garbled), `wa-akhiru` (the book says "our" where Quran 10:10 says "their"), `raziq-al` (the Quran names al-Razzaq, 51:58), `ruku` and `rakaa` (the book says "kneeling"; the act is a bow).
- **Attributions from memory:** the Tirmidhi, Abu Dawud and Bukhari sources for the supplications (new-moon, meal, clothing, farewell, return from travel, after the call to prayer, hasbuna Allah); the critics named in `ilal-al-hadith` and `jarh-wal-tadil-al` and the date 938; the schools' views in `ijma-al-sukuti-al`; the count of 29 surahs and 14 letters in `fawatih-al-suwar`; al-Jassas and Ibn al-Arabi for the Ahkam al-Quran genre; al-Suyuti on abrogation in `mansukh-al`; the examples in `masalih-al-mursalah`; the date of the hijrah calendar in `hijrah`.
- **Left unnamed on purpose:** the fifth school in `madhahib-al-khamsah` (a Shia school), to keep the site's scope.

#### New articles from the Winn workbooks (1.64.0)
All 15 were written from memory with no source opened. Every hadith book and chapter name, date, verse number, scholar's position and statistic below needs checking against the source before the article is treated as final. The most sensitive articles (medina-jewish-tribes-and-qurayza, raids-badr-and-the-conquest-of-mecca, prophets-marriages-zaynab-and-others, apes-and-pigs-and-the-jews, terrorism-and-muslims-the-data, race-in-quran-and-hadith, quran-on-christians) should be read by a person first.
- **raids-badr-and-the-conquest-of-mecca**, checked 15 October 2026. VERIFIED and corrected: Ibn Ishaq's counts (27 expeditions, 38 missions, from Ibn Kathir, al-Bidaya, vol. 5, pp. 216–217); Bukhari 2048 (Ibn Awf asked whether there was a market, and traded at Qaynuqa); Bukhari 3045 and 4086 (ten scouts under Asim ibn Thabit, pursued by Banu Lihyan of Hudhayl); Ahmad 2216 (ransom by teaching the children of the Ansar to write; good per al-Arnaut); Bukhari 1846 (Ibn Khatal); Abu Dawud 2683 (Ibn Abi Sarh; sound per al-Albani); Bukhari 4318–4319 (Hawazin captives); Bukhari 4339 (Khalid, "I am free of what Khalid has done", said twice). Also VERIFIED: the woman trampled in Umar's caliphate (Sara, in Ibn Ishaq as given by al-Baghawi on 110:1); Nakhla (Ibn Ishaq in Ibn Kathir on 2:217); Badr (Bukhari 3957, 3986; Mishkat 3971 on the two executions); the ten-year truce (Abu Dawud 2766) and the return term (Bukhari 2731–2732); the ten thousand in Ramadan (Bukhari 4276). The Meccan army of about a thousand and the withdrawal of Banu Zuhra rest on secondary sources (Mubarakpuri, via Wikipedia) and should be confirmed in Ibn Hisham.
  Original list: the counts of expeditions led in person ("about 27") and of missions; Bukhari, Sales, for Abd al-Rahman ibn Awf; the al-Raji accounts (Ibn Ishaq against Bukhari) and the claim that no raid on Adal or Qarah followed; Nakhla details (last day of Rajab, the ransom); Badr figures (313, about 1,000, about 70 captives, the executions of al-Nadr and Uqba); the Ahmad report on ransom by teaching children; the Hudaybiyah terms; the Mecca figures (about 10,000), the Ibn Khatal reports, the pardons of Ikrima and Abd Allah ibn Sad, and the story of the woman trampled in Umar's caliphate; Hunayn (the return of captives, Khalid at Banu Jadhima). Bukhari chapter headings in the Notes are approximate.
- **revisionist-case-weighed**, checked 15 October 2026. VERIFIED: Birmingham dating (University of Birmingham FAQ); Sanaa range 578–669 (islamic-awareness.org reporting Sadeghi and Bergmann); Der Islam 87 pp. 1–129; Bishapur drachm (Walker p. 97, Gaube, Johns); PERF 558 (Grohmann); Serjeant, JAOS 110, pp. 472–486; Bukhari 40. Corrected: Mustafa Shah did not voice reservations about the dating (he said it consolidated the traditional account), so the named reservations were removed. Not yet checked: Arabica page range, Ibn Abi Dawud, Doctrina Jacobi and Sebeos in Hoyland, Wasit and al-Baladhuri, the 70 AH issue. Original list: Sadeghi and Bergmann (Arabica 57, 2010) and Sadeghi and Goudarzi (Der Islam 87, 2012); Ibn Abi Dawud, Kitab al-Masahif; the Birmingham (568 to 645 CE) and Sanaa (578 to 669 CE) radiocarbon figures and the Shah and Deroche reservations; the Bishapur drachm (66 and 67 AH) and the 70 AH issue (Walker 1941 was not opened); Quran 3:19 on the Dome; PERF 558 as a 22 AH papyrus; Doctrina Jacobi and Sebeos, and Hoyland (1997); Serjeant's 1990 review; the claims about Wasit and al-Baladhuri; the al-Bara hadith on sixteen or seventeen months; the verse references (106:1-2, 62:11, 2:198, 22:78, 3:144, 33:40, 47:2, 48:29, 2:142 to 144).
- **medina-jewish-tribes-and-qurayza**, checked 15 October 2026. VERIFIED: Bukhari 4028 (Nadir, Qaynuqa, Qurayza), 2329 (Khaybar sharecropping), 4121 (Sa'd's verdict and "the judgment of God"), 4037 (Muhammad ibn Maslama's leave "to say a thing"), 4039 (Abu Rafi'), 6153 (Hassan ibn Thabit); Arafat, JRAS 108/2 (1976), pp. 100-107; Kister, JSAI 8 (1986), pp. 61-96; Serjeant, BSOAS 41 (1978), pp. 1-41 (eight documents); Wellhausen, Skizzen IV (1889), pp. 65-83; Ibn Ubayy's plea for Qaynuqa (al-Tabari from Ibn Ishaq, via Wikipedia); the two transmissions of the Constitution (Ibn Ishaq and Abu Ubayd). Corrected: "with their families" removed for Qaynuqa (the sources say only that they were expelled); the claim that Qurayza "never launched an assault" removed (NOT_FOUND); Ka'b ibn Zuhayr no longer placed on the Mecca list. Secondary only, to confirm: Ibn Adi, al-Kamil, vol. 7, p. 326, and al-Albani on the Asma chain (from Wikipedia).
  Original list: the dates (2, 4, 5 and 7 AH); Ibn Ubayy's intercession; Bukhari chapter names (Maghazi, Adab); the Prophet's "judgment of God" remark; the Nadir leaders at Khaybar; Wellhausen, Watt and Serjeant on the Constitution and their titles; Abu Ubayd's Kitab al-Amwal as a second source; the claim that Qurayza never launched an assault; the weak grading of the Asma and Abu Afak chains; the permission to Muhammad ibn Maslama; Kab ibn Zuhayr's pardon; the Arafat and Kister articles (not read); Quran 6:164, 47:4 and 33:9 to 11.
- **first-revelation-demonic-claim**, checked 15 October 2026. VERIFIED: Bukhari 3, 6982, 7, 2731; Muslim 177a; Ibn Hajar, Fath al-Bari 12:359 on the mountain passage as al-Zuhri's unconnected report (via IslamQA 152611); Khadija's test in Ibn Hisham 1:239 (secondary); al-Tabari's list of names on 16:103, including Jabr. Not yet checked: the brocade detail, the Meccan dating of 17:110 and 25:60, and "early passages mostly say Lord". Original list: Ibn Hajar on the mountain-peaks passage as al-Zuhri's balagh; the Ibn Ishaq details (brocade, the lap test, "poet or possessed"); Waraqa's "Namus" wording; Aisha's question about the horizon vision (Muslim, Faith; Bukhari, Beginning of Creation); Jabr in al-Tabari on 16:103; Abu Sufyan and Heraclius; Suhayl at Hudaybiyah; the verse numbers 26:193-194, 16:102, 81:19-25, 53:5-6, 26:210, 35:6, 16:98, 17:47-48, 10:16, 29:61, 31:25; the Meccan classification of 17:110 and 25:60; the claim that early passages mostly say "Lord".
- **islamic-rites-before-islam**, checked 15 October 2026. VERIFIED: Bukhari 1582, 1664, 3364, 2050, 3521, 2002, 876, 3828, 1622; Muslim 1134a; Abu Dawud 1919 (sound). Corrected: Bukhari makes Amr ibn Luhayy the first to dedicate animals to idols; it does not say he set up the idols. Not yet checked: the Ibn Ishaq passages, Jabir's talbiya, the Adnan-lineage scholars, Qusayy's date. Original list: Ibn Ishaq on the rebuilding, the cloak, Qusayy's offices and tahannuth; Bukhari (Hajj, Prophets, Manaqib) chapter names and the Hagar report; the claim that many early scholars held the lineage secure only to Adnan; Amr ibn Luhayy, Zayd ibn Amr, the Ukaz occasion for 2:198, Ashura and Friday reports, and the proclamation of 9 AH; "legacy of Abraham" (Abu Dawud, Tirmidhi); Jabir's talbiya; Genesis 21:21 and 25:13 to 15; "almost unexcavated"; Qusayy in the fifth century.
- **terrorism-and-muslims-the-data**, checked 15 October 2026. VERIFIED and corrected: GAO-17-300 (62 far-right and 23 jihadist fatal attacks, 106 and 119 deaths); TE-SAT 2018 (137 separatist, 33 jihadist, 62 of 68 deaths); GTI 2025 group ranking (replacing an unsourced list); Tantawi (AFP, 14 September 2001, via Kurzman); Saudi decree of November 1962 (Awad report, para. 409); Bukhari 25, 3015, 3166; Muslim 1853; Pew 2013 country figures. Removed all "needs checking" asides. Not yet checked: Pew 2017 population figure, the Muwatta report of Abu Bakr, Tahir ul-Qadri, the 9/11 figure. Original list: Pew 2017 (about 1.8 billion in 2015); "nearly 3,000" on 9/11; the Global Terrorism Index groups and countries (no edition given); Europol TE-SAT; GAO-17-300; Pew 2013; Tantawi's condemnation; the 2014 open letter to al-Baghdadi; Tahir ul-Qadri's fatwa; Saudi abolition of slavery in 1962; the 1924 abolition of the caliphate; Pape and Sageman; the 1998 World Islamic Front statement; hadith book headings. The Saudi sermon was not located, and the text says so.
- **magic-jinn-and-satan-hadiths**, checked 15 October 2026. VERIFIED and cited by number: Bukhari 5763, 6226, 1144, 1142, 3295, 608, 3860; Muslim 2189a, 450a, 2814a, 2815. Corrected: the fodder report is in Muslim 450a (Tirmidhi removed). Not yet checked: the readings of "aslama"; al-Nawawi, Ibn Hajar, al-Jassas and Abduh as named.
  Original list: all hadith book names in Notes 1, 7, 9, 10 and 12; the Tirmidhi fodder wording; al-Nawawi, Ibn Hajar, al-Jassas and Abduh as named; the identification of the Aisha "devil" report with the jealousy narration in Muslim; Muslim's "aslama" readings; Quran 38:41, 20:66, 5:67, 81:22 and 68:2.
- **sira-ibn-ishaq-hisham-tabari**, checked 15 October 2026. VERIFIED: Ibn Hisham's omissions (Guillaume p. 691); al-Shafii and Malik on Ibn Ishaq (al-Dhahabi, Siyar 7:33-55, via IslamQA 148009); Abu Dawud 4358; Muslim 3004; Bukhari 2990; Tirmidhi 3954 for Zayd. Not yet checked: death dates, Salama ibn al-Fadl, Yunus ibn Bukayr, Ibn Sad as al-Waqidi's secretary, Battle of the Masts (34 AH appears in the Cultural Atlas, p. 201), Umar's sheet. Original list: Ibn Hisham's introduction on omissions; Tabari and Salama ibn al-Fadl; Yunus ibn Bukayr; all death dates; Ibn Sad as al-Waqidi's secretary; Tabari's introduction; Watt's view; al-Shafii on Ibn Ishaq; the Abdullah ibn Sad ibn Abi Sarh reports (6:93, the pardon, the Battle of the Masts in 34 AH); Abu Dawud on apostates; Ibn Khatal; Zayd ibn Thabit in al-Hakim; Muslim on Zuhd; Bukhari on carrying the Quran into enemy land; Umar's sheet in Ibn Ishaq; Ibn al-Salah; the Dhahabi, Guillaume and Watt locations.
- **prophets-marriages-zaynab-and-others**, checked 15 October 2026. VERIFIED and cited by number: Bukhari 4782, 7420, 5086, 4138, 4788; Muslim 1428b, 1438a, 1456a, 2972a; Abu Dawud 3931 (good); Tirmidhi 3894 (sound). Corrected: Aisha's "conceal this verse" now cited to Bukhari 7420 (Tirmidhi 3207 is weak; Muslim 177 concerns 5:67); the ransom wording is in Muslim, not Bukhari; Zaynab's age changed from "about thirty-five" to her late thirties, from al-Tabari's fifty-three lunar years at death; Ibn Kathir and al-Qurtubi described separately (only al-Qurtubi gives the Ali ibn al-Husayn reading). Not yet checked: Rayhana, Mariya, Watt on alliances, Umm Habiba in Ibn Sad.
  Original list: Aisha's "conceal this verse" report (Muslim or Tirmidhi); the Ali ibn al-Husayn reading and its attribution to Qurtubi and Ibn Kathir; Zaynab "about 35"; Anas in Muslim on Zayd carrying the proposal; Ibn Umar's "Zayd ibn Muhammad" report in Bukhari; Abu Dawud on Juwayriyah; Anas on Safiyya's freedom as dower; Tirmidhi on Safiyya's taunts; Rayhana's disputed status; Mariya as "mostly concubine"; Abu Said's azl report; Muslim, Suckling, on istibra and 4:24; Aisha's "hastens to fulfil your wishes"; the "no fire lit" report; Watt on the alliances; Umm Habiba in Ibn Sad; Quran 2:220, 24:32 to 33, 90:13, 23:5 to 6; the modern-scholars claim on slavery and treaties.
- **apes-and-pigs-and-the-jews**, checked 15 October 2026. VERIFIED: al-Tabari on 2:65 (Mujahid figurative, Ibn Abbas literal) and on 2:62 (Ibn Abbas's report with 3:85; Mujahid and al-Suddi); Muslim 2663b; Bukhari 7 (Heraclius's courtiers); Abu Dawud 3002 (weak per al-Albani; chain through a client of Zayd ibn Thabit); Bukhari 2926 and Muslim 2922; Hamas charter, article 7; Bukhari 4121; Bukhari 2916 (armour at his death). Added: al-Tabari's own preference for the literal reading. Not yet checked: the place of Abu Dawud 3002 in Ibn Ishaq's narrative after the killing of Ka'b.
  Original list: Mujahid and Ibn Abbas on 2:65 in al-Tabari; Muslim, Destiny, on transformed peoples leaving no descendants; Tabari on 2:62 and 3:85; Abu Dawud 3002 (chapter heading, and its place after the killing of Kab); the rock-and-tree hadith chapters and Hamas charter article 7; Sad ibn Muadh and Qurayza; Bukhari, Sales, for the pledged armour; the context of 5:57 to 60; the claim that the classical rules rest on the Quran and the treaties.
- **satanic-verses-report**: checked (see 1.68.0 branch). Ibn Abbas's birth VERIFIED from al-Isaba 4:122 (thirteen at the Prophet's death) and Siyar 3:335 (a report of ten). Original list: Tabari and Ibn Sad as carriers; the claim that Bukhari and Muslim lack it and the chains are mursal; Ibn Abbas's birth about 3 years before the Hijra; Qadi Iyad, Ibn Kathir, al-Albani (Nasb al-Majaniq) and Ibn Hajar; Watt (1953); Shahab Ahmed, Studia Islamica 87 (1998); the Bukhari prostration report at the end of sura 53; Quran 53:19 to 23, 53:26, 41:26, 22:53, 15:9.
- **abrogation-and-the-sword-verse**, checked 15 October 2026. VERIFIED: Abu Dawud 2682 (sound); al-Tabari on 2:256 (rejects abrogation, added) and on 2:62; al-Razi on 2:106 (Abu Muslim); al-Suyuti about twenty and Shah Waliullah five (via Deoband.org). Not yet checked: al-Shatibi, Ibn Kathir on 2:256, the "over a hundred" count. Original list: al-Suyuti's "about twenty" (al-Itqan) and Shah Waliullah's five (al-Fawz al-kabir); Abu Muslim al-Isfahani and al-Razi at 2:106; al-Shatibi (al-Muwafaqat); Tabari and Ibn Kathir on 2:256; the Abu Dawud occasion of 2:256; Ibn Abbas on 2:62 and 3:85; "over a hundred" listed verses (no source); paraphrases of 9:1 to 7, 9:13, 10:99, 18:29, 5:69.
- **end-of-the-world-prediction**, checked 15 October 2026. VERIFIED: Abu Dawud 4350 (sound per al-Albani), Muslim 8a, Bukhari 6504, al-Suyuti's treatise and its argument. Corrected: the 500-year figure is Sa'd's answer, not the Prophet's; al-Suyuti's argument rested on the seven-thousand-year reports, and others rejected it; first-person wording and "not checked" asides removed. Still unchecked: al-Tabari I:181-182. Original list: the Abu Dawud report from Sad ibn Abi Waqqas ("half a day", "five hundred years"; no number or grading given); the claim that the exchange does not cite 22:47; al-Tabari I:181-182 (not opened); the al-Suyuti treatise (title unconfirmed); the hadith of Gabriel and the "two fingers" saying; Quran 7:187, 33:63, 31:34, 79:42 to 44, 22:47, 32:5, 70:4; the "most often repeated prophecy" claim, which the text says could not be confirmed.
- **race-in-quran-and-hadith**, checked 15 October 2026. VERIFIED: Bukhari 7139, 30, 3754, 1245, 4469, 7142, 2886; Ahmad 23489; al-Albani, Sahiha 2700 (Jabir); Tirmidhi 3931 (hasan per al-Tirmidhi, weak per Darussalam). Corrected: Bukhari 30 does not name Bilal; a first-person sentence removed; reader-facing "to be checked" notes resolved. NOT_FOUND: the Nabtal wording. Not yet checked: Ibn Khaldun, al-Jahiz, al-Tabari on Noah's sons, Salman and the trench. Original list: the farewell sermon in Ahmad and its grading; the Abu Dharr rebuke and whether versions name Bilal; "leaders from Quraysh" in Bukhari, Ahkam; the "raisin" and "slave of the dinar" wordings and locations; the nabtal wording (left vague); Usama's expedition; Salman and the trench; the funeral prayer for the Negus; the weak Tirmidhi report on Noah's sons; Ibn Khaldun on Ham; al-Jahiz, Fakhr al-Sudan; Tabari on Noah's sons; Genesis 9:18 to 27; Bilal's torture.
- **quran-on-christians**, checked 15 October 2026. VERIFIED: Bukhari 1601, 435–436; al-Azraqi via al-Dhahabi, Siyar 26:67–69. Corrected: al-Tabari on 5:51 gives three occasions, judges none established and reads the verse generally (the article had presented one occasion as his). Not yet checked: the Tabuk details (Ayla) and the Muta link. Original list: Tabari's occasion for 5:51; al-Azraqi on the image of Mary and Jesus in the Kaaba (the article does not say it was erased); Bukhari on the images of Abraham and Ishmael; the deathbed hadith (Bukhari, Prayer; Muslim, Mosques); the Tabuk details and the link to Muta; Ibn Abbas via Tabari on 2:62 and 3:85; 5:74; 98:1; 9:30; the claim that classical law protected life, property and worship of those who paid; the generic reference to the jizya article.

**Status of the first tier: complete (15 October 2026).** Every point in the lists above, including those marked "not yet checked" in the first pass, was closed in the two follow-up passes recorded at the end of this file: each is VERIFIED with its source, corrected in the text, or removed where no source could be found (the Muta envoy, the claim that Qurayza never attacked, the "since 1646" date).

#### New articles from the Winn workbooks, second tier (1.65.0)
Written from memory with no source opened, like the first tier. Check every hadith book and chapter name, date, verse reference, scholar's position and figure before treating an article as final.
- **fighting-verses-in-context**, checked 15 October 2026. VERIFIED: Muslim 1731a; Bukhari 4905; al-Tabari on 9:29 (Tabuk, saghirun), 9:123 (Byzantines), 5:33 (Ibn Abbas, nos. 9236, 9248); Mishnah Sanhedrin 4:5 with the "of Israel" variant; the Mosque of Dissent (al-Tabari, trans. vol. 9, pp. 60–61). Corrected: al-Tabari and his authorities gloss fitna in 2:191, 2:193 and 8:39 as shirk; the article had made "persecution" the majority reading. Also VERIFIED: al-Tabari on 9:4 and 8:17; Ibn Kathir on 9:1; the jizya exemptions (Abu Ubayd, Abu Yusuf, al-Halabi). Original list: Tabari and Ibn Kathir on 9:1 to 13, 9:29 (Tabuk) and 9:123 (Byzantines); the positions on abrogation by 9:5, jizya exemptions and prisoners in 47:4; the Badr numbers; Mishnah Sanhedrin 4:5 as the parallel to 5:32; the Ibn Abbas report on the penalties in 5:33 (Tabari); the commanders' instruction against mutilation and killing children (Muslim, Bukhari); the "Muhammad kills his companions" report; Abdullah ibn Ubayy; the demolished mosque of 9:107; the glosses of fitna.
- **first-muslims-kept-the-faith**, checked 15 October 2026, against Guillaume's translation of Ibn Ishaq (archive.org text) and sunnah.com. VERIFIED: Utba; Surah 109 occasion (al-Tabari); the order of first converts; 73 men and 2 women at the second Aqaba; Bilal; the family of Yasir; the boycott ("two or three years"); Bukhari 3612, 3231; Jafar and Surah Maryam. Corrected: the boycott length; 22:39 placed at the departure from Mecca per al-Tabari; the Abyssinia figures (first party of twelve men and four women per al-Mubarakpuri; eighty-three men in all per Ibn Ishaq). Original list: Utba ibn Rabia and Quran 41; Quran 109 and the proposal to worship each other's gods; Ibn Ishaq's order of the first converts; the numbers at the second pledge of Aqaba; the Abyssinia details (fifth year, about fifteen, Jafar and Surah Maryam); Sumayya as the first martyr; the length of the boycott; Bukhari headings for Khabbab, Taif and the first revelation; Aisha's Taif report; 22:39 as Medinan; Musnad Ahmad attributions (Umm Salama, Kab ibn Malik).
- **quran-anachronism-claims**: Tabari naming Nimrod at 2:258; the commentators on "his" in 6:84; Talmud Bava Batra 15a to b on Job; the Bukhari "sixty cubits" report; Adam in India and Adam's baldness in Tabari's History and their grading; the 104 scrolls via Abu Dharr in Ibn Hibban; the sense of salb in 20:71; the midrash on the raven and burial (undated, unnamed); the Gideon parallel to 2:249.
- **jihad-meaning-quran-hadith-jurists**: Bukhari chapter names and attributions (Ibn Masud, Aisha on women's hajj, Abu Hurayra, Abd al-Qays, the martyrs list, the parents report); the Ibn Umm Maktum report on 4:95; the Muadh hadith and the "mujahid struggles against his self" report (Tirmidhi, Ahmad); the grading of the tyrant report; Ibn Taymiyya and Ibn Hajar on the "greater jihad" report; Ibn al-Qayyim's Zad al-Maad; Shaybani and Ibn Qudama on fard kifaya; Surah 25 as Meccan; the gloss on Abd al-Qays and the road through hostile land (check or cut).
- **talmud-midrash-apocrypha-borrowing**: the dates of Pirke de-Rabbi Eliezer, Targum Sheni, Pseudo-Jonathan, Genesis Rabbah, Pseudo-Matthew, and the Infancy Gospel of Thomas and Protevangelium; the raven in PRE 21 and the calf in PRE 45; the manuscript variants of Sanhedrin 4:5; Shabbat 88a, Hagigah 12b, Rosh Hashanah 16b to 17a, Taanit 23a; Jubilees 12; Targum Sheni on the queen; Geiger's date of 1833; the cradle-speech motif in the Arabic Infancy Gospel; Quran 6:74 to 79 and 19:30.
- **hanif-poets-and-musaylima**: Bukhari and Ibn Ishaq on Zayd ibn Amr (date of death, the burial of girls, meat offered to idols); the wording and attribution of his verses; the Bukhari account of Waraqa; Musaylima, Banu Hanifa, Yamama in 11 to 12 AH and the sayings in Tabari; Quran 16:103, 25:4 to 5, 2:23, 17:88, 36:69, 69:41, 3:3, 21:25, 2:135, 3:67, 16:120. "No recorded accusation citing Zayd" is an argument from silence.
- **schacht-isnads-dating-hadith**: Ibn Sirin and Ibn al-Mubarak in Muslim's Introduction; Abu Shah, Ali's sheet and Abdullah ibn Amr (Bukhari, Knowledge); the prohibition on writing (Muslim, Zuhd) and Abu Dawud's permission; Umar ibn Abd al-Aziz's letter; Hammam's sahifa in Ahmad (Azami); Motzki on Abd al-Razzaq; Juynboll's common link; Bukhari's "600,000" in Khatib; Ibn Hajar's Hady al-Sari; Ibn al-Qayyim, Ibn Abi Hatim, Ibn Sad, Ibn al-Salah. The "99.5% spurious" figure has no identified source.
- **was-muhammad-illiterate-ummi**: the Encyclopaedia of the Quran article "Illiteracy" (title unconfirmed); Ibn Hajar on Hudaybiyah (no chapter); the unnamed "few medieval scholars" who held he wrote a little; the Hudaybiyah wording variants (Bukhari, Sulh); Aisha's hadith of the first revelation; 3:20, 3:75, 2:78, 25:5, 16:103, 75:17 to 18; the inference that 29:48 addresses people who had known him forty years; Lane and Lisan al-Arab on qara'a and alaq.
- **ridda-wars-abu-bakr-fadak**: the Abu Bakr and Umar exchange (the hobble or young goat variants); the two bracelets dream (Bukhari, Maghazi); Tulayha and Sajah; the claim that the campaigns began as defence; the size of Banu Hanifa; the Fadak account (Bukhari, Maintenance of Family, which may concern a different property); Fatima's anger; the Shia position, stated generally; Tabari, year 11 AH.

#### Material from al Faruqi (1.66.0)

Checked against the book itself: Ismail Raji al Faruqi, *Islam: Religion, Practice, Culture and World Order* (IIIT, 2012). Printed page numbers equal the PDF page less 20. Every added passage and quotation was compared with the cited page. Three of his statements were deliberately left out: that Muslims claim no miracles for Muhammad (p. 27); that no faction ever accused another of altering the Quran (p. 26), which later Shia polemic did; and that Khadija bore all the Prophet's children (p. 62), since Mariya bore Ibrahim.

The word "honestly", on the owner's banned list, was removed from jesus-not-god, quran-on-christians and relationship-with-god, and from the title of quran-science-claims-graded ("graded on the evidence"), which also changes the links in quran-anachronism-claims and was-muhammad-illiterate-ummi.

#### Material from *Essential Writings* (unreleased)

Checked against the PDF of Ismail Raji al Faruqi, *Essential Writings*, ed. Imtiyaz Yusuf (CICW and Islamic Book Trust, 2021). Printed page numbers are those in the book's own index, which lists jizyah at 48, 61, 69, 133, 166 and 193. The jizya article now quotes pp. 47 to 48 (charter of Aelia, including the clause barring Jews from Aelia), p. 61 (jizya "a far lesser economic and financial burden than the zakah"; stated as his judgment), p. 133 (refund to a non-Muslim who serves) and p. 166 (revenue falling "from numerous millions of dinars to a trifle"; flagged as in tension with the reports of converts still being charged). Other articles (printed pages of *Essential Writings*): jesus-not-god (p. 118, no documents from Jesus, with the dating of John corrected; p. 217, the prophet/triune contrast); where-is-the-injil (p. 217, the Bible as a record the human hand tampered with; p. 118, the canon, with Athanasius's letter of 367 added); quran-on-christians (p. 47, Najran; pp. 47 to 48, the charter of Aelia); islamic-art-and-the-one-god (pp. 145 to 147, calligraphy; p. 139, Western historians, flagged as a sweeping judgment); shared-prophets (pp. 97 to 98, 100 to 101, Adam); same-god (pp. 152 to 153, a prophet to every people); islam-religion-of-peace (p. 124, da'wah is not coercive); war-and-killing-in-islam (pp. 192 to 193, the uses of force, jihad as defensive war, flagged as narrower than the classical jurists); medina-jewish-tribes-and-qurayza (pp. 47 and 133, the Jews as an autonomous ummah; he does not discuss the three tribes). Left out on purpose: chapter 8's account of the ancient Hebrews and their god, and chapter 23's remarks on Zionism, orientalism and missions, which are polemic and not evidence. No passage on apostasy exists in the book, so apostasy-in-islam is unchanged.

Not yet used: p. 69 (Hindus and Buddhists), already covered through the *Cultural Atlas*. Page 193 is used in war-and-killing-in-islam (pp. 192 to 193).

#### Material from *Christian Ethics* (unreleased)

Checked against the page images of Ismail R. al Faruqi, *Christian Ethics: A Historical and Systematic Analysis of Its Dominant Ideas* (McGill University Press and Djambatan, 1967); the PDF's text layer is poor OCR, so every quotation was read from the image. Printed page equals PDF page less 13. Used: pp. 22 to 23 (fact and value; morality-without-god, with the note that his argument establishes a standard, not God); pp. 252 to 253 (the Islamic ethic adds action in the world; quranic-ethics, with the hadith on intentions as a counterweight); pp. 193 to 194 (peccatism, from Newbigin; shared-prophets); p. 81 (no law in Jesus's ethic; paul-and-the-law, flagged as contrary to Matthew 5:17 to 19 and the Quran). Not used: his Part One on Hebrew racialism and his chapters on Paul and the Reformation, which are polemic and mostly OCR-damaged. No passage fitted fate-and-free-will, second-peter or jesus-not-god beyond what *Essential Writings* already supplied, so those are unchanged.

#### New article: does-islam-teach-the-bible-was-corrupted (1.78.0)

Built from Bassam Zawadi's compilation of evidence for taḥrīf, with every reference rechecked where a source could be read, and graded by strength in the article. Checked: al-Ṭabarī on 4:46, Ibn Taymiyya (al-Jawab al-Sahih 2/407), Ibn Ḥazm (al-Fisal 2/2-3), Bukhari 7363 and 6841 with Fath al-Bari (all via the Shamela references already used in islamic-dilemma); Ibn Kathir on 12:100 and 6:115, al-Qurtubi on 5:13 and 15:9 (quran.ksu.edu.sa, text only); Majma al-Zawaid 4/312 and Ahmad 19403 / Ibn Majah 1853 (surahquran.com); Ibn al-Qayyim, Ighathat al-Lahfan ch. 13 (Wikisource, no page numbers).

Findings that shaped the article:

- The sentence "they lied about their prophets just as they distorted their book" is in Muʿādh's version only (Majmaʿ al-Zawāʾid 4/312, graded by al-Haythamī). The Ibn Abī Awfā version (Ahmad 19403, Ibn Majah 1853) has the same scene without it. Zawadi names the grader "Ibn Hajar al-Haytami"; Majmaʿ al-Zawāʾid is by Nūr al-Dīn al-Haythamī.
- Al-Qurṭubī on 5:13 gives the meaning reading as his own gloss and the letter-changing reading as "it is said"; Zawadi's use of the passage as a plain statement of textual change overstates it.
- Ibn al-Qayyim's three views show that the interpretation-only view is a recognised Muslim position, and that he himself sits nearest the middle view. The article concedes this.
- Zawadi's use of 4:157 and of Hudhayfa's remark (Bukhari 4987) as evidence of textual change is dropped as weak. The stoning report is used for concealment and the mixed state, not for textual loss.
- Reports from Kaʿb, ʿAbdullāh ibn Salām and Ibn Masʿūd on what the Torah contained are not cited one by one; Bukhari 2125 stands for the group, with the Isaiah 42 parallel. Zawadi's "Banu Israel wrote a book" hadiths (al-Albani, Silsila 2694 and 3860) were not cited because the gradings could not be read from a primary page.
- Checked later: Bukhari 4987 (hadithunlocked.com; Fath al-Bari 9/17-18 comments on Hudhayfa's alarm but not on the Jews-and-Christians clause) and 2125 (Fath al-Bari 4/342-343, where Ibn Hajar says Ata may have taken the report from Ibn Amr and Ibn Salam); the names in Ibn al-Qayyim's second view (al-Bukhari, and al-Razi in his tafsir) on the Wikisource text; al-Hakim 7325, which carries the Muʿādh sentence and is graded to the standard of the two Sahihs by al-Hakim and al-Dhahabi. The entry's strength label moves from "moderate at most" to "moderate".
- Still not checked: the Ahmad Musnad reference Zawadi gives for the Muʿādh report (the Ahmad report found, 19403, is the Ibn Abi Awfa version without the sentence); the printed page of Ighathat al-Lahfan.
- The Tanwir al-Miqbas tafsir, which Zawadi cites as Ibn Abbas, is not used.

Images, batch 4 (1.82.0): the four remaining articles below the rule that `claude/modest-ride-rgvjvk` does not edit. No close-up of a worshipper's face and no prophet in any picture added; the candle photograph for the terrorism article is a plain row of tea lights, not a memorial of a particular attack.

Images, batch 3 (1.81.0): ten articles that each needed one more image, chosen as those the long `claude/modest-ride-rgvjvk` branch does not edit. A picture of Khaba Fort first chosen for the Medina article turned out to be in Rajasthan, India, not Arabia (the Pexels description gives no country); it was replaced with Marid Castle at Dumat al-Jandal, Saudi Arabia, from Commons. Lesson kept: a caption names a place only when the source names it.

Images, batch 2 (1.80.0): the next ten articles by the same rule. Captions follow what each entry says (for example, Khadija's caravans, Aminah's death on the road from Medina, Baybars's capture of Krak des Chevaliers in 1271); each figure is placed beside the entry it illustrates, and CodeAnt's batch 1 nitpicks about mismatched placement were the model for that.

Images, batch 1 (1.79.0): ten articles, chosen as those most short of images under the rule of about one in-text image per two entries (minimum two). Each new image was picked from a contact sheet. Old images were kept and given captions. Featured images that come from the theme's older manifest (for example jizya and same-god) keep their credit line only, because a manifest entry cannot carry a caption without a change to inc/images.php. None of the photographs added shows a prophet or a close-up of a worshipper's face.

Images (1.78.0): seven articles now have one featured and three in-text photographs, chosen from contact sheets of Pexels, Pixabay and Commons candidates. Captions describe the picture and tie it to the article; alt text describes what is shown. No picture shows a person in a devotional role or any figure of a prophet. Left for a later pass: a third in-text image for the other 111 articles, which each have a featured image and two in-text images.

Also in this release: `bin/check-content.py` now reads the seed list from `inc/data/seed.json`. It had been reading `inc/setup.php`, which no longer holds the list, and reported 669 false problems; it now passes. `bin/build-related.py` also read each article's topic from `inc/setup.php`; it now reads `inc/data/seed.json`, and `inc/related-articles.json` is rebuilt with it (13 entries change: the new article and the 12 whose neighbours shifted).

#### Material from Shafaat (1.66.0)

Checked against Ahmad Shafaat, *The Gospel According to Islam* (New York: Vantage Press, 1979); printed page numbers equal the PDF page less 18, and the year comes from the UiTM library record, since the scanned copyright page is unreadable. Only his notes are cited, never his narrative, which is his own composition. His claim that the oldest manuscripts known to Origen read "Jesus Barabbas" was not used as he states it: the article gives the witnesses as Tyndale House, Cambridge lists them, and the comment attributed to Origen is of uncertain authorship.

#### Material from the two atlases (1.68.0)

Checked against the scans. *Historical Atlas of the Religions of the World* (New York: Macmillan, 1974): printed page equals the PDF page less 26; only al Faruqi's own chapter on Islam is cited. *The Cultural Atlas of Islam* (New York: Macmillan, 1986): printed page equals the PDF page less 15. Every added passage was compared with the cited page.

Left out on purpose, because the sources disagree or the claim overreaches:

- Historical Atlas: that the Jews of Qurayza were "banished" (p. 244), against the main sources; that Quraysh delegates "returned converted" (p. 241); the date 659 for Nahrawan (p. 265), where 658 is usual; the claim that apart from the Kharijites no Muslims judged the faith of other Muslims except under the law of apostasy (p. 265); the figures and order of events in the Crusades section (pp. 270–272).
- Historical Atlas: that a slave woman "rose to the status of the free wife" on conceiving (p. 245); the rule of the umm walad, already in the slavery article, is narrower.
- Cultural Atlas: that Muslims invented the pendulum clock, the magnetic compass and the astrolabe (p. 328); the statistics for tenth-century Cordoba (p. 319); that the jizya from Egypt "dwindled to nothing" under Umar ibn Abd al-Aziz (p. 222); the account of Ibn Mas'ud as "the exegete of the Quran" (p. 244), a title usually given to Ibn Abbas; the count of 1,500 reports shared by al-Bukhari and Muslim (p. 261).

To check: the Sind ruling is given in the Cultural Atlas as a council of scholars called by the caliph; the article gives it as theirs and adds that the early accounts set it in letters between Muhammad ibn al-Qasim and al-Hajjaj. A reviewer with al-Baladhuri to hand should confirm the wording.

#### No Wikipedia as a reference (1.68.0 branch, 15 October 2026)

Standing rule from the owner: the sources a Wikipedia article cites may be used, but a Wikipedia entry is never itself a reference. Sixteen Wikipedia citations in ten articles were replaced with the works those entries cite (apostasy, crucifixion, Indonesia, jizya, Kaaba witnesses, Library of Alexandria, manuscript transmission, Moon craters, Quran preservation, Satanic Verses). The "since 1646" date for the Macoraba identification had no source behind it and was removed.

#### Follow-up source check (15 October 2026)

Points left open in the first pass and now VERIFIED: the Meccan army of nine hundred to a thousand and the withdrawal of Banu Zuhra (Ibn Ishaq in Ibn Kathir on 8:42); the Tabuk covenant with Ayla (Ibn Hisham 2:525–526); Rayhana's disputed status (Ibn Sad 8:92–93 against Abu Ubayda in Zad al-Maad 1:114) and Mariya as concubine; Pew 2017, the Muwatta report of Abu Bakr (no. 971), Tahir ul-Qadri's fatwa (2 March 2010) and the 9/11 total of 2,977; Ibn Saad's death (Ibn Khallikan 3:65); Salama ibn al-Fadl and Yunus ibn Bukayr (Watt and McDonald; Raven, EI2 "Sira"); the Battle of the Masts (al-Tabari, trans. vol. 15, p. 77; the text now says Ibn Abi Sarh took part as governor, since Abu al-Awar commanded the fleet); the two readings of "aslama" (al-Khattabi, Qadi Iyad); Ibn Kathir on 2:256; the Meccan setting of 17:110 (al-Tabari, Makhul); Jabir's talbiya (Muslim 1218a); the Adnan lineage (Bukhari, chapter before 3851; Zad al-Maad 1:70); Qusayy's century (Lings p. 6); Ibn Khaldun (Dawood ed., p. 117; Gomez pp. 50–54); al-Jahiz (Sharawi; the text no longer calls his treatise a defence of the Zanj alone); Salman and the trench (al-Bidaya 4:97); Nabtal (al-Wahidi, Asbab al-Nuzul, p. 168, on 9:61); the brocade cloth (Ibn Hisham via Abu Zahra 1:271); Abu Dawud 3002 after Kab (Ibn Hisham 2:54–58).

Also VERIFIED in this pass: the 70 AH Kirman coin (Eshragh, ONS Newsletter 178); Doctrina Jacobi V.16 (Dagron and Déroche, pp. 208–213); Sebeos ch. 42; Wasit and Kufa (Crone, Meccan Trade, p. 198; the article now says al-Baladhuri's passage concerns Kufa, and that Wasit points to neither Mecca nor Jerusalem); Umm Habiba (Abu Dawud 2107, sound).

Also VERIFIED: al-Shatibi, al-Muwafaqat 3:344 (via the Dorar encyclopedia of usul); the count of 124 in Hibat Allah ibn Salama, al-Nasikh wa al-Mansukh, p. 46 (via al-Hakam). The Muta envoy could not be traced to a classical source online, so the clause was removed from quran-on-christians.

All remaining points are now accounted for:
- Al-Jassas (Ahkam al-Quran 1:49) and Abduh (on al-Falaq): VERIFIED through al-Shamrani's study on Saaid.net.
- Al-Tabari, History, vol. 1 (Rosenthal), pp. 182–183: VERIFIED from the SUNY translation, and the finding CHANGED the article. Al-Tabari did read the half-day report as five hundred years remaining in a world of seven thousand, so end-of-the-world-prediction now concedes that a classical precedent exists and answers it.
- Al-Tabari on the sons of Noah, vol. 2 (Brinner), pp. 11–12: VERIFIED; Wahb makes Ham "the father of the Blacks", and Ibn Ishaq gives the curse as what "the people of the Torah claim".
- The Asma chain: VERIFIED as Ibn al-Jawzi, al-Ilal 1:180 (quoting Ibn Adi), al-Dhahabi, Mizan 6:101, and al-Albani, Silsila daifa 13:33 (fabricated); the earlier "al-Kamil 7:326" reference, from Wikipedia, was replaced.
- Watt: Excursus L of Muhammad at Medina exists; his reading of it could not be quoted, so the sentence now says only that historians including Watt have discussed the marriages in that light.
- Ibn Abi Dawud: VERIFIED as edited by Arthur Jeffery in Materials for the History of the Text of the Qur'an (Brill, 1937).
- Arabica 57, no. 4 (2010), pp. 343–436: VERIFIED (Brill, DOI 10.1163/157005810X504518).

#### Second tier: closing pass (23 October 2026)
hanif-poets-and-musaylima (Zayd's death before the mission and the saving of girls, Ibn Hajar vol. 7 p. 143; Musaylima's sayings and Abu Bakr's reaction, Tabari vol. 3 pp. 284 and 300), was-muhammad-illiterate-ummi (the Encyclopaedia of the Quran citation corrected to Günther, "Ummī", EQ 5, pp. 399–402), ridda-wars-abu-bakr-fadak (Tabari vol. 3 locations), jihad-meaning-quran-hadith-jurists (Tirmidhi 2616, 2174, 1621, Abu Dawud 4344, Ibn Majah 4011 verified; Ibn Hajar on the greater-jihad saying via al-Ajluni), schacht-isnads-dating-hadith (Muslim 3004, Abu Dawud 3646, Umar ibn Abd al-Aziz's letter) and talmud-midrash-apocrypha-borrowing (all dates and passage references reread against standard references; no change needed) were checked. Still open: the wording of Ibn Taymiyya's remark on the greater-jihad saying (Majmu al-Fatawa); the Zad al-Maad stages of jihad.

#### Expansion to 1,600 words (1.77.2 onwards)

Written from memory or from secondary summaries, and to be checked against the sources:

- **quran-grammar-errors-claim**: al-Qurtubi on 4:162 (Aban ibn Uthman report, al-Qushayri's verdict) and on 5:69 (Sibawayh and al-Khalil, the two verses of poetry), checked only through the quran.ksu.edu.sa text; Ibn Mujahid on the readings of 20:63 (Abu Amr *inna hadhayni*; Hafs and Ibn Kathir *in*).
- **quran-contradictions-claims**: the chapter of al-Bukhari's Commentary (Fussilat) holding the Ibn Abbas report, and its exact list of questions; al-Shinqiti's title.
- **deuteronomy-prophecy-and-the-paraclete**: Sahih al-Bukhari 2125 and 4838 (Abdullah ibn Amr on the Torah description); Mingana's edition of Timothy's Apology (Woodbrooke Studies 2, 1928) and its treatment of Deuteronomy 18 and the Paraclete; the sections of Ibn Taymiyya's al-Jawab al-Sahih.
- **blasphemy-and-insults-to-the-prophet**: Sahih al-Bukhari 4905, 3610 and 4037; the Federal Shariat Court citation (PLD 1991 FSC 10, judgment of 30 October 1990); the Asia Bibi appeal number; the years of sections 298-A (1980) and 295-B (1982).
- **fly-hadith-and-adult-suckling**: Shah Wali Allah's chapter title; Ibn Khaldun on medicine; Abu Dawud 3844, al-Nasa'i 4262, Ibn Majah 3504; Abu Rayya (1958) and al-Siba'i's reply; al-Nawawi quoting Qadi Iyad; the Muwatta chapter on A'isha's practice; al-Layth and Ibn Hazm, and Ibn Taymiyya via Zad al-Ma'ad.
- **do-muslims-worship-muhammad**: Ibn Majah 1853; the Musnad Ahmad report "what God alone wills" (no number given); Bukhari 1040, 1044, 4454, 2411, 7510; Abu Dawud 2042; al-Tirmidhi 3148; the Muwatta chapter for "do not make my grave an idol".
- **muslims-by-the-numbers**: all new figures are from the two Pew pages already cited, read on 29 October 2026.

#### The Yahweh Question (1.78.0)

The five series articles carried their own fact-check list (section 7 of `docs/yahweh-series/editorial-and-seo-report.md`). That report and the Markdown drafts were removed in 1.86.1, because the articles now live in `content/`; they remain in the git history.

#### Expansion, second batch (1.78.1)

- **aisha-sheep-report-stoning-and-suckling-verses**: al-Suyuti, al-Itqan, chapter 47 (three categories of abrogation); al-Nawawi on Muslim 1452; Ibn al-Salah on *shadhdh* and *ziyada*; Ibn Ishaq's known *tadlis* (here attributed to the Tahdhib entry); Burton 1977.
- **ezra-and-mary**: Tirmidhi 3095 (Adi ibn Hatim); Ibn Hazm attributing the saying to a sect in Yemen; 3 Enoch 12:5 ("lesser YHWH"); Hagigah 15a; Newby 1988 on Ezra traditions in Arabia.
- **fastest-growing-religion**: Pew 2015 projections for 2050 (2.76 billion Muslims, 29.7 percent; 2.92 billion Christians, 31.4 percent); other figures from the 2025 Pew pages already cited.
- **second-peter**: Bauckham 1983 (testament genre); Ehrman 2013; Tertullian, On Baptism 17; Serapion in Eusebius 6.12; the Muratorian fragment's omission of the Petrine letters and the Peshitta's omissions.
- **why-five-prayers**: Bukhari 2931 and Muslim 627 (middle prayer as asr); Bukhari 631; Daniel W. Brown 1996 on the Ahl al-Quran and Parwez; the claim that Quranist groups differ on the number of prayers; Didache 8.3; Berakhot 26b.
- **temporary-marriage-mutah**: Ibn Qudama, al-Mughni, for the Sunni argument from 23:5-7; al-Tabarsi on 4:24; Ibn al-Qayyim, Zad al-Ma'ad, on the Khaybar report; the "two mutahs" report attributed to Umar (al-Bayhaqi reference to confirm); Haeri 2014 on misyar.

#### Expansion, third batch (1.78.2)

- **ramadan-last-ten-nights**: Bukhari 1901, 6, 1503, 2029, 2035.
- **arabic-words-science**: Burnett 2001 (Gerard of Cremona's seventy-odd translations); Robert of Chester's 1145 translation and its title; Fibonacci in Bijaya; King 1993; al-Biruni's Tahdid and the Ghazna qibla; Chaucer's Treatise on the Astrolabe; the Greek *ambix* behind *al-anbiq*; the star-name etymologies; the claim that much of al-Khwarizmi's algebra treats inheritance.
- **women-in-islamic-law-testimony-inheritance**: Ibn al-Qayyim, al-Turuq al-Hukmiyya, on testimony; al-Dhahabi on women narrators; Bukhari 2742, 5136, 5138; Tirmidhi 1128 (Ghaylan); the Hanbali stipulation against a second wife (al-Mughni).
- **houris-and-paradise**: the Shama'il report of the elderly woman; Bukhari 554, 2898; al-Ghazali's Book of Love; de Blois's review (Journal of Qur'anic Studies 5, 2003).
- **library-of-alexandria**: Lewis on the Fatimid library sale as context; Ibn Khaldun's story of the Persian books; the silence of Ibn Abd al-Hakam, al-Baladhuri and Eutychius; Seneca, Tranquillity 9.5, and Ammianus 22.16.13; Rufinus and Eunapius on the Serapeum; Gibbon ch. 51; Butler 1902.
- **kaaba-witnesses**: the "Kaaba of Najran"; Zuhayr's oath in his Mu'allaqa and Arberry's translation; Robin 2012 (title and volume) on the Murayghan inscription; Hoyland 2001.
- **prophets-you-may-not-know**: the Abu Dharr report on 124,000 prophets and its grading; van Bladel 2009 on Idris and Hermes; the Ubar expedition; the al-Kifl shrine and the EI2 article; the Dhul-Kifl and Buddha proposal; the Elijah and Khidr folklore.
- **child-marriage-in-islam**: the ages of maturity by school (al-Mughni); al-Sarakhsi on Ibn Shubruma and Abu Bakr al-Asamm; al-Nawawi on Muslim 1422; Indonesia Law 16/2019; Sindh 2013; the UNICEF 2023 report.

#### Expansion, fourth batch (1.78.4)

- **ex-muslim-blog-study**: the Lina Joy judgment date (30 May 2007); Watt's translation of al-Munqidh; Muslim 132; Aristotle, Rhetoric 1.2; the Pew switching figures.
- **who-saw-gabriel**: Bukhari 2, 4592, 4980; the chain of Muslim's hadith of Gabriel through Yahya ibn Ya'mar and Ibn Umar; Quran 20:9-12 and 19:16-19 as private calls.
- **jihad-meaning-quran-hadith-jurists**: Ibn Kathir on 9:73 quoting Ibn Abbas; Ibn al-Qayyim's four kinds of jihad in Zad al-Ma'ad; Shaltut 1948 and Peters 1996.
- **qibla-change**: Muslim 700 on 2:115; the Banu Salima mosque reports (Ibn Sa'd, Fath al-Bari); King's "The Petra Fallacy" (2018).
- **morality-without-god**: Moore, Principia Ethica, ch. 1, sect. 13; Darwin, Descent of Man, ch. 3 and 5; Euthyphro 10a; Hourani 1985 on *husn* and *qubh*.
- **taqiyya-myths**: the al-Kafi chapter and Kohlberg 1975; Cyprian, On the Lapsed; Muslim 1787 (Hudhayfa's promise); Quran 33:70, 9:119, 4:135.

#### Expansion, fifth batch (1.78.6)

- **satanic-verses-report**: Muir as the coiner of "Satanic Verses"; Ibn Hisham's omission and Guillaume pp. 165-167; al-Razi quoting Ibn Khuzayma on 22:52; Ahmed 2017.
- **mosques-adapt-to-place**: Bukhari 3583-3584 (the crying trunk); the Niujie and Huaisheng traditions and Steinhardt 2015; al-Sahili and Aradeon 1989; Mehmed Agha as Sinan's pupil.
- **end-of-time**: Bukhari 3688, 2222, 3346; Momen 1985; the seedling hadith (Ahmad; al-Adab al-Mufrad 479).
- **end-of-the-world-prediction**: Muslim 2901 cited for the ten signs (narrator Hudhayfa ibn Asid); Moin 2012 on the Tarikh-i Alfi; Numbers and Butler 1987 on the Millerites.

#### Expansion, sixth batch (1.78.7)

- **homosexuality-in-islam**: the schools on *sihaq* (al-Mughni); Bukhari 6824 and Muslim 1695 on Ma'iz; Navtej Johar (6 September 2018); Singapore's 2022 repeal of 377A.
- **who-is-allah**: Lane's Lexicon on the derivation of Allah; the Zabad inscription (512) and Littmann's article; Morey 1994; Bukhari 5999; al-Ghazali's al-Maqsad al-Asna in Burrell and Daher's translation.
- **western-scholars-of-islam**: Said 1978; Azami 1968 and 1985; the Bergsträsser-Pretzl archive and Corpus Coranicum; Schimmel 1975; Watt 1961.
- **moon-craters**: Grimaldi as Riccioli's collaborator; the 1009-1010 Bodleian copy of al-Sufi; the 2015 International Year of Light; Sachau's translation of al-Biruni's India; the crater names Thebit, Geber, Alpetragius, Abulfeda and Nasireddin; Saliba 2007.

#### Expansion, seventh batch (1.78.8)

- **abrogation-and-the-sword-verse**: Bukhari 4655 and 369 on the proclamation of 9 AH; the biblical examples (Genesis 29 with Leviticus 18:18, Hebrews 8:13, Mark 7:19); Fatoohi 2013.
- **islam-in-china**: the Old Book of Tang on the 651 embassy; Chen Dasheng 1984 on the Quanzhou inscriptions; Jaschok and Shui 2000; the 1417 Zheng He incense stele; the Dungan migration after the 1862-1877 revolt; Ma Jian's 1981 translation.
- **haman-anachronism**: Bucaille 1994 and Ranke 1935 (vol. 1 entry); the remark on fired brick in Egypt.
- **gods-attributes-and-anthropomorphism**: al-Aqida al-Wasitiyya's four refusals; al-Razi's Asas al-Taqdis and Ibn Taymiyya's Bayan Talbis al-Jahmiyya; Maimonides, Guide, part 1 (Pines translation).

#### Expansion, eighth batch (1.78.9)

- **islamophobia-facts**: Runnymede 2017; Allen 2010; the Najran deputation praying in the mosque; dates of the Quebec City, Finsbury Park and London, Ontario attacks; Canada's 2023 special representative; the UK's 2025 working group on a definition of anti-Muslim hatred.
- **seven-libraries**: Ibn Abi Usaybi'a on Hunayn's payment in gold; al-Hakam's marginal notes and the women copyists of Cordoba (later reports); the Nizamiyya network; Fatima al-Fihri (Rawd al-Qirtas) and Aziza Chaouni's restoration; Abdel Kader Haidara's evacuation; the Süleymaniye's completion in 1557 and Necipoğlu 2005.
- **was-muhammad-illiterate-ummi**: the Hebrew *ummot ha-olam* parallel (attributed to unnamed scholars via note 2); Bukhari 65 and 5872 on the seal; al-Baji and the Hudaybiyya controversy.
- **who-wrote-the-quran**: Ibn Ishaq on Bahira (Guillaume pp. 79-81) and Roggema 2009; the Birmingham radiocarbon announcement (22 July 2015); al-Baqillani's I'jaz al-Quran (Saqr edition) on style.

#### Expansion, ninth batch (1.78.10)

- **race-in-quran-and-hadith**: Quran 35:27-28; Malcolm X's letter from Mecca (Autobiography, ch. 17, paraphrased); Umm Ayman (Ibn Sa'd; Muslim 1771); Ahmad Baba's Mi'raj al-Su'ud and the Hunwick-Harrak edition.
- **camel-urine-hadith**: Bukhari 216 (the two graves); Bukhari 5678 (every disease has a cure).
- **funeral-dhikr-dua**: Bukhari 7563 as the last hadith of the collection; Muslim 2732; Bukhari 6369; Bukhari 1343 on the martyrs of Uhud; Muslim 963 (Awf ibn Malik's prayer); Muslim 977 and 974-975.
- **al-zutt-allegation**: Quran 29:65 and 43:12-13; the EI2 article "Zutt" on the Jats of Sind; the details of Muslim 450 (the search, Hira, the traces of fires).

#### Expansion, tenth batch (1.78.11)

- **preservation-and-truth**: Quran 30:2-5, 21:33, 36:38-40; the Great Isaiah Scroll's date and agreement with the Masoretic text (cited to Tov).
- **ridda-wars-abu-bakr-fadak**: Abu Bakr's first address (Guillaume p. 687); the Khalid and Malik ibn Nuwayra episode (al-Tabari); Ibn Kathir on 5:54; Ibn Sa'd on Abu Bakr's allowance; al-Baladhuri on Umar II and al-Ma'mun and Fadak; Bukhari 4986.
- **crusades-facts**: Chazan 1987; Ibn al-Qalanisi on the synagogue (Gibb 1932); Goitein 1952 on the Geniza letters; Ibn Shaddad on Acre 1191 and the 1192 truce (Richards 2001).
- **instruments-islamic-world**: al-Ijliyya in the Fihrist; al-Razi's Kitab al-Asrar and Ruska 1937; al-Farisi and Rashed's DSB article; al-Jazari (Hill 1974) and the crankshaft claim; Taqi al-Din's 1577 observatory and Sayılı 1960; Jai Singh's use of Ulugh Beg.

#### Expansion, eleventh batch (1.78.12)

- **hijab-and-modesty**: 1 Corinthians 11:5-6; Quran 24:60; the UN fact-finding mission report (March 2024) on Mahsa Amini; France's Law 2004-228; Quebec's Bill 21.
- **dome-inscriptions**: Süleyman's exterior tiles (Grabar 2006); Abd al-Malik's coin reform and Grierson 1960; Donner 1998.
- **hanif-poets-and-musaylima**: Ibn Ishaq's four hanifs; Bukhari on Zayd at the Kaaba (attributed here to note 4) and Waraqa's wish (Bukhari 3); the EI2 article on Umayya ibn Abi al-Salt; the Musaylima letters (Guillaume pp. 649-650) and Quran 7:128; the Rabi'a and Mudar saying in al-Tabari.
- **why-no-interest**: Muslim 1587 (six commodities); Bukhari 2305 (repaying with a better camel); Third Lateran canon 25 and Aquinas II-II q. 78; the Federal Shariat Court judgment of 28 April 2022.

#### Expansion, twelfth batch (1.78.13)

- **buraq-and-miracle-stories**: Bukhari 3886 and Muslim 170 (Jabir on describing Jerusalem); Abu Bakr's reply (Guillaume p. 183, page to confirm); Ibn Hajar's count of opinions on the date of the Isra; the ring where the prophets tied their mounts (in Muslim 162a); 2 Corinthians 12:2-4; Quran 17:90-93; Hume, Enquiry, section 10.
- **quran-preservation-reddit**: Sadeghi and Bergmann, Arabica 57 (2010), pages 343-436; van Putten's "grace of God" spelling argument as summarised here; the Tübingen fragment Ma VI 165 (press release of November 2014, 649-675); Bukhari 5003 (Anas on the four collectors); Sinai 2017, chapter 3 and the "closed by the 650s" summary.
- **first-muslims-kept-the-faith**: the sun-and-moon saying (Guillaume p. 119); Bukhari 6459 and 2916; Hamza and Umar (Guillaume page ranges to confirm) and Bukhari 3684; Umm Habiba, Ubayd Allah ibn Jahsh and Ruqayya (Guillaume page numbers to confirm).
- **universe-demands-creator**: Hoyle's 1949 "big bang" broadcast and Kragh 1996; Penzias and Wilson 1965; Quran 52:35-36; Hoyle, Engineering and Science (November 1981); Einstein, "Physics and Reality" (1936), wording of the comprehensibility remark; Ellis and Silk, Nature 516 (2014).

#### Expansion, thirteenth batch (1.78.14)

- **sira-ibn-ishaq-hisham-tabari**: Guillaume's restored passages (cited to note 1, his introduction); al-Tabari's warning to readers in his introduction; the gharaniq reports in al-Tabari, Qadi Iyad and Ibn Kathir on 22:52; Schoeler 2011 and Görke and Schoeler 2008 on Urwa, and the "about sixty years" summary; Bukhari 4990 and 2699; the Constitution of Medina (Guillaume pp. 231-233).
- **indonesia**: Marco Polo on Perlak (1292), the Malik al-Salih gravestone (1297) and the Malik Ibrahim tombstone (1419), all cited to Ricklefs; hajj quota "more than two hundred thousand"; Pesantren Law 18/2019; Islamic parties' minority share of the vote (Fealy and White 2008, KPU); Aisyiyah founded 1917; KUPI at Cirebon (2017) and Jepara (2022).
- **natural-world-verses**: Bukhari 1043 (eclipse); Quran 27:88 in context; Quran 24:35; Tirmidhi 2429 and its gradings; al-Tabari on 55:22; Bucaille 1976; al-Shatibi's argument in al-Muwafaqat.
- **quran-mysteries-in-the-stories**: the rebuke of Moses in Bukhari 122; Ezekiel 38-39 and Revelation 20:8; Abul Kalam Azad on Cyrus; Jacob of Serugh's homily and Gregory of Tours (chapter number to confirm); the solar and lunar years remark; Solomon in later lore; al-Tabari on 85:4 and Najran.

#### Expansion, fourteenth batch (1.78.15)

- **apes-and-pigs-and-the-jews**: Quran 7:175-176, 25:44, 62:5; Matthew 23:33 and Philippians 3:2; Ibn Kathir on 7:163-166 and his grading of the Ibn Batta report; Mukhayriq (Guillaume pp. 241 and 384, pages to confirm) and Bukhari 3329 and 3812 on Abd Allah ibn Salam; Cohen 1994 and Goitein on Jews under Muslim rule; Granada 1066 and the Almohads.
- **before-your-first-friday-prayer**: Abu Dawud 499 and Bukhari 604 (origin of the adhan); tayammum under Quran 5:6; Muslim 869 (short sermon); al-Hakim on al-Kahf and Abu Dawud 1047; Bukhari 935 (the hour on Friday); Muslim 875 (Sulayk).
- **prophet-died-poisoning**: Bukhari 3667-3668 (Abu Bakr's address); Bukhari 3169 (the Jews of Khaybar questioned); Bukhari 4428 as a suspended report; Musnad Ahmad 3617 (Ibn Masud on martyrdom; numbering to confirm); Hensley's death in 1955 and Hood and Williamson 2008.
- **magic-jinn-and-satan-hadiths**: the link between the spell and Surahs 113-114 (Ibn Hajar, Ibn Kathir); Bukhari 5017 and 6223; Quran 7:200; Ibn Taymiyya, Majmu al-Fatawa vol. 19 p. 10; the Mutazila on jinn; Muslim 2200 and 2230.

#### Expansion, fifteenth batch (1.78.16)

- **scholars-who-shaped-islamic-study**: Bukhari 4970 (Ibn Abbas and Surah 110); Ibn Ishaq's Jewish and Christian informants and move to Baghdad (cited to Guillaume, note 2); Ibn Abi Dawud's Kitab al-Masahif (Jeffery 1937); Ibn Abi Usaybia's story of Hunayn and al-Mutawakkil; al-Kindi's cryptanalysis treatise and Al-Kadi 1992; the Latin Maqasid and "Algazel".
- **uthman-copies**: the Mingana collection dates; Fedeli on the link between the Birmingham leaves and BnF Arabe 328(c); Altıkulaç's IRCICA editions (Topkapı 2007, Cairo later) and his dating; ijaza chains through Ibn al-Jazari; Ibn Abi Dawud on Ali and Musab ibn Sad, and Fath al-Bari vol. 9 p. 18.
- **does-the-quran-allow-wife-beating**: the occasion reports in al-Tabari and al-Wahidi; Ata ibn Abi Rabah as quoted by Ibn al-Arabi; al-Shafii in al-Umm; Quran 30:21; Malaysia's Act 521 (1994), Indonesia's Law 23/2004 and the Moroccan Mudawwana (2004); Brown 2014, chapter number to confirm.
- **companions-who-started-with-nothing**: Bukhari 3754, 1149, 447 and 119; Ibn Sad on Salman at al-Madain; Zayd ibn Haritha (Quran 33:37) and Bukhari 4250 on Usama's command.

#### Expansion, sixteenth batch (1.78.17)

- **quran-science-claims-graded**: Bukhari 3208 and Muslim 2645 on the timings; Ibn Hazm on the sphericity of the earth (al-Fisal, citing 39:5); al-Mamun's measurement of the earth; al-Tabari on 51:47.
- **revisionist-case-weighed**: Crone's 2008 openDemocracy piece (wording paraphrased; check the exact sentences); Cook's later position; Donner 1998; van Putten 2019; Thomas the Presbyter (Hoyland pp. 118-120, pages to confirm); King 2018 and Gibson 2017 on Petra.
- **war-and-killing-in-islam**: Bukhari 3166 and 5778; Quran 4:29 and 8:61; Bukhari 2731-2732 and Quran 48:1; the growth of the community after Hudaybiyya (al-Zuhri's remark as reported by Ibn Ishaq); Muslim 96 (Usama).
- **where-is-the-injil**: Eusebius, Ecclesiastical History 3.39.16 (Papias); Kloppenborg 2000 on Q; Quran 5:68 and 7:157; Bukhari 4485 and Quran 2:136.

#### Expansion, seventeenth batch (1.78.18)

- **manuscript-transmission**: the Ramadan night-prayer recitation paragraph (no note); Metzger's and Ehrman's positions (Ehrman 2005); P52 and Nongbri 2005; the Comma Johanneum and Erasmus's third edition (1522).
- **kharijites-and-takfir**: al-Shahrastani on the Azariqa and Nafi ibn al-Azraq (680s); Bukhari 3610 (Dhu al-Khuwaysira); Takfir wal-Hijra and the killing of al-Dhahabi in 1977 (Kepel 1985, chapter to confirm).
- **hajj-rites-explained**: Bukhari 1755 (farewell circuit); Malcolm X's letter (Autobiography, chapter 17); GASTAT figures "between one and a half and two million"; Quran 2:203.
- **quranic-stories-and-history**: the Ruwwafa inscription (166-169 CE; Bowersock 1983 pp. 96-97, pages to confirm); Procopius, Wars 1.20 on Abramus; Matthew 12:41 and Luke 11:32.

#### Expansion, eighteenth batch (1.78.19)

- **jesus-not-god**: Hebrews 5:7-8; Mark 10:17-18 and Luke 18:18-19; Mark 12:28-29 with Deuteronomy 6:4; Acts 2:22.
- **slavery-in-islam**: Abu Dawud 5156 and Ibn Majah 1625 (last words); Bukhari 2552 ("my young man"); Musnad Ahmad 2216 on the Badr ransom (numbering to confirm); Tunisia 1846 and the Zaytuna scholars (Montana 2013); the Ottoman ban of 1857 (Toledano 1982).
- **paul-and-the-law**: Galatians 2:11-14; Acts 21:18-24; James 2:10 and 2:24; Luther's "epistle of straw" (1522 preface).
- **crucifixion-quran**: Quran 3:54-55, 43:61 and 4:159 with al-Tabari; the Gospel of Barnabas manuscripts (Ragg 1907; Leirvik 2002, pages to confirm).

#### Expansion, nineteenth batch (1.78.20)

- **shared-prophets**: Genesis 9:29 and Quran 29:14; Exodus 2:5-10 and Quran 28:7-9 and 66:11 (Asiya); Quran 4:164, 40:78 and 35:24.
- **black-stone-and-kissing**: Bukhari 1608 (Muawiya and Ibn Abbas) and 1609 (Ibn Umar); the paragraph on the Qarmatian years (cited to Ibn al-Athir, note 3), including disruption of the pilgrimage in some years.
- **five-pillars-explained**: Bukhari 645; Tirmidhi 413; state zakah collection in Saudi Arabia, Malaysia and Pakistan (no note); Quran 97 and Bukhari 2026; Bukhari 1503 (zakat al-fitr).
- **islam-religion-of-peace**: Quran 25:63 and 41:34; Pew 2013, The World's Muslims, views of violence (figures to confirm by country).

#### Expansion, twentieth batch (1.78.21)

- **raids-badr-and-the-conquest-of-mecca**: Abu Aziz ibn Umayr on the captives' bread (Guillaume p. 309); Bukhari 4280 (Sad ibn Ubada and the standard); the Ayla, Jarba and Adhruh treaties (Guillaume pp. 607-608).
- **prophets-marriages-zaynab-and-others**: Bukhari 5212 (Sawda's night); Ibn Sad on Sawda, Zaynab bint Khuzayma and Umm Salama; Nasai 3254 on Umm Salama's reply; Quran 33:52 read with 33:50.

#### New articles: six graded articles from two transcripts (1.85.0)

Derived from two spoken transcripts (a debate and a short video reply). The transcripts are auto-captioned speech, so nothing is quoted from them and no speaker, channel or event is named. Every claim they made was rechecked against a written source or dropped.

Checked: Quran 3:3, 3:4, 3:50, 3:85, 5:43 to 5:48, 7:157, 9:111, 11:69, 15:9, 20:22, 27:12, 28:32, 51:24 and 61:6 (Saheeh International text, Quran.com API); Bible verses (World English Bible, bible-api.com): Matthew 1:23, 2:15, 2:23, 5:11 to 5:18, 10:39, 16:25, 23:2 to 3; Mark 7:19, 8:35; Luke 16:17; Acts 15:28 to 29; Galatians 3:10 to 13 and 3:24; Hebrews 8:13; Exodus 4:6 to 7 and 31:16 to 17; Genesis 18:1 to 3, 18:22 and 19:1; Deuteronomy 4:2, 21:23 and 27:26; Hosea 11:1; Isaiah 7:14 and 11:1; Jeremiah 25:11 to 12 and 29:10; Daniel 9:2 and 9:21 to 24. Irenaeus, Against Heresies 1.27.1 to 2 and 3.3.4 (Polycarp and Marcion), and the Pseudo-Clementine Homilies 2.38 to 39 and 3.47 to 50 (newadvent.org, Ante-Nicene Fathers). Babylonian Talmud, Bava Metzia 86b, on the three visitors as Michael, Gabriel and Raphael (Sefaria, Steinsaltz translation). Ibn Kathir on 5:47 to 48 (Shamela, vol. 3 pp. 127 to 128): the glosses of muhaymin as trustee, witness and judge, and "what agrees with it is true, what differs is false".

Findings that shaped the articles:

- The transcripts say the Quran confirms (musaddiq) only the Torah and not the Gospel. The verses show 5:46 describing the Gospel as confirming the Torah and 5:48 describing the Quran as confirming "what preceded it of the Scripture", so the claim is not used.
- The transcript reply says the reading of 5:47 and the argument from 3:85 settle the matter. The article gives Ibn Kathir's two readings and says that 5:43 is the strongest verse for the other side.
- The remark that the classical scholars never differed is overstated; the article says the outline holds and the detail does not.
- The Pseudo-Clementine date and Jewish-Christian setting, the "shoot" suggestion for Matthew 2:23, and the Hebrew and Greek words in Isaiah 7:14 are stated from general knowledge and flagged in the notes.

Not checked: the 1QpHab wording against a printed edition (paraphrase follows two online translations); Griffith, The Bible in Arabic, pp. 41 to 42, and Kashouh on the earliest Arabic Gospel manuscript (both read second-hand); a named scholar's wording on "confirming" and muhaymin was not used because no printed page was read.

Images: eight in-text images and two featured images, all from Wikimedia Commons; no picture of a prophet. A first pick of a painting of the Sermon on the Mount and an etching of Abraham with the three visitors was dropped for that reason.

Added with the other four (early-christians-charged-others-with-corrupting-scripture, quran-word-kitab-meanings, trinity-in-the-hebrew-bible-tested, matthew-fulfilment-quotations-checked):

- Checked: Justin, Dialogue 71 to 73 (the Ezra, Jeremiah and Psalm 96 charges; newadvent.org); Origen, Against Celsus 2.27; Tertullian, Against Marcion 1.19 and 4.2; Irenaeus 1.26.2 (Ebionites); Quran 2:2, 2:85, 2:183, 2:282, 3:48, 3:93, 4:171, 5:110, 6:7, 6:91, 7:156, 13:39, 15:9, 19:30, 29:48, 43:4, 46:12, 56:77 to 78, 68:1, 85:21 to 22 and 112:1; Bukhari 3 on Waraqa (Hebrew letters; hadithunlocked.com); Sanhedrin 38b (Metatron; Sefaria); the Bible passages named in each entry (World English Bible).
- Not checked on a primary page: the Arabic version of the Waraqa report (Bukhari 6982 is named by Seekers Guidance and Islamweb only); the Psalm 96 "from the wood" textual history, the Mark 16 and John 7:53 manuscript facts, the Hebrew and Greek words in Isaiah 7:14 and Isaiah 9:6, and the Greek form of Isaiah 42 in Matthew, all stated from general knowledge and flagged in the notes; the Babylonian Talmud Bava Batra 14b order of the prophets.
- The debate was long and heated; the weak and mocking exchanges (insults, riddles, challenges for yes-or-no answers) were not used. The arguments kept are those that rest on a text that can be looked up.

#### New article (1.85.0)

- **where-did-jesus-say-he-is-god**: Bukhari 3535, 4416 and 631; Muslim 2404 and 523; Bible quotations adapted from the ESV (consider switching to the World English Bible used by the Yahweh series); the Septuagint wording of Genesis 23:7, 1 Chronicles 29:20 and Exodus 3:14 (*ego eimi ho on*); Matthew 18:26 in the KJV. The images are from Pexels because Commons refused requests; under D48 they stay as they are.

#### Images, batch 5 (1.86.0)

Ten articles that were below the rule (about one in-text image per two entries, minimum two). Candidates were searched on Wikimedia Commons first (SSOT D48); where Commons was slow or had nothing fitting, a Pexels photograph was used. New Commons pictures: Penn Museum figurines from Beth Shemesh and the Tel Dan Stele (who-was-yahweh-archaeological-origins), the Judean pillar figurines in the Israel Museum (was-yahweh-one-of-many-gods). Pictures dropped on checking: Chinese and Mexican figurines offered by the Pexels search (wrong region for a Judean article); a Louvre Baal stele (tall crop cut the figure) replaced by a storm-cloud photograph with a caption about the shared storm imagery. Captions name a place only where the source does; the Sinai photograph near Nuweiba is captioned as not showing Kuntillet Ajrud.

#### Images, batch 6 (1.87.2)

Ten articles that were one image short of the rule. Candidates came from the Pexels API because Wikimedia Commons was refusing requests (SSOT D48 puts Commons first; the swap to Commons can follow). Caption checks: the Dome of the Rock picture shows the exterior tile drum, so the caption says the inscriptions are inside the building; the Bible page picture shows a Genesis page, and the Paul caption says so; the Nizwa Fort and desert-hill photographs are captioned as not showing Khaybar or Badr. The Perge and Side captions rely on Acts 13:13 and 14:25 for Paul's route and on Side lying in the same region; Side itself is not named in Acts.

#### Lecture articles (29 October 2026, released in 1.88.0)

The thirteen articles of 1.88.0 and the additions to six others were written with the sources named in their notes, several of them from memory. The details below need a check before they are relied on.

- **nabi-and-rasul**: the word counts (236, 86, 54, 21, 5) were made on the Tanzil text with a pattern match and should be checked against Abd al-Baqi's concordance; Ibn Taymiyya's definition and its page in al-Nubuwwat; the grading of the 124,000 and 315 reports and the narrator named in note 10; the Koehler-Baumgartner entry for *navi*.
- **messenger-to-every-nation**: Sargon II's Tamudi (Luckenbill, §17); the Ruwafa inscription in Bowersock; Ptolemy's Modiana.
- **muhammad-and-the-hebrew-prophets**: Mishnah Sanhedrin 4:5 wording; Ibn Qutayba's quotations of the Torah and Psalms (Adang, chapter 6); Ibn Rabban's citations.
- **ishmael-in-the-quran-and-genesis**: al-Tabari's preference for Isaac on 37:107; Josephus, Antiquities 1.214 and 1.220–221 numbering; Sozomen 6.38; Wenham and Westermann on Genesis 16:12.
- **no-compulsion-in-religion**: the report of al-Husayn of the Ansar in al-Tabari; the wording of al-Hajjaj's ruling in al-Baladhuri; that Ibn Qudama cites 2:256 for the compelled dhimmi; the narration from Ahmad agreeing with Malik; Akbar 1564 and Aurangzeb 1679; Satish Chandra 1969 page range; the Najran covenant in Abu Yusuf.
- **holy-war-in-the-bible**: Clermont canon 2 wording in Riley-Smith, p. 37; Bernard's "malicide" (chapter 3); Maimonides, Kings 6:1; Mishnah Yadayim 4:4 and Berakhot 28a.
- **septuagint**: the Greek Esther's count of mentions of God; Methuselah surviving the flood in some manuscripts; 4QExod-b at Exodus 1:5; Augustine Letter 71 and Jerome Letter 112 numbering; Cowley 1974.
- **oldest-bible-codices**: the 23,000 corrections figure from the Codex Sinaiticus Project; Hoskier's 3,000 differences; Vaticanus page 1512 note; Fee 1965 on P66 corrections; Martini 1966; the contents of P72.
- **comma-johanneum**: the manuscript list in Metzger (61, 88, 221, 429, 629, 636, 918, 2318, 2473); Priscillian, Liber Apologeticus 1.4; Denzinger numbers 3681–3682; Conybeare 1901; the Sinaiticus correction at 1 Timothy 3:16.
- **old-testament-variant-readings**: the *ketiv-qere* count range; the eighteen *tiqqune soferim* and Genesis Rabbah 49:7; Tov's page numbers; the Deuteronomy 27:4 fragment; 5/6HevPs at Psalm 22:17; Kiddushin 30a.
- **kings-and-chronicles-numbers**: that Archer adopts the division-of-the-kingdom reckoning for 2 Chronicles 16:1; the Septuagint and Syriac readings for 2 Chronicles 22:2 and 36:9; McCarter on 2 Samuel 21:19.
- **genealogies-of-jesus**: Julian's remark as preserved in Cyril; Annius of Viterbo as the source of the Mary theory (Brown, p. 89); the Bezae text of Luke 3; Africanus quoted in Eusebius 1.7.15.
- **bible-alteration-claim-before-islam**: Sotah 33b and y. Sotah 7:3 wording; Origen, Against Celsus 2.27; Eusebius 4.23.12 and 5.28; Augustine, Reply to Faustus 32.2 and 33.3; the Pseudo-Clementine "false pericopes" (Homilies 2.38, 3.47–50); Crone 1980.
- **Additions to existing articles**: Bukhari 4252 for the Umra of fulfilment; the Hanafi definition and the stages in Radd al-Muhtar; Abu Zahra's book; Bauer-Danker on *monogenes*.

#### Expansion, twenty-first batch (1.101.1)

- **kaab-ibn-al-ashraf-and-the-poets**: Bukhari 3030-3031 (deception in war); Abu Dawud 3000 (the agreement after Ka'b's killing) and al-Albani's grading; Bukhari 4123-4124 and Muslim 2486 (Hassan ibn Thabit); Ka'b ibn Zuhayr and "Banat Su'ad", with the mantle as later tradition; Ibn al-Ziba'ra's pardon; the Muwatta report on the raid on Ibn Abi al-Huqayq (chapter and number to confirm); Watt, Muhammad at Medina (chapter to confirm); al-Azhar's statement of 7 January 2015.

#### Expansion, twenty-second batch (1.101.2)

- **women-in-the-prophets-life**: Bukhari 3432 and Quran 3:42; Umm Salama's question and Quran 33:35 (al-Tabari; the Musnad Ahmad number to confirm).
- **apostasy-in-islam**: Muslim 1784 (the Hudaybiyya term and the words "God has sent him away"); Abu Dawud 2683 and 4358 on Abd Allah ibn Sa'd.
- **quran-anachronism-claims**: Quran 2:249 and Judges 7:4-7; the characterisation of the two tests.

#### Expansion, twenty-third and last batch (1.101.3)

- **fighting-verses-in-context**: Quran 2:193, 8:39, 8:61, 60:7.
- **medina-jewish-tribes-and-qurayza**: Quran 3:113-115 and 60:8-9.
- **talmud-midrash-apocrypha-borrowing**: Quran 16:103 and 25:4-6.
- **six-articles-of-faith**: Quran 2:285, 4:136, 54:49; Muslim 8.
- **islamic-rites-before-islam**: Quran 7:31 (al-Tabari's report from Ibn Abbas on circling naked) and 9:37.
- **same-god**: Acts 17:22-23.
- **islamic-art-and-the-one-god**: enamelled Mamluk mosque lamps with Quran 24:35 (name a catalogued example to replace the general museum reference).

### Shamela and sunnah.com review

Purpose: every classical-source note in a listicle is checked against the Arabic text on shamela.ws, and the note records the edition, volume and page (marked "Shamela text"). Where Shamela cannot confirm a claim, the claim is softened or removed. al-Islam.org and Wikipedia entries are never used as sources.

Hadith are also checked on sunnah.com (hadith number, book, chapter, narrator, grade), which is the numbering cited in the notes.

Method: find the book id with a search restricted to shamela.ws, locate the page, read the Arabic with diacritics stripped, then edit the article note. Hadith of al-Bukhari are located by hadith number in Ibn Hajar, Fath al-Bari (Salafiyya edition, Shamela book 1673).

#### Reference book ids

| Work | Shamela id |
|---|---|
| Ibn Hajar, Fath al-Bari (Salafiyya ed.) | 1673 |
| al-Nawawi, Sharh Sahih Muslim | 1711 |
| Ibn Hazm, al-Muhalla | 767 |
| Ibn Hajar, Tahdhib al-Tahdhib (Risala ed.) | 1293 |
| al-Baqillani, al-Intisar li-l-Quran | 1397 |
| Ibn Qutayba, Ta'wil mukhtalif al-hadith | 7292 |
| Ibn Abidin, Tanbih al-wulat | 1622 |
| Ibn Rushd, Bidayat al-mujtahid | 21739 |
| al-Qurtubi, al-Jami li-ahkam al-Quran | 20855 |
| Ibn al-Jazari, al-Nashr | 22642 |

#### Status

| Article | Status |
|---|---|
| blasphemy-and-insults-to-the-prophet | Done (Ibn Abidin, Tanbih al-wulat) |
| quran-on-christians | Done (Nawawi on Muslim 2767) |
| women-in-islamic-law-testimony-inheritance | Done (Ibn Rushd, Bidayat al-mujtahid) |
| fly-hadith-and-adult-suckling | Done (Fath al-Bari on the fly hadith) |
| aisha-sheep-report-stoning-and-suckling-verses | Done (Tahdhib al-Tahdhib, al-Muhalla, al-Intisar) |
| seven-ahruf-cairo-edition-stoning-verse | Done (Qurtubi, al-Nashr); IslamWeb fatwas and al-Imam, Variant Readings of the Qur'an added (10 points) |
| women-in-the-prophets-life | Partly done: Bukhari notes 3, 5-11, 17, 19, 20 carry hadith numbers and Fath al-Bari pages; notes 4 (Muslim), 8 (Tirmidhi part), 12, 13, 14, 16, 18 still to verify |
| schacht-isnads-dating-hadith | Extended with Azami, On Schacht's Origins (pp. 117-122, 182-185, 197-201) |
| sira-ibn-ishaq-hisham-tabari | Azami Appendix 1 added; Shamela check of the remaining notes pending |
| end-of-time | Partly done: Bukhari 50, 59, 80, 3337, 3439, 6503 and Muslim 8, 588, 809, 2901 verified with Shamela pages; notes 3, 13, 14, 15 (Abu Dawud, Tirmidhi, Ibn Khaldun, Ibn Taymiyya) still to verify |
| prophets-marriages-zaynab-and-others | Partly done: Bukhari 4782, 4788, 4138, 5086, 7420 and Qurtubi on 33:37 verified; Muslim, Abu Dawud, Tirmidhi, Ibn Sad notes still to verify |
| race-in-quran-and-hadith | Partly done: Bukhari 30, 1245, 2886, 3754, 4469, 7139, 7142 verified with Fath al-Bari pages; Ahmad, Tirmidhi, Ibn Kathir, Tabari and Wahidi notes still to verify |
| islamic-rites-before-islam | Partly done: Bukhari 876, 1582, 1622, 1664, 2002, 2050, 3364, 3521, 3828 verified with Fath al-Bari pages; Muslim, Abu Dawud and Ibn Ishaq notes still to verify |
| jihad-meaning-quran-hadith-jurists | Partly done: Bukhari 8, 26, 53, 527, 2785, 2875, 3004, 4594 verified with Fath al-Bari pages; Tirmidhi, Abu Dawud, Ibn Majah, Ahmad, Muwatta, Shaybani and Ibn Qudama notes still to verify |
| first-muslims-kept-the-faith | Partly done: Bukhari 3231 and 3612 verified; Ibn Ishaq (Guillaume), Tabari, Ahmad notes still to verify |
| funeral-dhikr-dua | Partly done: Bukhari 1245, 1253, 1264, 1303, 1315, 1325, 6340, 6407 verified with Fath al-Bari pages; Muslim, Abu Dawud and Tirmidhi notes still to verify |
| raids-badr-and-the-conquest-of-mecca | Partly done: Bukhari 1846, 2048, 3045, 3957, 3986, 4276 verified; Badr casualty nuance added from Ibn Hajar; Bukhari 4086, 4318, 4339, Abu Dawud, Ahmad and Ibn Ishaq notes still to verify |
| taqiyya-myths | Partly done: Bukhari 2692 and 3029 checked on sunnah.com; Quran commentary and remaining hadith notes (Muslim, Kab ibn Malik, Hudaybiyya) still to verify |
| apes-and-pigs-and-the-jews | Partly done: Muslim 2663b and 2922, Bukhari 2916, 2926 and 4121, Abu Dawud 3002 checked on sunnah.com and Shamela; Tabari and Ibn Hisham notes still to verify |
| questions-about-aisha | Partly done: Bukhari 2664, 2880, 3894, 4141, 5133, 5136, 5158, Muslim 177a, Tirmidhi 3883 checked; Hisham ibn Urwa sentence corrected from Tahdhib al-Tahdhib; Taqrib on Asma, Zarkashi and Tabari notes still to verify |
| quranic-ethics | Partly done: Bukhari 13, 33, 2620, 5304, 6979 (Fath pages); Muslim 45a, 1787, 2589; Abu Dawud 3580; Tirmidhi 1337 checked. Remaining: Tabarani (note 5), Nawawi Riyad al-Salihin (note 16), Muslim and Bukhari in notes 9 to 22 where no number given |
| all other listicles | To do, densest first |
| hajj-rites-explained | Partly done: Bukhari 1, 1549, 1562, 1542, 1597, 1643, 3364 (Fath pages); Abu Dawud 1949; Muslim 1218a, 1955a checked. Remaining: Muslim reports in notes 3, 4, 6, 9, 11, Tirmidhi, Ahmad, Ibn Kathir |
| fighting-verses-in-context | Partly done: Muslim 1731a and Bukhari 4905 (Fath 8/648) checked. Remaining: Tabari tafsir, Ibn Kathir, Ibn Hisham, Mishnah notes (no Shamela id yet) |
| medina-jewish-tribes-and-qurayza | Partly done: Bukhari 4028, 2329, 4121, 4037, 4039, 6153 checked on sunnah.com with Fath pages. Remaining: Ibn Ishaq, Tabari, Abu Ubayd notes |
| first-revelation-demonic-claim | Partly done: Bukhari 2, 3, 7, 2731 checked on sunnah.com with Fath pages; Muslim 177a earlier. Remaining: Bukhari 6982 (sunnah.com fetch failed; Fath 12/359 already cited), Ibn Hisham, Tabari, Ibn Kathir |
| homosexuality-in-islam | Partly done: Bukhari 5269, 5066, 48 (Fath pages); Muslim 2699a; Abu Dawud 4462 (hasan sahih per al-Albani; sentence on grading revised). Remaining: Tirmidhi 1456 (sunnah.com fetch failed), Kugle |
| ramadan-last-ten-nights | Partly done: Bukhari 2017, 49, 2024, 2026 (Fath pages); Muslim 762a, 1174, 1175; Ibn Majah 3850 checked. Remaining: Tirmidhi 3513 (sunnah.com fetch failed), Bukhari 6, Muslim 1172 |
| quran-4-34 | Partly done: Muslim 1218a, 2328a, 974b; Abu Dawud 2142, 2146; Ibn Majah 1977, 2340 checked on sunnah.com. Remaining: Tirmidhi 3895, Tabari tafsir, Muwatta |
| who-is-allah | Partly done: Bukhari 7405, 2736, 3668, 3445 (Fath pages); Muslim 2751a, 2677a checked. Remaining: Tirmidhi 3507 (name list), Ibn Hisham on Abd Allah |
| sharia-and-hudud | Partly done: Bukhari 3017, 6824, 7352 (Fath pages); Muslim 1716a; Tirmidhi 1424 (weak); Abu Dawud 4394 checked. Remaining: Bukhari 6922 (sunnah.com fetch failed), Nasai, Ibn Majah and Muwatta for Safwan, Muslim for Maiz |
| magic-jinn-and-satan-hadiths | Partly done: Bukhari 5763, 6226, 1142, 1144, 3295, 608, 3860 (Fath pages); Muslim 2189a, 450a, 2814a checked. Remaining: Muslim 2815 (sunnah.com fetch failed), Bukhari 2 / Aisha note 13 (see first-revelation) |
| was-muhammad-illiterate-ummi | Partly done: Hudaybiyya report corrected (the words "did not write well" are in Bukhari 4251, Maghazi, not in the Peacemaking version; Ibn Hajar 7/503); Bukhari 2698, 2699, 4251 (Fath pages), Muslim 1783a checked. Remaining: Tabari and Ibn Kathir on 7:157, 29:48; Lane |
| slavery-in-islam | Partly done: Bukhari 1936, 2517, 4261 (Fath pages); Muslim 3029a, 1661a, 1657a checked. Remaining: Ahmad on Salman, Bukhari 4469 (Fath 8/152 earlier) |
| five-pillars-explained | Partly done: Bukhari 349, 1399-1400, 1903 (Fath pages); Muslim 96a checked; Bukhari 3364 and Muslim 1218a earlier. Remaining: Muslim 8 / Bukhari 8 already; Gabriel hadith numbering (Muslim 8) |
| fate-and-free-will | Partly done: Bukhari 5729, 4949 (Fath pages); Muslim 771a, 2219a; Tirmidhi 2517 (hasan Darussalam; al-Qattan called it munkar) checked. Remaining: Tabari on 3:54 |
| companions-who-started-with-nothing | Partly done: Bukhari 3861, 2350, 3780 (Fath pages). Remaining: Ibn Ishaq, Ibn Sa'd, Ahmad notes, Bukhari on Bilal |
| houris-and-paradise | Partly done: Bukhari 3244, 5778 (Fath pages); Muslim 2824a, 109a; Tirmidhi 1663, Ibn Majah 2799 checked; sentence on Tirmidhi grading revised. Remaining: Ahmad |
| apostasy-in-islam | Partly done: Bukhari 6922 (read in Fath 12/267), 6878 (12/201), 3017; Muslim 1676a, 1676c checked. Remaining: modern sources (Islamonweb, Pew, Gomaa) are not classical |
| kharijites-and-takfir | Partly done: Bukhari 6931, 6104 (Fath pages); Muslim 1066a-g checked. Remaining: Tabari on Siffin, Nahrawan and 5:44 |
| revisionist-case-weighed | Partly done: Bukhari 40 (Fath 1/95). Remaining: Ibn Hisham and secondary sources |
| war-and-killing-in-islam | Partly done: Bukhari 6871, 6533, 3015, 2474 (Fath pages); Muslim 1678a, 1731a checked. Remaining: Muwatta (Abu Bakr's instructions), Marrakesh Declaration is modern |
| qibla-change | Partly done: Bukhari 40, 4486, 3366, 403 (Fath pages); Muslim 520a; Tirmidhi 342 checked |
| hijab-and-modesty | Partly done: Bukhari 24, 4758 (Fath pages); Abu Dawud 2149, 4104; Tirmidhi 2777 checked. Corrected: Aisha praised the early emigrant women (Bukhari 4758), not the Ansar; Abu Dawud 4104 is called mursal by Abu Dawud and sahih by al-Albani. Remaining: Muslim 36 |
| ridda-wars-abu-bakr-fadak | Partly done: Bukhari 1399-1400, 4375, 5357, 2904, 4240-4241 (Fath pages). Corrected the Fadak sentence to rest on Umar's report on Banu al-Nadir (Bukhari 2904). Remaining: Tabari on year 11 AH |
| islamic-dilemma | Partly done (1.76.2): Bukhari 4485, 7363 (13/333-334), 6841 and the Tawhid chapter gloss (13/522-523); Ibn Kathir 2/5, 3/126-128, 3/155, 4/296; Tabari 8/432; al-Jawab al-Sahih 2/407, 3/425; al-Fisal 2/2. Remaining: Muslim stoning report, Lazarus-Yafeh |
| child-marriage-in-islam | Partly done: Bukhari 5136 (Fath 9/191); Abu Dawud 2096 checked. Remaining: Bukhari 5133, 3894, 5158 Aisha reports (see questions-about-aisha) |
| black-stone-and-kissing | Partly done: Bukhari 1613, 4287 (Fath pages), 1597; Tirmidhi 877 checked. Remaining: Ibn Ishaq/Ibn Hisham on rebuilding |
| before-your-first-friday-prayer | Partly done: Bukhari 912, 934, 717 (Fath pages) checked |
| mosques-adapt-to-place | Partly done: Bukhari 335, 446 (Fath pages) checked. Remaining: Ibn Ishaq on Medina mosque |
| islam-religion-of-peace | Partly done: cross-checked with the war-and-killing and kharijites entries; Muslim 54 unverified (sunnah.com fetch failed) |
| camel-urine-hadith | Partly done: Bukhari 233, 5685 (Fath pages); Muslim 1671a checked. Remaining: Muslim 2362 (palm pollination; sunnah.com fetch failed) |
| buraq-and-miracle-stories | Partly done: Bukhari 3207 (Fath 6/302), 2856; Muslim 162a checked. Remaining: Ibn Ishaq and Tabari on 17:1 |
| why-five-prayers | Partly done: Bukhari 46, 521-522, 349 (Fath pages) checked |
| relationship-with-god | Partly done: Muslim 395a; Bukhari 7405, 2736 checked. Remaining: Muslim repentance report |
| why-no-interest | Partly done: Tirmidhi 1285 checked. Remaining: Muslim 1598 and Abu Dawud 3508 (sunnah.com fetch failed) |
| who-wrote-the-quran | Partly done: Bukhari 113 (Fath 1/206). Remaining: Muslim 3004 (fetch failed), Bukhari on collection |
| uthman-copies | Partly done: Bukhari 4998 (9/43), 4987 (9/11) checked |
| natural-world-verses | Partly done: Muslim 2229a checked. Remaining: Bukhari chapter heading quoting Qatada |
| islamophobia-facts | Partly done: Bukhari 10 (Fath 1/53) |
| prophets-you-may-not-know | Partly done: Bukhari 4485 (Fath 8/170) |
| prophet-died-poisoning | Partly done: Bukhari 2617 (5/230); Muslim 2190a; Abu Dawud 4510 (weak per al-Albani, Bishr not named in that wording) checked. Remaining: Ibn Ishaq on Bishr |
| satanic-verses-report | Partly done: Bukhari 1071 (2/553) checked. Remaining: Tabari, Ibn Saad, Qadi Iyad, Ibn Hajar on sura 53 |
| who-saw-gabriel | Partly done: Bukhari 3 checked. Remaining: Muslim Dihya report |
| hanif-poets-and-musaylima | Partly done: Bukhari 3826-3828 (Fath 7/142-143; Ibn Hajar says Zayd died before the mission). Remaining: Ibn Ishaq, Tabari |
| end-of-the-world-prediction | Partly done: Bukhari 6504 (Fath 11/347) |
| shared-prophets | Partly done: Bukhari 1385, 122 (Fath pages) |
| scholars-who-shaped-islamic-study | Partly done: Bukhari 143, 75 (Fath pages) |
| kaaba-witnesses | Partly done: Bukhari 4355, 1583 (Fath pages) |
| terrorism-and-muslims-the-data | Partly done: Bukhari 25, 3015 (Fath pages). Remaining: Bukhari 3166, Muslim 1853 (fetch failed), Muwatta 971; Pew is secondary |
| islamic-art-and-the-one-god | Partly done: Bukhari 5950 (Fath 10/382) |
| indonesia | Partly done: Bukhari 1909 (Fath 4/119) |
| western-scholars-of-islam | Partly done: Bukhari 4992 (Fath 9/23) |
| quran-mysteries-in-the-stories | Partly done: Bukhari 122, 3346 (Fath pages). Remaining: Muslim 2880, Tabari on 25:38 |
| manuscript-transmission | Partly done: Bukhari 4998, 4987, 6829 (Fath pages) |
| quran-preservation-reddit | Partly done: Bukhari 4986-4987 |
| where-is-the-injil | Partly done: Bukhari 7363 |
| morality-without-god | Partly done: Bukhari 1385 |

#### sunnah.com checks (non-Bukhari hadith)

Checked for number, book, chapter, narrator and grade, and matching the claim in the article:

- Prophet's marriages: Muslim 1428b, 1438a, 1456a, 2972a; Abu Dawud 2107 (sahih, al-Albani), 3931 (hasan, al-Albani); Tirmidhi 3207 (weak, Darussalam), 3894 (sahih, Darussalam).
- Raids, Badr and the conquest of Mecca: Abu Dawud 2683 (sahih, al-Albani), 2766 (hasan, al-Albani).
- Race in the Quran and hadith: Tirmidhi 3931 (weak per Darussalam; Tirmidhi called it hasan).
- Islamic rites before Islam: Abu Dawud 1919 (sahih, al-Albani); Muslim 1134a, 1218a.
- Women in the Prophet's life: Muslim 976a and 976b (the weeping is in 976b); Abu Dawud 3887 (sahih, al-Albani); Tirmidhi 3883 (hasan, Darussalam).
- Aisha, taqiyya, apes and pigs: see the status table.
| abrogation-and-the-sword-verse | Partly done: Tabari 2:62 (vol. 2, p. 155, Ibn Abbas via Ali ibn Abi Talha) and 2:256 (vol. 5, p. 407) located. Remaining: Razi and Ibn Kathir pages. |
| quran-contradictions-claims | Partly done: Tabari 41:9–10 (vol. 21, pp. 431–435) and 39:42 (vol. 21, pp. 298–299) located. Remaining: Ibn Kathir 70:4. |
| quran-on-christians | Partly done: Bukhari 1601 (Fath vol. 3, p. 468), 435–436 (vol. 1, p. 532), Muslim 2767a verified on sunnah.com; Tabari 12156 located. Remaining: Ibn Kathir pages. |
| al-zutt-allegation | Partly done: Muslim 450a verified on sunnah.com. Remaining: Ahmad 3788 and 3782 (not on sunnah.com). |
| preservation-and-truth | Partly done: Tabari 15:9 (vol. 17, p. 68) and 18:86 (vol. 18, pp. 95–96) located. |
| quran-grammar-errors-claim | Partly done: Tabari 4:162 report 10838 (vol. 9, p. 395) and 20:63 (vol. 18, pp. 326–328) located. |
| talmud-midrash-apocrypha-borrowing | Partly done: Tabari 2:259 located (vol. 5, pp. 438–439). Remaining: Talmud Taanit 23a. |
| schacht-isnads-dating-hadith | Partly done: Bukhari 111–113 located (Fath vol. 1, pp. 204–206), 112 verified on sunnah.com. Remaining: Muslim Introduction reports. |
| quran-anachronism-claims | Partly done: Bukhari 3326 verified on sunnah.com. Remaining: Tabari 2:258, 6:84; Ibn Hibban scrolls report. |
| six-articles-of-faith | Partly done: Muslim 8a, 2996 and Bukhari 4949 verified on sunnah.com; Fath vol. 8, p. 709. Remaining: Tabari on 18:50 and 2:34. |
| quranic-stories-and-history | Partly done: Bukhari 4419 verified (Fath vol. 8, p. 125). Remaining: Tabari on Cyrus proposals. |
| crucifixion-quran | Partly done: Tabari 4:157 (vol. 9, pp. 367–371), Bukhari gloss on mutawaffika (Fath vol. 8, p. 283), Abu Dawud 4324 verified on sunnah.com. Remaining: Razi 4:157. |
| who-is-allah, relationship-with-god | Added Bukhari 7554 ("My mercy has preceded My wrath"; Fath vol. 13, p. 522) in body and notes, from the text supplied by the site owner. |
| dome-inscriptions | Partly done: Bukhari 4987 verified on sunnah.com; Fath vol. 9, p. 11. |
| ezra-and-mary | Partly done: Tabari 9:30 report 16620 (vol. 14, p. 202). Remaining: Ibn Kathir on 9:30. |
| second-peter, jesus-not-god, same-god, verses-read-differently, seven-libraries, haman-anachronism | Reviewed: notes cite only modern scholarship or the Bible; no classical Arabic source to verify. |
| islam-in-china | Partly done: Bukhari 335 and 428 verified on sunnah.com; Fath vol. 1, pp. 435, 524–525, 540. |
| crusades-facts | Partly done: Tabari History vol. 3, p. 609 (terms of Aelia) text checked on Shamela. |
| kharijites-and-takfir | Added a point on Ibn Abbas's debate with the Haruriyya (Nasa'i, Sunan al-Kubra 8575). |
| gods-attributes-and-anthropomorphism | New article. Fath vol. 13, pp. 406–408 (istawa, Malik via Ibn Wahb, Juwayni, six positions), vol. 5, p. 183 (pronoun in the image hadith), vol. 11, p. 3 (Bukhari 6227); Bukhari 1145 on sunnah.com; Tabari vol. 22, pp. 375–376 (Quran 50:38). |
| temporary-marriage-mutah | New article. Fath vol. 9, pp. 167–173 (Bukhari 5115–5119 commentary, the six occasions, Umar's announcement, consensus reports); Bukhari 5071, 5115, 4216, Muslim 1405a and 1406d on sunnah.com; Tabari vol. 8, pp. 175–181 (Quran 4:24). |
| do-muslims-worship-muhammad | New article. Bukhari 3445, 1043, 435, 3416, 3667–3668 and Muslim 2278 on sunnah.com; Fath vol. 6, pp. 478, 490; vol. 7, p. 19 on Shamela. |
| muslims-by-the-numbers | New data article. Figures checked against Pew Research Center reports of 9 June 2025 (muslim-population-change and the global religious landscape report). |
| nabi-and-rasul | New article (1.78.0). Bukhari 3340 cited from memory. Remaining: Ibn Taymiyya, al-Nubuwwat 2/714–718 (Shamela book 11817, page located, text not read); Qurtubi on 22:52; Ibn Kathir on 33:40; the Abu Dharr and Abu Umama reports (Ahmad, Ibn Hibban) and al-Albani, Sahiha 2668 |
| messenger-to-every-nation | New article (1.78.0). Bukhari 335 and 4419 already verified in other articles (Fath 8/125). Remaining: Bukhari 3443; Tabari on 16:36; Ibn Kathir on 17:15 |
| muhammad-and-the-hebrew-prophets | New article (1.78.0). Bukhari 2125 checked on sunnah.com (Sales, raising the voice in the market, Ata ibn Yasar from Abd Allah ibn Amr); Bukhari 4485 already verified (Fath 8/170). Remaining: Bukhari 3461 (sunnah.com fetch failed), 3413, 3416; Tabari on 21:105 |
| ishmael-in-the-quran-and-genesis | New article (1.78.0). Bukhari 3373 checked on sunnah.com (Salama ibn al-Akwa, Book of the Prophets); Muslim 2276 checked on sunnah.com (Wathila ibn al-Asqa, Book of Virtues); Bukhari 3364 already verified (Fath 6/396). Remaining: Tabari on 19:54 and 37:107; Ibn Kathir on 37:107 |
| no-compulsion-in-religion | New article (1.78.0). Abu Dawud 2682 checked on sunnah.com (Ibn Abbas, sahih per al-Albani); Qurtubi on 9:29 checked (Malik, al-Awzai, al-Shafii, the Arab idolaters, the Magians) on quran.ksu.edu.sa; Tabari 2:256 verdict already located (vol. 5, p. 407); Tabari, History 3/609 already verified. Remaining: Bukhari 3156–3157 (sunnah.com fetch failed); al-Baladhuri, Futuh al-Buldan, Futuh al-Sind (Shamela book 12221, section located at page 413, text not read); Ibn Qudama, al-Mughni, on the compelled dhimmi; al-Kasani on the Hanafi position |
| holy-war-in-the-bible | New article (1.78.0). Bukhari 3015 already verified. Remaining: Muslim 1744a; Muwatta on Abu Bakr's instructions |
| septuagint, oldest-bible-codices, comma-johanneum, old-testament-variant-readings, kings-and-chronicles-numbers, genealogies-of-jesus | New articles (1.78.0). Notes cite the Bible, rabbinic texts, the church fathers and modern scholarship only; the Quran references in genealogies-of-jesus and kings-and-chronicles-numbers need no Shamela check beyond Ibn Kathir and Qurtubi on 70:4, already listed under quran-contradictions-claims |
| bible-alteration-claim-before-islam | New article (1.78.0). Bukhari 7363 and the chapter-heading gloss already verified (Fath 13/333–334, 13/522–523); Ibn Hazm, al-Fisal 2/2 and Ibn Taymiyya, al-Jawab al-Sahih 2/407 already verified in islamic-dilemma |
| muhammad-false-prophet-claim (1.78.0 additions) | Remaining: Bukhari 4252; Tabari on 24:55; Ibn Kathir on 9:33 |
| jihad-meaning-quran-hadith-jurists (1.78.0 additions) | Remaining: Ibn Abidin, Radd al-Muhtar, Book of Jihad (definition and the stages); Ibn al-Humam, Fath al-Qadir |
| ezra-and-mary (1.78.0 addition) | Muslim 2135 checked on sunnah.com (al-Mughira ibn Shuba, Book of Manners) |

### Objections catalogue

A working list of the objections Muslims meet in public, each cross-referenced to the article that answers it. Where nothing answered an objection, an article was written (marked **new**). Add a section for each source you catalogue.

#### Source 1: replies to a post by The Muslim Apologetic on X

Saved 13 October 2026 from `x.com/TaymiApologia/status/2109053692364079460`. The post says Christians are trying to prove Quran corruption, that the Gospels circulated anonymously through the first century, and that the Quran is corroborated by 250,000 manuscripts. The saved page holds 18 replies. Insults are summarised here, not repeated.

##### Claims in the post itself

| # | Claim | Answered in | Status |
|---|---|---|---|
| O1 | Christians are trying to prove the Quran was corrupted | `manuscript-transmission`, `uthman-copies`, `quran-preservation-reddit`, `preservation-and-truth` (**new**) | Covered |
| O2 | The Gospels circulated anonymously in the first century | `who-wrote-the-quran` (**new**, contrast entry), touched in `where-is-the-injil`, `second-peter` | Partly covered; no article on Gospel authorship as such |
| O3 | The Quran is corroborated by 250,000 manuscripts | `preservation-and-truth` (**new**) | **Do not repeat the figure.** It has no source here, and two replies mock it. The articles avoid any manuscript count. Add one only with a cited source |

##### Replies

| # | Reply, in summary | Kind | Answered in | Status |
|---|---|---|---|---|
| R1 | "Go kiss your black rock" | Idolatry charge | `black-stone-and-kissing` (**new**, includes the comparison with the Western Wall and the Stone of Anointing), `hajj-rites-explained` (Umar's saying), `same-god`, `who-is-allah` | Covered by the new article |
| R2 | Pilgrims travel to kiss a stone set in a sink-like object | Idolatry charge | `black-stone-and-kissing` (**new**) | Covered by the new article |
| R3 | The poster is a propagandist too stingy to be verified | Personal attack | None needed | Not an argument |
| R4 | The poster is effeminate; the Prophet would ban him from funerals | Personal attack with an unclear reference to a report | None | Not an argument; the report it points to is unclear, so nothing was written |
| R5 | The Quran has no errors since it is made by God, "LOL" | Alleged errors | `quran-science-claims-graded`, `haman-anachronism`, `verses-read-differently`, `quranic-stories-and-history`, `natural-world-verses`, `quran-mysteries-in-the-stories`, `preservation-and-truth` (**new**, entry on content) | Covered |
| R6 | "Daily dose of camel urine" | Hadith ridicule | `camel-urine-hadith` (**new**) | Covered by the new article |
| R7 | Personal testimony: God chose a relationship with a sinner; "my testimony is my own proof" | Experiential argument; behind it the claim that Islam has no relationship with God | `relationship-with-god` (**new**) | Covered by the new article |
| R8 | The poster is a lying grifter | Personal attack | None needed | Not an argument |
| R9 | "250,000 manuscripts? Lol" | Doubt about the figure | `preservation-and-truth` (**new**) | See O3 |
| R10 | "250,000 lies is still lies" | Preservation does not make a text true | `preservation-and-truth` (**new**) | Covered by the new article |
| R11 | Insult | Personal attack | None needed | Not an argument |
| R12 | A perfectly preserved Quran is still corrupt (content); insults to the Prophet; the Quran is exalted above the Word of God | Content charge, plus abuse | `preservation-and-truth` (**new**), `jesus-not-god`, `crucifixion-quran` | Covered; the abuse is not answered |
| R13 | Make it 500,000; Muslims are stupid | Mockery | None needed | Not an argument |
| R14 | "You have an anonymous god" (answering the Gospels point) | Retort on anonymity | `who-wrote-the-quran` (**new**), `who-is-allah` | Covered |
| R15 | Paradise is a delusional fantasy | Afterlife as wishful thinking | `houris-and-paradise`, `end-of-time`, `six-articles-of-faith` | Partly covered: these explain what Muslims believe; none argues against the wishful-thinking charge. A candidate for a later article |
| R16 | (Italian) Muslims confuse the goodness of a message with protecting Muhammad's copyright | Preservation is not truth | `preservation-and-truth` (**new**) | Covered by the new article |
| R17 | Reply with no text in the saved page (media only) | Unknown | None | Cannot be assessed |
| R18 | "He talks as if he knows exactly who wrote the Quran" | Authorship | `who-wrote-the-quran` (**new**) | Covered by the new article |

##### New articles written for the gaps

| Slug | Title | Answers |
|---|---|---|
| `black-stone-and-kissing` | 5 facts about the Black Stone and why Muslims touch it | R1, R2 |
| `camel-urine-hadith` | The camel urine hadith: 5 things the report says | R6 |
| `who-wrote-the-quran` | Who wrote the Quran? 5 answers to the authorship claim | R14, R18, O2 |
| `preservation-and-truth` | Does a preserved text prove it is true? 5 answers | O1, O3, R5, R9, R10, R12, R16 |
| `relationship-with-god` | Do Muslims have a relationship with God? 5 answers | R7 |

##### Still open

- **Gospel authorship** (O2) has no article of its own.
- **The afterlife as wishful thinking** (R15) is explained but not argued.
- **A sourced count of Quran manuscripts** (O3) would let the manuscript articles answer the figure directly. Nothing here states one.
- The details in the new articles that need checking against sources are in "Citations to check" above.

#### Source 2: a thread under the same post, with a critic of the Quran and hadith

Saved 13 October 2026 from `x.com/jacriaz/status/2109439881596617021`. A critic replies to the post in Source 1; the thread has six turns.

| # | Claim, in summary | Kind | Answered in | Status |
|---|---|---|---|---|
| S1 | "Islam is like a fairy tale" | Charge that the miraculous is absurd | `buraq-and-miracle-stories` (**new**) | Covered by the new article |
| S2 | The Prophet's donkey Yafur "is a joke" | Hadith ridicule | `buraq-and-miracle-stories` (**new**, entry on the donkey: the talking-donkey narration is graded fabricated by hadith critics) | Covered |
| S3 | The winged Buraq carrying a man to heaven "is a joke" | Hadith ridicule | `buraq-and-miracle-stories` (**new**, entry on the Buraq: the sound reports do not mention wings), `why-five-prayers` (the Night Journey) | Covered |
| S4 | Reply from the Muslim side: the Bible has miracles too (Matthew 27:51 to 53) | Tu quoque | `buraq-and-miracle-stories` (**new**, entry on the same charge against the Bible) | Covered |
| S5 | "I am a critic of the Quran and hadith. I do not read the Bible" | Declines to apply one standard to both | `buraq-and-miracle-stories` (**new**, closing entry on how to weigh a miracle report and reading the other side) | Covered |
| S6 | "Name-calling is a sign that someone has run out of valid arguments" | Remark on tone | None | Not a claim about Islam |

The `buraq-and-miracle-stories` article does not mention the thread or the people in it.

#### Source 3: the Winn workbooks (rebuttal plan for a critique of Islam)

Five spreadsheets (a single-source-of-truth workbook, two article-proposal sheets, a missed-claims sheet and a master list) plan rebuttals of the claims in one critic's two volumes. The site does not name the critic or the books in any article. Tier 1 of the plan is now written:

| Plan ref | Title | Slug |
|---|---|---|
| A01 | 6 facts about Medina's Jewish tribes and Banu Qurayza | `medina-jewish-tribes-and-qurayza` |
| A02 | Muhammad's raids, Badr and the conquest of Mecca: 6 facts | `raids-badr-and-the-conquest-of-mecca` |
| A03 | The Satanic Verses report: 5 facts about a disputed story | `satanic-verses-report` |
| A04 | Was the first revelation demonic? 5 answers from the sources | `first-revelation-demonic-claim` |
| A06 | Magic, jinn and Satan hadiths: 6 reports that critics cite | `magic-jinn-and-satan-hadiths` |
| A07 | Ibn Ishaq, Ibn Hisham, Tabari: how reliable is the Sira? 5 facts | `sira-ibn-ishaq-hisham-tabari` |
| A09 | The revisionist case: 6 answers on manuscripts, coins and Mecca | `revisionist-case-weighed` |
| A12 | Abrogation (naskh) and the Sword Verse: 5 facts to know | `abrogation-and-the-sword-verse` |
| A15 | 6 facts about Muhammad's marriages: Zaynab, Safiyya and others | `prophets-marriages-zaynab-and-others` |
| A18 | Which rites existed before Islam? 6 answers from the sources | `islamic-rites-before-islam` |
| A20 | Apes and pigs and the Jews in the Quran and hadith: 6 facts | `apes-and-pigs-and-the-jews` |
| A22 | Terrorism and Muslims: 5 facts about the data and the texts | `terrorism-and-muslims-the-data` |
| A39 | Did Muhammad predict the end in 1110? 5 facts on the reports | `end-of-the-world-prediction` |
| A40 | Five answers on race in the Quran and the hadith reports critics cite | `race-in-quran-and-hadith` |
| A41 | What the Quran says about Christians and Christianity: 6 answers | `quran-on-christians` |

##### Still open

- Tier 2 and later proposals in the workbooks (A13, A14, A19, A24, A27, A34, A08, A42, A43, A46 and others) are not written.
- Every citation in these 15 articles was written from memory; "Citations to check" above lists what to check.

##### Source 3, second tier

| Plan ref | Title | Slug |
|---|---|---|
| A13 | The fighting verses in context: 6 facts from the sources | `fighting-verses-in-context` |
| A27 | 5 facts about why the first Muslims kept the faith in Mecca | `first-muslims-kept-the-faith` |
| A34 | 6 answers to the claim that the Quran gets its history wrong | `quran-anachronism-claims` |
| A14 | 5 facts about jihad in the Quran, the hadith and the jurists | `jihad-meaning-quran-hadith-jurists` |
| A24 | Talmud, midrash and apocrypha: 6 answers on borrowing claims | `talmud-midrash-apocrypha-borrowing` |
| A19 | 5 facts about the Hanif poets, Musaylima and the Quran | `hanif-poets-and-musaylima` |
| A08 | Schacht, isnads and the dating of hadith: 5 answers | `schacht-isnads-dating-hadith` |
| A42 | Was Muhammad illiterate? 5 answers on ummi, Iqra and clots | `was-muhammad-illiterate-ummi` |
| A43 | 5 facts about Abu Bakr, the Ridda wars and the Fadak dispute | `ridda-wars-abu-bakr-fadak` |

The concordance of hadith citations (A46) and the lower-priority proposals are not written.

#### Source 4: islamicdilemma.com

A saved copy of islamicdilemma.com, a Christian site built on the Islamic dilemma, reviewed 28 October 2026. Its pages are the home page ("What is the Islamic Dilemma?"), "The Qur'an affirms the Gospel", "How to Present the Islamic Dilemma", "What is the Injil?", "Why Jesus?", "What is your identity in Christ" and "Understanding Spiritual Blindness". The home page promotes a book by a Speakers' Corner debater and links to TheDawahScript. Devotional material with no claim about Islam is left out, as are the WordPress files.

##### Claims

| # | Claim, in summary | Answered in | Status |
|---|---|---|---|
| D1 | The dilemma itself: if the Gospel is reliable Islam contradicts it; if it is corrupt the Quran wrongly affirmed it | `islamic-dilemma` | Covered: all five assumptions |
| D2 | 3:3–4, 5:47, 5:48, 5:68 and 10:94 show the Quran affirms the Bible Christians held | `islamic-dilemma` (entries on muhaymin, the occasion of 5:43–47, Ibn Kathir on 5:47, 5:68 and 10:94) | Covered |
| D3 | The Injil cannot be a separate book: where is it, and who quoted it? | `where-is-the-injil`, `islamic-dilemma` (first entry, Ibn Hazm's "four histories") | Covered |
| D4 | **18:27, "none can change His words": so the Bible cannot have been corrupted** | `islamic-dilemma` (third entry, 1.77.1) | Covered. The phrase covers God's promise (6:34, 10:64) and the Quran itself (6:115, 18:27); Ibn Kathir on 18:27 and al-Tabari on 6:115, checked on Shamela; the Quran's own use of *baddala* for people changing words (2:59, 48:15) |
| D5 | "Allah, Gabriel and Muhammad never said the Gospel was corrupt"; every mention of the Injil praises it | `islamic-dilemma` (the mixed-text entry), `where-is-the-injil` | Covered: 5:14 added to the second entry of `islamic-dilemma` (1.77.1) |
| D6 | The site's own list of Injil verses (3:3, 3:48, 3:65, 5:46–47, 5:66, 5:68, 5:110, 7:157, 9:111, 48:29, 57:27) | `islamic-dilemma`, `deuteronomy-prophecy-and-the-paraclete` (7:157) | Covered: the first entry of `islamic-dilemma` (1.77.1) notes that 7:157 and 9:111 say what the Injil contains in the Quran's own terms, and that calling this an error about the Gospels assumes the Quran was describing them. A Christian may point to Matthew 19:29 for 9:111; the article does not lean on the absence |
| D7 | Three "contradictions": 1 Timothy 2:5 vs 4:171 (mediator), 1 Corinthians 8:6 vs the Quran (Trinity), John 3:16 vs 3:185 (eternal life) | `pauline-letters-authorship`, `paul-claim-to-apostleship`, `jesus-not-god`, `relationship-with-god` | Covered in substance. **Point to make:** two of the three are from Paul, not from any Gospel, and 1 Timothy is one of the Pastorals most critical scholars do not attribute to Paul, so the site's own examples do not come from the Injil. The page's citations are also confused: the Arabic under the 4:171 heading is 2:255, and the Trinity item quotes 3:185 in English over 4:171 in Arabic. Note this; it is not an argument |
| D8 | 29:46 commands Muslims to say "we believe in what was revealed to you", yet Muslims call the Bible corrupt and the Christian God false | `quran-on-christians`, `same-god`, `who-is-allah` | Covered. The verse speaks of what was revealed, not of what is now bound in a Bible; see D2 |
| D9 | "If Allah revealed the Torah and Gospel as guidance, how could they become corrupted?" | `where-is-the-injil` (entry on preservation: earlier scriptures entrusted to their communities, 5:44; the promise of 15:9 attached to the Quran) | Covered |
| D10 | "When was it corrupted, by whom, and where is the uncorrupted version?" The burden of proof is on the Muslim | `where-is-the-injil`, `manuscript-transmission` (the ending of Mark, the adulteress passage) | Covered in substance; the burden-of-proof framing is not answered directly. The answer: the site has to prove the Quran's Injil is the four Gospels first (D3), and later additions to the Gospel manuscripts are accepted by Christian textual critics |
| D11 | The Quran was "in the hands of Christians in 700" | None needed | Factual error: the Prophet died in 632 and the Quran describes the Christians of his lifetime |
| D12 | "Islam says you can get your own sins forgiven. That is a lie"; no forgiveness without the cross | `crucifixion-quran` (Leviticus 5:11–13, Ezekiel 18:20–22, Hosea 6:6), `relationship-with-god` (forgiveness without a mediator, 39:53, 6:164) | Covered |
| D13 | One lie equals murder before God; the Quran's teaching that good deeds remove bad ones (11:114) is "a lie" | `crucifixion-quran`, `relationship-with-god` | Partly covered: no article answers the claim that every sin weighs the same. The Gospels themselves grade sin and punishment: "the one who handed me over to you has the greater sin" (John 19:11), and the servant beaten with many or few blows (Luke 12:47–48) |
| D14 | Islam is a religion of works with no assurance; its God is distant; "religions of submission only" | `relationship-with-god`, `fate-and-free-will` | Covered in part. Add the hadith that no one enters Paradise by his deeds, not even the Prophet, unless God covers him with mercy (Sahih al-Bukhari 6463, Sahih Muslim 2816), if it is not already cited where it is needed |
| D15 | Muslims cannot see the truth because Satan blinds them (2 Corinthians 4:4, John 12:40) | None needed | Not an argument: it explains disagreement without testing it, and the Quran makes the same claim the other way (2:7). Not worth an article |
| D16 | A "renunciation" line: "I reject Islam and Muhammad and break the chains of association to that religion" | None needed | Not a claim |

##### Still open

- **D10, D13 and D14** are small additions to `where-is-the-injil`, `crucifixion-quran` and `relationship-with-god`, to be made as those articles are expanded.

#### Source 5: three recorded missionary lectures on Islam

Three transcripts of lectures given in English with German interpretation, probably at a missionary training seminar in the late 1980s, saved 29 October 2026. Each transcript stops at thirty minutes. The articles do not name the speakers or the seminar. Claims that rest on a particular polemicist's booklet are answered on the evidence and the booklet is not defended.

##### Lecture 1: apostleship, prophecy and prophethood

| # | Claim, in summary | Answered in | Status |
|---|---|---|---|
| L1 | The Quran's *nabi* and *rasul* cannot be told apart; the distinction was invented later | `nabi-and-rasul` (**new**) | Covered |
| L2 | A *rasul* receives a book, yet Adam, Noah and Abraham received none | `nabi-and-rasul` (**new**) | Covered (scrolls of Abraham, 87:19, 53:36) |
| L3 | *Nabi* means one who "bubbles forth" (Gesenius) | `nabi-and-rasul` (**new**) | Covered |
| L4 | 315 messengers means most nations had one; a prophet for every people makes no sense | `nabi-and-rasul`, `messenger-to-every-nation` (**new**) | Covered |
| L5 | The Quran records messengers only to Hebrews, Arabs or Judeo-Arabs | `messenger-to-every-nation` (**new**) | Covered (Moses to Pharaoh, Jonah to Nineveh) |
| L6 | Hud, Salih and Shuayb are legend | `messenger-to-every-nation` (**new**), `quranic-stories-and-history` | Covered |
| L7 | Muhammad never quotes David, Job or John and did not know the writing prophets | `muhammad-and-the-hebrew-prophets` (**new**) | Covered (21:105 and Psalm 37:29; Jonah) |
| L8 | Ishmael was neither prophet nor apostle, only a tribal sheikh | `ishmael-in-the-quran-and-genesis` (**new**) | Covered |
| L9 | Muslim expositors give prophethood to the "seed" of Isaac, Ishmael and Lot | `ishmael-in-the-quran-and-genesis` (**new**) | Covered |
| L10 | Miriam of Exodus confused with Mary | `ezra-and-mary` (paragraph added, Muslim 2135) | Covered |
| L11 | No genuine prophecy in the Quran; no clear statement that Islam will prevail | `muhammad-false-prophet-claim` (paragraphs added: 48:27, 24:55, 9:33) | Covered |
| L12 | Muhammad used "the last shall be first" to place himself at the head of the prophets | None | Not answered: no source given, and the Quranic term is *khatam* (33:40), discussed in `nabi-and-rasul` |
| L13 | Fanatical devotion to the Prophet | `do-muslims-worship-muhammad`, `blasphemy-and-insults-to-the-prophet` | Covered |

##### Lecture 2: jihad or the holy war

| # | Claim, in summary | Answered in | Status |
|---|---|---|---|
| J1 | Jihad in itself means war; the milder meaning is a liberal invention | `jihad-meaning-quran-hadith-jurists` (paragraphs added on *qital*) | Covered |
| J2 | Many verses command aggression and only one, 2:256, commands tolerance | `no-compulsion-in-religion` (**new**), `fighting-verses-in-context` | Covered |
| J3 | 2:256 excludes idolaters, so Hindus and Buddhists are excluded (Sell) | `no-compulsion-in-religion` (**new**) | Covered (Malik, al-Awzai, the Hanafis; Sind) |
| J4 | 2:256 is abrogated by 9:5 | `no-compulsion-in-religion` (**new**), `abrogation-and-the-sword-verse` | Covered |
| J5 | Ibn Abidin's stages from preaching to fighting absolutely | `jihad-meaning-quran-hadith-jurists` (paragraph added) | Conceded and set in context |
| J6 | A holy war is a contradiction in terms; Christ reversed the Old Testament | `holy-war-in-the-bible` (**new**) | Covered |
| J7 | Muslims cite the Crusades and never their own holy wars | `holy-war-in-the-bible` (**new**), `crusades-facts` | Covered |
| J8 | Jihad is a sixth pillar | `five-pillars-explained`, `jihad-meaning-quran-hadith-jurists` | Partly covered; no article traces the "sixth pillar" phrase |
| J9 | An aside on Jewish money-lenders | None | Not an argument; not repeated |

##### Lecture 3: has the Bible been falsified?

| # | Claim, in summary | Answered in | Status |
|---|---|---|---|
| B1 | No other religion says the Bible was altered; Muslims start from a presupposition | `bible-alteration-claim-before-islam` (**new**) | Covered |
| B2 | The Septuagint has exactly the same books and text as the Hebrew | `septuagint` (**new**) | Covered |
| B3 | The Old Testament has no variant readings | `old-testament-variant-readings` (**new**) | Covered |
| B4 | Some 4,000 New Testament manuscripts before 400; three complete Bibles | `oldest-bible-codices` (**new**), `manuscript-transmission` | Covered |
| B5 | Only two disputed passages, half a page | `oldest-bible-codices`, `comma-johanneum` (**new**) | Covered |
| B6 | 1 John 5:7 first appears in Jerome's Vulgate; Matthew 28:19 does the same work | `comma-johanneum` (**new**) | Covered |
| B7 | 40,000 and 4,000 stalls differ by a dot | `kings-and-chronicles-numbers` (**new**) | Covered |
| B8 | Quran 32:5 and 70:4 are the same kind of discrepancy | `kings-and-chronicles-numbers` (**new**), `quran-contradictions-claims` | Covered |
| B9 | The genealogies are Joseph's and Mary's | `genealogies-of-jesus` (**new**) | Covered |
| B10 | John 3:16 "begotten" is only a translation question | `jesus-not-god` (paragraph added) | Covered |
| B11 | The four women in Matthew's genealogy; the "50,000 errors" figure | None | Not answered by choice: these rest on one polemicist's booklet, which the site does not defend |

##### Still open

- The second half of each lecture was not transcribed. The hadith section of lecture 1, the blackboard list of verses in lecture 2 and the remaining objections of lecture 3 are not catalogued.
- The details in the new articles that need checking against sources are listed in "Citations to check" above, under "Lecture articles (29 October 2026, released in 1.88.0)".

## Version history

See `CHANGELOG.md`.
