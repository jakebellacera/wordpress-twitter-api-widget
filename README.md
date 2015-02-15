# Twitter API Widget for Wordpress

This is a simple widget for Wordpress that displays your Twitter feed through the Twitter API. This plugin is intended for users that are familiar with CSS as it _will not_ be styled out of the box.

## Installation

1. Unzip and place the folder into your Wordpress install's `wp-content/plugins` directory.
2. In your Wordpress Admin panel, navigate to the **Plugins** section and enable the "Twitter API Widget."
3. In your Wordpress Admin panel, navigate to **Appearance > Widgets** and drag the "Twitter API" widget into a sidebar.
4. Register a Twitter application at [Twitter's Application Manager](https://apps.twitter.com/).
5. Set your newly registered application's Consumer key and secret in the widget's settings.

## Configuration

Each widget can be configured with these options:

* **Title** - The title of the widget.
* **Consumer key** - _Required_ - Your Twitter application's Consumer key.
* **Consumer secret** - _Required_ - Your Twitter application's Secret key.
* **Twitter username** - _Required_ - The Twitter user's tweets you'd like to fetch.
    * Make sure that this user's tweets are public! If the user is private, then you will not get the feed.
* **Number of tweets** - The number of tweets you'd like to fetch.
    * 5 tweets are fetched at a time by default.
* **Tweet template** - The template of the tweet (see below for more information).

#### Tweet template

The template that is used to display each tweet. Available template variables are:

* `{{permalink}}` - The permalink to the tweet on twitter.com.
* `{{posted_date}}` - The date of tweet. The default format is `n/j/Y`.
    * Optionally, you can add a `format` argument that accepts a [Date Format][php-doc-date-format] string. Usage is: `{{posted_date|format:"n/j/Y"}}`.
* `{{posted_date_ago}` - The relative date of the tweet (e.g. "10 minutes ago").
* `{{body}}` - The tweet's body.
* `{{username}}` - The user's name.
* `{{profile_url}}` - The user's profile URL.

This is the default template:

```html
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
</div>
```

## Contributing

Want to help? Fork [this repo][repo-url] on GitHub and submit a pull request! Even if you don't want to fork, you can still help out by submitting your bugs and feature suggestions to this project's [Issues section][repo-issues].

Have other questions? Message me on [Twitter (@jakebellacera)][twitter-url].

[repo-url]: https://github.com/jakebellacera/wordpress-twitter-api-widget
[repo-releases]: https://github.com/jakebellacera/wordpress-twitter-api-widget/releases
[repo-issues]: https://github.com/jakebellacera/wordpress-twitter-api-widget/issues
[twitter-url]: https://twitter.com/jakebellacera
[php-doc-date-format]: http://php.net/manual/en/function.date.php
