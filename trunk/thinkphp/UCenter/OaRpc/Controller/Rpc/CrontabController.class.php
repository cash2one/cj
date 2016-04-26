<?php
/**
 * CrontabController.class.php
 * $author$
 */

namespace OaRpc\Controller\Rpc;

class CrontabController extends AbstractController {


	public function Index() {

		// do nothing.
	}

	/**
	 * 新增计划任务
	 * @param array $params 任务信息
	 * + taskid 计划任务id(唯一标识)
	 * + domain 完整的域名: test.vchangyi.com
	 * + type 计划任务类型: sign
	 * + ip 服务器IP
	 * + runtime 运行时间戳
	 * + endtime 结束时间
	 * + looptime 间隔时间
	 * + times 需要执行的次数, 0 为不限制
	 * + runs 已运行的次数
	 * @return 返回计划任务信息
	 */
	public function Add($params, $force = false) {

		// 调用 Service 方法, 把计划任务入库
		$serv = D('Common/Crontab', 'Service');
		// 如果不是强制新增数据
		if (!$force) {
			$crontab = $serv->get_by_taskid_domain_type($params['taskid'], $params['domain'], $params['type']);
			if ($crontab) {
				$params['id'] = $crontab['c_id'];
				return $this->Update($params, true);
			}
		}

		// 如果不是强制新增并且有该计划任务
		$task = array();
		return $serv->add_task($task, $params);
	}

	/**
	 * 更新计划任务信息
	 * @param array $params 任务信息
	 * @param string $force
	 * @return \OaRpc\Controller\Rpc\返回计划任务信息
	 */
	public function Update($params, $force = false) {

		$serv = D('Common/Crontab', 'Service');
		// 如果不是强制更新, 则需要先读取
		if (!$force) {
			$crontab = $serv->get_by_taskid_domain_type($params['taskid'], $params['domain'], $params['type']);
			// 如果记录不存在, 则新增
			if (empty($crontab)) {
				return $this->Add($params, true);
			} else {
				$params['id'] = $crontab['c_id'];
			}
		}

		return $serv->update_task($params);
	}

	/**
	 * 根据域名和签到类型删除计划任务
	 * @param string $domain 域名
	 * @param string $type 任务类型
	 * @return boolean
	 */
	public function Del_by_domain_type($domain, $type) {

		$serv = D('Common/Crontab', 'Service');
		return $serv->del_by_domain_type($domain, $type);
	}

	/**
	 * 根据taskid和域名删除计划任务
	 * @param int $taskid 任务id
	 * @param string $domain 域名
	 * @return boolean
	 */
	public function Del_by_taskid_domain_type($taskid, $domain, $type) {

		$serv = D('Common/Crontab', 'Service');
		return $serv->del_by_taskid_domain_type($taskid, $domain, $type);
	}

}
