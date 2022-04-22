<?php
function sswps_pagination($current, $total, $wrapper_class) {
	if ($total > 1) {
		echo '<div class="' . $wrapper_class . '">';
        
		echo paginate_links(array(
			'base' => get_pagenum_link(1) . '%_%',
			'format' => 'page/%#%',
			'current' => $current,
			'total' => $total,
			'prev_text' => '«',
			'next_text' => '»',
		));
        
		echo '</div>';
	}

	wp_reset_postdata();
}
