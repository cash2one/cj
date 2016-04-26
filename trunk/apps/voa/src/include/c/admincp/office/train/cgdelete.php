<?php
/**
 * voa_c_admincp_office_train_atdelete
 * 企业后台/微办公管理/培训/删除目录
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_train_cgdelete extends voa_c_admincp_office_train_base {

	public function execute() {

		$ids = 0;
		$delete = $this->request->post('delete');
		$ta_id = $this->request->get('tc_id');

		if ($delete) {
			$ids = rintval($delete, true);
		} elseif ($ta_id) {
			$ids = rintval($ta_id, false);
			if (!empty($ids)) {
				$ids = array($ids);
			}
		}

		if (empty($ids)) {
			$this->message('error', '请指定要删除的 '.$this->_module_plugin['cp_name'].' 数据');
		}

		$uda_categorydelete = &uda::factory('voa_uda_frontend_train_action_categorydelete');

		//如果要删除的目录中有文章，则不让删除
		$uda_articlelist = &uda::factory('voa_uda_frontend_train_action_articlelist');
		foreach( $ids as $id ) {
			$count = $uda_articlelist->count_articles_by_category_id($id);
			if ($count > 0) { //只要其中一个目录 下有文章，则不让删除
				$this->message('error', voa_errcode_oa_train::DELETE_CATEGORY_FAILED1);
				return;
			}
		}

		//删除目录
		if ($uda_categorydelete->delete($ids)) {
			$this->message('success', '指定'.$this->_module_plugin['cp_name'].'信息删除完毕', get_referer($this->cpurl($this->_module, $this->_operation, 'cglist', $this->_module_plugin_id)), false);
		} else {
			$this->message('error', '指定'.$this->_module_plugin['cp_name'].'信息删除操作失败');
		}
	}

}
