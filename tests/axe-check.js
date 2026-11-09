/**
 * Accessibility check: axe-core (WCAG 2.0 to 2.2 A and AA) on the main templates.
 *
 *   npm i playwright axe-core        (in a scratch folder, not in the theme)
 *   BOI_BASE=http://127.0.0.1:8818 node tests/axe-check.js [/path ...]
 *
 * Exits 1 on any serious or critical violation at 375px or 1280px wide.
 * Scroll-reveal fades are waited out, since axe reads mid-fade colours.
 */
const { chromium } = require( 'playwright' );
const axeSrc = require( 'fs' ).readFileSync( require.resolve( 'axe-core/axe.min.js' ), 'utf8' );
const base = process.env.BOI_BASE || 'http://127.0.0.1:8818';
const paths = process.argv.length > 2 ? process.argv.slice( 2 ) : [ '/', '/glossary/', '/glossary/adoption/', '/faq/', '/about/contact/', '/?s=god', '/nope-xyz/' ];
( async () => {
	const browser = await chromium.launch();
	let failed = 0;
	for ( const width of [ 375, 1280 ] ) {
		const page = await ( await browser.newContext( { viewport: { width, height: 800 } } ) ).newPage();
		for ( const path of paths ) {
			await page.goto( base + path, { waitUntil: 'load' } );
			await page.waitForTimeout( 3000 );
			await page.addScriptTag( { content: axeSrc } );
			const found = await page.evaluate( async () => ( await axe.run( document, { runOnly: [ 'wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa' ] } ) ).violations );
			for ( const v of found.filter( ( x ) => [ 'serious', 'critical' ].includes( x.impact ) ) ) {
				failed++;
				console.log( `${ width } ${ path } ${ v.id } (${ v.impact }) x${ v.nodes.length }: ${ v.nodes[ 0 ].target.join( ' ' ) }` );
			}
		}
	}
	await browser.close();
	console.log( failed ? `${ failed } violation(s)` : 'axe: no serious or critical violations' );
	process.exit( failed ? 1 : 0 );
} )();
