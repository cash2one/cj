<?php
/**
 * voa_d_oa_file_attr
 * 首页
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_file_attr extends voa_d_abstruct {

	/** 初始化 */
	public function __construct() {
		/** 表名 */
		$this->_table = 'orm_oa.file_attr';
		/** 允许的字段 */
		$this->_allowed_fields = array('fla_id', 'at_id', 'fla_type', 'fla_icon', 'is_parent', 'parent_id', 'fla_level');
		/** 必须的字段 */
		$this->_required_fields = array('at_id', 'fla_type', 'fla_icon', 'fla_level');
		/** 主键 */
		$this->_pk = 'fla_id';

		parent::__construct();
	}

	/** 搜索
	 * @param number $uid
	 * @param number $fla_id
	 * @param number $limit
	 * @param number $page
	 * @return array
	*/
	public  function get_by_uid_fla_id($uid, $fla_id = 0, $limit, $page) {
		//id 文件ID 为0处理，即查询 level为 1 的文件。
		$conditions = array();
		if ($fla_id == 0) {
			$conditions['fla_level'] = 1;
		}else {
			$conditions['parent_id'] = $fla_id;
		}
		$conditions['m_uid'] = $uid;

		$this->_add($conditions);
		
		list(
			$start, $perpage, $page
		) = voa_h_func::get_limit($page, $limit);
		return $this->list_all(array($start, $perpage));
	}
	/** 计总 
	 * @param number $uid
	 * @param number $fla_id
	 * @return array
	*/
	public  function count_by_uid_fla_id($uid, $fla_id = 0) {
		//id 文件ID 为0处理，即查询 level为 1 的文件。
		$conditions = array();
		if ($fla_id == 0) {
			$conditions['fla_level'] = 1;
		}else {
			$conditions['parent_id'] = $fla_id;
		}
		$conditions['m_uid'] = $uid;
		$this->_add($conditions);
		return $this->_total();
	}
	/** 更新文件表状态及时间 
	 * @param number $fla_id
	 * @return 
	*/
	public  function del_by_fla_id($fla_id) {
		//需更新 字段 状态 3为删除 
		$data['fla_status'] = '3';
		$data['fla_deleted'] = time();
		return $this->update($fla_id, $data);
	}
	/** 检查 附件 是否被用 
	 * @param number $at_id
	 * @return 
	*/
	public  function check_by_at_id($at_id = 0) {
		//检查at_id
		$conditions['at_id'] = $at_id;
		$this->_add($conditions);
		$_total = $this->_total();
		if ($_total > 1) {
			return false;
		}
		return true;
	}
}


