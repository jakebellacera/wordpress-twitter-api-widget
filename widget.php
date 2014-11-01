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

    $twitter_username = $this->get_form_field_value($instance, 'twitter_username');

    // Get the feed
    $feed = $this->fetch_tweets($instance);

    ?>

    <ul class="tweets-listing">
      <?php if (count($feed['timeline']) > 0) : ?>
        <?php foreach($feed['timeline'] as $tweet) : ?>
          <li class="tweets-listing-item">
            <?php echo $this->tweet_html($instance, $tweet); ?>
          </li>
        <?php endforeach; ?>
      <?php else: ?>
        <li class="tweets-listing-item tweets-listing-item-none">This user has not posted any tweets yet.</li>
      <?php endif; ?>
    </ul>

    <a href="<?php echo $this->get_twitter_profile_url($twitter_username); ?>" class="tweets-listing-profile-link" title="Follow @<?php echo $twitter_username; ?>" data-twitter-username="<?php echo $twitter_username; ?>">Follow @<?php echo $twitter_username; ?></a>

    <?php

		echo $args['after_widget'];
	}

  /**
   * Renders a tweet into HTML.
   *
   * @see TwitterAPIWidget::parse_timeline
   *
   * @param array $instance Saved values from database.
   * @param array $tweet    A Tweet from a parsed Timeline.
   */
  private function tweet_html($instance, $tweet) {
    $template = $this->get_form_field_value($instance, 'tweet_template');

    // Replace variables
    $html = $template;
    $html = preg_replace_callback("(\{\{posted_date(\|format:\"(.*)?\")?\}\})", function ($matches) use ($tweet) {
      if (isset($matches[2])) {
        $format = $matches[2];
      } else {
        $format = '%m/%d/%Y';
      }
      return strftime($format, $tweet['posted_date']);
    }, $html);
    $html = str_replace('{{permalink}}', $tweet['permalink'], $html);
    $html = str_replace('{{posted_date_ago}}', $tweet['posted_date_ago'], $html);
    $html = str_replace('{{username}}', $tweet['username'], $html);
    $html = str_replace('{{profile_url}}', $tweet['profile_url'], $html);
    $html = str_replace('{{body}}', $tweet['body'], $html);
    return $html;
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
   * Fetches tweets and returns the timeline.
   *
   * @param array $instance Previously saved widget values from the database.
   */
  private function fetch_tweets($instance) {
    $username = $this->get_form_field_value($instance, 'twitter_username');
    $amount = $this->get_form_field_value($instance, 'number_tweets');

    // Before we do anything, check the cache for a recently fetched twitter feed.
    // We watch to bust the cache if it's been greater than 15 minutes
    if (get_option('twitter_api_widget_feed')) {
      $old_feed = get_option('twitter_api_widget_feed');
      if (time() - $old_feed['fetched_at'] < (60 * 15)) {
        return $old_feed;
      }
    }

    // Since we need to fetch a new feed from the Twitter API, we should use the
    // Bearer Token approach to keep rate limits independent to this application.
    //
    // We'll check the cache for the token first before fetching a new one from Twitter.
    if (get_option('twitter_api_widget_bearer_token')) {
      $bearer_token = get_option('twitter_api_widget_bearer_token');
      $cb = \Codebird\Codebird::getInstance();
    } else {
      \Codebird\Codebird::setConsumerKey($this->get_form_field_value($instance, 'consumer_key'), $this->get_form_field_value($instance, 'consumer_secret'));
      $cb = \Codebird\Codebird::getInstance();
      $reply = $cb->oauth2_token();
      $bearer_token = $reply->access_token;
      add_option('twitter_api_widget_bearer_token', $bearer_token); // Store it
    }

    \Codebird\Codebird::setBearerToken($bearer_token);

    // Now fetch the tweets
    $timeline = $this->fetch_user_timeline($cb, $username);
    $parsed_timeline = $this->parse_timeline($timeline, $amount);
    $feed = array(
      'fetched_at' => time(),
      'timeline' => $parsed_timeline
    );

    // Update the cache
    if (isset($old_feed)) {
      update_option('twitter_api_widget_feed', $feed);
    } else {
      add_option('twitter_api_widget_feed', $feed);
    }

    return $feed;
  }

  /**
   * Fetches the user timeline from the Twitter API via CodeBird. Limiting the number of tweets is done after the
   *
   * @see https://github.com/jublonet/codebird-php for CodeBird documentation.
   * @see https://dev.twitter.com/rest/reference/get/statuses/user_timeline for API documentation.
   *
   * @param class  $cb       An instantiated CodeBird object.
   * @param string $username The username for the user that you'd like to fetch the timeline from.
   */
  // feed has been fetched, as Twitter does not provide a way to accomplish this.
  private function fetch_user_timeline($cb, $username) {
    $api = 'statuses/userTimeline';
    $params['screen_name'] = $username;
    // It's best to fetch the maximum number of tweets possible because Twitter
    // doesn't filter the results until after the tweets have been queried.
    $params['count'] = 200;
    $params['include_rts'] = '0';
    $params['exclude_replies'] = '1';

    return (array) $cb->$api($params, true);
  }

  /**
   * Parses a "raw" Twitter timeline from Twitter's API.
   *
   * @see https://dev.twitter.com/rest/reference/get/statuses/user_timeline for an example of the response
   *
   * @param array   $timeline The Timeline array from a Twitter API response.
   * @param integer $amount   The amount of tweets that should be displayed.
   */
  private function parse_timeline($timeline, $amount) {
    $feed = array();
    $count = intval($amount);

    foreach ($timeline as $tweet) {
      if ($i == $count) break;

      if ($tweet->created_at) {
        $post = array();
        $datetime = new DateTime($tweet->created_at);
        $timestamp = $datetime->format('U');
        $post['permalink'] = $this->get_tweet_permalink($tweet->user->screen_name, $tweet->id);
        $post['posted_date'] = $timestamp;
        $post['posted_date_ago'] = $this->relative_date($timestamp);
        $post['body'] = $this->twitterify($tweet->text);
        $post['username'] = $tweet->user->screen_name;
        $post['profile_url'] = $this->get_twitter_profile_url($tweet->user->screen_name);

        array_push($feed, $post);
        $i++;
      }
    }

    return $feed;
  }

  /**
   * Returns a string with twitter links highlighted.
   *
   * @see http://www.snipe.net/2009/09/php-twitter-clickable-links/
   *
   * @param string $text The text with the links that should be highlighted.
   */
  private function twitterify($text) {
    $text = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $text);
    $text = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $text);
    $text = preg_replace("/@(\w+)/", "<a href=\"https://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>", $text);
    $text = preg_replace("/#(\w+)/", "<a href=\"https://twitter.com/search?q=%23\\1\" target=\"_blank\">#\\1</a>", $text);
    return $text;
  }

  /**
   * Pretty-prints a Twitter user's profile URL.
   *
   * @param string $username The user's username.
   */
  private function get_twitter_profile_url($username) {
    return "https://twitter.com/$username";
  }

  /**
   * Returns a Twitter status' (aka "Tweet") permalink URL.
   *
   * @param string $username The tweet's author.
   * @param string $id       The ID of the tweet.
   */
  private function get_tweet_permalink($username, $id) {
    return "https://twitter.com/$username/status/$id";
  }

  /**
   * Returns a relative date string from a Date object.
   *
   * @param string Date
   */
  private function relative_date($date) {
    $now = time();
    $diff = $now - strtotime($date);

    if ($diff < 60){
        return sprintf($diff > 1 ? '%s seconds ago' : 'a second ago', $diff);
    }

    $diff = floor($diff/60);

    if ($diff < 60){
        return sprintf($diff > 1 ? '%s minutes ago' : 'one minute ago', $diff);
    }

    $diff = floor($diff/60);

    if ($diff < 24){
        return sprintf($diff > 1 ? '%s hours ago' : 'an hour ago', $diff);
    }

    $diff = floor($diff/24);

    if ($diff < 7){
        return sprintf($diff > 1 ? '%s days ago' : 'yesterday', $diff);
    }

    if ($diff < 30)
    {
        $diff = floor($diff / 7);

        return sprintf($diff > 1 ? '%s weeks ago' : 'one week ago', $diff);
    }

    $diff = floor($diff/30);

    if ($diff < 12){
        return sprintf($diff > 1 ? '%s months ago' : 'last month', $diff);
    }

    $diff = date('Y', $now) - date('Y', $date);

    return sprintf($diff > 1 ? '%s years ago' : 'last year', $diff);
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

  /**
   * Gets a specific field name's value.
   *
   * @param string $instance   Saved values from the database
   * @param string $field_name The name of the fields.
   */
  private function get_form_field_value( $instance, $field_name ) {

    if (isset($instance[$field_name])) {
      $value = $instance[$field_name];
    } else {
      $defaults = $this->get_default_fields();
      $value = $defaults[$field_name]['default_value'];
    }

    return $value;
  }

  /**
   * The default tweet template.
   */
  private function default_tweet_template() {
    ob_start(); ?>
<div class="tweet">
  <p class="tweet-body">{{body}}</p>
  <div class="tweet-metadata">
    <span class="tweet-metadata-item tweet-username">
      <a href="{{profile_url}}" class="tweet-profile-link" rel="external">@{{username}}</a>
    </span>
    <span class="tweet-metadata-item tweet-date">
      <a href="{{permalink}}" class="tweet-permalink" rel="external">Posted on {{posted_date}}</a>
    </span>
  </div>
</div><?php

    $html = ob_get_contents();
    ob_end_clean();

    return $html;
  }
}
