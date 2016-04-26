<?php
/**
 * SignScheduleLogModel.class.php
 * $author$
 */

namespace Sign\Model;

class SignScheduleLogModel extends \Common\Model\AbstractModel {



	// 构造方法
	public function __construct() {

		parent::__construct();

	}

    public function get_later_schedule_log($params){

        $sql = "SELECT * FROM __TABLE__";
        $order_by = "ORDER BY created DESC LIMIT 1";

        // 查询条件
        $where = array('status<?');
        $where_params = array($this->get_st_delete());

        // 部门
        if (!empty($params['cd_id'])) {
            $where[] = "cd_id = ?";
            $where_params[] = $params['cd_id'];
        }

        if(!empty($params['schedule_id'])){
            $where[] = "schedule_id = ?";
            $where_params[] = $params['schedule_id'];
        }

        return $this->_m->fetch_row($sql . ' WHERE ' . implode(' AND ', $where) ."{$order_by}" , $where_params);

    }

    public function get_schedule_history($params){

        $sql = "SELECT * FROM __TABLE__";

        // 查询条件
        $where = array('status<?');
        $where_params = array($this->get_st_delete());

        // 部门
        if (!empty($params['cd_id'])) {
            $where[] = "cd_id = ?";
            $where_params[] = $params['cd_id'];
        }

        if(!empty($params['schedule_id'])){
            $where[] = "schedule_id = ?";
            $where_params[] = $params['schedule_id'];
        }

        // 指定日期 格式y-m-d
        if(!empty($params['time'])){
            $where[] = "FROM_UNIXTIME(begin_time, '%Y-%m-%d') <= ?";
            $where_params[] = $params['time'];
            $where[] = "FROM_UNIXTIME(end_time, '%Y-%m-%d') >= ?";
            $where_params[] = $params['time'];
        }

        return $this->_m->fetch_row($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
    }

}
