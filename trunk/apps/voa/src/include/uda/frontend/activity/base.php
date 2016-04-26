<?php

/**
 * voa_uda_frontend_activity_base
 * 统一数据访问/活动报名/基本控制
 * Create By tim_zhang
 * $Author$
 * $Id$
 */
class voa_uda_frontend_activity_base extends voa_uda_frontend_base {

	public function __construct() {
		parent::__construct();
		$this->_sets = voa_h_cache::get_instance()->get('plugin.activity.setting', 'oa');
	}

	/**
	 *活动状态判断
	 * @param int start
	 * @param int end
	 *return string type
	 */
	protected function _check_type($start, $end) {

		$time = startup_env::get('timestamp');//当前时间
		$type = array();
		if ($start <= $time && $time <= $end) {
			$type[0] = '已开始'; //已开始
			$type[1] = 1; //已开始
		} elseif ($start > $time) {
			$type[0] = '未开始'; //未开始
			$type[1] = 2; //未开始
		} elseif ($end < $time) {
			$type[0] = '已结束'; //已结束
			$type[1] = 3; //已结束
		}
		return $type;
	}

	/**
	 * 处理外部人员字段,增加name的MD5值，和序列化数组
	 * @param $in
	 * @param $out
	 */
	public function outfiled($in, &$out) {
		foreach ($in as $k => &$v) {
			$v['md5name'] = md5(trim($v['name']));
		}
		$out = serialize($in);
		return true;
	}

}
