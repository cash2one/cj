<?php
/**
 * voa_uda_frontend_addressbook_delete
 * 统一数据访问/通讯录/删除
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_addressbook_delete extends voa_uda_frontend_addressbook_base {

	/**
	 * 删除指定的通讯录记录
	 * @param array|cab_id $addressbook 可以是通讯录cab_id，也可以是通讯录数据信息
	 * @param string $ignore_qywx 当链接企业微信接口出错时是否忽略该错误而继续删除本地数据。默认：false
	 * @return boolean
	 */
	public function delete($addressbook, $ignore_qywx_error = false) {

		$uda_get = &uda::factory('voa_uda_frontend_addressbook_get');

		// 给定的参数值既不是通讯录信息数组也不是通讯录cab_id
		if (!isset($addressbook['cab_id']) && !is_numeric($addressbook)) {
			$this->errmsg(1001, '请指定要删除的记录');
			return false;
		}

		// 如果给定的是部门id，则找到其其他数据
		if (is_numeric($addressbook)) {
			$cab_id = $addressbook;
			$addressbook = array();
			$uda_get->addressbook($cab_id, $addressbook);
			if (empty($addressbook['cab_id'])) {
				$this->errmsg(1002, '指定的通讯录记录不存在或已被删除');
				return false;
			}
		}

		$cab_id = $addressbook['cab_id'];

		// 加载微信通讯录接口
		$wxqy_addressbook = new voa_wxqy_addressbook();
		// 返回的结果
		$result = array();
		if (!$wxqy_addressbook->user_delete($addressbook['m_openid'], $result) && !$ignore_qywx_error) {
			// 如果请求删除接口发生错误 且 未强制要求忽略接口错误则终止
			$this->errmsg(1004, '请求接口错误：'.$wxqy_addressbook->error_msg);
			return false;
		}

		/**
		 * 删除本地数据，删除member和addressbook
		 */
		if ($addressbook['m_uid']) {
			// 已关联了member表，则删除

			$member_serv = &service::factory('voa_s_oa_member');
			$member_serv->delete($addressbook['m_uid']);
		}

		// 删除addressbook表记录
		$this->serv->delete_by_conditions(array('cab_id'=>$cab_id));

		$this->errmsg(0, '指定记录删除操作完毕');

		return true;
	}

}
