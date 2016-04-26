<?php
class voa_c_cyadmin_content_join_delete extends voa_c_cyadmin_content_join_base {

	public function execute() {
		$uda = &uda::factory('voa_uda_cyadmin_content_join_list');
		$ids = 0;
		$delete = $this->request->get('delete');
		$jid = $this->request->get('jid');
		if ($delete) {
			$ids = rintval($delete, true);
		} elseif ($jid) {
			$ids = rintval($jid, false);
			if (!empty($ids)) {
				$ids = array(
					$ids 
				);
			}
		}
		
		if (empty($ids)) {
			$this->message('error', '请指定要删除的 数据');
		}
		$result = $uda->del_job($ids);
		if ($result === true) {
			$this->message('success', '指定信息删除完毕', $this->cpurl($this->_module, 'join', 'list'), false);
		} else {
			$this->message('error', '指定信息删除操作失败');
		}
	}
}
