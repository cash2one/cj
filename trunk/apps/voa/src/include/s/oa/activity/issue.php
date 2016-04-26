<?php
/**
 * h5发布权限
 * @date: 2015年5月8日
 * @author: kk
 * @version:
 */
class voa_s_oa_activity_issue extends voa_s_abstract {

	protected $_d_class;
	
	public function __construct() {
		
		parent::__construct();
		if ($this->_d_class == null) {
			$this->_d_class = new voa_d_oa_activity_issue();
		}
	}
	
	/**
	 * 获取当前用户发布新闻的权限
	 * @param unknown $m_uid
	 * @return boolean
	 */
	public function get_html5_issue($m_uid) {
	    return $this->_d_class->get_html5_issue($m_uid);
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
