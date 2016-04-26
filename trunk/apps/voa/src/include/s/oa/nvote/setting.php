<?php
/**
 * voa_s_oa_nvote_setting
 * 投票调研-选项
 * User: luckwang
 * Date: 15/3/9
 * Time: 上午10:35
 */

class voa_s_oa_nvote_setting extends voa_s_abstract {

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
