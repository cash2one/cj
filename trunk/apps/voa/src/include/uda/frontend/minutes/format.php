<?php
/**
 * voa_uda_frontend_minutes_format
 * 统一数据访问/会议记录应用/相关数据格式化
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_minutes_format extends voa_uda_frontend_minutes_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 格式化会议记录主表列表数据，含发起人用户信息
	 * @param array $list
	 * @return boolean
	 */
	public function minutes_list(&$list) {

		// 格式化会议记录列表数据
		foreach ($list as &$row) {
			$this->minutes($row);
		}

		return true;
	}

	/**
	 * 格式化会议参与人列表数据
	 * @param array $list
	 * @return boolean
	 */
	public function minutes_mem_list(&$list) {

		// 格式化参与人列表数据
		foreach ($list as &$row) {
			$this->minutes_mem($row);
		}

		return true;
	}

	/**
	 * 格式化回复列表数据，含回复人用户信息
	 * @param array $list
	 * @return boolean
	 */
	public function minutes_post_list(&$list) {

		// 格式化回复列表数据
		foreach ($list as &$row) {
			$this->minutes_post($row);
		}

		return true;
	}

	/**
	 * 格式化会议记录主表数据
	 * @param array $data
	 * @return boolean
	 */
	public function minutes(&$data) {

		/** 标题过滤 */
		$data['_subject'] = rhtmlspecialchars($data['mi_subject']);
		//创建时间，标准格式
		$data['_created'] = rgmdate($data['mi_created'], 'Y-m-d H:i');
		//创建时间，个性化格式
		$data['_created_u'] = rgmdate($data['mi_created'], 'u');

		return true;
	}

	/**
	 * 格式化会议记录参与人表数据
	 * @param array $data
	 * @return boolean
	 */
	public function minutes_mem(&$data) {

		//抄送时间，标准格式
		$data['_created'] = rgmdate($data['mim_created'], 'Y-m-d H:i');
		//抄送时间，个性化格式
		$data['_created_u'] = rgmdate($data['mim_created'], 'u');
		//状态描述文字
		$data['_status'] = isset($this->minutes_mem_status[$data['mim_status']]) ? $this->minutes_mem_status[$data['mim_status']] : '';

		return true;
	}

	/**
	 * 格式化会议记录评论/回复信息
	 * @param array $data
	 * @return boolean
	 */
	public function minutes_post(&$data) {

		/** 标题过滤 */
		$data['_subject'] = rhtmlspecialchars($data['mip_subject']);
		//转义消息内容代码
		$data['_message'] = bbcode::instance()->bbcode2html($data['mip_message']);
		//发表时间，标准格式
		$data['_created'] = rgmdate($data['mip_created'], 'Y-m-d H:i');
		//发表时间，个性化格式
		$data['_created_u'] = rgmdate($data['mip_created'], 'u');

		return true;
	}

}
