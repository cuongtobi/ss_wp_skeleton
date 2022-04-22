<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body>
<header>
    <nav>
        <ul>
            <li>
                <a href="<?php bloginfo('wpurl') ?>" title="Super Simple Skeleton Theme for Wordpress">
                    SS_WP_SKELETON
                </a>
            </li>
            <?php
                $categories = get_categories([
                    'orderby' => 'name',
                    'order'   => 'ASC',
                ]);

                foreach( $categories as $category ) {
            ?>
                <li>
                    <a href="<?php echo get_category_link($category->term_id) ?>" title="<?php echo $category->name ?>">
                        <?php echo $category->name ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
        <form action="<?php bloginfo('wpurl') ?>" method="get">
            <input type="search" name="s">
            <button type="submit">Search</button>
        </form>
    </nav>
</header>
