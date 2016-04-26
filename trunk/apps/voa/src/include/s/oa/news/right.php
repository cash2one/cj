<?php
/**
 * 新闻公告/权限
 * $Author$
 * $Id$
 */

class voa_s_oa_news_right extends voa_s_abstract {

	protected $_d_class;
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
		if ($this->_d_class == null) {
			$this->_d_class = new voa_d_oa_news_right();
		}
	}

	/**
	 * 取得一篇公告的权限（剔除所有人可见权限）
	 * @param int $ne_id
	 * @return array
	 */
	public function list_rights_for_single_news($ne_id) {
		return $this->_d_class->list_rights_for_single_news($ne_id);
	}

	/**
	 * 取得单个用户有阅读权限的公告
	 * @param int $nca_id
	 * @param int $m_uid
	 * @param int $start
	 * @param int $limit
	 * @return boolean|multitype:
	 */
	public  function list_rights_for_single_user($nca_id, $m_uid) {
		return $this->_d_class->list_rights_for_single_user($nca_id, $m_uid);
	}

	/**
	 * 判断用户是否有阅读某篇公告的权限
	 * @param int $nca_id
	 * @param int $m_uid
	 * @param int $start
	 * @param int $limit
	 * @return boolean|multitype:
	 */
	public  function confirm_right_for_user($ne_id, $m_uid) {
		return $this->_d_class->confirm_right_for_user($ne_id, $m_uid);
	}

	/**
	 * 物理删除权限记录
	 * @param array $conds
	 */
	public function delete_real_records_by_conds($conds) {
		return $this->_d_class->delete_real_records_by_conds($conds);
	}
}
