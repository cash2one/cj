<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 16/1/30
 * Time: 下午1:13
 */
namespace Stat\Service;

class StatMemberAllService extends AbstractService {

	public function __construct() {

		parent::__construct();
		$this->_d = D('Stat/StatMemberAll');
	}

	/** 数据字段 */
	protected $_field = array(
		'time',
		'add',
		'active_count',
		'attention',
		'unattention',
		'all',
	);
	/** 数据字段对应的名称 */
	protected $_field_name = array(
		'add' => '新增员工数',
		'active_count' => '活跃员工数',
		'attention' => '已关注员工数',
		'unattention' => '未关注员工数',
		'all' => '企业员工总数',
	);
	/** 图标数据类型对应字段 */
	protected $_chart_field = array(
		1 => 'add',
		2 => 'active_count',
		3 => 'attention',
		4 => 'unattention',
		5 => 'all',
	);

	/**
	 * 昨天数据
	 * @return mixed
	 */
	public function get_yesterday_member($params) {

		return $this->_d->get_yesterday_member($params);
	}

	/**
	 * 按日期查询数据
	 * @param $params array 时间参数
	 * @return mixed
	 */
	public function list_by_conds_time($params) {

		return $this->_d->list_by_conds_time($params);
	}


	/*
	 * 读取昨日数据
	 * @param $epid 公司id
	 *
	 */
	public function get_by_conds_lastday($ep_id) {

		// 获取昨日数据
		$cond = array(
			's_time' => rstrtotime(rgmdate(NOW_TIME, 'Y-m-d'))-86401,
			'e_time' => rstrtotime(rgmdate(NOW_TIME, 'Y-m-d')),
		);
		$yestoday = array();
		$before = array();
		$yestoday = $this->_d->get_by_conds_lastday($ep_id, $cond);
		//格式化数据

		// 获取前天数据
		$conds = array(
			's_time' => rstrtotime(rgmdate(NOW_TIME, 'Y-m-d')) - 172801,
			'e_time' => rstrtotime(rgmdate(NOW_TIME, 'Y-m-d')) - 86401,
		);
		$before = $this->_d->get_by_conds_lastday($ep_id, $conds);
		//格式化数据

		return $this->format_day($yestoday, $before);

	}


	/*
	 * 格式昨日数据
	 * @param $yestoday array 昨日数据
	 * @param $before array 前天数据
	 * @result array
	 */
	public function format_day($yestoday, $before) {

		$add_percent = 0;
		$active_percent = 0;
		$attention_percent = 0;
		$unattention_percent =0;
		$all_percent = 0;
		if (empty($yestoday)) {
			return array(
				'header' => array(),
			);
		}
		else if (!empty($yestoday)) {
			//新增员工数百分比
			$add_percent = (($yestoday['add'] - $before['add']) / $before['add'])*100;
			//活跃员工数百分比
			$active_percent = (($yestoday['active_count'] - $before['active_count']) / $before['active_count'])*100;
			//以关注员工数百分比
			$attention_percent = (($yestoday['attention'] - $before['attention']) / $before['attention'])*100;
			//未关注员工数百分比
			$unattention_percent = (($yestoday['unattention'] - $before['unattention']) / $before['unattention'])*100;
			//企业员工总数百分比
			$all_percent = (($yestoday['all'] - $before['all']) / $before['all'])*100;

			//保留两位小数
			$add_percent = round($add_percent,2);
			$active_percent = round($active_percent,2);
			$attention_percent = round($attention_percent,2);
			$unattention_percent = round($unattention_percent,2);
			$all_percent = round($all_percent,2);
		}

		$result = array(
			'header' => array(
				array(
					'name' => '新增员工数',
					'count' => isset($yestoday['add'])?$yestoday['add']:0,
					'percent' => $add_percent,
				),
				array(
					'name' => '活跃员工数',
					'count' => isset($yestoday['active_count'])?$yestoday['active_count']:0,
					'percent' => $active_percent,
				),
				array(
					'name' => '已关注员工数',
					'count' => isset($yestoday['attention'])?$yestoday['attention']:0,
					'percent' => $attention_percent,
				),
				array(
					'name' => '未关注员工数',
					'count' => isset($yestoday['unattention'])?$yestoday['unattention']:0,
					'percent' => $unattention_percent,
				),
				array(
					'name' => '企业员工总数',
					'count' => isset($yestoday['all'])?$yestoday['all']:0,
					'percent' => $all_percent,
				),
			),
		);

		return $result;
	}


	/*
	 * 单个企业 详情数据
	 * @param $star_time 开始时间
	 * @param $end_time 结束时间
	 * @param $ep_id 公司id
	 * @param $page_option 分页参数
	 * @return array
	 */
	public function list_by_conds_det($star_time, $end_time, $ep_id, $page_option) {

		$list = $this->_d->list_by_conds_detail($star_time, $end_time, $ep_id, $page_option);
		if (empty($list)) {
			return false;
		}

		$total = $this->_d->count_by_time_epid($star_time, $end_time, $ep_id);

		$result = array();
		foreach ($list as $_record) {
			$temp['date'] = rgmdate($_record['time'], 'Y-m-d');
			foreach ($_record as $_key => $_val) {
				if (in_array($_key, $this->_field)) {
					$temp[$_key] = $_val;
				}
			}
			$result[] = $temp;
			unset($temp);
		}

		return array(
			$result,
			$total,
		);
	}


	/**
	 * 获取用户数据 图表数据
	 * @param $start
	 * @param $end
	 * @param $type
	 * @return array
	 */
	public function user_charts_data($start, $end, $type, $ep_id) {

		// 根据时间获取数据
		$data = $this->_d->list_by_time($start, $end, $ep_id);

		if (empty($data)) {
			return true;
		}

		// 填充数据
		$name = empty($this->_field_name[$this->_chart_field[$type]]) ? '' : $this->_field_name[$this->_chart_field[$type]];
		$chart_days = array();
		$chart_count = array();
		foreach ($data as $_record) {
			$chart_days[] = rgmdate($_record['time'], 'Y-m-d');
			$chart_count[] = $_record[$this->_field[$type]];
		}

		return array(
			$name,
			$chart_days,
			$chart_count,
		);
	}

	/**
	 * 根据时间和公司id统计
	 * @param int $start
	 * @param int $end
	 * @return array|bool
	 */
	public function count_by_time_epid($start = 0, $end = 0, $ep_id) {

		return $this->_d->count_by_time_epid($start, $end, $ep_id);
	}

}