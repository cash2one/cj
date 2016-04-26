<?php
/**
 * 活动->编辑报名信息
 * $Author$
 * $Id$
 */
class voa_c_api_campaign_post_custom extends voa_c_api_campaign_base {

	public function execute() {

		$id = intval($_POST['id']);
		$custom = isset($_POST['custom']) ? $_POST['custom'] : array();
		foreach ($custom as & $c) {
			$c = strip_tags($c); // 过滤标签
		}

		if (! $id) {
			$this->_set_errcode('无效的活动id');
			return false;
		}

		// 创建自字义字段记录(不重复创建)
		$uda = new voa_uda_frontend_campaign_campaign();
		$rs = $uda->save_custom($id, $this->_member['m_uid'], $custom);

		$this->_result = $rs;
		return true;
	}
}
