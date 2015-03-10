=== Easy Post-to-Post Links ===
Contributors: coffee2code
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6ARCFJ9TX3522
Tags: post, posts, pages, links, shortcode, shortcut, coffee2code
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 3.6
Tested up to: 4.1
Stable tag: 4.0

Easily create a link to another post using a shortcode to reference the post by id or slug; the link text is the post's title, unless overridden.


== Description ==

Easily create a link to another post using a shortcode to reference the post by id or slug; the link text is the post's title, unless overridden.

When writing your posts, you can refer to other posts either by ID, like so:

`[post2post id="20"]`

or by the post slug/name, like so:

`[post2post id="hello-world"]`

When viewed on your site, the post-to-post link tag is replaced with a permalink to the post you've referenced. By default, the text of the link will be the referenced post's title, resulting in something like:

`"<a href="http://example.com/archives/2005/04/01/hello-world/" title="Hellow World!">Hello World!</a>"`

You can optionally customize the link text by specifying a `text=""` attribute and value to the post-to-post link tag:

`Check out [post2post id="hello-world"]my first post[/post2post].`

Which yields:

`Check out "<a href="http://example.com/archives/2011/07/01/hello-world/" title="Hello World!">my first post</a>".`

The plugin provides its own admin options page via `Settings` -> `Post2Post` in the WordPress admin. Here you can define text that you want to appear before and/or after each post-to-post substitution, and if you want to enable legacy tag support. The plugin's admin page also provides some documentation.

Links: [Plugin Homepage](http://coffee2code.com/wp-plugins/easy-post-to-post-links/) | [Plugin Directory Page](https://wordpress.org/plugins/easy-post-to-post-links/) | [Author Homepage](http://coffee2code.com)


== Installation ==

1. Unzip `easy-post-to-post-links.zip` inside the `/wp-content/plugins/` directory (or install via the built-in WordPress plugin installer)
1. Activate the plugin through the 'Plugins' admin menu in WordPress
1. Go to the `Settings` -> `Post2Post` admin options page. Optionally customize the settings.
1. Use the Post-to-Post link syntax in posts to refer to other posts, as needed. (See examples.)


== Examples ==

These are all valid ways to reference another of your posts on the same blog.

* `[post2post id="25"]`
* `[post2post id="25"/]`
* `[post2post id="the-best-post-ever"]
* `[post2post id="the-best-post-ever"/]

Assuming all of the above were used to reference the same post, the replacement for the post-to-post shortcut would be:

`"<a href="http://example.com/2011/07/01/the-best-post-ever" title="The Best Post Ever!">The Best Post Ever!</a>"`

For any of the above you can also optionally wrap the shortcode around text. If so defined, that text will be used as the link text as opposed to the referenced post's title.

* `[post2post id="25"]this post[/post2post]`
* `[post2post id="blog-anniversary"]Congratulate me![/post2post]`

The first of which would produce:

`"<a href="http://example.com/2011/07/01/the-best-post-ever" title="The Best Post Ever!">this post</a>"`

You can also optionally definte the text that appears before and after the generated links. By default these are quotes.

* `[post2post id="25" before=" " after="!"]

Yields:

` <a href="http://example.com/2011/07/01/the-best-post-ever" title="The Best Post Ever!">this post</a>!`


== Frequently Asked Questions ==

= Should I enable legacy tag support? =

The "Enable legacy HTML comment-style tag support?" setting controls whether the older, HTML comment style notation (from before v2.0 of the plugin) should be recognized. The "Enable legacy pseudo-shortcode tag support?" setting controls whether the older pseudo-shortcode syntax (from before v3.0 of the plugin) should be recognized  You should only enable a particular legacy mode if you've used versions of this plugin prior to v3.0 and still want those post-to-post links to work without updating them. If you started using the plugin at v3.0 or later, you do not need to enable legacy support. Unnecessarily enabling legacy tag support will have a negative impact on the performance of your site.

= What happens when a post-to-post tag references a post that does not exist? =

If no post on your blog matches the value you specified as either a post ID or post slug, then the post-to-post tag in question disappears from the view, having been replaced with an empty string.

= Can't the shortcode be something other than 'post2post'; perhaps something shorter? =

Yes. See the Filters section for an example of code to change the shortcode name. If you are going to make this short of change, it is recommended that you do so before making use of the shortcode in your posts/pages (unless you search and replace all occurrences of the original shortcode in other posts). Out of the box, the plugin only supports a single name for the shortcode.


== Screenshots ==

1. A screenshot of the plugin's admin options page.


== Legacy ==

The legacy syntaxes -- which are disabled by default and not recommended for use unless you've used version of the plugin earlier than v3.0 -- allow you to refer to other posts by ID using a pseudo-shortcode syntax or an HTML comment syntax, like so:

`[post="20"]` or `<!--post="20"-->`

or by the post slug/name, like so:

`[post="hello-world"]` or `<!--post="hello-world"-->.`

The HTML comment notation was the original syntax employed by earlier versions of this plugin (pre v2.0). While it is still supported, it is no longer the primary and recommended syntax. The pseudo-shortcode syntax was in use between v2.0 and v3.0 of the plugin.

NOTE: The HTML comment syntax notation does NOT play well with the visual (aka rich-text) editor in the WordPress admin.

Examples of old legacy syntax follow. Those that use the pseudo-shortcode syntax (i.e. `[post=25]`) only work if you've checked "Enable legacy pseudo-shortcode tag support?". Those that use HTML comment notation (i.e. `<!-- post="XX" -->`) only work if you've checked "Enable legacy HTML comment-style tag support?".

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

**NOTE:** Only activate the legacy mode(s) that apply to your use of older versions of the plugin. If you started using the plugin at v3.0 or later, you should not activate either legacy mode.


== Filters ==

The plugin exposes one filter for hooking. Typically, customizations utilizing this hook would be put into your active theme's functions.php file, or used by another plugin.

= c2c_post2post_shortcode (filter) =

The 'c2c_post2post_shortcode' hook allows you to define an alternative to the default shortcode tag. By default the shortcode tag name used is 'post2post'. It is recommended you only utilize this filter before making use of the plugin's shortcode in posts and pages. If you change the shortcode tag name, then any existing shortcodes using an older name will no longer work (unless you employ further coding efforts).

Arguments:

* $shortcode (string)

Example:

`
/**
 *  Use a shorter shortcode: i.e. [p2p id="32"]
 *
 * @param string  $shortcode The default shortcode name.
 * @return string The new shortcode name.
 */
function change_post2post_shortcde( $shortcode ) {
	return 'p2p';
}
add_filter( 'c2c_post2post_shortcode', 'change_post2post_shortcde' );
`


== Changelog ==

= 4.0 (2015-03-09) =
* Publicly release changes from the not-publicly released v2.0 and v3.0 of the plugin
* Update plugin framework to 039
* Add support for shortcode attributes 'before' and 'after' to override plugin setting on per-user basis
* Implement a Quicktags button "post2post" that inserts the shortcode into the post editor (Text-mode only)
* Remove support for the broken TinyMCE button (Visual-mode), including files
* Move plugin page examples out from bottom of settings page into help tab
* Add `help_tabs_content()`, `contextual_help()`
* Remove `show_examples()`, `mce_external_plugins()`, `mce_buttons()`
* Better singleton implementation:
    * Add `get_instance()` static method for returning/creating singleton instance
    * Make static variable 'instance' private
    * Make constructor protected
    * Make class final
    * Additional related changes in plugin framework (protected constructor, erroring `__clone()` and `__wakeup()`)
* Add unit tests
* Explicitly declare `activation()` and `uninstall()` static
* Add checks to prevent execution of code if file is directly accessed
* Re-license as GPLv2 or later (from X11)
* Reformat plugin header
* Add 'License' and 'License URI' header tags to readme.txt and plugin file
* Use explicit path for require_once()
* Discontinue use of PHP4-style constructor
* Discontinue use of explicit pass-by-reference for objects
* Remove ending PHP close tag
* Minor documentation improvements
* Minor code reformatting (spacing, bracing)
* Change documentation links to wp.org to be https
* Note compatibility through WP 4.1+
* Drop compatibility with version of WP older than 3.6
* Update copyright date (2015)
* Regenerate .pot
* Move .pot to lang/ subdirectory
* Change donate link
* Add assets directory to plugin repository checkout
* Update screenshot
* Move screenshot into repo's assets directory
* Add banner
* Add icon

= 3.0 =
* Add support for and favor 'post2post' shortcode to link to posts
* Add filter 'c2c_post2post_shortcode' to customize shortcode tag
* Re-implementation by extending C2C_Plugin_025, which adds support for:
    * Reset of options to default values
    * Better sanitization of input values
    * Offload of core/basic functionality to generic plugin framework
    * Additional hooks for various stages/places of plugin operation
    * Easier localization support
    * More
* Add shortcode examples to plugin settings page
* No longer attempt add button to quicktags editor buttonbar (not sure if this will be permanent or not)
* Full localization support
* Add __construct(), activation(), uninstall()
* Rename class from PostToPostLinks to c2c_EasyPostToPostLinks
* Move object instantiation to within the initial if(!class_exists()) check
* Save a static version of itself in class variable $instance
* Add PHPDoc documentation
* Add package info to top of plugin file
* Remove docs from top of plugin file (all that and more are in readme.txt)
* Minor code reformatting (spacing)
* Improve documentation
* Note compatibility through WP 3.2+
* Drop compatibility with versions of WP older than 3.0
* Tweak description
* Update copyright date (2011)
* Add Legacy, Filters, Changelog, and Upgrade Notice sections to readme.txt
* Add screenshot
* Add .pot file
* Add plugin homepage and author links in description in readme.txt

= 2.0 =
* Created its own class to encapsulate plugin functionality
* Added admin options page under Options -> Post2Post (or in WP 2.5: Settings -> Post2Post). Options are now saved to database, negating need to customize code within the plugin source file.
* Admin options page also includes documentation and examples
* Added support for new post-to-post tag syntax of [post="XX"]
  (also still works for legacy syntax)
* Added support for per-post-to-post-link override of display text, i.e. [post='25' title='this post']
* Fixed bug that prevented linking to posts vis post slug from working
* Send post’s ID to get_the_title(), not the title
* Utilize get_the_title() to obtain the post’s title, rather than direct retrieval
* Added filter so that content seen via RSS gets filtered
* Filter ‘get_the_except’ instead of ‘the_excerpt’
* Added new admin option ‘enable_legacy’ to control support of legacy syntax
* Changed filter priority level to 9 to trigger before some of WP’s formatting filters
* Added TinyMCE button (w/ custom image) to insert post-to-post link tag into editor. If text is highlighted, it becomes the text=”" value.
* Fixed Quicktags button support; now it also applies to writing and creating pages
* Changed installation instructions
* Added compatibility note
* Updated copyright date and version to 2.0
* Moved into its own subdirectory; added readme.txt and screenshot
* Tested compatibility with WP 2.3.3 and 2.5

= 1.0 =
* (Lost to time)

= 0.9 =
* Initial release


== Upgrade Notice ==

= 4.0 =
Recommended major update: added new shortcode attributes 'before' and 'after'; lots of internal improvements; added unit tests; updated plugin framework; compatibility now WP 3.6-4.1+

= 3.0 =
Recommended major update! Highlights: new shortcode shortcut; deprecated (but still support) all older shortcut methods; noted WP 3.2+ compatibility; dropped compatibility with versions of WP older than 3.0; utilize plugin framework; and more.
