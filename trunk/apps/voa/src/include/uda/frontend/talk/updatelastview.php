<?php
/**
 * voa_uda_frontend_talk_updatelastview
 * 更新最后聊天记录时间
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_talk_updatelastview extends voa_uda_frontend_talk_abstract {

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 执行
	 * @param array $in 输入
	 * @param array $out 输出
	 * @return boolean
	 */
	public function execute($in, &$out = null) {

		// 输入参数
		$this->_params = $in;

		// 需要提取的参数列表
		$fields = array(
			array('uid', self::VAR_INT, '', null, false), // 用户uid
			array('tv_uid', self::VAR_INT, '', null, false), // 客户uid
			array('message', self::VAR_STR, '', null, true), // 消息
			array('goodsid', self::VAR_INT, '', null, true), // 产品id
			array('viewts', self::VAR_INT, '', null, true), // 最后查看时间
			array('lastts', self::VAR_INT, '', null, true) // 最后更新时间戳
		);
		// 提取数据
		$data = array();
		if (!$this->extract_field($data, $fields)) {
			return false;
		}

		// 更新时间
		$serv_lastview = &service::factory('voa_s_oa_talk_lastview');
		$up = $data;
		unset($up['uid'], $up['tv_uid']);

		// 如果最后更新时间存在
		if (isset($in['newct'])) {
			$up['newct'] = $in['newct'];
		} elseif (!empty($data['lastts'])) {
			$up['lastts'] = $data['lastts'];
			$up['`newct`=`newct`+?'] = 1;
		} else {
			$up['newct'] = 0;
		}

		// 先读取记录
		$lastview = array();
		$conds = array(
			'uid' => $data['uid'],
			'tv_uid' => $data['tv_uid']
		);
		if (!$lastview = $serv_lastview->get_by_conds($conds)) {
			return false;
		}

		if ($lastview['viewts'] + 600 < startup_env::get('timestamp')) {
			$up['viewts'] = startup_env::get('timestamp');
		}

		// 更新
		$serv_lastview->update_by_conds(array('cs_id' => $lastview['cs_id']), $up);

		$out = $lastview;

		return true;
	}

}
