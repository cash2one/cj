<?php
/**
 * MemberDepartmentModel.class.php
 * $author$
 */
namespace Common\Model;

use Common\Model\AbstractModel;

class MemberDepartmentModel extends AbstractModel {


	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'md_';
	}

	/**
	 * 根据uid获取关联部门
	 * @param int $uid 用户id
	 * @return array
	 */
	public function list_by_uid($uid) {

		return $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE m_uid=? AND md_status<?", array (
			$uid, $this->get_st_delete()
		));
	}

	/**
	 * 根据部门id获取所有的uid
	 * @param array $cdids 传递的部门id数组
	 * @return array 返回的数据
	 */
	public function list_by_cdid($cdids, $page_option, $order_option = array('m_index' => 'ASC')) {

		// limit
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		// 拼装查询条件
		$wheres[] = "a.cd_id IN (?)";
		$params[] = $cdids;
		// 不查询已经删除的
		$wheres[] = "a.md_status<? AND b.m_status<?";
		$params[] = $this->get_st_delete();
		$params[] = \Common\Model\MemberModel::ST_DELETE;

		return $this->_m->fetch_array('SELECT DISTINCT a.m_uid FROM __TABLE__ AS a
				LEFT JOIN oa_member AS b ON a.m_uid=b.m_uid
				WHERE ' . implode(' AND ', $wheres) . $orderby . $limit, $params);
	}

	public function count_by_cdid($cdids) {

		return $this->_m->result("SELECT COUNT(DISTINCT m_uid) FROM __TABLE__ WHERE cd_id IN (?) AND md_status<?", array(
			(array)$cdids, $this->get_st_delete()
		));
	}

	/**
	 * 获取 部门人数
	 * @param string $cd_ids 所要获取人数的 部门id
	 * @return array
	 * + ct => int numbers 人数
	 */
	public function count_all_department_member_num($cd_ids) {

		return $this->_m->query("SELECT COUNT(*) AS ct, cd_id FROM __TABLE__ WHERE cd_id IN ($cd_ids) AND md_status <" . $this->get_st_delete() . " GROUP BY cd_id");
	}

	/**
	 * 获取部门人数 m_uid去重
	 * @param $cd_ids
	 * @return mixed
	 */
	public function unique_count_all_department_member_num($cd_ids) {

		return $this->_m->fetch_row("SELECT COUNT(DISTINCT m_uid) AS ct FROM __TABLE__ where cd_id IN ($cd_ids) AND md_status <" . $this->get_st_delete());
	}


    /**
     * 根据用户m_uid 查询全部所属部门
     * @param $m_uid
     * @return array
     */
    public function department_bv_mid($m_uid){
        $sql = "SELECT * FROM __TABLE__";

        // 查询条件
        $where = array('md_status<?');
        $where_params = array($this->get_st_delete());

        $where[] = "m_uid = ?";
        $where_params[] = $m_uid;

        return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
    }


	/**
	 * 获取用户职务ID
	 * @param $uid
	 * @return array
	 */
	public function get_mp_id_by_uid($uid) {

		$sql = "SELECT mp_id FROM __TABLE__ WHERE `m_uid`=? AND `md_status`<?";
		$params = array($uid, $this->get_st_delete());

		return $this->_m->fetch_row($sql, $params);
	}

	public function get_by_uid($m_uid) {

		$sql = "SELECT * FROM __TABLE__";
		//拼装查询条件
		$where[] = "m_uid IN (?)";
		$where_params[] = $m_uid;
		//不查询已经删除的
		$where[] = "md_status < ?";
		$where_params[] = $this->get_st_delete();

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}


}
