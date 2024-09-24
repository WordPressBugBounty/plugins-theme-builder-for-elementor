<?php
get_header();

if (have_posts()) : while (have_posts()) : the_post();
        ?>
        <div <?php post_class(); ?>>
            <?php the_content(); ?>
        </div>
        <?php
    endwhile;
else :
endif;
get_footer();
