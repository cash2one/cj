<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/3/21
 * Time: 下午4:08
 */
namespace Questionnaire\Service;

use Questionnaire\Model\QuestionnaireModel;
use Common\Common\WxqyMsg;
use Think\Log;

class QuestionnaireService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Questionnaire/Questionnaire");
	}

	/**
	 * 遍历数组并把Html实体转为字符
	 * @param mixed $data
	 */
	public static function html_decode(&$data) {
		if (is_array($data)) {
			foreach ($data as $k => $v) {
				self::html_decode($data[$k]);
			}
		}
		else {
			$data = html_entity_decode($data);
		}
	}

	/**
	 * 获取问卷数据
	 * @param int $qu_id 问卷ID
	 * @return mixed
	 */
	public function getData($qu_id) {

		$data = $this->_d->get_by_conds(['qu_id' => $qu_id, 'release_status <' => QuestionnaireModel::RELEASE_STATUS]);

		if ($data) {
			foreach ($data as $k => $v) {
				if (is_numeric($v)) {
					$data[$k] = intval($v);
				}
			}

			$data['title'] = html_entity_decode($data['title']);
			$data['deadline_date'] = rgmdate($data['deadline'], 'Y-m-d');
			$data['deadline_time'] = rgmdate($data['deadline'], 'G:i');

			if ($data['release'] > 0) {
				$data['release_date'] = rgmdate($data['release'], 'Y-m-d');
				$data['release_time'] = rgmdate($data['release'], 'G:i');
			}

			$data['field'] = json_decode($data['field'], true);
			self::html_decode($data['field']);

			foreach ($data['field'] as $k => $field) {
				if (isset($field['option'])) {
					$field['otheroption'] = array_pop($field['option']);

					if (isset($field['otheroption']['other'])) {
						$data['field'][$k] = $field;
					}
				}
			}

			unset($data['status']);
			unset($data['created']);
			unset($data['updated']);
			unset($data['deleted']);
		}

		return $data;
	}

	/**
	 * 保存问卷数据
	 * @param mixed $data 问卷数据
	 * @return mixed
	 */
	public function saveData($data) {

		$qu_id = (isset($data['qu_id']) && is_numeric($data['qu_id'])) ? $data['qu_id'] : 0;

		if (isset($data['field'])) {
			$data['field'] = json_encode($data['field']);
		}

		if (isset($data['release_status']) && $data['release_status'] != 2 && isset($data['release'])) {
			$data['release_time'] = $data['release'] == 0 ? time() : $data['release'];
		}

		if ($qu_id > 0) {
			$count = $this->_d->count_by_conds(['qu_id' => $qu_id, 'release_status <' => QuestionnaireModel::RELEASE_STATUS]);

			if ($count == 1) {
				$this->_d->update_by_conds(array('qu_id' => $qu_id), $data);

				return $qu_id;
			}

			return false;
		}
		else {
			return $this->_d->insert($data);
		}
	}

	/**
	 * 发送问卷发布提醒
	 * @param array $q 问卷数据
	 * @param array $uid 可见范围人员ID
	 */
	public function sendReleaseMsg($q, $uid) {
		$cache = &\Common\Common\Cache::instance();
		$setting = $cache->get('Common.setting');
		$pluginModel = D('Common/CommonPlugin');
		$plugin = $pluginModel->get_by_identifier('questionnaire');

		$deadline = rgmdate($q['deadline'], 'Y-m-d H:i');
		$desc = "您有一个问卷需要填写，请于{$deadline}前填写完毕";
		$url = cfg('PROTOCAL') . $setting['domain'] . '/newh5/questionnaire/index.html?#/app/page/questionnaire/questionnaire-form?qu_id=' . $q['qu_id'];

		WxqyMsg::instance()->send_news($q['title'], $desc, $url, $uid, '', '', $plugin['cp_agentid'], $plugin['cp_pluginid']);
		Log::record("发送问卷发布提醒，qu_id：{$q['qu_id']}", Log::INFO);
	}

	/**
	 * 定时发布问卷
	 * @param array $q 问卷数据
	 */
	public function timeRelease($q) {
		$cache = &\Common\Common\Cache::instance();
		$setting = $cache->get('Common.setting');
		$task_id = md5("{$setting['domain']}_questionnaire_timerelease_{$q['qu_id']}");
		$client = &\Com\Rpc::phprpc(cfg('UCENTER_RPC_HOST') . '/OaRpc/Rpc/Crontab');

		$result = $client->Add([
			'taskid' => $task_id,
			'domain' => $setting['domain'],
			'type' => 'questionnaire',
			'params' => [
				'type' => 'crontab',
				'qu_id' => $q['qu_id'],
			],
			'runtime' => $q['release'],
			'endtime' => 0,
			'times' => 1,
		]);

		Log::record("计划任务-定时发布问卷，qu_id：{$q['qu_id']}，result：{$result}", Log::INFO);
	}

	/**
	 * 问卷结束提醒
	 * @param array $q 问卷数据
	 */
	public function endRemind($q) {
		$cache = &\Common\Common\Cache::instance();
		$setting = $cache->get('Common.setting');
		$task_id = md5("{$setting['domain']}_questionnaire_endremind_{$q['qu_id']}");
		$client = &\Com\Rpc::phprpc(cfg('UCENTER_RPC_HOST') . '/OaRpc/Rpc/Crontab');

		$result = $client->Add([
			'taskid' => $task_id,
			'domain' => $setting['domain'],
			'type' => 'questionnaire',
			'params' => [
				'type' => 'terminate',
				'qu_id' => $q['qu_id'],
			],
			'runtime' => $q['deadline'] - $q['remind'] * 60,
			'endtime' => 0,
			'times' => 1,
		]);

		Log::record("计划任务-问卷结束提醒，qu_id：{$q['qu_id']}，result：{$result}", Log::INFO);
	}

	/**
	 * 微信端列表接口
	 * @param array $request      条件
	 * @param array $page_option  页数/条数
	 * @param array $order_option 排序
	 */
	public function questionnaireList(array $request, $page_option = array(), $order_option = array()) {

		return $this->_d->list_by_condition($request, $page_option, $order_option);

	}

	/**
	 * 统计列表总数
	 * @param array $request 条件
	 * @return mixed
	 */
	public function questionnaireListTotal(array $request) {

		return $this->_d->total_by_condition($request);
	}

	/**
	 * 前端列表格式化
	 * @param array $request
	 * @param array $result
	 * @return bool
	 */
	public function formatQuesrtionnaireList(array $request, array &$result = array()) {

		$serv_my = D('Questionnaire/QuestionnaireRecord');
		if ($request['list']) {
			foreach ($request['list'] as $key => $val) {
				if(empty($val['title'])){
					continue;
				}
				if($val['is_all'] != QuestionnaireModel::IS_ALL && !$this->_is_show($val['qu_id'], $request['uid'])){
					//continue;
				}
				$result[$key]['qu_id']     = $val['qu_id'];
				$result[$key]['title']     = htmlspecialchars_decode($val['title']);
				$result[$key]['deadline']  = (int)$val['deadline'];
				$result[$key]['anonymous'] = (int)$val['anonymous'];
				$result[$key]['repeat']    = (int)$val['repeat'];
				$data                      = array(
					'uid'   => $request['m_uid'],
					'qu_id' => $val['qu_id']
				);
				$isJoin                    = $serv_my->get_by_conds($data);
				$result[$key]['is_join']   = $isJoin ? 1 : 2;
			}
		}

		return true;
	}

	/**
	 * 根据问卷ID获取可见范围人员ID列表
	 * @param $qu_id
	 * @return array|bool
	 */
	protected function _is_show($qu_id, $uid) {

		$model_viewrange = D('Questionnaire/QuestionnaireViewrange');
		$conds      = array('qu_id' => $qu_id);
		$view_rande = $model_viewrange->list_by_conds($conds);
		if (empty($view_rande)) {
			return false;
		}
		$range_uid   = array();
		$range_cdid  = array();
		$range_label = array();
		// 获取范围
		foreach ($view_rande as $_range) {
			if (!empty($_range['view_range_uid'])) {
				$range_uid[] = $_range['view_range_uid'];
				continue;
			}
			if (!empty($_range['view_range_cdid'])) {
				$range_cdid[] = $_range['view_range_cdid'];
				continue;
			}
			if (!empty($_range['view_range_label'])) {
				$range_label[] = $_range['view_range_label'];
				continue;
			}
		}
		$uid_list = array();
		if (!empty($range_cdid)) {
			$range_cdid = array_unique($range_cdid);
			// 查询部门下的所有子部门
			$dep_list = \Common\Common\Department::instance()->list_childrens_by_cdid($range_cdid, true);
			// 获取部门下的人员
			if (!empty($dep_list)) {
				$model_dep_mem = D('Common/MemberDepartment');
				$temp_dep_mem  = $model_dep_mem->list_by_conds(array('cd_id' => $dep_list));
				$dep_mem       = array_unique(array_column($temp_dep_mem, 'm_uid'));
				$uid_list      = array_merge($range_uid, $dep_mem);
			}
		}
		// 获取标签下的人员
		if (!empty($range_label)) {
			$range_label = array_unique($range_label);
			// 查询标签下的人员
			$model_label_mem = D('Common/CommonLabelMember');
			$temp_label_mem  = $model_label_mem->list_by_conds(array('laid' => $range_label));
			$label_mem       = array_column($temp_label_mem, 'm_uid');
			$uid_list        = array_unique(array_merge($label_mem, $uid_list));
		}

		if(!in_array($uid_list, $uid)) {
			return false;
		}

		return true;
	}

	/**
	 * 后端列表接口
	 * @param array $request      条件
	 * @param array $page_option  页数/条数
	 * @param array $order_option 排序
	 */
	public function questionnaireBackendList(array $request, $page_option = array(), $order_option = array()) {

		if(isset($request['status']) && $request['status']){
			switch($request['status']) {
				//进行时
				case '3':
					if(isset($request['start']) && $request['start']){
						if(rstrtotime($request['start']) < time()){
							$request['start'] = rgmdate(time(),'Y-m-d H:i:s');
						}
					} else {
						$request['start'] = rgmdate(time(),'Y-m-d H:i:s');
					}
					$request['status'] = 3;
					break;
				case '4'://结束了
					if(isset($request['end']) && $request['end']){
						if(rstrtotime($request['end']) < time()){
							$request['end_j'] = rgmdate(time(),'Y-m-d H:i:s');
						}
					} else {
						$request['end_j'] = rgmdate(time(),'Y-m-d H:i:s');
					}
					$request['status'] = 3;
					break;
			}
		}
		return $this->_d->list_by_conditionBackend($request, $page_option, $order_option);

	}

	/**
	 * 后端统计列表总数
	 * @param array $request 条件
	 * @return mixed
	 */
	public function questionnaireBackendListTotal(array $request) {

		if(isset($request['status']) && $request['status']){
			switch($request['status']) {
				//进行时
				case '3':
					if(isset($request['start']) && $request['start']){
						if(rstrtotime($request['start']) < time()){
							$request['start'] = rgmdate(time(),'Y-m-d H:i:s');
						}
					} else {
						$request['start'] = rgmdate(time(),'Y-m-d H:i:s');
					}
					$request['status'] = 3;
					break;
				case '4'://结束了
					if(isset($request['end']) && $request['end']){
						if(rstrtotime($request['end']) < time()){
							$request['end'] = rgmdate(time(),'Y-m-d H:i:s');
						}
					} else {
						$request['end'] = rgmdate(time(),'Y-m-d H:i:s');
					}
					$request['status'] = 3;
					break;
			}
		}

		return $this->_d->total_by_conditionBackend($request);
	}

	/**
	 * 后端列表格式化
	 * @param array $request
	 * @param array $result
	 * @return bool
	 */
	public function formatQuesrtionnaireBackendList(array $request, array &$result = array()) {

		if ($request) {
			$serv_f   = D('Questionnaire/QuestionnaireClassify');
			$cid      = array_column($request, 'qc_id');
			$classify = $serv_f->list_by_pks($cid);
			foreach ($classify as $_val) {
				$classifydata[$_val['qc_id']] = $_val;
			}
			foreach ($request as $key => $val) {
				$result[$key]['qu_id']     = $val['qu_id'];
				$result[$key]['title']     = $val['title'];
				$result[$key]['classname'] = $classifydata[$val['qc_id']]['name'];
				$result[$key]['status']    = $this->__formatStatus($val);
				$result[$key]['created']   = '-';
				if($val['release_time']){
					$result[$key]['created']   = rgmdate($val['release_time'], 'Y-m-d H:i:s');
				}
				$result[$key]['deadline']  = '-';
				if($val['deadline']){
					$result[$key]['deadline']  = rgmdate($val['deadline'], 'Y-m-d H:i:s');
				}
				$result[$key]['total']     = $this->__formatJoinTotal($val['qu_id']);
			}
		}

		return true;
	}

	/**
	 * 状态
	 * @param $result
	 * @return string
	 */
	private function __formatStatus($result) {

		if ($result['release_status'] == QuestionnaireModel::DRATE_STATUS) {
			$status = '草稿';

			return $status;
		}
		if ($result['deadline'] > time()) {
			$status = '进行中';
			if ($result['release'] != 0 && $result['release'] > time()) {
				$status = '预发布';
			}

			return $status;
		}
		$status = '已结束';

		return $status;
	}

	/**
	 * 报名人数
	 * @param $result
	 */
	private function __formatJoinTotal($result) {

		$serv_r = D('Questionnaire/QuestionnaireRecord');
		//封装数据
		$data = array(
			'qu_id' => $result
		);

		return $serv_r->count_by_conds($data);
	}

}