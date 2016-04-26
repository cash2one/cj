<?php
/**
 * 报销相关的删除操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_reimburse_delete extends voa_uda_frontend_reimburse_base {

	public function __construct() {
		parent::__construct();
	}


	public function reimburse_delete($rb_ids, $shard_key = array()) {

		$serv = &service::factory('voa_s_oa_reimburse', array('pluginid' => startup_env::get('pluginid')));
		$serv_bill = &service::factory('voa_s_oa_reimburse_bill', array('pluginid' => startup_env::get('pluginid')));
		$serv_bill_submit = &service::factory('voa_s_oa_reimburse_bill_submit', array('pluginid' => startup_env::get('pluginid')));
		$serv_post = &service::factory('voa_s_oa_reimburse_post', array('pluginid' => startup_env::get('pluginid')));
		$serv_proc = &service::factory('voa_s_oa_reimburse_proc', array('pluginid' => startup_env::get('pluginid')));

		$conditions_by_rb_ids = array('rb_id' => array($rb_ids));

		// 根据提交的清单表(bill_submit)找到对应的清单id, rb_id => rbb_id
		$rbb_list = $serv_bill_submit->fetch_by_conditions(array(
			'rb_id' => array($rb_ids),
			'rbs_status' => array(voa_d_oa_reimburse_bill_submit::STATUS_REMOVE, '<'),
		));

		// @ 待删除的公共附件id
		$at_ids = array();
		try {

			// 开始过程
			$serv->begin();

			// @ 删除主表
			$serv->delete_by_ids($rb_ids, $shard_key);

			// @ 删除回复表
			$serv_post->delete_by_conditions($conditions_by_rb_ids, $shard_key);
			// @ 删除进度表
			$serv_proc->delete_by_conditions($conditions_by_rb_ids, $shard_key);
			if (!empty($rbb_list)) {

				// 找到提交清单id rbbs_id
				$rbbs_ids = array_keys($rbb_list);
				// 找到清单id
				$rbb_ids = array();
				foreach ($rbb_list as $_rbb) {
					if (!isset($rbbs_ids[$_rbb['rbb_id']])) {
						$rbbs_ids[$_rbb['rbb_id']] = $_rbb['rbb_id'];
						if ($_rbb['at_id']) {
							$at_ids[] = $_rbb['at_id'];
						}
					}
				}

				// @ 删除清单表
				$serv_bill->delete_by_ids($rbb_ids);
				// @ 删除提交清单表
				$serv_bill_submit->delete_by_ids($rbbs_ids);

			}

			// 提交过程
			$serv->commit();

		} catch (Exception $e) {
			$serv->rollback();
			$this->errmsg(100, '操作失败');
			return false;
		}

		if ($at_ids) {
			// 根据公共附件表id删除附件
			$uda_attachment_delete = &uda::factory('voa_uda_frontend_attachment_delete');
			$uda_attachment_delete->delete($at_ids);
		}

		return true;
	}

	/**
	 * 删除指定的报销清单
	 * @param int $nc_id
	 * @param array $shard_key
	 * @return boolean
	 */
	public function reimburse_bill_delete($rbb_id, $shard_key = array()) {
		$rbb_id = (int)$rbb_id;
		$serv = &service::factory('voa_s_oa_reimburse_bill', array('pluginid' => startup_env::get('pluginid')));
		try {
			$serv->begin();
			$serv->delete_by_ids($rbb_id);
			$serv->commit();
		} catch (Exception $e) {
			$serv->rollback();
			/** 入库操作失败 */
			$this->errmsg(100, '操作失败');
			return false;
		}

		return true;
	}
}
