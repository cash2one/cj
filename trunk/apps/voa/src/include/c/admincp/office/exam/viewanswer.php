<?php
/**
* 查看答卷
* Create By wogu
* $Author$
* $Id$
*/
class voa_c_admincp_office_exam_viewanswer extends voa_c_admincp_office_exam_base {

	public function execute() {
		$tjid = intval($this->request->get('tjid'));
		if(!$tjid) {
			$this->_error_message('参数错误');
		}

		$s_tj = new voa_s_oa_exam_tj();
		$tj = $s_tj->get($tjid);
		if(!$tj) {
			$this->_error_message('参数错误');
		}
		$id = $tj['paper_id'];
		$s_titj = new voa_s_oa_exam_titj();
		$myanswers = $s_titj->list_by_tj_id($tjid);

		$this->view->set('myanswers', $myanswers);

		$s_paper = new voa_s_oa_exam_paper();
		$paper = $s_paper->get_by_id($id);
		if(!$paper) {
			$this->_error_message('试卷不存在');
		}

		// 获取试卷详情
		$tids=array_keys($myanswers);

		// 获取试题
		$s_ti = new voa_s_oa_exam_ti();
		$tis = array();
		if(!empty($tids)){
			$tis = $s_ti->list_by_ids_real($tids);
			// 获取最终的试题
			foreach ($tis as $k=>$ti) {
				$tis[$k]['options']=empty($ti['options']) ? array() : explode("\r\n", $ti['options']);
			}
		}
		

		$this->view->set('tis', $tis);
		$this->view->set('no_join_count', $no_join_count);
		$this->view->set('join_count', $join_count);
		$this->view->set('paper', $paper);
		$this->view->set('types', voa_d_oa_exam_ti::$TYPES);
		// 返回统计
		$this->view->set('tjdetail_url', $this->cpurl($this->_module, $this->_operation, 'tjdetail', $this->_module_plugin_id, array('id' => $paper['id'])));
		$this->output('office/exam/view_answer');
	}
}
