<?php
/**
 * SignRecordModel.class.php
 * $author$
 */

namespace Sign\Model;

class SignRecordModel extends AbstractModel {

    //考勤类型：上班
    const SIGN_TYPE_ON = 1;

    //考勤类型：下班
    const SIGN_TYPE_OFF = 2;

	// 构造方法
	public function __construct() {
		
		$this->prefield = 'sr_';
		parent::__construct();
	}

	public function get_sign_record($params) {
		$sql = "SELECT * FROM __TABLE__";
		// 查询条件

		$end = rstrtotime($params['udate']) + 86400;


		$where = array(
			'm_uid = ?',
			'sr_created > ?',
			'sr_created < ?',
			'sr_status < ?'
		);
		$where_params = array(
			$params['m_uid'],
			rstrtotime($params['udate']),
			$end,
			$this->get_st_delete()
		);

		if(!empty($params['cd_id'])){
			$where[] = '(cd_id = ? or cd_id=0)';
			$where_params[] = $params['cd_id'];
		}
		if(!empty($params['sr_batch'])){
			$where[] = 'sr_batch = ?';
			$where_params[] = $params['sr_batch'];
		}

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);

	}

	public function get_sign_record_by_sbid($params){
		$sql = "SELECT sr_batch FROM __TABLE__";
		// 查询条件

		$end = rstrtotime($params['udate']) + 86400;


		$where = array(
			'm_uid = ?',
			'sr_created > ?',
			'sr_created < ?',
			'sr_status < ?'
		);
		$where_params = array(
			$params['m_uid'],
			rstrtotime($params['udate']),
			$end,
			$this->get_st_delete()
		);

		if(!empty($params['cd_id'])){
			$where[] = '(cd_id = ? or cd_id=0)';
			$where_params[] = $params['cd_id'];
		}

		if(!empty($params['sr_batch'])){
			$where[] = 'sr_batch = ?';
			$where_params[] = $params['sr_batch'];
		}


		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where) . "{$group_by}", $where_params);

	}
	/**
	 * 当天考勤记录不重复的班次Id
	 * @param unknown $params
	 * @return unknown
	 */
	public function get_sign_record_groupby_sbid($params) {
		$sql = "SELECT sr_batch FROM __TABLE__";
		// 查询条件

		$end = rstrtotime($params['udate']) + 86400;


		$where = array(
			'm_uid = ?',
			'sr_created > ?',
			'sr_created < ?',
			'sr_status < ?'
		);
		$where_params = array(
			$params['m_uid'],
			rstrtotime($params['udate']),
			$end,
			$this->get_st_delete()
		);

		if(!empty($params['cd_id'])){
			$where[] = '(cd_id = ? or cd_id=0)';
			$where_params[] = $params['cd_id'];
		}
		$group_by = 'group by sr_batch';
		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where) . "{$group_by}", $where_params);

	}

	public function list_by_condition($params) {
		$sql = "SELECT * FROM __TABLE__";
		// 条件
		$wheres = array(
			'sr_created > ?',
			'sr_created < ?',
			'm_uid = ?',
			'sr_status< ?'
		);
		//值
		$where_params = array(
			$params['stime'],
			$params['etime'],
			$params['m_uid'],
			$this->get_st_delete()
		);

		return $this->_m->fetch_array($sql . " WHERE " . implode(' AND ', $wheres), $where_params);
	}


    public function list_by_condition_new($params) {
        $sql = "SELECT * FROM __TABLE__";
        // 条件
        $wheres = array(
            'FROM_UNIXTIME(sr_created, "%Y-%m-%d") >= ?',
            'FROM_UNIXTIME(sr_created, "%Y-%m-%d") <= ?',
            'm_uid = ?',
            'sr_status< ?'
        );
        //值
        $where_params = array(
            $params['stime'],
            $params['etime'],
            $params['m_uid'],
            $this->get_st_delete()
        );

        if(!empty($params['dep_id'])){
            $wheres[] = 'cd_id = ?';
            $where_params[] = $params['dep_id'];
        }

        return $this->_m->fetch_array($sql . " WHERE " . implode(' AND ', $wheres), $where_params);
    }

    /**
     * 查询最近一点公司考勤的打卡记录
     * @param $params
     * @return array
     */
    public function get_by_condition_new($params) {
        $sql = "SELECT * FROM __TABLE__";
        // 条件
        $wheres = array(
            'm_uid = ?',
            'sr_status< ?'
        );
        //值
        $where_params = array(
            $params['m_uid'],
            $this->get_st_delete()
        );

        // 排序
        $orderby = '';
        $this->_order_by($orderby, array('sr_id' => 'DESC'));

        return $this->_m->fetch_array($sql . " WHERE " . implode(' AND ', $wheres) . "{$orderby}" . " LIMIT 1", $where_params);
    }

	/**
	 * 根据时间段 获取 关于某人的签到记录
	 * @param $btime
	 * @param $batch_id 班次id
	 * @param $m_uid
     * @param $_ssid 排班id
	 * @return array
	 */
	public function get_by_time($btime, $batch_id, $m_uid, $sr_type) {
		$sql = "SELECT * FROM __TABLE__";
		$wheres = array(
			'sr_signtime >= ?',
			'm_uid = ?',
            'sr_batch = ?',
            'sr_type = ?',
			'sr_status< ?'
		);
		$where_params = array(
			$btime,
			$m_uid,
            $batch_id,
            $sr_type,
			$this->get_st_delete()
		);

		return $this->_m->fetch_array($sql . " WHERE " . implode(' AND ', $wheres), $where_params);
	}



	// 查询签到的数据[上班]
	public function list_by_qdtime_on($params) {

		$sql = "SELECT * FROM __TABLE__";
		// 查询条件

		$end = rstrtotime($params['udate']) + 86400;

		$where = array(
			//'sr_created > ?',
			//'sr_created < ?',
			'sr_type = ?'
		);
		$where_params = array(
			//NOW_TIME + 3600*3 + 1000,
			//NOW_TIME,
			1
		);

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

	// 查询签到的数据[下班]
	public function list_by_qdtime_off($params) {

		$sql = "SELECT * FROM __TABLE__";
		// 查询条件

		$end = rstrtotime($params['udate']) + 86400;

		$where = array(
			'sr_created >= ?',
			'sr_created < ?',
			'sr_type = ?'
		);
		$where_params = array(
			up_work,
			NOW_TIME,
			2
		);

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

	// 获取此时上班已经签过到的人员数据
	public function get_sbid_qd_muid($k, $v) {

		$sql = "SELECT * FROM __TABLE__";
		// 查询条件
		$h = substr($v['work_begin'], 0, -2);
		$m = substr($v['work_begin'], -2);
		$h_m_time = $h . ":" . $m;

		/*var_dump(NOW_TIME);
		var_dump(rgmdate(NOW_TIME, 'Y-m-d H:i:s'));
		var_dump(strtotime(rgmdate(NOW_TIME, 'Y-m-d H:i:s')));die;*/
		//var_dump(strtotime(rgmdate(NOW_TIME, 'Y-m-d') . " " . $h_m_time) - 60*60*3);die;
		$where = array(
			'sr_batch = ?',
			'sr_type = ?',
			'sr_created <= ?',
			'sr_created > ?',
			'sr_status < ?'
		);
		$where_params = array(
			$k,
			1, // 上班
			rstrtotime(rgmdate(NOW_TIME, 'Y-m-d H:i:s')),
			rstrtotime(rgmdate(NOW_TIME, 'Y-m-d') . " " . $h_m_time) - 60*60*3,
			64
		);

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

	// get_sbid_qd_muid_off
	// 获取此时下班已经签过到的人员数据
	public function get_sbid_qd_muid_off($k, $v) {

		$sql = "SELECT * FROM __TABLE__";
		// 查询条件
		$h = substr($v['work_begin'], 0, -2);
		$m = substr($v['work_begin'], -2);
		$sign = 0; // 跨天标记
		if ($h >= 24) {
			$h = $h - 24;
			$sign = 1;
		}

		$h_m_time = $h . ":" . $m;

		/*var_dump(strtotime(rgmdate(NOW_TIME, 'Y-m-d') . " " . $h_m_time) + $sign * 86400);die;*/
		/*var_dump(strtotime(rgmdate(NOW_TIME, 'Y-m-d H:i:s')));
		var_dump(strtotime(rgmdate(NOW_TIME, 'Y-m-d') . " " . $h_m_time));die;*/
		$where = array(
			'sr_batch = ?',
			'sr_type = ?',
			'sr_created <= ?',
			'sr_created >= ?',
			'sr_status < ?'
		);

		$where_params = array(
			$k,
			2, // 下班
			rstrtotime(rgmdate(NOW_TIME, 'Y-m-d H:i:s')),
			rstrtotime(rgmdate(NOW_TIME, 'Y-m-d') . " " . $h_m_time) + $sign * 86400,
			64
		);

	

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

    public function list_sign_record_by_params($params) {

        $where_params = array();
        // 更新条件
        $wheres = array();

        // 状态条件
        $wheres[] = "`{$this->prefield}status`<?";
        $where_params[] = $this->get_st_delete();

        if(!empty($params['sr_batch'])){
            $wheres[] = "sr_batch = ?";
            $where_params[] = $params['sr_batch'];
        }

		if(!empty($params['cd_id'])){
			$wheres[] = "cd_id = ?";
			$where_params[] = $params['cd_id'];
		}

        $wheres[] = "m_uid = ?";
        $where_params[] = $params['m_uid'];

        $wheres[] = "FROM_UNIXTIME(sr_created, '%Y-%m-%d') = ?";
        $where_params[] = $params['sr_created'];

        return $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE ".implode(' AND ', $wheres), $where_params);
    }

    public function select_later_date($params){
        $sql = "select sr_created, schedule_id from __TABLE__ ";
        $group_by = "GROUP BY  sr_batch , FROM_UNIXTIME(sr_created, '%Y-%m-%d')";
        $order_by = "ORDER BY sr_created desc LIMIT 1";
        $where[] = "cd_id = ?";
        $where_params[] = $params['cd_id'];

        $where[] = "m_uid = ?";
        $where_params[] = $params['m_uid'];

        $where[] = "sr_status < ?";
        $where_params[] = $this->get_st_delete();

        return $this->_m->fetch_row($sql . ' WHERE ' . implode(' AND ', $where) ."{$group_by}{$order_by}", $where_params);
    }

    public function list_later_batch($params){
        $sql = "select osb.sbid,osb.`name` from __TABLE__  osr
                LEFT JOIN oa_sign_batch osb on osr.sr_batch=osb.sbid ";

        $group_by = "GROUP BY osb.sbid";

        $where[] = "osr.cd_id = ?";
        $where_params[] = $params['cd_id'];

        $where[] = "osr.m_uid = ?";
        $where_params[] = $params['m_uid'];

        $where[] = "FROM_UNIXTIME(osr.sr_work_begin,'%Y-%m-%d') = ?";
        $where_params[] = $params['sr_work_begin'];

        $where[] = "sr_status<?";
        $where_params[] = $this->get_st_delete();

        return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where)."{$group_by}", $where_params);
    }

    public function list_by_conds_for_record($conds, $page_option = null, $order_option = array()) {

        $params = array();
        // 条件
        $wheres = array();

        if (!empty($conds['signtime_min'])) { // 发起时间
            $wheres[] = "FROM_UNIXTIME(sr_created, '%Y-%m-%d') >= ?";
            $params[] = $conds['signtime_min'];
        }
        if (!empty($conds['signtime_max'])) { // 发起时间
            $wheres[] = "FROM_UNIXTIME(sr_created, '%Y-%m-%d') <= ?";
            $params[] = $conds['signtime_max'];
        }
        if (!empty($conds['m_username'])) { // 发起人
            $wheres[] = "m_username like ?";
            $params[] = "%" . $conds['m_username'] . "%";
        }
        if (!empty($conds['sr_type'])) { // 类型
            $wheres[] = 'sr_type = ?';
            $params[] = $conds['sr_type'];
        }
        if (! empty($conds['sr_sign'])) { // 状态
            $wheres[] = 'sr_sign = ?';
            $params[] = $conds['sr_sign'];
        }
        if (!empty($conds['cd_id'])) { // 部门搜索条件
            //把部门条件换成m_uid
//            $serv_member_dep = &service::factory('voa_s_oa_member_department');
//            $conds_dep['cd_id'] = $conds['cd_id'][0];
//            $mem_list = $serv_member_dep->fetch_all_by_conditions($conds_dep);
//            $conds['m_uid'] = array();
//            if(!empty($mem_list)){
//                foreach($mem_list as $val){
//                    $muids[] = $val['m_uid'];
//                }
//                $conds['m_uid'] = $muids;
//            }
            $wheres[] = 'cd_id = ?';
            $params[] = $conds['cd_id'];
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




    /**
     * 根据条件读取数据数组
     * @param array $conds 条件数组
     * @param array $order_option 排序
     * @throws service_exception
     */
    public function list_by_conds_for_export($conds, $page_option = null, $order_option = array()) {

        $params = array();
        // 条件
        $wheres = array();

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

        $sql = "SELECT om.m_uid uid,om.m_username username,osi.* FROM oa_member om
                  left join(select osc.*,FROM_UNIXTIME(osc.sr_created, '%Y-%m-%d') signtime, ocd.cd_name cd_name, osb.name batch_name
                  from oa_sign_record osc LEFT JOIN oa_common_department ocd ON ocd.cd_id = osc.cd_id
                  left join oa_sign_batch osb on osb.sbid = osc.sr_batch
                  WHERE osc.sr_status < 3 AND ocd.cd_status < 3";

        // 部门
        if(!empty($conds['cd_id'])){
            $sql .= " and osc.cd_id =" . $conds['cd_id'];
        }

        // 签到类型
        if(!empty($conds['sr_type'])){
            $sql .= " and osc.sr_type =" . $conds['sr_type'];
        }

        // 考勤状态
        if(!empty($conds['sr_sign'])){
            $sql .= " and osc.sr_sign =" . $conds['sr_sign'];
        }

        // 姓名
        if(!empty($conds['m_username'])){
            $sql .= " and osc.m_username like " . "'%" . $conds['m_username'] . "%'";
        }

        $sql .= " AND FROM_UNIXTIME(osc.sr_created, '%Y-%m-%d') BETWEEN '" . $conds['signtime_min'] . "' AND '" . $conds['signtime_max'] .
            "') osi on osi.m_uid = om.m_uid  where om.m_qywxstatus = 1";

        if(!empty($conds['m_uid'])){
            $sql .= " and om.m_uid in (" . implode(',', $conds['m_uid']) . ")";
        }

        $sql .= " order by osi.signtime {$limit}";

        return $this->_m->query($sql);
        // 读取记录
       // return $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE ".implode(' AND ', $wheres)."{$orderby}", $params);
    }


}
