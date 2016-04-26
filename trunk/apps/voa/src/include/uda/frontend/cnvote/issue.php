<?php
/**
 * Created by PhpStorm.
 * User: Muzhitao
 * Date: 2016/2/19 0019
 * Time: 15:13
 * Email：muzhitao@vchangyi.com
 */

class voa_uda_frontend_cnvote_issue extends voa_uda_frontend_cnvote_abstract {
	private $__service = null;
	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_cnvote_setting();
		}

	}

	public function add($data) {

		// 签到权限
		$m_uids = !empty($data['m_uids']) ? $data['m_uids'] : '';
		$cd_ids = !empty($data['cd_ids']) ? $data['cd_ids'] : '';
		$all = (int)$data['is_all'];

		// all  0:指定对象 1:全公司
		$datas = array(
			'm_uids' => $all == 1 ? '':$m_uids,
			'cd_ids' => $all == 1 ? '': $cd_ids,
			'all' => $all,
		);

		$this->__service->update_settings($datas);

		voa_h_cache::get_instance()->get('plugin.cnvote.setting', 'oa', true);

		return true;
	}
}
