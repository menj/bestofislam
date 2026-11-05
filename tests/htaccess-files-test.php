<?php
/**
 * Self-test for the "block direct file access" lines that inc/security.php
 * writes into .htaccess. Run: php tests/htaccess-files-test.php
 *
 * Takes boi_sec_rewrite_prefix(), boi_sec_rewrite_quote() and
 * boi_sec_files_htaccess_lines() out of inc/security.php, builds the lines
 * for a site at the domain root and for one in a subfolder, checks their
 * shape (balanced IfModule, each RewriteCond followed by a RewriteRule,
 * every pattern a valid regular expression) and runs them over request
 * paths the way mod_rewrite does in .htaccess: the pattern is matched
 * against the path below the .htaccess folder, a RewriteCond against the
 * full REQUEST_URI.
 *
 * @package BestOfIslam
 */

if ( 'cli' !== PHP_SAPI ) {
	exit;
}

define( 'ABSPATH', '/srv/site/' );
define( 'BOI_DIR', '/srv/site/wp-content/themes/bestofislam' );
function wp_normalize_path( $p ) { return str_replace( '\\', '/', (string) $p ); } // phpcs:ignore
function trailingslashit( $p ) { return rtrim( (string) $p, '/\\' ) . '/'; } // phpcs:ignore
function get_theme_root() { return $GLOBALS['boi_theme_root']; } // phpcs:ignore

$boi_src = (string) file_get_contents( dirname( __DIR__ ) . '/inc/security.php' );
foreach ( array( 'boi_sec_rewrite_prefix', 'boi_sec_rewrite_quote', 'boi_sec_files_htaccess_lines' ) as $boi_fn ) {
	if ( ! preg_match( '/^function ' . $boi_fn . '\(.*?^}$/ms', $boi_src, $boi_m ) ) {
		echo "FAIL $boi_fn not found in inc/security.php\n";
		exit( 1 );
	}
	eval( $boi_m[0] ); // phpcs:ignore Squiz.PHP.Eval.Discouraged -- test only.
}

$boi_fail = 0;
function boi_check( $ok, $label ) {
	global $boi_fail;
	echo ( $ok ? 'ok   ' : 'FAIL ' ) . $label . "\n";
	$boi_fail += $ok ? 0 : 1;
}

/**
 * Whether the lines answer 403 for a request, as mod_rewrite would.
 *
 * @param string[] $lines Lines.
 * @param string   $uri   REQUEST_URI.
 * @param string   $base  URL path of the .htaccess folder, with slashes.
 * @return bool
 */
function boi_forbidden( $lines, $uri, $base ) {
	$path  = (string) substr( $uri, strlen( $base ) );
	$conds = array();
	foreach ( $lines as $line ) {
		if ( preg_match( '/^RewriteCond %\{REQUEST_URI\} (!?)(\S+)$/', $line, $m ) ) {
			$conds[] = array( '!' === $m[1], $m[2] );
			continue;
		}
		if ( preg_match( '/^RewriteRule (\S+) - \[F\]$/', $line, $m ) ) {
			$pass = 1 === preg_match( '#' . $m[1] . '#', $path );
			foreach ( $conds as $c ) {
				$hit  = 1 === preg_match( '#' . $c[1] . '#', $uri );
				$pass = $pass && ( $c[0] ? ! $hit : $hit );
			}
			$conds = array();
			if ( $pass ) {
				return true;
			}
		}
	}
	return false;
}

$boi_denied  = array( '.git/config', '.git/HEAD', '.gitignore', '.claude/CLAUDE.md', 'wp-content/themes/bestofislam/.git/config', 'wp-content/themes/bestofislam/.claude/FARUQI.md', 'wp-includes/version.php', 'wp-includes/blocks/archives.php', 'wp-includes/version.php/x', 'wp-includes/blocks/archives.php/wp-includes/js/tinymce/wp-tinymce.php', 'wp-includes/blocks/archives.php/wp-includes/ms-files.php', 'wp-settings.php/x', 'wp-admin/includes/admin.php', 'wp-content/themes/bestofislam/functions.php', 'wp-content/themes/twentytwentyfive/functions.php', 'wp-content/themes/bestofislam/tests/unlist-test.php', 'wp-content/themes/bestofislam/bin/build-error-pages.php', 'wp-content/themes/bestofislam/docs/SSOT.md', 'wp-content/themes/bestofislam/docs/', 'wp-settings.php', 'wp-config-sample.php', 'wp-admin/install.php', 'readme.html', 'license.txt' );
$boi_allowed = array( '', 'index.php', 'wp-login.php', 'wp-cron.php', 'xmlrpc.php', 'wp-admin/', 'wp-admin/index.php', 'wp-admin/admin-ajax.php', 'wp-admin/post.php', 'wp-admin/load-scripts.php', 'wp-json/wp/v2/posts', 'wp-includes/js/tinymce/wp-tinymce.php', 'wp-includes/ms-files.php', 'wp-includes/js/dist/block-editor.min.js', 'wp-includes/css/dist/block-library/style.min.css', 'wp-content/themes/bestofislam/style.css', 'wp-content/themes/bestofislam/assets/js/theme.js', 'wp-content/themes/bestofislam/testsuite.css', 'wp-content/uploads/2026/11/photo.webp', 'what-is-the-qibla/', 'binary-choices/', 'documents/' );

$boi_sites = array(
	'at the domain root' => array( '/srv/site/', '/' ),
	'in a subfolder'     => array( '/srv/site/', '/blog/' ),
);
foreach ( $boi_sites as $boi_label => $boi_site ) {
	$GLOBALS['boi_theme_root'] = '/srv/site/wp-content/themes';
	$boi_lines = boi_sec_files_htaccess_lines( $boi_site[0] );

	boi_check( '<IfModule mod_rewrite.c>' === $boi_lines[0] && '</IfModule>' === end( $boi_lines ) && 2 === count( preg_grep( '/IfModule/', $boi_lines ) ), "$boi_label: one balanced IfModule guard" );
	$boi_shape = true;
	foreach ( $boi_lines as $boi_i => $boi_line ) {
		if ( 0 === strpos( $boi_line, 'RewriteCond' ) && 0 !== strpos( $boi_lines[ $boi_i + 1 ] ?? '', 'RewriteRule' ) ) {
			$boi_shape = false;
		}
		if ( preg_match( '/^Rewrite(?:Rule|Cond %\{REQUEST_URI\} !?)\s*(\S+)/', $boi_line, $boi_m ) && false === @preg_match( '#' . $boi_m[1] . '#', '' ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors
			$boi_shape = false;
		}
		if ( ! preg_match( '/^(<IfModule mod_rewrite\.c>|<\/IfModule>|RewriteEngine On|RewriteCond %\{REQUEST_URI\} \S+|RewriteRule \S+ - \[F\])$/', $boi_line ) ) {
			$boi_shape = false;
		}
	}
	boi_check( $boi_shape, "$boi_label: every line is a known directive with a valid pattern" );

	foreach ( $boi_denied as $boi_p ) {
		boi_check( boi_forbidden( $boi_lines, $boi_site[1] . $boi_p, $boi_site[1] ), "$boi_label: 403 for /$boi_p" );
	}
	foreach ( $boi_allowed as $boi_p ) {
		boi_check( ! boi_forbidden( $boi_lines, $boi_site[1] . $boi_p, $boi_site[1] ), "$boi_label: open /$boi_p" );
	}
}

// A folder name with a regex metacharacter is quoted, so its own paths match.
boi_check( 'wp\\.6/' === boi_sec_rewrite_prefix( '/srv/wp.6/', '/srv/' ), 'a dot in a folder name is escaped, not replaced' );
$boi_pfx = boi_sec_rewrite_prefix( '/srv/wp.6/', '/srv/' );
boi_check( 1 === preg_match( '#^' . $boi_pfx . 'wp-includes/.+\\.php$#', 'wp.6/wp-includes/version.php' ) && 0 === preg_match( '#^' . $boi_pfx . 'wp-includes/.+\\.php$#', 'wpx6/wp-includes/version.php' ), 'a pattern with the quoted prefix matches that folder only' );

// WordPress in its own folder below the .htaccess (wp/), themes beside it.
$GLOBALS['boi_theme_root'] = '/srv/site/wp-content/themes';
$boi_split = boi_sec_files_htaccess_lines( '/srv/' );
boi_check( (bool) preg_grep( '#\^site/wp-includes/#', $boi_split ) && (bool) preg_grep( '#\^site/wp-content/themes/#', $boi_split ), 'core and themes below the .htaccess folder get their prefix' );
boi_check( boi_forbidden( $boi_split, '/site/wp-includes/version.php', '/' ) && ! boi_forbidden( $boi_split, '/site/wp-includes/js/tinymce/wp-tinymce.php', '/' ), 'the prefixed rules refuse and allow as at the root' );

echo $boi_fail ? "$boi_fail failed\n" : "all passed\n";
exit( $boi_fail ? 1 : 0 );
