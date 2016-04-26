<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/11/10
 * Time: 上午11:47
 */

namespace Askfor\Controller\Api;

use Askfor\Model\AskforProcModel;
use Askfor\Model\AskforModel;
use Askfor\Model\AskforProcRecordModel;
use Common\Common\Department;

class FixedController extends AbstractController {

	/**
	 * 新建固定审批初始化页面数据
	 * @return bool
	 */
	public function Initial_get() {

		$get = I('get.');

		if (!isset($get['aft_id']) || empty($get['aft_id'])) {
			E('_ERR_MISS_PARAMETER_AFTID');
		}

		$this->_serv_template = D('Askfor/AskforTemplate', 'Service');
		$this->_serv_customcols = D('Askfor/AskforCustomcols', 'Service');
		$serv_mem = D('Common/Member', 'Service');

		// 判断模板是否可用
		if (!$this->_tem_can_be_use($get['aft_id'], $aft_data)) {
			E('_ERR_TEMP_CANT_USE');

			return false;
		}
		// 获取自定义字段
		$customcols = array();
		if (!empty($aft_data['custom'])) {
			$customcols = unserialize($aft_data['custom']);
			$customcols = array_values($customcols);
		}

		// 获取抄送人
		if (empty($aft_data['copy'])) {
			$copy = array();
		} else {
			$copy = unserialize($aft_data['copy']);
			$copy = array_values($copy);
		}

		// 获取审批人头像
		$approvers = unserialize($aft_data['approvers']);
		$approvers_array = array();
		if (!empty($approvers)) {
			// 获取审批人ID
			foreach ($approvers as $_level => $_user_list) {
				foreach ($_user_list as $_user_data) {
					$approvers_array[] = $_user_data['m_uid'];
				}
			}
			// 去重
			$approvers_array = array_unique($approvers_array);
			// 查询人员信息
			$approvers_list = $serv_mem->list_by_conds(array('m_uid' => $approvers_array));
			// 匹配头像
			foreach ($approvers as $_level => &$_user_list) {
				foreach ($_user_list as &$_user_data) {

					foreach ($approvers_list as $_approvers_data) {
						if ($_approvers_data['m_uid'] == $_user_data['m_uid']) {
							$_user_data['m_face'] = $_approvers_data['m_face'];
							break;
						}
					}
				}
			}
		}

		$this->_result = array(
			'title' => $aft_data['name'],
			'to' => $approvers,
			'cc' => $copy,
			'diy' => $customcols,
		);

		return true;
	}

	/**
	 * 新建固定审批提交
	 * @return bool
	 */
	public function Insert_post() {

		$post = I('post.');

		// 验证操作
		$this->_first_insert($post, $aft_data);

		// 数据入库操作
		$this->_insertdata_insert($post, $aft_data, $af_id);

		// 判断 审批人是否有发起人 和 给审批人发送消息
		$post['m_uid'] = $this->_login->user['m_uid'];
		$post['m_username'] = $this->_login->user['m_username'];
		$post['af_subject'] = $post['title']; // 标题更名
		$this->_level = 0; // 这里为0 是因为 在_next_level里 是以下级开始
		$proc_data = $this->_serv_proc->list_by_conds(array('af_id' => $af_id));
		$this->_next_level($post, $proc_data, $af_id);

		$this->_result = array('af_id' => $af_id);

		return true;
	}

	/**
	 * 判断模板是否可用
	 * @param $aft_id
	 * @return bool
	 */
	protected function _tem_can_be_use($aft_id, &$aft_data) {

		// 获取上级部门
		list($c_cdids, $p_cdids) = Department::instance()->list_cdid_by_uid($this->_login->user['m_uid'], true);
		// 合并所属部门 并且去重
		$cdids = array_unique(array_merge($c_cdids, $p_cdids));
		// 获取模板记录
		$aft_data = $this->_serv_template->get_by_aft_id($aft_id);
		$aft_data = reset($aft_data);

		if (empty($aft_data)) {
			E('_ERR_MISS_TEMP');

			return false;
		}

		// 适用是否为 全公司
		if ($aft_data['bu_id'] == - 1) {
			return true;
		}
		// 适用是否为 在所属部门里
		foreach (explode(',', $aft_data['bu_id']) as $k => $v) {
			if (in_array($v, $cdids)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * 新建固定模板 接口的前置操作
	 * @param $post
	 * @param $aft_data
	 * @return bool
	 */
	protected function _first_insert($post, &$aft_data) {

		$this->_serv_proc = D('Askfor/AskforProc', 'Service');
		$this->_serv_template = D('Askfor/AskforTemplate', 'Service');
		$this->_serv_askfor = D('Askfor/Askfor', 'Service');

		if (!isset($post['aft_id']) || empty($post['aft_id'])) {
			E('_ERR_MISS_PARAMETER_AFTID');

			return false;
		}
		if (!isset($post['title']) || empty($post['title'])) {
			E('_ERR_MISS_PARAMETER_TITLE');

			return false;
		}
		if (!isset($post['content']) || empty($post['content'])) {
			E('_ERR_MISS_PARAMETER_CONTENT');

			return false;
		}
		// 自定义字段检查

		// 判断模板是否可用
		if (!$this->_tem_can_be_use($post['aft_id'], $aft_data)) {
			E('_ERR_TEMP_CANT_USE');

			return false;
		}

		return true;
	}

	/**
	 * 数据入库
	 * @param $post
	 * @param $aft_data
	 * @param $af_id
	 * @return bool
	 */
	protected function _insertdata_insert($post, $aft_data, &$af_id) {

		// 审批主题入库
		$askfor_insert_data = array(
			'm_uid' => $this->_login->user['m_uid'],
			'm_username' => $this->_login->user['m_username'],
			'af_subject' => $post['title'],
			'af_message' => $post['content'],
			'aft_id' => $post['aft_id'],
			'af_condition' => AskforModel::ASKING
		);
		$af_id = $this->_serv_askfor->insert($askfor_insert_data);

		// 图片
		if (!empty($post['att_id'])) {
			foreach ($post['att_id'] as $k => $v) {
				$askfor_att_insert_data_array[] = array(
					'af_id' => $af_id,
					'at_id' => $v,
					'm_uid' => $this->_login->user['m_uid'],
					'm_username' => $this->_login->user['m_username'],
				);
			}
			// 图片入库
			$this->_serv_att = D('Askfor/AskforAttachment', 'Service');
			$this->_serv_att->insert_all($askfor_att_insert_data_array);
		}

		// 自定义字段数据
		$custom = array();
		if (!empty($aft_data['custom'])) {
			$custom = unserialize($aft_data['custom']);
		}
		if (!empty($post['custom_data']) && !empty($custom)) {
			// 判断自定义字段
			foreach ($custom as $k => $v) {
				// 如果为必填
				if ($v['required'] == 1) {
					foreach ($post['custom_data'] as $_k => $_v) {
						// 如果为空 报错
						if ($v['name'] == $_v['name'] && empty($_v['value'])) {
							E('_ERR_MISS_REQUIRED');

							return false;
							// 不然就赋值
						} else if ($v['name'] == $_v['name']) {
							$insert_data = array();
							$insert_data['af_id'] = $af_id;
							$insert_data['value'] = $_v['value'];
							$insert_data['name'] = empty($v['name']) ? '' : $v['name'];
							$insert_data['type'] = empty($v['type']) ? '' : $v['type'];
							$insert_data['field'] = empty($v['field']) ? '' : $v['field'];
							$custom_data_array[] = $insert_data;
							unset($insert_data);
							break;
						}
					}
				} else {
					// 如果不为必填
					foreach ($post['custom_data'] as $_k => $_v) {
						if ($v['name'] == $_v['name']) {
							$insert_data = array();
							$insert_data['af_id'] = $af_id;
							$insert_data['value'] = empty($_v['value']) ? '' : $_v['value'];
							$insert_data['name'] = empty($v['name']) ? '' : $v['name'];
							$insert_data['type'] = empty($v['type']) ? '' : $v['type'];
							$insert_data['field'] = empty($v['field']) ? '' : $v['field'];
							$custom_data_array[] = $insert_data;
							unset($insert_data);
							break;
						}
					}
				}
			}

			// 自定义数据入库
			$this->_serv_custom = D('Askfor/AskforCustomdata', 'Service');
			$this->_serv_custom->insert_all($custom_data_array);
		}

		// 获取入库的审批人数据
		$serv_mem = D('Common/Member', 'Service');
		$approvers = unserialize($aft_data['approvers']); // 审批人数据
		$temp = array();
		$all_approvers = array();
		$approvers_array = array();
		//		array(
		//			0 => array( // 等级
		//				0 => array( // 审批人数据
		//					'm_uid' => 680,
		//					'm_username' => '深海',
		//					'm_face' => 'http://shp.qpic.cn/bizmp/yXoOYPuQdT3fD3GyqalRhO9xMZmmL0O1xzU4Je1IuvJ6CNh58owBUQ/'
		//				),
		//				1 => array(...),
		//			),
		//		);
		foreach ($approvers as $_level => $_user_data) {
			foreach ($_user_data as $_data) {
				// 记录所有审批人ID
				$all_approvers[] = $_data['m_uid'];
				$temp['af_id'] = $af_id;
				$temp['m_uid'] = $_data['m_uid'];
				$temp['m_username'] = $_data['m_username'];
				$temp['afp_level'] = $_level + 1;
				$temp['afp_condition'] = AskforProcModel::ASKING;
				$approvers_array[] = $temp;
				$temp = array();
			}
		}
		// 判断审批人是否存在
		$approvers_uid = array_column($approvers_array, 'm_uid');
		$approvers_data = $serv_mem->list_by_conds(array('m_uid' => $approvers_uid));
		foreach ($approvers_data as $_u_data) {
			if (!in_array($_u_data['m_uid'], $approvers_uid)) {
				$this->_serv_askfor->delete_by_conds(array('af_id' => $af_id));

				E(L('_ERR_TEMP_MISS_APPROVER_DATA', array('username' => $_u_data['m_username'])));
			}
		}

		// 抄送人入库数据
		if (!empty($post['copy'])) {
			$mem_data = $serv_mem->list_by_conds(array('m_uid' => $post['copy']));
			$m_uids = array_column($mem_data, 'm_uid'); // 提取uid
			foreach ($post['copy'] as $k => $v) {
				// 剔除抄送人里的自己和 审批人
				if (in_array($v, $all_approvers) || $v == $this->_login->user['m_uid']) {

					$this->_serv_askfor->delete_by_conds(array('af_id' => $af_id));
					$this->_serv_proc->delete_by_conds(array('af_id' => $af_id));

					E('_ERR_COPY_CAN_NOT_HAVE_APPROVER_OR_MINE');
					return false;
				}
				// 如果没有这个人的信息
				if (!in_array($v, $m_uids)) {
					$this->_serv_askfor->delete_by_conds(array('af_id' => $af_id));
					$this->_serv_proc->delete_by_conds(array('af_id' => $af_id));

					E('_ERR_MISS_MEMBER');
					return false;
				}
				$temp['af_id'] = $af_id;
				$temp['m_uid'] = $v;
				// 匹配抄送人姓名
				foreach ($mem_data as $_k => $_v) {
					if ($_v['m_uid'] == $v) {
						$temp['m_username'] = $_v['m_username'];
						break;
					}
				}
				$temp['afp_level'] = 0; // 抄送人默认数据
				$temp['afp_condition'] = AskforProcModel::COPYASK;
				$approvers_array[] = $temp;
				$copy_array[] = $temp;
				$temp = array();
			}
			// 取出抄送人ID
			$copy_uids = array_column($copy_array, 'm_uid');
			// 发送消息给 抄送人
			$data['af_id'] = $af_id;
			$data['aft_id'] = $aft_data['aft_id'];
			$data['title'] = '抄送' . $this->_login->user['m_username'] . '审批申请';
			$data['content'] = "审批主题：" . $post['title'] . "\n申请人:" . $this->_login->user['m_username'];
			$this->send_msg($data, $copy_uids);
		}

		// 审批进度信息 入库
		$this->_serv_proc->insert_all($approvers_array);

		return true;
	}

}
