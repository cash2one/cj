<?php
/**
 * voa_uda_frontend_notice_search
 * 统一数据访问/通知公告/搜索条件
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_notice_search extends voa_uda_frontend_notice_base {

/** controller_request 实例 */
	protected $_request;

	public function __construct() {
		parent::__construct();
		$this->_request = controller_request::get_instance();
	}

	/**
	 * 构造用于检索查询的条件值
	 * @param array $search_by
	 * @param array $search_condition
	 * @param array $shard_key
	 * @return boolean
	 */
	public function notice_conditions(&$search_by, &$search_condition, $shard_key = array()) {

		// 搜索条件定义
		$search_default_field_define = array(
				'm_uid' => array(array(), 'array'),// 公告作者uid
				'm_username' => array('', 'string'),// 公告作者名
				'nt_subject' => array('', 'string'),// 公告标题
				'nt_author' => array('', 'string'),// 公告发布人
				'nt_tag' => array('', 'string'), // 公告标签、类别
				'nt_created_after' => array('', 'string', 'search_val_date'),// 公告发布日期：此时间之后（大于此时间）.
				'nt_created_before' => array('', 'string', 'search_val_date'),// 公告发布日期：此时间之前（小于此时间）.
		);

		// 真实有效的搜素条件
		$search_condition = array();

		// 搜索条件原型
		$search_by = array();
		foreach ($search_default_field_define as $k => $s) {
			$search_by[$k] = $s[0];
		}

		// 经过校验后的搜索条件数组
		$format_condition = array();
		if (!$this->_submit2search_condition($search_default_field_define, $format_condition, $search_by)) {
			return false;
		}

		// 没有提供有效的搜索条件
		if (empty($format_condition)) {
			return true;
		}

		// 搜索 m_uid
		if (isset($format_condition['m_uid'])) {
			$search_condition['m_uid'] = array($search_condition['m_uid']);
		}

		// 搜索公告发布者
		if (isset($format_condition['m_username']) && $format_condition['m_username'] !== '') {
			$search_condition['m_username'] = array('%'.addcslashes($format_condition['m_username'], '%_').'%', 'like');
		}

		// 搜索标题
		if (isset($format_condition['nt_tag']) && $format_condition['nt_tag'] !== '') {
			$search_condition['nt_tag'] = array('%'.addcslashes($format_condition['nt_tag'], '%_').'%', 'like');
		}

		// 搜索标题
		if (isset($format_condition['nt_subject']) && $format_condition['nt_subject'] !== '') {
			$search_condition['nt_subject'] = array('%'.addcslashes($format_condition['nt_subject'], '%_').'%', 'like');
		}

		// 搜索发布人
		if (isset($format_condition['nt_author']) && $format_condition['nt_author'] !== '') {
			$search_condition['nt_author'] = array('%'.addcslashes($format_condition['nt_author'], '%_').'%', 'like');
		}

		// 拜访时间最小值
		if (isset($format_condition['nt_created_after'])) {
			$search_condition['nt_created'] = array($format_condition['nt_created_after'], '>=');
		}

		// 拜访时间最大值
		if (isset($format_condition['nt_created_before'])) {
			$search_condition['nt_created_before'] = array($format_condition['nt_created_before'] + 86400, '<');
		}

		if (!empty($search_condition)) {
			$search_condition['nt_status'] = array(voa_d_oa_notice::STATUS_REMOVE, '<');
		}

		return true;
	}

}
