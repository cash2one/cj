<?php
/**
 * voa_c_admincp_office_news_delete
* 企业后台/微办公管理/新闻公告/删除
* Create By YanWenzhong
* $Author$
* $Id$
*/
class voa_c_admincp_office_news_delete extends voa_c_admincp_office_news_base {

	public function execute() {

		$ids = 0;
		$delete = $this->request->get('delete');
		$ne_id = $this->request->get('ne_id');

		if ($delete) {
			$ids = rintval($delete, true);
		} elseif ($ne_id) {
			$ids = rintval($ne_id, false);
			if (!empty($ids)) {
				$ids = array($ids);
			}
		}

		if (empty($ids)) {
			$this->message('error', '请指定要删除的 '.$this->_module_plugin['cp_name'].' 数据');
		}
		$uda = &uda::factory('voa_uda_frontend_news_delete');
		if ($uda->delete_news($ids)) {
			$this->message('success', '指定'.$this->_module_plugin['cp_name'].'信息删除完毕', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
		} else {
			$this->message('error', '指定'.$this->_module_plugin['cp_name'].'信息删除操作失败');
		}
	}

}
