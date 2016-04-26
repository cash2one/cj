<?php
/**
 * api部门列表
 * voa_c_api_department_list
 */

class voa_c_api_department_get_list extends voa_c_api_department_base {

	public function execute() {
		//获取上级id参数
		$up_id = $this->request->get('up_id');
		$up_id = rintval($up_id);

		if ($up_id < 1) {
			$this->_result = array();
		}
		//搜索的部门
		$search = $this->request->get('search');
		$search_list = array();
		$list = array();
		if(!empty($search)){
			foreach($this->_departments as $_cdid=>$_val){
				if(preg_match('/'.$search.'/', $_val['cd_name'])){
					$search_list['id'] = $_cdid;
					$search_list['name'] = $_val['cd_name'];
					array_push($list, $search_list);
				}
			}
			$this->_result = array('list' => $list);
			return ;
		}
		$departments = array();
		//遍历所有部门，获取up_id下的所有部门
		$main_cdids = array();
		foreach ($this->_departments as $key=>$department) {
			if ($department['cd_upid'] == $up_id) {
				$temp['id'] = $department['cd_id'];
				$temp['name'] = $department['cd_name'];
				array_push($departments, $temp);
				//$departments[$key] = $department['cd_name'];
			}

			if (0 == $department['cd_upid']) {
				$main_cdids[] = $department['cd_id'];
			}
		}

		// 读取公司下的用户
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$condi = array(
			'cd_id' => array($main_cdids)
		);
		$mems = $serv_m->fetch_all_by_conditions($condi, array('m_displayorder' => 'ASC'), 0, 300);
		$users = array();
		foreach ($mems as $_m) {
			$item['m_uid'] = $_m['m_uid'];
			$item['m_username'] = $_m['m_username'];
			$item['m_face'] = voa_h_user::avatar($_m['m_uid'], $_m);
			$users[] = $item;
		}

		$this->_result = array('list' => $departments, 'users' => $users);
	}
}
