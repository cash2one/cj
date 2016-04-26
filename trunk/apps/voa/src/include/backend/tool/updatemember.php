<?php
/**
 * 命令行方式管理通讯录数据
 * @uses php tool.php -n updatemember -domain demo -file mems.php
 *
 * $Author$
 * $Id$
 */

class voa_backend_tool_updatemember extends voa_backend_base {

	private $__opts = array();
	/** 动作方法前缀名 */
	private $__prefix = '__';

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;
		startup_env::set('domain', $opts['domain']);
		voa_h_conf::init_db();
	}

	public function main() {

		return;
		$serv_job = &service::factory('voa_s_oa_common_job');
		$jobs = $serv_job->fetch_all();
		$cj_name2id = array();
		foreach ($jobs as $_v) {
			$cj_name2id[$_v['cj_name']] = $_v['cj_id'];
		}

		require APP_PATH.'/data/'.$this->__opts['file'];
		$serv_m = &service::factory('voa_s_oa_member');
		$uda_m = &uda::factory('voa_uda_frontend_member_update');
		foreach ($mems as $_mobile => $_v) {
			$mem = $serv_m->fetch_by_mobilephone($_mobile);
			if (empty($mem) || empty($cj_name2id[$_v[0]])) {
				continue;
			}

			$submit = $mem;
			$submit['cj_id'] = $cj_name2id[$_v[0]];
			$submit['m_number'] = (int)substr($_v[1], 2);
			$member = array();
			$member_field = array();
			$uda_m->update($mem, $submit, $member, $member_field);

			echo implode(':', $submit)." is ok\n";
		}

		echo "complete...\n";
	}

}
