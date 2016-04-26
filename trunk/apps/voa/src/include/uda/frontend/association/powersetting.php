<?php
/**
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/12/2
 * Time: 16:18
 */

class voa_uda_frontend_association_powersetting extends voa_uda_frontend_community_abstract {

	// 列表
	protected $_serv = null;
	private $__request = null;

	public function __construct() {

		parent::__construct();
		if ($this->_serv == null) {
			$this->_serv = new voa_s_oa_community_setting();
		}
	}

	/**
	 * 社群权限
	 * @param $request
	 * @return bool
	 * @throws help_exception
	 */
	public function add_power_setting($request) {

		$fields = array(
			// 用户ID
			'm_uids' => array(
				'm_uids', parent::VAR_ARR,
				null,
				null, false
			)
		);
		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}

		// 添加社群权限人员
		$m_uids = !empty($this->__request['m_uids']) ? $this->__request['m_uids'] : '';

		$data = array('m_uids' => $m_uids);
		$this->_serv->update_setting($data);


		voa_h_cache::get_instance()->get('plugin.community.setting', 'oa', true);

		return true;
	}

	/**
	 * 执行计划任务
	 * @param array $in
	 * @param array $out
	 * @return bool
	 * @throws help_exception
	 */
	public function update_crontab($in=array(), &$out=array()) {
		$fields = array(
			// 用户ID
			'crontab' => array(
				'crontab', parent::VAR_INT,
				null,
				null, false
			)
		);
		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $in)) {
			return false;
		}

		// 添加
		$crontab = !empty($this->__request['crontab']) ? $this->__request['crontab'] : 0;

		$work_begin = time();
		$data = array('crontab' => $crontab, 'crontab_begin' => $work_begin);
		if($this->_serv->update_setting($data)) {
			$out = 0;
		}else {
			$out = 500;
		}


		$origin_str = implode(',', array($this->_setting['domain'], 'community'));
		if($crontab == 1) {
			//添加计划任务
			$this->__add_task(md5($origin_str), $work_begin, 'community');
		}else{
			//删除计划任务
			$this->__del_task($origin_str, 'community');
		}

		voa_h_cache::get_instance()->get('plugin.community.setting', 'oa', true);

		return true;
	}

	/**
	 * 新增计划任务
	 * @param int $taskid 计划任务ID
	 * @param string $runtime 执行时间点
	 * @param string $type 任务类型
	 */
	public function __add_task($taskid, $runtime= '', $type) {

		$rpc_crontab = voa_h_rpc::phprpc(config::get('voa.uc_url') . 'OaRpc/Rpc/Crontab');
		//凌晨3点
		$ts = rstrtotime(rgmdate(strtotime('+1 day'), 'Y-m-d')) + 60*60*3;

		// 添加计划任务
		return $rpc_crontab->add(array(
			'taskid' => $taskid,
			'domain' => $this->_setting['domain'],
			'type' => $type,
			'ip' => '',
			'runtime' => $ts,
			'endtime' => 0,
			'looptime' => 86400,
			'times' => 0,
			'runs' => 0
		));
	}

	/**
	 * 删除任务
	 * @param int $taskid 任务id
	 */
	public function __del_task($taskid, $type) {

		$setting = voa_h_cache::get_instance()->get('setting', 'oa');
		$rpc_crontab = voa_h_rpc::phprpc(config::get('voa.uc_url') . 'OaRpc/Rpc/Crontab');
		return $rpc_crontab->Del_by_taskid_domain_type($taskid, $setting['domain'], $type);
	}
}