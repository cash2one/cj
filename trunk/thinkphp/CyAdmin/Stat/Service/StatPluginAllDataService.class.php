<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/2/25
 * Time: 下午1:22
 */

namespace Stat\Service;

class StatPluginAllDataService extends AbstractService {

	public function __construct() {

		parent::__construct();
		$this->_d = D('Stat/StatPluginAllData');
	}

	/** 图标数据类型对应字段 */
	protected $_chart_field = array(
		1 => 'count_index',
		2 => 'count_all',
		3 => 'active_plugin',
		4 => 'active_ep',
		5 => 'active_staff',
		6 => 'new_install',
	);
	/** 数据字段 */
	protected $_field = array(
		'count_index',
		'count_all',
		'active_plugin',
		'active_ep',
		'active_staff',
		'new_install',
	);
	/** 数据字段 */
	protected $_fields = array(
		'count_index',
		'count_all',
		'active_plugin',
		'active_ep',
		'active_staff',
		'new_install',
		'time',
	);

	/** 数据字段对应的名称 */
	protected $_field_name = array(
		'count_index' => '应用主数据',
		'count_all' => '应用总数据',
		'active_plugin' => '活跃应用数',
		'active_ep' => '活跃企业数',
		'active_staff' => '活跃员工数',
		'new_install' => '新增应用安装数',
	);

	/**
	 * 按日期查询数据
	 * @param $params array 时间参数
	 * @return mixed
	 */
	public function list_by_conds_time($params, $page_option) {

		return $this->_d->list_by_conds_time($params, $page_option);
	}

	/**
	 * 根据时间查询汇总数据
	 * @param int   $start 开始时间戳
	 * @param int   $end 结束时间戳
	 * @param array $page_option 分页参数
	 * @return mixed
	 */
	public function list_by_time($start = 0, $end = 0, $page_option) {

		$list = $this->_d->list_by_time($start, $end, $page_option, 'ASC');
		
		$result = array();
		foreach($list as $_val) {
			$temp['date'] = rgmdate($_val['time'], 'Y-m-d');
			foreach($_val as $k => $v) {
				if(in_array($k, $this->_fields)) {
					$temp[$k] = $v;
				}
			}
			$result[] = $temp;
			unset($temp);
		}
		return $result;
	}

	/**
	 * 根据时间统计汇总数据
	 * @param int $start 开始时间戳
	 * @param int $end 结束时间戳
	 * @return mixed
	 */
	public function count_by_time($start = 0, $end = 0) {

		return $this->_d->count_by_time($start, $end);
	}

	/**
	 * 获取应用纬度的头部数据
	 * @return array
	 */
	public function get_plugin_header() {

		// 根据时间获取数据
		$data = $this->list_by_time($this->_yesterday_time - 86400, 0);

		if (empty($data)) {
			return true;
		}

		// 前天和昨天的数据
		$yesterday = array();
		$qiantian = array();
		foreach ($this->_field as $_val) {
			$yesterday[$_val] = 0;
			$qiantian[$_val] = 0;
		}

		// 获取数据
		foreach ($data as $_record) {
			// 昨天
			if ($_record['time'] > $this->_yesterday_time && $_record['time'] < $this->_today_time) {
				foreach ($this->_field as $_field) {
					$yesterday[$_field] = $_record[$_field];
				}
				// 前天
			} elseif ($_record['time'] < $this->_yesterday_time) {
				foreach ($this->_field as $_field) {
					$qiantian[$_field] = $_record[$_field];
				}
			}
		}

		// 整理数据
		$result = array();
		foreach ($this->_field as $_field) {
			$temp['name'] = $this->_field_name[$_field];
			$temp['count'] = (int)$yesterday[$_field];
			$temp['percent'] = round(($temp['count'] - $qiantian[$_field]) / (empty($qiantian[$_field]) ? $temp['count'] : $qiantian[$_field]), 2) * 100;
			$result[] = $temp;
			unset($temp);
		}

		return $result;
	}

	/**
	 * 获取应用纬度 图表数据
	 * @param $start
	 * @param $end
	 * @param $type
	 * @return array
	 */
	public function chart_data($start, $end, $type) {

		// 根据时间获取数据
		$data = $this->list_by_time($start, $end);
		if (empty($data)) {
			return true;
		}

		// 填充数据
		$name = empty($this->_field_name[$this->_chart_field[$type]]) ? '' : $this->_field_name[$this->_chart_field[$type]];
		$chart_days = array();
		$chart_count = array();
		foreach ($data as $_record) {
			$chart_days[] = rgmdate($_record['time'], 'Y-m-d');
			$chart_count[] = $_record[$this->_field[$type - 1]];
		}

		return array(
			$name,
			$chart_days,
			$chart_count,
		);
	}

	/**
	 * 获取应用纬度详情列表数据
	 * @param $start
	 * @param $end
	 * @param $page_option
	 * @return array|bool
	 */
	public function detail_data($start, $end, $page_option) {

		// 查询数据
		$list = $this->list_by_time($start, $end, $page_option);
		if (empty($list)) {
			return true;
		}
		$total = $this->count_by_time($start, $end);

		// 格式化数据
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
}