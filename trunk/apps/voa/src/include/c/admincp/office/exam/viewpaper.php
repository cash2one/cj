<?php
/**
* 查看详情
* Create By wogu
* $Author$
* $Id$
*/
class voa_c_admincp_office_exam_viewpaper extends voa_c_admincp_office_exam_base {

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
		if($paper['status'] == 0) {
			$paper['status_show'] = '草稿';
		} elseif($paper['status'] == 2) {
			$paper['status_show'] = '已终止';
		} else {
			$currtime = time();
			if($currtime < $paper['begin_time']) {
				$paper['status_show'] = '未开始';
			} elseif($currtime > $paper['end_time']) {
				$paper['status_show'] = '已结束';
			} else {
				$paper['status_show'] = '已开始';
			}
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

		if(!empty($paper['cd_ids'])) {
			$serv_d = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
			$depms = $serv_d->fetch_all_by_key(explode(',', $paper['cd_ids']));
			foreach($depms as $k => $v) {
				$departments[] = $v['cd_name'];
			}
			$this->view->set('departments', implode(',', $departments));
		}

		if(!empty($paper['m_uids'])) {
			$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			$users = $serv_m->fetch_all_by_ids(explode(',', $paper['m_uids']));
			foreach($users as $k => $v) {
				$members[] = $v['m_username'];
			}

			$this->view->set('members', implode(',', $members));
		}

		$paper['picurl'] = voa_h_attach::attachment_url($paper['cover_id']);

		$uda_tj = &uda::factory('voa_uda_frontend_exam_tj');
		$no_join_count=$uda_tj->count_by_conds(array('paper_id'=>$paper['id'], 'status'=>0));
		$join_count=$uda_tj->count_by_conds(array('paper_id'=>$paper['id'], 'status>?'=>0));

		$this->view->set('tis', $result);
		$this->view->set('no_join_count', $no_join_count);
		$this->view->set('join_count', $join_count);
		$this->view->set('paper', $paper);
		$this->view->set('types', voa_d_oa_exam_ti::$TYPES);
		$this->view->set('tjdetail_url', $this->cpurl($this->_module, $this->_operation, 'tjdetail', $this->_module_plugin_id, array('id' => $paper['id'])));
		$this->output('office/exam/view_paper');
	}
}
