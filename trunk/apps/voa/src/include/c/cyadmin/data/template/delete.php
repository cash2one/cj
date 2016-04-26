<?php

/**

 * $Author$
 * $Id$
 */
class voa_c_cyadmin_data_template_delete extends voa_c_cyadmin_base {

	public function execute() {
		$ids = 0;
		$delete = $this->request->post('delete');

		$acid = $this->request->get('ne_id');

		if ($delete) {
			$ids = rintval($delete, true);
		} elseif ($acid) {
			$ids = rintval($acid, false);
			if (!empty($ids)) {
				$ids = array($ids);
			}
		}

		if (empty($ids)) {
			$this->message('error', '请指定要删除的 ' . $this->_module_plugin['cp_name'] . ' 数据');
		}
		//删除
		$serv = &service::factory('voa_s_cyadmin_news_template');

		if ($serv->delete($ids)) {

			$this->message('success', '指定信息删除完毕', $this->cpurl($this->_module, $this->_operation, 'list'));
		} else {
			$this->message('error', '指定信息删除操作失败');
		}
	}

}
