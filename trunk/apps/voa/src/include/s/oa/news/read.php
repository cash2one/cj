<?php
/**
 * 新闻公告/已读人数
 * $Author$
 * $Id$
 */

class voa_s_oa_news_read extends voa_s_abstract {

	protected $_d_class;
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
		if ($this->_d_class == null) {
			$this->_d_class = new voa_d_oa_news_read();
		}
	}

	/**
	 * 根据新闻公告ID数组查找已读人数
	 * @param array $ne_ids
	 * @return array
	 */
	public function list_read_numbers($ne_ids) {
		return $this->_d_class->list_read_numbers($ne_ids);
	}

	/**
	 * 根据新闻公告ID数组查找已读人员列表
	 * @param int $ne_id
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	public function list_read_users($ne_id, $start = 0, $limit = 0) {
		return $this->_d_class->list_read_users($ne_id, $start, $limit);
	}

	/**
	 * 根据新闻公告ID数组查找已读人员总数
	 * @param int $ne_id
	 * @return number
	 */
	public function count_read_users($ne_id) {
		return $this->_d_class->count_read_users($ne_id);
	}
	
	/**
	 * 物理删除阅读情况记录
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
	public function validator_ne_id($ne_id){
		if ($ne_id < 1) {  //验证是否合法，不合法则抛出错误信息
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::NE_ID_ERROR, $ne_id);
		}
		return true;
	}

	public function delete_by_ne_uid($ne_id, array $conds) {
		return $this->_d_class->delete_by_ne_uid($ne_id, $conds);
	}


}
