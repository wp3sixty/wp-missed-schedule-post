<?php
/**
 * Class MainTest
 *
 * @package Wp_Missed_Schedule_Posts
 */

/**
 * Sample test case.
 */
class MainTest extends WP_UnitTestCase {

	function __construct() {
		$this->post = new WP_UnitTest_Factory_For_Post( $this );
	}

	
	/**
	 * A single example test.
	 */
	public function test_check_publish() {
		$newtimestamp = date('Y-m-d H:i:s',  strtotime(current_time( 'mysql', 0 ) . ' + 1 minutes') );
		$p = $this->factory->post->create( array( 'post_title' => 'Test Post', 'post_date' => $newtimestamp, 'post_status' => 'future' ) );
		$post= get_post($p);
		var_dump(current_time( 'mysql', 0 ));
		var_dump($post->post_date);
		$this->assertEquals( 'future',$post->post_status );
		sleep(60);
		nv_wpmsp_init();
		$post= get_post($p);
		$this->assertEquals( $post->post_status, 'publish' );
	}
}
