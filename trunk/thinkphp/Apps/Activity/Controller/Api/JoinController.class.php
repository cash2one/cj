<?php
/**
 * 活动报名 内部人员 外部人员
 * User: Muzhitao
 * Date: 2015/9/30 0030
 * Time: 14:22
 * Email：muzhitao@vchangyi.com
 */
namespace Activity\Controller\Api;

class JoinController extends AbstractController {

	/**
	 * 内部人员报名提交
	 * @return bool
	 */
	public function Internal_Join_post() {

		$acid = I('post.acid', '', 'intval');
		$remark = I('post.remark', '', 'htmlspecialchars');

		// 如果acid不合法
		if (empty($acid)) {
			$this->_set_error('_ERR_ACID_NOT_LE_LEGAL');
			return false;
		}

		$serv_p = D('Activity/ActivityPartake', 'Service');

		// 数据组装
		$data = array(
			'm_uid' => $this->_login->user['m_uid'],
			'acid' => $acid,
			'name' => $this->_login->user['m_username'],
			'type' => 1,
			'remark' => $remark
		);

		print_r($data);
		exit();

		// 插入数据
		if (!$serv_p->insert_data($data)) {
			return false;
		}

		return true;
	}

	/**
	 * 内部报名人员列表
	 * @return bool
	 */
	public function Internal_List_get() {

		$acid = I('get.acid', '', 'intval');
		$page = I('get.page', 1, 'intval');

		list($start, $limit, $page) = page_limit($page, $this->_plugin->setting['perpage']);

		// 分页数组
		$page_option = array($start, $limit);

		// 排序
		$orderby = array('created' => 'DESC');

		// 查询条件
		$conds = array(
			'acid' => $acid
		);

		$fields = "m_uid, name, acid, remark, created";
		$serv_p = D('Activity/ActivityPartake', 'Service');

		$list = $serv_p->data_list($conds, $page_option, $orderby, $fields);

		$serv_p->format_data($list);

		// 返回数据
		$this->_result = array(
			'page' => $page,
			'list' => $list
		);

		return true;
	}

	/**
	 * 外部报名人员列表
	 * @return bool
	 */
	public function External_List_get() {

		$acid = I('get.acid', '', 'intval');
		$page = I('get.page', 1, 'intval');

		list($start, $limit, $page) = page_limit($page, $this->_plugin->setting['perpage']);

		// 分页数组
		$page_option = array($start, $limit);

		// 排序
		$orderby = array('created' => 'DESC');

		// 查询条件
		$conds = array(
			'acid' => $acid
		);

		// 自定义查询的字段
		$fields = "outname, outphone, remark, other, created";

		$serv_o = D('Activity/ActivityOutsider', 'Service');

		$list= $serv_o->data_list($conds, $page_option, $orderby, $fields);
		// 格式化
		$serv_o->format_data($list);

		// 返回数据
		$this->_result = array(
			'page' => $page,
			'list' => $list
		);

		return true;
	}

	/**
	 * 外部人员报名提交
	 * @return bool
	 */
	public function External_Join_post() {

		$acid = I('post.acid', '', 'intval');
		$name = I('post.outname');
		$outphone = I('post.outphone');
		$remark = I('post.remark');

		$serv_o = D('Activity/ActivityOutsider', 'Service');
		// 判断当前提交的用户手机号码是否存在
		if (!$serv_o->get_by_uid_mobile($acid, $outphone)) {
			$this->_set_error('_ERROR_IS_JOIN');
			return false;
		}

		$other = I('post.field', ''); // 自定义字段

		// 数据组装
		$data = array(
			'acid' => $acid,
			'outname' => $name,
			'outphone' => $outphone,
			'remark' => $remark,
			'other' => serialize($other)
		);

		// 插入数据
		$serv_o->insert_data($data);

		return true;
	}

	/**
	 * 外部人员报名自定义字段
	 * @return bool
	 */
	public function External_show_get() {

		$acid = I('get.acid', '', 'intval');

		// 自定义字段
		$fields = "outsider, outfield";

		$serv_a = D('Activity/Activity', 'Service');
		$detail = $serv_a->view($acid, $fields);

		// 如果外部人员报名没有开启
		if (!$detail['outsider']) {
			$this->_set_error('_ERROR_OUTSIDE_NOT_JOIN');
			return false;
		}

		// 返回数据
		$this->_result = array(
			'acid' => $acid,
			'outfield' => unserialize($detail['outfield'])
		);

		return true;
	}
}

// end
