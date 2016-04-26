<?php
/**
 * voa_d_oa_jobtrain_study
 * Create By wowxavi
 * $Author$
 * $Id$
 */

class voa_d_oa_jobtrain_study extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.jobtrain_study';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'id';
		parent::__construct(null);
	}
	/**
	 * 获取列表
	 * @return array
	 */
	public function list_study_by_conds($conds, $pager, $cata) {
		if($pager) {
			list($start, $limit) = $pager;
			$limit = $limit?"LIMIT $start, $limit":'';
		}else{
			$limit = '';
		}
		if($conds['is_study']){
			$sql = "SELECT * FROM ".$this->_table." WHERE `aid`='".$conds['aid']."' AND `status`<'".self::STATUS_DELETE."' ORDER BY study_time DESC $limit";
			//$total = "SELECT COUNT(*) FROM ".$this->_table." WHERE `aid`='".$conds['aid']."' AND `status`<'".self::STATUS_DELETE."'";
		}else{			
			if($cata['is_all']){
				// 全范围
				$sql = "SELECT a.m_uid, a.m_username, a.cd_id, a.cj_id, a.m_mobilephone as mobile FROM oa_member a LEFT JOIN (SELECT m_uid FROM ".$this->_table." WHERE aid='".$conds['aid']."' AND status<'".self::STATUS_DELETE."') b ON a.m_uid=b.m_uid WHERE a.m_status<'".self::STATUS_DELETE."' AND b.m_uid IS NULL $limit";
				//$total = "SELECT COUNT(*) FROM oa_member a LEFT JOIN (SELECT m_uid FROM ".$this->_table." WHERE aid='".$conds['aid']."' AND status<'".self::STATUS_DELETE."') b ON a.m_uid=b.m_uid WHERE a.m_status<'".self::STATUS_DELETE."' AND b.m_uid IS null";
			}else{
				// 人员范围
				$in_uids = '';
				if($cata['m_uids']){
					$in_uids = "AND a.m_uid IN (".$cata['m_uids'].")";
				}
				if($cata['cd_ids']){
					$in_uids = "AND c.cd_id IN (".$cata['cd_ids'].")";
				}
				// 部门范围
				$in_cids = '';
				if($cata['cd_ids']){
					$in_cids = "LEFT JOIN oa_member_department c ON a.m_uid=c.m_uid";
				}
				if($cata['m_uids']&&$cata['cd_ids']){
					$in_uids = "AND ( a.m_uid IN (".$cata['m_uids'].") OR c.cd_id IN (".$cata['cd_ids'].") )";
				}
				$sql = "SELECT a.m_uid, a.m_username, a.cd_id, a.cj_id, a.m_mobilephone as mobile FROM oa_member a $in_cids LEFT JOIN (SELECT m_uid FROM ".$this->_table." WHERE aid='".$conds['aid']."' AND status<'".self::STATUS_DELETE."') b ON a.m_uid=b.m_uid WHERE a.m_status<'".self::STATUS_DELETE."' AND b.m_uid IS NULL $in_uids GROUP BY a.m_uid $limit";
				//$total = "SELECT COUNT(*) FROM oa_member a $in_cids LEFT JOIN (SELECT m_uid FROM ".$this->_table." WHERE aid='".$conds['aid']."' AND status<'".self::STATUS_DELETE."') b ON a.m_uid=b.m_uid WHERE a.m_status<'".self::STATUS_DELETE."' AND b.m_uid IS NULL $in_uids GROUP BY a.m_uid";
			}
		}

		$list = $this->_getAll($sql);
		//$total = $this->_getOne($total);
		//return array('list' => $list, 'total' => $total);
		return array('list' => $list);
	}
}