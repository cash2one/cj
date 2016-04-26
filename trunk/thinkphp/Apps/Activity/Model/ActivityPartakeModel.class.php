<?php
/**
 * ActivityModel.class.php
 * $author$
 */

namespace Activity\Model;

class ActivityPartakeModel extends AbstractModel {

	private $__where = "";

	//同意取消
	const CANCEL_TYPE = 3;
	const APPLY_TYPE = 2;
	const JOIN_TYPE = 1;
	const NO_REG = 0;
	// 构造方法
	public function __construct() {

		parent::__construct();

		$this->__where = " AND `status`<".self::ST_DELETE;
	}


	/**
	 * 统计报名人数
	 * @param int $acid
	 * @return int
	 * */
	public function count_reg_num($acid){

		$sql = "SELECT COUNT(*) total FROM __TABLE__ WHERE acid=? AND `type`<".self::CANCEL_TYPE.$this->__where;
		$result = $this->_m->fetch_row($sql, array($acid));

		$total = 0;
		if(!$result){

			return $total;
		}

		$total = $result['total'];

		return $total;
	}

	/**
	 * 查看指定内部人员的报名状态
	 * @param int $acid
	 * @param int $m_uid
	 * @return int
	 * */

	public function get_user_type($acid, $m_uid){

		$sql = "SELECT `type` FROM __TABLE__ WHERE acid=? AND m_uid=?".$this->__where;
		$result = $this->_m->fetch_row($sql, array($acid, $m_uid));
		//如果结果集不存在就返回0
		if(!$result){
			return self::NO_REG;
		}

		return $result['type'];
	}

	/**
	 * 查询活动内部报名人数
	 * @param $acids
	 * @return array
	 */
	public function list_count_by_acid($acids) {

		if (is_array($acids)) {
			$acids = implode(',', $acids);
		}

		$sql ="SELECT `acid`, COUNT(`apid`) AS `_count`	FROM __TABLE__
 				WHERE `acid` IN ({$acids}) AND `type` < ? AND `status`<? GROUP BY `acid`";
		$params = array(self::CANCEL_TYPE, $this->get_st_delete());

		 return $this->_m->fetch_array($sql, $params);

	}

	/**
	 * 条件查询列表 可选择字段
	 * @param $conds
	 * @param $page_option
	 * @param $order_option
	 * @param string $fields
	 * @return array|bool
	 */
	public function fetch_all_by_conds($conds, $page_option, $order_option, $fields = "*") {

		$params = array();
		// 条件
		$wheres = array();
		if (!$this->_parse_where($wheres, $params, $conds)) {
			return false;
		}

		// 状态条件
		$wheres[] = "`{$this->prefield}status`<?";
		$wheres[] = "`{$this->prefield}type`<?";
		$params[] = $this->get_st_delete();
		$params[] = self::CANCEL_TYPE;
		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}

		$sql = "SELECT {$fields} FROM __TABLE__ WHERE ".implode(' AND ', $wheres)."{$orderby}{$limit}";

		return $this->_m->fetch_array($sql, $params);
	}

	/**
	 * 获取详情 优化字段
	 * @param $acid
	 * @param string $field
	 * @return array
	 */
	public function get_detail_by_apid($apid, $field = "*") {

		$sql = "SELECT {$field} FROM __TABLE__ WHERE apid=? AND status<? LIMIT 1";
		$params = array($apid, $this->get_st_delete());

		return $this->_m->fetch_row($sql, $params);
	}
}
