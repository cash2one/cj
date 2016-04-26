<?php
/**
 * 内部人员 报名 Service
 * User: Muzhitao
 * Date: 2015/9/30 0030
 * Time: 14:25
 * Email：muzhitao@vchangyi.com
 */

namespace Activity\Service;
use Common\Common\User;

class ActivityPartakeService extends AbstractService {

	protected $_activity_model;

	public function __construct() {

		parent::__construct();
		$this->_d = D("Activity/ActivityPartake");
		$this->_activity_model = D('Activity/Activity');
	}

	/**
	 * 插入数据
	 * @param array $data
	 */
	public function insert_data($data = array()) {

		// 如果为空
		if (empty($data)) {
			$this->_set_error('_ERROR_JOIN_DATA_NULL');
			return false;
		}
		// 获取详情
		$detail = $this->_activity_model->get_detail_by_acid($data['acid'], 'acid, title, start_time, end_time, m_uid');

		// 如果活动不存在
		if (empty($detail)) {
			$this->_set_error('_ERR_ACT_NOT_NULL');
			return false;
		}

		// 插入数据
		$this->_d->insert($data);

		$time = date('m-d H:i', $detail['start_time'])." 到 ". date('m-d H:i', $detail['end_time']);
		$description = "主题：【".$detail['title']."】\n"."活动时间：{$time}";
		// 数据组装
		$data = array(
			'title' => "您发布的活动有人报名啦",
			'description' => $description,
			'url' => $this->view_url($detail['acid'])
		);

		$to_user = array($detail['m_uid']);
		// 发送消息
		$this->send_msg($data, $to_user);

		return true;
	}

	/**
	 * 获取内部报名人员列表
	 * @param $conds 查询条件
	 * @param $page_option 分页参数
	 * @param $order_option 排序条件
	 * @param $field 选择的字段
	 * @return mixed
	 */
	public function data_list($conds, $page_option, $order_option, $field = "*") {

		$list = $this->_d->fetch_all_by_conds($conds, $page_option, $order_option, $field);

		return $list;
	}

	/**
	 * 格式化数据列表
	 * @param $data
	 * @return bool
	 */
	public function format_data(&$data) {

		// 如果为空，则返回空数组
		if (empty($data)) {
			return true;
		}

		// 格式化相关数据
		foreach ($data as $_k => $_v) {
			$data[$_k]['avator'] = User::instance()->avatar($_v['m_uid']);
			$data[$_k]['created'] = date("m月d日", $_v['created']);
		}
	}

	/**
	 * 二维码扫描
	 * @param $id
	 * @return bool
	 */
	public function scan($id) {

		// 参数错误
		if (empty($id)) {
			$this->_set_error('_ERROR_PARAMETER');
			return false;
		}

		$detail = $this->_d->get_detail_by_apid($id);

		// 报名数据为空
		if (empty($detail)) {
			$this->_set_error('_ERROR_JOIN_DATA_NULL');
			return false;
		}

		// 已经报名过了
		if ($detail['check'] == self::IS_CHECK) {
			$this->_set_error('_ERROR_IS_JOIN');
			return false;
		}

		$conds = array('check' => self::IS_CHECK);
		// 更新签到状态
		$this->_d->update($id, $conds);

		return true;
	}
}

//end
