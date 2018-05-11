<?php

defined( 'ABSPATH' ) or die();

class Easy_Post_to_Post_Links_Test extends WP_UnitTestCase {

	public static function setUpBeforeClass() {
		c2c_EasyPostToPostLinks::get_instance()->install();
	}

	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();

		// Reset options
		c2c_EasyPostToPostLinks::get_instance()->reset_options();

		remove_shortcode( 'p2p' );

		remove_filter( 'c2c_post2post_shortcode', array( $this, 'change_post2post_shortcde' ) );
	}


	//
	//
	// DATA PROVIDERS
	//
	//


	//
	//
	// HELPER FUNCTIONS
	//
	//


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


	//
	//
	// TESTS
	//
	//


	public function test_class_exists() {
		$this->assertTrue( class_exists( 'c2c_EasyPostToPostLinks' ) );
	}

	public function test_plugin_framework_class_name() {
		$this->assertTrue( class_exists( 'c2c_EasyPostToPostLinks_Plugin_047' ) );
	}

	public function test_plugin_framework_version() {
		$this->assertEquals( '047', c2c_EasyPostToPostLinks::get_instance()->c2c_plugin_version() );
	}

	public function test_version() {
		$this->assertEquals( '4.2', c2c_EasyPostToPostLinks::get_instance()->version() );
	}

	public function test_instance_object_is_returned() {
		$this->assertTrue( is_a( c2c_EasyPostToPostLinks::get_instance(), 'c2c_EasyPostToPostLinks' ) );
	}

	/*
	 * Test shortcode
	 */

	public function test_shortcode_with_valid_id() {
		$post_id = $this->factory->post->create( array( 'post_title' => 'Sample post' ) );

		$this->assertEquals( $this->get_link( $post_id ), do_shortcode( '[post2post id="' . $post_id . '"]' ) );
	}

	public function test_shortcode_with_valid_slug() {
		$post_id = $this->factory->post->create( array( 'post_title' => 'Sample post' ) );

		$this->assertEquals( $this->get_link( $post_id ), do_shortcode( '[post2post id="sample-post"]' ) );
	}

	public function test_shortcode_with_empty_id() {
		$this->assertEmpty( do_shortcode( '[post2post id=""]' ) );
	}

	public function test_shortcode_with_invalid_id() {
		$this->assertEmpty( do_shortcode( '[post2post id="555"]' ) );
	}

	public function test_shortcode_with_invalid_non_int_id() {
		$this->assertEmpty( do_shortcode( '[post2post id="cat"]' ) );
	}

	public function test_shortcode_with_invalid_no_id() {
		$this->assertEmpty( do_shortcode( '[post2post cat="555"]' ) );
	}

	public function test_shortcode_with_before_text_and_after_text() {
		$post_id = $this->factory->post->create( array( 'post_title' => 'Sample post' ) );

		$this->assertEquals(
			$this->get_link( $post_id, '(', ')' ),
			do_shortcode( '[post2post id="' . $post_id . '" before="(" after=")"]' )
		);
	}

	public function test_shortcode_with_before_text_and_after_text_both_a_space() {
		$post_id = $this->factory->post->create( array( 'post_title' => 'Sample post' ) );

		$this->assertEquals(
			$this->get_link( $post_id, ' ', ' ' ),
			do_shortcode( '[post2post id="' . $post_id . '" before=" " after=" "]' )
		);
	}

	public function test_shortcode_with_content() {
		$post_id = $this->factory->post->create( array( 'post_title' => 'Sample post' ) );

		$this->assertEquals(
			$this->get_link( $post_id, '"', '"', 'link to the post' ),
			do_shortcode( '[post2post id="' . $post_id . '"]link to the post[/post2post]' )
		);
	}

	public function test_shortcode_with_blank_settings_before_text_and_after_text() {
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

	public function test_filter_c2c_post2post_shortcode() {
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

	public function test_legacy_with_valid_id_not_recognized_if_not_enabled() {
		$post_id = $this->factory->post->create( array( 'post_title' => 'Sample post' ) );

		$this->assertEquals(
			'<!--post="' . $post_id . '"-->',
			c2c_EasyPostToPostLinks::get_instance()->post_to_post_link( '<!--post="' . $post_id . '"-->' )
		);
	}

	public function test_legacy_with_valid_id() {
		$this->set_option( array( 'enable_legacy' => true ) );
		$post_id = $this->factory->post->create( array( 'post_title' => 'Sample post' ) );

		$this->assertEquals(
			$this->get_link( $post_id ),
			c2c_EasyPostToPostLinks::get_instance()->post_to_post_link( '<!--post="' . $post_id . '"-->' )
		);
	}

	public function test_legacy_with_valid_id_and_spaces() {
		$this->set_option( array( 'enable_legacy' => true ) );
		$post_id = $this->factory->post->create( array( 'post_title' => 'Sample post' ) );

		$this->assertEquals(
			$this->get_link( $post_id ),
			c2c_EasyPostToPostLinks::get_instance()->post_to_post_link( '<!--  post  =  "' . $post_id . '"  -->' )
		);
	}

	public function test_legacy_with_valid_slug() {
		$this->set_option( array( 'enable_legacy' => true ) );
		$post_id = $this->factory->post->create( array( 'post_title' => 'Sample post' ) );

		$this->assertEquals(
			$this->get_link( $post_id ),
			c2c_EasyPostToPostLinks::get_instance()->post_to_post_link( '<!--post="sample-post"-->' )
		);
	}

	public function test_legacy_with_valid_id_and_text() {
		$this->set_option( array( 'enable_legacy' => true ) );
		$post_id = $this->factory->post->create( array( 'post_title' => 'Sample post' ) );

		$this->assertEquals(
			$this->get_link( $post_id, '"', '"', 'this post' ),
			c2c_EasyPostToPostLinks::get_instance()->post_to_post_link( '<!--post="' . $post_id . '" text="this post"-->' )
		);
	}

	public function test_legacy_with_empty_id() {
		$this->set_option( array( 'enable_legacy' => true ) );

		$this->assertEquals(
			'<!--post=""-->',
			c2c_EasyPostToPostLinks::get_instance()->post_to_post_link( '<!--post=""-->' )
		);
	}

	public function test_legacy_with_invalid_id() {
		$this->set_option( array( 'enable_legacy' => true ) );

		$this->assertEmpty(
			c2c_EasyPostToPostLinks::get_instance()->post_to_post_link( '<!--post="555"-->' )
		);
	}

	public function test_legacy_with_invalid_non_int_id() {
		$this->set_option( array( 'enable_legacy' => true ) );

		$this->assertEmpty(
			c2c_EasyPostToPostLinks::get_instance()->post_to_post_link( '<!--post="cat"-->' )
		);
	}

	/*
	 * Setting handling
	 */

	public function test_does_not_immediately_store_default_settings_in_db() {
		$option_name = c2c_EasyPostToPostLinks::SETTING_NAME;
		// Get the options just to see if they may get saved.
		$options     = c2c_EasyPostToPostLinks::get_instance()->get_options();

		$this->assertFalse( get_option( $option_name ) );
	}

	public function test_uninstall_deletes_option() {
		$option_name = c2c_EasyPostToPostLinks::SETTING_NAME;
		$options     = c2c_EasyPostToPostLinks::get_instance()->get_options();

		// Explicitly set an option to ensure options get saved to the database.
		$this->set_option( array( 'auto_remember_me' => '1' ) );

		$this->assertNotEmpty( $options );
		$this->assertNotFalse( get_option( $option_name ) );

		c2c_EasyPostToPostLinks::uninstall();

		$this->assertFalse( get_option( $option_name ) );
	}

}
