<?php
/**
 * SignRecordModel.class.php
 * $author$
 */

namespace Sign\Model;

class SignDetailModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		$this->prefield = 'sd_';
		parent::__construct();
	}

	/**
	 * 根据签到记录id获取备注信息
	 * @param array $srids 签到记录id
	 */
	public function get_in_srids($srids, $date) {

		$sql = "SELECT * FROM __TABLE__";
		//查询条件
		$where = array(
			'sr_id IN (?)',
			'sd_status < ?'
		);
		$where_params = array(
			$srids,
			$this->get_st_delete()
		);

        if(!empty($date)){
            $where[] = "FROM_UNIXTIME(sd_created,'%Y-%m-%d') = ?";
            $where_params[] = $date;
        }

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

	/**
	 * 查询 本次提交的备注 相关的 type和签到id 备注
	 * @param $post
	 * @return array
	 */
	public function list_by_reason_post($post) {

		$sql = "SELECT * FROM __TABLE__";
		//查询条件
		$where = array(
			'sr_id = ?',
			'type = ?',
			'sd_status < ?'
		);
		$where_params = array(
			$post['id'],
			$post['type'],
			$this->get_st_delete()
		);

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}


    public function list_by_params($conds) {

        $params = array();
        // 条件
        $wheres = array();

        // 状态条件
        $wheres[] = "`sd_status`<?";
        $params[] = $this->get_st_delete();

        if(!empty($conds['sr_id'])){
            $wheres[] = "sr_id = ?";
            $params[] = $conds['sr_id'];
        }

        if(!empty($conds['start_time'])){
            $wheres[] = "sd_created >= ?";
            $params[] = $conds['start_time'];
        }
        if(!empty($conds['end_time'])){
            $wheres[] = "sd_created <= ?";
            $params[] = $conds['end_time'];
        }


        // 读取记录
        return $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE ".implode(' AND ', $wheres), $params);
    }
}
