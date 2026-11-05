<?php
/**
 * Title: Questions readers ask
 * Slug: bestofislam/faq
 * Categories: bestofislam-listicles
 * Description: A heading and three FAQ-style Details blocks. The questions are marked up as an FAQPage only while they are on the page.
 * Keywords: faq, questions, answers
 *
 * @package BestOfIslam
 */

defined( 'ABSPATH' ) || exit;

?>
<!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Questions readers ask', 'bestofislam' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:details {"className":"is-style-faq"} -->
<details class="wp-block-details is-style-faq"><summary><?php esc_html_e( 'Write the question as a reader would ask it', 'bestofislam' ); ?></summary><!-- wp:paragraph -->
<p><?php esc_html_e( 'Answer it in one or two sentences, and name the source.', 'bestofislam' ); ?></p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details {"className":"is-style-faq"} -->
<details class="wp-block-details is-style-faq"><summary><?php esc_html_e( 'A second question', 'bestofislam' ); ?></summary><!-- wp:paragraph -->
<p><?php esc_html_e( 'Its answer.', 'bestofislam' ); ?></p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details {"className":"is-style-faq"} -->
<details class="wp-block-details is-style-faq"><summary><?php esc_html_e( 'A third question', 'bestofislam' ); ?></summary><!-- wp:paragraph -->
<p><?php esc_html_e( 'Its answer.', 'bestofislam' ); ?></p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->
