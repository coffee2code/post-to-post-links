<?php
/*
Plugin Name: Easy Post-to-Post Links
Version: 2.0
Author: Scott Reilly
Author URI: http://www.coffee2code.com
Description: Easily reference another post in your blog using a shortcut, either by id or post slug.  The shortcut is replaced with the hyperlinked title of the referenced post.

Compatible with WordPress 2.2+, and 2.3+.

=>> Read the accompanying readme.txt file for more information.  Also, visit the plugin's homepage
=>> for more information and the latest updates

Installation:

1. Download the file http://www.coffee2code.com/wp-plugins/post-to-post-links.zip and unzip it into your 
/wp-content/plugins/ directory.
-OR-
Copy and paste the the code ( http://www.coffee2code.com/wp-plugins/post-to-post-links.phps ) into a file called 
post-to-post-links.php, and put that file into your /wp-content/plugins/ directory.
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

	function PostToPostLinks() {
		$this->config = array(
			// input can be 'checkbox', 'text', 'textarea', 'inline_textarea', 'hidden', or 'none'
			// datatype can be 'array' or 'hash'
			// can also specify input_attributes
			'make_quicktag' => array('input' => 'checkbox', 'default' => true,
					'label' => 'Enable quicktag button?',
					'help' => 'Add the quicktag button to the post editor toolbar?'),
			'before_text' => array('input' => 'text', 'default' => '"',
					'label' => 'Before link text',
					'help' => 'Text to appear before title of a referenced post'),
			'after_text' => array('input' => 'text', 'default' => '"',
					'label' => 'After link text',
					'help' => 'Text to appear after title of a referenced post'),
			'enable_legacy' => array('input' => 'checkbox', 'default' => false,
					'label' => 'Enable legacy tag support?',
					'help' => 'Enable support for pre-2.0 post-to-post tag syntax of <code>&lt;!--post="24"--></code>?<br />
							 Check this if you have used an older version of this plugin and thus used the older syntax.')
		);

		add_action('admin_menu', array(&$this, 'admin_menu'));		
		add_filter('the_content', array(&$this, 'post_to_post_link'), 9);
		add_filter('the_content_rss', array(&$this, 'post_to_post_link'), 9);
		add_filter('get_the_excerpt', array(&$this, 'post_to_post_link'), 9);
	}

	function install() {
		$this->options = $this->get_options();
		update_option($this->admin_options_name, $this->options);
	}

	function admin_menu() {
		if ( $this->show_admin )
			add_options_page('Easy Post-to-Post Links', 'Post2Post', 9, basename(__FILE__), array(&$this, 'options_page'));
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
				$options[$opt] = stripslashes($_POST[$opt]);
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
			<p>Easily reference another post in your blog using a shortcut, either by id or post slug.  The shortcut is replaced with the hyperlinked title of the referenced post.</p>
			
			<form name="post_to_post_links" action="$action_url" method="post">	
END;
				wp_nonce_field($this->nonce_field);
		echo '<fieldset class="option"><table width="100%" cellspacing="2" cellpadding="5" class="optiontable editform">';
				foreach (array_keys($options) as $opt) {
					$input = $this->config[$opt]['input'];
					if ($input == 'none') continue;
					$label = $this->config[$opt]['label'];
					$value = htmlspecialchars($options[$opt]);
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
						if ($label) echo "<strong>$label :</strong><br />";
						echo "<textarea name='$opt' id='$opt' {$this->config[$opt]['input_attributes']}>" . $value . '</textarea>';
					} else {
						echo "<th width='50%' scope='row'>$label : </th><td>";
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
			</fieldset>
			<input type="hidden" name="submitted" value="1" />
			<div class="submit"><input type="submit" name="Submit" value="Update Options &raquo;" /></div>
		</form>
			</div>
END;
		echo <<<END
		<div class='wrap' style="text-align:center; color:#888;">This plugin brought to you by <a href="http://coffee2code.com" title="coffee2code.com">Scott Reilly, aka coffee2code</a>.<br /><span style="font-size:x-small;"><a href="http://coffee2code.com/donate">Did you find this plugin useful?</a></span></div>
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