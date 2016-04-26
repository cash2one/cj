<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 16/1/30
 * Time: 上午10:49
 */
namespace Home\Controller\Apicp;

class ApplicationController extends AbstractController {

	/**
	 * 列表接口
	 */
	public function Index() {

		$params = I('get.');
		$params['s_time'] = '2016-01-11';
		$params['e_time'] = '2016-01-31';
		// 判断是否为空
		if (empty($params['page'])) {
			$page = 1;
			$params['page'] = 1;
		}
		if (empty($params['limit'])) {
			$limit = 10;
			$params['limit'] = 10;
		}
		// 分页参数
		list($start, $limit, $page) = page_limit($page, $limit);
		// 分页参数
		$page_option = array($start, $limit);
		$serv_company = D('Home/StatCompany', 'Service');

		$list = $serv_company->list_by_conds_cp($params, $page_option);

		//返回值
		$this->_response($list);
	}

}