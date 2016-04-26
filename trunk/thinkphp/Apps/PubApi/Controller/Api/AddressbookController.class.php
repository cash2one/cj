<?php
/**
 * AddressbookController.class.php
 * $author$
 */

namespace PubApi\Controller\Api;
use Common\Common\Cache;
use Common\Common\User;

class AddressbookController extends AbstractController {

	// 取用户列表(包括部门列表)
	public function ListMember_get() {

		// 参数处理
		$fields = array(
			'cd_id' => array('cd_id', 'int'),
			'limit' => array('limit', 'int'),
			'page' => array('page', 'int'),
			'keyword' => array('keyword', 'string'),
			'keyindex' => array('keyindex', 'string')
		);
		// 提取数据
		$params = array();
		if (!extract_field($params, $fields)) {
			E('_ERR_PARAMS_ERROR');
			return false;
		}

		// 取用户列表
		$serv_addr = D('PubApi/Addressbook', 'Service');
		if (!$serv_addr->list_member($this->_result, $params)) {
			E('_ERR_LIST_MEMBER_ERROR');
			return false;
		}

		return true;
	}

	// 获取指定部门下的子部门
	public function ListDepartment_get() {

		// 参数处理
		$fields = array(
			'cd_id' => array('cd_id', 'int'),
			'limit' => array('limit', 'int'),
			'page' => array('page', 'int')
		);
		// 提取数据
		$params = array();
		if (!extract_field($params, $fields)) {
			E('_ERR_PARAMS_ERROR');
			return false;
		}

		// 取用户列表
		$serv_addr = D('PubApi/Addressbook', 'Service');
		$dps = array();
		if (!$serv_addr->list_department($dps, $params)) {
			E('_ERR_LIST_DEPARTMENT_ERROR');
			return false;
		}

		// 取所有下级部门
		$this->_result = array('departments' => $dps);

		return true;
	}

}
