<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/2/25
 * Time: 上午11:17
 */

namespace Stat\Service;

class StatPluginAddService extends AbstractService {

	public function __construct() {

		parent::__construct();
		$this->_d = D('Stat/StatPluginAdd');
	}

	/**
	 * 查询最新安装应用的企业
	 * @param $start
	 * @param $end
	 * @param $identifier
	 * @param $page_option
	 * @return array
	 */
	public function list_new_install_ep($start, $end, $identifier, $page_option) {

		// 获取安装应用的企业信息
		$result = array();
		$list = $this->_d->list_by_time_identifier_join_enterprise_profile($start, $end, $identifier, $page_option);
		if (empty($list)) {
			return $result;
		} else {
			foreach ($list as &$_list) {
				// 客户等级
				if (!empty($_list['ep_customer_level'])) {
					$_list['ep_customer_level'] = $this->_customer_level[$_list['ep_customer_level']];
				}
				// 客户状态
				if (!empty($_list['customer_status'])) {
					$_list['customer_status'] = $this->_customer_status[$_list['customer_status']];
				}
				// 注册时间
				$_list['ep_created'] = rgmdate($_list['ep_created'], 'Y-m-d, H:i');
				// 最后更新时间
				$_list['ep_updated'] = rgmdate($_list['ep_updated'], 'Y-m-d, H:i');
				// 是否绑定
				$_list['bangding'] = empty($_list['ep_wxcorpid']) ? '未绑定' : '已绑定';
			}
		}

		// 获取负责人名称和 付费状态
		$this->get_ca_name_pay_status($list);

		return $list;
	}

	/**
	 * 统计最新安装应用的企业
	 * @param $start
	 * @param $end
	 * @param $identifier
	 * @return mixed
	 */
	public function count_new_install_ep($start, $end, $identifier) {

		return $this->_d->count_new_install_ep($start, $end, $identifier);
	}

	/**
	 * 根据开始时间 结束时间查询最新安装的应用 和 安装应用的企业信息
	 * @param $start_time
	 * @param $end_time
	 * @param $page_option
	 * @return mixed
	 */
	public function list_new_install_plugin($start_time, $end_time, $page_option) {

		$list = $this->_d->list_by_time_join_enterprise_profile($start_time, $end_time, $page_option);

		// 格式化时间
		if (!empty($list)) {
			foreach ($list as &$_data) {
				if (!empty($_data['time'])) {
					$_data['time'] = rgmdate($_data['time'], 'Y-m-d');
				}
				// 客户等级
				if (!empty($_data['ep_customer_level'])) {
					$_data['ep_customer_level'] = $this->_customer_level[$_data['ep_customer_level']];
				}
				// 客户状态
				if (!empty($_list['customer_status'])) {
					$_data['customer_status'] = $this->_customer_status[$_data['customer_status']];
				}
				// 注册时间
				$_data['ep_created'] = rgmdate($_data['ep_created'], 'Y-m-d, H:i');
				// 最后更新时间
				$_data['ep_updated'] = rgmdate($_data['ep_updated'], 'Y-m-d, H:i');
				// 是否绑定
				$_data['bangding'] = empty($_data['ep_wxcorpid']) ? '未绑定' : '已绑定';
			}
		}

		// 获取负责人名称和 付费状态
		$this->get_ca_name_pay_status($list);

		return $list;
	}

	public function count_new_install_pliugin($start_time, $end_time) {

		return $this->_d->count_new_install_pliugin($start_time, $end_time);
	}

	/**
	 * 获取负责人名称 和 付费状态
	 * @param $list
	 * @return bool
	 */
	public function get_ca_name_pay_status(&$list) {

		if (empty($list)) {
			return true;
		}

		// 获取ep_id, ca_id
		$ep_ids = array_column($list, 'ep_id');
		$ca_ids = array_unique(array_column($list, 'ca_id'));
		// 去除没有负责人的查询
		if (array_search(0, $ca_ids) !== false) {
			unset($ca_ids[array_search(0, $ca_ids)]);
		}
		foreach ($list as &$__record) {
			$__record['ca_name'] = '';
		}
		// 查询负责人
		if (!empty($ca_ids)) {
			$serv_adminer = D('Common/CommonAdminer');
			$adminer_list = $serv_adminer->list_by_conds(array('ca_id' => $ca_ids));
			// 获取负责人名称
			if (!empty($adminer_list)) {
				foreach ($adminer_list as $_adminer) {
					foreach ($list as &$_record) {
						if ($_adminer['ca_id'] == $_record['ca_id']) {
							// 获取名称
							$_record['ca_name'] = $_adminer['ca_realname'];
						}
					}
					// 如果没有负责人
					if (!isset($_record['ca_name'])) {
						$_record['ca_name'] = '';
					}
				}
			}
		}

		// 查询付费状态
		$serv_pay = D('Common/CompanyPaysetting');
		$pay_array = $serv_pay->list_by_conds_pay($ep_ids);
		if (!empty($pay_array)) {
			// 获取套件信息
			$serv_plugin_group = D('Common/CommonPluginGroup');
			$plugin_group = $serv_plugin_group->list_all();
			$group_data = array();
			foreach ($plugin_group as $_group) {
				$group_data[$_group['cpg_id']] = $_group['cpg_name'];
			}

			// 获取企业付费信息
			foreach ($list as &$_val) {
				$_val['pay_status'] = '';
				foreach ($pay_array as $_pay) {
					if ($_val['ep_id'] == $_pay['ep_id']) {
						$_val['pay_status'] .= $group_data[$_pay['cpg_id']] . ' ' . $this->_pay_status[$_pay['pay_status']] . ';';
					}
				}
			}
		} else {
			foreach ($list as &$_value) {
				$_value['pay_status'] = '';
			}
		}

		return true;
	}
}