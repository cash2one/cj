<?php
/**
 * SignRecordModel.class.php
 * $author$
 */

namespace Sign\Model;

class SignLocationModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		$this->prefield = 'sl_';
		parent::__construct();
	}

	/**
	 * 取所有记录
	 */
	public function list_records() {
		$sql = "SELECT * From __TABLE__";

		return $this->_m->fetch_array($sql);
	}

	/**
	 * 获取当天外勤记录
	 * @param unknown $params
	 * @return unknown
	 */
	public function get_out_record($params, $page_option) {
		$sql = "SELECT * FROM __TABLE__";
		$page = $params['page'];
		$limit = $params['limit'];
		// 查询条件
		$where = array('sl_status<?');
		$where_params = array($this->get_st_delete());

		// 用户id
		if (!empty($params['m_uid'])) {
			$where[] = "m_uid = ?";
			$where_params[] = $params['m_uid'];
		}

		//查询时间
		if (!empty($params['udate'])) {
			$where[] = "sl_signtime > ?";
			$where_params[] = rstrtotime($params['udate']);
			$where[] = "sl_signtime < ?";
			$where_params[] = rstrtotime($params['udate']) + 86400;
		}
		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where) . "{$limit}", $where_params);

	}

    public function list_by_condition_new($params) {
        $sql = "SELECT * FROM __TABLE__";
        // 条件
        $wheres = array(
            'FROM_UNIXTIME(sl_signtime, "%Y-%m-%d") >= ?',
            'FROM_UNIXTIME(sl_signtime, "%Y-%m-%d") <= ?',
            'm_uid = ?',
            'sl_status< ? group by FROM_UNIXTIME(sl_signtime, "%Y-%m-%d")'
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

}
