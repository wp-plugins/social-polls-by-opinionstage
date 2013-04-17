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
 * Adds the poll button to the html edit bar for new/edited post/page
 */
function opinionstage_poll_footer_admin() {
	echo '<script type="text/javascript">'."\n";
	echo '/* <![CDATA[ */'."\n";
	echo "\t".'var opsPollBtn = {'."\n";
	echo "\t\t".'poll: "'.esc_js(__('social poll', OPINIONSTAGE_WIDGET_UNIQUE_ID)).'",'."\n";
	echo "\t\t".'insert_poll: "'.esc_js(__('Insert social poll', OPINIONSTAGE_WIDGET_UNIQUE_ID)).'"'."\n";
	echo "\t".'};'."\n";
	echo "\t".'if(document.getElementById("ed_toolbar")){'."\n";
	echo "\t\t".'edButtons[edButtons.length] = new edButton("ed_o_poll",opsPollBtn.poll, "", "","");'."\n";
	echo "\t\t".'jQuery(document).ready(function($){'."\n";
	echo "\t\t\t".'var popup_width = jQuery(window).width();'."\n";
	echo "\t\t\t".'var popup_height = jQuery(window).height();'."\n";
	echo "\t\t\t".'popup_width = ( 720 < popup_width ) ? 640 : popup_width - 80;'."\n";
	echo "\t\t\t".'$(\'#qt_content_ed_o_poll\').replaceWith(\'<input type="button" id="qt_content_ed_o_poll" accesskey="" class="ed_button" onclick="tb_show( \\\'Insert Poll\\\', \\\'#TB_inline?=&height=popup_height&width=popup_width&inlineId=opinionstage-insert-poll-form\\\' );" value="\' + opsPollBtn.poll + \'" title="\' + opsPollBtn.insert_poll + \'" />\');'."\n";
	echo "\t\t".'});'."\n";
	echo "\t".'}'."\n";
	echo '/* ]]> */'."\n";
	echo '</script>'."\n";
}

/**
 * Sidebar menu
 */
function opinionstage_poll_menu() {
	if (function_exists('add_menu_page')) {
		add_menu_page(__('Add Polls', OPINIONSTAGE_WIDGET_UNIQUE_ID), __('Social Polls', OPINIONSTAGE_WIDGET_UNIQUE_ID), 'edit_posts', OPINIONSTAGE_WIDGET_UNIQUE_LOCATION, 'opinionstage_add_poll_page', 
			plugins_url(OPINIONSTAGE_WIDGET_UNIQUE_ID.'/images/os.png'));
	}
}

/**
 * Instructions page for adding a poll 
 */
function opinionstage_add_poll_page() {
  ?>
  <h1><strong>Opinion Stage Social Polls</strong></h1>
  <h3><strong>To add a social poll to your post/page:</strong></h3>
  <p>1) &nbsp; <?php echo opinionstage_create_link('Start a new poll', 'new_poll', ''); ?> or locate a poll from <?php echo opinionstage_create_link('your dashboard', 'dashboard', ''); ?></p>
  <p>2) &nbsp; From the poll page, copy the embed ID (located near the embed button)</p>
  <p>3) &nbsp; From the WordPress post/page text editor, click on the social poll icon to open the insert poll dialog</p>
  <img src="http://a5.opinionstage-res.cloudinary.com/image/upload/c_fit,h_294,w_474/v1332371481/mw4b8djjlljrwjy2w3iqa.jpg" />
  <p>4) &nbsp; Paste the ID into the insert poll dialog</p>
  <span>Note: Instead of steps 3 & 4, you can add the following code directly into the post/page: [socialpoll id="xyz"] , where xyz is the poll id.</span>
  <br>
  <?php echo opinionstage_insturctions_html_suffix(); ?>
  <?php
}

/**
 * Load the js script
 */
function opinionstage_load_scripts() {
	wp_enqueue_script( 'ospolls', plugins_url(OPINIONSTAGE_WIDGET_UNIQUE_ID.'/opinionstage_plugin.js'), array( 'jquery', 'thickbox' ));
}

function mytheme_tinymce_config( $init ) {
	$valid_shortcode = OPINIONSTAGE_WIDGET_SHORTCODE;
	if ( isset( $init['extended_valid_elements'] ) ) {
		$init['extended_valid_elements'] .= ',' . $valid_shortcode;
	} else {
		$init['extended_valid_elements'] = $valid_shortcode;
	}
	return $init;
}

/**
 * The popup window in the post/page edit/new page
 */
function opinionstage_add_poll_popup() {
	?>
	<div id="opinionstage-insert-poll-form" style="display:none;">
      <div id="content">
		<h1><strong>Insert a Social Poll</strong></h1>
		<h3><strong>Enter Poll ID (e.g. 436):</strong></h3>
		<p><input type="text" name="poll-id" id="opinionstage-poll-id" value="" /></p>
		<p class="submit">
		  <input type="button" id="opinionstage-submit" class="button-primary" value="Insert Poll" name="submit" />
		</p>
		<br>
		<p><strong>Haven't created a poll yet? / Don't know the poll ID?</strong></p>
		<p>1) &nbsp; <?php echo opinionstage_create_link('Start a new poll', 'new_poll', ''); ?> or locate a poll from <?php echo opinionstage_create_link('your dashboard', 'dashboard', ''); ?></p>
		<p>2) &nbsp; From the poll page, copy the embed ID</p>
		<?php echo opinionstage_insturctions_html_suffix(); ?>
	  </div>
	</div>  
	<?php
}

function opinionstage_insturctions_html_suffix() {
	?>
	<br>
	Need more information? <?php echo opinionstage_create_link('click here', 'publishers/wordpress', ''); ?>. For support or feedback, please <?php echo opinionstage_create_link('contact us', 'contact_requests/new', ''); ?>  
	<?php
}
/**
 * Utility function to create a link with the correct host and all the required information.
 */
function opinionstage_create_link($caption, $page, $params = "", $options = array()) {
	$style = empty($options['style']) ? '' : $options['style'];
	$new_page = empty($options['new_page']) ? true : $options['new_page'];	
	$params_prefix = empty($params) ? "" : "&";	
	$link = "http://".OPINIONSTAGE_SERVER_BASE."/".$page."?o=".OPINIONSTAGE_WIDGET_API_KEY.$params_prefix.$params;
	
	return "<a href=\"".$link."\"".($new_page ? " target='_blank'" : "")." style=".$style.">".$caption."</a>";
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
?>