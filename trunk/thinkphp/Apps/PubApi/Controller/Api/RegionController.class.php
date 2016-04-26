<?php
/**
 * 城市地区词典
 * User: Muzhitao
 * Date: 2015/12/28
 * Time: 11:28
 * Email：muzhitao@vchangyi.com
 */

namespace PubApi\Controller\Api;
class RegionController extends AbstractController {

	protected $_require_login = false;

	/**
	 * 城市级联列表
	 *
	 * @return bool
	 */
	public function Area_get() {

		// 父类ID
		$parent_id = I('get.parent_id', '', 'intval');
		$serv_region = D('PubApi/Region', 'Service');
		$list = $serv_region->get_region_list($parent_id);

		// 返回数据
		$this->_result = array('list' => $list);

		return true;
	}

}
