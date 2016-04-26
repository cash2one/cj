<?php
/**
 * voa_uda_frontend_inspect_score_rank
 * 统一数据访问/巡店/获取分数排行列表
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_inspect_score_rank extends voa_uda_frontend_inspect_score_abstract {

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 输入参数
	 * @param array $in 输入参数
	 * @param array &$out 输出参数
	 * @return boolean
	 */
	public function execute($in, &$out) {

		$this->_params = $in;
		// 查询表格的条件
		$fields = array(
			array('ymd_s', self::VAR_INT, null, null, true),
			array('ymd_e', self::VAR_INT, null, null, true),
			array('page', self::VAR_INT, null, null, true),
			array('perpage', self::VAR_INT, null, null, true),
			'_uids' => array('b.m_uid', self::VAR_ARR, null, null, false),
			'insi_id' => array('a.insi_id', self::VAR_INT, null, null, true),
			'_cr_ids' => array('a.cr_id', self::VAR_ARR, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		// 如果 insi_id 不存在
		if (!isset($conds['insi_id'])) {
			$conds['insi_id'] = 0;
		}

		// 如果 ymd 为空
		if (empty($conds['ymd_s']) || empty($conds['ymd_e'])) {
			$conds['ymd_e'] = rgmdate(startup_env::get('timestamp'), 'Ymd');
			$conds['ymd_s'] = rgmdate(startup_env::get('timestamp') - 7 * 86400, 'Ymd');
		}

		if ($conds['ymd_e'] < $conds['ymd_s']) {
			list($conds['ymd_e'], $conds['ymd_s']) = array($conds['ymd_s'], $conds['ymd_e']);
		}

		$conds['a.isr_date>?'] = $conds['ymd_s'] - 1;
		$conds['a.isr_date<?'] = $conds['ymd_e'] + 1;
		unset($conds['ymd_s'], $conds['ymd_e']);

		// 分页信息
		$this->_get_page_option($option, $conds);

		// 读取表格字段
		$out = $this->_serv->list_rank_join_mem($conds, $option);
		if (empty($out)) {
			$out = array();
		}

		return true;
	}

}
