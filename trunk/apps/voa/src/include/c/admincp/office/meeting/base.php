<?php
/**
 * voa_c_admincp_office_meeting_base
 * 会议通后台管理基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_meeting_base extends voa_c_admincp_office_base {

	/** 会议室容量大小文字描述 */
	protected $_meeting_room_volum_descriptions = array(
			voa_d_oa_meeting_room::VOLUME_SMALL => '小',
			voa_d_oa_meeting_room::VOLUME_MIDDLE => '中',
			voa_d_oa_meeting_room::VOLUME_BIG => '大'
	);

	/** 会议室最多记录数 */
	protected $_meeting_room_count_max = voa_d_oa_meeting_room::COUNT_MAX;

	/** 会议状态 */
	protected $_status_descriptions = array(
			voa_d_oa_meeting::STATUS_NORMAL => '新创建',
			voa_d_oa_meeting::STATUS_UPDATE => '已更新',
			voa_d_oa_meeting::STATUS_CANCEL => '已取消',
			voa_d_oa_meeting::STATUS_REMOVE => '已删除',
	);

	/** 会议取消时的状态值mt_status */
	protected $_cancel_status_value = voa_d_oa_meeting::STATUS_CANCEL;

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$navLinks = array();
		$navLinkBaseUrl = $this->cpurl($this->_module, $this->_operation, '', $this->_module_plugin_id);
		foreach ( $this->_subop_list[$this->_module][$this->_operation] AS $_k=>$_m ) {
			if ( in_array($_k, array('list', 'mrlist', 'mradd')) ) {
				$_m['url'] = $navLinkBaseUrl.$_k;
				$navLinks[$_k] = $_m;
			}
		}
		$this->view->set('navmenu', array('links'=>$navLinks));
		return true;
	}

	/**
	 * 返回指定id的会议室详情
	 * 如不存在则返回会议室字段默认值
	 * @param number $mr_id
	 * @return array
	 */
	protected function _get_meeting_room($cp_pluginid, $mr_id = 0) {
		if ( $mr_id > 0 && ($meetingRoom = $this->_service_single('meeting_room', $cp_pluginid, 'fetch_by_id', $mr_id)) ) {
			return $meetingRoom;
		} else {
			return $this->_service_single('meeting_room', $cp_pluginid, 'fetch_all_field', true);
		}
	}

	/**
	 * 提交编辑或新增会议室
	 * @param array $meetingRoom 会议室原始数据（编辑为之前数据，新增则为默认字段数据）
	 * @param array $param 变更的数据
	 * @param number $mr_id
	 */
	protected function _meeting_room_submit_edit($cp_pluginid, $meetingRoom, $param, $mr_id = 0) {

		/** 待更新的数据 */
		$update = array();

		/** 检查会议室名称 */
		if ( !isset($param['mr_name']) ) {
			$this->message('error', '会议室名称必须填写');
		} elseif ( !validator::is_len_in_range($param['mr_name'], 1, 30) ) {
			$this->message('error', '会议室名称长度不能超过30字节');
		} elseif ( $param['mr_name'] != rhtmlspecialchars($param['mr_name']) ) {
			$this->message('error', '会议室名称不能包含特殊字符');
		} elseif ( $param['mr_name'] != $meetingRoom['mr_name'] ) {
			$update['mr_name'] = $param['mr_name'];
		}

		/** 检查会议室地址 */
		if ( !isset($param['mr_address']) ) {
			$this->message('error', '会议室地址必须填写');
		} elseif ( !validator::is_len_in_range($param['mr_address'], 1, 255) ) {
			$this->message('error', '会议室地址长度不能超过200字节');
		} elseif ( $param['mr_address'] != $meetingRoom['mr_address'] ) {
			$update['mr_address'] = $param['mr_address'];
		}
		
		/** 检查会议室楼层 */
		if ( !isset($param['mr_floor']) ) {
			$this->message('error', '会议室楼层必须填写');
		} elseif ( !validator::is_int($param['mr_floor']) ) {
			$this->message('error', '会议室楼层必须为正整数');
		}
		$update['mr_floor'] = $param['mr_floor'];

		/** 检查会议室容纳人数填写 */
		if ( isset($param['mr_galleryful']) && $param['mr_galleryful'] != $meetingRoom['mr_galleryful'] ) {
			if ( !validator::is_len_in_range($param['mr_galleryful'], 0, 255) ) {
				$this->message('error', '会议室容纳人数输入文字不能超过200字节');
			}
			$update['mr_galleryful'] = $param['mr_galleryful'];
		}

		/** 检查会议室设备填写 */
		if ( isset($param['mr_device']) && $param['mr_device'] != $meetingRoom['mr_device'] ) {
			if ( !validator::is_len_in_range($param['mr_device'], 0, 255) ) {
				$this->message('error', '会议室设备文字长度不能超过200字节');
			}
			$update['mr_device'] = $param['mr_device'];
		}

		/** 检查会议室规模选择 */
		/*if ( isset($param['mr_volume']) && $param['mr_volume'] != $meetingRoom['mr_volume'] ) {
			if ( $param['mr_volume'] != 0 && !isset($this->_meeting_room_volum_descriptions[$param['mr_volume']]) ) {
				$this->message('error', '会议室规模选择错误');
			}
			$update['mr_volume'] = $param['mr_volume'];
		}
*/

		/** 检查会议室预定时间范围设置 */
		if ( isset($param['mr_timestart']) && isset($param['mr_timeend']) && ( $param['mr_timestart'] != $meetingRoom['mr_timestart'] || $param['mr_timeend'] != $meetingRoom['mr_timeend'] ) ) {
			$timestart = $this->_format_time($param['mr_timestart']);
			$timeend = $this->_format_time($param['mr_timeend']);
			if ( str_replace(':', '', $timestart) > str_replace(':', '', $timeend) ) {
				$update['mr_timestart'] = $timeend;
				$update['mr_timeend'] = $timestart;
			} else {
				$update['mr_timestart'] = $timestart;
				$update['mr_timeend'] = $timeend;
			}
		}

		if ( empty($update) ) {
			$this->message('error', '会议室信息未改动，无须进行更新');
		}

		if ( $mr_id ) {
			$this->_service_single('meeting_room', $cp_pluginid, 'update', $update, array('mr_id'=>$mr_id));
			$message = '编辑会议室信息操作完毕';
		} else {
			if ( $this->_service_single('meeting_room', $cp_pluginid, 'count_all', true) >= $this->_meeting_room_count_max ) {
				$this->message('error', '系统限制只能最多添加'.$this->_meeting_room_count_max.'个会议室');
			}
			$update['mr_code'] = $param['mr_code'];
			$this->_service_single('meeting_room', $cp_pluginid, 'insert', $update);
			$message = '新增会议室操作完毕';
		}

		// 更新缓存
		voa_h_cache::get_instance()->get('plugin.meeting.room', 'oa', true);

		$this->message('success', $message, get_referer($this->cpurl($this->_module, $this->_operation, 'mrlist', $this->_module_plugin_id)), false);
	}

	/**
	 * 格式化一个时间字符串为标准格式
	 * @param string $string
	 * @return string
	 */
	protected function _format_time($string) {
		$h = $i = $s = 0;
		@list($h,$i,$s) = explode(':', $string);
		$h = rintval($h);
		$i = rintval($i);
		$s = rintval($s);
		if ( $h > 23 || $h < 0 ) {
			$h = 0;
		}
		if ( $i > 59 || $i < 0 ) {
			$i = 0;
		}
		if ( $s > 59 || $s < 0 ) {
			$s = 0;
		}
		return sprintf('%02s',$h).':'.sprintf('%02s',$i).':'.sprintf('%02s',$s);
	}

	/**
	 * 返回全部会议室列表
	 * @return array
	 */
	protected function _meeting_room_list($cp_pluginid) {
		if ( isset($this->_meeting_list) ) {
			return $this->_meeting_list;
		}
		$list	=	array();
		foreach ( $this->_service_single('meeting_room', $cp_pluginid, 'fetch_all', $this->_meeting_room_count_max) AS $mr_id => $mr ) {
			$mr['_timestart'] = substr($mr['mr_timestart'], 0, -3);
			$mr['_timeend'] = substr($mr['mr_timeend'], 0, -3);
			$mr['_volume'] = isset($this->_meeting_room_volum_descriptions[$mr['mr_volume']]) ? $this->_meeting_room_volum_descriptions[$mr['mr_volume']] : '';
			$mr['_url'] = $this->cpurl('office', 'meeting', 'mredit', $this->_module_plugin_id, array('mr_id'=>$mr_id));
			$list[$mr_id] = $mr;
		}
		return $this->_meeting_list = $list;
	}

	/**
	 * 格式化会议信息输出
	 * @param array $meeting
	 * @return array
	 */
	protected function _format_meeting($meeting) {
		$meetingRoomList = $this->_meeting_room_list($this->_module_plugin_id);
		$meeting['_updated'] = rgmdate($meeting['mt_updated'], 'Y-m-d H:i');
		if ( isset($meetingRoomList[$meeting['mr_id']]) ) {
			$meeting['_meeting_room'] =  $meetingRoomList[$meeting['mr_id']]['mr_name'];
			$meeting['_meeting_room_url'] = $meetingRoomList[$meeting['mr_id']]['_url'];
		} else {
			$meeting['_meeting_room'] = '';
			$meeting['_meeting_room_url'] = '';
		}
		$meeting['_begintime'] = rgmdate($meeting['mt_begintime'], 'Y-m-d H:i');
		$meeting['_endtime'] = rgmdate($meeting['mt_endtime'], 'Y-m-d H:i');
		$meeting['_created'] = rgmdate($meeting['mt_created'], 'Y-m-d H:i');
		$meeting['_status'] = isset($this->_status_descriptions[$meeting['mt_status']]) ? $this->_status_descriptions[$meeting['mt_status']] : '';
		return $meeting;
	}

}
