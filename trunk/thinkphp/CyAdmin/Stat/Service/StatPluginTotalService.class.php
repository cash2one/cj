<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/2/27
 * Time: 下午2:18
 */

namespace Stat\Service;

class StatPluginTotalService extends AbstractService {

	/** @var int 今日零点 */
	protected $_today = 0;
	/** @var int 昨天零点 */
	protected $_yesterday = 0;
	/** @var array 所有标识名称 */
	protected $_all_identifier = array();

	public function __construct() {

		$this->_today = rstrtotime(rgmdate(NOW_TIME, 'Y-m-d') . ' 00:00:00');
		$this->_yesterday = $this->_today - 86400;

		parent::__construct();
		$this->_d = D('Stat/StatPluginTotal');
	}

	/**
	 * 根据时间 标识 查询
	 * @param $start
	 * @param $end
	 * @param $identifier
	 * @param $page_option
	 * @return array
	 */
	public function list_by_time_or_identifier($start, $end, $identifier, $page_option) {

		$list = $this->_d->list_by_time_or_identifier($start, $end, $identifier, $page_option);

		// 格式化时间
		if (!empty($list)) {
			foreach ($list as &$_record) {
				$_record['time'] = empty($_record['time']) ? '-' : rgmdate($_record['time'], 'Y-m-d');
			}
		} else {
			$list = array();
		}

		return $list;
	}

	/**
	 * 根据时间 标识 统计
	 * @param $start
	 * @param $end
	 * @param $identifier
	 * @return mixed
	 */
	public function count_by_time_or_identifier($start, $end, $identifier) {

		return $this->_d->count_by_time_or_identifier($start, $end, $identifier);
	}

	/**
	 * 统计应用纬度数据
	 * @return bool
	 */
	public function total_plugin_data() {

		// 统计所有应用数据
		$field_array = array(
			'SUM(count_all) as count_all', // 总数据
			'SUM(count_index) as count_index', // 主数据
			//			'active_plugin', // 活跃应用数
			//			'active_ep', // 活跃企业数
			//			'active_staff', // 活跃员工数
			'SUM(new_install) as new_install', // 新增安装企业数
		);
		$field_array = implode(',', $field_array);
		$result = $this->_d->total_plugin_data($this->_yesterday, $this->_today, $field_array);
		$result = $result[0];

		$insert_array = array(
			'count_all' => empty($result['count_all']) ? 0 : $result['count_all'],
			'count_index' => empty($result['count_index']) ? 0 : $result['count_index'],
			'new_install' => empty($result['new_install']) ? 0 : $result['new_install'],
//			'active_plugin' => 0,
//			'active_ep' => 0,
//			'active_staff' => 0,
			'time' => NOW_TIME - 86400,
		);

		$model = D('Stat/StatPluginAllData');
		$model->insert($insert_array);

		return true;
	}

	/**
	 * 统计今日应用的字段总和
	 * @return mixed
	 */
	public function sum_plugin_data() {

		// 所有应用标识
		$this->_all_identifier = \Stat\Model\StatPluginTotalModel::$_identifier_name;
		// 查询字段汇总
		$field = array(
			'SUM(count_all) as count_all',
			'SUM(count_index) as count_index',
			'AVG(pre_devote) as pre_devote',
		);
		$field = implode(',', $field);

		// 统计今日所有企业产生的应用数据
		$model_daily = D('Stat/StatPluginDaily');
		$model_total = D('Stat/StatPluginTotal');
		// 入库的数组
		$insert_array = array();
		// 遍历所有应用标识
		foreach ($this->_all_identifier as $_ident_ifier => $_name) {
			$temp = array(
				'pg_identifier' => $_ident_ifier, // 标识
				'pg_name' => $_name, // 名称
				//				'active_staff' => , // 应用活跃人数 (暂无)
				//				'active_degree' => , // 应用活跃度 (暂无)
				//				'new_active_staff' => , // 新增活跃员工数 (暂无)
			);

			$data = $model_daily->stat_plugin_by_identifier_time($this->_yesterday, $this->_today, $_ident_ifier, $field);
			//			$data = $model_daily->stat_plugin_by_identifier_time(0,0, $_ident_ifier, $field);
			$data = $data[0];
			$temp['count_all'] = empty($data['count_all']) ? 0 : $data['count_all'];
			$temp['count_index'] = empty($data['count_index']) ? 0 : $data['count_index'];
			$temp['pre_devote'] = empty($data['pre_devote']) ? 0 : $data['pre_devote'];

			// 计算多少企业安装了这个应用
			$temp['install_count'] = $model_daily->count_install_plugin_epid($_ident_ifier);
			if (empty($temp['install_count'])) {
				$temp['install_count'] = 0;
			}
			// 查询昨日应用安装企业数
			$yesterday_install_count = $model_total->get_by_conds(array(
				'time>?' => $this->_yesterday,
				'time<?' => $this->_today,
			));
			$temp['new_install'] = 0;
			// 计算新增安装的企业数
			if (!empty($yesterday_install_count)) {
				$temp['new_install'] = $temp['install_count'] - $yesterday_install_count['install_count']; // 昨日安装企业减去今日统计数量
			} else {
				$temp['new_install'] = $temp['install_count'];
			}
			$temp['time'] = NOW_TIME - 86400;

			// 写入入库数组
			$insert_array[] = $temp;
			unset($temp);
		}

		if (!empty($insert_array)) {
			$model_total->insert_all($insert_array);
		}

		return true;
	}
}
