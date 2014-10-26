<?php

// Prevent access to the plugin
defined('ABSPATH') or die();

class TwitterAPIWidget extends WP_Widget {
  protected $default_fields;

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {

    $this->default_fields = array(
      'basic' => array(
        'title' => array(
          'label' => 'Title',
          'default_value' => 'Twitter Feed',
          'type' => 'text',
          'description' => 'The title of the widget.',
          'required' => false,
        ),

        'consumer_key' => array(
          'label' => 'Consumer key',
          'default_value' => '',
          'type' => 'text',
          'description' => 'Your Twitter application\'s Consumer key.',
          'required' => true,
        ),

        'consumer_secret' => array(
          'label' => 'Consumer secret',
          'default_value' => '',
          'type' => 'text',
          'description' => 'Your Twitter application\'s Secret key.',
          'required' => true,
        ),

        'twitter_username' => array(
          'label' => 'Twitter username',
          'default_value' => '',
          'type' => 'text',
          'description' => 'The Twitter user\'s tweets you\'d like to fetch.',
          'required' => true,
        ),

        'number_tweets' => array(
          'label' => 'Number of tweets',
          'default_value' => '5',
          'type' => 'number',
          'maxlength' => '2',
          'description' => 'The number of tweets you\'d like to fetch.',
          'required' => false,
        ),
      ),

      'advanced' => array(
        'tweet_template' => array(
          'label' => 'Tweet template',
          'default_value' => $this->default_tweet_template(),
          'type' => 'textarea',
          'description' => 'The template that is used to display each tweet.',
          'required' => true,
        ),
      )
    );

		parent::__construct(
			'twitter_api_widget', // Base ID
			__('Twitter API', 'twitter_api_widget'), // Name
			array( 'description' => __( 'Displays your Twitter feed through the Twitter API.', 'twitter_api_widget' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
    echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'twitter_api_widget', $instance['title'] ). $args['after_title'];
		}
		echo __( 'Hello, World!', 'twitter_api_widget' );
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
    $all_fields = $this->get_default_fields();

    // Display all of the basic fields
    foreach ($this->get_form_field_values($instance, 'basic') as $name => $value) {
      $field_info = $all_fields[$name];
      echo $this->form_group_html($name, $value, $field_info);
    }

    // Display all of the advanced fields
    foreach ($this->get_form_field_values($instance, 'advanced') as $name => $value) {
      $field_info = $all_fields[$name];
      echo $this->form_group_html($name, $value, $field_info);
    }
	}

  /**
   * Outputs HTML for a form group in the Back-end widget form.
   *
   * @param string $name       The name of the field.
   * @param string $value      The value of the field.
   * @param array  $field_info The metadata of the form_field as defined by default_fields.
   */
  public function form_group_html($name, $value, $field_info) {
    ob_start();
    ?>
    <p>
      <label for="<?php echo $this->get_field_id($name) ?>"><?php _e($field_info['label']); ?>:</label>
      <?php echo $this->form_field_input_html($name, $value, $field_info); ?>
    </p>
    <?php
    $html = ob_get_contents();
    ob_end_clean();
    return $html;
  }

  /**
   * Outputs HTML for a form field in the Back-end widget form.
   *
   * @param string $name       The name of the field.
   * @param string $value      The value of the field.
   * @param array  $field_info The metadata of the form_field as defined by default_fields.
   */
  public function form_field_input_html($name, $value, $field_info) {
    switch($field_info['type']) {
      case "text":
        $html = '<input class="widefat" id="' . $this->get_field_id($name) . '" name="' . $this->get_field_name($name) . '" type="text" value="' . esc_attr($value) . '">';
        break;
      case "number":
        $html = '<input id="' . $this->get_field_id($name) . '" name="' . $this->get_field_name($name) . '" type="text" value="' . esc_attr($value) . '"'. (isset($field_info['maxlength']) ? ' maxlength="' . $field_info['maxlength'] . '" size="'. $field_info['maxlength'] .'"': '') .'>';
        break;
      case "textarea":
        $html = '<textarea class="widefat code" style="font-size: 12px;" rows="8" id="' . $this->get_field_id($name) . '" name="' . $this->get_field_name($name) . '">' . esc_attr($value) . '</textarea>';
        break;
    }

    return $html;
  }

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
    foreach ($this->get_form_field_values($new_instance) as $name => $value) {
  		$instance[$name] = $value;
    }

		return $instance;
	}

  /**
   * Gets a group of default fields.
   *
   * @param string $group The name of the group of fields. If this is omitted, then all groups will be returned.
   */
  private function get_default_fields($group = false) {
    if ($group != false) {
      $fields = $this->default_fields[$group];
    } else {
      $fields = array_merge($this->default_fields['basic'], $this->default_fields['advanced']);
    }
    return $fields;
  }

  /**
   * Gets the names of each of the fields in a group.
   *
   * @param string $group The name of the group of fields. If this is omitted, then all groups will be returned.
   */
  private function get_default_field_names($group = false) {
    $all_fields = $this->get_default_fields($group);
    return array_keys($all_fields);
  }

  /**
   * Gets all of the field names and their values.
   *
   * @param string $instance Saved values from the database
   * @param string $group    The name of the group of fields. If this is omitted, then all groups will be returned.
   */
  private function get_form_field_values( $instance, $group ) {
    $fields = array();

    foreach ($this->get_default_field_names($group) as $field_name) {
      $fields[$field_name] = $this->get_form_field_value($instance, $field_name);
    }

    return $fields;
  }

  private function get_form_field_value( $instance, $field_name ) {

    if (isset($instance[$field_name])) {
      $value = $instance[$field_name];
    } else {
      $defaults = $this->get_default_fields();
      $value = $defaults[$field_name]['default_value'];
    }

    return $value;
  }

  private function default_tweet_template() {
    ob_start(); ?>
<div class="tweet">
  <p class="tweet-body">{{body}}</p>
  <div class="tweet-metadata">
    <span class="tweet-metadata-item tweet-username">
      <a href="{{profile_url}}" class="tweet-profile-link" rel="external">{{username}}</a>
    </span>
    <span class="tweet-metadata-item tweet-date">
      <a href="{{permalink}}" class="tweet-permalink" rel="external">{{posted_date_ago}}</a>
    </span>
  </div>
</div><?php

    $html = ob_get_contents();
    ob_end_clean();

    return $html;
  }
}
