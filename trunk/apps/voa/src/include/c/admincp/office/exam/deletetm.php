<?php
/**
* 删除题目
* Create By wogu
* $Author$
* $Id$
*/
class voa_c_admincp_office_exam_deletetm extends voa_c_admincp_office_exam_base {

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
			$this->message('error', '请指定要删除的题目');
		}
		$uda = &uda::factory('voa_uda_frontend_exam_ti');
		if ($uda->delete_ti($ids)) {
			$this->message('success', '指定题目删除完毕', $_SERVER['HTTP_REFERER']);
		} else {
			$this->message('error', '指定题目删除失败');
		}
	}

}
