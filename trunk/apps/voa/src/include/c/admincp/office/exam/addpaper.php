<?php
/**
* 增加试卷
* Create By wogu
* $Author$
* $Id$
*/

class voa_c_admincp_office_exam_addpaper extends voa_c_admincp_office_exam_base {

	public function execute() {
		if ($this->_is_post()) {
			$paper = array();
			$data = $this->request->postx();
			try {
				$args = array(
					'id' => intval($data['id']),
					'username' => $this->_user['ca_username']
				);
				$uda = &uda::factory('voa_uda_frontend_exam_paper');
				$uda->add_paper($data, $paper, $args);
			} catch (help_exception $h) {
				$this->_admincp_error_message($h);
			} catch (Exception $e) {
				logger::error($e);
				$this->_admincp_system_message($e);
			}

			if(($paper['type'] == 0 && $paper['use_all'] != 1) || $paper['type'] == 1)
				$this->message('success', '请选择题目', $this->cpurl($this->_module, $this->_operation, 'paperdetail', $this->_module_plugin_id, array('id' => $paper['id'])));
			else
				$this->message('success', '请设置试卷详细信息', $this->cpurl($this->_module, $this->_operation, 'papersetting', $this->_module_plugin_id, array('id' => $paper['id'])));
		}

		$id = intval($this->request->get('id'));
		if($id) {
			$s_paper = new voa_s_oa_exam_paper();
			$paper = $s_paper->get_by_id($id);
			$this->view->set('paper', $paper);
		}

		$s_tiku = new voa_s_oa_exam_tiku();
		$tikus = $s_tiku->list_all_tiku();
		$this->view->set('tikus', $tikus);

		$this->view->set('types', voa_d_oa_exam_paper::$TYPES);
		$this->output('office/exam/addpaper');
	}
}
