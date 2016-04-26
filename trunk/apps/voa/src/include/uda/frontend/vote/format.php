<?php
/**
 * voa_uda_frontend_vote_format
 * 统一数据访问/微评选应用/数据格式化
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_vote_format extends voa_uda_frontend_vote_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 格式化投票数据数组
	 * @param array $list 投票信息数组
	 * @return boolean
	 */
	public function vote_list(&$list) {
		foreach ($list as &$vote) {
			$this->vote($vote);
		}

		return true;
	}

	/**
	 * 格式化投票数据
	 * @param array $vote 投票信息
	 */
	public function vote(&$vote) {
		$vote['v_subject'] = rhtmlspecialchars($vote['v_subject']);
		$vote['v_message'] = rhtmlspecialchars($vote['v_message']);
		$vote['_begintime'] = rgmdate($vote['v_begintime'], 'Y-m-d H:i');
		$vote['_begintime_u'] = rgmdate($vote['v_begintime']);
		$vote['_endtime'] = rgmdate($vote['v_endtime'], 'Y-m-d H:i');
		$vote['_endtime_u'] = rgmdate($vote['v_endtime']);

		return true;
	}
}
