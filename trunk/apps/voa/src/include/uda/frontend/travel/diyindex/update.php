<?php
/**
 * voa_uda_frontend_travel_diyindex_update
 * 统一数据访问/旅游产品应用/更新指定自定义首页信息(单条)
 * $Author$
 * $Id$
 */

class voa_uda_frontend_travel_diyindex_update extends voa_uda_frontend_travel_abstract {

	public function __construct() {

		parent::__construct();
	}

	public function execute($in, &$out) {

		$this->_params = $in;
		$serv = new voa_s_oa_travel_diyindex();
		// 查询表格的条件
		$fields = array(
			array('tiid', self::VAR_INT, null, null, true),
			array('subject', self::VAR_STR, array($serv, 'chk_subject'), null, false),
			array('uid', self::VAR_INT, null, null, true),
			array('message', self::VAR_ARR, array($serv, 'chk_message'), null, false)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		// 主页内容
		$conds['message'] = is_array($conds['message']) ? $conds['message'] : array();
		$conds['message'] = serialize($conds['message']);

		if (!empty($conds['uid'])) {
			$serv_m = &service::factory('voa_s_oa_member');
			if ($member = $serv_m->fetch_by_uid($conds['uid'])) {
				$conds['username'] = $member['m_username'];
				$conds['related'] = 1;
			} else {
				$conds['uid'] = 0;
				$conds['username'] = '';
			}
		}

		// 读取记录
		if ($out = $serv->get($conds['tiid'])) {
			unset($conds['tiid']);
			$serv->update($out['tiid'], $conds);
		} else {
			$serv->insert($conds);
		}

		return true;
	}
}
