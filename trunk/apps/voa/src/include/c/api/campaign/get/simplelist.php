<?php
/**
 * 简单活动列表,只返回id,subject,供select使用
 * $Author$
 * $Id$
 */
class voa_c_api_campaign_get_simplelist extends voa_c_api_campaign_base {

	public function execute() {

		// 1.读所有部门权限
		$where = "is_all = 1";

		// 2.根据部门id读活动列表
		$rithg = new voa_d_oa_campaign_right();
		$cd_id = $this->_member['cd_id'];
		$rights_list = $rithg->list_by_conds(array('depid' => $cd_id));
		$actids = array_column($rights_list, 'actid');
		if ($actids) {
			$where .= " OR id in(" . implode(',', $actids) . ")";
		}

		$where = "($where)";

		// 3.筛选状态
		$where .= " AND status < 3";

		// 4.剔除过期活动
		$where .= " AND overtime > " . time();

		$sql = "SELECT * FROM {campaign} WHERE $where";
		$list = $this->db->getAll($sql);
		$return = array();
		foreach ($list as & $l) {
			$return[$l['id']] = $l['subject'];
		}

		/* 输出结果 */
		$this->_result = $return;

		return true;
	}
}
