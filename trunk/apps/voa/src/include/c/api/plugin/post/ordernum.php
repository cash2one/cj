<?php
/**
 * 更改插件排序列表
 * $Author$
 * $Id$
 */

class voa_c_api_plugin_post_ordernum extends voa_c_api_plugin_base {
	/** 最大入库数 */
	protected $_max_num = 50;

	public function execute() {

		/** 是否常用 */
		$isfav = (int)$this->_get('isfav', 0);

		/** 读取排序号/应用id */
		$onstr = (string)$this->_get('ordernum', '');
		$pstr = (string)$this->_get('pluginid', '');

		$ordernums = explode(',', $onstr);
		$pluginids = explode(',', $pstr);

		/** 判断数据格式是否对称 */
		if (count($ordernums) != count($pluginids)) {
			$this->_set_errcode(voa_errcode_api_plugin::PLUGIN_OR_ORDERNUM_ERR);
			return false;
		}

		/** 判断排序号和应用 id 的正确性 */
		$pluginid2ordernum = array();
		$count = 0;
		foreach ($pluginids as $_k => $_id) {
			if (!array_key_exists($_id, $this->_plugins)) {
				continue;
			}

			$pluginid2ordernum[$_id] = (int)$ordernums[$_k];
			if (++ $count >= $this->_max_num) {
				break;
			}
		}

		/** 判断需要更新的记录是否为空 */
		if (empty($pluginid2ordernum)) {
			$this->_set_errcode(voa_errcode_api_plugin::PLUGIN_IS_NOT_EXIST);
			return false;
		}

		/** 处理应用数据 */
		$serv_pd = &service::factory('voa_s_oa_common_plugin_display', array('pluginid' => startup_env::get('pluginid')));
		try {
			$serv_pd->begin();

			/** 先清理已存在的记录 */
			$serv_pd->del_by_uid_pluginids($this->_member['m_uid'], array_keys($pluginid2ordernum));

			/** 应用信息入库 */
			$pds = array();
			foreach ($pluginid2ordernum as $id => $ordernum) {
				$pds[] = array(
					'm_uid' => $this->_member['m_uid'],
					'cpd_isfav' => $isfav,
					'cp_pluginid' => $id,
					'cpd_ordernum' => $ordernum
				);
			}

			$serv_pd->insert_multi($pds);

			$serv_pd->commit();
		} catch (Exception $e) {
			$serv_pd->rollback();
			$this->_set_errcode(voa_errcode_api_plugin::PLUGIN_ORDER_FAILED);
			return false;
		}

		return true;
	}

}
