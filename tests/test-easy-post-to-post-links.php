<?php

class Easy_Post_to_Post_Links_Test extends WP_UnitTestCase {

	function setUp() {
		parent::setUp();
		$this->set_option();

	}

	function tearDown() {
		parent::tearDown();

		remove_shortcode( 'p2p' );

		remove_filter( 'c2c_post2post_shortcode', array( $this, 'change_post2post_shortcde' ) );
	}



	/*
	 *
	 * DATA PROVIDERS
	 *
	 */



	/*
	 *
	 * HELPER FUNCTIONS
	 *
	 */



	private function set_option( $settings = array() ) {
		$defaults = array(
			'before_text'      => '"',
			'after_text'       => '"',
			'enable_legacy'    => false,
			'enable_legacy_v2' => false,
		);
		$settings = wp_parse_args( $settings, $defaults );
		c2c_EasyPostToPostLinks::get_instance()->update_option( $settings, true );
	}

	private function get_link( $post_id, $before = '"', $after = '"', $content = '' ) {
		return sprintf(
			$before . '<a href="%s" title="%s">%s</a>' . $after ,
			get_permalink( $post_id ),
			esc_attr( get_the_title( $post_id ) ),
			$content ? $content : get_the_title( $post_id )
		);
	}

	/**
	 *  Use a shorter shortcode: i.e. [p2p id="32"]
	 *
	 * Taken from readme.txt example.
	 *
	 * @param string  $shortcode The default shortcode name.
	 * @return string The new shortcode name.
	 */
	public function change_post2post_shortcde( $shortcode ) {
		return 'p2p';
	}


	/*
	 *
	 * TESTS
	 *
	 */


	function test_class_exists() {
		$this->assertTrue( class_exists( 'c2c_EasyPostToPostLinks' ) );
	}

	function test_plugin_framework_class_name() {
		$this->assertTrue( class_exists( 'C2C_Plugin_039' ) );
	}

	function test_version() {
		$this->assertEquals( '4.0', c2c_EasyPostToPostLinks::get_instance()->version() );
	}

	function test_instance_object_is_returned() {
		$this->assertTrue( is_a( c2c_EasyPostToPostLinks::get_instance(), 'c2c_EasyPostToPostLinks' ) );
	}

	/*
	 * Test shortcode
	 */

	function test_shortcode_with_valid_id() {
		$post_id = $this->factory->post->create( array( 'post_title' => 'Sample post' ) );

		$this->assertEquals( $this->get_link( $post_id ), do_shortcode( '[post2post id="' . $post_id . '"]' ) );
	}

	function test_shortcode_with_valid_slug() {
		$post_id = $this->factory->post->create( array( 'post_title' => 'Sample post' ) );

		$this->assertEquals( $this->get_link( $post_id ), do_shortcode( '[post2post id="sample-post"]' ) );
	}

	function test_shortcode_with_empty_id() {
		$this->assertEmpty( do_shortcode( '[post2post id=""]' ) );
	}

	function test_shortcode_with_invalid_id() {
		$this->assertEmpty( do_shortcode( '[post2post id="555"]' ) );
	}

	function test_shortcode_with_invalid_non_int_id() {
		$this->assertEmpty( do_shortcode( '[post2post id="cat"]' ) );
	}

	function test_shortcode_with_invalid_no_id() {
		$this->assertEmpty( do_shortcode( '[post2post cat="555"]' ) );
	}

	function test_shortcode_with_before_text_and_after_text() {
		$post_id = $this->factory->post->create( array( 'post_title' => 'Sample post' ) );

		$this->assertEquals(
			$this->get_link( $post_id, '(', ')' ),
			do_shortcode( '[post2post id="' . $post_id . '" before="(" after=")"]' )
		);
	}

	function test_shortcode_with_before_text_and_after_text_both_a_space() {
		$post_id = $this->factory->post->create( array( 'post_title' => 'Sample post' ) );

		$this->assertEquals(
			$this->get_link( $post_id, ' ', ' ' ),
			do_shortcode( '[post2post id="' . $post_id . '" before=" " after=" "]' )
		);
	}

	function test_shortcode_with_content() {
		$post_id = $this->factory->post->create( array( 'post_title' => 'Sample post' ) );

		$this->assertEquals(
			$this->get_link( $post_id, '"', '"', 'link to the post' ),
			do_shortcode( '[post2post id="' . $post_id . '"]link to the post[/post2post]' )
		);
	}

	function test_shortcode_with_blank_settings_before_text_and_after_text() {
		$this->set_option( array(
			'before_text' => '',
			'after_text'  => '',
		) );
		$post_id = $this->factory->post->create( array( 'post_title' => 'Sample post' ) );

		$this->assertEquals(
			$this->get_link( $post_id, '', '' ),
			do_shortcode( '[post2post id="' . $post_id . '"]' )
		);
	}

	function test_filter_c2c_post2post_shortcode() {
		add_filter( 'c2c_post2post_shortcode', array( $this, 'change_post2post_shortcde' ) );
		// Since unit tests don't facilitate hooking init just for a single test, manually
		// fire off the plugin's action that would normally fire and asusme the filter being
		// tested was hooked properly.
		c2c_EasyPostToPostLinks::get_instance()->load_config();
		c2c_EasyPostToPostLinks::get_instance()->register_filters();

		$post_id = $this->factory->post->create( array( 'post_title' => 'Sample post' ) );

		$this->assertEquals( $this->get_link( $post_id ), do_shortcode( '[p2p id="' . $post_id . '"]' ) );
	}

	/*
	 * Test legacy
	 */

	// TODO: Test filters are hooked when legacy enabled.

	function test_legacy_with_valid_id_not_recognized_if_not_enabled() {
		$post_id = $this->factory->post->create( array( 'post_title' => 'Sample post' ) );

		$this->assertEquals(
			'<!--post="' . $post_id . '"-->',
			c2c_EasyPostToPostLinks::get_instance()->post_to_post_link( '<!--post="' . $post_id . '"-->' )
		);
	}

	function test_legacy_with_valid_id() {
		$this->set_option( array( 'enable_legacy' => true ) );
		$post_id = $this->factory->post->create( array( 'post_title' => 'Sample post' ) );

		$this->assertEquals(
			$this->get_link( $post_id ),
			c2c_EasyPostToPostLinks::get_instance()->post_to_post_link( '<!--post="' . $post_id . '"-->' )
		);
	}

	function test_legacy_with_valid_id_and_spaces() {
		$this->set_option( array( 'enable_legacy' => true ) );
		$post_id = $this->factory->post->create( array( 'post_title' => 'Sample post' ) );

		$this->assertEquals(
			$this->get_link( $post_id ),
			c2c_EasyPostToPostLinks::get_instance()->post_to_post_link( '<!--  post  =  "' . $post_id . '"  -->' )
		);
	}

	function test_legacy_with_valid_slug() {
		$this->set_option( array( 'enable_legacy' => true ) );
		$post_id = $this->factory->post->create( array( 'post_title' => 'Sample post' ) );

		$this->assertEquals(
			$this->get_link( $post_id ),
			c2c_EasyPostToPostLinks::get_instance()->post_to_post_link( '<!--post="sample-post"-->' )
		);
	}

	function test_legacy_with_valid_id_and_text() {
		$this->set_option( array( 'enable_legacy' => true ) );
		$post_id = $this->factory->post->create( array( 'post_title' => 'Sample post' ) );

		$this->assertEquals(
			$this->get_link( $post_id, '"', '"', 'this post' ),
			c2c_EasyPostToPostLinks::get_instance()->post_to_post_link( '<!--post="' . $post_id . '" text="this post"-->' )
		);
	}

	function test_legacy_with_empty_id() {
		$this->set_option( array( 'enable_legacy' => true ) );

		$this->assertEquals(
			'<!--post=""-->',
			c2c_EasyPostToPostLinks::get_instance()->post_to_post_link( '<!--post=""-->' )
		);
	}

	function test_legacy_with_invalid_id() {
		$this->set_option( array( 'enable_legacy' => true ) );

		$this->assertEmpty(
			c2c_EasyPostToPostLinks::get_instance()->post_to_post_link( '<!--post="555"-->' )
		);
	}

	function test_legacy_with_invalid_non_int_id() {
		$this->set_option( array( 'enable_legacy' => true ) );

		$this->assertEmpty(
			c2c_EasyPostToPostLinks::get_instance()->post_to_post_link( '<!--post="cat"-->' )
		);
	}


	function test_uninstall_deletes_option() {
		$option = 'c2c_easy_post_to_post_links';
		c2c_EasyPostToPostLinks::get_instance()->get_options();

		$this->assertNotFalse( get_option( $option ) );

		c2c_EasyPostToPostLinks::uninstall();

		$this->assertFalse( get_option( $option ) );
	}

}
