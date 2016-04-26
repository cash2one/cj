<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/2/27
 * Time: 下午2:20
 */

namespace Stat\Model;

class StatPluginTotalModel extends AbstractModel {

	static $_identifier_name = array(
		'project' => '任务',
		'minutes' => '会议记录',
		//'namecard' => '名片夹',
		//'weekreport' => '周报',
		'vnote' => '备忘录',
		'askfor' => '审批',
		//'todo' => '代办事项',
		'thread' => '同事社区',
		//'vote' => '微评选',
		'reimburse' => '报销',
		'dailyreport' => '工作报告',
		//'plan' => '日程报告',
		//'secret' => '秘密',
		'sign' => '考勤',
		'meeting' => '订会议室',
		'askoff' => '请假',
		//'notice' => '通知公告',
		//'footprint' => '销售轨迹',
		'addressbook' => '通讯录',
		'inspect' => '巡店',
		//'file' => '文件',
		//'productive' => '活动反馈',
		'workorder' => '移动派单',
		//'travel' => '微分销',
		//'train' => '培训',
		'showroom' => '陈列',
		//'superreport' => '超级报表',
		'news' => '新闻公告',
		'nvote' => '投票调研',
		'activity' => '活动报名',
		'express' => '快递助手',
		'campaign' => '活动推广',
		//'redpack' => '企业红包',
		//'sale' => '销售管理',
		'invite' => '邀请人员',
		'chatgroup' => '同事聊天',
		'blessingredpack' => '祝福红包',
		'exam' => '考试',
		'jobtrain' => '企业培训',

	);

	//不统计的应用
	static $except = array(
		'namecard', // => '名片夹',
		'weekreport', // => '周报',
		'todo',  //=> '代办事项',
		'vote', // => '微评选',
		'plan', // => '日程报告',
		'secret', // => '秘密',
		'notice', // => '通知公告',
		'footprint', // => '销售轨迹',
		'file', // => '文件',
		'productive', // => '活动反馈',
		'travel', // => '微分销',
		'train', // => '培训',
		'superreport', // => '超级报表',
		'redpack', // => '企业红包',
		'sale', // => '销售管理',
	);

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 根据时间和唯一标识查询
	 * @param int $start 开始时间
	 * @param int $end 结束时间
	 * @param string $pg_identifier 唯一标识
	 * @param array $page_option 分页参数
	 * @return array|bool
	 */
	public function list_by_time_or_identifier($start, $end, $pg_identifier, $page_option) {

		$sql = "SELECT * FROM __TABLE__";

		// 设置条件
		$where = array(
			'status < ?',
		);
		$where_params = array(
			$this->get_st_delete(),
		);
		if (!empty($start)) {
			$where[] = 'time > ?';
			$where_params[] = $start;
		}
		if (!empty($end)) {
			$where[] = 'time <= ?';
			$where_params[] = $end;
		}
		if (!empty($pg_identifier)) {
			$where[] = 'pg_identifier = ?';
			$where_params[] = $pg_identifier;
		}

		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}
		$order_option = array('time' => 'DESC');

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where) . "{$orderby}{$limit}", $where_params);
	}

	/**
	 * 根据时间和唯一标识统计
	 * @param int $start 开始时间
	 * @param int $end 结束时间
	 * @param array $pg_identifier 唯一标识
	 * @return array
	 */
	public function count_by_time_or_identifier($start, $end, $pg_identifier) {

		$sql = "SELECT COUNT(*) FROM __TABLE__";

		// 设置条件
		$where = array(
			'status < ?',
		);
		$where_params = array(
			$this->get_st_delete(),
		);
		if (!empty($start)) {
			$where[] = 'time > ?';
			$where_params[] = $start;
		}
		if (!empty($end)) {
			$where[] = 'time <= ?';
			$where_params[] = $end;
		}
		if (!empty($pg_identifier)) {
			$where[] = 'pg_identifier = ?';
			$where_params[] = $pg_identifier;
		}

		return $this->_m->result($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

	/**
	 * 根据时间统计所有应用数据
	 * @param $start
	 * @param $end
	 * @param $field
	 * @return array
	 */
	public function total_plugin_data($start, $end, $field) {

		$sql = "SELECT {$field} FROM __TABLE__";

		// 设置条件
		$where = array(
			'status < ?',
		);
		$where_params = array(
			$this->get_st_delete(),
		);
		if (!empty($start)) {
			$where[] = 'time > ?';
			$where_params[] = $start;
		}
		if (!empty($end)) {
			$where[] = 'time <= ?';
			$where_params[] = $end;
		}

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

	/**
	 * 根据时间查询
	 * @param $date
	 * @return array
	 */
	public function get_by_conds_time($date, $pg_identifier) {

		$sql = "SELECT * FROM __TABLE__";

		// 设置条件
		$where = array(
			'status < ?',
		);
		$where_params = array(
			$this->get_st_delete(),
		);
		if (!empty($date['s_time'])) {
			$where[] = 'time > ?';
			$where_params[] = $date['s_time'];
		}
		if (!empty($date['e_time'])) {
			$where[] = 'time <= ?';
			$where_params[] = $date['e_time'];
		}
		if (!empty($pg_identifier)) {
			$where[] = 'pg_identifier = ?';
			$where_params[] = $pg_identifier;
		}

		return $this->_m->fetch_row($sql . ' WHERE ' . implode(' AND ', $where), $where_params);

	}
}