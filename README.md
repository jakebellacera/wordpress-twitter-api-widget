# Wordpress Twitter Widget

This is a basic widget for Wordpress that displays your Twitter feed.

## Installation

1. Go to the [Releases section][repo-releases] of this project and download the latest release as a zip file.
2. Unzip and place the folder into your Wordpress install's `wp-content/plugins` directory.
3. In your Wordpress Admin panel, navigate to **Appearance > Widgets** and drag the "Twitter Widget" into a sidebar.

## Configuration

Each widget can be configured with these options:

* **Title** - The title of the widget.
* **Consumer key** - _Required_ - Your Twitter application's Consumer key.
* **Consumer secret** - _Required_ - Your Twitter application's Secret key.
* **Twitter username** - _Required_ - The Twitter user's tweets you'd like to fetch.
    * Make sure that this user's tweets are public! If the user is private, then you will not get the feed.
* **Number of tweets** - The number of tweets you'd like to fetch.
    * 5 tweets are fetched at a time by default.
* **Show replies?** - If checked, the user's replies will be fetched in addition to the user's tweets.
    * By default, this option is unchecked.

### Advanced Options

The following advanced options are available as well:

* **Tweet template** - The template of the tweet (see below for more information).
* **Highlight links?** - If checked, any URLs found in the tweet will be parsed and set as `<a>` tags.
   * By default, this option is checked.

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

[repo-url]: https://github.com/jakebellacera/wordpress-twitter-widget
[repo-releases]: https://github.com/jakebellacera/wordpress-twitter-widget/releases
[repo-issues]: https://github.com/jakebellacera/wordpress-twitter-widget/issues
[twitter-url]: https://twitter.com/jakebellacera
[php-doc-date-format]: http://php.net/manual/en/function.date.php
