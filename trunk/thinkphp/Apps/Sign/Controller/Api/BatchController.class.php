<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/9/11
 * Time: 下午3:09
 */

namespace Sign\Controller\Api;

use Common\Common\Cache;

class BatchController extends AbstractController {
	// 人员表
	protected $_serv_member = null;
	// 人员部门关联数据库
	protected $_serv_memberpart = null;
	// 部门班次关联数据库
	protected $_serv_department = null;
	// 班次数据库
	protected $_serv_batch = null;
	// 签到记录表
	protected $_serv_recode = null;
	// 备注记录表
	protected $_serv_detail = null;
	// 登陆用户ID
	protected $_m_uid = null;
	// 部门缓存
	protected $_all_department = array();

	/**
	 * 获取班次列表 接口
	 * @return bool
	 */
	public function Batchlist_get() {

		// 实例化
		$this->_serv_batch = D('Sign/SignBatch', 'Service');

		// 获取当前人物信息和 所在班次
		$this->_m_uid = $this->_login->user['m_uid'];
		$this->_member_data = $this->_get_member_batch($this->_m_uid, $department);

		// 获取班次信息
		$info = array();
		if (isset($this->_member_data['batch'])) {
			$info['batchlist'] = $this->_member_data['batch'];
		} else {
			E('_ERR_NO_BATCH');
		}

		// 获取班次ID 对应的 人物所属班次名
		$this->_serv_batch->batch_user_in_department($info['batchlist']);

		$this->_result = $info;

		return true;
	}

	/**
	 * 获取班次信息 接口
	 * @return bool
	 */
	public function Info_get() {

		// 实例化数据库
		$this->__execute();

		// 获取当前登陆人物
		$this->_m_uid = $this->_login->user['m_uid'];
		// 获取班次信息
		$info = $this->_serv_batch->get_batch_info_for_index();
		if (empty($info)) {
			E('_ERR_MISS_BATCH_ID');
		}

		// 获取到班次信息后计算其他属性
		if (isset ($info)) {
			// 获取 签到记录 当前签到还是签退 是否允许签到
			$this->__get_first_data($info, $records, $sb_set, $allow_sign);

			// 获取 签到ID 班次上下班的时间 签到记录 签退记录 当前签到ID 当天上班签到记录的时间 当天下班签到记录的时间
			$this->__get_second_data($records, $sb_set, $info, $sr_id, $p_set, $work_on, $work_off, $detail, $on_signtime_hi, $off_signtime_hi);

			// 获取 签到备注记录 签退备注记录 签到类型 部门ID 人物信息
			$this->__get_third_data($sr_id, $info, $work_on, $work_on_detail, $work_off_detail, $sign_type, $department_name);

			$this->_result = array(
				'allow_sign' => $allow_sign, // 判断当前打什么卡 1: 上班卡 2: 下班卡
				'work_on_detail' => $work_on_detail, // 签到备注
				'work_off_detail' => $work_off_detail, // 签退备注
				'detail' => $detail, // 签到记录ID 判断是否显示添加 备注按钮
				'p_set' => $p_set, // 打卡开始/结束时间
				'work_on' => $work_on, // 上班的签到记录
				'work_off' => $work_off, // 下班的签到记录
				'sign_type' => $sign_type, // 当前的签到类型; 1:打完上班卡,应该打下班卡 2:打下班卡 给签到接口的数据
				'sb_set' => $sb_set, // 后台设置的签到类型
				'on_signtime_hi' => $on_signtime_hi, // 上班签到按钮上面的时间
				'off_signtime_hi' => $off_signtime_hi, // 下班签到按钮上面的时间
				'sbid' => $info['sbid'], //班次ID
				'department' => $department_name, // 当前班次对应的部门名称
				'username' => $this->_login->user['m_username'] // 当前人物的名称
			);
		}

		return true;
	}

	// 实例化数据库
	private function __execute() {

		// 实例化数据库
		$this->_serv_member = D('Common/Member', 'Service');
		$this->_serv_memberpart = D('Common/MemberDepartment', 'Service');
		$this->_serv_department = D('Sign/SignDepartment', 'Service');
		$this->_serv_batch = D('Sign/SignBatch', 'Service');
		$this->_serv_recode = D('Sign/SignRecord', 'Service');
		$this->_serv_detail = D('Sign/SignDetail', 'Service');

		return true;
	}

	/**
	 * 获取 签到记录 当前签到还是签退 是否允许签到
	 * @param $info 班次信息
	 * @param $records 签到记录
	 * @param $sb_set 当前签到还是签退
	 * @param $allow_sign 是否允许签到
	 * @return bool
	 */
	private function __get_first_data($info, &$records, &$sb_set, &$allow_sign) {

		$btime = '';
		$etime = '';
		// 获取 起始时间 结束时间 是否允许签到 当前签到还是签退
		$this->_serv_batch->first_property($info, $btime, $etime, $allow_sign, $sb_set);
		// 当天的签到情况
		$records = $this->_serv_recode->get_by_time($btime, $etime, $this->_m_uid);
		// 数据过滤
		$this->_serv_recode->sign_record_list($records);

		return true;
	}

	/**
	 * 获取 签到ID 班次上下班的时间 签到记录 签退记录 当前签到ID 当天上班签到记录的时间 当天下班签到记录的时间
	 * @param $records 签到记录
	 * @param $sb_set 当前签到还是签退
	 * @param $info 班次信息
	 * @param $sr_id 签到ID
	 * @param $p_set 班次上下班的时间
	 * @param $work_on 签到记录
	 * @param $work_off 签退记录
	 * @param $detail 当前签到ID
	 * @param $on_signtime_hi 当天上班签到记录的时间
	 * @param $off_signtime_hi 当天下班签到记录的时间
	 * @return bool
	 */
	private function __get_second_data($records, $sb_set, $info, &$sr_id, &$p_set, &$work_on, &$work_off, &$detail, &$on_signtime_hi, &$off_signtime_hi) {

		// 剩下的数据
		$work_on = null;
		$work_off = null;
		$sign_detail = null;
		$on_signtime_hi = null;
		$off_signtime_hi = null;
		$sr_id = null;

		// 上班卡/下班卡
		$up_down_data = $this->_serv_batch->up_down_work($records, $sb_set);
		$on_signtime_hi = $up_down_data['on_signtime_hi'];
		$work_on = $up_down_data['work_on'];
		$off_signtime_hi = $up_down_data['off_signtime_hi'];
		$work_off = $up_down_data['work_off'];

		// 判断那种卡未打, 设置对应的时间
		$up_down_data = $this->_serv_batch->is_no_get($work_on, $sb_set, $info, $on_signtime_hi, $off_signtime_hi);
		$on_signtime_hi = $up_down_data['on_signtime_hi'];
		$off_signtime_hi = $up_down_data['off_signtime_hi'];
		$p_set = $up_down_data['p_set'];

		//判断签到次数计算传过去的sr_id
		$re_data = $this->_serv_batch->qian_pass($work_on, $work_off);
		$detail = $re_data['detail'];
		$sr_id = $re_data['sr_id'];

		return true;
	}

	/**
	 * 获取 签到备注记录 签退备注记录 签到类型 部门ID 人物信息
	 * @param $sr_id 签到记录ID
	 * @param $info 班次信息
	 * @param $work_on 签到记录
	 * @param $work_on_detail 签到备注记录
	 * @param $work_off_detail 签退备注记录
	 * @param $sign_type 签到类型
	 * @param $department_name 部门名称
	 * @return bool
	 */
	private function __get_third_data($sr_id, $info, $work_on, &$work_on_detail, &$work_off_detail, &$sign_type, &$department_name) {

		// 备注
		$work_on_detail = array(); // 签到 备注
		$work_off_detail = array(); // 签退备注
		if (!empty($sr_id)) {
			// 获取备注
			$sign_detail = $this->_serv_detail->list_by_sr_id($sr_id);
			// 分类备注
			$this->_serv_batch->remark_classify($sign_detail, $work_on_detail, $work_off_detail);
		}

		// 跳到备注的方法
		$beizhu = $this->_serv_batch->beizhu($info, $work_on);
		$sign_type = $beizhu['sign_type'];

		// 获取部门缓存
		$all_department = &Cache::instance()->get('Common.department');
		$this->_member_data = $this->_get_member_batch($this->_login->user['m_uid'], $department);

		$department_name = $this->_serv_batch->get_cdname_by_batchid($all_department, $department);

		return true;
	}
}
