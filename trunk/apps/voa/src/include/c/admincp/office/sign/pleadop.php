<?php

/**
 * voa_c_admincp_office_sign_pleadop
 * 企业后台/微办公管理/考勤签到/处理申诉
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_sign_pleadop extends voa_c_admincp_office_sign_base {

	/** 当前处理的申诉id */
	protected $_sp_id = 0;
	/** 当前处理的申诉信息 */
	protected $_plead = array();
	/** 定义星期（只用于显示） */
	protected $_week_set = array(
		1 => '星期一',
		2 => '星期二',
		3 => '星期三',
		4 => '星期四',
		5 => '星期五',
		6 => '周六',
		7 => '周日'
	);

	public function execute() {

		$sp_id = $this->request->get('sp_id');
		$sp_id = rintval($sp_id, false);
		if ($sp_id <= 0 || !($signPlead = self::_get_sign_plead($this->_module_plugin_id, $sp_id))) {
			$this->message('error', '指定申诉信息不存在或已删除');
		}
		$this->_sp_id = $sp_id;
		$this->_plead = $signPlead;

		/** 整理出本次申诉的，时间范围以及，每日的申诉内容 */
		list($minTimestamp, $maxTimestamp, $dayPleads) = $this->_get_day_plead_list();
		$recordList = array();
		if (!empty($dayPleads)) {
			/** 在计算的时间范围外延展一天，避免漏掉数据 */
			$minTimestamp -= 86400;
			$maxTimestamp += 86400;
			/** 获取该范围内的签到记录数据 */
			$recordList = $this->_get_timearea_sign_record($this->_module_plugin_id, $minTimestamp, $maxTimestamp);
		}

		/** 提交处理请求 */
		if ($this->_is_post()) {
			$this->_sign_plead_operation_submit($this->_module_plugin_id, $dayPleads, $recordList);
		}

		/** 签到状态的选择下拉框选项 */
		//此处构造html为了节省模板的输出处理
		$statusOptions = '';
		$signStatus = $this->_sign_status;
		unset($signStatus[$this->_sign_status_set['remove']], $signStatus[$this->_sign_status_set['unknown']]);
		foreach ($signStatus AS $_statusValue => $_statusName) {
			$statusOptions .= '<option value="' . $_statusValue . '"' . ($_statusValue == $this->_sign_status_set['work'] ? ' selected="selected"' : '') . '>' . $_statusName . '</option>';
		}

		$this->view->set('dayPleads', $dayPleads);
		$this->view->set('recordList', $recordList);
		$this->view->set('signType', $this->_sign_type);
		$this->view->set('signStatus', $signStatus);
		$this->view->set('formActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('sp_id' => $this->_sp_id)));
		$this->view->set('plead', $this->_plead);
		$this->view->set('statusOptions', $statusOptions);
		$this->view->set('signStatusSet', $this->_sign_status_set);

		$this->output('office/sign/pleadop');
	}

	/**
	 * 格式化申诉消息内容
	 * @param unknown $message
	 * @return mixed
	 */
	protected function _format_plead_message($message) {
		$message = preg_replace('/^(\s)?(:)?/', '', $message);

		return trim($message);
	}

	/**
	 * 获取指定申诉信息
	 * @param number $sp_id
	 * @return boolean|unknown
	 */
	protected function _get_sign_plead($cp_pluginid, $sp_id) {
		$plead = $this->_service_single('sign_plead', $cp_pluginid, 'fetch_by_id', $sp_id);
		if (empty($plead)) {
			return false;
		}

		return parent::_format_sign_plead($plead);
	}

	/**
	 * 重新整理分析每日的申诉内容，以及当次申诉的时间最早和最晚区间
	 * @return array(minTime, maxTime, list)
	 */
	protected function _get_day_plead_list() {

		/** 每日申诉内容 array('date'=>'', 'timestamp'=>'', 'message'=>'') */
		$dayPleads = array();

		/** 当次提交的月份（该值为安全可靠值），整理为yyyy-mm格式 */
		$currentDate = $this->_plead['sp_year'] . '-' . sprintf('%02s', $this->_plead['sp_month']);

		/** 整理全部申诉内容 */
		$data = $this->_plead['sp_message'];
		$data = str_replace(array("\r\n", "\r"), "\n", $data);
		$data = preg_replace("/\n+/i", "\n", $data);
		$data = trim($data);
		$data = explode("\n", trim($data));

		/** 当次处理的申诉最早时间戳 */
		$minTimestamp = 0;
		/** 当次处理的申诉最晚时间戳 */
		$maxTimestamp = 0;

		/** 循环遍历分析每日的申诉内容 */
		$lineNum = 0;
		foreach ($data AS $k => $line) {
			if (preg_match('/^(\d{4}-\d{1,2}-\d{1,2})(.+?)$/s', $line, $match)) {
				//以日期开头

				/** 时期格式不正确则忽略 */
				if (!validator::is_date($match[1])) {
					continue;
				}

				/** 该天的Unix时间戳 */
				$timestamp = rstrtotime($match[1]);

				/** 格式化当日的日期文本格式 */
				$date = rgmdate($timestamp, 'Y-m-d');

				/** 判断当日是否在本次处理的月份范围内 */
				if (strpos($date, $currentDate) !== 0) {
					continue;
				}

				/** 初始化当次处理的最早时间戳 */
				if ($minTimestamp == 0) {
					$minTimestamp = $timestamp;
				}
				/** 初始化当次处理的最晚时间戳 */
				if ($maxTimestamp == 0) {
					$maxTimestamp = $timestamp;
				}

				/** 设置最早的时间戳 */
				if ($minTimestamp > $timestamp) {
					$minTimestamp = $timestamp;
				}

				/** 设置最晚的时间戳 */
				if ($maxTimestamp < $timestamp) {
					$maxTimestamp = $timestamp;
				}

				/** 星期 */


				/** 增加一天记录 */
				$lineNum ++;
				$dayPleads[$lineNum]['date'] = $date;
				$dayPleads[$lineNum]['week'] = $this->_week_set[rgmdate($timestamp, 'N')];
				$dayPleads[$lineNum]['timestamp'] = $timestamp;
				$dayPleads[$lineNum]['message'] = self::_format_plead_message($match[2]);

			} else {
				//非日期开头，则可能是上一天的换行，叠加到上一天
				if (isset($dayPleads[$lineNum])) {
					$dayPleads[$lineNum]['message'] .= "\n" . self::_format_plead_message($line);
				}
			}
		}
		$tmp = $dayPleads;
		/** 重新整理申诉以日期为键名 */
		$dayPleads = array();
		foreach ($tmp AS $_data) {
			$dayPleads[$_data['date']] = $_data;
		}
		unset($tmp);

		return array($minTimestamp, $maxTimestamp, $dayPleads);
	}

	/**
	 * 获取并格式指定时间范围内的签到数据
	 * 输出格式化为以日期为一级键名、签到类型为二级键名的数组
	 * @param number $min
	 * @param number $max
	 * @return array
	 */
	protected function _get_timearea_sign_record($cp_pluginid, $min, $max) {
		$list = array();
		$tmp = $this->_service_single('sign_record', $cp_pluginid, 'fetch_by_uid_time', $this->_plead['m_uid'], $min, $max);
		foreach ($tmp AS $_id => $_data) {
			$_data = parent::_format_sign_record($_data);

			$list[$_data['_signdate']][$_data['sr_type']] = $_data;
		}

		return $list;
	}

	/**
	 * 处理提交的请求
	 * @param array $dayPleads
	 * @param array $recordList
	 */
	protected function _sign_plead_operation_submit($cp_pluginid, $dayPleads, $recordList) {

		/** 设置该申诉为已处理 */
		$spData = array('sp_status' => $this->_sign_plead_status_set['done']);
		/** 提交后返回的链接 */
		$backurl = get_referer($this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('sp_id' => $this->_sp_id)));

		/** 如果没有读取到有效的申诉记录，则直接将本申诉设置为已处理 */
		if (empty($dayPleads)) {
			if ($this->_sign_plead_status_set['done'] != $this->_plead['sp_status']) {
				$this->_service_single('sign_plead', $cp_pluginid, 'update', $spData, array('sp_id' => $this->_sp_id));
			}
			$this->message('success', '处理申诉操作完毕', $backurl, false);
		}

		$sr_status = $this->request->post('sr_status');
		$sd_message = $this->request->post('sd_message');
		!is_array($sr_status) && $sr_status = array();
		!is_array($sd_message) && $sd_message = array();

		/** 初始化待变更的数据 */
		$insert = $update = array();
		/** 初始化待写入的变更日志 */
		$detail = array();

		foreach ($sr_status AS $_date => $_typeData) {
			/** 提交了未申诉的日期，则认为是非法的，跳过 */
			if (!isset($dayPleads[$_date])) {
				continue;
			}
			/** 签到类型数据非数组，则跳过 */
			if (!is_array($_typeData)) {
				continue;
			}
			foreach ($_typeData AS $_type => $_status) {
				/** 非法的签到类型，跳过 */
				if (!isset($this->_sign_type[$_type])) {
					continue;
				}
				/** 如果设置的状态未定义，则跳过 */
				if (!isset($this->_sign_status[$_status])) {
					continue;
				}
				/** 如果设置的状态是 未知 或 删除 则跳过*/
				if ($_status == $this->_sign_status_set['unknown'] || $_status == $this->_sign_status_set['remove']) {
					continue;
				}

				/** 获取到上下班时间，用来设置签到时间 */
				$time = rgmdate(startup_env::get('timestamp'), 'H:i');
				if ($_type == $this->_sign_type_set['on']) {
					//上班
					$time = !empty($this->_sign_setting['work_begin_hi']) ? $this->_sign_setting['work_begin_hi'] : '';
				} elseif ($_type == $this->_sign_type_set['off']) {
					//下班
					$time = !empty($this->_sign_setting['work_end_hi']) ? $this->_sign_setting['work_end_hi'] : '';
				}
				$time .= ':00';

				/** 处理申诉的备忘信息 */
				$detail_reason = '';
				/** 如果管理员手动填写了备忘信息 */
				if (isset($sd_message[$_date][$_type]) && is_scalar($sd_message[$_date][$_type]) && !empty($sd_message[$_date][$_type])) {
					$detail_reason = $sd_message[$_date][$_type];
				} else {
					//未填写，则将处理状态变化，以及用户填写的内容写入
					$detail_reason = '处理申诉：状态 ';
					$detail_reason .= isset($recordList[$_date][$_type]) ? $recordList[$_date][$_type]['_status'] : '**未签到**';
					$detail_reason .= ' 重设为 ' . $this->_sign_status[$_status];
					if (!empty($dayPleads[$_date]['message'])) {
						$detail_reason .= "\r\n";
						$detail_reason .= '申诉内容：';
						$detail_reason .= $dayPleads[$_date]['message'];
					}
				}

				/** 签到时间 */
				$datetime = $_date . ' ' . $time;
				if (!isset($recordList[$_date][$_type])) {
					//无签到记录，则新增
					$insert[] = array(
						'record' => array(
							'm_uid' => $this->_plead['m_uid'],
							'm_username' => $this->_plead['m_username'],
							'sr_signtime' => rstrtotime($datetime),
							'sr_ip' => '255.255.255.255',
							'sr_type' => $_type,
							'sr_status' => $_status
						),
						'detail' => array(
							'sd_reason' => $detail_reason
						)
					);
				} else {
					//之前存在签到记录

					/** 如果状态发生改变了，才会进行更新 */
					if ($recordList[$_date][$_type] != $_status) {
						$update[$recordList[$_date][$_type]['sr_id']] = array(
							'sr_status' => $_status
						);
						$detail[] = array(
							'sr_id' => $recordList[$_date][$_type]['sr_id'],
							'sd_reason' => $detail_reason
						);
					}
				}
			}
		}

		if (empty($insert) && empty($update) && empty($detail)) {
			$this->_service_single('sign_plead', $cp_pluginid, 'update', $spData, array('sp_id' => $this->_sp_id));
			$this->message('success', '处理申诉操作完毕', $backurl, false);
		}
		/** 开始准备提交数据更新 */
		try {
			$this->_service_single('sign_plead', $cp_pluginid, 'begin', null);

			/** 设置当前申诉状态为已处理 */
			$this->_service_single('sign_plead', $cp_pluginid, 'update', $spData, array('sp_id' => $this->_sp_id));

			/** 新增的签到记录 并写入备忘信息 */
			$_data = array();
			$_sr_id = 0;
			foreach ($insert AS $_data) {

				/** 写入新签到记录 */
				$_sr_id = $this->_service_single('sign_record', $cp_pluginid, 'insert', $_data['record'], true);

				/** 写入备忘 */
				$_data['detail']['sr_id'] = $_sr_id;
				$this->_service_single('sign_detail', $cp_pluginid, 'insert', $_data['detail']);
			}
			unset($_sr_id, $_data);

			/** 签到状态更改 */
			foreach ($update AS $_sr_id => $_data) {
				$_sr_id = $this->_service_single('sign_record', $cp_pluginid, 'update', $_data, array('sr_id' => $_sr_id));
			}
			unset($_sr_id, $_data);

			/** 写入签到状态更改备忘 */
			foreach ($detail AS $_data) {
				$this->_service_single('sign_detail', $cp_pluginid, 'insert', $_data);
			}

			/** 提交过程 */
			$this->_service_single('sign_plead', $cp_pluginid, 'commit', null);
		} catch (Exception $e) {
			$this->_service_single('sign_plead', $cp_pluginid, 'rollback', null);
			$this->message('error', '处理申诉操作失败，请返回尝试重试');
		}

		$this->message('success', '处理申诉操作成功完毕', $backurl, false);

	}
}
