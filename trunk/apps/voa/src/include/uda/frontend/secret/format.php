<?php
/**
 * voa_uda_frontend_secret_format
 * 统一数据访问/秘密应用/相关数据格式化
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_secret_format extends voa_uda_frontend_secret_base {

	/**
	 * 格式化主题表列表数据
	 * @param array $list
	 * @return boolean
	 */
	public function secret_list(&$list) {

		// 格式化列表数据
		foreach ($list as &$row) {
			$this->secret($row);
		}

		return true;
	}

	/**
	 * 格式化内容表列表数据
	 * @param array $list
	 * @return boolean
	 */
	public function secret_post_list(&$list) {

		// 格式化列表数据
		foreach ($list as &$row) {
			$this->secret_post($row);
		}

		return true;
	}

	/**
	 * 格式化主题表数据
	 * @param array $data
	 * @return boolean
	 */
	public function secret(&$data) {

		// 发表时间，标准格式
		$data['_created'] = rgmdate($data['st_created'], 'Y-m-d H:i');
		// 发表时间，个性化格式
		$data['_created_u'] = rgmdate($data['st_created'], 'u');

		return true;
	}

	/**
	 * 格式化内容表数据
	 * @param array $data
	 * @return boolean
	 */
	public function secret_post(&$data) {

		// 转义正文内容代码
		$data['_message'] = bbcode::instance()->bbcode2html($data['stp_message']);
		// 发表时间，标准格式
		$data['_created'] = rgmdate($data['stp_created'], 'Y-m-d H:i');
		// 发表时间，个性化格式
		$data['_created_u'] = rgmdate($data['stp_created'], 'u');
		// 内容类型
		$data['_first'] = isset($this->post_first_type[$data['stp_first']]) ? $this->post_first_type[$data['stp_first']] : '';

		return true;
	}

}
