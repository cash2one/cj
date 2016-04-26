<?php

/**
 * voa_c_cyadmin_api_company_message
 * 用户 信息 发送 接口
 * Created by zhoutao.
 * Created Time: 2015/8/5  16:53
 */
class voa_c_cyadmin_api_company_message extends voa_c_cyadmin_api_base {

	/** 一次的數量 */
	const LIMIT = 2000;

	public function execute() {
		$postx = $this->request->postx();

		if (empty($postx['message_id'])) {
			$this->_errcode = '10003';
			$this->_errmsg = '请选择消息模版';

			return false;
		}
		if (empty($postx['selected_id'])) {
			$this->_errcode = '10004';
			$this->_errmsg = '请选择用户';

			return false;
		}

		// 多条数据插入
		$serv = &service::factory('voa_s_cyadmin_enterprise_message_log');
		$serv_profile = &service::factory('voa_s_cyadmin_enterprise_newprofile');
		$insert_data = array(); // 多条记录集
		$ep_ids = array(); // 企业ID
		// 是否发送所有
		if (in_array(-1, $postx['selected_id'])) {
			$total = $serv_profile->count();
			$times = ceil($total / self::LIMIT);
			// 分次查询
			for ($i = 1; $i <= $times; $i ++) {
				$insert_data = array();
				$conds = array(
					'ep_id >' => 0,
				);
				$pagerOptions = array(
					'per_page' => self::LIMIT,
					'current_page' => $i,
				);
				pager::resolve_options($pagerOptions);
				$pager_options = array($pagerOptions['start'], self::LIMIT);
				$list = $serv_profile->list_by_conds($conds, $pager_options);

				// 获取ep_id
				$ep_ids = array_column($list, 'ep_id');

				// 要写入的消息数据
				foreach ($ep_ids as $_id) {
					$insert_data[] = array(
						'epid' => $_id,
						'meid' => $postx['message_id'],
						'title' => $postx['message_title'],
					);
				}
				$re = $serv->insert_multi($insert_data);
			}
		} else {
			foreach ($postx['selected_id'] as $k => $v) {
				$insert_data[] = array(
					'epid' => $v,
					'meid' => $postx['message_id'],
					'title' => $postx['message_title'],
				);

				$ep_ids[] = $v;
			}
			$re = $serv->insert_multi($insert_data);
		}

		if ($re) {
			$this->_errcode = '0';
			$this->_errmsg = '发送成功';

			return true;
		}

		return true;
	}
}
