<?php
/**
 * @Author: ppker
 * @Date:   2015-10-19 18:07:02
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-10-21 23:39:34
 */

class voa_c_cyadmin_company_operationrecord_list extends voa_c_cyadmin_company_base {

	public function execute() {

		// 首先获取数据
		$act = $this->_user['ca_job'];
		$page = $this->request->get( 'page' );
		// $user = $this->_user;
		$uid = (int)$this->_user['ca_id'];
		if(empty($uid)) $this->message('error', '数据异常，非法操作！');
		$uda = &uda::factory('voa_uda_cyadmin_company_operationrecord');
		$data = array();
		if (2 == $act) { // 符合条件		
			$multi    = '';
			$record_list = array();
			$total    = '';
			$uda->getlist($page, $record_list, $multi, $total, $uid);
			$uda->format( $record_list, $data, $this->_adminer); // 格式化数据
			$this->view->set( 'data', $data );
			$this->view->set( 'multi', $multi );
			$this->view->set( 'total', $total );
		}

		$this->view->set('uid', $uid);

		$this->output('cyadmin/company/operationrecord/list');
		return true;
	}

}
