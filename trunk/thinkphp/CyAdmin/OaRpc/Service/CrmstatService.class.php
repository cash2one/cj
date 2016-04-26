<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 2016/1/27
 * Time: 10:54
 */

namespace OaRpc\Service;

class CrmstatService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 统计每日新增公司
	 */
	public function add_company() {

		$profile = D('Common/EnterpriseProfile');

		$conds_company['created > ?'] = rstrtotime('yesterday');
		$conds_company['created < ?'] = rstrtotime('today');

		$count = $profile->count_by_conds($conds_company);

		return $count;
	}

	/**
	 * 处理OA的每日数据
	 * @param $ep_id
	 * @param $plugin_data
	 * @return bool
	 */
	public function deal_stat($ep_id, $plugin_data) {

		if (empty($ep_id) || empty($plugin_data)) {
			return false;
		}

		// 获取应用统计数据
		$plugin_stat = $plugin_data['install_plugin'];

		//公司人员信息入库
		$this->insert_company_member($ep_id, $plugin_data);

		//unset($plugin_data['install_plugin']);
		// 应用纬度数据
		$this->_plugin_data($ep_id, $plugin_stat);

		return true;
	}

	/**
	 * 入库公司员工
	 * @param $ep_id int 公司id
	 * @param $plugin_data array 统计数据
	 * @return bool
	 */
	public function insert_company_member($ep_id, $plugin_data) {

		//待入库数据
		$data['ep_id'] = $ep_id;
		$data['attention'] = $plugin_data['attention'];
		$data['unattention'] = $plugin_data['unattention'];
		$data['all'] = $plugin_data['mem_number'];
		$data['time'] = NOW_TIME - 86400;

		$model_stat_member_all = D('Stat/StatMemberAll');
		//计算新增员工数
		$old_count = $model_stat_member_all->get_old_member($ep_id);
		$data['add'] = $plugin_data['mem_number'] - $old_count['all'];

		//应用安装数
		$data['install_plugin'] = count($plugin_data['install_plugin']);
		$model_stat_member_all->insert($data);

		return true;
	}

	/**
	 * 每日企业应用数据
	 * @param $ep_id
	 * @param $plugin_stat
	 * @return bool
	 */
	protected function _plugin_data($ep_id, $plugin_stat) {

		$today_time = rstrtotime(rgmdate(NOW_TIME, 'Y-m-d') . '00:00:00');
		$yesterday_time = $today_time - 86400;

		if (empty($plugin_stat)) {
			return false;
		}
		//获取该公司以前安装过的应用列表
		$model_plugin_add = D('Stat/StatPluginAdd');

		$company_plugin_list = $model_plugin_add->list_by_epid($ep_id);

		//应用历史列表
		if (!empty($company_plugin_list)) {
			foreach ($company_plugin_list as $_plugin_name) {
				$plugin_list[] = $_plugin_name['pg_identifier'];
			}
		}
		// 要入每日企业应用表的数据
		$insert_data = array();
		// 企业新装应用表的数据
		$insert_add_data = array();
		foreach ($plugin_stat as $_plugin) {
			//排除统计的应用
			if (in_array($_plugin['cp_identifier'], \Stat\Model\StatPluginTotalModel::$except)) {
				continue;
			}
			$insert_data[] = array(
				'ep_id' => $ep_id,
				'pg_identifier' => empty($_plugin['cp_identifier']) ? '' : $_plugin['cp_identifier'],
				'pg_name' => empty($_plugin['cp_name']) ? '' : $_plugin['cp_name'],
				//				'active_staff' => $_plugin['active_staff'],
				//				'active_degree' => $_plugin['active_degree'],
				'count_all' => empty($_plugin['plugin_total']) ? 0 : $_plugin['plugin_total'],
				'count_index' => empty($_plugin['main_data_number']) ? 0 : $_plugin['main_data_number'],
				'pre_devote' => empty($_plugin['capita_contribution']) ? 0 : round($_plugin['capita_contribution'], 2),
				'is_activation' => empty($_plugin['is_activation']) ? 0 : $_plugin['is_activation'],
				'time' => NOW_TIME - 86400,
			);

			/*// 传过来的数据已经是安装了的应用,只要判断 是否更新时间在当天 即是新装
			if (!empty($_plugin['cp_updated']) && $_plugin['cp_updated'] > $yesterday_time && $_plugin['cp_updated'] < $today_time) {
				$insert_add_data[] = array(
					'ep_id' => $ep_id,
					'pg_identifier' => empty($_plugin['cp_identifier']) ? '' : $_plugin['cp_identifier'],
					'pg_name' => empty($_plugin['cp_name']) ? '' : $_plugin['cp_name'],
					'time' => NOW_TIME - 86400,
				);
			}*/

			//不在历史列表里即为新增应用
			if (!in_array($_plugin['cp_identifier'], $plugin_list)) {
				$insert_add_data[] = array(
					'ep_id' => $ep_id,
					'pg_identifier' => empty($_plugin['cp_identifier']) ? '' : $_plugin['cp_identifier'],
					'pg_name' => empty($_plugin['cp_name']) ? '' : $_plugin['cp_name'],
					'time' => NOW_TIME - 86400,
				);
			}
			//昨天的应用列表
			$current_plugin[] = $_plugin['cp_identifier'];
		}
		//被删除的应用
		$delete_plugin = array_diff($plugin_list, $current_plugin);
		if (!empty($delete_plugin)) {
			//删除被删除的应用
			$conds_delete['pg_identifier'] = $delete_plugin;
			$conds_delete['ep_id'] = $ep_id;
			$model_plugin_add->delete_by_conds($conds_delete);
		}

		// 入库
		$serv_plugin_daily = D('Stat/StatPluginDaily');
		$serv_plugin_daily->insert_all($insert_data);
		if (!empty($insert_add_data)) {
			$model_plugin_add->insert_all($insert_add_data);
		}

		return true;
	}
}