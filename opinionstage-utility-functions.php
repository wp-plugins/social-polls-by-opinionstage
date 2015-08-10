<?php
/**
 * Main function for creating the widget html representation.
 * Transforms the shortcode parameters to the desired iframe call.
 *
 * Syntax as follows:
 * shortcode name - OPINIONSTAGE_WIDGET_SHORTCODE
 *
 * Arguments:
 * @param  id - Id of the poll
 *
 */
function opinionstage_add_poll($atts) {
	extract(shortcode_atts(array('id' => 0, 'type' => 'poll'), $atts));
	if(!is_feed()) {
		$id = intval($id);
		return opinionstage_create_embed_code($id, $type);
	} else {
		return __('Note: There is a poll embedded within this post, please visit the site to participate in this post\'s poll.', OPINIONSTAGE_WIDGET_UNIQUE_ID);
	}
}

/**
 * Main function for creating the placement html representation.
 * Transforms the shortcode parameters to the desired code.
 *
 * Syntax as follows:
 * shortcode name - OPINIONSTAGE_PLACEMENT_SHORTCODE
 *
 * Arguments:
 * @param  id - Id of the placement
 *
 */
function opinionstage_add_placement($atts) {
	extract(shortcode_atts(array('id' => 0), $atts));
	if(!is_feed()) {
		$id = intval($id);
		return opinionstage_create_placement_embed_code($id);
	} 
}

/**
 * Create the The iframe HTML Tag according to the given parameters.
 * Either get the embed code or embeds it directly in case 
 *
 * Arguments:
 * @param  id - Id of the poll
 */
function opinionstage_create_embed_code($id, $type) {
    
    // Only present if id is available 
    if (isset($id) && !empty($id)) {        		
		// Load embed code from the cache if possible
		$is_homepage = is_home();
		$transient_name = 'embed_code' . $id . '_' . $type . '_' . ($is_homepage ? "1" : "0");
		$code = get_transient($transient_name);
		if ( false === $code || '' === $code ) {
			if ($type == 'set') {
				$embed_code_url = "http://".OPINIONSTAGE_SERVER_BASE."/api/sets/" . $id . "/embed_code.json";			
			} else {
				$embed_code_url = "http://".OPINIONSTAGE_SERVER_BASE."/api/debates/" . $id . "/embed_code.json";
			}
			
			if ($is_homepage) {
				$embed_code_url .= "?h=1";
			}
		
			extract(opinionstage_get_contents($embed_code_url));
			$data = json_decode($raw_data);
			if ($success) {
				$code = $data->{'code'};			
				// Set the embed code to be cached for an hour
				set_transient($transient_name, $code, 3600);
			}
		}
    }
	return $code;
}

function opinionstage_create_placement_embed_code($id) {
    
    // Only present if id is available 
    if (isset($id) && !empty($id)) {        		
		// Load embed code from the cache if possible
		$is_homepage = is_home();
		$transient_name = 'embed_code' . $id . '_' . 'placement';
		$code = get_transient($transient_name);
		if ( false === $code || '' === $code ) {
			$embed_code_url = "http://".OPINIONSTAGE_SERVER_BASE."/api/containers/" . $id . "/embed_code.json";					
			extract(opinionstage_get_contents($embed_code_url));
			$data = json_decode($raw_data);
			if ($success) {
				$code = $data->{'code'};			
				// Set the embed code to be cached for an hour
				set_transient($transient_name, $code, 3600);
			}
		}
    }
	return $code;
}
/**
 * Utility function to create a link with the correct host and all the required information.
 */
function opinionstage_create_link($caption, $page, $params = "", $options = array()) {
	$style = empty($options['style']) ? '' : $options['style'];
	$new_page = empty($options['new_page']) ? true : $options['new_page'];
	$params_prefix = empty($params) ? "" : "&";	
	$link = "http://".OPINIONSTAGE_SERVER_BASE."/".$page."?" . "o=".OPINIONSTAGE_WIDGET_API_KEY.$params_prefix.$params;
	return "<a href=\"".$link."\"".($new_page ? " target='_blank'" : "")." style=".$style.">".$caption."</a>";
}

/**
 * CSS file loading
 */
function opinionstage_add_stylesheet() {
	// Respects SSL, Style.css is relative to the current file
	wp_register_style( 'opinionstage-style', plugins_url('style.css', __FILE__) );
	wp_enqueue_style( 'opinionstage-style' );
}

/**
 * Adds the poll button to the edit bar for new/edited post/page In TinyMCE >= WordPress 2.5
 */
function opinionstage_poll_tinymce_addbuttons() {
	if(!current_user_can('edit_posts') && ! current_user_can('edit_pages')) {
		return;
	}
	if(get_user_option('rich_editing') == 'true') {
		add_filter("mce_external_plugins", "opinionstage_poll_tinymce_addplugin");
		add_filter('mce_buttons', 'opinionstage_poll_tinymce_registerbutton');
	}
}
function opinionstage_poll_tinymce_registerbutton($buttons) {
	array_push($buttons, 'separator', 'ospolls');
	return $buttons;
}

function opinionstage_poll_tinymce_addplugin($plugin_array) {
	$plugin_array['ospolls'] = plugins_url(OPINIONSTAGE_WIDGET_UNIQUE_ID.'/tinymce/plugins/polls/editor_plugin.js');
	return $plugin_array;
}
function opinionstage_flyout_edit_url() {
	$os_options = (array) get_option(OPINIONSTAGE_OPTIONS_KEY);
	return 'http://'.OPINIONSTAGE_SERVER_BASE.'/containers/'.$os_options['fly_id'].'/edit?token='.$os_options['token'];
}
function opinionstage_article_placement_edit_url() {
	$os_options = (array) get_option(OPINIONSTAGE_OPTIONS_KEY);
	return 'http://'.OPINIONSTAGE_SERVER_BASE.'/containers/'.$os_options['article_placement_id'].'/edit?token='.$os_options['token'];
}
function opinionstage_create_poll_link() {
	$os_options = (array) get_option(OPINIONSTAGE_OPTIONS_KEY);
	if (empty($os_options["uid"])) {
		return opinionstage_create_link('Create a Poll', 'new_poll', '');
	} else {
		return opinionstage_create_link('Create a Poll', 'new_poll', 'token='.$os_options['token']);
	}	
}
function opinionstage_create_set_link() {
	$os_options = (array) get_option(OPINIONSTAGE_OPTIONS_KEY);
	if (empty($os_options["uid"])) {
		return opinionstage_create_link('Create a Set', 'sets/new', '');
	} else {
		return opinionstage_create_link('Create a Set', 'sets/new', 'token='.$os_options['token']);
	}	
}
function opinionstage_dashboard_link($text, $tab) {
	$os_options = (array) get_option(OPINIONSTAGE_OPTIONS_KEY);
	if (empty($os_options["uid"])) {
		return opinionstage_create_link($text, 'dashboard', 'tab='.$tab);
	} else {
		return opinionstage_create_link($text, 'dashboard', 'tab='.$tab.'&token='.$os_options['token']);
	}	
}
function opinionstage_logged_in_link($text, $link) {
	return opinionstage_create_link($text, 'registrations/new', 'return_to='.$link);
}


/**
 * Perform an HTTP GET Call to retrieve the data for the required content.
 * 
 * Arguments:
 * @param $url
 * @return array - raw_data and a success flag
 */
function opinionstage_get_contents($url) {
    $response = wp_remote_get($url, array('header' => array('Accept' => 'application/json; charset=utf-8')));

    return opinionstage_parse_response($response);
}

/**
 * Parse the HTTP response and return the data and if was successful or not.
 */
function opinionstage_parse_response($response) {
    $success = false;
    $raw_data = "Unknown error";
    
    if (is_wp_error($response)) {
        $raw_data = $response->get_error_message();
    
    } elseif (!empty($response['response'])) {
        if ($response['response']['code'] != 200) {
            $raw_data = $response['response']['message'];
        } else {
            $success = true;
            $raw_data = $response['body'];
        }
    }
    
    return compact('raw_data', 'success');
}
/**
 * Take the received data and parse it
 * 
 * Returns the newly updated widgets parameters.
*/
function opinionstage_parse_client_data($raw_data) {
	$os_options = array('uid' => $raw_data['uid'], 
						   'email' => $raw_data['email'],
						   'fly_id' => $raw_data['fly_id'],
						   'article_placement_id' => $raw_data['article_placement_id'],
						   'sidebar_placement_id' => $raw_data['sidebar_placement_id'],
						   'version' => OPINIONSTAGE_WIDGET_VERSION,
						   'fly_out_active' => 'false',
						   'article_placement_active' => 'false',
						   'token' => $raw_data['token']);
							   
	update_option(OPINIONSTAGE_OPTIONS_KEY, $os_options);
}

?>