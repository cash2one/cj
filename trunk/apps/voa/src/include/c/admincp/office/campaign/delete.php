<?php
/**
 * 企业后台/微办公管理/活动/删除
 * Create By linshiling
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_campaign_delete extends voa_c_admincp_office_campaign_base {

	public function execute() {

		$act = new voa_s_oa_campaign();
		if (isset($_POST['delete'])) {
			// 批量删除
			$ids = array();
			foreach ($_POST['delete'] as $id) {
				$ids[] = intval($id);
			}

			if (! $ids) {
				$this->ajax(0, '请选择要删除的活动');
			}

			$rs = $act->del_act($ids);
		} else {
			// 单个删除
			$id = intval($_GET['id']);
			$rs = $act->del_act($id);
		}

		if ($rs) {
			$this->ajax(1);
		}

		$this->ajax(0, '删除失败');
	}
}
