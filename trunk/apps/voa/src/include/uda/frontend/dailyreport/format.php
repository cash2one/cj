<?php
/**
 * voa_uda_frontend_dailyreport_format
 * 统一数据访问/日报应用/数据格式化
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_dailyreport_format extends voa_uda_frontend_dailyreport_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 格式化日报主表数据
	 * @param array $dailyreport
	 * @param array $member
	 * @return boolean
	 */
	public function format(&$dailyreport, $member = array()) {
		/** 发起时间 */
		$dailyreport['_created'] = rgmdate($dailyreport['dr_created'], 'Y-m-d H:i');
		/** 个性化发起时间 */
		$dailyreport['_created_u'] = rgmdate($dailyreport['dr_created'], 'u');
		/** 发起人真实姓名 */
		$dailyreport['_realname'] = '';
		/** 发起人所在部门名称 */
		$dailyreport['_department'] = '';
		/** 发起人担任职务名称 */
		$dailyreport['_job'] = '';
		/** 报告时间 */
		$dailyreport['_reporttime'] = rgmdate($dailyreport['dr_reporttime'], 'Y-m-d');
		$dailyreport['_reporttime_fmt'] = voa_h_func::date_fmt('Y m d w', $dailyreport['dr_reporttime']);
		/** 标题 */
		$dailyreport['_subject'] = rhtmlspecialchars($dailyreport['dr_subject']);
		/** 日报详情 */
		$dailyreport['_message'] = '';
		if ($member) {
			$uda = &uda::factory('voa_uda_frontend_member_format');
			$uda->format($member);
			$dailyreport['_realname'] = $member['_realname'];
			$dailyreport['_department'] = $member['_department'];
			$dailyreport['_job'] = $member['_job'];
		}
		if (isset($dailyreport['drp_message'])) {
			$dailyreport['_message'] = $dailyreport['drp_message'];//bbcode::instance()->bbcode2html($dailyreport['drp_message']);
		}
		return true;
	}

	/**
	 * 格式化日报/回复详情
	 * @param array $data
	 */
	public function dailyreport_post(&$data) {
		$data['_subject'] = rhtmlspecialchars($data['drp_subject']);
		//$data['_message'] = bbcode::instance()->bbcode2html($data['drp_message']);
		$data['_message'] = $data['drp_message'];
		$data['_created_u'] = rgmdate($data['drp_created'], 'u');
		return true;
	}

	/**
	 * 格式化日报列表输出
	 * @param array $data
	 */
	public function format_posts($posts) {

		$result = array();
		if ($posts) {
			foreach ($posts as $k => $post) {
				$result[$k]['dr_id'] = $post['dr_id'];
				$result[$k]['uid'] = $post['m_uid'];
				$result[$k]['reporttime'] = rgmdate($post['dr_reporttime'], 'Y-m-d');
				$result[$k]['username'] = rhtmlspecialchars($post['m_username']);
				$result[$k]['dr_subject'] =  $post['dr_subject'];
				$result[$k]['is_read'] = $post['is_read'];
			}
		}
		return $result;
	}

	/**
	 * 格式化日报评论列表输出
	 * @param array $data
	 */
	public function format_post_reply($posts) {
		$result = array();
		if ($posts) {
			foreach ($posts as $k => $post) {
				$result[$k]['drp_id'] = $post['drp_id'];
				$result[$k]['dr_id'] = $post['dr_id'];
				$result[$k]['uid'] = $post['m_uid'];
				$result[$k]['username'] = rhtmlspecialchars($post['m_username']);
				$result[$k]['message'] = $post['drp_message'];
				$result[$k]['created'] = $post['drp_created'];
			}
		}
		return $result;
	}

}
