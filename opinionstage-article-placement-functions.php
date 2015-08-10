<?php
	class OpinionStageArticlePlacement {
		static function initialize() {
			add_filter($hook = 'the_content', array(__CLASS__, $hook));
		}
								
		static function the_content($content) {
			global $post;
			$type = $post->post_type;
			if($type == "post") {
  
			}
			$os_options = (array) get_option(OPINIONSTAGE_OPTIONS_KEY);
			if (!empty($os_options['article_placement_id']) && $os_options['article_placement_active'] == 'true' && !is_admin() ) {
				$shortcode = do_shortcode(
					sprintf(
						'[osplacement id="%s"]', 
						$os_options['article_placement_id']
					)
				);
				return $content . $shortcode;
			}
			return $content;
		}	
		
	}
	return OpinionStageArticlePlacement::initialize();
?>