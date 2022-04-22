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

    <?php if ($total > 1) { ?>
        <div class="pagination">
            <?php
                echo paginate_links(array(
                    'base' => get_pagenum_link(1) . '%_%',
                    'format' => 'page/%#%',
                    'current' => $current,
                    'total' => $total,
                    'prev_text' => '«',
                    'next_text' => '»',
                ));
            ?>                    
        </div>
    <?php
        }
        wp_reset_postdata();
    ?>
</section>

<?php get_sidebar(); ?>

<?php
get_footer();
