<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package wordpress-gulp
 */

if ( ! function_exists( 'wpg_posts_navigation' ) ) :
/**
 * Display navigation to next/previous set of posts when applicable.
 *
 * @todo Remove this function when WordPress 4.3 is released.
 */
function wpg_posts_navigation() {
  // Don't print empty markup if there's only one page.
  if ( $GLOBALS['wp_query']->max_num_pages < 2 ) {
    return;
  }
  ?>
  <nav class="Pager" role="navigation" aria-labelledby="posts-nav-heading">
    <p id="posts-nav-heading" class="u-hiddenVisually">Posts navigation</p>
    <ul class="Pager-list u-cf">
      <?php if ( get_next_posts_link() ) : ?>
        <li class="Pager-listItem Pager-previous"><?php next_posts_link( esc_html__( 'Older posts', 'wpg' ) ); ?></li>
      <?php endif; ?>
      <?php if ( get_previous_posts_link() ) : ?>
        <li class="Pager-listItem Pager-next"><?php previous_posts_link( esc_html__( 'Newer posts', 'wpg' ) ); ?></li>
      <?php endif; ?>
    </ul>
  </nav>
  <?php
}
endif;

if ( ! function_exists( 'wpg_post_navigation' ) ) :
/**
 * Display navigation to next/previous post when applicable.
 *
 * @todo Remove this function when WordPress 4.3 is released.
 */
function wpg_post_navigation() {
  // Don't print empty markup if there's nowhere to navigate.
  $previous = ( is_attachment() ) ? get_post( get_post()->post_parent ) : get_adjacent_post( false, '', true );
  $next     = get_adjacent_post( false, '', false );

  if ( ! $next && ! $previous ) {
    return;
  }
  ?>
  <nav class="Pager" role="navigation" aria-labelledby="posts-nav-heading">
    <p id="posts-nav-heading" class="u-hiddenVisually">Posts navigation</p>
    <ul class="Pager-list u-cf">
      <?php
        previous_post_link( '<li class="Pager-listItem Pager-previous">%link</li>', '%title' );
        next_post_link( '<li class="Pager-listItem Pager-next">%link</li>', '%title' );
      ?>
    </ul>
  </nav>
  <?php
}
endif;

if ( ! function_exists( 'wpg_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function wpg_posted_on() {
  $time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
  if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
    $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
  }

  $time_string = sprintf( $time_string,
    esc_attr( get_the_date( 'c' ) ),
    esc_html( get_the_date() ),
    esc_attr( get_the_modified_date( 'c' ) ),
    esc_html( get_the_modified_date() )
  );

  $posted_on = sprintf(
    esc_html_x( 'Posted on %s', 'post date', 'wpg' ),
    '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
  );

  $byline = sprintf(
    esc_html_x( 'by %s', 'post author', 'wpg' ),
    '<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
  );

  echo '<span class="posted-on">' . $posted_on . '</span><span class="byline"> ' . $byline . '</span>';

}
endif;

if ( ! function_exists( 'wpg_entry_footer' ) ) :
/**
 * Prints HTML with meta information for the categories, tags and comments.
 */
function wpg_entry_footer() {
  // Hide category and tag text for pages.
  if ( 'post' === get_post_type() ) {
    /* translators: used between list items, there is a space after the comma */
    $categories_list = get_the_category_list( esc_html__( ', ', 'wpg' ) );
    if ( $categories_list && wpg_categorized_blog() ) {
      printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'wpg' ) . '</span>', $categories_list );
    }

    /* translators: used between list items, there is a space after the comma */
    $tags_list = get_the_tag_list( '', esc_html__( ', ', 'wpg' ) );
    if ( $tags_list ) {
      printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'wpg' ) . '</span>', $tags_list );
    }
  }

  if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
    echo '<span class="comments-link">';
    comments_popup_link( esc_html__( 'Leave a comment', 'wpg' ), esc_html__( '1 Comment', 'wpg' ), esc_html__( '% Comments', 'wpg' ) );
    echo '</span>';
  }

  edit_post_link(
    sprintf(
      /* translators: %s: Name of current post */
      esc_html__( 'Edit %s', 'wpg' ),
      the_title( '<span class="screen-reader-text">"', '"</span>', false )
    ),
    '<span class="edit-link">',
    '</span>'
  );
}
endif;

if ( ! function_exists( 'the_archive_description' ) ) :
/**
 * Shim for `the_archive_description()`.
 *
 * Display category, tag, or term description.
 *
 * @todo Remove this function when WordPress 4.3 is released.
 *
 * @param string $before Optional. Content to prepend to the description. Default empty.
 * @param string $after  Optional. Content to append to the description. Default empty.
 */
function the_archive_description( $before = '', $after = '' ) {
  $description = apply_filters( 'get_the_archive_description', term_description() );

  if ( ! empty( $description ) ) {
    /**
     * Filter the archive description.
     *
     * @see term_description()
     *
     * @param string $description Archive description to be displayed.
     */
    echo $before . $description . $after;
  }
}
endif;

/**
 * Returns true if a blog has more than 1 category.
 *
 * @return bool
 */
function wpg_categorized_blog() {
  if ( false === ( $all_the_cool_cats = get_transient( 'wpg_categories' ) ) ) {
    // Create an array of all the categories that are attached to posts.
    $all_the_cool_cats = get_categories( array(
      'fields'     => 'ids',
      'hide_empty' => 1,

      // We only need to know if there is more than one category.
      'number'     => 2,
    ) );

    // Count the number of categories that are attached to the posts.
    $all_the_cool_cats = count( $all_the_cool_cats );

    set_transient( 'wpg_categories', $all_the_cool_cats );
  }

  if ( $all_the_cool_cats > 1 ) {
    // This blog has more than 1 category so wpg_categorized_blog should return true.
    return true;
  } else {
    // This blog has only 1 category so wpg_categorized_blog should return false.
    return false;
  }
}

/**
 * Flush out the transients used in wpg_categorized_blog.
 */
function wpg_category_transient_flusher() {
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
    return;
  }
  delete_transient( 'wpg_categories' );
}
add_action( 'edit_category', 'wpg_category_transient_flusher' );
add_action( 'save_post',     'wpg_category_transient_flusher' );
