<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/11/10
 * Time: 下午4:56
 */
namespace Askfor\Model;

class AskforProcModel extends AbstractModel {

	const ASKING = 1; // 审批中
	const ASKPASS = 2; // 审核通过
	const TURNASK = 3; // 转审批
	const ASKFAIL = 4; // 审批不通过
	const COPYASK = 5; // 抄送
	const PRESSASK = 6; // 催办
	const CENCEL = 7; // 已撤销

	const PRESS_TIME = 300;

	// 构造方法
	public function __construct() {

		$this->prefield = 'afp_';
		parent::__construct();
	}

	/**
	 * 根据审批ID 查询
	 * @param $af_id 审批ID
	 * @return array
	 */
	public function list_by_afid($af_id) {

		$sql = "SELECT * FROM __TABLE__";

		// 查询条件
		$where = array(
			'af_id = ?',
			'afp_status < ?',
		);
		$where_params = array(
			$af_id,
			$this->get_st_delete(),
		);

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where) . 'ORDER BY `afp_updated` DESC, `afp_created` DESC', $where_params);
	}

	/**
	 * 根据审批ID 和 状态查询
	 * @param $af_id 审批ID
	 * @param $afp_condition 审批状态
	 * @return array
	 */
	public function list_in_condition($af_id, $afp_condition) {

		$sql = "SELECT * FROM __TABLE__";

		// 查询条件
		$where = array(
			'af_id = ?',
			'afp_condition IN (?)',
			'afp_status < ?',
		);
		$where_params = array(
			$af_id,
			$afp_condition,
			$this->get_st_delete(),
		);

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

	/**
	 * 根据用户id 和 审批id 查询
	 * @param $uid
	 * @param $af_id
	 * @return array
	 */
	public function get_proc_by_uid_afid($uid, $af_id) {

		$sql = "SELECT * FROM __TABLE__";

		// 查询条件
		$where = array(
			'm_uid = ?',
			'af_id = ?',
			'afp_status < ?',
		);
		$where_params = array(
			$uid,
			$af_id,
			$this->get_st_delete(),
		);

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

	/**
	 * 查询第几级审批人
	 * @param $af_id
	 * @param $afp_level
	 * @return array
	 */
	public function get_by_afid_level($af_id, $afp_level) {

		$sql = "SELECT * FROM __TABLE__";

		// 查询条件
		$where = array(
			'af_id = ?',
			'afp_level = ?',
			'afp_status < ?',
		);
		$where_params = array(
			$af_id,
			$afp_level,
			$this->get_st_delete(),
		);

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

	/**
	 * 根据 uid 和 审批状态查询
	 * @param $m_uid
	 * @param $afp_condition
	 * @return array
	 */
	public function get_by_uid_cond($m_uid, $afp_condition) {

		$sql = "SELECT * FROM __TABLE__";

		// 查询条件
		$where = array(
			'm_uid = ?',
			'afp_condition IN (?)',
			'afp_status < ?',
		);
		$where_params = array(
			$m_uid,
			$afp_condition,
			$this->get_st_delete(),
		);

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

}
