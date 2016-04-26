<?php
/**
 * 活动报名 外部报名人员
 * User: Muzhitao
 * Date: 2015/9/30 0030
 * Time: 16:09
 * Email：muzhitao@vchangyi.com
 */

namespace Activity\Service;
use Common\Common\User;

class ActivityOutsiderService extends AbstractService{

	protected $_activity_model;

	public function __construct() {

		parent::__construct();
		// 实例化相关模型
		$this->_d = D("Activity/ActivityOutsider");
		$this->_activity_model = D('Activity/Activity');
	}

	/**
	 * 通过活动iD和手机号码 判断是否报名
	 * @param $acid
	 * @param $mobile
	 * @return bool
	 */
	public function get_by_uid_mobile($acid, $mobile) {

		$result = $this->_d->get_by_uid_mobile($acid, $mobile);

		// 判断是否存在
		if ($result) {
			$this->_set_error('_ERROR_IS_JOIN');
			return false;
		}

		return true;
	}

	/**
	 * 通过活动iD和用户姓名和手机号码 判断是否报名
	 * @param $acid
	 * @param $mobile
	 * @return bool
	 */
	public function get_by_uid_out($acid, $outname, $outphone) {

		$result = $this->_d->get_by_uid_out($acid, $outname, $outphone);

		// 判断是否存在
		if (empty($result)) {
			$this->_set_error('_ERROR_IS_JOIN');
			return false;
		}

		return $result;
	}

	/**
	 * 外部人员报名信息入库
	 * @param $data
	 * @return bool
	 */
	public function insert_data($data) {

		// 如果为空 返回错误
		if (empty($data)) {
			$this->_set_error('_ERROR_JOIN_DATA_NULL');
			return false;
		}

		// 插入数据
		$this->_d->insert($data);

		// 获取详情
		$detail = $this->_activity_model->get_detail_by_acid($data['acid'], 'acid, title, start_time, end_time, m_uid');

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
	}

	/**
	 * 获取外部报名人员列表
	 * @param $conds
	 * @param $page_option
	 * @param $order_option
	 * @param string $field
	 * @return mixed
	 */
	public function data_list($conds, $page_option, $order_option, $field = "*") {

		$list = $this->_d->fetch_all_by_conds($conds, $page_option, $order_option, $field);

		return $list;
	}

	/**
	 * 格式化数据
	 * @param $data
	 * @return bool
	 */
	public function format_data(&$data) {

		// 如果为空，则直接返回空数据
		if (empty($data)) {
			return true;
		}

			// 格式化
		foreach ($data as $_k => $_v) {
			$data[$_k]['avator'] = User::instance()->avatar(0);
			$data[$_k]['created'] = date("m月d日", $_v['created']);
			$data[$_k]['other'] = unserialize($_v['other']);
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

		$detail = $this->_d->get_detail_by_oapid($id);

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

// end
