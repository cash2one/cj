<?php

/**
 * 企业后台/微办公管理/活动/统计数据
 * voa_uda_frontend_campaign_total
 * Create By XiaoDingchen
 * $Author$
 * $Id$
 */
class voa_uda_frontend_campaign_total extends voa_uda_frontend_campaign_base {

	const TYPE_TOTAL = 1;

	const TYPE_RANK = 2;

	const STATUS_DEL = 4;
 // 已删除
	const STATUS_CAN = 3;
 // 已取消
	const SEARCH_DEF = - 1;

	const SEARCH_DEF_ACT = - 2;

	const LIMIT = 10;

	const DOWN_LIMIT = 1000;

	const IS_EFFECT = 1;

	protected $_table = "{campaign_total}";

	protected $_where;

	public function __construct() {

		parent::__construct();
		$this->_where = " WHERE `status`<" . self::STATUS_CAN;
	}

	/**
	 * 获取活动类型
	 *
	 * @return array
	 *
	 */
	public function list_type() {

		$sql = "select id,title from {campaign_type}" . $this->_where;
		$types = $this->_campaign('def')->getAll($sql);
		$type_list = array();
		foreach ($types as $v) {
			$type_list[$v['id']] = $v['title'];
		}
		return $type_list;
	}

	/**
	 * 获取活动列表
	 *
	 * @return array
	 *
	 */
	public function list_act() {

		$sql = "select id,subject from {campaign}" . $this->_where;
		$acts = $this->_campaign('def')->getAll($sql);
		$act_list = array();
		foreach ($acts as $v) {
			$act_list[$v['id']] = $v['subject'];
		}
		return $act_list;
	}

	/**
	 * 获取统计列表
	 *
	 * @param array $request
	 * @param array $data
	 * @param array|bool &$result
	 * @param bool $down=false 是否下载
	 * @return bool
	 *
	 */
	public function list_count($request, $type, &$result, $down = false) {

		$typeid = $request['typeid'];
		$actid = $request['actid'];
		$page = $request['page'];

		if ($type == self::TYPE_RANK) {
			// 获取排行榜数据
			$result = $this->_list_rank_type($typeid, $actid, $page, $down);
		} else {
			// 获取数据统计数据
			$result = $this->_list_total_type($typeid, $actid, $page, $down);
		}

		return true;
	}

	/**
	 * 获取排行榜的列表，分两种情况
	 * 指定类型下的自媒体排行榜
	 * 指定活动下的自媒体排行榜
	 *
	 * @param int $typeid
	 * @param int $actid
	 * @param int $page
	 * @param boolean $down
	 *
	 */
	protected function _list_rank_type($typeid, $actid, $page, $down) {

		$serv_m = &service::factory('voa_s_oa_member');
		if ($actid > 0) {
			$data = $this->_list_rank_act($actid, $page, $serv_m, $down,$typeid);
		} else {
			$data = $this->_list_rank_one_type($typeid, $page, $serv_m, $down);
		}
		return $data;
	}

	/**
	 * 获取指定类型的自媒体排行榜
	 *
	 * @param int $typeid
	 * @param int $page
	 * @param string $serv_m
	 * @param bool $down
	 * @return array
	 *
	 */
	protected function _list_rank_one_type($typeid, $page, $serv_m, $down) {

		$where = $this->_where . " AND typeid=" . $typeid;
		$sql = sprintf("SELECT COUNT(DISTINCT saleid) FROM %s %s", $this->_table, $where);
		// 获取数目
		$total = 0;
		$total = $this->__count_total($sql);
		if ($total == 0) {
			return false;
		}
		// 获取指定类型下的活动数据
		$limit = self::LIMIT;
		if ($down) {
			$limit = self::DOWN_LIMIT;
		}
		list($start, $limit, $page) = voa_h_func::get_limit($page, $limit);
		$sql = sprintf("SELECT saleid,salename,cur_score,influence,sum(share) share, sum(hits) hits, sum(regs) regs,sum(signs) signs FROM %s %s GROUP BY saleid limit %d,%d", $this->_table, $where, $start, $limit);
		$list = $this->__list_data($sql);
		$list = $this->_formart_rank_type($list, $serv_m, $typeid, $start);
		if ($down) {
			return array($total, $list);
		}
		$multi = $this->__get_multi($total, $page);
		return array($total, $multi, $list);
	}

	/**
	 * 格式化数据
	 * 主要是得到自媒体的用户名和影响力
	 *
	 * @param array $list
	 * @return array
	 *
	 */
	protected function _formart_rank_type($list, $serv_m, $typeid, $start) {

		$where = $this->_where . " AND typeid=" . $typeid;
		// 活动该分类下的总报名人数
		$sql = sprintf("SELECT sum(regs) FROM %s %s", $this->_table, $where);
		$regs = $this->__count_total($sql);
		// 获取自媒体的个人信息列表
		$m_uids = array_column($list, 'saleid');
		$saleids = join(',', $m_uids);
		$users = $serv_m->fetch_all_by_ids($m_uids);
		// 统计自媒体参加指定类型的次数
		$sql = sprintf("SELECT DISTINCT saleid,actid FROM %s %s and saleid in(%s)", $this->_table, $where, $saleids);
		$type_count = $this->__list_data($sql);
		$saleid_count = array_column($type_count, 'saleid');
		$saleid_count = array_count_values($saleid_count);
		$f_list = array();
		foreach ($list as $val) {
			// 计算影响力
			if ($typeid == 0) {
				$val['effect'] = $val['cur_score'];
			} else {
				$val['effect'] = $val['influence'];
			}
			// 获取用户名
			if (isset($users[$val['saleid']]['m_username'])) {
				$val['_salename'] = $users[$val['saleid']]['m_username'];
			} else {
				$val['_salename'] = $val['salename'];
			}
			unset($val['salename']);
			$f_list[] = $val;
		}
		// 对得到的结果进行排序
		$start = $start + 1;
		$tf_list = $this->__sort_top($f_list, $start);
		return $tf_list;
	}

	/**
	 * 获取指定活动的自媒体排行榜
	 *
	 * @param int $actid
	 * @param int $page
	 * @param string $serv_m
	 * @param bool $down
	 * @return array
	 *
	 */
	protected function _list_rank_act($actid, $page, $serv_m, $down,$typeid = 0) {

		if (!empty($typeid)) {
			$where = $this->_where . " AND actid=" . $actid ." AND typeid=".$typeid;
		}else {
			$where = $this->_where . " AND actid=" . $actid;
		}

		$sql = sprintf("SELECT COUNT(DISTINCT saleid) FROM %s %s", $this->_table, $where);

		$total = 0;
		$total = $this->__count_total($sql);
		if ($total == 0) {
			return false;
		}
		// 获取指定活动下的数据概览
		$limit = self::LIMIT;
		if ($down) {
			$limit = self::DOWN_LIMIT;
		}
		list($start, $limit, $page) = voa_h_func::get_limit($page, $limit);
		$sql = sprintf("SELECT saleid,salename, share, hits, regs, influence, signs FROM %s %s GROUP BY saleid limit %d,%d", $this->_table, $where, $start, $limit);
		$list = $this->__list_data($sql);
		$list = $this->_formart_rank_act($list, $serv_m, $start);
		if ($down) {
			return array($total, $list);
		}
		$multi = $this->__get_multi($total, $page);
		return array($total, $multi, $list);
	}

	/**
	 * 格式化数据
	 * 主要是得到自媒体的用户名和影响力
	 *
	 * @param array $list
	 * @return array
	 *
	 */
	protected function _formart_rank_act($list, $serv_m, $start) {

		$m_uids = array_column($list, 'saleid');
		// 获取当前活动的总报名数
// 		$regs = array_column($list, 'regs');
// 		$regs = array_sum($regs);
		$users = $serv_m->fetch_all_by_ids($m_uids);
		// 获取自媒体用户名和影响力
		$f_list = array();
		foreach ($list as $val) {
			// 计算影响力
			$val['effect'] = $val['influence'];
			// 获取用户名
			if (isset($users[$val['saleid']]['m_username'])) {
				$val['_salename'] = $users[$val['saleid']]['m_username'];
			} else {
				$val['_salename'] = $val['salename'];
			}
			//判断用户名
			if(empty($val['_salename'])){
				$val['_salename'] = '未知用户';
			}
			unset($val['salename']);
			$f_list[] = $val;
		}
		// 对得到的结果进行排序
		$start = $start + 1;
		$tf_list = $this->__sort_top($f_list, $start);
		return $tf_list;
	}

	/**
	 * 对所给的数据进行排序
	 *
	 * @param array $f_list
	 * @param string 健名
	 * @return array
	 *
	 */
	private function __sort_top($f_list, $start, $key = 'effect') {
		// 对得到的结果进行排序
		$effects = array_column($f_list, $key);
		array_multisort($effects, SORT_DESC, $f_list);
		// 获取排名
		$tf_list = array();
		foreach ($f_list as $v) {
			// 获取排名
			$v['_top'] = $start ++;
			$tf_list[] = $v;
		}
		return $tf_list;
	}

	/**
	 * 获取数据统计类型的数据列表，分五种情况
	 *
	 * @param int $typeid
	 * @param int $actid
	 * @param int $page
	 * @param boolean $down
	 * @return array
	 *
	 */
	protected function _list_total_type($typeid, $actid, $page, $down) {

		if ($typeid == self::SEARCH_DEF && $actid == self::SEARCH_DEF_ACT) { // 默认显示的数据
			$data = $this->_list_total_all($page, $down);
		} elseif ($typeid != self::SEARCH_DEF && $actid == self::SEARCH_DEF_ACT) { // 指定类型下的数据概览
			$data = $this->_list_total_one($typeid, $down);
		} elseif ($typeid != self::SEARCH_DEF && $actid == self::SEARCH_DEF) { // 指定类型下的活动列表数据详情
			$data = $this->_list_total_one_act($typeid, $page, $down);
		} elseif ($typeid == self::SEARCH_DEF && $actid == self::SEARCH_DEF) { // 获取所有活动的数据统计
			$data = $this->_list_total_all_act($page, $down);
		} elseif ($actid > 0) { // 获取单一活动下的数据概览
			$data = $this->_list_total_act_one($actid, $down);
		} else { // 如果以上都不符合条件那么就显示默认数据
			$data = $this->_list_total_all($page, $down);
		}

		return $data;
	}

	/**
	 * 获取所有类别的排行，默认
	 *
	 * @param int $page
	 * @return array
	 *
	 */
	protected function _list_total_all($page, $down) {
		// $where = " WHERE `status`<". self::STATUS_DEL;
		// 获取总数目
		$total = 0;
		$where = $this->_where." AND typeid !=0";
		$sql = sprintf("SELECT COUNT(DISTINCT typeid) FROM %s %s", $this->_table, $where);

		$total = $this->__count_total($sql);
		if ($total == 0) {
			return false;
		}
		// 获取列表
		$limit = self::LIMIT;
		if ($down) {
			$limit = self::DOWN_LIMIT;
		}
		list($start, $limit, $page) = voa_h_func::get_limit($page, $limit);
		$sql = sprintf("SELECT typeid,sum(share) share, sum(hits) hits, sum(regs) regs,sum(signs) signs FROM %s %s GROUP BY typeid order by hits desc LIMIT %d,%d", $this->_table, $where, $start, $limit);
		$list = $this->__list_data($sql);
		$list = $this->__formart_total($list, $start);
		if ($down) {
			return array($total, $list);
		}
		$multi = $this->__get_multi($total, $page);
		return array($total, $multi, $list);
	}

	/**
	 * 获取指定类别下的数据概览
	 *
	 * @param int $typeid
	 * @return array
	 *
	 */
	protected function _list_total_one($typeid, $down) {

		$where = $this->_where . " AND typeid=" . $typeid;
		// 获取总数目
		$total = 0;
		$sql = sprintf("SELECT COUNT(DISTINCT typeid) FROM %s %s", $this->_table, $where);
		$total = $this->__count_total($sql);
		if ($total == 0) {
			return false;
		}
		$sql = sprintf("SELECT typeid,sum(share) share, sum(hits) hits, sum(regs) regs,sum(signs) signs FROM %s %s", $this->_table, $where);
		$list = $this->__list_data($sql);
		$list = $this->__formart_total($list);
		if ($down) {
			return array($total, $list);
		}
		$multi = $this->__get_multi($total);
		return array($total, $multi, $list);
	}

	/**
	 * 获取指定类型下的活动列表数据
	 *
	 * @param int $typeid
	 * @return array
	 *
	 */
	protected function _list_total_one_act($typeid, $page, $down) {

		$where = $this->_where . " AND typeid=" . $typeid;
		// 获取总数目
		$total = 0;
		$sql = sprintf("SELECT COUNT(DISTINCT actid) FROM %s %s", $this->_table, $where);
		$total = $this->__count_total($sql);

		if ($total == 0) {
			return false;
		}
		// 获取列表
		$limit = self::LIMIT;
		if ($down) {
			$limit = self::DOWN_LIMIT;
		}
		list($start, $limit, $page) = voa_h_func::get_limit($page, $limit);
		$sql = sprintf("SELECT actid,sum(share) share, sum(hits) hits, sum(regs) regs,sum(signs) signs FROM %s %s GROUP BY actid order by hits desc limit %d, %d", $this->_table, $where, $start, $limit);
		$list = $this->__list_data($sql);
		$list = $this->__formart_act($list, $start);
		if ($down) {
			return array($total, $list);
		}
		$multi = $this->__get_multi($total, $page);
		return array($total, $multi, $list);
	}

	/**
	 * 获取所有活动的数据统计
	 *
	 * @param int $page
	 * @return array
	 *
	 */
	protected function _list_total_all_act($page, $down) {
		// 获取总数目
		$total = 0;
		$sql = sprintf("SELECT COUNT(DISTINCT actid) FROM %s %s", $this->_table, $this->_where);
		$total = $this->__count_total($sql);
		if ($total == 0) {
			return false;
		}
		// 获取列表
		$limit = self::LIMIT;
		if ($down) {
			$limit = self::DOWN_LIMIT;
		}
		list($start, $limit, $page) = voa_h_func::get_limit($page, $limit);
		$sql = sprintf("SELECT actid,sum(share) share, sum(hits) hits, sum(regs) regs,sum(signs) signs FROM %s %s GROUP BY actid order by hits desc limit %d, %d", $this->_table, $this->_where, $start, $limit);
		$list = $this->__list_data($sql);
		$list = $this->__formart_act($list, $start);
		if ($down) {
			return array($total, $list);
		}
		$multi = $this->__get_multi($total, $page);
		return array($total, $multi, $list);
	}

	/**
	 * 获取单一活动下的数据概览
	 *
	 * @param int $actid
	 * @return array
	 *
	 */
	protected function _list_total_act_one($actid, $down) {
		// 组合查询条件
		$where = $this->_where . " AND actid=" . $actid;
		// 准备sql语句
		$sql = sprintf("SELECT COUNT(DISTINCT actid) FROM %s %s", $this->_table, $where);
		// 获取总数目
		$total = 0;
		$total = $this->__count_total($sql);
		if ($total == 0) {
			return false;
		}
		// 获取列表
		$sql = sprintf("SELECT actid,sum(share) share, sum(hits) hits, sum(regs) regs,sum(signs) signs FROM %s %s", $this->_table, $where);
		$list = $this->__list_data($sql);
		$list = $this->__formart_act($list);
		if ($down) {
			return array($total, $list);
		}
		$multi = $this->__get_multi($total);
		return array($total, $multi, $list);
	}

	/**
	 * 获取满足某个影响力下的自媒体个数
	 *
	 * @param int $effect
	 * @return bool
	 */
	public function get_effect_total($effect = 0) {

		if (empty($effect)) {
			return 0;
		}
		$total = 0;


		// 判断高于次影响力下的个数
		$this->_where .= " AND cur_score>{$effect} AND actid=0";
		$sqls = sprintf("SELECT COUNT(id) FROM %s %s", $this->_table, $this->_where);
		$total = $this->__count_total($sqls);
		if(!$total){

			$total = 0;
		}

		return $total;
	}

	/**
	 * 统计数目
	 *
	 * @param string $sql
	 * @return int
	 *
	 */
	private function __count_total($sql) {

		$total = 0;
		$total = $this->_campaign('def')->getOne($sql);
		return $total;
	}

	/**
	 * 获取列表
	 *
	 * @param string $sql
	 * @return array
	 *
	 */
	private function __list_data($sql) {

		$list = array();
		$list = $this->_campaign('def')->getAll($sql);
		return $list;
	}

	/**
	 * 格式化数据，主要是得到类别名称
	 *
	 * @return array
	 *
	 */
	private function __formart_total($list, $start = 0) {

		$start = $start + 1;
		$f_list = array();
		$types = $this->list_type();
		foreach ($list as $v) {
			if (array_key_exists($v['typeid'], $types)) {
				$v['type_name'] = $types[$v['typeid']];
			} else {
				$v['type_name'] = '未知类型';
			}
			$v['_top'] = $start ++;
			$f_list[] = $v;
		}
		return $f_list;
	}

	/**
	 * 格式化数据，主要是得到活动名称
	 *
	 * @return array
	 *
	 */
	private function __formart_act($list, $start = 0) {

		$start = $start + 1;
		$f_list = array();
		$acts = $this->list_act();
		foreach ($list as $v) {
			if (array_key_exists($v['actid'], $acts)) {
				$v['act_name'] = $acts[$v['actid']];
			} else {
				$v['act_name'] = '活动已删除';
			}
			$v['_top'] = $start ++;
			$f_list[] = $v;
		}

		return $f_list;
	}

	/**
	 * 获取分页信息
	 *
	 * @param int $total
	 * @param int $limit
	 * @param int $page
	 * @return string
	 *
	 */
	private function __get_multi($total, $page = 0) {

		$limit = self::LIMIT;
		// 分页链接信息
		$multi = '';
		if ($total > 0) {
			// 输出分页信息
			$multi = pager::make_links(array('total_items' => $total, 'per_page' => $limit, 'current_page' => $page, 'show_total_items' => true));

			return $multi;
		}
		return false;
	}

}