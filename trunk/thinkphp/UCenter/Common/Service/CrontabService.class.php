<?php
/**
 * CrontabService.class.php
 * $author$
 */

namespace Common\Service;
use Com\Validator;
use Org\Net\Snoopy;

class CrontabService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Common/Crontab");
	}

	/**
	 * 检查类型是否正确
	 * @param string $type 类型字串
	 */
	public function check_type($type) {

		return $this->_d->check_type($type);
	}

	/**
	 * 根据域名和签到类型删除计划任务
	 * @param string $domain 域名
	 * @param string $type 任务类型
	 * @return boolean
	 */
	public function del_by_domain_type($domain, $type) {

		return $this->_d->del_by_domain_type($domain, $type);
	}

	/**
	 * 根据域名和签到类型删除计划任务
	 * @param string $taskid 任务id
	 * @param string $domain 域名
	 * @param string $type 任务类型
	 * @return boolean
	 */
	public function del_by_taskid_domain_type($taskid, $domain, $type) {

		return $this->_d->del_by_taskid_domain_type($taskid, $domain, $type);
	}

	/**
	 * 根据taskid/域名/计划任务类型读取任务信息
	 * @param string $taskid 任务(单个任务类型)的唯一标识
	 * @param string $domain 域名
	 * @param string $type 任务类型
	 */
	public function get_by_taskid_domain_type($taskid, $domain, $type) {

		return $this->_d->get_by_taskid_domain_type($taskid, $domain, $type);
	}

	/**
	 * 提取计划任务信息
	 * @param array $task 计划任务信息
	 * @param array $params 输入参数
	 * @return boolean
	 */
	private function __extract_task(&$task, $params) {

		// 任务字段信息
		$fields = array(
			'id' => array('c_id'),
			'taskid' => array('c_taskid'),
			'domain' => array('c_domain'),
			'type' => array('c_type'),
			'method' => array('c_method'),
			'params' => array('c_params', 'array'),
			'ip' => array('c_ip'),
			'runtime' => array('c_runtime', 'int'),
			'endtime' => array('c_endtime', 'int'),
			'looptime' => array('c_looptime', 'int'),
			'times' => array('c_times', 'int'),
			'runs' => array('c_runs', 'int')
		);
		// 提取数据
		$task = array();
		if (!extract_field($task, $fields, $params)) {
			E('_ERR_PARAMS_ERROR');
			return false;
		}

		// 参数检查配置
		$options = array(
			// 域名
			'c_domain' => array(
				'required' => array('rule' => true, 'message' => '_ERR_DOMAIN_IS_EMPTY'),
				'domain' => array('rule' => true, 'message' => '_ERR_DOMAIN_INVALID')
			),
			// 类型
			'c_type' => array(
				'required' => array('rule' => true, 'message' => '_ERR_TYPE_IS_EMPTY')
			),
			// 任务id
			'c_taskid' => array(
				'required' => array('rule' => true, 'message' => '_ERR_TASKID_IS_EMPTY'),
				'md5' => array('rule' => true, 'message' => '_ERR_TASKID_INVALID')
			)
		);
		// 检查
		$errors = array();
		if (!Validator::verify($errors, $task, $options)) {
			E(reset($errors));
			return false;
		}

		// 检查ip
		if (!empty($task['c_ip']) && !Validator::is_ip($task['c_ip'])) {
			E('_ERR_IP_INVALID');
			return false;
		}

		$this->check_method($task['c_method']);
		$this->check_params($task['c_params']);

		return true;
	}

	/**
	 * 检查计划任务参数
	 * @param array $params 请求参数
	 * @return boolean
	 */
	public function check_params(&$params = array()) {

		if (!is_array($params) || empty($params)) {
			$params = array();
		}

		// 序列化
		$params = serialize($params);
		return true;
	}

	/**
	 * 检查请求类型
	 * @param string $method 请求类型
	 * @return boolean
	 */
	public function check_method(&$method = 'GET') {

		$method = rstrtoupper($method);
		if (empty($method) || !in_array($method, array('GET', 'POST'))) {
			$method = 'GET';
		}

		return true;
	}

	/**
	 * 更新计划任务
	 * @param array $params 输入参数
	 */
	public function update_task($params) {

		// 获取任务信息
		$task = array();
		$this->__extract_task($task, $params);
		return $this->_d->update($task['c_id'], $task);
	}

	/**
	 * 新增计划任务
	 * @param array &$task 任务信息
	 * @param array $params 输入参数
	 */
	public function add_task(&$task, $params) {

		// 获取任务信息
		$this->__extract_task($task, $params);
		// 任务信息入库
		return $this->_d->insert($task);
	}

	/**
	 * 执行计划任务
	 * @param int $runtime 执行时间, 小于该值的都需要执行
	 * @param int $limit 每次执行任务个数
	 */
	public function run($runtime, $limit) {

		// 读取计划任务
		$list = $this->_d->list_by_runtime($runtime, $limit);
		// 待删除的计划任务
		$del_ids = array();
		// 遍历所有计划任务
		foreach ($list as $_crontab) {
			// 如果计划任务结束时间小于运行时间
			if (0 < $_crontab['c_endtime'] && $_crontab['c_endtime'] < $_crontab['c_runtime']) {
				$del_ids[] = $_crontab['c_id'];
				continue;
			}

			// 如果运行次数超过了预期
			if (0 < $_crontab['c_times'] && $_crontab['c_runs'] >= $_crontab['c_times']) {
				$del_ids[] = $_crontab['c_id'];
				continue;
			}

			// 反序列化请求参数
			$params = array();
			if (!empty($_crontab['c_params'])) {
				$params = unserialize($_crontab['c_params']);
				// 如果解析失败
				if (FALSE === $params) {
					$params = array();
				}
			}

			// 计划任务执行日志
			\Think\Log::record(cfg('PROTOCAL').$_crontab['c_domain'].'/UcRpc/Rpc/Crontab');
			\Think\Log::record($_crontab['c_type'] . ', ' . var_export($params, true));
			// 执行计划任务
			$client = &\Com\Rpc::phprpc(cfg('PROTOCAL').$_crontab['c_domain'].'/UcRpc/Rpc/Crontab');
			//$client->set_async(true);
			$client->setTimeout(2);
			$client->run(array($_crontab['c_type']), $params);

			// 如果循环周期为 0, 或者执行次数已到要求次数
			if (0 >= $_crontab['c_looptime']
					|| (0 < $_crontab['c_times'] && $_crontab['c_runs'] + 1 >= $_crontab['c_times'])) {
				$del_ids[] = $_crontab['c_id'];
				continue;
			}

			// 更新执行时间以及执行次数
			$this->_d->update_runtime_by_id($_crontab['c_id'], $_crontab['c_looptime']);
		}

		// 删除已完成的计划任务
		if (!empty($del_ids)) {
			$this->_d->delete($del_ids);
		}

		return count($list);
	}

}
