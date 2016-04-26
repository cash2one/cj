<?php
/**
* 删除试卷
* Create By wogu
* $Author$
* $Id$
*/
class voa_c_admincp_office_exam_deletepaper extends voa_c_admincp_office_exam_base {

	public function execute() {
		$ids = 0;
		$delete = $this->request->get('delete');
		$id = $this->request->get('id');

		if ($delete) {
			$ids = rintval($delete, true);
		} elseif ($id) {
			$ids = rintval($id, false);
			if (!empty($ids)) {
				$ids = array($ids);
			}
		}
		if (empty($ids)) {
			$this->message('error', '请指定要删除的试卷');
		}
		$uda = &uda::factory('voa_uda_frontend_exam_paper');
		if ($uda->delete_paper($ids)) {
			$this->message('success', '指定试卷删除完毕', $this->cpurl($this->_module, $this->_operation, 'paperlist', $this->_module_plugin_id));
		} else {
			$this->message('error', '指定试卷删除失败');
		}
	}

}
