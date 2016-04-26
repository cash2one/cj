<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 16/1/30
 * Time: 下午2:06
 */

class voa_c_cyadmin_enterprise_stat_company extends voa_c_cyadmin_enterprise_base {

	public function execute() {

		$s_time = $this->request->get('s_time');
		$e_time = $this->request->get('e_time');
		$act = $this->request->get('act');
		$type = $this->request->get('type');
		$ca_id = $this->request->get('ca_id');

		if (empty($ca_id)) {
			$ca_id = 0;
		}
		if (empty($type)) {
			$type = 'company';
		}
		$this->view->set('s_time', $s_time);
		$this->view->set('e_time', $e_time);
		$this->view->set('act', $act);
		$this->view->set('type', $type);
		$this->view->set('ca_id', $ca_id);

		$this->output('cyadmin/enterprise/stat/company');

		return true;
	}

}
