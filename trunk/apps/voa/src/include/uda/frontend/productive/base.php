<?php
/**
 * voa_uda_frontend_productive_base
 * 统一数据访问/活动/产品应用/基类
 * $Author$
 * $Id$
 */
class voa_uda_frontend_productive_base extends voa_uda_frontend_base {

	/** 配置信息 */
	protected $_sets = array();
	/** 打分项配置 */
	protected $_items = array();

	public function __construct() {

		parent::__construct();
		$this->_sets = voa_h_cache::get_instance()->get('plugin.productive.setting', 'oa');
		/** 取评分项信息 */
		$this->_items = voa_h_cache::get_instance()->get('plugin.productive.item', 'oa');
	}

	/**
	 * 检查店铺id是否正常
	 * @param int &$id 店铺id
	 * @param array &$data 当前数据
	 * @param array $odata 旧数据
	 */
	public function val_csp_id(&$id, &$data, $odata = array()) {

		$id = (int)$id;
		$shops = voa_h_cache::get_instance()->get('shop', 'oa');
		if (!array_key_exists($id, $shops)) {
			$this->errmsg(110, 'shop_is_not_exist');
			return false;
		}

		if (empty($odata) || $odata['csp_id'] != $id) {
			$data['csp_id'] = $id;
		}

		return true;
	}

	/**
	 * 检查备注是否正常
	 * @param string $note 备注
	 * @param array &$data 当前数据
	 * @param array $odata 旧数据
	 */
	public function val_note(&$note, &$data, $odata = array()) {

		if (empty($odata) || $odata['pt_note'] != $note) {
			$data['pt_note'] = $note;
		}

		return true;
	}

	/**
	 * 取经度值
	 * @param float $longitude
	 * @param array &$data 当前数据
	 * @param array $odata 旧数据
	 */
	public function val_longitude(&$longitude, &$data, $odata = array()) {

		$longitude = (float)$longitude;

		if (empty($odata) || $odata['pt_lng'] != $longitude) {
			$data['pt_lng'] = $longitude;
		}

		return true;
	}

	/**
	 * 取纬度值
	 * @param float $latitude
	 * @param array &$data 当前数据
	 * @param array $odata 旧数据
	 */
	public function val_latitude(&$latitude, &$data, $odata = array()) {

		$latitude = (float)$latitude;

		if (empty($odata) || $odata['pt_lat'] != $latitude) {
			$data['pt_lat'] = $latitude;
		}

		return true;
	}

	/**
	 * 检查评分是否正常
	 * @param string $scores 评分字串
	 * @param array &$item_scores 当前数据
	 */
	public function chk_item_scores($scorestr, &$item_scores) {

		$scorestr = (string)$scorestr;
		$scores = explode(',', $scorestr);
		$items = voa_h_cache::get_instance()->get('plugin.productive.item', 'oa');

		/** 验证分数 */
		$i = 0;
		foreach ($items as $_it) {
			if (!array_key_exists($i, $scores) || $_it['pti_score'] < $scores[$i]) {
				$this->errmsg(110, 'score_invalid');
				return false;
			}

			$item_scores[$_it['pti_id']] = $scores[$i];
			$i ++;
		}

		return true;
	}

	/**
	 * 验证附件id
	 * @param unknown $at_idstr
	 * @param unknown $at_ids
	 * @return boolean
	 */
	public function chk_at_ids($at_idstr, &$at_ids) {
		$str = (string)$at_idstr;
		$str = trim($str);
		$tmps = empty($str) ? array() : explode(',', $str);
		$at_ids = (array)$at_ids;
		foreach ($tmps as $id) {
			$id = (int)$id;
			if (0 < $id) {
				$at_ids[$id] = $id;
			}
		}

		return true;
	}

	/**
	 * 验证抄送人
	 * @param string $uidstr
	 * @param array $uids
	 * @return boolean
	 */
	public function chk_uids($uidstr, &$uids) {
		$uidstr = (string)$uidstr;
		$uidstr = trim($uidstr);
		$tmps = empty($uidstr) ? array() : explode(',', $uidstr);
		$uids = array();
		foreach ($tmps as $uid) {
			$uid = (int)$uid;
			if (0 < $uid) {
				$uids[$uid] = $uid;
			}
		}

		return true;
	}

	/**
	 * 技术总分
	 * @param int $total 总分数
	 * @param array $item2score 打分项目对应的分数
	 * @param array $list 打分列表
	 * @return boolean
	 */
	public function calc_score(&$total, &$item2score, &$list) {

		/** 是否已完成 */
		$done = true;
		/** 打分项总数 */
		$item_count = 0;

		/** 遍历开始 */
		foreach ($this->_items['p2c'][0] as $_pid) {
			/** 大项的打分项分数 */
			$p_score = 0;
			/** 子项个数(不包括忽略不计的) */
			$p_count = 0;
			/** 子项是否都完成 */
			$c_done = true;
			/** 遍历子项 */
			$p2c = isset($this->_items['p2c'][$_pid]) ? $this->_items['p2c'][$_pid] : array();
			foreach ($p2c as $_cid) {
				$p_count ++;
				$item_count ++;
				/** 判断是否已经处理过 */
				if (!array_key_exists($_cid, $list)) {
					$done = false;
					$c_done = false;
					continue;
				}

				/** 判断是否已经打过分 */
				if (!isset($list[$_cid]) || 0 >= $list[$_cid]['ptsr_score']) {
					$done = false;
					$c_done = false;
					continue;
				}

				/** 记录打分项id和分值对应关系 */
				$item2score[$_cid] = $list[$_cid]['ptsr_score'];
				/** 如果是自定义的规则 */
				if (0 < $this->_sets['score_rule_diy']) {
					if ($list[$_cid]['ptsr_score'] == $this->_sets['score_rules_ignoreid']) {
						$p_count --;
						$item_count --;
					}

					if ($list[$_cid]['ptsr_score'] == $this->_sets['score_rules_passid']) {
						$p_score ++;
					}
				} else { /** 正常打分 */
					$p_score += $list[$_cid]['ptsr_score'];
				}
			}

			/** 计算打分大项 */
			$list[$_pid]['_is_over'] = $c_done;
			if (0 < $this->_sets['score_rule_diy']) {
				if (0 == $p_count) {
					$item2score[$_pid] = $c_done ? 100 : 0;
				} else {
					$item2score[$_pid] = floor(($p_score * 100) / $p_count);
				}
			} else {
				$item2score[$_pid] = $p_score;
			}

			/** 总分数 */
			$total += $p_score;
		}

		$item_count = 0 < $item_count ? $item_count : 1;
		$total = floor(100 * $total / $item_count);

		return true;
	}
}
