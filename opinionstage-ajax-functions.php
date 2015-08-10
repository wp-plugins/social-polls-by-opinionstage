<?php

add_action( 'wp_ajax_opinionstage_ajax_toggle_flyout', 'opinionstage_ajax_toggle_flyout');
add_action( 'wp_ajax_opinionstage_ajax_toggle_article_placement', 'opinionstage_ajax_toggle_article_placement');

function opinionstage_ajax_toggle_flyout() {	
	$os_options = (array) get_option(OPINIONSTAGE_OPTIONS_KEY);
	$os_options['fly_out_active'] = $_POST['activate'];
	update_option(OPINIONSTAGE_OPTIONS_KEY, $os_options);
}

function opinionstage_ajax_toggle_article_placement() {
	$os_options = (array) get_option(OPINIONSTAGE_OPTIONS_KEY);
	$os_options['article_placement_active'] = $_POST['activate'];
	update_option(OPINIONSTAGE_OPTIONS_KEY, $os_options);
}
?>