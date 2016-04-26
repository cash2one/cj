<?php
/**
 * voa_uda_frontend_department_delete
 * 统一数据访问/部门/删除
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_department_delete extends voa_uda_frontend_department_base {

	/**
	 * 删除指定$department的部门信息
	 * @param array|number $department 可以是部门的id，也可以是部门的信息数组，推荐设置为部门信息数组
	 * @param boolean $force 是否强制请求微信接口（一般情况下，本地验证失败则不再请求微信接口，设为true后，无论本地怎么验证，都将请求微信接口删除）
	 * @return boolean
	 */
	public function delete($department, $force = false) {

		$uda_get = &uda::factory('voa_uda_frontend_department_get');

		// 给定的参数值既不是部门信息数组也不是部门id
		if (!isset($department['cd_id']) && !is_numeric($department)) {
			$this->errmsg(1001, '请指定要删除的部门');
			return false;
		}

		// 如果给定的是部门id，则找到其其他数据
		if (is_numeric($department)) {
			$cd_id = $department;
			$department = array();
			$uda_get->department($cd_id, $department);
			if (empty($department['cd_id'])) {
				$this->errmsg(1002, '指定的部门不存在或已被删除');
				return false;
			}
		}

		$cd_id = $department['cd_id'];

		// 如未强制请求删除则先本地判断其下是否有成员用户
		$usernum = 0;
		if (!$force && ($uda_get->count_by_cd_id($cd_id, $usernum) && $usernum > 0)) {
			$this->errmsg(1003, '指定待删除的部门下存在成员，请先移除该部门的成员后再删除');
			return false;
		}

		// 本地存在该部门在企业微信部门id的对应关系，则请求接口删除
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		if (!empty($department['cd_qywxid']) && !empty($sets['ep_wxqy'])) {

			// 加载微信通讯录接口
			$wxqy_addressbook = new voa_wxqy_addressbook();
			// 返回的结果
			$result = array();
			if (!$wxqy_addressbook->department_delete($department['cd_qywxid'], $result)) {
				$this->errmsg(1004, '请求接口错误：'.$wxqy_addressbook->error_msg);
				return false;
			}
		}

		// 删除本地数据
		$this->serv->delete($cd_id);

		// 更新缓存
		parent::update_cache();

		return true;
	}

}
