<?php
/**
* 试卷设置
* Create By wogu
* $Author$
* $Id$
*/

class voa_c_admincp_office_exam_papersetting extends voa_c_admincp_office_exam_base {

	public function execute() {
		if ($this->_is_post()) {
			$data = $this->request->postx();

			$id = intval($data['id']);
			try {
				$args = array(
					'id' => $id,
					'agentid' => $this->_module_plugin['cp_agentid'],
					'domain' => $this->_setting['domain']
				);
				$result = array();
				$uda = &uda::factory('voa_uda_frontend_exam_paper');
				$uda->edit_setting($data, $result, $args);
			} catch (help_exception $h) {
				$this->_admincp_error_message($h);
			} catch (Exception $e) {
				logger::error($e);
				$this->_admincp_system_message($e);
			}


			if($data['submitype']=='goback') {
				$action = ($result['type'] == 0 && $result['tiku'] != '0') || $result['type'] == 1 ? 'paperdetail' : 'addpaper';
				$this->message('success', '返回上一步', $this->cpurl($this->_module, $this->_operation, $action, $this->_module_plugin_id, array('id' => $result['id'])));
			} else if($data['submitype']=='preview') {
				// 预览
				$this->redirect($this->cpurl($this->_module, $this->_operation, 'paperpreview', $this->_module_plugin_id, array('id' => $id), false ));
			} else {
				$this->message('success', '设置试卷详细信息成功', $this->cpurl($this->_module, $this->_operation, 'paperlist', $this->_module_plugin_id));
			}
			
		}

		$id = intval($this->request->get('id'));
		if(!$id) {
			$this->_error_message('参数错误');
		}

		$s_paper = new voa_s_oa_exam_paper();
		$paper = $s_paper->get_by_id($id);
		if(!$paper) {
			$this->_error_message('试卷不存在');
		}

		if(!empty($paper['cd_ids'])) {
			$serv_d = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
			$depms = $serv_d->fetch_all_by_key(explode(',', $paper['cd_ids']));
			foreach($depms as $k => $v) {
				$default_departments[] = array(
					'id' => $k,
					'cd_name' => $v['cd_name'],
					'isChecked' => (bool)true,
				);
			}

			$this->view->set('default_departments', rjson_encode(array_values($default_departments)));
		}

		if(!empty($paper['m_uids'])) {
			$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			$users = $serv_m->fetch_all_by_ids(explode(',', $paper['m_uids']));
			foreach($users as $k => $v) {
				$default_users[] = array(
					'm_uid' => $v['m_uid'],
					'm_username' => $v['m_username'],
					'selected' => (bool)true,
				);
			}

			$this->view->set('default_users', rjson_encode(array_values($default_users)));
		}
		$this->view->set('paper', $paper);
		$this->view->set('timestamp', startup_env::get('timestamp'));
		$this->view->set('preview_url', $this->cpurl($this->_module, $this->_operation, 'paperpreview', $this->_module_plugin_id, array('id' => '')));
		$this->output('office/exam/paper_setting');
	}
}
