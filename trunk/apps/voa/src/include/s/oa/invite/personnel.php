<?php

/**
 * personnel.php
 *
 * Created by zhoutao.
 * Created Time: 2015/7/8  17:03
 */
class voa_s_oa_invite_personnel extends voa_s_abstract {
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 验证用姓名
	 *
	 * @param string
	 *
	 * @return booleannt
	 */

	public function validator_name( $name ) {

		$name = trim( $name );
		if( ! validator::is_required( $name ) ) {
			return voa_h_func::throw_errmsg( voa_errcode_oa_invite::NAME_NULL, $name );
		}

		return true;
	}

}
