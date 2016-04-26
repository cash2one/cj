<?php
/**
 * 名片夹数据过滤
 * $Author$
 * $Id$
 */

class voa_uda_frontend_namecard_base extends voa_uda_frontend_base {
	/** 配置信息 */
	protected $_sets = array();

	public function __construct() {
		parent::__construct();
		$this->_sets = voa_h_cache::get_instance()->get('plugin.namecard.setting', 'oa');
	}

	/** 获取公司信息 */
	protected function _get_company_id($name) {
		if (empty($name)) {
			return 0;
		}

		$serv = &service::factory('voa_s_oa_namecard_company', array('pluginid' => startup_env::get('pluginid')));
		$company = $serv->fetch_by_name($name);
		if (empty($company)) {
			$company = array(
				'm_uid' => startup_env::get('wbs_uid'),
				'ncc_name' => $name
			);
			$ncc_id = $serv->insert($company, true);
			$company['ncc_id'] = $ncc_id;
		}

		return $company['ncc_id'];
	}

	/** 获取职位信息 */
	protected function _get_job_id($name) {
		if (empty($name)) {
			return 0;
		}

		$serv = &service::factory('voa_s_oa_namecard_job', array('pluginid' => startup_env::get('pluginid')));
		$job = $serv->fetch_by_name($name);
		if (empty($job)) {
			$job = array(
				'm_uid' => startup_env::get('wbs_uid'),
				'ncj_name' => $name
			);
			$ncj_id = $serv->insert($job, true);
			$job['ncj_id'] = $ncj_id;
		}

		return $job['ncj_id'];
	}

	/**
	 * 验证真实姓名
	 * @param string $realname
	 * @param array $data
	 * @param array $namecard
	 * @return boolean
	 */
	public function val_realname($realname, &$data, $namecard = array()) {
		$realname = trim($realname);
		if (empty($realname)) {
			$this->errmsg(100, 'realname_is_empty');
			return false;
		}

		if (empty($namecard) || $namecard['nc_realname'] != $realname) {
			$data['nc_realname'] = $realname;
		}

		return true;
	}

	/**
	 * 验证微信号
	 * @param unknown $wxuser
	 * @param unknown $data
	 * @param unknown $namecard
	 * @return boolean
	 */
	public function val_wxuser($wxuser, &$data, $namecard = array()) {
		$wxuser = trim($wxuser);
		if (empty($namecard) || $namecard['nc_wxuser'] != $wxuser) {
			$data['nc_wxuser'] = $wxuser;
		}

		return true;
	}

	/**
	 * 验证职位信息
	 */
	public function val_job($job) {
		$job = trim($job);
		return true;
	}

	/**
	 * 验证分组id
	 * @param unknown $ncf_id
	 * @return boolean
	 */
	public function val_ncf_id($ncf_id) {
		$ncf_id = (int)$ncf_id;
		return true;
	}

	/**
	 * 验证手机号码
	 * @param unknown $mobilephone
	 * @param unknown $data
	 * @param unknown $namecard
	 * @return boolean
	 */
	public function val_mobilephone($mobilephone, &$data, $namecard = array()) {
		$mobilephone = trim($mobilephone);
		if (!validator::is_mobile($mobilephone)) {
			$this->errmsg(100, 'mobilephone_invalid');
			return false;
		}

		if (empty($namecard) || $namecard['nc_mobilephone'] != $mobilephone) {
			$data['nc_mobilephone'] = $mobilephone;
		}

		return true;
	}

	/**
	 * 验证固定电话
	 * @param unknown $phone
	 * @param unknown $data
	 * @param unknown $namecard
	 * @return boolean
	 */
	public function val_telephone($phone, &$data, $namecard = array()) {
		$phone = trim($phone);
		if (!validator::is_phone($phone)) {
			$phone = '';
		}

		if (empty($namecard) || $namecard['nc_telephone'] != $phone) {
			$data['nc_telephone'] = $phone;
		}

		return true;
	}

	/**
	 * 验证邮箱
	 * @param unknown $email
	 * @param unknown $data
	 * @param unknown $namecard
	 * @return boolean
	 */
	public function val_email($email, &$data, $namecard = array()) {
		$email = trim($email);
		if (!validator::is_email($email)) {
			$email = '';
		}

		if (empty($namecard) || $namecard['nc_email'] != $email) {
			$data['nc_email'] = $email;
		}


		return true;
	}

	/**
	 * 验证公司名称
	 * @param unknown $company
	 * @return boolean
	 */
	public function val_company($company) {
		$company = trim($company);
		return true;
	}

	/**
	 * 验证地址
	 * @param unknown $address
	 * @param unknown $data
	 * @param unknown $namecard
	 * @return boolean
	 */
	public function val_address($address, &$data, $namecard = array()) {
		$address = trim($address);
		if (empty($namecard) || $namecard['nc_address'] != $address) {
			$data['nc_address'] = $address;
		}

		return true;
	}

	/**
	 * 验证邮编
	 * @param unknown $postcode
	 * @param unknown $data
	 * @param unknown $namecard
	 * @return boolean
	 */
	public function val_postcode($postcode, &$data, $namecard = array()) {
		$postcode = trim($postcode);
		if (!validator::is_postalcode($postcode)) {
			$postcode = '';
		}

		if (empty($namecard) || $namecard['nc_postcode'] != $postcode) {
			$data['nc_postcode'] = $postcode;
		}

		return true;
	}

	/**
	 * 验证批注
	 * @param unknown $remark
	 * @param unknown $data
	 * @param unknown $namecard
	 * @return boolean
	 */
	public function val_remark($remark, &$data, $namecard = array()) {
		$remark = trim($remark);
		if (empty($namecard) || $namecard['nc_remark'] != $remark) {
			$data['nc_remark'] = $remark;
		}

		return true;
	}
}
