<?php
/**
 * 我的活动列表
 * MyController.class.php
 * $author$
 * User: XiaoDingchen
 */
namespace Activity\Controller\Api;

class MyController extends AbstractController {


	/**
	 * 我的活动列表
	 * get方式
	 * @return bool
	 */
	public function My_get() {

		$page = I('get.page', 1, 'intval');
		$action = I('get.ac'); //读取的列表类型

		$limit = 10; //每页显示数目
		$start = 0;
		list($start, $limit, $page) = page_limit($page, $limit);

		$serv_a = D('Activity/Activity', 'Service');

		$result = $serv_a->my_list($action, $this->_login->user['m_uid'], $start, $limit);

		$serv_a->format_my($result);
		// 返回数据
		$this->_result = array(
			'page' => $page,
			'list' => $result
		);

		return true;
	}
}
