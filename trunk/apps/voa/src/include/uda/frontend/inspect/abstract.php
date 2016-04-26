<?php
/**
 * voa_uda_frontend_inspect_abstract
 * 统一数据访问/巡店应用/基类
 * $Author$
 * $Id$
 */

class voa_uda_frontend_inspect_abstract extends voa_uda_frontend_base {
	// 最大附件数
	protected $_attach_max = 5;
	/** 配置信息 */
	protected $_sets = array();
	/** 打分项配置 */
	protected $_items = array();

	public function __construct() {

		parent::__construct();
		$this->_sets = voa_h_cache::get_instance()->get('plugin.inspect.setting', 'oa');
		/** 取评分项信息 */
		$this->_items = voa_h_cache::get_instance()->get('plugin.inspect.item', 'oa');

		// 初始化 service
		if (null == $this->_serv) {
			$this->_serv = new voa_s_oa_inspect();
		}
	}

	public function set_items($items) {

		$this->_items = $items;
	}

	/**
	 * 检查备注是否正常
	 * @param string $note 备注
	 * @param array &$data 当前数据
	 * @param array $odata 旧数据
	 */
	public function val_note(&$note, &$data, $odata = array()) {

		if (empty($odata) || $odata['ins_note'] != $note) {
			$data['ins_note'] = $note;
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

		if (empty($odata) || $odata['ins_lng'] != $longitude) {
			$data['ins_lng'] = $longitude;
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

		if (empty($odata) || $odata['ins_lat'] != $latitude) {
			$data['ins_lat'] = $latitude;
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
		$items = voa_h_cache::get_instance()->get('plugin.inspect.item', 'oa');

		/** 验证分数 */
		$i = 0;
		foreach ($items as $_it) {
			if (!array_key_exists($i, $scores) || $_it['insi_score'] < $scores[$i]) {
				$this->errmsg(110, 'score_invalid');
				return false;
			}

			$item_scores[$_it['insi_id']] = $scores[$i];
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
			foreach ($this->_items['p2c'][$_pid] as $_cid) {
				$p_count ++;
				$item_count ++;
				/** 判断是否已经处理过 */
				if (!array_key_exists($_cid, $list)) {
					$done = false;
					$c_done = false;
					continue;
				}

				/** 判断是否已经打过分 */
				if (!isset($list[$_cid]) || 0 >= $list[$_cid]['isr_score']) {
					$done = false;
					$c_done = false;
					continue;
				}

				/** 记录打分项id和分值对应关系 */
				$item2score[$_cid] = $list[$_cid]['isr_score'];
				/** 如果是自定义的规则 */
				if (0 < $this->_sets['score_rule_diy']) {
					if ($list[$_cid]['isr_score'] == $this->_sets['score_rules_ignoreid']) {
						$p_count --;
						$item_count --;
					}

					if ($list[$_cid]['isr_score'] == $this->_sets['score_rules_passid']) {
						$p_score ++;
					}
				} else { /** 正常打分 */
					$p_score += $list[$_cid]['isr_score'];
				}
			}

			/** 计算打分大项 */
			$list[$_pid]['_is_over'] = $c_done;
			if (0 < $this->_sets['score_rule_diy']) {
				if (0 == $p_count) {
					$item2score[$_pid] = 0;
				} else {
					$item2score[$_pid] = floor(($p_score * 100) / $p_count);
				}
			} else {
				$item2score[$_pid] = $p_score;
			}

			/** 总分数 */
			$total += $p_score;
		}

		$item_count = empty($item_count) ? 1 : $item_count;
		$total = false == $done ? 0 : $total;
		if (0 < $this->_sets['score_rule_diy']) {
			$total = floor(100 * $total / $item_count);
		}

		return true;
	}
}
