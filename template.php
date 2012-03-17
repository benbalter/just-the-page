<?php
/**
 * Template Name: No Formatting
 *
 * Removes the header and footer, just displays post content
 *
 * @author Benjamin J. Balter <ben@balter.com>
 */

if ( have_posts() ) : while ( have_posts() ) : the_post(); 

the_content();
					
endwhile; endif;