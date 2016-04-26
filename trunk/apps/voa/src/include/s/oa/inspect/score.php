<?php
/**
 * 巡店打分信息表
 * $Author$
 * $Id$
 */

class voa_s_oa_inspect_score extends voa_s_abstract {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 格式化巡店打分数据
	 * @param array &$score 巡店打分
	 * @return boolean
	 */
	public function format(&$score) {

		// 发起时间
		$score['_created'] = rgmdate($score['isr_created'], 'Y-m-d H:i');
		list($score['_created_ymd'], $score['_created_hi']) = explode(' ', $score['_created']);
		// 个性化发起时间
		$score['_updated_u'] = rgmdate($score['isr_updated'], 'u');
		// 备注
		$score['_message'] = bbcode::instance()->bbcode2html($score['isr_message']);
		return true;
	}

}
