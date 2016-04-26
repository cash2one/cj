<?php
/**
 * 重置执行.
 * User: Muzhitao
 * Date: 2015/10/19 0019
 * Time: 15:45
 * Email：muzhitao@vchangyi.com
 */

class voa_c_admincp_office_interface_reset extends voa_c_admincp_office_interface_base {

	public function execute() {

		// 获取参数
		$f_ids = $this->request->getx();

		if (empty($f_ids['f_id'])) {
			$this->message('error', '请选择流程');
		}

		$serv = &uda::factory('voa_uda_frontend_interface_flowlist');

		$result = $serv->update_exec($f_ids);

		// 判断操作
		if ($result) {
			$this->_admincp_success_message('重置成功', $this->cpurl($this->_module, $this->_operation, 'flowlist', $this->_module_plugin_id));
		} else {
			$this->message('error', '重置失败');
		}

	}
}
