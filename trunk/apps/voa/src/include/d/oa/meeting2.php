<?php
/**
 * 会议表
 * $Author$
 * $Id$
 */

class voa_d_oa_meeting2 extends voa_d_abstruct {
	
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新过 */
	const STATUS_UPDATE = 2;
	/** 已取消 */
	const STATUS_CANCEL = 3;
	/** 已删除 */
	const STATUS_REMOVE = 4;
	/** 会议的状态 */
	const MEETING_ALL = 0;
	const MEETING_NEW = 1;
	const MEETING_FIN = 2;

	public function __construct($cfg = null) {

		// 表名
		$this->_table = 'orm_oa.meeting';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'mt_id';

		parent::__construct(null);
	}
	
	//待参加列表
	public function join_list($uid, $start, $limit)
	{
		$where = "mm.m_uid = $uid AND m.mt_status < 3 AND m.mt_endtime > ".time();
		$sql = "SELECT m.mt_id,m.m_uid,m.m_username,m.mt_subject,m.mt_begintime,m.mt_endtime,m.mt_status FROM oa_meeting m
				LEFT JOIN oa_meeting_mem mm ON m.mt_id = mm.mt_id
				WHERE $where ORDER BY mt_begintime ASC LIMIT $start, $limit";
		$list = $this->_getAll($sql);
		
		$total = "SELECT COUNT(*) FROM oa_meeting m
				LEFT JOIN oa_meeting_mem mm ON m.mt_id = mm.mt_id
				WHERE $where";
		$total = $this->_getOne($total);
		return array('list' => $list, 'total' => $total);
	}
	
	//已结束列表
	public function fin_list($uid, $start, $limit)
	{
		$where = "mm.m_uid = $uid AND m.mt_status < 3 AND m.mt_endtime < ".time();
		
		$sql = "SELECT m.mt_id,m.m_uid,m.m_username,m.mt_subject,m.mt_begintime,m.mt_endtime,m.mt_status FROM oa_meeting m
				LEFT JOIN oa_meeting_mem mm ON m.mt_id = mm.mt_id
				WHERE $where ORDER BY mt_begintime DESC LIMIT $start, $limit";
		$list = $this->_getAll($sql);
		
		$total = "SELECT COUNT(*) FROM oa_meeting m
				LEFT JOIN oa_meeting_mem mm ON m.mt_id = mm.mt_id
				WHERE $where";
		$total = $this->_getOne($total);
		return array('list' => $list, 'total' => $total);
	}
}
