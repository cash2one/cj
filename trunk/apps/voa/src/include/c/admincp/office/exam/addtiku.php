<?php
/**
* 增加题库
* Create By wogu
* $Author$
* $Id$
*/
class voa_c_admincp_office_exam_addtiku extends voa_c_admincp_office_exam_base {

	public function execute() {
		if ($this->_is_post()) {
			$tiku = array();
			$data = $this->request->postx();
			$step=$data['step'];
			unset($data['step']);
			try {
				$args = array(
					'username' => $this->_user['ca_username'],
					'id' => intval($data['id'])
				);

				$uda = &uda::factory('voa_uda_frontend_exam_tiku');
				$uda->add_tiku($data, $tiku,$args);
			} catch (help_exception $h) {
				$this->_admincp_error_message($h);
			} catch (Exception $e) {
				logger::error($e);
				$this->_admincp_system_message($e);
			}

			if ($step){
				
				$this->message('success', '题库添加成功', $this->cpurl($this->_module, $this->_operation, 'addtm', $this->_module_plugin_id,array('tiku_id' => $tiku['id'])));
			}

			$this->message('success', '题库添加成功', $this->cpurl($this->_module, $this->_operation, 'tikulist', $this->_module_plugin_id));
		}

		$id = intval($this->request->get('id'));
		if($id) {
			$s_tiku = new voa_s_oa_exam_tiku();
			$tiku = $s_tiku->get_by_id($id);
			$this->view->set('tiku', $tiku);
		}

		$this->output('office/exam/addtiku');
	}
}
