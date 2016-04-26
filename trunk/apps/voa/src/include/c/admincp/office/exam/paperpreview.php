<?php
/**
* 试卷预览
* Create By wogu
* $Author$
* $Id$
*/

class voa_c_admincp_office_exam_paperpreview extends voa_c_admincp_office_exam_base {

	public function execute() {
		$id = intval($this->request->get('id'));
		if(!$id) {
			$this->_error_message('参数错误');
		}


		$s_paper = new voa_s_oa_exam_paper();
		$paper = $s_paper->get_by_id($id);
		if(!$paper) {
			$this->_error_message('试卷不存在');
		}

		// 获取试卷详情
		$s_detail = new voa_s_oa_exam_paperdetail();
		$details = $s_detail->list_by_paperid($id);
		$tids = array();
		foreach($details as $detail) {
			$tids[] = $detail['ti_id'];
		}

		// 获取试题
		$s_ti = new voa_s_oa_exam_ti();
		$tis = $s_ti->list_by_ids($tids);

		// 获取最终的试题
		$result = array();
		foreach ($details as &$detail) {
			$ti = $tis[$detail['ti_id']];
			$result[] = array(
				'id' => $ti['id'],
				'tiku_id' => $ti['tiku_id'],
				'type' => $ti['type'],
				'title' => $ti['title'],
				'score' => $detail['score'],
				'options' => empty($ti['options']) ? array() : explode("\r\n", $ti['options']),
				'answer' => $ti['answer']
			);
		}

		

		$this->view->set('tis', $result);
		$this->view->set('no_join_count', $no_join_count);
		$this->view->set('join_count', $join_count);
		$this->view->set('paper', $paper);
		$this->view->set('types', voa_d_oa_exam_ti::$TYPES);
		$this->output('office/exam/paper_preview');
	}
}
