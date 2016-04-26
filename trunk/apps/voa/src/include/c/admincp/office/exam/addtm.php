<?php
/**
* 增加题目
* Create By wogu
* $Author$
* $Id$
*/
class voa_c_admincp_office_exam_addtm extends voa_c_admincp_office_exam_base {

	public function execute() {
		
		if ($this->_is_post()) {
			$data = $this->request->postx();

			try {
				$args = array(
					'id' => intval($data['id'])
				);
				$answer=explode("\r\n", $data['answer']);
				$data['answer']=implode("\r\n", $this->_array_del_null($answer) );
				$data['options']=implode("\r\n", $this->_array_del_null($data['options']) );
				
				$uda = &uda::factory('voa_uda_frontend_exam_ti');
				$uda->add_ti($data, $args);
			} catch (help_exception $h) {
				$this->_admincp_error_message($h);
			} catch (Exception $e) {
				logger::error($e);
				$this->_admincp_system_message($e);
			}

			if($data['save']){
				$this->message('success', '题目添加成功', $this->cpurl($this->_module, $this->_operation, 'viewtm', $this->_module_plugin_id, array('id' => $data['tiku_id'])) );
			}else{
				$url_arr=array('tiku_id' => $data['tiku_id']);
				if($data['isedit']){
					$url_arr=array('isedit' => 1, 'tiku_id' => $data['tiku_id']);
				}
				$this->redirect($this->cpurl($this->_module, $this->_operation, 'addtm', $this->_module_plugin_id, $url_arr, false ));
			}
		}

		$id = intval($this->request->get('id'));
		$isedit = intval($this->request->get('isedit'));

		$page = $this->request->get('page');
		if($id) {
			$s_ti = new voa_s_oa_exam_ti();
			$ti = $s_ti->get_by_id($id);
			$this->view->set('ti', $ti);
			$tiku_id = $ti['tiku_id'];
		} else {
			$tiku_id = intval($this->request->get('tiku_id'));
		}
		$s_tiku = new voa_s_oa_exam_tiku();

		

		if($id){
			$back_url=$this->cpurl($this->_module, $this->_operation, 'viewtm', $this->_module_plugin_id, array('id' => $tiku_id,'page'=>$page));
		}else{
			$back_url=$this->cpurl($this->_module, $this->_operation, 'tikulist', $this->_module_plugin_id);
		}

		$tiku = $s_tiku->get_by_id($tiku_id);
		$this->view->set('tiku', $tiku);
		$this->view->set('tiku_id', $tiku_id);
		$this->view->set('isedit', $isedit);
		$this->view->set('back_url', $back_url);
		$this->view->set('types', voa_d_oa_exam_ti::$TYPES);
		$this->output('office/exam/addtm');
	}

	protected function _array_del_null($arr){
		foreach ($arr as $key => $value) {
			if(empty($value)){
				unset($arr[$key]);
			}
		}
		return $arr;
	}
}
