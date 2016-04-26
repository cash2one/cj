<?php
/**
 * 新闻公告审核表
 * voa_s_oa_news_check
 * @date: 2015年5月8日
 * @author: kk
 * @version:
 */
class voa_s_oa_news_check extends voa_s_abstract {
	protected $_d_class = null;

	public function __construct() {
		parent::__construct();
		if ($this->_d_class == null) {
			$this->_d_class = new voa_d_oa_news_check();
		}
	}

	/**
	 * 判断是否是审批人
	 * @param integer $ne_id 公告id
	 * @param integer $us_id 用户id
	 * @return boolean
	 */
	public function is_check($ne_id, $us_id) {
		return $this->_d_class->is_check($ne_id, $us_id);
	}

	/**
	 * 获取审核人列表
	 * @param unknown $ne_id
	 * @return Ambigous
	 */
	public function list_check_users($ne_id) {
		return $this->_d_class->list_by_conds(array('news_id' => $ne_id));
	}

	/**
	 * 物理删除权限记录
	 * @param array $conds
	 */
	public function delete_real_records_by_conds($conds) {
		return $this->_d_class->delete_real_records_by_conds($conds);
	}

	/**
	 * 验证公告ID
	 * @param int $ne_id
	 * @return boolean
	 */
	public function validator_newid($ne_id){
		if ($ne_id < 1) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::NE_ID_ERROR, $ne_id);
		}
		return true;
	}

	/**
	 * 验证用户ID
	 * @param int $ne_id
	 * @return boolean
	 */
	public function validator_uid($m_uid){
		if ($m_uid < 1) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::M_UID_CHECK, $m_uid);
		}
		return true;
	}
}
