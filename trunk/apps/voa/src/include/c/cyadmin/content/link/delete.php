<?php
class voa_c_cyadmin_content_link_delete extends voa_c_cyadmin_content_link_base {

	public function execute() {
		$uda = &uda::factory('voa_uda_cyadmin_content_link_list');
		$ids = 0;
		$delete = $this->request->get('delete');
		$lid = $this->request->get('lid');
		
		if ($delete) {
			$ids = rintval($delete, true);
		} elseif ($lid) {
			$ids = rintval($lid, false);
			if (!empty($ids)) {
				$ids = array(
					$ids 
				);
			}
		}
		
		if (empty($ids)) {
			$this->message('error', '请指定要删除的 数据');
		}
		
		$result = $uda->del_link($ids);
		if ($result === true) {
			$this->message('success', '指定信息删除完毕', $this->cpurl($this->_module, 'link', 'list'), false);
		} else {
			$this->message('error', '指定信息删除操作失败');
		}
	}
}
