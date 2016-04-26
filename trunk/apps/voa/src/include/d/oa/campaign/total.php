<?php
/**
 * 活动业绩
 * Create By linshiling
 * $Author$
 * $Id$
 */

class voa_d_oa_campaign_total extends voa_d_abstruct {


	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.campaign_total';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'id';
		parent::__construct(null);
	}

	/**
	 * 获取统计信息
	 * @return mixed $actid	活动id,可以是整型或数组
	 */
	public function get_total($actid) {

		$array_mod = 0;
		if (is_array($actid)) {
			$actid = implode(',', $actid);
			$array_mod = 1;
		}

		$sql = "SELECT actid,sum(share) share, sum(hits) hits, sum(regs) regs, sum(signs) signs FROM $this->_table
				WHERE actid IN($actid) GROUP BY actid";
		$list = $this->_getAll($sql);
		$temp = array();
		foreach ($list as $l) {
			$temp[$l['actid']] = $l;
			unset($temp[$l['actid']]['actid']);
		}

		$list = $temp;
		return $array_mod ? $list : current($list);
	}

	/**
	 * 获取签到排行
	 * @return int $actid	活动id
	 */
	public function sign_order($actid) {

		$sql = "SELECT saleid,sum(share) share, sum(hits) hits, sum(regs) regs, sum(signs) signs FROM $this->_table
				WHERE actid IN($actid) GROUP BY saleid ORDER BY signs DESC";
		$list = $this->_getAll($sql);
		if (! $list) {
			return array();
		}

		// 获取销售名,为提高效率,采取一次性读销售名的方式
		foreach ($list as $l) {
			$sales[] = $l['saleid'];
		}

		$mem = new voa_d_oa_member();
		$member = $mem->fetch_all_by_ids($sales);
		foreach ($list as &$v) {
			$v['_sale'] = $member[$v['saleid']]['m_username'];
		}

		return $list;
	}

		// 初始化业绩记录
	private function init($actid, $saleid) {

		$da = new voa_d_oa_campaign_campaign();
		$act = $da->get($actid);
		$date = rgmdate(time(), 'Y-m-d');
		$data = array('actid' => $actid, 'saleid' => $saleid, 'date' => $date);
		$rec = $this->get_by_conds($data);
		if (! $rec) {
			$data['typeid'] = $act['typeid'];
			$this->insert($data);
		}
	}

	/**
	 * 浏览量+1
	 *
	 * @param int $actid
	 * @param int $saleid
	 */
	public function hits($actid, $saleid) {

		$date = rgmdate(time(), 'Y-m-d');
		$this->init($actid, $saleid);
		$where = array('actid' => $actid, 'saleid' => $saleid, 'date' => $date);
		$rec = $this->get_by_conds($where);
		$data = array('hits' => $rec['hits'] + 1);
		$this->update($rec['id'], $data);
	}

	// 统计签到数
	public function signs($actid, $saleid) {

		$date = rgmdate(time(), 'Y-m-d');
		$this->init($actid, $saleid);

		$dr = new voa_d_oa_campaign_reg();
		$where = array('actid' => $actid, 'saleid' => $saleid, 'is_sign' => 1, 'signtime > ?' => rstrtotime('today'), 'signtime < ?' => rstrtotime('today') + 86399);
		$signs = $dr->count_by_conds($where);

		$where = array('actid' => $actid, 'saleid' => $saleid, 'date' => $date);
		$rec = $this->get_by_conds($where);
		$data = array('signs' => intval($signs));
		$this->update($rec['id'], $data);
	}

	// 统计报名数
	public function regs($actid, $saleid, $date = '') {

		if (! $date) {
			$date = rgmdate(time(), 'Y-m-d');
		}

		$starttime = rstrtotime($date);
		$this->init($actid, $saleid);

		$dr = new voa_d_oa_campaign_reg();
		$where = array('actid' => $actid, 'saleid' => $saleid, 'created > ?' => $starttime, 'created < ?' => $starttime + 86399);
		$regs = $dr->count_by_conds($where);
		$where = array('actid' => $actid, 'saleid' => $saleid, 'date' => $date);
		$rec = $this->get_by_conds($where);
		$data = array('regs' => intval($regs));
		$this->update($rec['id'], $data);
	}

	// 统计分享数
	public function share($actid, $saleid, $date = '') {

		if (! $date) {
			$date = rgmdate(time(), 'Y-m-d');
		}

		$this->init($actid, $saleid);

		$dr = new voa_d_oa_campaign_share();
		$where = array('actid' => $actid, 'saleid' => $saleid, 'date' => $date);
		$share = $dr->count_by_conds($where);
		$where = array('actid' => $actid, 'saleid' => $saleid, 'date' => $date);
		$rec = $this->get_by_conds($where);
		$this->update($rec['id'], array('share' => intval($share)));
	}
}

