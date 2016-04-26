<?php

/**
 * voa_uda_frontend_campaign_get
 * 读取数据
 * */
class voa_uda_frontend_campaign_get extends voa_uda_frontend_campaign_base {

	/**
	 * 获取一条活动记录
	 *
	 * @param int $id
	 *
	 */
	public function get_act($id) {

		return $this->_campaign('campaign')->get($id);
	}

	/**
	 * 获取一条接单记录
	 *
	 * @param int $id
	 *
	 */
	public function get_orders($cid) {

		return $this->_campaign('orders')->get_by_conds(array('cid' => $cid));
	}

	/**
	 * 获取一条活动推广自定义记录
	 *
	 * @param int $id
	 *
	 */
	public function get_customcols($cid) {

		return $this->_campaign('customcols')->get_by_conds(array('cid' => $cid));
	}

	/**
	 * 获取一条活动报名详情
	 *
	 * @param int $id
	 *
	 */
	public function get_reg($conds,$page_option = array(),$orderby = array()) {

		return $this->_campaign('reg')->list_by_conds($conds,$page_option,$orderby);
	}

	/**
	 * 获取活动数量
	 * @param int $cid
	 */
	public function get_count($conds) {

		return $this->_campaign('reg')->count_by_conds($conds);
	}

	/**
	 * 获取指定活动的自定义字段
	 *
	 * @param int $actid
	 * @param int $saleid
	 *
	 */
	public function get_custom($actid, $saleid) {

		$rec = $this->_campaign('custom')->get_by_conds(array('actid' => $actid, 'saleid' => $saleid));
		$custom = $rec['custom'] ? explode(',', $rec['custom']) : array();
		return $custom;
	}

	/**
	 * 读取指定活动的分享数
	 *
	 * @param int $id
	 *
	 */
	public function get_share($id) {

		return $this->_campaign('total')->get_total($id);
	}

	/**
	 * 获取指定活动的签到排行
	 *
	 * @param int $id
	 *
	 */
	public function get_sign($id) {

		return $this->_campaign('total')->sign_order($id);
	}

	/*
	 * 客户列表
	 */
	public function CuserList($param) {

		return $this->_campaign('reg')->getCuser($param);
	}

	/**
	 * 活动列表
	 */
	public function get_activity_list($where, $start, $limit) {

		return $this->_campaign('def')->activity_list($where, $start, $limit);
	}

	/**
	 * 选项列表
	 */
	public function get_option_list($where) {

		return $this->_campaign('def')->option_list($where);
	}

}