<?php
/**
 * 新闻公告/分类
 * $Author$
 * $Id$
 */

class voa_s_oa_news_setting extends voa_s_abstract {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}


	/**
	 * 验证用户ID数组
	 * @param array $uids
	 * @return boolean
	 */
	public function validator_uids($uids){
		if (!is_array($uids)) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::U_IDS_ERROR, $uids);
		}
		return true;
	}
	/**
	 * 验证部门ID数组
	 * @param array $uids
	 * @return boolean
	 */
	public function validator_cdids($cdids){
		if (!is_array($cdids)) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::CD_IDS_ERROR, $cdids);
		}
		return true;
	}

}
