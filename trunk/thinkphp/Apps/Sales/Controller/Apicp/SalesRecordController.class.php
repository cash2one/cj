<?php
/**
 * SalesRecordController.class.php
 * $author$
 */
namespace Sales\Controller\Apicp;

class SalesRecordController extends AbstractController {

	/**
	 * 商机状态变更记录列表查询
	 * $author zhubeihai
	 */
	public function List_business_modify_record_get() {

		$params = I('request.');
		$list_record = array ();
		// 每页条数
		$limit = (int)$params["limit"];
		$page = $params["page"];
		$order_by = $params["orderby"];

		// 判断每页条数是否正确 ,如果不合法赋予系统默认值
		if ($limit < cfg('perpage_min') || $limit > cfg('perpage_max')) {
			$limit = $this->_plugin->setting['perpage'];
		}
		list($start, $limit, $page) = page_limit($page, $limit);
		// 分页参数
		$page_option = array (
			$start,
			$limit
		);

		$serv_sb = D('Sales/SalesRecord', 'Service');
		if (!$serv_sb->list_business_modify_record($list_record, $params, $page_option, $order_by)) {
			E($serv_sb->get_errcode().':'.$serv_sb->get_errmsg());
			return false;
		}
		$serv_fmt = D('Sales/Format', 'Service');
		$serv_fmt->record_format($list_record);
		// 列表总数
		$count = $serv_sb->count_by_condition($params);
		$this->_result = array (
			"total" => $count,
			"limit" => $limit,
			"data"  => $list_record
		);

		return true;
	}
}
