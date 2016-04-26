<?php
/**
 * ActivityModel.class.php
 * $author$
 */

namespace Activity\Model;

class ActivityOutsiderModel extends AbstractModel {

	private $__where = "";

	// 构造方法
	public function __construct() {

		parent::__construct();

		$this->__where = " AND `status`<".self::ST_DELETE;
	}


	/**
	 * 统计外部报名人数
	 * @param int $acid
	 * @return int
	 * */
	public function count_reg_num($acid){

		$sql = "SELECT COUNT(*) total FROM __TABLE__ WHERE acid=? ".$this->__where;
		$result = $this->_m->fetch_row($sql, array($acid));

		$total = 0;
		if(!$result){

			return $total;
		}

		$total = $result['total'];

		return $total;
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

		$sql ="SELECT `acid`, COUNT(`oapid`) AS `_count`	FROM __TABLE__
 				WHERE `acid` IN ({$acids}) AND `status`<? GROUP BY `acid`";
		$params = array($this->get_st_delete());

		return $this->_m->fetch_array($sql, $params);

	}

	/**
	 * 查询单条记录是否存在
	 * @param $acid
	 * @param $mobile
	 * @return array
	 */
	public function get_by_uid_mobile($acid, $mobile) {

		$sql = "SELECT acid FROM __TABLE__ WHERE acid=? AND outphone=? AND status<? LIMIT 1";

		$params = array($acid, $mobile, $this->get_st_delete());

		return $this->_m->fetch_row($sql, $params);
	}

	/**
	 * 查询当前用户报名记录是否存在
	 * @param $acid
	 * @param $mobile
	 * @return array
	 */
	public function get_by_uid_out($acid, $outname, $outphone) {

		$sql = "SELECT oapid FROM __TABLE__ WHERE acid=? AND outname=? AND outphone=? AND status<? LIMIT 1";

		$params = array($acid, $outname, $outphone, $this->get_st_delete());

		$result = $this->_m->fetch_row($sql, $params);

		$return = $result['oapid'];
		return $return;
	}

	/**
	 * 获取外部人员报名列表
	 * @param $conds 查询条件
	 * @param $page_option 分页参数
	 * @param $order_option 排序条件
	 * @param string $fields 自定义查询字段
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
		$params[] = $this->get_st_delete();

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
	 * @param $oapid
	 * @param string $field
	 * @return array
	 */
	public function get_detail_by_oapid($oapid, $field = "*") {

		$sql = "SELECT {$field} FROM __TABLE__ WHERE oapid=? AND status<? LIMIT 1";

		$params = array($oapid, $this->get_st_delete());

		return $this->_m->fetch_row($sql, $params);
	}
}
