<?php

/**
 * voa_c_admincp_office_express_view
 * 企业后台/快递助手/快递详情
 * Create By gaosong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_express_view extends voa_c_admincp_office_express_base
{

	public function execute()
	{
		$eid = rintval($this->request->get('eid'));

		//读取快递基本信息
		$uda_express = &uda::factory('voa_uda_frontend_express_view');
		$express = array();
		if (!$uda_express->execute(array('eid' => $eid), $express)) {
			$this->_error_message($uda_express->errmsg);
			return true;
		}

		// 读取快递扩展信息
		$uda_express_mem = &uda::factory('voa_uda_frontend_express_mem_list');
		$express_mem = array();
		$conds = array(
			'eid' => $eid
		);
		if (!$uda_express_mem->execute($conds, $express_mem)) {
			$this->_error_message($express_mem->errmsg);
			return true;
		}

		// 整理数据
		$uids = array();
		foreach ($express_mem as $_k => $_p) {
			if (voa_d_oa_express_mem::COLLECTION == $_p['flag']) {//设置代领人姓名
				$express['c_username'] = $_p['username'];
				continue;
			}elseif (voa_d_oa_express_mem::RECEIVE == $_p['flag']) {
				$express['r_username'] = $_p['username'];//设置接收人姓名
				continue;
			}
		}
		unset($_p);
		
		// 附件
		$ids = explode(",", $express['at_id']);
		$attach = array();
		foreach ($ids as $_v) {
			if (! empty($_v)) {
				$attach[]['aid'] = $_v;
			}
		}

		$this->view->set('attachs', $attach); // 附件
		$this->view->set('express', $express); //快递信息
		$this->output('office/express/view');
	}
}
