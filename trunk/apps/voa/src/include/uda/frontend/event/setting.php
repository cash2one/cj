<?php
/**
 * Created by PhpStorm.
 * User: xbs
 * Date: 16/2/25
 * Time: 14:31
 */

class voa_uda_frontend_event_setting extends voa_uda_frontend_event_base {


	protected $_setting = array();
	private $__request;

	public function __construct() {

		parent::__construct();
		if ($this->_serv == null) {
			$this->_serv = new voa_s_oa_event_setting();
		}
		$this->_setting = voa_h_cache::get_instance()->get('setting', 'oa');
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
			),
			'crontab_date' => array(
				'crontab_date', parent::VAR_INT,
				null,
				null, false
			),
			'crontab_time' => array(
				'crontab_time', parent::VAR_INT,
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
		$work_date = !empty($this->__request['crontab_date']) ? $this->__request['crontab_date'] : 1; //提前几天提醒
		$work_time = !empty($this->__request['crontab_time']) ? $this->__request['crontab_time'] : 10;//在什么时候提醒
		$data = array(
			'crontab' => $crontab,
			'crontab_date' => $work_date,
			'crontab_time' => $work_time
		);

		if(!$this->_serv->update_settings($data)) {
			$this->errmsg(10001, '保存失败请稍后再试');
			return false;
		}
		$work_begin = rstrtotime(rgmdate(strtotime('+1 day'), 'Y-m-d')) + 60*60*$work_time;
		$params = $work_date+1;//提前的天数
		$origin_str = implode(',', array($this->_setting['domain'], 'eventRemind'));
		if($crontab == 1) {
			//添加计划任务
			if(isset($this->_sets['crontab'])) {
				$this->__update_task(md5($origin_str), $work_begin, 'eventRemind', $params);
			} else {
				$this->__add_task(md5($origin_str), $work_begin, 'eventRemind', $params);
			}

		}else{
			//删除计划任务
			$this->__del_task($origin_str, 'eventRemind');
		}

		voa_h_cache::get_instance()->get('plugin.event.setting', 'oa', true);

		return true;
	}

	/**
	 * 新增计划任务
	 * @param int $taskid 计划任务ID
	 * @param string $runtime 执行时间点
	 * @param string $type 任务类型
	 */
	public function __add_task($taskid, $runtime= '', $type, $params) {

		$rpc_crontab = voa_h_rpc::phprpc(config::get('voa.uc_url') . 'OaRpc/Rpc/Crontab');

		//执行时间
		$ts = $runtime;

		// 添加计划任务
		return $rpc_crontab->Add(array(
			'taskid' => $taskid,
			'domain' => $this->_setting['domain'],
			'type' => $type,
			'params' => $params,
			'ip' => '',
			'runtime' => $ts,
			'endtime' => 0,
			'looptime' => 86400,
			'times' => 0,
			'runs' => 0
		));
	}

	/**
	 * 新增计划任务
	 * @param int $taskid 计划任务ID
	 * @param string $runtime 执行时间点
	 * @param string $type 任务类型
	 */
	public function __update_task($taskid, $runtime= '', $type, $params) {

		$rpc_crontab = voa_h_rpc::phprpc(config::get('voa.uc_url') . 'OaRpc/Rpc/Crontab');

		//执行时间
		$ts = $runtime;

		// 更新计划任务
		return $rpc_crontab->Update(array(
			'taskid' => $taskid,
			'domain' => $this->_setting['domain'],
			'type' => $type,
			'params' => $params,
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

		$rpc_crontab = voa_h_rpc::phprpc(config::get('voa.uc_url') . 'OaRpc/Rpc/Crontab');
		return $rpc_crontab->Del_by_taskid_domain_type($taskid, $this->_setting['domain'], $type);
	}
}
