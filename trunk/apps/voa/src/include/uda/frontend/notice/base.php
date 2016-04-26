<?php
/**
 * voa_uda_frontend_notice_base
 * 统一数据访问/通知公告/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_notice_base extends voa_uda_frontend_base {

	public $serv_notice = null;
	public $serv_notice_to = null;
	public $serv_notice_read = null;


	public function __construct() {
		parent::__construct();
		$this->_sets = voa_h_cache::get_instance()->get('plugin.notice.setting', 'oa');
		if ($this->serv_notice === null) {
			$this->serv_notice = &service::factory('voa_s_oa_notice');
			$this->serv_notice_read = &service::factory('voa_s_oa_notice_read');
			$this->serv_notice_to = &service::factory('voa_s_oa_notice_to');
		}
	}

	/**
	 * 检查公告标题
	 * @param string &$subject 标题文字
	 * @param array &$notice 新公告数据数组
	 * @param array &$history 旧公告数据数组
	 * @return boolean
	 */
	public function val_subject(&$subject, &$notice, $history = array()) {
		if (isset($history['nt_subject']) && $history['nt_subject'] == $subject) {
			// 字段未发生改变
			unset($notice['nt_subject']);
			return true;
		}
		$subject = (string)$subject;
		$subject = trim($subject);
		if (!validator::is_string_count_in_range($subject, 1, 80)) {
			$this->errmsg('101', '公告标题长度应介于 1到80 个字符之间');
			return false;
		}
		$notice['nt_subject'] = $subject;
		return true;
	}

	/**
	 * 检查公告内容
	 * @param string &$message 内容文字
	 * @param array &$notice 新公告数据数组
	 * @param array &$history 旧公告数据数组
	 * @return boolean
	 */
	public function val_message(&$message, &$notice, $history = array()) {
		if (isset($history['nt_message']) && $history['nt_message'] == $message) {
			// 字段未发生改变
			unset($notice['nt_message']);
			return true;
		}
		$message = (string)$message;

		$notice['nt_message'] = $message;
		return true;
	}

	/**
	 * 检查发布者
	 * @param string &$author 发布者文字内容
	 * @param array &$notice 新公告数据数组
	 * @param array &$history 旧公告数据数组
	 * @return boolean
	 */
	public function val_author(&$author, &$notice, $history = array()) {
		if (isset($history['nt_author']) && $history['nt_author'] == $author) {
			// 字段未发生改变
			unset($notice['nt_author']);
			return true;
		}
		$author = (string) $author;
		$author = trim($author);
		if (!validator::is_string_count_in_range($author, -1, 54)) {
			$this->errmsg('103', '填写发布人的长度不能超过 54个字符');
			return false;
		}
		$notice['nt_author'] = $author;
		return true;
	}

	/**
	 * 检查标签
	 * @param string &$tag 标签文字
	 * @param array &$notice 新公告数据数组
	 * @param array &$history 旧公告数据数组
	 * @return boolean
	 */
	public function var_tag(&$tag, &$notice, $history = array()) {
		if (isset($history['nt_tag']) && $history['nt_tag'] == $tag) {
			// 字段未发生改变
			unset($notice['nt_tag']);
			return true;
		}
		$tag = (string)$tag;
		$tag = trim($tag);
		if (!validator::is_string_count_in_range($tag, -1, 54)) {
			$this->errmsg('104', '标签长度不能超过 54个字符');
			return false;
		}
		$notice['nt_tag'] = $tag;
		return true;
	}

	/**
	 * 检查整理公告接收部门
	 * @param string | array $receiver m_uid字符串或数组
	 * @param array &$notice 新公告数据数组
	 * @param array &$history 旧公告数据数组
	 * @return boolean
	 */
	public function var_receiver(&$receiver, &$notice, $history = array()) {

		if (is_scalar($receiver)) {
			// 如果给出的值是字符串，则转为数组

			if (empty($receiver)) {
				// 为空
				$receiver = array();
				if (!$history['nt_receiver'] || $history['nt_receiver'] == serialize($receiver)) {
					// 未发生改变
					unset($notice['nt_receiver']);
				} else {
					$notice['nt_receiver'] = array();
				}
				return true;
			}
			$receiver = explode(',', $receiver);
		}

		if (isset($receiver[0])) {
			// 给所有人发送
			$notice['nt_receiver'] = array();
		}

		// 新的接收部门
		$new_receiver = array();
		foreach ($receiver as $v) {
			$v = trim($v);
			if ($v && is_numeric($v)) {
				$new_receiver[] = $v;
			}
		}
		$receiver = $new_receiver;
		unset($new_receiver);
		if (empty($receiver)) {
			// 为空
			$receiver = array();
			if (!$history['nt_receiver'] || $history['nt_receiver'] == serialize($receiver)) {
				// 未发生改变
				unset($notice['nt_receiver']);
			} else {
				$notice['nt_receiver'] = array();
			}
			return true;
		}

		// 通过cid找到部门
		$serv = &service::factory('voa_s_oa_common_department');
		$receiver = array_keys($serv->fetch_all_by_key($receiver));

		if (empty($receiver)) {
			// 为空
			$receiver = array();
			if (!$history['nt_receiver'] || $history['nt_receiver'] == serialize($receiver)) {
				// 未发生改变
				unset($notice['nt_receiver']);
			} else {
				$notice['nt_receiver'] = array();
			}
			return true;
		}

		$new_data = array();
		// 整理接收部门
		foreach ($receiver as $cd_id) {
			$new_data[$cd_id] = $cd_id;
		}

		if ($history['nt_receiver'] == serialize($new_data)) {
			// 未发生改变
			unset($notice['nt_receiver']);
			return true;
		}

		$notice['nt_receiver'] = $new_data;
		return true;
	}

	/**
	 * 检查整理重复提醒间隔时间（不能小于半小时）
	 * @param number &$repeattimestamp 重复提醒时间间隔（单位：秒）
	 * @param array &$notice 新公告数据数组
	 * @param array &$history 旧公告数据数组
	 * @return boolean
	 */
	public function val_repeattimestamp(&$repeattimestamp, &$notice, $history = array()) {
		$repeattimestamp = (int)$repeattimestamp;
		if ($repeattimestamp < 1800 && $repeattimestamp != 0) {
			$repeattimestamp = 1800;
		}
		if (isset($history['nt_repeattimestamp']) && $history['nt_repeattimestamp'] == $repeattimestamp) {
			// 字段未发生改变
			unset($notice['nt_repeattimestamp']);
			return true;
		}
		$notice['nt_repeattimestamp'] = $repeattimestamp;
		return true;
	}

}
