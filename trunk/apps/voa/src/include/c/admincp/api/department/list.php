<?php

/**
 * api部门列表
 * voa_c_admincp_api_department_list
 * User: luckwang
 * Date: 15/4/2
 * Time: 下午7:48
 */
class voa_c_admincp_api_department_list extends voa_c_admincp_api_department_base {

	public function execute() {

		if ($this->request->get('isall')) {
			$list = $this->__get_sub(0);
			$this->_output_result(array('list' => $list));

			return;
		}

		//获取上级id参数
		$up_id = $this->request->get('up_id');
		$up_id = rintval($up_id);

		if ($up_id < 1) {
			$this->_output_result(array());
		}

		$departments = array();
		//遍历所有部门，获取up_id下的所有部门
		foreach ($this->_departments as $key => $department) {
			if ($department['cd_upid'] == $up_id) {
				$temp['id'] = $department['cd_id'];
				$temp['name'] = $department['cd_name'];
				array_push($departments, $temp);
				//$departments[$key] = $department['cd_name'];
			}
		}

		// 判断名称是否过长
		foreach ($departments as &$_dep) {
			if (mb_strlen($_dep['name']) > 25) {
				$_dep['name'] = rsubstr($_dep['name'], 25);
			}
		}

		$this->_output_result(array('list' => $departments));
	}

	private function __get_sub($up_id) {
		$departments = array();
		//遍历所有部门，获取up_id下的所有部门
		foreach ($this->_departments as $key => $department) {
			if ($department['cd_upid'] == $up_id) {
				$temp['id'] = $department['cd_id'];
				$temp['text'] = $department['cd_name'];
				$temp['subs'] = $this->__get_sub($department['cd_id']);
				array_push($departments, $temp);
			}
		}

		return $departments;
	}
}
