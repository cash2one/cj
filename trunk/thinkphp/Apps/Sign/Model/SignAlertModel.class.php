<?php
/**
 * SignRecordModel.class.php
 * $author$
 */

namespace Sign\Model;

class SignAlertModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}


	/**
	 * [insert 插入数据]
	 * @param  [type] $data [传入的数据]
	 * @return [type]       [返回的值]
	 */
	public function insert($data) {

		return $this->_m->insert($data);
	}

	/**
	 * [list_by_on 提醒记录表里面的数据]
	 * @param  [type] $type [数据]
	 * @return [type]       [返回的数据]
	 */
	public function list_by_on($type) {

		$sql = "SELECT * FROM __TABLE__";
		// 查询条件
		$today_start = rstrtotime(rgmdate(NOW_TIME, 'Y-m-d 00:00:00'));
		$today_end = rstrtotime(rgmdate(NOW_TIME, 'Y-m-d 23:59:59'));
		$where = array(
			'type = ?',
			'status < ?',
			'alert_time >= ?',
			'alert_time <= ?'
		);
		$where_params = array(
			$type,
			$this->get_st_delete(),
			$today_start,
			$today_end
		);

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

	/**
	 * 根据条件删除数据
	 * @param $conds
	 * @return mixed
	 */
	public function delete_by_params($conds) {

		$params = array();
		// SET
		$sets = array("`{$this->prefield}status`=?", "`{$this->prefield}deleted`=?");
		$params[] = $this->get_st_delete();
		$params[] = NOW_TIME;
		// 更新条件
		$wheres = array();

		// 状态条件
		$wheres[] = "`{$this->prefield}status`<?";
		$params[] = $this->get_st_delete();

		if(!empty($conds['created'])){
			$wheres[] = "FROM_UNIXTIME(created, '%Y-%m-%d') = ?";
			$params[] = $conds['created'];
		}
		if(!empty($conds['batch_id'])){
			$wheres[] = "batch_id = ?";
			$params[] = $conds['batch_id'];
		}


		return $this->_m->execsql("UPDATE __TABLE__ SET ".implode(',', $sets)." WHERE ".implode(' AND ', $wheres), $params);
	}

}
