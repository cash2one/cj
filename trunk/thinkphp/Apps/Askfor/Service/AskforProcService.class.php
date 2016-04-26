<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/11/10
 * Time: 下午4:54
 */
namespace Askfor\Service;

use Askfor\Model\AskforProcModel;

class AskforProcService extends AbstractService {

	const PROMOTER = 1; // 发起人
	const APPROVER = 2; // 审批人
	const COPY = 3; // 抄送人

	// 构造方法
	public function __construct() {

		$this->_d = D("Askfor/AskforProc");
		parent::__construct();
	}

	/**
	 * 判断是否有查看权限
	 * @param $uid 用户ID
	 * @param $af_id 审批ID
	 * @param $af_data 审批数据
	 * @param $af_proc 审批进度
	 * @param $identity 当前用户身份
	 * @return bool true 有权限 false 无
	 */
	public function is_viewer($uid, $af_id, &$af_data, &$af_proc, &$identity) {

		// 获取和审批ID有关的 进度
		$af_proc = $this->_d->list_by_afid($af_id);

		// 获取用户头像
		$m_uids = array_column($af_proc, 'm_uid');
		$m_uids[] = $af_data['m_uid'];
		$serv_member = D('Common/Member');
		$m_uid_data = $serv_member->list_by_conds(array('m_uid' => $m_uids));
		foreach ($m_uid_data as $k => $v) {
			foreach ($af_proc as $_k => &$_v) {
				if ($v['m_uid'] == $_v['m_uid']) {
					$_v['m_face'] = $v['m_face'];
					break;
				}
			}
			if ($v['m_uid'] == $af_data['m_uid']) {
				$af_data['m_face'] = $v['m_face'];
			}
		}

		// 是否是发起人
		if ($uid == $af_data['m_uid']) {
			$identity = self::PROMOTER;

			return true;
		}

		// 判断 是否是 审批人 或者是抄送人
		foreach ($af_proc as $k => $v) {
			if ($v['m_uid'] == $uid) {
				$identity = self::APPROVER;
				if ($v['afp_condition'] == AskforProcModel::COPYASK) {
					$identity = self::COPY;
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * 判断是否还有下一级审批人 查询下一级审批人
	 * @param $af_id
	 * @param $afp_level
	 * @param $af_proc 下一级审批人的数据
	 * @return bool true 有下级 false 没有
	 */
	public function has_next_level($af_id, $afp_level, &$af_proc) {

		$af_proc = $this->_d->get_by_afid_level($af_id, $afp_level);

		if (!empty($af_proc)) {
			return true;
		}

		return false;
	}

	/**
	 * 删除抄送人
	 * @param $re_m_uid
	 * @param $af_id
	 * @return mixed
	 */
	public function delete_copy_by_reuid($re_m_uid, $af_id) {

		$conds = array(
			'af_id' => $af_id,
			'afp_condition' => AskforProcModel::COPYASK,
			'm_uid' => $re_m_uid,
		);

		return $this->_d->delete_by_conds($conds);
	}

	/**
	 * 更改审批人审批状态
	 * @param $m_uid
	 * @param $af_id
	 * @param $afp_condition
	 */
	public function update_afp_condition($m_uid, $af_id, $afp_condition, $mark = '') {

		$conds = array(
			'm_uid' => $m_uid,
			'af_id' => $af_id,
		);
		$up_data = array(
			'afp_condition' => $afp_condition,
//			'is_active' => self::UNACTIVE,
			'afp_note' => $mark,
		);
		$this->update_by_conds($conds, $up_data);
	}

	/**
	 * 根据 uid 和 审批状态查询
	 * @param $uid
	 * @param $afp_condition
	 * @return mixed
	 */
	public function get_by_uid_cond($uid, $afp_condition) {

		return $this->_d->get_by_uid_cond($uid, $afp_condition);
	}

	/**
	 * 自由流程审批人入库
	 * @param array $params 接收参数
	 * @param int   $af_id 审批id
	 * @return bool
	 */
	public function sp_add($params, $af_id) {

		$s_list = array();
		//整理入库数据
		foreach ($params['s_uids'] as $val) {
			$s_list[] = array(
				'af_id' => $af_id,
				'm_uid' => $val['m_uid'],
				'm_username' => $val['m_username'],
				'afp_condition' => AskforProcModel::ASKING,
				'is_active' => 1,
			);
		}
		//入库操作

		$this->_d->insert_all($s_list);

		return true;
	}

	/**
	 * 自由流程抄送人入库
	 * @param $params array 接收参数
	 * @param $af_id int 审批id
	 * @return bool
	 */
	public function cs_add($params, $af_id) {

		$c_list = array();
		//格式入库数据
		foreach ($params['c_uids'] as $val) {
			$c_list [] = array(
				'af_id' => $af_id,
				'm_uid' => $val['m_uid'],
				'm_username' => $val['m_username'],
				'afp_condition' => AskforProcModel::COPYASK,
			);
		}
		//抄送人入库
		$this->_d->insert_all($c_list);

		return true;
	}

	/**
	 * 获取自由审批中未审批人列表
	 * @param int $af_id 审批id
	 * @return array $s_list 未审批人列表
	 */
	public function press_free($af_id) {

		//查所有审批人中没有审批的人
		$conds_proc['afp_condition'] = AskforProcModel::ASKING;
		$conds_proc['af_id'] = $af_id;
		//获取未审批人的记录
		$pro_list = $this->_d->list_by_conds($conds_proc);

		//整合所有未审批的人
		$s_list = array();
		if (!empty($pro_list)) {
			foreach ($pro_list as $val) {
				$s_list[] = $val['m_uid'];
			}
		}

		//给数组中的人发消息

		return $s_list;
	}

	/**
	 * 固定流程获取未审批人列表
	 * @param int $af_id 审批id
	 * @return array $s_list 审批人列表
	 */
	public function press_fixed($af_id) {

		//获取当前审批级数
		$conds_current['is_active'] = self::ACTIVE;
		$conds_current['afp_condition'] = AskforProcModel::ASKING;
		$conds_current['af_id'] = $af_id;
		$re_current = $this->_d->list_by_conds($conds_current);

		//当前级所有人数组
		$s_list = array();
		foreach ($re_current as $v) {
			$s_list[] = $v['m_uid'];
		}

		//给当前级数人发消息

		return $s_list;
	}

	/**
	 * 撤销审批返回所有的审批人和抄送人
	 * @param int $af_id 审批id
	 * @return array $push_list 要发消息的人列表
	 */
	public function cancel_free($af_id) {

		//获取所有正在审批的审批人和抄送人
		$conds_pro['af_id'] = $af_id;
		$conds_pro['afp_condition'] = array(AskforProcModel::ASKING, AskforProcModel::COPYASK);

		$list = $this->_d->list_by_conds($conds_pro);

		//获取所有人id
		$push_list['sp_list'] = array();
		$push_list['cs_list'] = array();
		foreach ($list as $val) {
			//审批人
			if($val['afp_condition'] == AskforProcModel::ASKING){
				$push_list['sp_list'][] = $val['m_uid'];
			}
			//抄送人
			if($val['afp_condition'] == AskforProcModel::COPYASK){
				$push_list['cs_list'][] = $val['m_uid'];
			}
		}

		return $push_list;
	}

	/**
	 * 撤销固定流程返回审批人和抄送人
	 * @param int $af_id 审批id
	 * @return array $push 要发消息的人列表
	 */
	public function cancel_fixed($af_id) {

		//获取一级审批人
		$conds_proc['af_id'] = $af_id;
		$list = $this->_d->list_by_conds($conds_proc);

		$push_list['sp_list'] = array();
		$push_list['cs_list'] = array();
		//获取所有人id
		$push_list['sp_list'] = array();
		foreach ($list as $val) {
			//审批人
			if($val['afp_level'] == 1){
				$push_list['sp_list'][] = $val['m_uid'];
			}
			//抄送人
			if($val['afp_condition'] == AskforProcModel::COPYASK){
				$push_list['cs_list'][] = $val['m_uid'];
			}
		}

		return $push_list;
	}

	/**
	 * 后台查询进程
	 * @param int $af_id 审批id
	 * @return array $proc_list 进程列表
	 */
	public function cp_list_by_conds($af_id) {

		$conds['af_id'] = $af_id;
		$conds['afp_condition'] = array(
			AskforProcModel::ASKING,
			AskforProcModel::ASKPASS,
			AskforProcModel::TURNASK,
			AskforProcModel::ASKFAIL,
			AskforProcModel::PRESSASK,
			AskforProcModel::CENCEL,
		);
		$proc_list = $this->_d->list_by_conds($conds);

		return $proc_list;
	}
}
