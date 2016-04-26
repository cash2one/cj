<?php
/**
 * h5发布权限
 * @date: 2015年5月8日
 * @author: kk
 * @version:
 */
class voa_s_oa_nvote_issue extends voa_s_abstract {
	
	public function __construct() {
		
		parent::__construct();

	}

	/**
	 * 验证用户ID
	 * @param int $m_uid
	 * @return boolean
	 */
	public function validator_m_uid($m_uid) {
		if ($m_uid < 1) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::M_UID_ERROR, $m_uid);
		}
		return true;
	}
	
	/**
	 * 验证部门id
	 * @param int $ca_id
	 * @return false|boolean
	 */
	public function validator_ca_id($ca_id) {
		if($ca_id < 1) {
		    return voa_h_func::throw_errmsg(voa_errcode_oa_news::CD_IDS_ERROR,$ca_id);
		}
		return true;
	}
}
