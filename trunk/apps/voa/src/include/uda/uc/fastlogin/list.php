<?php
/**
 * voa_uda_uc_login2conn_list
 * 统一数据访问/微信登陆关联登录/关联表列表
 * Created by zhoutao.
 * Created Time: 2015/6/19  10:41
 */

class voa_uda_uc_fastlogin_list extends voa_uda_uc_fastlogin_base {

	public function __construct() {
		parent::__construct();
	}

	// 加密密钥
	private $__state_secret_key = null;

	/**
	 * 登录关联列表
	 */
	public function fetch_by_conditions(&$list, $conditions) {

		// 加密密钥
		$this->__state_secret_key = config::get('voa.rpc.client.auth_key');
		/** 数据操作方法 */
		$list = $this->_fastlogin->list_by_conds($conditions);
		// 获取企业名称
		if (!empty($list)) {

			$ep_ids = array_column($list, 'ep_id');
			$fa_ids = array_column($list, 'fa_id');
			$ca_ids = array_column($list, 'ca_id');

			$enterprises = $this->_fastenterprise->list_by_conds(array('ep_id IN (?)' => $ep_ids));
			$logos = $this->_fastinformation->list_by_conds(array('fa_id IN (?)' => $fa_ids));
			// 最后返回的列表
			$list = null;
			foreach ($enterprises as $k => $enterprise) {
				foreach ($ca_ids as $ke) {
					foreach ($logos as $key => $val) {
						//            ca_id        微信端email             ep_id                         当前时间戳
						$secretdata = $ke . "\t" . $val['email'] . "\t" . $enterprise['ep_id'] . "\t" . startup_env::get('timestamp');
						// 加密传送
						$secretdata = authcode($secretdata, $this->__state_secret_key, 'ENCODE');
						$secretdata = rbase64_encode($secretdata);
						$list[] = array(
							'ep_name' => $enterprise['ep_name'],
							'ep_domain' => $enterprise['ep_domain'],
							'logo' => $val['corp_round_logo_url'],
							'secretdata' => $secretdata
						);
					}
				}
			}
		}
		return true;
	}
}

