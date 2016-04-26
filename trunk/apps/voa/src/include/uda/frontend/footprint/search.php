<?php
/**
 * voa_uda_frontend_footprint_search
 * 统一数据访问/销售轨迹应用/搜索
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_footprint_search extends voa_uda_frontend_footprint_base {

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
	public function footprint_conditions(&$search_by, &$search_condition, $shard_key = array()) {

		// 搜索条件定义
		$search_default_field_define = array(
				'm_uid' => array(array(), 'array'),//轨迹所有人id
				'm_username' => array('', 'string'),//轨迹所有人名字
				'fp_subject' => array('', 'string'),//客户名称
				'fp_address' => array('', 'string'),//位置关键词
				'fp_type' => array('', 'string'),//轨迹分类.
				'fp_visittime_after' => array('', 'string', 'search_val_date'),//拜访时间范围：此时间之后（大于此时间）.
				'fp_visittime_before' => array('', 'string', 'search_val_date'),//拜访时间范围：此时间之前（小于此时间）.
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

		// 搜索轨迹发布者
		if (isset($format_condition['m_username']) && $format_condition['m_username'] !== '') {
			$search_condition['m_username'] = array('%'.addcslashes($format_condition['m_username'], '%_').'%', 'like');
		}

		// 搜索客户名称
		if (isset($format_condition['fp_subject']) && $format_condition['fp_subject'] !== '') {
			$search_condition['fp_subject'] = array('%'.addcslashes($format_condition['fp_subject'], '%_').'%', 'like');
		}

		// 位置关键词
		if (isset($format_condition['fp_address']) && $format_condition['fp_address'] !== '') {
			$search_condition['fp_address'] = array('%'.addcslashes($format_condition['fp_address'], '%_').'%', 'like');
		}

		// 轨迹分类
		if (isset($format_condition['fp_type']) && isset($this->_sets['types'][$format_condition['fp_type']])) {
			$search_condition['fp_type'] = $format_condition['fp_type'];
		}

		// 拜访时间最小值
		if (isset($format_condition['fp_visittime_after'])) {
			$search_condition['fp_visittime'] = array($format_condition['fp_visittime_after'], '>=');
		}

		// 拜访时间最大值
		if (isset($format_condition['fp_visittime_before'])) {
			$search_condition['fp_visittime_before'] = array($format_condition['fp_visittime_before'] + 86400, '<');
		}

		if (!empty($search_condition)) {
			$search_condition['fp_status'] = array(voa_d_oa_footprint::STATUS_REMOVE, '<');
		}

		return true;
	}

}
