<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/2/29
 * Time: 下午4:12
 */

namespace Stat\Service;

use Stat\Model\StatPluginTotalModel;

class StatPluginDailyService extends AbstractService {

	public function __construct() {

		parent::__construct();
		$this->_d = D('Stat/StatPluginDaily');
	}

	/** 数据字段 */
	protected $_field = array(
		'time',
		'install_number',
		'count_index',
		'count_all',
	);
	protected $_fields = array(
		'time',
		'count_all',
		'count_index',
		'active_staff',
		'pre_devote',
		'active_degree',
		'pg_identifier',
	);
	/** 数据字段对应的名称 */
	protected $_field_name = array(
		'install_number' => '应用安装数',
		'count_index' => '应用主数据',
		'count_all' => '应用总数据',
		'active_staff' => '应用活跃员工数',
		'add_staff' => '新增员工数',
	);
	/** 图标数据类型对应字段 */
	protected $_chart_field = array(
		1 => 'install_number',
		2 => 'count_index',
		3 => 'count_all',
		4 => 'active_staff',
		5 => 'add_staff',
	);


	/*
 * 根据公司id获得昨天数据
	 * @param $ep_id 公司id
	 * @return array
 */
	public function get_by_cond_lastday($ep_id) {

		// 获取昨日数据
		$cond = array(
			's_time' => rstrtotime(rgmdate(NOW_TIME, 'Y-m-d')) - 86401,
			'e_time' => rstrtotime(rgmdate(NOW_TIME, 'Y-m-d')),
		);
		$install_number_yestaday = $this->_d->get_by_cond_install_number($ep_id,$cond);
		$yestoday = $this->_d->get_by_cond_lastday($ep_id,$cond);

		//格式化数据
		foreach ($yestoday as $k => $v) {

			$yestoday['install_number'] = $install_number_yestaday['pg_identifier'];

		};

		// 获取前天数据
		$conds = array(
			's_time' => rstrtotime(rgmdate(NOW_TIME, 'Y-m-d')) - 172801,
			'e_time' => rstrtotime(rgmdate(NOW_TIME, 'Y-m-d')) - 86401,
		);
		$install_number_before = $this->_d->get_by_cond_install_number($ep_id,$conds);
		$before = $this->_d->get_by_cond_lastday($ep_id,$conds);

		//格式化数据
		foreach ($before as $k => $v) {

			$before['install_number'] = $install_number_before['pg_identifier'];

		};

		return $this->formit_day($yestoday,$before);

	}

	/*
		 * 格式应用数据 昨日数据
	     * @param $yestoday 昨日数据
		 * @param $before 前天数据
		 * @return array
		 */
	public function formit_day($yestoday,$before) {

		if (!empty($yestoday) && !empty($before)) {
			//应用安装数百分比
			$install_number = (($yestoday['install_number'] - $before['install_number'])/$before['install_number'])*100;
			//应用主数据百分比
			$index_percent = (($yestoday['count_index'] - $before['count_index'])/$before['count_index'])*100;
			//应用总数据百分比
			$all_percent = (($yestoday['count_all'] - $before['count_all'])/$before['count_all'])*100;
			//应用活跃员工数百分比
			//$staff_percent = (($yestoday['active_staff'] - $before['active_staff'])/$before['active_staff']);
			$install_number = round($install_number,2);
			$index_percent = round($index_percent,2);
			$all_percent = round($all_percent,2);
		} else {
			//应用安装百分比
			$install_number = 0;
			//应用主数据百分比
			$index_percent = 0;
			//应用总数据百分比
			$all_percent = 0;
			//应用活跃员工数百分比
			//$staff_percent = 0;

		}

		$result = array(
			'header' => array(
				array(
					'name' => '应用安装数',
					'count' => isset($yestoday['install_number'])?$yestoday['install_number']:0,
					'percent' => $install_number
				),
				array(
					'name' => '应用主数据',
					'count' => isset($yestoday['count_index'])?$yestoday['count_index']:0,
					'percent' => $index_percent
				),
				array(
					'name' => '应用总数据',
					'count' => isset($yestoday['count_all'])?$yestoday['count_all']:0,
					'percent' => $all_percent
				),
				array(
					'name' => '应用活跃员工数',
					'count' => 0,
					'percent' => 0
				),
				array(
					'name' => '新增活跃员工数',
					'count' => 0,
					'percent' => 0
				)
			)
		);

		return $result;
	}

	/*
	 * 根据公司id获得数据详情列表
	 * @param $start_time 搜索开始时间
	 * @param $end_time 搜索截至时间
	 * @param $ep_id	公司id
	 * @param $page_option 分页配置
	 * @return array
	 *
	 */
	public function list_by_epid_view($start_time, $end_time, $ep_id, $page_option) {

		$list = $this->_d->list_by_epid_detail($start_time, $end_time, $ep_id, $page_option);
		if (empty($list)) {
			return false;
		}

		$install_number = $this->_d->get_by_cond_day_install_number($ep_id);

		//格式化数据
		foreach ($list as $k => &$v) {

			$v['install_plugin'] = $install_number[$k]['pg_identifier'];


		};

		$total = $this->_d->count_by_epid_view($start_time, $end_time, $ep_id);

		$result = array();
		foreach ($list as $_record) {
			$temp['date'] = rgmdate($_record['time'], 'Y-m-d');
			$temp['install_plugin'] =$_record['install_plugin'];
			if(empty($temp['active_staff'])) {
				$temp['active_staff'] = 0;
			}
			if(empty($temp['add_staff'])) {
				$temp['add_staff'] = 0;
			}
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
			$total
		);
	}


	/*
	 * 根据公司id获得数据详情列表
	 * @param $start_time 搜索开始时间
	 * @param $end_time 搜索截至时间
	 * @param $ep_id	公司id
	 * @param $page_option 分页配置
	 * @return array
	 *
	 */
	public function count_by_epid_view($start_time, $end_time, $ep_id) {

		$list = $this->_d->count_by_epid_view($start_time, $end_time, $ep_id);
		return count($list);
	}

	/*
	 * 单个企业 套件/应用详情
	 * @param $start_time 搜索开始时间
	 * @param $end_time 搜索截至时间
	 * @param $ep_id	公司id
	 * @param $identifier 公司唯一标识
	 * @param $page_option 分页配置
	 * @return array
	 */
	public function list_by_identifier_view($start_time, $end_time, $ep_id, $identifier, $page_option){

		$field = "*";

		$list = $this->_d->plugin_by_identifier_time($start_time, $end_time, $ep_id, $identifier, $field, $page_option);

		$total = $this->_d->count_by_time_epid_identifier($start_time, $end_time, $ep_id, $identifier);
		// 选项
		$select = StatPluginTotalModel::$_identifier_name;
		foreach($select as $_val){
			$_val[$list['$_val']];

		}
		$result = array();
		foreach ($list as $_record) {
			$temp['date'] = rgmdate($_record['time'], 'Y-m-d');
			$temp['pg_name'] = $select[$_record['pg_identifier']];
			foreach ($_record as $_key => $_val) {
				if (in_array($_key, $this->_fields)) {
					$temp[$_key] = $_val;
				}
			}
			$result[] = $temp;
			unset($temp);
		}

		return array(
			$result,
			$total
		);

	}

	/*
	 * 单个企业 全部套件/应用详情
	 * @param $start_time 搜索开始时间
	 * @param $end_time 搜索截至时间
	 * @param $ep_id	公司id
	 * @param $page_option 分页配置
	 * @return array
	 */
	public function list_by_all_view($start_time, $end_time, $ep_id, $page_option){

		$field = "*";

		$list = $this->_d->plugin_by_identifier_time($start_time, $end_time, $ep_id, null,$field, $page_option);

		$total = $this->_d->count_by_time_epid_identifier($start_time, $end_time, $ep_id, null);

		// 选项
		$select = StatPluginTotalModel::$_identifier_name;
		foreach($select as $_val){
			$_val[$list['$_val']];

		}
		$result = array();
		foreach ($list as $_record) {
			$temp['date'] = rgmdate($_record['time'], 'Y-m-d');
			$temp['pg_name'] = $select[$_record['pg_identifier']];
			foreach ($_record as $_key => $_val) {
				if (in_array($_key, $this->_fields)) {
					$temp[$_key] = $_val;
				}
			}
			$result[] = $temp;
			unset($temp);
		}

		return array(
			$result,
			$total
		);

	}

	/**
	 * 获取应用数据 图表数据
	 * @param $start
	 * @param $end
	 * @param $type
	 * @return array
	 */
	public function plugin_chart_data($start, $end, $type, $ep_id) {

		// 根据时间获取数据
		$data = $this->_d->list_by_time($start, $end, $ep_id);
		//获取应用安装数

		if (empty($data)) {
			return true;
		}
		$install_number = $this->_d->get_by_cond_day_install_number($ep_id);

		//格式化数据
		foreach ($data as $k => &$v) {

			$v['install_number'] = $install_number[$k]['pg_identifier'];

		};

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

	/*
	 * 单个企业 导出全部套件/应用详情
	 * @param $start_time 搜索开始时间
	 * @param $end_time 搜索截至时间
	 * @param $ep_id	公司id
	 * @param $page_option 分页配置
	 * @return array
	 */
	public function list_by_all_view_out($start_time, $end_time, $ep_id, $page_option){

		$field = "*";

		$list = $this->_d->plugin_by_identifier_time($start_time, $end_time, $ep_id, null,$field, $page_option);

		$total = $this->_d->count_by_time_epid_identifier($start_time, $end_time, $ep_id, null);

		// 选项
		$select = StatPluginTotalModel::$_identifier_name;
		foreach($select as $_val){
			$_val[$list['$_val']];

		}
		$result = array();
		foreach ($list as $_record) {
			$temp['date'] = rgmdate($_record['time'], 'Y-m-d');
			$temp['pg_name'] = $select[$_record['pg_identifier']];
			$temp['add_staff'] = 0;
			$temp['active_staff'] = 0;
			$temp['active_percent'] = 0;
			foreach ($_record as $_key => $_val) {
				if (in_array($_key, $this->_fields)) {
					$temp[$_key] = $_val;
				}
			}
			$result[] = $temp;
			unset($temp);
		}

		return array(
			$result,
			$total
		);

	}

	/*
	 * 单个企业 根据公司id导出套件/应用详情
	 * @param $start_time 搜索开始时间
	 * @param $end_time 搜索截至时间
	 * @param $ep_id	公司id
	 * @param $identifier 公司唯一标识
	 * @param $page_option 分页配置
	 * @return array
	 */
	public function list_by_identifier_view_out($start_time, $end_time, $ep_id, $identifier, $page_option){

		$field = "*";

		$list = $this->_d->plugin_by_identifier_time($start_time, $end_time, $ep_id, $identifier, $field, $page_option);

		$total = $this->_d->count_by_time_epid_identifier($start_time, $end_time, $ep_id, $identifier);
		// 选项
		$select = StatPluginTotalModel::$_identifier_name;
		foreach($select as $_val){
			$_val[$list['$_val']];

		}
		$result = array();
		foreach ($list as $_record) {
			$temp['date'] = rgmdate($_record['time'], 'Y-m-d');
			$temp['pg_name'] = $select[$_record['pg_identifier']];
			$temp['add_staff'] = 0;
			$temp['active_staff'] = 0;
			$temp['active_percent'] = 0;
			foreach ($_record as $_key => $_val) {
				if (in_array($_key, $this->_fields)) {
					$temp[$_key] = $_val;
				}
			}
			$result[] = $temp;
			unset($temp);
		}

		return array(
			$result,
			$total
		);

	}

}