<?php
/**
 * 公共方法
 * User: Muzhitao
 * Date: 2015/12/2 0002
 * Time: 10:28
 * Email：muzhitao@vchangyi.com
 */

namespace PubApi\Controller\Api;

class CommonController extends AbstractController {

	/**
	 * 动态添加操作
	 * @return bool
	 */
	public function Common_Add_post($obj_id, $cp_identifier, $dynamic, $user, $is_special) {

		// 是否是特殊操作 默认不是
		if(empty($is_special)){
			$is_special = 0;
		}

		// 参数不能为空
		if (empty($obj_id) || empty($cp_identifier) || empty($dynamic)) {
			$this->_set_error('_ERROR_PARAMS_IS_NOT');
			return false;
		}

		// 添加数据组装
		$add_data = array(
			'obj_id' => $obj_id,
			'cp_identifier' => $cp_identifier,
			'm_uid' => $user['m_uid'],
			'm_username' => $user['m_username'],
			'dynamic' => $dynamic,
			'is_special' => $is_special,
		);

		// 数据添加操作
		$pu_d = D('Common/CommonDynamic', 'Service');
		if (!$pu_d->add_dynamic($add_data)) {
			return false;
		}

		return true;
	}

	/**
	 * 动态删除操作
	 * @return bool
	 */
	public function Common_Del_post($obj_id, $cp_identifier, $dynamic, $user, $is_special) {

		// 是否是特殊操作 默认不是
		if(empty($is_special)){
			$is_special = 0;
		}

		// 参数不能为空
		if (empty($obj_id) || empty($cp_identifier) || empty($dynamic)) {
			$this->_set_error('_ERROR_PARAMS_IS_NOT');
			return false;
		}

		// 添加数据组装
		$del_data = array(
			'obj_id' => $obj_id,
			'cp_identifier' => $cp_identifier,
			'm_uid' => $user['m_uid'],
			'm_username' => $user['m_username'],
			'dynamic' => $dynamic,
			'is_special' => $is_special,
		);

		// 数据添加操作
		$pu_d = D('Common/CommonDynamic', 'Service');
		if (!$pu_d->del_dynamic($del_data)) {
			return false;
		}

		return true;
	}
}
