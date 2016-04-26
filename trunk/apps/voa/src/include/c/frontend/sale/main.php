<?php
/**
 * 销售管理-首页
 * $Author$ tim_zhang
 * $Id$
 */

class voa_c_frontend_sale_main extends voa_c_frontend_sale_base {

	public function execute() {
		//获取用户信息
		$user = voa_h_user::get(startup_env::get('wbs_uid'));
		$face = voa_h_user::avatar(startup_env::get('wbs_uid'), $user);
		$this->view->set('navtitle', '销售管理');
		$this->view->set('name', $user['m_username']);
		//获取当前用户的职位 如果存在
		$job = '';
		if (!empty($user['cj_id'])) {
			$jobs = voa_h_cache::get_instance()->get('job', 'oa');
			if (!empty($jobs[$user['cj_id']])) {
				$job = $jobs[$user['cj_id']]['cj_name'];
			}
		}


		//获取公司
		$company = '';
		$departments = voa_h_cache::get_instance()->get('department', 'oa');
		foreach ($departments as $d) {
			if ($d['cd_upid'] == 0) {
				$company = $d['cd_name'];
				break;
			}
		}
		$this->view->set('job', $job);
		$this->view->set('company', $company);
		$this->view->set('face', $face);
		
		// 引入应用模板
		$this->_output('mobile/'.$this->_plugin_identifier.'/main');
		
		return true;

	}

}
