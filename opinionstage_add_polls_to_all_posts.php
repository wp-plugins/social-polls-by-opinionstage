<?php
	class AddPollsToAllPosts {
		static $identifier = 'addpollstoallposts';
		
		static function bootstrap() {
			add_action($hook = 'admin_menu', array(__CLASS__, $hook));
			add_filter($hook = 'the_content', array(__CLASS__, $hook));
		}
		
		static function admin_menu() {
			$page_callback = array(__CLASS__, 'render_page');
			$url = self::register_admin_page($page_callback);
		}
		
		static function the_content($content) {
			global $post;
			$opinionstage_aptap = get_option('opinionstage_aptap');
			if (is_array($opinionstage_aptap)) {
				if (isset($opinionstage_aptap['post_types']) && !empty($opinionstage_aptap['post_types']) && is_array($opinionstage_aptap['post_types'])) {
					if (in_array($post->post_type, $opinionstage_aptap['post_types'])) {
						$shortcode = do_shortcode(
							sprintf(
								'[socialpoll id="%s" type="%s"]', 
								$opinionstage_aptap['configure_id'], 
								$opinionstage_aptap['content_types']
							)
						);
						return $content . $shortcode;
					}
				}
			}
			
			return $content;
		}
		
		static function register_admin_page($callback) {
			$parent_slug = 'admin.php';
			$hookname = get_plugin_page_hookname(AddPollsToAllPosts::$identifier, $parent_slug);
			add_filter($hookname, $callback);
			$GLOBALS['_registered_pages'][$hookname] = true;
			$url = admin_url($parent_slug . '?page=' . $identifier);
			return $url;
		}
		
		static function render_page() {
			if (isset($_POST['opinionstage_aptap']) && is_array($_POST['opinionstage_aptap'])) {
				$opinionstage_aptap = $_POST['opinionstage_aptap'];
				update_option('opinionstage_aptap', $opinionstage_aptap);
				?><div class="updated"><p><strong>Options saved.</strong></p></div><?php
			}
			opinionstage_add_stylesheet();
			$post_types = get_post_types(array('public' => true));
			function os_get_option($options, $option_name) {
				if (is_array($options))
					return isset($options[$option_name]) && !empty($options[$option_name]) ? $options[$option_name] : '';
				return '';
			}
			$opinionstage_aptap = get_option('opinionstage_aptap');
			?>
			<div class="opinionstage-wrap">
				<div id="opinionstage-head"></div>
				<div class="section">
					<form action="" method="POST"
						<h2>Add polls to all posts</h2>
						<hr />
						<h3>Select post types</h3>
						<div class="chkboxs" style="background-color: #FFF; border: 1px solid #DDD; padding: 5px 20px 5px 10px; display: inline-block;">
							<?php foreach ($post_types as $pt) { ?>
								<?php
									$is_checked = false;
									if (is_array(os_get_option($opinionstage_aptap, 'post_types'))) {
										$post_types = os_get_option($opinionstage_aptap, 'post_types');
										if (in_array($pt, $post_types)) $is_checked = true;
									}
								?>
								<label for="pt-<?php _e($pt) ?>"><input type="checkbox" name="opinionstage_aptap[post_types][]" value="<?php _e($pt) ?>" id="pt-<?php _e($pt) ?>" <?php _e($is_checked ? 'checked="checked"' : '') ?> />&nbsp;&nbsp;&nbsp;<?php _e($pt) ?></label><br />
							<?php } ?>
						</div>
						<br />
						<h3><label for="pt-ct">Select content types</label></h3>
						<select name="opinionstage_aptap[content_types]" id="pt-ct">
							<option value="poll" <?php _e(os_get_option($opinionstage_aptap, 'content_types') == 'poll' ? 'selected="selected"' : '') ?>>Poll</option>
							<option value="container" <?php _e(os_get_option($opinionstage_aptap, 'content_types') == 'container' ? 'selected="selected"' : '') ?>>Container</option>
							<option value="set" <?php _e(os_get_option($opinionstage_aptap, 'content_types') == 'set' ? 'selected="selected"' : '') ?>>Set</option>
						</select>
						<br />
						<h3><label for="pt-cnfid">Configure ID</label></h3>
						<input type="text" value="<?php _e(os_get_option($opinionstage_aptap, 'configure_id')) ?>" name="opinionstage_aptap[configure_id]" id="pt-cnfid" />
						<div><a href="#" id="pt-locid" target="_blank">Locate ID in Dashboard</a></div>
						<br />
						<p class="submit"><input type="submit" class="button button-primary" value="Save Changes"></p>
						<br />
						<div style="background-color: #FFF; border: 1px solid #DDD; padding: 5px 20px 5px 10px; display: inline-block; max-width: 300px;">Note: If you would like to add a poll to only one post/page, click on the Opinion Stage icon from the create post/page visual editor.</div>
						<br />
						<p>Need more help? <a href="http://blog.opinionstage.com/wordpress-poll-how-to-add-polls-to-wordpress-sites/" target="_blank">Click here!</a></p>
					</form>
				</div>
			</div>
			<script type="text/javascript">
				jQuery(function ($)
				{
					$("#toplevel_page_social-polls-by-opinionstage-opinionstage-polls").addClass("current").find(" > a").addClass("current");
					$("#pt-ct").on("change", function ()
					{
						var $this = $(this),
							v = $this.val(),
							$locid = $("#pt-locid");
						var rootURL = "http://www.opinionstage.com/dashboard?o=wp35e8";
						if (v == "container") rootURL += "&tab=containers";
						if (v == "set") rootURL += "&tab=sets";
						$locid.attr("href", rootURL);
					}).trigger("change");
				});
			</script>
			<?php
		}
	}
	return AddPollsToAllPosts::bootstrap();
?>