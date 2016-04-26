<?php
/**
 * 活动报名 审核申请.
 * User: Muzhitao
 * Date: 2015/10/9 0009
 * Time: 16:36
 * Email：muzhitao@vchangyi.com
 */

namespace Activity\Service;

class ActivityNopartakeService extends AbstractService {

	protected $_partake_model;
	protected $_activity_model;

	public function __construct() {

		parent::__construct();

		// 实例化相关模型
		$this->_d = D("Activity/ActivityNopartake");
		$this->_partake_model = D("Activity/ActivityPartake");
		$this->_activity_model = D('Activity/Activity');
	}

	/**
	 * 申请退出
	 * @param $conds
	 * @param $apply
	 * @return bool
	 */
	public function doit($conds, $apply) {

		$result = $this->_partake_model->get_by_conds($conds);
		// 如果当前已经签到过，则无法申请退出
		if ($result['check'] == self::IS_CHECK) {
			$this->_set_error('_ERROR_IS_CHECK');
			return false;
		}

		// 更新当前状态,设置为申请取消
		$this->_partake_model->update_by_conds(array('apid' => $result['apid']), array('type' => 2));

		$nopartake = $this->_d->get_anpid_by_apid($result['apid']);

		if ($nopartake) {
			$this->_d->update($nopartake['anpid'], array('apply'=>$apply));
		} else {
			$data = array(
				'apid' => $result['apid'],
				'apply' => $apply
			);
			$this->_d->insert($data);
		}

		// 获取详情
		$detail = $this->_activity_model->get_detail_by_acid($conds['acid'], 'acid, title, start_time, end_time, m_uid');

		$time = date('m-d H:i', $detail['start_time'])." 到 ". date('m-d H:i', $detail['end_time']);
		$description = "主题：【".$detail['title']."】\n"."活动时间：{$time}";
		// 数据组装
		$data = array(
			'title' => "您收到一条申请取消报名的信息",
			'description' => $description,
			'url' => $this->_return_view($result['apid'], $detail['acid'])
		);

		$to_user = array($detail['m_uid']);
		// 发送消息
		$this->send_msg($data, $to_user);

		return true;
	}

	/**
	 * 审批取消视图
	 * @param $acid
	 * @param $apid
	 * @param $m_uid
	 * @return bool
	 */
	public function return_detail($acid, $apid, $m_uid, &$data) {

		// 查询活动信息的标题和当前作者
		$fields = "m_uid, title";
		$detail = $this->_activity_model->get_detail_by_acid($acid, $fields);

		// 活动不存在
		if (empty($detail)) {
			$this->_set_error('_ERR_ACT_NOT_NULL');
			return false;
		}

		// 没有审核权限
		if ($detail['m_uid'] != $m_uid) {
			$this->_set_error('_ERROR_NO_PRIV');
			return false;
		}

		// 查询用户申请退出的信息
		$ap_fields = "name, type";
		$result = $this->_partake_model->get_detail_by_apid($apid, $ap_fields);

		// 用户未申请退出
		if ($result['type'] == self::NO_APPLY) {
			$this->_set_error('_ERROR_NO_APPLY');
			return false;
		}

		// 已经处理过了
		if ($result['type'] == self::END_APPLY) {
			$this->_set_error('_ERROR_IS_OPTION');
			return false;
		}

		// 查询用户申请退出的原因
		$reason = $this->_d->get_anpid_by_apid($apid);

		// 返回数据
		$data = array(
			'acid' => $acid,
			'anpid' => $reason['anpid'],
			'apid' => $apid,
			'title' => $detail['title'],
			'name' => $result['name'],
			'apply' => $reason['apply']
		);

		return $data;
	}

	/**
	 * 驳回取消报名申请
	 * @param $acid
	 * @param $apid
	 * @param $anpid
	 * @param $apply
	 * @return bool
	 */
	public function reject_apply($acid, $apid, $anpid, $apply) {

		$partake = $this->_partake_model->get_detail_by_apid($apid, 'm_uid, type');
		// 申请记录不存在
		if (empty($partake)) {
			$this->_set_error('_ERROR_NOT_APPLY');
			return false;
		}

		// 用户未申请退出
		if($partake['type'] != 2) {
			$this->_set_error('_ERROR_NO_APPLY');
			return false;
		}

		// 更新审核原因
		$this->_d->update($anpid, array('reject' => $apply));
		$this->_partake_model->update($apid, array('type' => 1));

		// 获取详情
		$detail = $this->_activity_model->get_detail_by_acid($acid, 'acid, title, start_time, end_time, m_uid');

		$time = date('m-d H:i', $detail['start_time'])." 到 ". date('m-d H:i', $detail['end_time']);
		$description = "主题：【".$detail['title']."】\n"."活动时间：{$time}";

		// 数据组装
		$data = array(
			'title' => "您申请的取消报名已被驳回",
			'description' => $description,
			'url' => $this->view_url($acid)
		);

		$to_user = array($partake['m_uid']);
		// 发送消息
		$this->send_msg($data, $to_user);
	}

	/**
	 * 同意取消操作
	 * @param $acid
	 * @param $apid
	 * @return bool
	 */
	public function agree_apply($acid, $apid) {

		$partake = $this->_partake_model->get_detail_by_apid($apid, 'm_uid, type');
		// 申请记录不存在
		if (empty($partake)) {
			$this->_set_error('_ERROR_NOT_APPLY');
			return false;
		}

		// 用户未申请退出
		if($partake['type'] != 2) {
			$this->_set_error('_ERROR_NO_APPLY');
			return false;
		}

		$this->_partake_model->update($apid, array('type' => 3));

		// 获取详情
		$detail = $this->_activity_model->get_detail_by_acid($acid, 'acid, title, start_time, end_time, m_uid');

		$time = date('m-d H:i', $detail['start_time'])." 到 ". date('m-d H:i', $detail['end_time']);
		$description = "主题：【".$detail['title']."】\n"."活动时间：{$time}";

		// 数据组装
		$data = array(
			'title' => "您的报名已取消",
			'description' => $description,
			'url' => $this->view_url($acid)
		);

		$to_user = array($partake['m_uid']);
		// 发送消息
		$this->send_msg($data, $to_user);
	}

	/**
	 * 审批取消报名url地址
	 * @param $apid
	 * @param $acid
	 * @return string
	 */
	protected function _return_view($apid, $acid){

		$cache = &\Common\Common\Cache::instance();
		$sets = $cache->get('Common.setting');
		$face_base_url = cfg('PROTOCAL') . $sets ['domain'];

		$url = $face_base_url. "/frontend/activity/return/?apid={$apid}&acid={$acid}";

		return $url;
	}
}

// end
