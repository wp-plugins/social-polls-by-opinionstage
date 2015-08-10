<?php
/* --- Wordpress Hooks Implementations --- */

/**
 * Initialize the plugin 
 */
function opinionstage_init() {
	opinionstage_initialize_data();	
	register_uninstall_hook(OPINIONSTAGE_WIDGET_UNIQUE_LOCATION, 'opinionstage_uninstall');	
}

/**
 * Initialiaze the data options
 */
function opinionstage_initialize_data() {
	$os_options = (array) get_option(OPINIONSTAGE_OPTIONS_KEY);	
	$os_options['version'] = OPINIONSTAGE_WIDGET_VERSION;	
	
	update_option(OPINIONSTAGE_OPTIONS_KEY, $os_options);
}

/**
 * Remove the plugin data
 */
function opinionstage_uninstall() {
    delete_option(OPINIONSTAGE_OPTIONS_KEY);
}

/**
 * Adds the poll button to the html edit bar for new/edited post/page
 */
function opinionstage_poll_footer_admin() {
	echo '<script type="text/javascript">'."\n";
	echo '/* <![CDATA[ */'."\n";
	echo "\t".'var opsPollBtn = {'."\n";
	echo "\t\t".'poll: "'.esc_js(__('poll', OPINIONSTAGE_WIDGET_UNIQUE_ID)).'",'."\n";
	echo "\t\t".'insert_poll: "'.esc_js(__('Insert poll', OPINIONSTAGE_WIDGET_UNIQUE_ID)).'"'."\n";
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
		add_menu_page(__(OPINIONSTAGE_WIDGET_MENU_NAME, OPINIONSTAGE_WIDGET_UNIQUE_ID), __(OPINIONSTAGE_WIDGET_MENU_NAME, OPINIONSTAGE_WIDGET_MENU_NAME), 'edit_posts', OPINIONSTAGE_WIDGET_UNIQUE_LOCATION, 'opinionstage_add_poll_page', 
			plugins_url(OPINIONSTAGE_WIDGET_UNIQUE_ID.'/images/os.png'), '25.234323221');
		add_submenu_page(null, __('', OPINIONSTAGE_WIDGET_MENU_NAME), __('', OPINIONSTAGE_WIDGET_MENU_NAME), 'edit_posts', OPINIONSTAGE_WIDGET_UNIQUE_ID.'/opinionstage-callback.php');
	}
}

/**
 * Instructions page for adding a poll 
 */
function opinionstage_add_poll_page() {
  opinionstage_add_stylesheet();
  $os_options = (array) get_option(OPINIONSTAGE_OPTIONS_KEY);
  if (empty($os_options["uid"])) {
	$first_time = true;	
  } else {
	$first_time = false;
  }
  ?>
    	<script type='text/javascript'>
			jQuery(function ($) {
                var callbackURL = function() {
					return "<?php echo $url = get_admin_url('', '', 'admin') . 'admin.php?page='.OPINIONSTAGE_WIDGET_UNIQUE_ID.'/opinionstage-callback.php' ?>";
				};
				var handleWatermark = function(input){
					if(input.val().trim() != "") {
						input.removeClass('os-watermark');						
					} else {
						input.val(input.data('watermark'));
						input.addClass('os-watermark');
					}
				};	
				var toggleSettingsAjax = function(currObject, action) {	
					$.post(ajaxurl, {action: action, activate: currObject.is(':checked')}, function(response) { });
				};

		        $('#start-login').click(function(){
					var emailInput = $('#os-email');
					var email = $(emailInput).val();
					if (email == emailInput.data('watermark')) {
						email = "";
					}
					var new_location = "http://" + "<?php echo OPINIONSTAGE_LOGIN_PATH.'?callback=' ?>" + encodeURIComponent(callbackURL()) + "&email=" + email; 
					window.location = new_location;
				});
				
				$('#switch-email').click(function(){
					var new_location = "http://" + "<?php echo OPINIONSTAGE_LOGIN_PATH.'?callback=' ?>" + encodeURIComponent(callbackURL()); 
					window.location = new_location;
				});
				
				$('#os-email').keypress(function(e){
					if (e.keyCode == 13) {
						$('#start-login').click();
					}
				});
									     				    				 
				$('input.watermark').focus(function(){
					var input = $(this);
					if (input.data('watermark') == input.val()) {
						input.val("");
						input.removeClass('os-watermark');
					}
				}).each(function(){
					handleWatermark($(this));
				}).blur(function(){
					handleWatermark($(this));
				});	

				$('#fly-out-switch').change(function(){
					toggleSettingsAjax($(this), "opinionstage_ajax_toggle_flyout");
				});

				$('#article-placement-switch').change(function(){
					toggleSettingsAjax($(this), "opinionstage_ajax_toggle_article_placement");
				});				
			});
			
		</script>  
  <div class="opinionstage-wrap">
	  <div id="opinionstage-head"></div>
	  <div class="section">	    
	    <?php if($first_time) {?>	    	
			<h2>Connect to Opinion Stage</h3>
			<p>Connect WordPress with Opinion Stage to enable all features</p>
	    	<input id="os-email" type="text" value="" class="watermark" data-watermark="Enter Your Email"/>
	    	<a href="javascript:void(0)" class="os-button" id="start-login">Connect</a>	    	    			
	    <?php } else { ?>
			<p>You are connected to Opinion Stage with the following email</p>
	    	<label class="checked" for="user-email"></label>
	    	<input id="os-email" type="text" disabled="disabled" value="<?php echo($os_options["email"]) ?>"/>
	    	<a href="javascript:void(0)" class="os-button" id="switch-email" >Switch Account</a>
	    <?php } ?>
	  </div>

	  <div class="section">
		  <h2>Content</h2>
		  <ul class="os_links_list">
			<li><?php echo opinionstage_create_poll_link(); ?></li>
			<li><?php echo opinionstage_dashboard_link('Manage Polls', 'polls'); ?></li>
			<li><?php echo opinionstage_create_set_link(); ?></li>
			<li><?php echo opinionstage_dashboard_link('Manage Sets', 'sets'); ?></li>						
		  </ul>
	  </div>
	  <div class="section">
		  <h2>Placements</h2>
			<div class="placement_wrapper">
				<div class='description'>
					<div class="text">
						Fly-out
					</div>
					<a href="http://blog.opinionstage.com/fly-out-placements/?o=wp35e8" class="question-link" target="_blank">(?)</a>
				</div>
				<div class="onoffswitch left <?php echo($first_time ? "disabled" : "")?>">
					<input type="checkbox" name="fly-out-switch" class="onoffswitch-checkbox" <?php echo($first_time ? "disabled" : "")?> id="fly-out-switch" <?php echo($os_options['fly_out_active'] == 'true' ? "checked" : "") ?>>
					  <label class="onoffswitch-label" for="fly-out-switch">
						<div class="onoffswitch-inner"></div>
						<div class="onoffswitch-switch"></div>
					</label>
				</div>							
				<?php if(!$first_time) {?>	    					
					<a href="<?php echo opinionstage_flyout_edit_url(); ?>" target="_blank">Configure</a>
			    <?php } ?>
			</div>
			<div class="placement_wrapper">
				<div class='description'>
					<div class="text">
						Article Section
					</div>					
					<a href="http://blog.opinionstage.com/poll-placements/?o=wp35e8" class="question-link" target="_blank">(?)</a>
				</div>	
				<div class="onoffswitch left <?php echo($first_time ? "disabled" : "")?>">
					<input type="checkbox" name="article-placement-switch" class="onoffswitch-checkbox" <?php echo($first_time ? "disabled" : "")?> id="article-placement-switch" <?php echo($os_options['article_placement_active'] == 'true' ? "checked" : "") ?>>
					  <label class="onoffswitch-label" for="article-placement-switch">
						<div class="onoffswitch-inner"></div>
						<div class="onoffswitch-switch"></div>
					</label>
				</div>							
				<?php if(!$first_time) {?>	    					
					<a href="<?php echo opinionstage_article_placement_edit_url(); ?>" target="_blank">Configure</a>
			    <?php } ?>
			</div>			
	  </div>
	  <div class="section">			
		  <h2>Monetization</h2>
		  <ul class="os_links_list">
			<li><?php echo opinionstage_logged_in_link('Contact us to monetize your traffic', "http://".OPINIONSTAGE_SERVER_BASE."/advanced-solutions"); ?></li>		 
		  </ul>	  
	  </div>  
	  <div class="section">			
		  <h2>Help</h2>
		  <ul class="os_links_list">			
			<li><a href="http://blog.opinionstage.com/wordpress-poll-how-to-add-polls-to-wordpress-sites/?o=wp35e8" target="_blank">How to use this plugin</a></li>					  
			<li><?php echo opinionstage_create_link('View Examples', 'showcase', ''); ?></li>
			<li><a href="https://opinionstage.zendesk.com/anonymous_requests/new" target="_blank">Contact Us</a></li>					  
		  </ul>	  
	  </div>  
  </div>
  <?php
}

/**
 * Load the js script
 */
function opinionstage_load_scripts() {
	wp_enqueue_script( 'ospolls', plugins_url(OPINIONSTAGE_WIDGET_UNIQUE_ID.'/opinionstage_plugin.js'), array( 'jquery', 'thickbox' ), '3' );
}

/**
 * The popup window in the post/page edit/new page
 */
function opinionstage_add_poll_popup() {
	?>
	<div id="opinionstage-insert-poll-form" style="display:none;">
      <div id="content">
		<h3><strong>Type:</strong></h3>
		<p>
			<select style="width: 100%; max-width: 300px; font-size: 20px; height: 40px; line-height: 40px;" id="opinionstage-type">
				<option value="poll">Insert a Poll</option>
				<option value="set">Insert a Set</option>
			</select>
		</p>
		<style type="text/css">
			.pollWrp p, .setWrp p { padding: 1px 0 !important; }
		</style>
		<script type="text/javascript">
			jQuery(function ($)
			{
				var $pollWrp = $(".pollWrp");
				var $setWrp = $(".setWrp");
				$("#opinionstage-type").change(function ()
				{
					var $this = $(this);
					var val = $this.val();
					if (val == "poll")
					{
						$setWrp.fadeOut(0, function ()
						{
							$pollWrp.fadeIn("fast");
						});
					}
					else if (val == "set")
					{
						$pollWrp.fadeOut(0, function ()
						{
							$setWrp.fadeIn("fast");
						});					
					}
				}).trigger("change");
			});
		</script>
		<div class="pollWrp" style="display: none;">
			<h3><strong>Enter Poll ID (e.g. 2195036):</strong></h3>
			<p><input type="text" name="poll-id" id="opinionstage-poll-id" value="" /></p>
			<p class="submit">
			  <input type="button" class="opinionstage-submit button-primary" value="Insert Poll" name="submit" />
			</p>
			<p><strong>Haven't created a poll yet?</strong></br></br>
				<?php echo opinionstage_create_poll_link(); ?>
			</p>
			<p><strong>Don't know the poll ID?</strong></br></br>
				<?php echo opinionstage_dashboard_link('Locate ID of an existing poll', 'polls'); ?>				
			</p>
		</div>
		<div class="setWrp" style="display: none;">
			<h3><strong>Enter Set ID (e.g. 2152089):</strong></h3>
			<p><input type="text" name="set-id" id="opinionstage-set-id" value="" /></p>
			<p class="submit">
			  <input type="button" class="opinionstage-submit button-primary" value="Insert Set" name="submit" />
			</p>
			<p><strong>Haven't created a set yet?</strong></br></br>
				<?php echo opinionstage_create_set_link(); ?>
			</p>
			<p><strong>Don't know the set ID?</strong></br></br>
				<?php echo opinionstage_dashboard_link('Locate ID of an existing set', 'sets'); ?>				
			</p>
		</div>
	  </div>
	</div>  
	<?php
}

/**
 * Add the flyout embed code to the page header
 */
function opinionstage_add_flyout() {
	$os_options = (array) get_option(OPINIONSTAGE_OPTIONS_KEY);
	
	if (!empty($os_options['fly_id']) && $os_options['fly_out_active'] == 'true' && !is_admin() ) {
		// Will be added to the head of the page
		?>
		 <script type="text/javascript">//<![CDATA[
			window.AutoEngageSettings = {
			  id : '<?php echo $os_options['fly_id']; ?>'
			};
			(function(d, s, id){
			var js,
				fjs = d.getElementsByTagName(s)[0],
				p = (('https:' == d.location.protocol) ? 'https://' : 'http://'),
				r = Math.floor(new Date().getTime() / 1000000);
			if (d.getElementById(id)) {return;}
			js = d.createElement(s); js.id = id; js.async=1;
			js.src = p + '<?php echo OPINIONSTAGE_SERVER_BASE; ?>' + '/assets/autoengage.js?' + r;
			fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'os-jssdk'));
			
		//]]></script>
		
		<?php
	}
}

?>