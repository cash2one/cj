<?php
/**
 * SignRecordModel.class.php
 * $author$
 */

namespace Sign\Model;

class SignBatchModel extends AbstractModel {

    //班次类型 常规
    const BTACH_COMMON_TYPE = 1;

    //班次类型 弹性
    const BTACH_ELASTIC_TYPE = 2;

    //启用加班
    const LATE_RANGE_OFF = 0;

    //禁用加班
    const LATE_RANGE_ON = 1;

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 根据id获取班次信息
	 * @param unknown $sbid
	 */
	public function get($sbid) {
		$sql = "SELECT * FROM __TABLE__";
		//搜索条件
		$where = array(
			'sbid = ?',

		);
		//搜索值
		$where_params = array(
			$sbid,

		);

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

	/**
	 * 根据条件查询班次
	 * @param $all_batch
	 * @return array
	 */
	public function list_by_condition($all_batch) {
		$sql = "SELECT * FROM __TABLE__";
		//拼装查询条件
		$where[] = "sbid IN (?)";
		$where_params[] = $all_batch;
		//不查询已经删除的
		$where[] = "status < ?";
		$where_params[] = $this->get_st_delete();

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

	/**
	 * 获取开启的班次
	 * @return array
	 */
	public function list_by_enable_cond() {
		$sql = "SELECT * FROM __TABLE__";

		return $this->_m->fetch_array($sql . ' WHERE ' . '`enable` = 1' . ' AND ' . '`status` < ' . $this->get_st_delete());
	}


	/**
	 * [list_by_true_bc 获取可行班次的数据(上班)]
	 * @return [type] [description]
	 */
	public function list_by_true_bc() {

		// 搜索条件
		$where = array(
			'enable = ?',
			'status < ?',
			'start_begin < ?',
			'work_begin <= ?',
			'work_begin > ?',
			'sign_on = ?'
		);

		$work_begin = rgmdate(NOW_TIME + 360, 'Hi');
		$work_now = rgmdate(NOW_TIME - 360, 'Hi');

		// 搜索值
		$where_params = array(1, $this->get_st_delete(), NOW_TIME, $work_begin, $work_now, 1);

		return $this->_m->fetch_array('SELECT * FROM __TABLE__ WHERE ' . implode(' AND ', $where) . ' AND (`start_end`>' .NOW_TIME . ' OR `start_end`=0)', $where_params);
	}

	/**
	 * [list_by_true_bc_off 获取可行班次的数据(下班)]
	 * @return [type] [description]
	 */
	public function list_by_true_bc_off() {

		// 搜索条件
		$where = array(
			'enable = ?',
			'status < ?',
			'start_begin < ?',
			'sign_off = ?'
		);

		// 取前后 5 分钟的记录
		$work_end = rgmdate(NOW_TIME - 360, 'Hi');
		$work_now = rgmdate(NOW_TIME + 360, 'Hi');

		$work_end_big = 2330 < $work_end ? $work_end : ($work_end + 2400);
		$work_now_big = $work_now + 2400;
		// 搜索值
		$where_params = array(1, $this->get_st_delete(), NOW_TIME, 1);

		return $this->_m->fetch_array('SELECT * FROM __TABLE__ WHERE ' . implode(' AND ', $where) . ' AND (`start_end`>' .NOW_TIME . ' OR `start_end`=0) AND ((`work_end`<=2330 AND `work_end`<'. $work_now . ' AND `work_end`>=' . $work_end . ') OR (`work_end`>2330 AND `work_end`<'. $work_now_big .' AND `work_end`>=' . $work_end_big . '))', $where_params);
	}

	/**
	 * [get_batch_info 根据班次id返回班次数据]
	 * @param  [type] $true_sbids [传递的班次id数组]
	 * @return [type]             [返回的数据]
	 */
	public function get_batch_info($true_sbids) {

		$sql = "SELECT * FROM __TABLE__";
		//拼装查询条件
		$where[] = "sbid IN (?)";
		$where_params[] = $true_sbids;
		//不查询已经删除的
		$where[] = "status < ?";
		$where_params[] = $this->get_st_delete();

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}


    public function list_by_conds_for_like($conds, $page_option = null, $order_option = array()){
        $params = array();
        // 条件
        $wheres = array();

        // name
        if (!empty($conds['name'])) {
            $wheres[] = "name LIKE ?";
            $params[] = '%' . $conds['name'] . '%';
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

        // 读取记录
        return $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE ".implode(' AND ', $wheres)."{$orderby}{$limit}", $params);
    }

    public function count_by_conds_for_like($conds){
        $params = array();
        // 更新条件
        $wheres = array();
        // name
        if (!empty($conds['name'])) {
            $wheres[] = "name LIKE ?";
            $params[] = '%' . $conds['name'] . '%';
        }

        // 状态条件
        $wheres[] = "`{$this->prefield}status`<?";
        $params[] = $this->get_st_delete();

        return $this->_m->result("SELECT COUNT(*) FROM __TABLE__ WHERE ".implode(' AND ', $wheres), $params);
    }


	public function list_batch_orderby_work_begin($sbids) {
		$sql = "SELECT * FROM __TABLE__";
		//拼装查询条件
		$where[] = "sbid IN (?)";
		$where_params[] = $sbids;
		//不查询已经删除的
		$where[] = "status < ? ";
		$where_params[] = $this->get_st_delete();
		$order_by = " ORDER BY FROM_UNIXTIME(work_begin,'%H:%i') ASC";
		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where) . "{$order_by}", $where_params);
	}




}
