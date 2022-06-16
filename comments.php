<?php
if (post_password_required()) {
	return;
}
?>

<section id="comments">
    <?php
        if (have_comments()) {
            $comment_count = get_comments_number();
            echo "<h3>Comments Count: $comment_count</h3>";

            wp_list_comments(
                array(
                    'style' => 'div',
                    'short_ping' => true,
                )
            );

            the_comments_navigation();
        }

        if (!comments_open()) {
            echo '<p>Comments are closed.</p>';
        }

        comment_form();
    ?>
</section>
