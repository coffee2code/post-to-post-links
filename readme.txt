=== Easy Post-to-Post Links ===
Contributors: coffee2code
Donate link: http://coffee2code.com
Tags: posts, links
Requires at least: 2.2
Tested up to: 2.5
Stable tag: trunk
Version: 2.0

Easily create a link to another post using a simple shortcut that references the post's id or slug; the link text is the post's title, unless overridden.

== Description ==

Easily create a link to another post using a simple shortcut that references the post's id or slug; the link text is the post's title, unless overridden.

When writing your posts, you can refer to other posts either by ID, like so: 
	`[post="20"]` or `<!--post="20"-->`
or by the post slug/name, like so:
	`[post="hello-world"]` or `<!--post="hello-world"-->.`

When viewed on your site, the post-to-post link tag is replaced with a permalink to the post you've referenced.
By default, the text of the link will be the referenced post's title, resulting in something like:
  `<a href="http://example.com/archives/2005/04/01/hello-world/" title="Hellow World!">Hello World!</a>`

You can optionally customize the link text by specifying a `text=""` value to the post-to-post link tag:
  `Check out [post="hello-world" text="my first post"].`
Yields:
  `Check out <a href="http://example.com/archives/2005/04/01/hello-world/" title="Hello World!">my first post</a>.`

The HTML comment notation was the original syntax employed by earlier versions of this plugin.  While it is still supported, it is no longer the primary and recommended syntax.  Instead, use the square-bracket notation.  However, you can enable legacy tag support by checking the appropriate option on the plugin's admin options page.

A quicktag button labeled "post link" is created by default, which will automatically insert `[post=""]` into the post/page textarea.  Insert the ID/post slug between the double-quotes.  A visual editor button has also been added which does the same thing as well, which also adds the feature of treating highlighted text as the `text=""` value when the button is pressed.

The plugin provides its own admin options page via `Options` -> `Post2Post` (or in WP 2.5: `Settings` -> `Post2Post`) in the WordPress admin.  Here you can define text that you want to appear before and/or after each post-to-post substitution, whether to create the post editor quicktag buttons, and if you want to enable legacy tag support.  This admin page also provides some documentation.

NOTE: The HTML comment syntax notation does NOT play well with the visual (aka rich-text) editor in the WordPress admin.  

== Installation ==

1. Unzip `post-to-post-links.zip` inside the `/wp-content/plugins/` directory, or upload `post-to-post-links.php` to `/wp-content/plugins/`
1. Activate the plugin through the 'Plugins' admin menu in WordPress
1. Go to the `Options` -> `Post2Post` (or in WP 2.5: `Settings` -> `Post2Post`) admin options page.  Optionally customize the settings.
1. Use the Post-to-Post link syntax in posts to refer to other posts, as needed. (See examples.)

== Examples ==

These are all valid ways to reference another of your posts on the same blog.  Those that use HTML comment notation (i.e. `<!-- post="XX" -->`) only work if you've enabled legacy tag support.

* `[post=25]`
* `[post="25"]`
* `[post = "25"]`
* `[post='25']`
* `[post = '25']`
* `[post="the-best-post-ever"]`
* `[post = "the-best-post-ever"]`
* `[post='the-best-post-ever']`
* `[post = 'the-best-post-ever']`
* `<!--post=25-->`
* `<!-- post = 25 -->`
* `<!--post="25"-->`
* `<!--post = "25" -->`
* `<!-- post='25'-->`
* `<!-- post = '25' -->`

Assuming all of the above were used to reference the same post, the replacement for the post-to-post shortcut would be:

`<a href="http://example.com/2008/03/01/the-best-post-ever" title="The Best Post Ever!">The Best Post Ever!</a>`

For any of the above you can also optionally specify a `text=""` value.  If so defined, that text will be used as the link text as opposed to the referenced post's title.

* `[post="25" text="this post"]`
* `[post="blog-anniversary" text="Congratulate me!"]`
* `<!--post="25" text="this post"-->`
* `<!--post='hello-world' text='this post'-->`

The first of which would produce:

`<a href="http://example.com/2008/03/01/the-best-post-ever" title="The Best Post Ever!">this post</a>`

== Frequently Asked Questions ==

= Should I enable legacy tag support? =

The "Enable legacy tag support?" setting controls whether the older, HTML comment style notation should be recognized.  You should only enable legacy if you've used versions of this plugin prior to v2.0 and still want those post-to-post links to work without updating them.  If you started using the plugin at v2.0 or later, you do not need to enable legacy support.

=  What happens a post-to-post tag references a post that does not exist? =

If no post on your blog matches the value you specified as either a post ID or post slug, then the post-to-post tag in question disappears from the view, having been replaced with an empty string.

== Screenshots ==

1. A screenshot of the plugin's admin options page.
