<?php
/**
 * AbstractController.class.php
 * $author$
 */
namespace Askfor\Controller\Api;

use Askfor\Model\AskforProcRecordModel;
use Common\Common\Plugin;
use Common\Common\Cache;
use Common\Common\Wxqy\Service;
use Common\Common\WxqyMsg;
use Askfor\Model\AskforProcModel;
use Askfor\Model\AskforModel;
use Askfor\Service\AskforProcService;


abstract class AbstractController extends \Common\Controller\Api\AbstractController {

	const ACTIVE = 1; // 达到状态
	const UNACTIVE = 0; // 没有达到状态

	const ABS_PROMOTER = 'promoter'; // 发起人
	const ABS_APPROVER = 'approver'; // 审批人
	const ABS_VIEWER = 'viewer'; // 查看人

	protected $_serv_askfor = null; // 审批表
	protected $_serv_proc = null; // 审批进度表
	protected $_serv_custom = null; // 自定义数据
	protected $_serv_customcols = null; // 自定义字段结构
	protected $_serv_template = null; // 模板
	protected $_serv_att = null; // 附件

	protected $_operator_proc = ''; // 当前操作人
	protected $_level = ''; // 当前等级

	public function before_action() {

		return parent::before_action();
	}

	public function after_action() {

		return parent::after_action();
	}

	// 获取插件配置
	protected function _get_plugin() {

		// 获取插件信息
		$this->_plugin = &Plugin::instance('askfor');

		// 更新 pluginid, agentid 配置
		cfg('PLUGIN_ID', $this->_plugin->get_pluginid());
		cfg('AGENT_ID', $this->_plugin->get_agentid());
		cfg('PLUGIN_IDENTIFIER', $this->_plugin->get_name());

		return true;
	}

	/**
	 * 审批应用发送微信消息
	 * @param       $data
	 *  + int af_id 审批id
	 *  + string title 消息类型标题信息(催办消息/撤销消息)
	 *  + string content 自己拼接好的内容
	 * @param array $to_users 接收消息的用户
	 * @return bool
	 */
	public function send_msg($data, $to_users) {

			// 获取url
		$cache = &\Common\Common\Cache::instance();
		$setting = $cache->get('Common.setting');

		// 实例化消息类
		if (! empty($data['aft_id']) && $data['aft_id'] != 0) {
			$url = U('/Askfor/Frontend/Index/ViewFixed', "af_id={$data['af_id']}", false, true);
		} else {
			$url = U('/Askfor/Frontend/Index/ViewFree', "af_id={$data['af_id']}", false, true);
		}

		//发消息
		Wxqymsg::instance()->send_news($data['title'], $data['content'], $url, $to_users, '', '', cfg('AGENT_ID'), cfg('PLUGIN_ID'));

		return true;
	}

	/**
	 * @param array $data
	 * + af_id 申请主题ID
	 * + m_uid 审批人UID
	 * + m_username 审批人名称
	 * + rafp_note 备注进度
	 * + rafp_condition 操作状态, 1=审核中，2=审核通过, 3=转审批, 4=审核不通过, 5=抄送，6=已催办，7=已撤销, 8=发起
	 * + re_m_uid 转审批人ID
	 * + re_m_username 转审批人名称
	 * @param bool $is_multi 是否多条
	 * @param array $result 备用条件
	 * @return mixed
	 */
	protected function _proc_record($data, $is_multi = false, $result = array()) {

		$serv_proc_record = D('Askfor/AskforProcRecord', 'Service');

		if ($is_multi) {
			$sql = $serv_proc_record->insert_all($data);
		} else {
			$sql = $serv_proc_record->insert($data);
		}

		return $sql;
	}

	/**
	 * 判断是否有权限
	 * @param int    $uid 用户iD
	 * @param int    $af_id 审批ID
	 * @param int    $type 类型
	 * @param array  $af_data 审批数据
	 * @param array  $af_proc 审批进度数据
	 * @param string $identity 身份
	 * @return bool true 有权限 false 无
	 */
	protected function _is_privileges($uid, $af_id, $type, &$af_data = array(), &$af_proc = array(), &$identity = '') {

		// 获取审批主题数据
		$serv_askfor = D('Askfor/Askfor', 'Service');
		$af_data = $serv_askfor->get_data_by_af_id($af_id);

		// 为空
		if (empty($af_data)) {
			E('_ERR_NO_DATA');

			return false;
		}

		switch ($type) {
			case self::ABS_PROMOTER: // 发起人

				// 结束状态
				if (in_array($af_data['af_condition'], array(
					AskforProcModel::ASKPASS,
					AskforProcModel::ASKFAIL,
					AskforProcModel::CENCEL,
				))) {
					E('_ERR_IS_OVER');

					return false;
				}

				if ($uid == $af_data['m_uid']) {
					return true;
				}
				break;
			case self::ABS_APPROVER: // 审批操作权限

				// 结束状态
				if (in_array($af_data['af_condition'], array(
					AskforProcModel::ASKPASS,
					AskforProcModel::ASKFAIL,
					AskforProcModel::CENCEL,
				))) {
					E('_ERR_IS_OVER');

					return false;
				}

				// 取出所有审批记录
				$af_proc = $this->_serv_proc->list_by_conds(array('af_id' => $af_id));

				// 如果所有记录里 有操作人的 并且 审批状态为 审批中
				foreach ($af_proc as $k => $v) {
					if ($v['m_uid'] == $uid && $v['afp_condition'] == AskforProcModel::ASKING) {
						return true;
					}
				}

				break;
			case self::ABS_VIEWER: // 查看权限

				// 取出所有关于这条审批的 审批人
				if ($this->_serv_proc->is_viewer($uid, $af_id, $af_data, $af_proc, $identity)) {
					return true;
				};

				break;
		}

		return false;
	}

	/**
	 * 下一级 审批人
	 * @param array $askfor_data 审批主状态
	 * @param array $proc_data 所有审批记录
	 * @param int   $af_id 审批ID
	 * @param bool  $is_agree 是否是同意
	 * @return bool
	 */
	protected function _next_level($askfor_data, $proc_data, $af_id, $is_agree = false) {

		// 当前级数加一 到下一级
		$this->_level = (int)$this->_level + 1;

		// 是否有下一级审批人
		$next_array = array();
		foreach ($proc_data as $k => $v) {
			if ($v['afp_level'] == $this->_level) {
				$next_array[] = $proc_data[$k];
			}
		}
		if (empty($next_array)) {
			// 给发起人,抄送人发送消息
			// 发起人 $askfor_data['m_uid'] 抄送人 foreach ($proc_data as $v) {if ($v['afp_condition'] == AskforProcModel::COPYASK) {$copy[] = $v['m_uid'];}}
			$data['af_id'] = $af_id;
			$data['title'] = $askfor_data['af_subject'] . '审批已通过';
			$data['aft_id'] = $askfor_data['aft_id'];
			$data['content'] = "审批主题：" . $askfor_data['af_subject'];
			$message_to = array();
			foreach ($proc_data as $v) {
				if ($v['afp_condition'] == AskforProcModel::COPYASK || $v['afp_condition'] == AskforProcModel::ASKPASS) {
					$message_to[] = $v['m_uid'];
				}
			}
			$message_to[] = $askfor_data['m_uid'];
			$this->send_msg($data, $message_to);
			// 更改审批状态为通过
			$this->_serv_askfor->update_by_conds(array('af_id' => $af_id), array('af_condition' => AskforModel::ASKPASS));

			return true;
		} else {
			// 更改全部 为 未到达
			$up_data = array('is_active' => self::UNACTIVE);
			$conds = array('is_active' => self::ACTIVE, 'af_id' => $af_id);
			$this->_serv_proc->update_by_conds($conds, $up_data);
		}

		// 判断发起人是否在下一级审批人里
		$next_m_uids = array_column($next_array, 'm_uid'); // 下一级审批人的m_uid
		if (in_array($askfor_data['m_uid'], $next_m_uids)) {

			// 更改发起人直接为同意
			$up_data = array('afp_condition' => AskforProcModel::ASKPASS);
			$conds = array('m_uid' => $askfor_data['m_uid'], 'af_id' => $af_id);
			$this->_serv_proc->update_by_conds($conds, $up_data);
			// 操作记录
			$proc_record_data = array(
				'af_id' => $af_id,
				'm_uid' => $askfor_data['m_uid'],
				'm_username' => $askfor_data['m_username'],
				'rafp_condition' => AskforProcRecordModel::ASKPASS
			);
			$this->_proc_record($proc_record_data);

			// 更新审批所在的级数状态 is_active 为 到达
			$up_data = array('is_active' => self::ACTIVE);
			$conds = array('afp_level' => $this->_level, 'af_id' => $af_id);
			$this->_serv_proc->update_by_conds($conds, $up_data);

			// 下一级只有发起人自己一人
			if (count($next_array) == 1) {
				// 进入下一级 再次判断
				$this->_next_level($askfor_data, $proc_data, $af_id);
			} else {
				// 剔除发起人
				$other_uid = array_diff($next_m_uids, array($askfor_data['m_uid']));

				// 发送消息给其余审批人 $other_uid
				$data['af_id'] = $af_id;
				$data['aft_id'] = $askfor_data['aft_id'];
				$data['title'] = '您收到一条新的审批';
				$data['content'] = "审批主题：" . $askfor_data['af_subject'] . "\n申请人:" . $askfor_data['m_username'];
				$this->send_msg($data, $other_uid);

				return true;
			}
		} else {
			// 发送消息给审批人 $next_m_uids
			$data['af_id'] = $af_id;
			$data['title'] = '有新的审批';
			$data['aft_id'] = $askfor_data['aft_id'];
			$data['content'] = "审批主题：" . $askfor_data['af_subject'] . "\n申请人:" . $askfor_data['m_username'];
			$this->send_msg($data, $next_m_uids);

			// 给发起人发送消息, 当前审批人已同意 $af_data['m_uid']
//			if ($is_agree) {
//				$get = I('get.');
//				$data['af_id'] = $af_id;
//				$data['title'] = $this->_login->user['m_username'] . '已经同意审批';
//				$data['aft_id'] = $askfor_data['aft_id'];
//				$data['content'] = "审批主题：" . $askfor_data['af_subject'] . "\n审批人:" . $this->_login->user['m_username'] . "\n备注:" . $get['mark'];
//				$this->send_msg($data, $askfor_data['m_uid']);
//			}

			// 更新审批所在的级数状态 is_active 为 到达
			$up_data = array('is_active' => self::ACTIVE);
			$conds = array('afp_level' => $this->_level, 'af_id' => $af_id);
			$this->_serv_proc->update_by_conds($conds, $up_data);

			return true;
		}

		return true;
	}

	/**
	 * 从缓存中获取所有部门
	 * @return array $list 所有部门
	 */
	public function department_list() {

		$cache = &\Common\Common\Cache::instance();
		$list = $cache->get('Common.department');

		return $list;
	}
}
