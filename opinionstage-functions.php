<?php
/* --- Wordpress Hooks Implementations --- */

/**
 * Main function for creating the widget html representation.
 * Transforms the shorcode parameters to the desired iframe call.
 *
 * Syntax as follows:
 * shortcode name - OPINIONSTAGE_WIDGET_SHORTCODE
 *
 * Arguments:
 * @param  id - Id of the poll
 *
 */
function opinionstage_add_poll($atts) {
	extract(shortcode_atts(array('id' => 0), $atts));
	if(!is_feed()) {
		$id = intval($id);
		return opinionstage_create_embed_code($id);
	} else {
		return __('Note: There is a poll embedded within this post, please visit the site to participate in this post\'s poll.', OPINIONSTAGE_WIDGET_UNIQUE_ID);
	}
}

/**
 * Create the The iframe HTML Tag according to the given paramters.
 * Either get the embed code or embeds it directly in case 
 *
 * Arguments:
 * @param  id - Id of the poll
 */
function opinionstage_create_embed_code($id) {
    
    // Only present if id is available 
    if (isset($id) && !empty($id)) {        		
		// Load embed code from the cache if possible
		if ( false === ( $code = get_transient( 'embed_code' . $id) ) ) {
			extract(opinionstage_get_contents("http://".OPINIONSTAGE_SERVER_BASE."/api/debates/" . $id . "/embed_code.json"));
			$data = json_decode($raw_data);
			if ($success) {
				$code = $data->{'code'};			
				// Set the embed code to be cached for an hour
				set_transient( 'embed_code' . $id, $code, 3600);
			}
		}
    }
	return $code;
}

/**
 * Perform an HTTP GET Call to retrieve the data for the required content.
 * 
 * Arguments:
 * @param $url
 * @return array - raw_data and a success flag
 */
function opinionstage_get_contents($url) {
    $response = wp_remote_get($url, array('header' => array('Accept' => 'application/json; charset=utf-8'),
                                          'timeout' => 10));

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
 * Adds the poll button to the edit bar for new/edited post/page
 */
function opinionstage_poll_footer_admin() {
	// Javascript Code Courtesy Of WP-AddQuicktag (http://bueltge.de/wp-addquicktags-de-plugin/120/)
	echo '<script type="text/javascript">'."\n";
	echo '/* <![CDATA[ */'."\n";
	echo "\t".'var opsPollBtn = {'."\n";
	echo "\t\t".'enter_poll_id: "'.esc_js(__('Enter Poll ID', OPINIONSTAGE_WIDGET_UNIQUE_ID)).'",'."\n";
	echo "\t\t".'enter_poll_id_again: "'.esc_js(__('Error: Poll ID must be numeric', OPINIONSTAGE_WIDGET_UNIQUE_ID)).'\n\n'.esc_js(__('Please enter Poll ID again', OPINIONSTAGE_WIDGET_UNIQUE_ID)).'",'."\n";
	echo "\t\t".'poll: "'.esc_js(__('social poll', OPINIONSTAGE_WIDGET_UNIQUE_ID)).'",'."\n";
	echo "\t\t".'insert_poll: "'.esc_js(__('Insert social poll', OPINIONSTAGE_WIDGET_UNIQUE_ID)).'"'."\n";
	echo "\t".'};'."\n";
	echo "\t".'function insertOSPoll(where, myField) {'."\n";
	echo "\t\t".'var poll_id = jQuery.trim(prompt(opsPollBtn.enter_poll_id));'."\n";
	echo "\t\t".'while(isNaN(poll_id)) {'."\n";
	echo "\t\t\t".'poll_id = jQuery.trim(prompt(opsPollBtn.enter_poll_id_again));'."\n";
	echo "\t\t".'}'."\n";
	echo "\t\t".'if (poll_id >= -1 && poll_id != null && poll_id != "") {'."\n";
	echo "\t\t\t".'if(where == \'code\') {'."\n";
	echo "\t\t\t\t".'edInsertContent(myField, \'['.OPINIONSTAGE_WIDGET_SHORTCODE.' id="\' + poll_id + \'"]\');'."\n";
	echo "\t\t\t".'} else {'."\n";
	echo "\t\t\t\t".'return \'['.OPINIONSTAGE_WIDGET_SHORTCODE.' id="\' + poll_id + \'"]\';'."\n";
	echo "\t\t\t".'}'."\n";
	echo "\t\t".'}'."\n";
	echo "\t".'}'."\n";
	echo "\t".'if(document.getElementById("ed_toolbar")){'."\n";
	echo "\t\t".'edButtons[edButtons.length] = new edButton("ed_o_poll",opsPollBtn.poll, "", "","");'."\n";
	echo "\t\t".'jQuery(document).ready(function($){'."\n";
	echo "\t\t\t".'$(\'#qt_content_ed_o_poll\').replaceWith(\'<input type="button" id="qt_content_ed_o_poll" accesskey="" class="ed_button" onclick="insertOSPoll(\\\'code\\\', edCanvas);" value="\' + opsPollBtn.poll + \'" title="\' + opsPollBtn.insert_poll + \'" />\');'."\n";
	echo "\t\t".'});'."\n";
	echo "\t".'}'."\n";
	echo '/* ]]> */'."\n";
	echo '</script>'."\n";
}

/**
 * Adds the poll button to the edit bar for new/edited post/page 
 */
function opinionstage_poll_menu() {
	if (function_exists('add_menu_page')) {
		add_menu_page(__('Add Polls', OPINIONSTAGE_WIDGET_UNIQUE_ID), __('Social Polls', OPINIONSTAGE_WIDGET_UNIQUE_ID), 'edit_posts', OPINIONSTAGE_WIDGET_UNIQUE_LOCATION, 'opinionstage_add_poll_page', 
      plugins_url(OPINIONSTAGE_WIDGET_UNIQUE_ID.'/images/os-small.png'));
	}
}

/**
 * Instructions for adding a poll 
 */
function opinionstage_add_poll_page() {
  ?>
  <h1><strong>Opinion Stage Social Polls</strong></h1>
  <BR>
  <h3><strong>To add a social poll to your post/page:</strong></h3>
  <p>1) &nbsp; Start a new poll using the <?php echo opinionstage_create_link('start a poll / debate', 'new_debate', ''); ?> form on OpinionStage.com</p>
  <p>2) &nbsp; Copy the embed ID (located near the Embed button on the poll / debate page)</p>
  <p>3) &nbsp; From the WordPress post/page text editor, click on the social poll icon to open the embed dialog</p>
  <p>4) &nbsp; Paste the ID into the embed dialog</p> 
  <BR>
  Need more information? <?php echo opinionstage_create_link('click here', 'publishers/wordpress', ''); ?>. For support or feedback, please email us at:&nbsp;<a href="mailto:info@opinionstage.com">info@opinionstage.com</a>  
  <?php
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

/**
 * Utility function to create a link with the correct host and all the required information.
 */
function opinionstage_create_link($caption, $page, $params = "", $options = array()) {
	$style = empty($options['style']) ? '' : $options['style'];
	$new_page = empty($options['new_page']) ? true : $options['new_page'];
	
	$params_prefix = empty($params) ? "" : "&";
	
	$link = "http://".OPINIONSTAGE_SERVER_BASE."/".$page."?ref=".OPINIONSTAGE_WIDGET_API_KEY.$params_prefix.$params;
	
	return "<a href=\"".$link."\"".($new_page ? " target='_blank'" : "")." style=".$style.">".$caption."</a>";
}

?>