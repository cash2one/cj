<?php
/**
 * 报销数据格式化
 * $Author$
 * $Id$
 */

class voa_uda_frontend_reimburse_format extends voa_uda_frontend_reimburse_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 格式化报销信息列表
	 * @param array $list
	 * @return boolean
	 */
	public function reimburse_list(&$list) {
		foreach ($list as &$data) {
			$this->reimburse($data);
		}

		return true;
	}

	public function reimburse(&$data) {
		$timestamp = startup_env::get('timestamp');
		$data['_subject'] = rhtmlspecialchars($data['rb_subject']);
		$data['_time'] = rgmdate($data['rb_time']);
		$data['_time_u'] = rgmdate($data['rb_time'], 'u');
		$data['_expend'] = round($data['rb_expend'] / 100, 2);
		$data['_created'] = rgmdate(!empty($data['rb_created']) ? $data['rb_created'] : $timestamp);
		$data['_created_u'] = rgmdate(!empty($data['rb_created']) ? $data['rb_created'] : $timestamp, 'u');
		$data['_updated_u'] = rgmdate(!empty($data['rb_updated']) ? $data['rb_updated'] : $timestamp, 'u');
		$data['_status'] = (isset($data['rb_status']) && isset($this->reimburse_status[$data['rb_status']])) ? $this->reimburse_status[$data['rb_status']] : '';
		$data['_type'] = (isset($data['rb_type']) && isset($this->_sets['types'][$data['rb_type']])) ? $this->_sets['types'][$data['rb_type']] : '';
		/** 状态显示 */
		$data['_status_class'] = 'doing';
		if (isset($data['rb_status']) && voa_d_oa_reimburse::STATUS_APPROVE == $data['rb_status']) {
			$data['_status_tip'] = '已通过';
			$data['_status_class'] = '';
		} elseif (isset($data['rb_status']) && voa_d_oa_reimburse::STATUS_REFUSE == $data['rb_status']) {
			$data['_status_class'] = 'reject';
			$data['_status_tip'] = '已驳回';
		} else {
			$data['_status_tip'] = '审批中';
		}

		return true;
	}

	/**
	 * 格式化报销清单列表
	 * @param unknown $list
	 * @return boolean
	 */
	public function reimburse_bill_list(&$list) {
		foreach ($list as &$data) {
			$this->reimburse_bill($data);
		}

		return true;
	}

	public function reimburse_bill(&$data) {

		$data['_reason'] = bbcode::instance()->bbcode2html($data['rbb_reason']);
		$data['_created'] = rgmdate($data['rbb_created']);
		$data['_created_u'] = rgmdate($data['rbb_created'], 'u');
		// 格式化金额单位为：元
		$data['_expend'] = round($data['rbb_expend'] / 100, 2);
		$data['_time'] = rgmdate($data['rbb_time'] ? $data['rbb_time'] : startup_env::get('timestamp'));
		$data['_time_md'] = rgmdate($data['rbb_time'] ? $data['rbb_time'] : startup_env::get('timestamp'), 'm-d');
		$data['_time_u'] = rgmdate($data['rbb_time'] ? $data['rbb_time'] : startup_env::get('timestamp'), 'u');
		// 清单类型
		$data['_type'] = isset($this->_sets['types'][$data['rbb_type']]) ? $this->_sets['types'][$data['rbb_type']] : '-';
		unset($data['rbb_status']);
		//unset($data['rbb_type']);
		unset($data['rbb_deleted']);
		//unset($data['rbb_expend']);

		// 附件列表
		$data['_attachs'] = array();

		// 获取附件列表
		$serv_rbbat = &service::factory('voa_s_oa_reimburse_bill_attachment', array('pluginid' => startup_env::get('pluginid')));
		$attach_list = $serv_rbbat->fetch_all_by_rbb_ids($data['rbb_id']);
		if (empty($attach_list)) {
			return true;
		}

		if ($attach_list) {
			// 报销清单所关联的公共附件ID
			$at_ids = array();
			foreach ($attach_list as $v) {
				$at_ids[] = $v['at_id'];
			}

			$serv_at = &service::factory('voa_s_oa_common_attachment', array('pluginid' => 0));
			$common_attach_list = $serv_at->fetch_by_ids($at_ids);

			foreach ($attach_list as $v) {
				if (!isset($common_attach_list[$v['at_id']])) {
					continue;
				}
				$at = $common_attach_list[$v['at_id']];
				$data['_attachs'][$v['rbbat_id']] = array(
					'at_id' => $v['at_id'],// 公共文件附件ID
					'id' => $v['rbbat_id'], // 日报文件ID
					'filename' => $at['at_filename'],// 附件名称
					'filesize' => $at['at_filesize'],// 附件容量
					'description' => $at['at_description'],// 附件描述
					'isimage' => $at['at_isimage'] ? 1 : 0,// 是否是图片
					'url' => voa_h_attach::attachment_url($v['at_id'], 0),// 附件文件url
					'thumb' => $at['at_isimage'] ? voa_h_attach::attachment_url($v['at_id'], 45) : '',// 缩略图URL
				);
			}
		}

		return true;
	}

	/**
	 * 格式化报销回复信息列表
	 * @param unknown $list
	 * @return boolean
	 */
	public function reimburse_post_list(&$list) {
		foreach ($list as &$data) {
			$this->reimburse_post($data);
		}

		return true;
	}

	public function reimburse_post(&$data) {
		$data['_subject'] = rhtmlspecialchars($data['rbpt_subject']);
		$data['_created'] = rgmdate($data['rbpt_created']);
		$data['_created_u'] = rgmdate($data['rbpt_created'], 'u');
		$data['_message'] = bbcode::instance()->bbcode2html($data['rbpt_message']);
		return true;
	}

	/**
	 * 格式化报销审批进度列表
	 * @param unknown $list
	 * @return boolean
	 */
	public function reimburse_proc_list(&$list) {
		foreach ($list as &$data) {
			$this->reimburse_proc($data);
		}

		return true;
	}

	public function reimburse_proc(&$data) {
		$data['_remark'] = rhtmlspecialchars($data['rbpc_remark']);
		$data['_created'] = rgmdate($data['rbpc_created']);
		$data['_created_u'] = rgmdate($data['rbpc_created'], 'u');
		$data['_status'] = isset($this->proc_status[$data['rbpc_status']]) ? $this->proc_status[$data['rbpc_status']] : '';
		//$data['_type'] = isset($this->_sets['types'][$data['rbpc_type']]) ? $this->_sets['types'][$data['rbpc_type']] : '';
		/** 状态显示 */
		$data['_status_class'] = 'wait';
		if (voa_d_oa_reimburse_proc::STATUS_APPROVE == $data['rbpc_status'] || voa_d_oa_reimburse_proc::STATUS_TRANSMIT == $data['rbpc_status']) {
			$data['_status_tip'] = '已通过';
			$data['_status_class'] = 'succ';
		} else if (voa_d_oa_reimburse_proc::STATUS_REFUSE == $data['rbpc_status']) {
			$data['_status_class'] = 'succ';
			$data['_status_tip'] = '已驳回';
		} else {
			$data['_status_tip'] = '审批中';
		}

		return true;
	}
}
