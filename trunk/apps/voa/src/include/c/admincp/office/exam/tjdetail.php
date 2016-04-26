<?php
/**
* 查看详情
* Create By wogu
* $Author$
* $Id$
*/

class voa_c_admincp_office_exam_tjdetail extends voa_c_admincp_office_exam_base {

	public function execute() {

		$partin = intval($this->request->get('partin'));
		

		$id = intval($this->request->get('id'));
		if(!$id) {
			$this->_error_message('参数错误');
		}

		$s_paper = new voa_s_oa_exam_paper();
		$paper = $s_paper->get_by_id($id);
		if(!$paper) {
			$this->_error_message('试卷不存在');
		}
		// 获取考试范围
		if(!empty($paper['cd_ids'])) {
			$serv_d = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
			$depms = $serv_d->fetch_all_by_key(explode(',', $paper['cd_ids']));
			foreach($depms as $k => $v) {
				$range_departments[] = $v['cd_name'];
			}
			$this->view->set('range_departments', implode(',', $range_departments));
		}
		if(!empty($paper['m_uids'])) {
			$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			$users = $serv_m->fetch_all_by_ids(explode(',', $paper['m_uids']));
			foreach($users as $k => $v) {
				$range_members[] = $v['m_username'];
			}
			$this->view->set('range_members', implode(',', $range_members));
		}

		$limit = 15;   // 每页显示数量
		$page = $this->request->get('page');   // 当前页码
		if (!is_numeric($page) || $page < 1) {
			$page = 1;
		}
		list($start, $limit, $page) = voa_h_func::get_limit($page, $limit);

		$uda_list = &uda::factory('voa_uda_frontend_exam_tj');
		$result = array();
		$conditions=array(
			'paper_id'=>$id
		);
		if($partin==0){
			$conditions['status']=array(0,'>');
		}else{
			$conditions['status']=0;
		}

		$uda_list->list_tj($result, $conditions, $start, $limit);

		$count=$uda_list->count_by_conds(array('paper_id'=>$id));

		$tjs=$result['list'];

		if(!empty($result['list'])) {
			$data = $uids = array();
			foreach($tjs as $tj) {
				$data[$tj['id']] = $tj;
				$uids[] = $tj['m_uid'];
			}
			// 获取考生信息
			$s_member = new voa_s_oa_member();
			$members = $s_member->fetch_all_by_ids($uids);
			
			$cd_ids = array();
			foreach($members as $member) {
				$cd_ids[] = $member['cd_id'];
			}

			// 获取部门信息
			$departments = voa_h_department::get_multi($cd_ids);

			


			// 分页链接信息
			$multi = '';
			if ($result['total'] > 0) {
				// 输出分页信息
				$multi = pager::make_links(array(
					'total_items' => $result['total'],
					'per_page' => $limit,
					'current_page' => $page,
					'show_total_items' => true,
				));
			}
			
			$this->view->set('multi', $multi);
			$this->view->set('data', $data);
			$this->view->set('members', $members);
			$this->view->set('departments', $departments);
			
		}

		$this->view->set('total',$result['total']);
		$this->view->set('total_s', $count-$result['total']);
		$this->view->set('partin', $partin);
		$this->view->set('paper', $paper);
		$this->view->set('id', $id);
		$this->view->set('form_notify_url', $this->cpurl($this->_module, $this->_operation, 'tjnotify', $this->_module_plugin_id));
		$this->view->set('form_tabs_url', $this->cpurl($this->_module, $this->_operation, 'tjdetail', $this->_module_plugin_id));
		$this->view->set('notify_url', $this->cpurl($this->_module, $this->_operation, 'tjnotify', $this->_module_plugin_id, array('id' => '')));
		$this->view->set('view_answer_url', $this->cpurl($this->_module, $this->_operation, 'viewanswer', $this->_module_plugin_id, array('tjid' => '')));
		$this->view->set('tjexport_url', $this->cpurl($this->_module, $this->_operation, 'tjexport', $this->_module_plugin_id, array('id' => $id)));
		// 返回统计列表
		$this->view->set('tjlist_url', $this->cpurl($this->_module, $this->_operation, 'tj', $this->_module_plugin_id));


		$this->output('office/exam/tj_detail');
	}

	
}
