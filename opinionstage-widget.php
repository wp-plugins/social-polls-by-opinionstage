<?php
	class OpinionStageWidget extends WP_Widget {
		function OpinionStageWidget() {
			$widget_ops = array('classname' => 'opinionstage_widget', 'description' => __('Adds a highly engaging social polling system to your widget section.', OPINIONSTAGE_WIDGET_UNIQUE_ID));
			$this->WP_Widget('opinionstage_widget', __('Opinion Stage Poll', OPINIONSTAGE_WIDGET_UNIQUE_ID), $widget_ops);
		}

		function widget($args, $instance) {
			extract($args);
			echo $before_widget;
			$title   = @$instance['title'];
			$poll_id = @$instance['poll_id'];
			$display = @$instance['display'];
			if (!empty($title)) echo $before_title . apply_filters('widget_title', $title) . $after_title;
			if (!empty($poll_id) && $display == 1) echo do_shortcode('[' . OPINIONSTAGE_WIDGET_SHORTCODE . ' id="' . $poll_id . '"]');
			echo $after_widget;
		}

		function update($new_instance, $old_instance) {
			$instance = $old_instance;
			$instance['title']   = strip_tags($new_instance['title']);
			$instance['poll_id'] = strip_tags($new_instance['poll_id']);
			$instance['display'] = strip_tags($new_instance['display']);
			return $instance;
		}

		function form($instance) {
			$title   = isset($instance['title'])   ? esc_attr($instance['title'])   : '';
			$poll_id = isset($instance['poll_id']) ? esc_attr($instance['poll_id']) : '';
			$display = isset($instance['display']) ? esc_attr($instance['display']) : 1;
			?>
				<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', OPINIONSTAGE_WIDGET_UNIQUE_ID); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
				
				<p><label for="<?php echo $this->get_field_id('display'); ?>"><?php _e('Display:', OPINIONSTAGE_WIDGET_UNIQUE_ID); ?></label>
				<select class="widefat" name="<?php echo $this->get_field_name('display'); ?>" id="<?php echo $this->get_field_id('display'); ?>"><option value="1" <?php selected($display, 1) ?>>Display Poll</option><option value="0" <?php selected($display, 0) ?>>Do not display anything (Disable)</option></select></p>				
				
				<p><label for="<?php echo $this->get_field_id('poll_id'); ?>"><?php _e('Poll ID:', OPINIONSTAGE_WIDGET_UNIQUE_ID); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('poll_id'); ?>" name="<?php echo $this->get_field_name('poll_id'); ?>" type="text" value="<?php echo $poll_id; ?>" /></p>
				<p><?php echo opinionstage_create_link('Manage my polls', 'dashboard', ''); ?></p>
			<?php
		}
	}

	function opinionstage_init_widget() {
		register_widget('OpinionStageWidget');
	}

	add_action('widgets_init', 'opinionstage_init_widget');
?>