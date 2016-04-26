<?php
/**
 * 公共收藏接口
 */
namespace PubApi\Controller\Api;

class DynamicController extends AbstractController {

	/**
	 * 我的动态接口
	 * @return boolean
	 */
	public function dynamic_get() {

		$uid = I('get.uid', '', 'intval');
		$page = I('get.page', '1', 'intval');
		if(empty($uid)){
			// 获取当前登录用户uid
			$uid = $this->_login->user['m_uid'];
		}

		$serv_c = D('Common/CommonDynamic', 'Service');

		list($start, $limit, $page) = page_limit($page, 10);

		// 分页数组
		$page_option = array($start, $limit);
		// 排序
		$orderby = array('created' => 'DESC');

		$list = $serv_c->list_by_uid($uid, $page_option, $orderby);
		$total = $serv_c->total_by_uid($uid);

		// 返回数据
		$this->_result = array(
			'page' => $page,
			'limit' => $limit,
			'total' => (int)$total,
			'list' => $list,
		);

		return true;
	}

	/**
	 * 所有朋友圈动态
	 * @return bool
	 */
	public function dynamic_all_get() {

		$uid = I('get.uid', '', 'intval');
		$page = I('get.page', 1, 'intval');
		$limit = I('get.limit', 10, 'intval');

		// 获取当前登录用户uid
		if(empty($uid)){
			$uid = $this->_login->user['m_uid'];
		}

		$serv_c = D('Common/CommonDynamic', 'Service');

		list($start, $limit, $page) = page_limit($page, $limit);

		// 分页数组
		$page_option = array($start, $limit);
		// 排序
		$orderby = array('created' => 'DESC');

		$list = $serv_c->list_all_by_uid($uid, $page_option, $orderby);
		$total = $serv_c->total_all_by_uid($uid);

		// 返回数据
		$this->_result = array(
			'page' => $page,
			'limit' => $limit,
			'total' => (int)$total,
			'list' => $list,
		);

		return true;
	}

	/**
	 * 我的收藏列表接口
	 * @return boolean
	 */
	public function collect_get() {

		// 获取当前登录用户uid
		$uid = $this->_login->user['m_uid'];
		$page = I('get.page', '1', 'intval');
		$serv_c = D('PubApi/Collect', 'Service');

		list($start, $limit, $page) = page_limit($page, 10);

		// 分页数组
		$page_option = array($start, $limit);
		// 排序
		$orderby = array('created' => 'DESC');

		$list = $serv_c->list_by_uid($uid, $page_option, $orderby);
		$total = $serv_c->total_by_uid($uid);

		// 返回数据
		$this->_result = array(
			'page' => $page,
			'limit' => $limit,
			'total' => $total,
			'list' => $list,
		);

		return true;
	}

	/**
	 * 我的收藏取消接口
	 * @return boolean
	 */
	public function cancel_post() {

		$id = I('post.id', '', 'intval');
		$serv_c = D('Common/CommonDynamic', 'Service');

		// 参数不能为空
		if(empty($id)){
			$this->_set_error('_ERR_COLLECT_ID_IS_NOT_EXIST');
			return false;
		}

		/* 如果不是数组，则将其定义成数组 */
		if (!is_array($id)) {
			$ids = array($id);
		} else {
			$ids = $id;
		}

		// 操作
		if (!$serv_c->delete_dynamic_pks($ids)) {
			return false;
		}

		return true;
	}

	/**
	 * 内部使用接口
	 * @param array $o_params 内部数据调用
	 * @return bool
	 */
	public function Dynamic_inner_post($params = array()) {

		$obj_id = $params['obj_id'];
		$cp_identifier = $params['cp_identifier'];
		$dynamic = $params['dynamic'];

		if (!is_numeric($obj_id)) {
			$this->_set_error('_ERR_INSERT_ERROR');
			return false;
		}
		/* 必填参数不能为空 */
		if (empty($obj_id) ||empty($cp_identifier) || empty($dynamic)) {
			$this->_set_error('_ERROR_PARAMS_IS_NOT');
			return false;
		}

		$is_special = isset($params['is_special']) ? $params['is_special'] : 0;

		// 获取用户信息
		$user = $params['user'];
		// 添加数据组装
		$add_data = array(
			'obj_id' => $obj_id,
			'cp_identifier' => $cp_identifier,
			'm_uid' => $user['m_uid'],
			'm_username' => $user['m_username'],
			'dynamic' => $dynamic,
			'is_special' => $is_special,
		);

		$serv_c = D('Common/CommonDynamic', 'Service');

		/* 如果是收藏 需要判断是否有重复收藏 点赞 */
		if ($dynamic == 5 ) {
			if (!$serv_c->get_dynamic_by_conds($add_data)) {
				$this->_set_error('_ERROR_NOT_ALLOW_CANCLE');
				return false;
			}
		}
		if ($dynamic == 1) {
			if (!$serv_c->get_dynamic_by_conds($add_data)) {
				$this->_set_error('_ERROR_ADD_ALLOW_LIKE');
				return false;
			}
		}

		// 数据入库
		if (!$serv_c->add_dynamic($add_data)) {
			return false;
		}

		return true;
	}

	/**
	 * 外部动态收藏接口
	 * @return bool
	 */
	public function Dynamic_outsider_post() {

		$params = I('post.');

		// 判断参数
		if (empty($params)) {
			$this->_set_error('_ERROR_PARAMS_IS_NOT');
			return false;
		}

		// 获取用户信息
		$user = $this->_login->user;
		$params['user'] = $user;

		// 调用内部方法
		$this->Dynamic_inner_post($params);

		return true;

	}

	/**
	 * 删除收藏接口
	 * @return bool
	 */
	public function Collect_Del_post() {

		$dynamic = I('post.dynamic', '', 'intval');

		// 只能是收藏可以删除
		if ($dynamic !== 5) {
			$this->_set_error('_ERROR_DELET_DATA');
			return false;
		}

		$obj_id = I('post.obj_id', '', 'intval');
		$cp_identifier = I('post.cp_identifier');

		// 参数不能为空
		if (empty($obj_id) || empty($cp_identifier)) {
			$this->_set_error('_ERROR_PARAMS_IS_NOT');
			return false;
		}

		$del_data = array(
			'obj_id' => $obj_id,
			'cp_identifier' => $cp_identifier,
			'm_uid' => $this->_login->user['m_uid'],
			'dynamic' => $dynamic,
		);

		// 删除收藏
		$pu_d = D('Common/CommonDynamic', 'Service');
		if (!$pu_d->del_dynamic($del_data)) {
			return false;
		}

		return true;
	}

	/**
	 * 删除点赞接口
	 * @return bool
	 */
	public function Likes_Del_post($params = array()) {

		$obj_id = $params['obj_id'];
		$cp_identifier = $params['cp_identifier'];
		$dynamic = $params['dynamic'];

		/* 必填参数不能为空 */
		if (empty($obj_id) ||empty($cp_identifier) || empty($dynamic)) {
			$this->_set_error('_ERROR_PARAMS_IS_NOT');
			return false;
		}

		// 获取用户信息
		$user = $params['user'];
		// 删除数据组装
		$del_data = array(
			'obj_id' => $obj_id,
			'cp_identifier' => $cp_identifier,
			'm_uid' => $user['m_uid'],
			'dynamic' => $dynamic,
		);

		$serv_c = D('Common/CommonDynamic', 'Service');

		// 数据入库
		if (!$serv_c->del_likesdynamic($del_data)) {
			return false;
		}
	}
}

//end
