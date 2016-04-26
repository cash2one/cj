<?php
class voa_c_cyadmin_content_train_delete extends voa_c_cyadmin_content_train_base {

	public function execute() {
		$uda = &uda::factory('voa_uda_cyadmin_content_train_list');
		$ids = 0;
		$delete = $this->request->get('delete');
		$tid = $this->request->get('tid');
		
		if ($delete) {
			$ids = rintval($delete, true);
		} elseif ($tid) {
			$ids = rintval($tid, false);
			if (!empty($ids)) {
				$ids = array(
					$ids 
				);
			}
		}
		
		if (empty($ids)) {
			$this->message('error', '请指定要删除的 数据');
		}
		
		if ($uda->del_train($ids)) {
			$this->message('success', '指定信息删除完毕', $this->cpurl($this->_module, 'train', 'list'), false);
		} else {
			$this->message('error', '指定信息删除操作失败');
		}
	}
}
