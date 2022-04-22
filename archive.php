<?php
get_header();
?>

<?php the_archive_title('<h1>', '</h1>'); ?>

<section>
    <ul>
	<?php
	    if (have_posts()) {
		while (have_posts()) {
		    the_post();
		    get_template_part('template-parts/content', get_post_type());
		}
	    } else {
	        get_template_part('template-parts/content-none', get_post_type());
	    }
	?>
    </ul>

    <?php the_posts_pagination(); ?>
</section>

<?php
get_footer();
