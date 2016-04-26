<?php
/**
 * 城市数据词典
 * User: Muzhitao
 * Date: 2015/12/28 0028
 * Time: 11:29
 * Email：muzhitao@vchangyi.com
 */

namespace PubApi\Service;

class RegionService extends AbstractService {

	public function __construct() {

		parent::__construct();
		$this->_region = D("PubApi/Region");
	}

	/**
	 * 城市列表
	 *
	 * @param $parent_id
	 * @return array
	 */
	public function get_region_list($parent_id) {

		$data = array();
		// 如果为空 则筛选省份菜单。否则根据父类ID查询当前所属的城市
		$parent_id = empty($parent_id) ? 1 : $parent_id;
		$conds = array('parent_id' => $parent_id);
		$data = $this->_region->list_by_conds($conds);

		if (empty($data)) {
			$data = array();
		}

		return $data;
	}

}
