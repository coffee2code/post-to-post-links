<?php
/**
 * Plugin Name: Easy Post-to-Post Links
 * Version:     4.0
 * Plugin URI:  http://coffee2code.com/wp-plugins/easy-post-to-post-links/
 * Author:      Scott Reilly
 * Author URI:  http://coffee2code.com
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: easy-post-to-post-links
 * Domain Path: /lang/
 * Description: Easily create a link to another post using a shortcode to reference the post by id or slug; the link text is the post's title, unless overridden.
 *
 * Compatible with WordPress 3.6+ through 4.1+.
 *
 * =>> Read the accompanying readme.txt file for instructions and documentation.
 * =>> Also, visit the plugin's homepage for additional information and updates.
 * =>> Or visit: https://wordpress.org/plugins/easy-post-to-post-links/
 *
 * @package Easy_Post_to_Post_Links
 * @author  Scott Reilly
 * @version 4.0
 */

/*
 * TODO:
 * - Allow shortcode to support more natural name="" to supply post slug
 * - Reintroduce TinyMCE button
 */

/*
	Copyright (c) 2005-2015 by Scott Reilly (aka coffee2code)

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'c2c_EasyPostToPostLinks' ) ) :

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'c2c-plugin.php' );

final class c2c_EasyPostToPostLinks extends C2C_Plugin_039 {

	/**
	 * The one true instance.
	 *
	 * @var c2c_EasyPostToPostLinks
	 */
	private static $instance;

	/**
	 * The shortcode name.
	 *
	 * Filterable via 'post2post_shortcode'.
	 *
	 * @var string
	 */
	protected $shortcode = 'post2post';

	/**
	 * Get singleton instance.
	 *
	 * @since 4.0
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	protected function __construct() {
		parent::__construct( '4.0', 'easy-post-to-post-links', 'c2c', __FILE__, array() );
		register_activation_hook( __FILE__, array( __CLASS__, 'activation' ) );

		return self::$instance = $this;
	}

	/**
	 * Handle plugin upgrades.
	 *
	 * Intended to be used for updating plugin options, etc.
	 *
	 * @since 3.0
	 *
	 * @param string $old_version The version number of the old version of
	 *        the plugin. '0.0' if version number wasn't previously stored
	 */
	protected function handle_plugin_upgrade( $old_version, $options ) {
		// v3.0 of the plugin disabled the broken buttonbar code
		if ( version_compare( $old_version , '3.0', '<' ) ) {
			$options['make_quicktag'] = '';
		}

		update_option( $this->admin_options_name, $options );
		$this->options = $options;
	}

	/**
	 * Handles activation tasks, such as registering the uninstall hook.
	 *
	 * @since 3.0
	 */
	public static function activation() {
		register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );
	}

	/**
	 * Handles uninstallation tasks, such as deleting plugin options.
	 *
	 * @since 3.0
	 */
	public static function uninstall() {
		delete_option( 'c2c_easy_post_to_post_links' );
	}

	/**
	 * Initializes the plugin's configuration and localizable text variables.
	 *
	 * @since 3.0
	 */
	public function load_config() {
		$this->name      = __( 'Easy Post-to-Post Links', $this->textdomain );
		$this->menu_name = __( 'Post2Post', $this->textdomain );
		$this->shortcode = apply_filters( 'c2c_post2post_shortcode', $this->shortcode );

		$this->config = array(
			'make_quicktag' => array( 'input' => 'checkbox', 'default' => '',
					'label' => __( 'Enable post editor button?', $this->textdomain ),
					'help'  => __( 'Add button to the post editor toolbar?', $this->textdomain ) ),
			'before_text' => array( 'input' => 'text', 'default' => '"',
					'label' => __( 'Before link text', $this->textdomain ),
					'help'  => __( 'Text to appear before title of a referenced post', $this->textdomain ) ),
			'after_text' => array( 'input' => 'text', 'default' => '"',
					'label' => __( 'After link text', $this->textdomain ),
					'help'  => __( 'Text to appear after title of a referenced post', $this->textdomain ) ),
			'enable_legacy' => array( 'input' => 'checkbox', 'default' => false,
					'label' => __( 'Enable legacy HTML comment-style tag support?', $this->textdomain ),
					'help'  => __( 'Enable support for pre-2.0 post-to-post tag syntax of <code>&lt;!--post="24"--></code>?<br />Check this if you have used an older version of this plugin and thus have the older syntax in existing posts.', $this->textdomain ) . '<br />' .
							__( 'NOTE: This does NOT play well with the Visual (aka rich-text) editor in the WordPress admin.', $this->textdomain ) ),
			'enable_legacy_v2' => array( 'input' => 'checkbox', 'default' => false,
					'label' => __( 'Enable legacy pseudo-shortcode tag support?', $this->textdomain ),
					'help'  => __( 'Enable support for pre-3.0 post-to-post tag syntax of <code>[post="24"]</code>?<br />Check this if you have used an older version of this plugin and thus have the older syntax in existing posts.', $this->textdomain ) )
		);
	}

	/**
	 * Override the plugin framework's register_filters() to actually actions against filters.
	 *
	 * @since 3.0
	 */
	public function register_filters() {
		$options = $this->get_options();

		add_action( 'admin_menu',                     array( $this, 'admin_menu' ) );

		if ( $options['enable_legacy_v2'] || $options['enable_legacy'] ) {
			add_filter( 'the_content',                array( $this, 'post_to_post_link' ), 9 );
			add_filter( 'the_content_rss',            array( $this, 'post_to_post_link' ), 9 );
			add_filter( 'get_the_excerpt',            array( $this, 'post_to_post_link' ), 9 );
		}

		if ( is_admin() && $options['make_quicktag'] ) {
			add_action( 'admin_print_footer_scripts', array( $this, 'addbuttons' ) );
		}

		add_shortcode( $this->shortcode,              array( $this, 'shortcode' ) );
	}

	/**
	 * Outputs the text above the setting form.
	 *
	 * @since 3.0
	 *
	 * @param string $localized_heading_text (optional) Localized page heading text.
	 */
	public function options_page_description( $localized_heading_text = '' ) {
		$options = $this->get_options();
		parent::options_page_description( __( 'Easy Post-to-Post Settings', $this->textdomain ) );

		echo '<p>' . __( 'Easily create a link to another post using a simple shortcut and using the post\'s id or slug; the link text is the post\'s title, unless overridden.', $this->textdomain ) . '</p>';
		echo '<p>' . __( 'See the Examples tab of the "Help" link to the top-right of the page for example usage.', $this->textdomain ) . '</p>';
	}

	/**
	 * Configures help tabs content.
	 *
	 * @since 4.0
	 */
	public function help_tabs_content( $screen ) {
		$screen->add_help_tab( array(
			'id'      => 'c2c-examples-' . $this->id_base,
			'title'   => __( 'Examples', $this->textdomain ),
			'content' => self::contextual_help( '', $this->options_page )
		) );

		parent::help_tabs_content( $screen );
	}

	/**
	 * Outputs examples text.
	 *
	 * @since 4.0
	 *
	 * @param string $contextual_help The default contextual help.
	 * @param int    $screen_id       The screen ID.
	 * @param object $screen          The screen object (only supplied in WP 3.0).
	 */
	public function contextual_help( $contextual_help, $screen_id, $screen = null ) {
		if ( $screen_id != $this->options_page ) {
			return $contextual_help;
		}

		$options = $this->get_options();

		$help = '<h3>' . __( 'Examples', $this->textdomain ) . '</h3>';

		$help .= '<p>' . __( 'These are all valid ways to reference another of your posts on the same blog using the provided shortcode.', $this->textdomain ) . '</p>';

		$help .= "<ul>\n";
		$help .= '<li><code>[post2post id="25"]</code></li>' . "\n";
		$help .= '<li><code>[post2post id="25"/]</code></li>' . "\n";
		$help .= '<li><code>[post2post id="the-best-post-ever"]</code></li>' . "\n";
		$help .= '<li><code>[post2post id="the-best-post-ever"/]</code></li>' . "\n";
		$help .= "</ul>\n";

		if ( $options['enable_legacy_v2'] ) {
			$help .= '<p>' . __( 'These only work if you\'ve enabled legacy pseudo-shortcode tag support, which is not recommended unless you used the plugin back in it\'s 3.0 days.', $this->textdomain ) . '</p>';

			$help .= "<ul>\n";
			$help .= '<li><code>[post=25]</code></li>' . "\n";
			$help .= '<li><code>[post="25"]</code></li>' . "\n";
			$help .= '<li><code>[post = "25"]</code></li>' . "\n";
			$help .= "<li><code>[post='25']</code></li>\n";
			$help .= "<li><code>[post = '25']</code></li>\n";
			$help .= '<li><code>[post="the-best-post-ever"]</code></li>' . "\n";
			$help .= '<li><code>[post = "the-best-post-ever"]</code></li>' . "\n";
			$help .= "<li><code>[post='the-best-post-ever']</code></li>\n";
			$help .= "<li><code>[post = 'the-best-post-ever']</code></li>\n";
			$help .= "</ul>\n";
		}

		if ( $options['enable_legacy'] ) {
			$help .= '<p>' . __( 'These only work if you\'ve enabled legacy HTML comment-style tag support, which is not recommended unless you used the plugin back in pre-3.0 days.', $this->textdomain ) . '</p>';

			$help .= "<ul>\n";
			$help .= '<li><code>&lt;!--post=25--></code></li>' . "\n";
			$help .= '<li><code>&lt;!-- post = 25 --></code></li>' . "\n";
			$help .= '<li><code>&lt;!--post="25"--></code></li>' . "\n";
			$help .= '<li><code>&lt;!--post = "25" --></code></li>' . "\n";
			$help .= "<li><code>&lt;!-- post='25'--></code></li>\n";
			$help .= "<li><code>&lt;!-- post = '25' --></code></li>\n";
			$help .= "</ul>\n";
		}

		$help .= '<p>' . __( 'Assuming all of the above were used to reference the same post, the replacement for the post-to-post shortcut would be:', $this->textdomain ) . "</p>\n";

		$help .= '<p><code>';
		$help .= $options['before_text'];
		$help .= '&lt;a href="http://example.com/2015/03/01/the-best-post-ever" title="The Best Post Ever!">The Best Post Ever!&lt;/a>';
		$help .= $options['after_text'];
		$help .= '</code></p>' . "\n";

		$help .= '<p>' . __( 'For any of the above you can also optionally specify text to be used as the link text as opposed to the referenced post\'s title.', $this->textdomain ) . "</p>\n";

		$help .= "<ul>\n";
		$help .= '<li><code>[post2post id="25"]this post[/post2post]</code></li>' . "\n";
		$help .= '<li><code>[post2post id="the-best-post-ever"]this post[/post2post]</code></li>' . "\n";

		if ( $options['enable_legacy_v2'] ) {
			$help .= '<li><strong>' . __( 'Legacy (pre v3.0)', $this->textdomain ) . "</strong></li>\n";
			$help .= '<li><code>[post="25" text="this post"]</code></li>' . "\n";
			$help .= '<li><code>[post="blog-anniversary" text="this post"]</code></li>' . "\n";
		}

		if ( $options['enable_legacy'] ) {
			$help .= '<li><strong>' . __( 'Legacy (pre v2.0)', $this->textdomain ) . "</strong></li>\n";
			$help .= '<li><code>&lt;!--post="25" text="this post"--></code></li>' . "\n";
			$help .= "<li><code>&lt;!--post='hello-world' text='this post'--></code></li>\n";
		}

		$help .= "</ul>\n";

		$help .= '<p>' . __( 'Which would produce:', $this->textdomain ) . "</p>\n";

		$help .= '<p><code>';
		$help .= $options['before_text'];
		$help .= '&lt;a href="http://example.com/2015/03/01/the-best-post-ever" title="The Best Post Ever!">this post&lt;/a>';
		$help .= $options['after_text'];
		$help .= '</code></p>' . "\n";

		$help .= '<p>' . __( 'You can also optionally specify the attribute shortcodes "before" and "after" to override the plugin\'s settings for text to appear before and after the link, respectively.', $this->textdomain ) . "</p>\n";

		$help .= "<ul>\n";
		$help .= '<li><code>[post2post id="25" before="(" after="!)"]this post[/post2post]</code></li>' . "\n";
		$help .= '<li><code>[post2post id="the-best-post-ever" before="(" after="!)"]this post[/post2post]</code></li>' . "\n";
		$help .= "</ul>\n";

		$help .= '<p>' . __( 'Which would produce:', $this->textdomain ) . "</p>\n";

		$help .= '<p><code>';
		$help .= '(';
		$help .= '&lt;a href="http://example.com/2015/03/01/the-best-post-ever" title="The Best Post Ever!">this post&lt;/a>';
		$help .= '!)';
		$help .= '</code></p>' . "\n";

		return $help;
	}

	/**
	 * Adds button to TinyMCE (the visual editor).
	 */
	public function addbuttons() {
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		if ( user_can_richedit() & wp_script_is( 'quicktags' ) ) {
			echo <<<HTML
			<script>
				QTags.addButton( 'post2post', 'post2post', '[post2post id=""]', '[/post2post]' );
			</script>
HTML;
		}
	}

	/**
	 * Handle the shortcode for post-to-post linking.
	 *
	 * Examples of shortcode usage:
	 *   [post2post id="41"]
	 *   [post2post id="41"]check out this post[/post2post]
	 *
	 * @since 3.0
	 *
	 * @param string  $atts    Array of attributes supplied in the shortcode.
	 * @param string  $content The text between the shortcode's start and end tags, if so used. Default null.
	 * @param string  $tag     The name of the shortcode tag.
	 * @return string Text with post-to-post links handled.
	 */
	public function shortcode( $atts, $content = null, $tag = '' ) {
		$options = $this->get_options();

		$defaults = array(
			'id'     => '',
			'before' => $options['before_text'],
			'after'  => $options['after_text'],
		);
		$atts2 = shortcode_atts( $defaults, $atts );

		return $this->post_to_post_link_handler( array(
			2        => $atts2['id'],
			4        => $content,
			'before' => $atts2['before'],
			'after'  => $atts2['after']
		) );
	}

	/**
	 * Post to Post link handler.
	 *
	 * @param array   $matches Array of matches from a preg_replace().
	 * @return string The HTML marked-up link to the post referenced by the post-to-post link shortcode.
	 */
	public function post_to_post_link_handler( $matches ) {
		global $wpdb;

		$options = $this->get_options();
		$post_id_or_name = $matches[2];
		$title   = isset( $matches[4] ) ? $matches[4] : '';
		$title   = trim( $title );
		$before  = ( isset( $matches['before'] ) && $matches['before'] ) ? $matches['before'] : $options['before_text'];
		$after   = ( isset( $matches['after'] ) && $matches['after'] ) ? $matches['after'] : $options['after_text'];

		if ( empty( $post_id_or_name ) ) {
			return '';
		}

		if ( is_numeric( $post_id_or_name ) ) {
			$post = &get_post( $post_id_or_name );
		} else {
			$post = get_page_by_path( $post_id_or_name, OBJECT, 'post' );
		}

		if ( ! $post || empty( $post->ID ) || empty( $post->post_title ) ) {
			return '';
		}

		$post_title = get_the_title( $post );
		return $before .
			   '<a href="' . get_permalink( $post ) . '" title="' . esc_attr( strip_tags( $post_title ) ) . '">' .
			   ( $title ? $title : $post_title ) .
			   '</a>' . $after;
	}

	/**
	 * Search text for possible post-to-post link syntax.
	 *
	 * @param string  $text The text.
	 * @return string The text with all post-to-post link shortcodes replaced.
	 */
	public function post_to_post_link( $text ) {
		$options = $this->get_options();

		if ( $options['enable_legacy_v2'] ) {
			$text = preg_replace_callback(
				"#(\[post[ ]*=[ ]*['\"]?([^'\" ]+)['\"]?( text[ ]*=[ ]*['\"]([^'\"]+)['\"])?\])#imsU",
				array( $this, 'post_to_post_link_handler' ),
				$text
			);
		}

		if ( $options['enable_legacy'] ) {
			$text = preg_replace_callback(
				"#(<!--[ ]*post[ ]*=[ ]*['\"]?([^'\" ]+)['\"]?[ ]*(text[ ]*=[ ]*['\"]([^'\"]+)['\"])?[ ]*-->)#imsU",
				array( $this, 'post_to_post_link_handler' ),
				$text
			);
		}

		return $text;
	}

} //end c2c_EasyPostToPostLinks

c2c_EasyPostToPostLinks::get_instance();

endif; // end if !class_exists()
