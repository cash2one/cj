<?php
/**
* voa_c_admincp_office_jobtrain_view
* Create By wowxavi
* $Author$
* $Id$
*/
class voa_c_admincp_office_jobtrain_view extends voa_c_admincp_office_jobtrain_base {

	public function execute() {
		$uda_cata = &uda::factory('voa_uda_frontend_jobtrain_category');
		$uda = &uda::factory('voa_uda_frontend_jobtrain_article');
		$id = $this->request->get('id');
		$catas = $uda_cata->list_cata(false);
		// 读取内容
		$result = $uda->get_article($id);

		$cata = $catas[$result['cid']];

		if(!empty($cata['cd_ids'])) {
			$serv_d = &service::factory('voa_s_oa_common_department');
			$depms = $serv_d->fetch_all_by_key(explode(',', $cata['cd_ids']));
			foreach($depms as $k => $v) {
				$departments[] = $v['cd_name'];
			}
			$this->view->set('departments', implode(',', $departments));
		}

		if(!empty($cata['m_uids'])) {
			$serv_m = &service::factory('voa_s_oa_member');
			$users = $serv_m->fetch_all_by_ids(explode(',', $cata['m_uids']));
			foreach($users as $k => $v) {
				$members[] = $v['m_username'];
			}

			$this->view->set('members', implode(',', $members));
		}
		
		$this->view->set('secret_id', config::get('voa.jobtrain.secret_id')); // 设置视频 secret_id
		$this->view->set('app_id', config::get('voa.jobtrain.app_id'));
		$this->view->set('domain', $this->_setting['domain']); // 设置域名
		$this->view->set('result', $result);
		$this->view->set('catas', $catas);
		$this->view->set('types', voa_d_oa_jobtrain_article::$TYPES);
		$this->view->set('edit_url', $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id));
		$this->view->set('del_url', $this->cpurl($this->_module, $this->_operation, 'del', $this->_module_plugin_id));
		$this->output('office/jobtrain/view');
	}

}