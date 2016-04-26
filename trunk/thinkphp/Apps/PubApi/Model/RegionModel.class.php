<?php
/**
 * 城市数据词典.
 * User: Muzhitao
 * Date: 2015/12/28 0028
 * Time: 11:31
 * Email：muzhitao@vchangyi.com
 */
namespace PubApi\Model;

class RegionModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	public function list_by_condis($conds) {

		$params = array();
		// 条件
		$wheres = array();
		if (! $this->_parse_where($wheres, $params, $conds)) {
			return false;
		}

		// 状态条件
		$wheres[] = "`{$this->prefield}status`<?";
		$params[] = $this->get_st_delete();

		return $this->_m->fetch_array("SELECT region_id,parent_id,region_name,region_type FROM __TABLE__ WHERE " . implode(' AND ', $wheres), $params);
	}
}
