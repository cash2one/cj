<?php
/**
 * voa_s_oa_talk_lastview
 * 访客操作
 *
 * $Author$
 * $Id$
 */

class voa_s_oa_talk_lastview extends voa_s_abstract {


	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	public function newguest($uid) {

		$lastview = &service::factory('voa_d_oa_talk_lastview');
		$conds = array(
			'uid'	=>	$uid,
			'newct>?'	=>	0,
		);
		$count = $lastview->count_by_conds($conds);
		return $count;
	}
}

