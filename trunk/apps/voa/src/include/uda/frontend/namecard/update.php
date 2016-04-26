<?php
/**
 * 名片相关的更新操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_namecard_update extends voa_uda_frontend_namecard_base {

	public function __construct() {
		parent::__construct();
	}

	public function namecard_update(&$namecard, $folders) {
		/** 整理需要更新的数据 */
		$data = array();
		/** 数据和处理方法的对应关系 */
		$gps = array(
			'realname' => 'val_realname', /** 真实姓名 */
			'wxuser' => 'val_wxuser', /** 微信号 */
			'mobilephone' => 'val_mobilephone', /** 手机 */
			'telephone' => 'val_telephone', /** 固话 */
			'email' => 'val_email', /** email */
			'address' => 'val_address', /** 地址 */
			'postcode' => 'val_postcode', /** 邮编 */
			'remark' => 'val_remark' /** 批注 */
		);
		if (!$this->_submit2table($gps, $data, $namecard)) {
			return false;
		}

		/** 职位 */
		$job = (string)$this->_request->get('job');
		if (!$this->val_job($job)) {
			return false;
		}

		/** 分组id */
		$ncf_id = (int)$this->_request->get('ncf_id');
		if (!array_key_exists($ncf_id, $folders)) {
			$ncf_id = 0;
		}

		/** 公司 */
		$company = (string)$this->_request->get('company');
		if (!$this->val_company($company)) {
			return false;
		}

		$serv = &service::factory('voa_s_oa_namecard', array('pluginid' => startup_env::get('pluginid')));
		$serv_f = &service::factory('voa_s_oa_namecard_folder', array('pluginid' => startup_env::get('pluginid')));
		$serv_c = &service::factory('voa_s_oa_namecard_company', array('pluginid' => startup_env::get('pluginid')));
		$serv_j = &service::factory('voa_s_oa_namecard_job', array('pluginid' => startup_env::get('pluginid')));
		try {
			$serv->begin();

			/** 获取公司id/职位id */
			$ncc_id = $this->_get_company_id($company);
			$ncj_id = $this->_get_job_id($job);

			if ($ncf_id != $namecard['ncf_id']) {
				0 < $namecard['ncf_id'] && $serv_f->update_num($namecard['ncf_id'], '-');
				0 < $ncf_id && $serv_f->update_num($ncf_id);
				$data['ncf_id'] = $ncf_id;
			}

			if ($ncc_id != $namecard['ncc_id']) {
				0< $namecard['ncc_id'] && $serv_c->update_num($namecard['ncc_id'], '-');
				0< $ncc_id && $serv_c->update_num($ncc_id);
				$data['ncc_id'] = $ncc_id;
			}

			if ($ncj_id != $namecard['ncj_id']) {
				0 < $namecard['ncj_id'] && $serv_j->update_num($namecard['ncj_id'], '-');
				0 < $ncj_id && $serv_j->update_num($ncj_id);
				$data['ncj_id'] = $ncj_id;
			}

			/** 更新名片信息 */
			if (!empty($data)) {
				$serv->update($data, array('nc_id' => $namecard['nc_id']));
			}

			$serv->commit();
		} catch (Exception $e) {
			$serv->rollback();
			/** 入库操作失败 */
			$this->errmsg(100, '操作失败');
			return false;
		}

		/** 返回最新的数据 */
		$namecard = array_merge($namecard, $data);

		/** 更新数据入库 */
		$serv_so = &service::factory('voa_s_oa_namecard_search', array('pluginid' => startup_env::get('pluginid')));
		$ncso = array(
			$namecard['nc_realname'], $namecard['nc_mobilephone'], $namecard['nc_wxuser'], $namecard['nc_address'],
			$namecard['nc_email'], $namecard['nc_qq'], $namecard['nc_postcode'], $namecard['nc_remark'],
			$company, $job
		);
		$serv_so->update(array(
			'ncso_message' => implode("\n", $ncso)
		), array('m_uid' => $namecard['m_uid'], 'nc_id' => $namecard['nc_id']));

		return true;
	}
}
