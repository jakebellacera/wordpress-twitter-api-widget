<?php

/**
 * Plugin Name: Twitter API Widget
 * Plugin URI: https://github.com/jakebellacera/wordpress-twitter-api-widget
 * Description: A simple widget for Wordpress that displays your Twitter feed through the Twitter API.
 * Version: 1.0.0
 * Author: Jake Bellacera
 * Author URI: http://jakebellacera.com/
 * License: MIT
 */

// Prevent access to the plugin
defined('ABSPATH') or die();

// Load codebird PHP Twitter library
require_once( join(DIRECTORY_SEPARATOR, array('lib', 'codebird-php', 'codebird.php')) );

// Widget code
require_once('widget.php');

// Register the widget
add_action('widgets_init',
     create_function('', 'return register_widget("TwitterAPIWidget");')
);
