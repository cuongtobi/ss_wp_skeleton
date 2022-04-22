<?php
get_header();
?>
<h1>Super Simple Skeleton Theme for Wordpress</h1>

<section>
    <ul>
        <?php
            $paged = get_query_var('paged');
            $query = new WP_Query([
                'posts_per_page' => SSWPS_POSTS_PER_PAGE,
                'paged' => $paged,
            ]);
            $total = $query->max_num_pages;
            $current = max(1, $paged);

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    get_template_part('template-parts/content', get_post_type());
                }
            } else {
                get_template_part('template-parts/content-none', get_post_type());
            }
        ?>
    </ul>

    <?php sswps_pagination($current, $total, 'pagination') ?>
</section>

<?php get_sidebar(); ?>

<?php
get_footer();
