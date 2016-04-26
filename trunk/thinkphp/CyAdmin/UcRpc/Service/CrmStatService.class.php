<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 16/1/29
 * Time: 下午2:42
 */
namespace UcRpc\Service;

use Common\Common\Cache;

class CrmStatService extends AbstractService {

	/** @var int 今日零点 */
	protected $_today = 0;
	/** @var int 昨天零点 */
	protected $_yesterday = 0;
	/** @var array 所有标识名称 */
	protected $_all_identifier = array();
	//字符串今天时间
	protected $_str_today = '';
	public $_adminer = array();
	//字符串昨天时间
	protected $_str_yesterday = '';
	//前天
	protected $_str_before_yesterday = '';
	const LOSE_TIME = 60;//60未产生数据，为流失

	// 构造方法
	public function __construct() {

		$this->_today = rstrtotime(rgmdate(NOW_TIME, 'Y-m-d') . '00:00:00');
		$this->_yesterday = $this->_today - 86400;
		$this->_before_yesterday = $this->_today - 2 * 86400;

		//今天0点
		$this->_str_today = rgmdate(NOW_TIME, 'Y-m-d');
		//昨天0点
		$this->_str_yesterday = rgmdate(NOW_TIME - 86400, 'Y-m-d');
		//前天0点
		$this->_str_before_yesterday = rgmdate(NOW_TIME - 86400 * 2, 'Y-m-d');
		$cache = &\Common\Common\Cache::instance();
		$adminer = $cache->get('common.adminer');

		foreach ($adminer as $_admin) {
			$this->_adminer[$_admin['ca_id']] = $_admin['ca_username'];
		}
		parent::__construct();
	}

	/**
	 * cy统计数据入口方法
	 */
	public function stat() {

		//入库公司信息
		$all_company = $this->insert_company_info();

		//入库所有负责人负责的公司
		$adminer_list = $this->insert_adminer_company($all_company);

		//应用维度数据入库
		$this->sum_plugin_data();

		$this->total_plugin_data();

		return true;
	}

	/**
	 * 统计所有应用数据
	 * @return bool
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
			$temp['pre_devote'] = empty($data['pre_devote']) ? 0 : round($data['pre_devote'], 2);

			// 计算多少企业安装了这个应用
			$temp['install_count'] = $model_daily->count_install_plugin_epid($_ident_ifier);
			if (empty($temp['install_count'])) {
				$temp['install_count'] = 0;
			}
			// 查询昨日应用安装企业数
			$conds_total['s_time'] = $this->_before_yesterday;
			$conds_total['e_time'] = $this->_yesterday;
			//单个应用前天安装数
			$yesterday_install_count = $model_total->get_by_conds_time($conds_total, $_ident_ifier);
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

	/**
	 * 统计应用纬度数据
	 * @return bool
	 */
	public function total_plugin_data() {

		$model_total = D('Stat/StatPluginTotal');
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
		$result = $model_total->total_plugin_data($this->_yesterday, $this->_today, $field_array);
		$result = $result[0];
		$active_company = $this->count_active_company_data();
		$insert_array = array(
			'count_all' => empty($result['count_all']) ? 0 : $result['count_all'],
			'count_index' => empty($result['count_index']) ? 0 : $result['count_index'],
			'new_install' => empty($result['new_install']) ? 0 : $result['new_install'],
			//			'active_plugin' => 0,
			'active_ep' => $active_company,
			//			'active_staff' => 0,
			'time' => NOW_TIME - 86400,
		);

		$model = D('Stat/StatPluginAllData');
		$model->insert($insert_array);

		return true;
	}

	/**
	 * 入库所有公司信息
	 */
	public function insert_company_info() {

		$new_pay = 0;
		$new_company = 0;
		$new_application = 0;

		//统计昨天新增公司数量
		$new_company = $this->count_new_company();

		//统计昨天新增应用数量
		$new_application = $this->count_new_application();

		//统计新增付费公司数量
		$new_pay = $this->count_new_pay();

		//新增公司负责人关联入库
		//$this->company_connect_data();

		//查询所有活跃公司(昨天主数据大于0)
		$active_company = $this->count_active_company_data();

		//统计每日关注总人数
		$attention = $this->count_attention();

		//统计昨天总人数
		$count_member = $this->count_member();

		//统计昨天新增人数
		$count_new_member = $this->count_new_member();

		//每日统计总企业数
		$all_company = $this->count_all_company();

		//每日付费转化率
		$pay_percent = round($new_pay / $all_company, 6);

		//每日激活企业数
		$activation_count = $this->count_activation();

		//每日激活率
		$activation_percent = round($activation_count / $all_company, 6);
		//每日流失数
		//$lose_number = $this->get_lose();
		$lose_number = $this->get_lose_number();

		//\Think\Log::record(var_export('dd', true));
		//每日流失率
		$lose_percent = round($lose_number / $all_company, 6);

		$data_time = NOW_TIME - 86400;
		$insert = array(
			'company_count' => $new_company,
			'plugin_count' => $new_application,
			'pay_count' => $new_pay,
			'time' => $data_time,
			'active_company' => $active_company,
			'active_member' => 0,
			'active_plugin' => 0,
			'attention' => $attention,
			'add_member' => $count_new_member,
			'count_member' => $count_member,
			'all_company' => $all_company,
			'pay_percent' => $pay_percent,
			'activation_count' => $activation_count,
			'activation_percent' => $activation_percent,
			'lose_number' => $lose_number,
			'lose_percent' => $lose_percent,
		);

		//记录数据
		$model_stat_company = D('Stat/StatCompany');
		$model_stat_company->insert($insert);

		return $all_company;
	}

	/**
	 * 获取流失率
	 */
	public function get_lose_number() {

		$model_stat_plugin_daily = D('Stat/StatPluginDaily');
		$model_profile = D('Common/EnterpriseProfile');
		//构造流失时间

		$date['s_time'] = rgmdate(NOW_TIME - 86400 * self::LOSE_TIME, 'Y-m-d');
		$date['e_time'] = rgmdate(NOW_TIME, 'Y-m-d');
		$lose_epid = $model_stat_plugin_daily->list_lose_company($date);


		//查询之前记录的流失公司
		$model_stat_lose = D('Stat/StatLose');
		//今天的公司总数量
		$date_today['s_time'] = $this->_str_yesterday;
		$date_today['e_time'] = $this->_str_today;
		$lose_company = $model_stat_lose->list_all();

		//流失公司的id
		if (!empty($lose_company)) {
			foreach ($lose_company as $val) {
				$lose_list[] = $val['ep_id'];
			}
		}

		//每天查询流失的企业有无再产生过数据，有则移除流失组
		$this->remove_lose_company($lose_list);
		$insert_data = array();
		//去重
		if (!empty($lose_epid)) {
			foreach ($lose_epid as $key_epid => $_epid) {
				$id_list[] = $_epid['ep_id'];
				if (in_array($_epid['ep_id'], $lose_list)) {
					unset($lose_epid[$key_epid]);
					continue;
				}
				$insert_data[] = array(
					'ep_id' => $_epid['ep_id'],
					'time' => NOW_TIME - 86400,
				);
			}
			$model_stat_lose->insert_all($insert_data);

		}

		$lose_number = count($insert_data);

		return $lose_number;
	}

	/**
	 * 把再次活跃的企业移除流失组
	 * @param $lose_list
	 * @return bool
	 */
	public function remove_lose_company($lose_list) {

		$model_stat_lose = D('Stat/StatLose');
		$model_stat_plugin_daily = D('Stat/StatPluginDaily');

		$date['s_time'] = rgmdate(NOW_TIME - 86400 * self::LOSE_TIME, 'Y-m-d');
		$date['e_time'] = rgmdate(NOW_TIME, 'Y-m-d');
		//判断这些公司有没有再次产生过数据
		$lose_company = $model_stat_plugin_daily->list_lose_company($date, $lose_list);
		\Think\Log::record(var_export($lose_company, true));

		if (!empty($lose_company)) {
			foreach ($lose_company as $_lose) {
				$lose_id[] = $_lose['ep_id'];
			}
			foreach ($lose_list as $key => $_id) {
				if (!in_array($_id, $lose_id)) {
					$delete_id[] = $_id;
				}
			}

			$conds_del['ep_id'] = $delete_id;

			//移除流失组
			$model_stat_lose->delete_by_conds($conds_del);
		}

		return true;
	}

	/**
	 * 获取流失率
	 */
	public function get_lose() {

		$model_stat_plugin_daily = D('Stat/StatPluginDaily');
		//构造流失时间

		$date['s_time'] = rgmdate(NOW_TIME - 86400 * self::LOSE_TIME, 'Y-m-d');
		$date['e_time'] = rgmdate(NOW_TIME, 'Y-m-d');

		$ep_list = $model_stat_plugin_daily->list_lose_company($date);

		$lose_number = 0;
		$old_company = array();
		if (!empty($ep_list)) {
			foreach ($ep_list as $key => $val) {

				if (!in_array($val['ep_id'], $old_company)) {
					//总数据数量
					$count_all = $model_stat_plugin_daily->count_lose_company($date, $val['ep_id']);
					if ($count_all < 1) {
						$lose_number ++;
					}
					if ($key % 100 == 0) {
						sleep(1);
					}
					$old_company[] = $val['ep_id'];
				} else {
					continue;
				}
			}
		}

		return $lose_number;
	}

	/**
	 * 获取公司负责人和公司
	 */
	public function insert_adminer_company($all_company) {

		$insert_all = array();
		$model_enterprise_profile = D('Common/EnterpriseProfile');
		$cache = &\Common\Common\Cache::instance();
		$adminer_cache = $cache->get('Common.adminer');

		$model_member_all = D('Stat/StatMemberAll');

		//查询每个负责人的公司
		foreach ($adminer_cache as $val) {
			$list_company = $model_enterprise_profile->list_by_caid($val['ca_id']);

			if (!empty($list_company)) {
				//获取昨日企业数
				$insert_all[] = $this->get_new_company_caid($val['ca_id'], $all_company);
			} else {
				$insert_all[] = array(
					'ca_id' => $val['ca_id'],
					'all_company' => 0,
					'count_member' => 0,
					'company_count' => 0,
					'add_member' => 0,
					'time' => NOW_TIME - 86400,
					'active_company' => 0,
					'pay_count' => 0,
					'pay_percent' => 0,
					'activation_count' => 0,
					'activation_percent' => 0,
					'lose_count' => 0,
					'lose_percent' => 0,
				);
			}
		}

		$model_company_adminer = D('Stat/StatCompanyAdminer');
		$model_company_adminer->insert_all($insert_all);

		return true;
	}

	/**
	 * 根据负责人id统计新增公司
	 * @param $ca_id int 负责人id
	 */
	public function get_new_company_caid($ca_id, $all_company) {

		$model_member_all = D('Stat/StatMemberAll');
		$model_profile = D('Common/EnterpriseProfile');
		$model_company_adminer = D('Stat/StatCompanyAdminer');
		//查该负责人负责的公司
		$conds_profile['ca_id'] = $ca_id;
		$adminer_company = $model_profile->list_by_conds($conds_profile);

		//有负责的公司
		if (!empty($adminer_company)) {
			$company_epid = array_column($adminer_company, 'ep_id');

			//昨天的公司总数量、总员工数量
			$date_yesterday['s_time'] = $this->_str_before_yesterday;
			$date_yesterday['e_time'] = $this->_str_yesterday;
			$record_yesterday = $model_company_adminer->get_yesterday_record($date_yesterday, $ca_id);

			if (empty($record_yesterday)) {
				$count_company_yesterday = 0;
				$count_member_yesterday = 0;
			} else {
				$count_company_yesterday = $record_yesterday['all_company'];
				$count_member_yesterday = $record_yesterday['count_member'];
			}

			//今天的公司总数量
			$date_today['s_time'] = $this->_str_yesterday;
			$date_today['e_time'] = $this->_str_today;

			$today_all_company_count = $model_member_all->count_all_company_epid($date_today, $company_epid);
			$today_all_member_count = $model_member_all->sum_all_member_epid($date_today, $company_epid);

			//\Think\Log::record(var_export($count_member_yesterday, true));

			if (!$today_all_member_count) {
				$today_all_member_count = 0;
			}
			$adminer_active_company = 0;
			//查询负责人今日活跃企业数
			$adminer_active_company = $this->get_adminer_active_company($company_epid);

			//负责人新增付费公司
			$adminer_new_pay = $this->get_adminer_new_pay($company_epid, $ca_id);
			//负责人付费转化率
			$adminer_pay_percent = round($adminer_new_pay / $all_company, 6);
			//负责人公司激活数
			$activation_count = $this->count_activation($company_epid);
			//负责人公司激活率
			$activation_percent = round($activation_count / $all_company, 6);
			//负责人公司流失数
			$lose_count = $this->get_adminer_lose_number($company_epid);
			//负责人公司流失率
			$lose_percent = round($lose_count / $all_company, 6);

			//入库负责人今日新增公司
			$company_count = $this->new_company_insert($company_epid, $ca_id);
			\Think\Log::record(var_export($company_count, true));

			$temp = array(
				'ca_id' => $ca_id,
				'all_company' => $today_all_company_count,
				'count_member' => $today_all_member_count,
				'company_count' => $company_count,
				'add_member' => $today_all_member_count - $count_member_yesterday,
				'time' => NOW_TIME - 86400,
				'active_company' => $adminer_active_company,
				'pay_count' => $adminer_new_pay,
				'pay_percent' => $adminer_pay_percent,
				'activation_count' => $activation_count,
				'activation_percent' => $activation_percent,
				'lose_count' => $lose_count,
				'lose_percent' => $lose_percent,
			);
		}

		return $temp;
	}

	/**
	 * 统计新增公司记录
	 * @param $ep_list array 负责人的公司id
	 * @param $ca_id int 负责人id
	 * @return mixed
	 */
	public function new_company_insert($ep_list, $ca_id) {

		//今天的公司总数量
		$date_today['s_time'] = $this->_str_yesterday;
		$date_today['e_time'] = $this->_str_today;

		$model_profile = D('Common/EnterpriseProfile');
		$company_count = $model_profile->count_add_company_epid($date_today, $ep_list);

		//新增公司信息入库
		$add_list = $model_profile->list_add_company_epid($date_today, $ep_list);

		if (!empty($add_list)) {
			foreach ($add_list as $val) {
				$tmp = array();
				$tmp['ep_id'] = $val['ep_id'];
				$tmp['ca_id'] = $ca_id;
				$tmp['ca_name'] = $this->_adminer[$ca_id];
				$tmp['time'] = NOW_TIME - 86400;
				$insert_all[] = $tmp;
			}
			//入库
			$model_adminer_company_record = D('Stat/StatAdminerCompanyRecord');
			$model_adminer_company_record->insert_all($insert_all);
		}

		return $company_count;
	}

	/**
	 * 跟进人流失公司
	 * @param $ep_id
	 * @return int
	 */
	public function get_adminer_lose_number($ep_id) {

		$model_stat_plugin_daily = D('Stat/StatPluginDaily');
		//构造流失时间

		$date['s_time'] = rgmdate(NOW_TIME - 86400 * self::LOSE_TIME, 'Y-m-d');
		$date['e_time'] = rgmdate(NOW_TIME, 'Y-m-d');
		$lose_epid = $model_stat_plugin_daily->list_lose_company($date, $ep_id);

		//查询之前记录的流失公司
		$model_stat_lose = D('Stat/StatLose');
		//今天的公司总数量
		$date_today['s_time'] = $this->_str_yesterday;
		$date_today['e_time'] = $this->_str_today;
		$lose_company = $model_stat_lose->list_all();

		//流失公司的id
		if (!empty($lose_company)) {
			foreach ($lose_company as $val) {
				$lose_list[] = $val['ep_id'];
			}
		}

		$insert_data = array();
		//去重
		if (!empty($lose_epid)) {
			foreach ($lose_epid as $key_epid => $_epid) {
				if (in_array($_epid['ep_id'], $lose_list)) {
					unset($lose_epid[$key_epid]);
					continue;
				}
				$insert_data[] = array(
					'ep_id' => $_epid['ep_id'],
					'time' => NOW_TIME - 86400,
				);
			}
		}

		//删除之前的数据
		//$model_stat_lose->delete_all();
		//将今天的流失公司记录
		/*if (!empty($insert_data)) {
			$model_stat_lose->insert_all($insert_data);
		}*/

		return count($insert_data);

	}

	/**
	 * 获取负责人付费公司
	 * @param $ep_id
	 */
	public function get_adminer_new_pay($ep_id, $ca_id) {

		$date['s_time'] = $this->_str_yesterday;
		$date['e_time'] = $this->_str_today;

		//查询公司表
		$serv_paysetting = D('Common/CompanyPaysetting');

		$pay_list_date = $serv_paysetting->list_new_pay($date, array(), $ep_id);

		$pay_company_list = array();
		//去重查询新付费公司
		if (!empty($pay_list_date)) {
			$ep_list = array_unique(array_column($pay_list_date, 'ep_id'));
			foreach ($ep_list as $ep_id) {
				$pay_record_count = $serv_paysetting->count_pay_record(array('s_time' => $date['e_time']), $ep_id);
				if ($pay_record_count == 0) {
					$pay_company_list[] = $ep_id;
				}
			}
		}

		//去重
		$pay_company_list = array_unique($pay_company_list);
		return count($pay_company_list);
	}

	/**
	 * 获取负责人今日负责的公司的活跃企业数
	 * @param $ca_id int 负责人id
	 * @return mixed
	 */
	public function get_adminer_active_company($ep_id) {

		$model_plugin_all_data = D('Stat/StatPluginDaily');

		$date['s_time'] = $this->_str_yesterday;
		$date['e_time'] = $this->_str_today;
		$count = $model_plugin_all_data->count_adminer_active_company_date($date, $ep_id);

		return $count;
	}

	/**
	 * 统计每日激活企业数
	 */
	public function count_activation($ep_list = array()) {

		$model_stat_plugin_all_data = D('Stat/StatPluginDaily');
		//构造日期
		$date['s_time'] = $this->_str_yesterday;
		$date['e_time'] = $this->_str_today;

		return $model_stat_plugin_all_data->count_by_conds_activation($date, $ep_list);
	}

	/**
	 * 统计公司总数
	 */
	public function count_all_company() {

		$model_profile = D('Common/EnterpriseProfile');

		return $model_profile->count_all_company();
	}

	/**
	 * 统计所有公司的总人数
	 */
	public function count_member() {

		$model_stat_member_all = D('Stat/StatMemberAll');

		return $model_stat_member_all->count_all_member();
	}

	/**
	 * 统计所有公司的总人数
	 */
	public function count_new_member() {

		$model_stat_member_all = D('Stat/StatMemberAll');

		return $model_stat_member_all->count_new_member();
	}

	/**
	 * 统计每日关注的总人数
	 */
	public function count_attention() {

		//查询昨日关注总人数
		$model_stat_company = D('Stat/StatCompany');

		//昨天数据
		$date['s_time'] = $this->_str_yesterday;
		$date['e_time'] = $this->_str_today;
		$record = $model_stat_company->get_company($date);
		$yesterday_attention = $record['attention'];
		//前天数据
		$conds_time['s_time'] = $this->_str_before_yesterday;
		$conds_time['e_time'] = $date['s_time'];

		$before_yesterday_record = $model_stat_company->get_company($conds_time);
		$before_yesterday_attention = $before_yesterday_record['attention'];
		//今日关注的总人数
		$count = $yesterday_attention - $before_yesterday_attention;

		return $count;
	}

	/**
	 * 统计昨天的新增的公司
	 * @return mixed
	 */
	public function count_new_company() {

		$profile = D('Common/EnterpriseProfile');

		return $profile->count_new_company();
	}

	/**
	 * 统计昨天的新增的应用
	 * @return mixed
	 */
	public function count_new_application() {

		$profile = D('Common/EnterpriseApp');

		return $profile->count_new_app();
	}

	/**
	 * 统计一天的付费公司数
	 * @return int 公司数量
	 */
	public function count_new_pay() {

		$count = 0;
		$payModel = D('Common/CompanyPaysetting');
		$model_profile = D('Common/EnterpriseProfile');
		$current_data = $payModel->list_by_conds_time();
		$model_pay = D('Stat/StatAdminerPayRecord');

		//统计当天产生的付费公司
		if (!empty($current_data)) {
			$ep_list = array_column($current_data, 'ep_id');

			//去重
			$old_data = $payModel->list_by_conds_pay($ep_list);

			if (!empty($old_data)) {
				$old_id = array_column($old_data, 'ep_id');
				$new_id = array_diff($ep_list, $old_id);
			} else {
				$new_id = $ep_list;
			}
			$new_id = array_unique($new_id);
			$count = count($new_id);

			if (!empty($new_id)) {
				//入库新付费公司
				$conds_prof['ep_id'] = $new_id;
				$company_info = $model_profile->list_by_conds($conds_prof);

				foreach ($company_info as $_val) {
					$ca_epid[$_val['ep_id']]['ca_id'] = $_val['ca_id'];
					$ca_epid[$_val['ep_id']]['ca_name'] = $this->_adminer[$_val['ca_id']];
				}
				//入库
				foreach ($new_id as $_ep_id) {
					$tmp = array();
					if (!empty($ca_epid[$_ep_id]['ca_id'])) {

						$tmp['ep_id'] = $_ep_id;
						$tmp['ca_id'] = empty($ca_epid[$_ep_id]) ? '' : $ca_epid[$_ep_id]['ca_id'];
						$tmp['ca_name'] = $ca_epid[$_ep_id]['ca_name'];
						$tmp['time'] = NOW_TIME - 86400;
						$insert_all[] = $tmp;
					}
				}
				if (!empty($insert_all)) {
					$model_pay->insert_all($insert_all);
				}
			}
		}


		return $count;
	}

	/**
	 * cy公司负责人数据同步
	 */
	public function company_connect_data() {

		//新增公司绑定负责人
		$model_profile = D('Common/EnterpriseProfile');
		$list = $model_profile->list_by_conds_connect();
		$model_company_connect = D('Stat/StatCompanyConnect');
		//负责人公司入库
		if (!empty($list)) {
			foreach ($list as $val) {
				$tmp = array();
				$tmp['ep_id'] = $val['ep_id'];
				$tmp['ca_id'] = $val['ca_id'];
				$tmp['time'] = $val['ep_created'];
				$insert[] = $tmp;
			}
			$model_company_connect->insert_all($insert);
		}

		return true;
	}

	/**
	 * 统计主数据大于0的公司数
	 * @return mixed
	 */
	public function count_active_company_data() {

		//判断总数据大于0的
		$model_stat_plugin_daily = D('Stat/StatPluginDaily');
		$date['s_time'] = $this->_str_yesterday;
		$date['e_time'] = $this->_str_today;

		$active_company = $model_stat_plugin_daily->count_active_company($date);

		return count($active_company);
	}

	/**
	 * 活跃公司数
	 * @return int
	 */
	public function count_active_company($active_list) {

		$ep_list = array();
		if (!empty($active_list)) {
			foreach ($active_list as $val) {
				//去重计算
				if (!in_array($val['ep_id'], $ep_list)) {
					$ep_list[] = $val['ep_id'];
				}
			}
		}

		return count($ep_list);
	}

	/**
	 * 活跃应用数
	 * @return int
	 */
	public function count_active_application($active_list) {

		$app_list = array();
		if (!empty($active_list)) {
			foreach ($active_list as $val) {
				//去重计算
				if (!in_array($val['pluginid'], $app_list)) {
					$app_list[] = $val['pluginid'];
				}
			}
		}

		return count($app_list);
	}

	/**
	 * 统计所有公司的所有活跃人员
	 */
	public function count_active_member() {

		$statActive = D('Stat/StatActive');

		return $statActive->count_all_active_member();
	}
}