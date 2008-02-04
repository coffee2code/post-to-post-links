=== Easy Post-to-Post Links ===
Contributors: coffee2code
Donate link: http://coffee2code.com
Tags: posts, links
Requires at least: 2.0.2
Tested up to: 2.3.2
Stable tag: trunk
Version: 2.0

Easily create a link to another post using a simple shortcut and using the post's id or slug; the link text is the post's title, unless overridden.

== Description ==

Easily create a link to another post using a simple shortcut and using the post's id or slug; the link text is the post's title, unless overridden.

When writing your posts, you can refer to other posts either by ID, like so: 
	[post="20"] or <!--post="20"-->	
or by the post slug/name, like so:
	[post="hello-world"] or <!--post="hello-world"-->.

The HTML comment notation was the original syntax employed by earlier versions of this plugin.  While it is still supported, it is no longer the primary and recommended syntax.  Instead, use the square-bracket notation.  However, you can enable legacy tag support by checking the appropriate option on the plugin's admin options page.

A quicktag button labeled "post link" is created by default, which will automatically insert `[post=""]` into the post/page textarea.  Insert the ID/post slug between the double-quotes.  A visual editor button has also been added which does the same thing as well, which also adds the feature of treating highlighted text as the `text=""` value when the button is pressed.

The plugin provides its own admin options page via `Options` -> `Post2Post` in the WordPress admin.

NOTE: The HTML comment syntax notation does NOT play well with the visual (aka rich-text) editor in the WordPress admin.  

== Installation ==

1. Unzip `post-to-post-links.zip` inside the `/wp-content/plugins/` directory, or upload `post-to-post-links.php` to `/wp-content/plugins/`
1. Activate the plugin through the 'Plugins' admin menu in WordPress
1. Go to the new `Options` -> `Post2Post` admin options page.  Optionally customize the limits.
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

For any of the above you can also optionally specify a `text=""` value.  If so defined, that text will be used as the link text as opposed to the referenced post's title.

* `[post="25" text="this post"]`
* `[post="blog-anniversary" text="Congratulate me!"]`
* `<!--post="25" text="this post"-->`
* `<!--post='hello-world' text='this post'-->`

== Screenshots ==

1. A screenshot of the plugin's admin options page.
