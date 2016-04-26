<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 15/11/5
 * Time: 下午2:04
 */
namespace Askfor\Controller\Api;

use Askfor\Model\AskforModel;
use Askfor\Model\AskforProcModel;
use Askfor\Model\AskforProcRecordModel;
use Askfor\Service\AskforProcService;
use Common\Common\Department;

class AskforController extends AbstractController {

	const ASKFOR_UNTREATED = 1; // 未处理
	const ASKFOR_PROCESSED = 2; // 已处理
	const ASKFOR_COPY = 3; // 抄送

	const ACTIVE = 1; // 达到状态
	const UNACTIVE = 0; // 没有达到状态

	/**
	 * 审批详情
	 * @return bool
	 */
	public function View_get() {

		$get = I('get.');

		// 判断参数
		if (empty($get['af_id'])) {
			E('_ERR_MISS_PARAMETER_AFID');

			return false;
		}

		// 实例化
		$this->_serv_askfor = D('Askfor/Askfor', 'Service');
		$this->_serv_proc = D('Askfor/AskforProc', 'Service');
		$this->_serv_custom = D('Askfor/AskforCustomdata', 'Service');
		$serv_mem = D('Common/Member', 'Service');
		$serv_proc_record = D('Askfor/AskforProcRecord', 'Service');

		// 判断权限
		if (!$this->_is_privileges($this->_login->user['m_uid'], $get['af_id'], 'viewer', $af_data, $proc_data, $identity)) {
			E('_ERR_NO_AUTHORITY');

			return false;
		}

		// 获取自定义数据
		$customdata = $this->_serv_custom->list_by_conds(array('af_id' => $get['af_id']));

		// 抄送人
		$copy_data = array();
		// 取出抄送人
		foreach ($proc_data as $k => $v) {
			if ($v['afp_condition'] == AskforProcModel::COPYASK) {
				$copy_data[] = $proc_data[$k];
				unset($proc_data[$k]);
			}
		}

		// 审批详情
		$approver_data = array();
		// 审批详情 按等级分
		$inarray = array(
			AskforProcModel::ASKING,
			AskforProcModel::ASKPASS,
			AskforProcModel::TURNASK,
			AskforProcModel::ASKFAIL,
		);
		foreach ($proc_data as $k => $v) {
			// 剔除 除了审批的其他 状态
			if (!in_array($v['afp_condition'], $inarray)) {
				continue;
			}
			// 如果进度表不为空 并且 模板id 不为零 是固定流程
			if (!empty($proc_data) && $af_data['aft_id'] != 0) {
				// 按等级分的数组
				$approver_data[$v['afp_level']][] = $proc_data[$k];
			} else {
				// 自由流程
				$approver_data[] = $proc_data[$k];
			}
		}

		// 排序
		if (!empty($proc_data) && $af_data['aft_id'] != 0) {
			ksort($approver_data);
			$approver_data = array_values($approver_data);
		}


		// 取出操作记录
		$temp = $serv_proc_record->list_by_conds(array('af_id' => $get['af_id']), null, array('rafp_id' => 'DESC'));
		$had_operat = array();
		$mine_operation = array();
		if (!empty($temp)) {
			$proc_uids = array_unique(array_column($temp, 'm_uid'));
			$mem_data = $serv_mem->list_by_conds(array('m_uid' => $proc_uids));
			// 匹配人员头像
			foreach ($temp as &$_record) {
				foreach ($mem_data as $_data) {
					if ($_data['m_uid'] == $_record['m_uid']) {
						$_record['m_face'] = $_data['m_face'];
						break;
					}
				}
				// 更名
				$_record['afp_created'] = $_record['rafp_created'];
				$_record['afp_condition'] = $_record['rafp_condition'];
			}
			$had_operat = $temp;
		}

		// 获取登陆人的操作
		$mine_operation = array();
		foreach ($proc_data as $k => $v) {
			if ($v['m_uid'] == $this->_login->user['m_uid']) {
				$mine_operation = $proc_data[$k];
				break;
			}
		}

		// 获取图片
		$serv_att = D('Askfor/AskforAttachment', 'Service');
		$atts = $serv_att->list_by_conds(array('af_id' => $get['af_id']));
		$atts_url = array();
		if (!empty($atts)) {
			foreach ($atts as $at_id) {
				$atts_url[] = cfg('PROTOCAL') . $this->_setting['domain'] . '/attachment/read/' . $at_id['at_id'];
			}
		}

		$this->_result = array(
			'af_data' => $af_data, // 审批数据
			'afp_data' => $had_operat, // 审批流程
			'mine_operation' => $mine_operation, // 当前查看人的操作记录
			'afp_copy' => $copy_data, // 抄送人
			'approver_data' => $approver_data, // 审批人
			'customdata' => $customdata, // 自定义字段数据
			'identity' => $identity, // 当前用户身份
			'images' => $atts_url, // 图片
		);

		return true;
	}

	/**
	 * 同意
	 * @return bool
	 */
	public function Agree_post() {

		$post = I('post.');
		$this->_serv_askfor = D('Askfor/Askfor', 'Service');
		$this->_serv_proc = D('Askfor/AskforProc', 'Service');

		// 判断参数
		if (empty($post['af_id'])) {
			E('_ERR_MISS_PARAMETER_AFID');

			return false;
		}

		// 权限判断
		if (!$this->_is_privileges($this->_login->user['m_uid'], $post['af_id'], 'approver', $af_data, $af_proc)) {
			E('_ERR_NO_AUTHORITY');

			return false;
		}

		// 固定流程 / 自由流程
		$active = array(); // 审批中的人
		if (isset($af_data['aft_id']) && $af_data['aft_id'] != 0) {
			// 获取当前操作人 审批记录
			foreach ($af_proc as $k => $v) {
				if ($v['m_uid'] == $this->_login->user['m_uid']) {
					$this->_operator_proc = $af_proc[$k];
					break;
				}
			}
			// 没有到操作人
			if ($this->_operator_proc['is_active'] == self::UNACTIVE) {
				E('_ERR_UNACTIVE');

				return false;
			}
			// 更新操作人审批状态
			$this->_serv_proc->update_afp_condition($this->_login->user['m_uid'], $post['af_id'], AskforProcModel::ASKPASS, $post['mark']);
			// 操作记录
			$proc_record_data = array(
				'af_id' => $post['af_id'],
				'm_uid' => $this->_login->user['m_uid'],
				'm_username' => $this->_login->user['m_username'],
				'rafp_note' => empty($post['mark']) ? '' : $post['mark'],
				'rafp_condition' => AskforProcRecordModel::ASKPASS,
			);
			$this->_proc_record($proc_record_data);
			// 获取同级
			$all_agree = true;
			foreach ($af_proc as $k => $v) {
				if ($v['afp_level'] == $this->_operator_proc['afp_level'] && $v['m_uid'] != $this->_operator_proc['m_uid']) { // 排除操作人
					// 判断是否同意
					if ($v['afp_condition'] != AskforProcModel::ASKPASS) {
						$all_agree = false;
					}
				}
			}
			// 如果都同意
			if ($all_agree) {
				// 当前等级
				$this->_level = $this->_operator_proc['afp_level'];
				// 检查是否有下级
				$this->_next_level($af_data, $af_proc, $post['af_id'], true);
			} else {
				// 更改审批状态
				$this->_serv_askfor->update_by_conds(array('af_id' => $post['af_id']), array('af_condition' => AskforProcModel::ASKING));
			}
			// 发送消息给 发起人 $af_data['m_uid'] 当前审批人同意审批
			$data['af_id'] = $post['af_id'];
			$data['aft_id'] = $af_data['aft_id'];
			$data['title'] = $this->_login->user['m_username'] . '已经同意审批';
			$data['content'] = "审批主题：" . $af_data['af_subject'] . "\n审批人:" . $this->_login->user['m_username'] . "\n备注:" . $post['mark'];
			$this->send_msg($data, $af_data['m_uid']);
		} else {
			// 更新操作人审批状态
			$this->_serv_proc->update_afp_condition($this->_login->user['m_uid'], $post['af_id'], AskforProcModel::ASKPASS, $post['mark']);
			// 操作记录
			$proc_record_data = array(
				'af_id' => $post['af_id'],
				'm_uid' => $this->_login->user['m_uid'],
				'm_username' => $this->_login->user['m_username'],
				'rafp_note' => empty($post['mark']) ? '' : $post['mark'],
				'rafp_condition' => AskforProcRecordModel::ASKPASS,
			);
			$this->_proc_record($proc_record_data);
			// 获取 审批中的审批人
			foreach ($af_proc as $k => $v) {
				if ($v['afp_condition'] == AskforProcModel::ASKING && $v['m_uid'] != $this->_login->user['m_uid']) {
					$active[] = $af_proc[$k];
				}
			}
			// 如果都同意
			if (empty($active)) {
				// 给发起人,抄送人发送结束消息
				$data['af_id'] = $post['af_id'];
				$data['title'] = $af_data['af_subject'] . '审批已通过';
				$data['content'] = "审批主题：" . $af_data['af_subject'];
				// 取出抄送人
				$message_to = array();
				foreach ($af_proc as $_proc_data) {
					if ($_proc_data['afp_condition'] == AskforProcModel::COPYASK || $_proc_data['afp_condition'] == AskforProcModel::TURNASK || $_proc_data['afp_condition'] == AskforProcModel::ASKPASS) {
						$message_to[] = $_proc_data['m_uid'];
					}
				}
				$message_to[] = $af_data['m_uid']; // 发起人
				$this->send_msg($data, $message_to);
				// 更改审批状态为通过
				$this->_serv_askfor->update_by_conds(array('af_id' => $post['af_id']), array('af_condition' => AskforProcModel::ASKPASS));
			} else {
				// 给发起人发送消息, 当前审批人已同意 $af_data['m_uid']
				$data['af_id'] = $post['af_id'];
				$data['title'] = $this->_login->user['m_username'] . '已经同意审批';
				$data['content'] = "审批主题：" . $af_data['af_subject'] . "\n审批人:" . $this->_login->user['m_username'] . "\n备注:" . $post['mark'];
				$this->send_msg($data, $af_data['m_uid']);
			}

		}

		return true;
	}

	/**
	 * 驳回
	 * @return bool
	 */
	public function Veto_post() {

		$post = I('post.');

		// 判断参数
		if (empty($post['af_id'])) {
			E('_ERR_MISS_PARAMETER_AFID');

			return false;
		}
		if (empty($post['mark'])) {
			E('_ERR_MISS_MARK');

			return false;
		}

		// 实例化
		$this->_serv_askfor = D('Askfor/Askfor', 'Service');
		$this->_serv_proc = D('Askfor/AskforProc', 'Service');

		// 权限判断
		if (!$this->_is_privileges($this->_login->user['m_uid'], $post['af_id'], 'approver', $af_data, $af_proc)) {
			E('_ERR_NO_AUTHORITY');

			return false;
		}
		// 如果是固定流程 判断当前人物是否可以操作
		if (isset($af_data['aft_id']) && $af_data['aft_id'] != 0) {
			// 获取当前操作人 审批记录
			foreach ($af_proc as $k => $v) {
				if ($v['m_uid'] == $this->_login->user['m_uid']) {
					$operator_proc = $af_proc[$k];
					break;
				}
			}
			// 没有到操作人
			if (!empty($operator_proc) && $operator_proc['is_active'] == self::UNACTIVE) {
				E('_ERR_UNACTIVE');

				return false;
			}
		}

		// 更新审批人 审批状态
		$this->_serv_proc->update_afp_condition($this->_login->user['m_uid'], $post['af_id'], AskforProcModel::ASKFAIL, $post['mark']);
		// 操作记录
		$proc_record_data = array(
			'af_id' => $post['af_id'],
			'm_uid' => $this->_login->user['m_uid'],
			'm_username' => $this->_login->user['m_username'],
			'rafp_note' => empty($post['mark']) ? '' : $post['mark'],
			'rafp_condition' => AskforProcRecordModel::ASKFAIL
		);
		$this->_proc_record($proc_record_data);
		// 更新审批状态
		$this->_serv_askfor->update_by_conds(array('af_id' => $post['af_id']), array('af_condition' => AskforProcModel::ASKFAIL));

		// 固定流程 else 自由流程
		if (isset($af_data['aft_id']) && $af_data['aft_id'] != 0) {

			$message_to = $this->__get_fixed_message_to($post, $af_data);

			// 发送消息 $message_to
			$data['af_id'] = $post['af_id'];
			$data['aft_id'] = $af_data['aft_id'];
			$data['title'] = $this->_login->user['m_username'] . '驳回审批';
			$data['content'] = "审批主题：" . $af_data['af_subject'] . "\n审批人:" . $this->_login->user['m_username'] . "\n驳回备注:" . $post['mark'];
			$this->send_msg($data, $message_to);
		} else {

			$message_to = $this->__get_free_message_to($post, $af_data);

			// 发送消息 $message_to
			$data['af_id'] = $post['af_id'];
			$data['title'] = $this->_login->user['m_username'] . '驳回审批';
			$data['content'] = "审批主题：" . $af_data['af_subject'] . "\n审批人:" . $this->_login->user['m_username'] . "\n驳回备注:" . $post['mark'];
			$this->send_msg($data, $message_to);
		}

		return true;
	}

	/**
	 * 获取驳回 固定 流程里 要发送消息的目标
	 * @param array $post 提交的数据
	 * @param array $af_data 审批主题数据
	 * @return array
	 */
	private function __get_fixed_message_to($post, $af_data) {

		// 获取 操作过 审批中 抄送 人
		$conds = array(
			'af_id' => $post['af_id'],
			'afp_condition' => array(AskforProcModel::ASKPASS, AskforProcModel::ASKING, AskforProcModel::COPYASK),
		);
		$is_operation = $this->_serv_proc->list_by_conds($conds);

		$message_to = array_column($is_operation, 'm_uid');
		$message_to = array_unique($message_to);
		// 合并 发起人
		$message_to = array_merge($message_to, array($af_data['m_uid']));
		// 剔除操作人
		$message_to = array_diff($message_to, array($this->_login->user['m_uid']));

		return $message_to;
	}

	/**
	 * 获取驳回 自由 流程里 要发送消息的目标
	 * @param array $post 提交的数据
	 * @param array $af_data 审批主题数据
	 * @return mixed
	 */
	private function __get_free_message_to($post, $af_data) {

		$conds = array(
			'af_id' => $post['af_id'],
		);
		$message_to = $this->_serv_proc->list_by_conds($conds);

		// 合并 发起人
		$message_to = array_merge($message_to, array($af_data));
		// 提取 uid 去重
		$message_to = array_unique(array_column($message_to, 'm_uid'));
		// 剔除操作人
		$message_to = array_diff($message_to, array($this->_login->user['m_uid']));

		return $message_to;
	}

	/**
	 * 收到的审批
	 * @return bool
	 */
	public function Ask_get() {

		$get = I('get.');
		// 判断是否有参数
		if (empty($get['type'])) {
			E('_ERR_MISS_PARAMETER_TYPE');

			return false;
		}

		$this->_serv_askfor = D('Askfor/Askfor', 'Service');
		$this->_serv_proc = D('Askfor/AskforProc', 'Service');

		// 审批进度条件
		$askfor_condition = array();
		$afp_condition = array();
		$is_active = '';
		switch ($get['type']) {
			case self::ASKFOR_UNTREATED: // 未处理
				$afp_condition = array(AskforProcModel::ASKING);
				$is_active = self::ACTIVE;
				// askfor表条件
				$askfor_condition = array(AskforModel::ASKING, AskforModel::TURNASK, AskforModel::PRESSASK);

				break;
			case self::ASKFOR_PROCESSED: // 已处理
				$afp_condition = array(
					AskforProcModel::ASKPASS,
					AskforProcModel::TURNASK,
					AskforProcModel::ASKFAIL,
				);

				break;
			case self::ASKFOR_COPY: // 抄送
				$afp_condition = array(AskforProcModel::COPYASK);

				break;
		}

		// askfor表条件
		if ($get['type'] != self::ASKFOR_UNTREATED) {
			$askfor_condition = array(
				AskforModel::ASKING,
				AskforModel::ASKPASS,
				AskforModel::TURNASK,
				AskforModel::ASKFAIL,
				AskforModel::DRAFT,
				AskforModel::PRESSASK,
			);
		}
		$afp_condition = implode(',', $afp_condition);
		$askfor_condition = implode(',', $askfor_condition);

		// 分页参数
		$af['limit'] = empty($get['limit']) ? 10 : $get['limit'];
		$af['page'] = empty($get['page']) ? 1 : $get['page'];
		list($start, $limit, $page) = page_limit($get['page'], $af['limit']);
		$page_option = array('start' => $start,'limit' => $limit);

		$af['result'] = $this->_serv_askfor->left_join_proc($this->_login->user['m_uid'], $afp_condition, $is_active, $page_option, $askfor_condition);
		$af['count'] = $this->_serv_askfor->count_left_join_proc($this->_login->user['m_uid'], $afp_condition, $is_active, $askfor_condition);
		$af['page'] = $page;

		$this->_result = $af;

		return true;
	}

	/**
	 * 转审批
	 * @return bool
	 */
	public function Turnask_post() {

		$post = I('post.');

		// 判断参数
		if (empty($post['af_id'])) {
			E('_ERR_MISS_PARAMETER_AFID');

			return false;
		}
		if (empty($post['re_m_uid'])) {
			E('_ERR_MISS_PARAMETER_RE_UID');

			return false;
		}

		// 转审批人只能有一人
		if (is_array($post['re_m_uid'])) {
			E('_ERR_TRUN_APPROVER_ONLY_ONE');

			return false;
		}

		// 实例化
		$this->_serv_askfor = D('Askfor/Askfor', 'Service');
		$this->_serv_proc = D('Askfor/AskforProc', 'Service');

		// 权限判断
		$is_auth = $this->_is_privileges($this->_login->user['m_uid'], $post['af_id'], 'approver', $af_data, $af_proc);
		// 判断是否固定流程 固定流程有模板
		if (isset($af_data['aft_id']) && $af_data['aft_id'] != 0) {
			E('_ERR_IS_FIXED_NOTRUN');

			return false;
		}
		// 判断权限
		if (!$is_auth) {
			E('_ERR_NO_AUTHORITY');

			return false;
		}

		// 判断是否存在此人
		$serv_mem = D('Common/Member', 'Service');
		$re_m_data = $serv_mem->get_by_conds(array('m_uid' => $post['re_m_uid']));
		if (empty($re_m_data)) {
			E('_ERR_MISS_MEMBER');

			return false;
		}

		// 判断是不是已经是审批人 获取是发起人
		foreach ($af_proc as $k => $v) {
			if ($post['re_m_uid'] == $v['m_uid']) {
				E('_ERR_IS_APPROVER_NOW');

				return false;
			}
			if ($post['re_m_uid'] == $af_data['m_uid']) {
				E('_ERR_RETURN_IS_PROMOTER');

				return false;
			}
		}

		// 去掉原有 抄送人 里的 转审批人(可能有)
		$this->_serv_proc->delete_copy_by_reuid($post['re_m_uid'], $post['af_id']);

		// 转审批人 入库
		$this->_new_approver_insert($post, $af_proc);

		// 更新操作人的审批状态
		$update_conds = array('m_uid' => $this->_login->user['m_uid'], 'af_id' => $post['af_id']);
		$update_data = array('re_m_username' => $re_m_data['m_username'], 're_m_uid' => $re_m_data['m_uid'], 'afp_condition' => AskforProcModel::TURNASK, 'afp_note' => $post['mark']);
		$this->_serv_proc->update_by_conds($update_conds, $update_data);
		// 操作记录
		$proc_record_data = array(
			'af_id' => $post['af_id'],
			'm_uid' => $this->_login->user['m_uid'],
			'm_username' => $this->_login->user['m_username'],
			'rafp_note' => empty($post['mark']) ? '' : $post['mark'],
			'rafp_condition' => AskforProcRecordModel::TURNASK,
			're_m_uid' => $re_m_data['m_uid'],
			're_m_username' => $re_m_data['m_username'],
		);
		$this->_proc_record($proc_record_data);

		//	发送消息给发起人 $af_data['m_uid']
		$data['af_id'] = $post['af_id'];
		$data['title'] = $this->_login->user['m_username'] . '转审给' . $re_m_data['m_username'];
		$data['content'] = "审批主题：" . $af_data['af_subject'];
		$this->send_msg($data, $af_data['m_uid']);
		// 被审批的人 $post['re_m_uid']
		$data['af_id'] = $post['af_id'];
		$data['title'] = '您收到一条新的审批';
		$data['content'] = "审批主题：" . $af_data['af_subject'] . "\n申请人:" . $af_data['m_username'] . "\n备注:" . $post['mark'];
		$this->send_msg($data, $post['re_m_uid']);

		return true;
	}

	/**
	 * 转审批人 入库
	 * @param array $post 提交数据
	 * @param array $uid_af_data 操作人审批人 进度记录
	 * @return bool
	 */
	protected function _new_approver_insert($post, $af_proc) {

		// 查询转审批人
		$serv_mem = D('Common/Member', 'Service');
		$remember_data = $serv_mem->get_by_conds(array('m_uid' => $post['re_m_uid']));
		if (empty($remember_data)) {
			E('_ERR_MISS_MEMBER');

			return false;
		}

		// 取出操作人 记录
		foreach ($af_proc as $k => $v) {
			if ($v['m_uid'] == $this->_login->user['m_uid']) {
				$uid_af_data = $af_proc[$k];
			}
		}

		$insert_data = array(
			'af_id' => $post['af_id'],
			'm_uid' => $post['re_m_uid'],
			'm_username' => $remember_data['m_username'],
			'mp_id' => 0, // 无用
			'mp_name' => '无', // 无用
			'afp_condition' => AskforProcModel::ASKING, // 审批中
			'afp_level' => $uid_af_data['afp_level'] + 1, // 操作人级数加一
			'is_active' => self::ACTIVE,
			're_m_uid' => $this->_login->user['m_uid'],
			're_m_username' => $this->_login->user['m_username'],
		);

		$this->_serv_proc->insert($insert_data);

		return true;
	}

	/**
	 * 获取该部门的对应的审批模板接口
	 */
	public function Templates_get() {

		// 从缓存中读取模板信息
		$cache = &\Common\Common\Cache::instance();
		$tmplist = $cache->get('Askfor.template');

		// 根据用户id获取所有上级部门
		list($c_cdids, $p_cdids) = Department::instance()->list_cdid_by_uid($this->_login->user['m_uid'], true);
		$cdids = array_unique(array_merge($c_cdids, $p_cdids));
		// $tmplist审批模板列表
		$show = array();
		if (empty($tmplist)) {
			return true;
		}

		// 遍历所有模板
		foreach ($tmplist as $val) {
			// 启用的模板
			if (1 != $val['is_use']) {
				continue;
			}

			// -1为通用模板
			if (in_array(-1, $val['bu_ids'])) {
				// 要显示的模板数组
				$show[] = array(
					'aftid' => $val['aft_id'],
					'name' => $val['name']
				);
			}

			// 匹配有权限的部门
			$res = array_intersect($cdids, $val['bu_ids']);
			if (!empty($res)) {
				// 要显示的模板数组
				$show[] = array(
					'aftid' => $val['aft_id'],
					'name' => $val['name']
				);
			}
		}

		// 返回数据
		$this->_result = array(
			'list' => $show,
		);
	}

	/**
	 * 催办接口
	 * @param $af_id
	 */
	public function Press_post() {

		$params = I('post.');
		$af_id = $params['af_id'];

		if (!empty($params['mark'])) {
			$mark = $params['mark'];
		} else {
			$mark = null;
		}

		$serv_proc = D('Askfor/AskforProc', 'Service');
		//该数据是否可操作
		$serv_askfor = D('Askfor/Askfor', 'Service');
		$record = $serv_askfor->is_end($this->_login->user['m_uid'], $af_id, 'press');

		//判断催办时间间隔是否小于300秒
		$serv_procrec = D('Askfor/AskforProcRecord', 'Service');
		$serv_procrec->last_press($af_id);

		//把审批记录主表进程改为已催办
		$serv_askfor->press($af_id);

		//添加催办流程记录
		$serv_procrec->press_record($af_id, $this->_login->user['m_uid'], $this->_login->user['m_username'], $mark);

		//如果是自由审批
		if ($record['aft_id'] == 0) {
			$s_list = $serv_proc->press_free($af_id);

			//给审批人发消息
			$data['af_id'] = $record['af_id'];
			$data['title'] = '您收到一条催办消息';
			$data['content'] = "催办理由：".$mark."\n主题：".$record['af_subject']."\n催办人：".$this->_login->user['m_username'];
			$this->send_msg($data, $s_list);
		} else {//固定审批
			$s_list = $serv_proc->press_fixed($af_id);

			//给审批人发消息
			$data['af_id'] = $record['af_id'];
			$data['aft_id'] = $record['aft_id'];
			$data['title'] = '您收到一条催办消息';
			$data['content'] = "催办理由：".$mark."\n主题：".$record['af_subject']."\n催办人：".$this->_login->user['m_username'];
			$this->send_msg($data, $s_list);
		}

		//返回值
		$this->_result = array(
			'cbid' => $s_list,
		);
	}

	/**
	 * 撤销接口
	 */
	public function Cancel_post() {

		$params = I('post.');
		$af_id = $params['af_id'];

		//备注不能为空
		if (empty($params['mark'])) {
			E('_ERR_MISS_MARK');

			return false;
		}
		$mark = $params['mark'];

		//实例化
		$serv_proc = D('Askfor/AskforProc', 'Service');
		$serv_askfor = D('Askfor/Askfor', 'Service');
		$serv_procrec = D('Askfor/AskforProcRecord', 'Service');

		//该数据是否可操作
		$result = $serv_askfor->is_end($this->_login->user['m_uid'], $af_id, 'cancel');

		//更改审批状态为撤销
		$serv_askfor->cancel($af_id);
		//新增撤销记录
		$serv_procrec->cancel_record($af_id, $this->_login->user['m_uid'], $this->_login->user['m_username'], $mark);

		//获取需要发消息的人
		$push_list = array();
		if ($result['aft_id'] == 0) {//如果是自由流程
			$push_list = $serv_proc->cancel_free($af_id);
		} else {//如果是固定流程
			$push_list = $serv_proc->cancel_fixed($af_id);
		}

		//发消息给审批人
		$data['af_id'] = $af_id;
		$data['aft_id'] = $result['aft_id'];
		$data['title'] = $this->_login->user['m_username'].$result['af_subject'].'审批已被撤销';
		$data['content'] = "撤销理由：".$mark."\n审批主题：".$result['af_subject']."\n申请人：".$this->_login->user['m_username'];
		$this->send_msg($data, $push_list['sp_list']);

		//发消息给抄送人
		if(!empty($push_list['cs_list'])){
			$data['af_id'] = $af_id;
			$data['aft_id'] = $result['aft_id'];
			$data['title'] = '抄送'.$this->_login->user['m_username'].$result['af_subject'].'审批已被撤销';
			$data['content'] = "撤销理由：".$mark."\n审批主题：".$result['af_subject']."\n申请人：".$this->_login->user['m_username'];
			$this->send_msg($data, $push_list['cs_list']);
		}

		return true;
	}
}
