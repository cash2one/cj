<?php
/**
 * 活动报名列表
 * User: Muzhitao
 * Date: 2015/9/30 0030
 * Time: 10:52
 * Email：muzhitao@vchangyi.com
 */
namespace Activity\Controller\Api;

class ListController extends AbstractController {


	/**
	 * 活动报名列表
	 * @return bool
	 */
	public function List_get() {

		$status = I('get.status', '', 'intval');
		$page = I('get.page', 1, 'intval');

		$serv_a = D('Activity/Activity', 'Service');

		list($start, $limit, $page) = page_limit($page, $this->_plugin->setting['perpage']);

		// 分页数组
		$page_option = array($start, $limit);
		// 排序
		$orderby = array('created' => 'DESC');

		$result = $serv_a->activity_list($status, $page_option, $orderby);

		$serv_a->format_list($result);
		// 返回数据
		$this->_result = array(
			'page' => $page,
			'list' => $result
		);

		return true;
	}
}
