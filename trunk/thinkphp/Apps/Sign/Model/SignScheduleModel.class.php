<?php
/**
 * SignScheduleModel.class.php
 * $author$
 */

namespace Sign\Model;

class SignScheduleModel extends \Common\Model\AbstractModel {


    //排班周期单位：天
    const CYCLE_UNIT_DAY = 1;

    //排班周期单位：周
    const CYCLE_UNIT_WEEK = 2;

    //排班周期单位：月
    const CYCLE_UNIT_MONTH = 3;

    //排班状态：排班
    const SCHEDULE_WORK_STATUS = 1;

    //排班状态：休息
    const REST_WORK_STATUS = 2;

    //排班状态：休息日上班
    const REST_AND_WORK_STATUS = 3;

    //关闭考勤范围
    const SIGN_RANGE_OFF = 0;

    //开启考勤范围
    const SIGN_RANGE_ON = 1;

    //启用排班
    const SCHEDULE_ENABLED = 2;

    //禁用排班
    const SCHEDULE_DISABLE = 1;

    //排班禁用中
    const SCHEDULE_DISABLING = 3;

    //排班对象 全公司
    const SCHEDULE_ALL = 1;

    //排班对象 其他部门
    const SCHEDULE_DEPT = 2;

    //今天已经有签到
    const ALREADY_SIGN = 1;

    //今天还没有签到
    const NOT_SIGN = 2;

	const TODAY = 1;

	const YESTERDAY = 2;



	// 构造方法
	public function __construct() {

		parent::__construct();

	}


    /**
     * 根据条件查询排班总数
     * @param $params
     * @return array
     */
    public function count_by_params($params) {

        $where_params = array();
        // 更新条件
        $wheres = array();

        // 状态条件
        $wheres[] = "`{$this->prefield}status`<?";
        $where_params[] = $this->get_st_delete();

        // 部门
        if (!empty($params['cdid_array'])) {
            $wheres[] = "cd_id in (?)";
            $where_params[] = $params['cdid_array'];
        }

        if(!empty($params['start_time'])){
            $wheres[] = "schedule_begin_time >= ?";
            $where_params[] = $params['start_time'];
        }

        if(!empty($params['end_time'])){
            $wheres[] = "schedule_end_time <= ?";
            $where_params[] = $params['end_time'];
        }

        return $this->_m->result("SELECT COUNT(*) FROM __TABLE__ WHERE ".implode(' AND ', $wheres), $where_params);
    }


    /**
     * 根据条件查询排班列表 分页
     * @param $params
     * @param $page_option
     * @param $order_option
     * @return array
     */
    public function list_page($params, $page_option, $order_option){

        $sql = "SELECT ocd.cd_name as cd_name, oss.* FROM __TABLE__ oss
                LEFT JOIN oa_common_department ocd on oss.cd_id=ocd.cd_id ";

        // 查询条件
        $where = array('oss.status<?');
        $where_params = array($this->get_st_delete());

        // 部门
        if (!empty($params['cdid_array'])) {
            $where[] = "oss.cd_id in (?)";
            $where_params[] = $params['cdid_array'];
        }

        if(!empty($params['start_time'])){
            $where[] = "oss.schedule_begin_time >= ?";
            $where_params[] = $params['start_time'];
        }

        if(!empty($params['end_time'])){
            $where[] = "oss.schedule_end_time <= ?";
            $where_params[] = $params['end_time'];
        }

        // 分页参数
        $limit = '';
        $this->_limit($limit, $page_option);
        // 排序
        //$orderby = '';
        //$this->_order_by($orderby, $order_option);
        $order_by = 'ORDER BY cd_id , created desc';

        return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where) . "{$order_by}{$limit}", $where_params);

    }

    public function list_batch_in_schedule($sbid, $ssid, $flag){
        $sql = "SELECT * FROM __TABLE__";

        // 查询条件
        $where = array('status<?');
        $where_params = array($this->get_st_delete());

        // 班次，格式为,1,2,3,
        if (!empty($sbid)) {
            $where[] = "sbid like ?";
            $where_params[] = '%' . $sbid . ',' . '%';
        }

        if (!empty($ssid)) {
            $where[] = "id = ?";
            $where_params[] = $ssid;
        }

        if(empty($flag)){
            // 只有禁用的排班，该班次才能修改
            $where[] = "enabled != ?";
            $where_params[] = 1;
        }
        return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);

    }

    /**
     * 查询非全公司、已启用的数量
     * @return array
     */
    public function count_no_allcompany(){
        $sql = "SELECT count(*) FROM __TABLE__";

        // 查询条件
        $where = array('status<?');
        $where_params = array($this->get_st_delete());

        $where[] = "cd_id != 0";
        $where[] = "enabled != 1";

        return $this->_m->result($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
    }


    public function list_schedule($params){

        $sql = "SELECT ocd.cd_name as cd_name, oss.* FROM __TABLE__ oss
                LEFT JOIN oa_common_department ocd on oss.cd_id=ocd.cd_id ";

        // 查询条件
        $where = array('oss.status<?');
        $where_params = array($this->get_st_delete());

        // 部门
        if (!empty($params['cdid_array'])) {
            $where[] = "oss.cd_id in (?)";
            $where_params[] = $params['cdid_array'];
        }

        if(!empty($params['enabled'])){
            $where[] = "oss.enabled != ?";
            $where_params[] = $params['enabled'];
        }

        return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);

    }

	public function list_schedule_by_params($params){

		$sql = "SELECT * FROM __TABLE__ ";

		// 查询条件
		$where = array('status<?');
		$where_params = array($this->get_st_delete());

		// 部门
		if (!empty($params['cdid_array'])) {
			$where[] = "cd_id in (?)";
			$where_params[] = $params['cdid_array'];
		}

		if(!empty($params['enabled'])){
			$where[] = "enabled != ?";
			$where_params[] = $params['enabled'];
		}

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);

	}

//    public function common_list_schedule($params){
//        $sql = "SELECT * FROM __TABLE__ ";
//
//        // 查询条件
//        $where = array('status<?');
//        $where_params = array($this->get_st_delete());
//
//        // 部门
//        if (!empty($params['cdid_array'])) {
//            $where[] = "cd_id in (?)";
//            $where_params[] = $params['cdid_array'];
//        }
//
//        if(!empty($params['enabled'])){
//            $where[] = "enabled != ?";
//            $where_params[] = $params['enabled'];
//        }
//
//        return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
//    }


    public function get_schedule_for_dep($params){

        $sql = "SELECT * FROM __TABLE__";

        // 查询条件
        $where = array('status<?');
        $where_params = array($this->get_st_delete());

        // 部门
        if (!empty($params['cd_id'])) {
            $where[] = "(cd_id = ? or cd_id = 0)";
            $where_params[] = $params['cd_id'];
        }

        if(!empty($params['enabled'])){
            $where[] = "enabled != ?";
            $where_params[] = $params['enabled'];
        }

        return $this->_m->fetch_row($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
    }

    public function get_schedule_for_deps($params){

        $sql = "SELECT * FROM __TABLE__";

        // 查询条件
        $where = array('status<?');
        $where_params = array($this->get_st_delete());

        // 部门
        if (!empty($params['cd_id'])) {
            $where[] = "cd_id = ?";
            $where_params[] = $params['cd_id'];
        }

        if(!empty($params['enabled'])){
            $where[] = "enabled != ?";
            $where_params[] = $params['enabled'];
        }

        return $this->_m->fetch_row($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
    }


}
