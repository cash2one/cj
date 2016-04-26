<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/2/23
 * Time: 下午6:05
 */

class voa_c_cyadmin_enterprise_stat_plugin extends voa_c_cyadmin_enterprise_base {

	public function execute() {
		$s_time = $this->request->get('s_time');
		$e_time = $this->request->get('e_time');
		$select_identifier = $this->request->get('select_identifier');

		$this->view->set('s_time', $s_time);
		$this->view->set('e_time', $e_time);
		$this->view->set('select_identifier', $select_identifier);

//		// 默认tab
//		$default_tab = $this->request->get('tab');
//		$act_array = array(
//			'plugin',
//			'ep_install',
//		);
//
//		if (empty($act) || !in_array($default_tab, $act_array)) {
//			$get = 'plugin';
//		}
//
//		$this->view->set('tab', $default_tab);
		$this->output('cyadmin/enterprise/stat/plugin');

		return true;
	}

}
