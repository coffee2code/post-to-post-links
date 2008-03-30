<?php
/*
Plugin Name: Easy Post-to-Post Links
Version: 2.0
Plugin URI: http://coffee2code.com/wp-plugins/post-to-post-links
Author: Scott Reilly
Author URI: http://coffee2code.com
Description: Easily create a link to another post using a simple shortcut and using the post's id or slug; the link text is the post's title, unless overridden.

When writing your posts, you can refer to other posts either by ID, like so: 
	[post="20"] or <!--post="20"-->	
or by the post slug/name, like so:
	[post="hello-world"] or <!--post="hello-world"-->.

The HTML comment notation was the original syntax employed by earlier versions of this plugin.  While it is still supported, it is no longer the primary and recommended syntax.  Instead, use the square-bracket notation.  However, you can enable legacy tag support by checking the appropriate option on the plugin's admin options page.

A quicktag button labeled "post link" is created by default, which will automatically insert [post=""] into the post/page
textarea.  Insert the ID/post slug between the double-quotes.  A visual editor button has also been added which does the same thing
as well, which also adds the feature of treating highlighted text as the text="" value when the button is pressed.

The plugin provides its own admin options page via `Options` -> `Post2Post` in the WordPress admin.

NOTE: The HTML comment syntax notation does NOT play well with the visual (aka rich-text) editor in the WordPress admin.  

Compatible with WordPress 2.2+, 2.3+, and 2.5.

=>> Read the accompanying readme.txt file for more information.  Also, visit the plugin's homepage
=>> for more information and the latest updates

Installation:

1. Download the file http://coffee2code.com/wp-plugins/post-to-post-links.zip and unzip it into your 
/wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' admin menu in WordPress
3. Go to the new Options -> Post2Post admin options page.  Optionally customize the options.

*/

/*
Copyright (c) 2005-2008 by Scott Reilly (aka coffee2code)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation 
files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, 
modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the 
Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

if ( !class_exists('PostToPostLinks') ) :

class PostToPostLinks {
	var $admin_options_name = 'c2c_post_to_post_links';
	var $nonce_field = 'update-post_to_post_links';
	var $show_admin = true;	// Change this to false if you don't want the plugin's admin page shown.
	var $config = array();
	var $options = array(); // Don't use this directly
	var $folder = 'wp-content/plugins/post-to-post-links/';
	var $fullfolderurl;

	function PostToPostLinks() {
		$this->fullfolderurl = get_bloginfo('wpurl') . '/' . $this->folder;
		$this->config = array(
			// input can be 'checkbox', 'text', 'textarea', 'inline_textarea', 'hidden', or 'none'
			// datatype can be 'array' or 'hash'
			// can also specify input_attributes
			'make_quicktag' => array('input' => 'checkbox', 'default' => true,
					'label' => 'Enable post editor button?',
					'help' => 'Add button to the post editor toolbar?'),
			'before_text' => array('input' => 'text', 'default' => '"',
					'label' => 'Before link text',
					'help' => 'Text to appear before title of a referenced post'),
			'after_text' => array('input' => 'text', 'default' => '"',
					'label' => 'After link text',
					'help' => 'Text to appear after title of a referenced post'),
			'enable_legacy' => array('input' => 'checkbox', 'default' => false,
					'label' => 'Enable legacy tag support?',
					'help' => 'Enable support for pre-2.0 post-to-post tag syntax of <code>&lt;!--post="24"--></code>?<br />
							 Check this if you have used an older version of this plugin and thus have the older syntax in existing posts.')
		);
		$options = $this->get_options();
		add_action('admin_menu', array(&$this, 'admin_menu'));		
		add_filter('the_content', array(&$this, 'post_to_post_link'), 9);
		add_filter('the_content_rss', array(&$this, 'post_to_post_link'), 9);
		add_filter('get_the_excerpt', array(&$this, 'post_to_post_link'), 9);
		if ( $options['make_quicktag'] )
				add_action('init', array(&$this, 'addbuttons'));
	}

	function install() {
		$this->options = $this->get_options();
		update_option($this->admin_options_name, $this->options);
	}

	function admin_menu() {
		if ( $this->show_admin )
			add_options_page('Easy Post-to-Post Links', 'Post2Post', 9, basename(__FILE__), array(&$this, 'options_page'));
	}

    function addbuttons() {
    	global $wp_db_version;
        if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) return;

		if ( !get_user_option( 'rich_editing' ) || !user_can_richedit() )
			add_filter('admin_footer', array(&$this, 'add_postlink_button'));
       	else {
			// Load and append TinyMCE external plugins
			add_filter('mce_plugins', array(&$this, 'mce_plugins'));
			add_filter('mce_buttons', array(&$this, 'mce_buttons'));
			add_action('tinymce_before_init', array(&$this, 'tinymce_before_init'));
		}
	}
	
	function mce_plugins($plugins) {
		array_push($plugins, '-posttopostlinks');
		return $plugins;
	}
	function mce_buttons($buttons) {
		array_push($buttons, 'separator', 'posttopostlinks');
		return $buttons;
	}
	function tinymce_before_init() {
		echo 'tinyMCE.loadPlugin("posttopostlinks", "' . $this->fullfolderurl . "tinymce/\");\n";
	}

	function add_postlink_button() {
		if ( in_array(basename($_SERVER['SCRIPT_NAME']), array('post-new.php', 'page-new.php', 'post.php', 'page.php')) ) {
			echo <<<HTML
		<script language="JavaScript" type="text/javascript"><!--
		function js_c2c_add_postlink_button () {
			var edspell = document.getElementById("ed_spell");
			if (edspell == null) return;
			var edpostlink = document.getElementById("ed_postlink");
			if (edpostlink != null) return;
			edButtons[edButtons.length] =
			new edButton('ed_postlink'
			,'post link'
			,'[post=""]'
			,''
			,''
			);
			n = edButtons.length - 1;
			edShowButton(edButtons[n], n);
			var newbutton = document.getElementById(edButtons[n].id);
			edspell.parentNode.insertBefore(newbutton, edspell);
			return;
		}        
		js_c2c_add_postlink_button();
		//--></script>
HTML;
		}
	}

	function get_options() {
		if ( !empty($this->options)) return $this->options;
		// Derive options from the config
		$options = array();
		foreach (array_keys($this->config) as $opt) {
			$options[$opt] = $this->config[$opt]['default'];
		}
        $existing_options = get_option($this->admin_options_name);
        if (!empty($existing_options)) {
            foreach ($existing_options as $key => $value)
                $options[$key] = $value;
        }            
		$this->options = $options;
        return $options;
	}

	function options_page() {
		$options = $this->get_options();
		// See if user has submitted form
		if ( isset($_POST['submitted']) ) {
			check_admin_referer($this->nonce_field);

			foreach (array_keys($options) AS $opt) {
				$options[$opt] = htmlspecialchars(stripslashes($_POST[$opt]));
				$input = $this->config[$opt]['input'];
				if (($input == 'checkbox') && !$options[$opt])
					$options[$opt] = 0;
				if ($this->config[$opt]['datatype'] == 'array') {
					if ($input == 'text')
						$options[$opt] = explode(',', str_replace(array(', ', ' ', ','), ',', $options[$opt]));
					else
						$options[$opt] = array_map('trim', explode("\n", trim($options[$opt])));
				}
				elseif ($this->config[$opt]['datatype'] == 'hash') {
					if ( !empty($options[$opt]) ) {
						$new_values = array();
						foreach (explode("\n", $options[$opt]) AS $line) {
							list($shortcut, $text) = array_map('trim', explode("=>", $line, 2));
							if (!empty($shortcut)) $new_values[str_replace('\\', '', $shortcut)] = str_replace('\\', '', $text);
						}
						$options[$opt] = $new_values;
					}
				}
			}
			// Remember to put all the other options into the array or they'll get lost!
			update_option($this->admin_options_name, $options);

			echo "<div class='updated'><p>Plugin settings saved.</p></div>";
		}

		$action_url = $_SERVER[PHP_SELF] . '?page=' . basename(__FILE__);

		echo <<<END
		<div class='wrap'>
			<h2>Post-to-Post Links Plugin Options</h2>
			<p>Easily create a link to another post using a simple shortcut and using the post's id or slug; the link text is the post's title, unless overridden.</p>

			<p>See the <a href="#examples" title="Examples">Examples</a> section for example usage.</p>
			
			<p><em>NOTE: The HTML comment notation (aka legacy tag) does NOT play well with the visual (aka rich-text) editor in the WordPress admin.</em></p>
			<form name="post_to_post_links" action="$action_url" method="post">	
END;
				wp_nonce_field($this->nonce_field);
		echo '<table width="100%" cellspacing="2" cellpadding="5" class="optiontable editform form-table">';
				foreach (array_keys($options) as $opt) {
					$input = $this->config[$opt]['input'];
					if ($input == 'none') continue;
					$label = $this->config[$opt]['label'];
					$value = $options[$opt];
					if ($input == 'checkbox') {
						$checked = ($value == 1) ? 'checked=checked ' : '';
						$value = 1;
					} else {
						$checked = '';
					};
					if ($this->config[$opt]['datatype'] == 'array') {
						if ($input == 'textarea' || $input == 'inline_textarea')
							$value = implode("\n", $value);
						else
							$value = implode(', ', $value);
					} elseif ($this->config[$opt]['datatype'] == 'hash') {
						$new_value = '';
						foreach ($value AS $shortcut => $replacement) {
							$new_value .= "$shortcut => $replacement\n";
						}
						$value = $new_value;
					}
					echo "<tr valign='top'>";
					if ($input == 'textarea') {
						echo "<td colspan='2'>";
						if ($label) echo "<strong>$label</strong><br />";
						echo "<textarea name='$opt' id='$opt' {$this->config[$opt]['input_attributes']}>" . $value . '</textarea>';
					} else {
						echo "<th scope='row'>$label</th><td>";
						if ($input == "inline_textarea")
							echo "<textarea name='$opt' id='$opt' {$this->config[$opt]['input_attributes']}>" . $value . '</textarea>';
						else
							echo "<input name='$opt' type='$input' id='$opt' value='$value' $checked {$this->config[$opt]['input_attributes']} />";
					}
					if ($this->config[$opt]['help']) {
						echo "<br /><span style='color:#777; font-size:x-small;'>";
						echo $this->config[$opt]['help'];
						echo "</span>";
					}
					echo "</td></tr>";
				}
		echo <<<END
			</table>
			<input type="hidden" name="submitted" value="1" />
			<div class="submit"><input type="submit" name="Submit" value="Save Changes" /></div>
		</form>
			</div>
END;
		$logo = get_option('siteurl') . '/wp-content/plugins/' . basename($_GET['page'], '.php') . '/c2c_minilogo.png';
		echo <<<END
		<style type="text/css">
			#c2c {
				text-align:center;
				color:#888;
				background-color:#ffffef;
				padding:5px 0 0;
				margin-top:12px;
				border-style:solid;
				border-color:#dadada;
				border-width:1px 0;
			}
			#c2c div {
				margin:0 auto;
				padding:5px 40px 0 0;
				width:45%;
				min-height:40px;
				background:url('$logo') no-repeat top right;
			}
			#c2c span {
				display:block;
				font-size:x-small;
			}
		</style>
		<div id='c2c' class='wrap'>
			<div>
			This plugin brought to you by <a href="http://coffee2code.com" title="coffee2code.com">Scott Reilly, aka coffee2code</a>.
			<span><a href="http://coffee2code.com/donate" title="Please consider a donation">Did you find this plugin useful?</a></span>
			</div>
		</div>
END;
		echo <<<END
		<div class='wrap'>
		<h2>Examples</h2>
		<a name="example"></a>These are all valid ways to reference another of your posts on the same blog.  Those that use HTML comment notation (i.e. <code>&lt;!-- post="XX" --></code>) only work if you've enabled legacy tag support.
	
		<ul>
		<li><code>[post=25]</code></li>
		<li><code>[post="25"]</code></li>
		<li><code>[post = "25"]</code></li>
		<li><code>[post='25']</code></li>
		<li><code>[post = '25']</code></li>
		<li><code>[post="the-best-post-ever"]</code></li>
		<li><code>[post = "the-best-post-ever"]</code></li>
		<li><code>[post='the-best-post-ever']</code></li>
		<li><code>[post = 'the-best-post-ever']</code></li>
		<li><code>&lt;!--post=25--></code></li>
		<li><code>&lt;!-- post = 25 --></code></li>
		<li><code>&lt;!--post="25"--></code></li>
		<li><code>&lt;!--post = "25" --></code></li>
		<li><code>&lt;!-- post='25'--></code></li>
		<li><code>&lt;!-- post = '25' --></code></li>
		</ul>

		For any of the above you can also optionally specify a <code>text=""</code> value.  If so defined, that text will be used as the link text as opposed to the referenced post's title.

		<ul>
		<li><code>[post="25" text="this post"]</li>
		<li><code>[post="blog-anniversary" text="Congratulate me!"]</li>
		<li><code>&lt;!--post="25" text="this post"--></li>
		<li><code>&lt;!--post='hello-world' text='this post'--></li>
		</ul>
		
		</div>
END;
	}

	/* This is a helper function. */
	function post_to_post_link_handler( $matches ) {
		global $wpdb;
		$options = $this->get_options();
		$post_id_or_name = $wpdb->escape($matches[2]);
		$title = $matches[4];
		if ( empty($post_id_or_name) ) return '';
//		$field = (is_numeric($post_id_or_name)) ? 'ID' : 'post_name';
		if ( is_numeric($post_id_or_name) )
			$post = get_post($post_id_or_name);
		else
			$post = $wpdb->get_row("SELECT ID, post_title FROM $wpdb->posts WHERE post_name = '$post_id_or_name' LIMIT 1");
		if ( empty($post->post_title) ) return '';
		return $options['before_text'] .
			   '<a href="' . get_permalink($post->ID) . '">' .
			   ($title ? $title : apply_filters('the_title', get_the_title($post->ID))) .
			   '</a>' . $options['after_text'];
	} //end post_to_post_link_handler

	function post_to_post_link( $text ) {
		$options = $this->get_options();
		$text = preg_replace_callback(
			"#(\[post[ ]*=[ ]*['\"]?([^'\" ]+)['\"]?( text[ ]*=[ ]*['\"]([^'\"]+)['\"])?\])#imsU",
			array(&$this, 'post_to_post_link_handler'),
			$text
		);
		if ( $options['enable_legacy'] ) {
			$text = preg_replace_callback(
				"#(<!--[ ]*post[ ]*=[ ]*['\"]?([^'\" ]+)['\"]?[ ]*(text[ ]*=[ ]*['\"]([^'\"]+)['\"])?[ ]*-->)#imsU",
				array(&$this, 'post_to_post_link_handler'),
				$text
			);
		}
		return $text;
	} //end post_to_post_links

} // end PostToPostLinks

endif; // end if !class_exists()
if ( class_exists('PostToPostLinks') ) :
	// Get the ball rolling
	$post_to_post_links = new PostToPostLinks();
	// Actions and filters
	if (isset($post_to_post_links)) {
		register_activation_hook( __FILE__, array(&$post_to_post_links, 'install') );
	}
endif;

?>