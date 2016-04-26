<?php
/**
* 删除题库
* Create By wogu
* $Author$
* $Id$
*/
class voa_c_admincp_office_exam_deletetiku extends voa_c_admincp_office_exam_base {

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
			$this->message('error', '请指定要删除的题库');
		}
		$uda = &uda::factory('voa_uda_frontend_exam_tiku');
		if ($uda->delete_tiku($ids)) {
			$this->message('success', '指定题库删除完毕', $this->cpurl($this->_module, $this->_operation, 'tikulist', $this->_module_plugin_id));
		} else {
			$this->message('error', '指定题库删除失败');
		}
	}

}
