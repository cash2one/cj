<?php
/**
 * 处理签到信息
 * $Author$
 * $Id$
 */
class voa_sign_handle extends voa_sign_base {
	// 地球半径, 单位(米) */
	const EARTH_RADIUS = 6378137;
	// 所有签到类型 */
	private $__types = array (
			'qrcode',
			'location',
			'ip'
	);
	// 二维码签到 */
	const TYPE_QRCODE = 'qrcode';
	// 地理位置签到 */
	const TYPE_LOCATION = 'location';
	// ip地址 */
	const TYPE_IP = 'ip';
	// 错误号 */
	public $error;
	public function __construct() {
		parent::__construct ();
	}

	/**
	 * 签到/签退操作
	 * @param int $record 签到/签退记录
	 * @param array $user 用户信息
	 * @param string $type 打卡类型
	 * @return boolean
	 */
	public function sign(&$overrange, &$record, $user, $type, $location = array(), $info) {
		if (empty ( $type ) || ! in_array ( $type, $this->__types )) {
			$this->error = voa_errcode_oa_sign::TYPE_ERROR;
			return false;
		}

		/**
		 * if (!empty($this->_set['type']) && $this->_set['type'] != $type) {
		 * $this->error = 402;
		 * return false;
		 * }
		 */

		// 判断签到是否超出范围
		$sign_range = $info ['address_range']; // 考勤范围
		if (! empty ( $info ['address'] )) {
			$sr_longitude = $location ['longitude'];
			$sr_latitude = $location ['latitude'];
			$bat_longitude = $info ['longitude'];
			$bat_latitude = $info ['latitude'];

			$length = $this->get_distance ( $sr_latitude, $sr_longitude, $bat_latitude, $bat_longitude );
			if ($length > $sign_range) {
				$overrange = 1;
			}
		}

		// 根据类型的不同, 调用不同的签到方法
		$func = '_' . $type . '_sign';
		if (method_exists ( $this, $func )) {
			return $this->$func ( $record, $user, $location, $info, $overrange );
		}
	}

	// 二维码签到
	protected function _qrcode_sign(&$record, $user) {
		// 获取接收到的消息
		$msg = voa_weixin_service::instance ()->msg;
		// 读取二维码生成记录
		$serv_qr = &service::factory ( 'voa_s_oa_weixin_qrcode', array (
				'pluginid' => 0
		) );
		$qrcode = $serv_qr->fetch_by_id ( $msg ['event_key'] );
		// 读取打卡用户信息
		$serv_m = &service::factory ( 'voa_s_oa_member', array (
				'pluginid' => 0
		) );
		$m = $serv_m->fetch_by_uid ( $qrcode ['m_uid'] );
		// 如果没有二维码/打卡用户记录或二维码已过期
		if (empty ( $qrcode ) || empty ( $m ) || $qrcode ['wq_created'] + $this->_expires_ts < startup_env::get ( 'timestamp' )) {
			$this->error = voa_errcode_oa_sign::QRCODE_EXPIRED;
			// return false;
		}

		return $this->_record_sign ( $record, $m );
	}

	// 地理位置签到
	protected function _location_sign(&$record, $user, $location, $info, $overrange = 0) {
		if (empty ( $location )) {
			// 读取最近一次收到的位置信息
			$serv_l = &service::factory ( 'voa_s_oa_weixin_location', array (
					'pluginid' => 0
			) );
			$locs = $serv_l->fetch_by_uid ( $user ['m_uid'], 0, 1 );
			$location = array_shift ( $locs );
		} else {
			$location ['wl_longitude'] = $location ['longitude'];
			$location ['wl_latitude'] = $location ['latitude'];
			$location ['wl_created'] = startup_env::get ( 'timestamp' );
		}

		// 避免用户设置过小的周期导致获取位置信息失败的频率加高
		if ($this->_expires_ts < 900) {
			$expires_ts = 900;
		} else {
			$expires_ts = $this->_expires_ts;
		}

		// 初始化当前签到数据
		$record ['sr_ip'] = controller_request::get_instance ()->get_client_ip ();

		// 存在经纬度信息且数据有效未过期，则尝试使用经纬度获取
		if (! empty ( $location ) && startup_env::get ( 'timestamp' ) - $location ['wl_created'] < $expires_ts) {
			$record ['sr_address'] = $this->_get_address_by_lnglat ( $location ['wl_longitude'], $location ['wl_latitude'] );
			$record ['sr_longitude'] = $location ['wl_longitude'];
			$record ['sr_latitude'] = $location ['wl_latitude'];
		} else {
			$record ['sr_address'] = empty ( $record ['sr_address'] ) ? '' : $record ['sr_address'];
			$record ['sr_longitude'] = empty ( $record ['sr_longitude'] ) ? '0.000000' : $record ['sr_longitude'];
			$record ['sr_latitude'] = empty ( $record ['sr_latitude'] ) ? '0.000000' : $record ['sr_latitude'];
		}

		// 如果无法使用上面的方式获取位置，则尝试使用ip获取
		if (empty ( $record ['sr_address'] )) {
			$record ['sr_address'] = $this->_get_address_by_ip ( $record ['sr_ip'] );
		}

		// 如果上述方式均无法获取位置，则填充位置信息为 IP字符串
		if (empty ( $record ['sr_address'] )) {
			$record ['sr_address'] = $record ['sr_ip'];
		}
		//判断是否超出签到范围
		if($overrange != 0){
			$record ['sr_addunusual'] = 1;
		}else{
			$record ['sr_addunusual'] = 0;
		}
		return $this->_record_sign ( $record, $user, $info );
	}

	/**
	 * 根据ip地址进行签到
	 * @param array $user 用户信息
	 */
	protected function _ip_sign(&$record, $user) {
		return $this->_record_sign ( $record, $user );
	}

	// 签到
	protected function _record_sign(&$record, $user, $info) {
		// 所属部门
		$cdid = $user ['cd_id'];

		$work_begin = $this->formattime ( $info ['work_begin'] );

		$work_end1 = $this->formattime ( $info ['work_end'] );
		$work_begin = $this->_to_seconds ( $work_begin );
		$work_end = $this->_to_seconds ( $work_end1 );
		/*
		 * // 读取当天范围内的打卡记录
		 * $remainder = $this->_to_seconds ( rgmdate ( startup_env::get ( 'timestamp' ), 'H:i' ) );
		 * $btime = startup_env::get ( 'timestamp' ) - $remainder;
		 * $etime = $btime + 86400;
		 */
		// 起始时间和结束时间
		$ymd = rgmdate ( startup_env::get ( 'timestamp' ), 'Y-m-d' );
		// 开始时间为工作时间前6小时
		$btime = rstrtotime ( $ymd . ' ' . $this->formattime ( $info ['work_begin'] ) ) - 3600 * 6;

		// 结束时间为工作时间后9小时
		$work_e = substr ( $work_end1, 0, 2 );

		if ($work_e - 24 > 0) {
			$etime = $this->totime ( $ymd, $work_end1 ) + 3600 * 9;
		} else {
			$etime = rstrtotime ( $ymd . ' ' . $this->formattime ( $info ['work_end'] ) ) + 3600 * 9;
		}

		// $etime = rstrtotime ( $ymd . ' ' . $this->formattime ( $info ['work_end'] ) ) + 3600*9;

		$serv_rcd = &service::factory ( 'voa_s_oa_sign_record', array (
				'pluginid' => startup_env::get ( 'pluginid' )
		) );
		$signs = $this->get_by_time ( $user ['m_uid'], $btime, $etime );

		// 类型值
		$type = voa_d_oa_sign_record::TYPE_ON;

		// 判断打卡设置
		// + $this->_to_seconds ( $this->_set ['sign_begin_hi'] )
		if ($info ['sb_set'] == 1) { // 只签到
			if (empty ( $signs )) { // 为空没有签到记录
			                        // 如果小于开始上班打卡时间, 则不记录
				if (startup_env::get ( 'timestamp' ) < $btime) {
					$this->error = voa_errcode_oa_sign::SIGN_ON_EARLY_SIGN_BEGIN_HI;
					return false;
				}
				$type = voa_d_oa_sign_record::TYPE_ON;
			} elseif (count ( $signs ) == 1) { // 说明签过完成
				$this->error = voa_errcode_oa_sign::SIGN_FINISHED;
				return false;
			}
		} elseif ($info ['sb_set'] == 2) { // 只签退
			if (empty ( $signs )) { // 为空没有签退记录
				$type = voa_d_oa_sign_record::TYPE_OFF;
			} elseif (count ( $signs ) == 1) { // 说明签退完成
				$this->error = voa_errcode_oa_sign::SIGN_FINISHED;
				return false;
			}
		} elseif ($info ['sb_set'] == 3) { // 签到和签退
		                                   // 如果没有记录
			if (empty ( $signs )) { // 如果还没有签到记录
			                        // 如果小于开始上班打卡时间, 则不记录+ $this->_to_seconds ( $this->_set ['sign_begin_hi'] )

				if (startup_env::get ( 'timestamp' ) < $btime) {
					$this->error = voa_errcode_oa_sign::SIGN_ON_EARLY_SIGN_BEGIN_HI;
					return false;
				}

				$type = voa_d_oa_sign_record::TYPE_ON;
			} else if (2 <= count ( $signs )) { // 有两条记录, 则说明上下班的卡已经都打过
				$this->error = voa_errcode_oa_sign::SIGN_FINISHED;
				return false;
			} else { // 如果打过上班卡, 则
			         // 如果时间还在上班时间之前, 则不记录 + $work_begin
				if (startup_env::get ( 'timestamp' ) < $btime) {
					$this->error = voa_errcode_oa_sign::SIGN_OFF_EARLY_WORK_BEGIN_HI;
					return false;
				}

				$type = voa_d_oa_sign_record::TYPE_OFF;
			}
		}

		$overtime = 0;
		if ($type == voa_d_oa_sign_record::TYPE_ON) {
			$status = $this->on_status ( startup_env::get ( 'timestamp' ), $work_begin );
		} else {
			// 计算加班时长
			$remainder = $this->_to_seconds ( rgmdate ( startup_env::get ( 'timestamp' ), 'H:i' ) );
			$over = $remainder - $work_end;
			// 判断打下班卡时间是否超过打卡时间（下班后9小时）
			if ($over > 9 * 3600) {
				$this->error = voa_errcode_oa_sign::SING_END;
				return false;
			}
			$status = $this->off_status ( startup_env::get ( 'timestamp' ), $work_end );
			if ($status == 1) {
				if ($remainder > $work_end) { // 加班
					$overtime = $over;
				}
			}
		}

		//打卡信息入库

		$record = array_merge ( $record, array (
				'm_uid' => $user ['m_uid'],
				'm_username' => $user ['m_username'],
				'sr_signtime' => startup_env::get ( 'timestamp' ),
				'sr_ip' => controller_request::get_instance ()->get_client_ip (),
				'sr_type' => $type,
				'sr_sign' => $status,
				'sr_longitude' => $record ['sr_longitude'],
				'sr_latitude' => $record ['sr_latitude'],
				'sr_address' => $record ['sr_address'],
				'sr_overtime' => $overtime,
				'sr_addunusual' => $record['sr_addunusual'],
				'sr_batch' => $info ['sbid']
		)
		 );
		$record_result = $serv_rcd->insert ( $record, true );

		$record ['sr_id'] = $record_result ['sr_id'];

		//发送微信模板消息


		return true;
	}

	/**
	 * 角度 => 弧度
	 */
	function rad($dis) {
		return round ( $dis * (M_PI / 180), 6 );
	}

	/**
	 * 计算经纬度之间的距离
	 * @param float $lat1 经度
	 * @param float $lng1 纬度
	 * @param float $lat2 经度
	 * @param float $lng2 纬度
	 */
	function get_distance($lat1, $lng1, $lat2, $lng2) {
		$lat1 = round ( $lat1, 6 );
		$lng1 = round ( $lng1, 6 );
		$lat2 = round ( $lat2, 6 );
		$lng2 = round ( $lng2, 6 );
		$radLat1 = $this->rad ( $lat1 );
		$radLat2 = $this->rad ( $lat2 );
		$a = $radLat1 - $radLat2;
		$b = $this->rad ( $lng1 ) - $this->rad ( $lng2 );
		$s = 2 * asin ( sqrt ( pow ( sin ( $a / 2 ), 2 ) + cos ( $radLat1 ) * cos ( $radLat2 ) * pow ( sin ( $b / 2 ), 2 ) ) );
		$s = round ( $s * self::EARTH_RADIUS, 0 );
		return $s;
	}

	/**
	 * 新签到
	 */
	/*
	 * protected function _record_sign_new(&$record, $user) {
	 *
	 * // 人员部门
	 *
	 * $cd = $user ['cd_id'];
	 * var_dump ( $cd );
	 * die ();
	 *
	 * // 查所属班次
	 *
	 * $serv_batch = &service::factory ( 'voa_s_oa_sign_batch' );
	 * $conds ['enable'] = 1;
	 * $conds ['department'] = $cd;
	 * $batch_info = $serv_batch->list_by_conds ( $conds );
	 *
	 * // 读取当天范围内的打卡记录
	 *
	 * $remainder = $this->_to_seconds ( rgmdate ( startup_env::get ( 'timestamp' ), 'H:i' ) );
	 * $btime = startup_env::get ( 'timestamp' ) - $remainder;
	 * $etime = $btime + 86400;
	 * $serv_rcd = &service::factory ( 'voa_s_oa_sign_record', array (
	 * 'pluginid' => startup_env::get ( 'pluginid' )
	 * ) );
	 * $signs = $serv_rcd->fetch_by_uid_time ( $user ['m_uid'], $btime, $etime );
	 *
	 *
	 * // 类型值
	 *
	 * $type = voa_d_oa_sign_record::TYPE_ON;
	 *
	 * // 如果没有记录
	 *
	 * if (empty ( $signs )) {
	 *
	 * // 如果还没有签到记录
	 *
	 *
	 * // 如果小于开始上班打卡时间, 则不记录
	 *
	 * if (startup_env::get ( 'timestamp' ) < $btime + $this->_to_seconds ( $this->_set ['sign_begin_hi'] )) {
	 * $this->error = voa_errcode_oa_sign::SIGN_ON_EARLY_SIGN_BEGIN_HI;
	 * return false;
	 * }
	 *
	 * $type = voa_d_oa_sign_record::TYPE_ON;
	 * } else if (2 <= count ( $signs )) {
	 *
	 * // 有两条记录, 则说明上下班的卡已经都打过
	 *
	 * $this->error = voa_errcode_oa_sign::SIGN_FINISHED;
	 * return false;
	 * } else {
	 *
	 * // 如果打过上班卡, 则
	 *
	 *
	 * // 如果时间还在上班时间之前, 则不记录
	 *
	 * if (startup_env::get ( 'timestamp' ) < $btime + $this->_to_seconds ( $this->_set ['work_begin_hi'] )) {
	 * $this->error = voa_errcode_oa_sign::SIGN_OFF_EARLY_WORK_BEGIN_HI;
	 * return false;
	 * }
	 *
	 * $type = voa_d_oa_sign_record::TYPE_OFF;
	 * }
	 *
	 * if ($type == voa_d_oa_sign_record::TYPE_ON) {
	 * $status = $this->on_status ( startup_env::get ( 'timestamp' ) );
	 * } else {
	 * $status = $this->off_status ( startup_env::get ( 'timestamp' ) );
	 * }
	 *
	 *
	 * // 打卡信息入库
	 *
	 * $record = array_merge ( $record, array (
	 * 'm_uid' => $user ['m_uid'],
	 * 'm_username' => $user ['m_username'],
	 * 'sr_signtime' => startup_env::get ( 'timestamp' ),
	 * 'sr_ip' => controller_request::get_instance ()->get_client_ip (),
	 * 'sr_type' => $type,
	 * 'sr_status' => $status
	 * ) );
	 * $record_result = $serv_rcd->insert ( $record, true );
	 *
	 * $record['sr_id'] = $record_result['sr_id'];
	 *
	 * // 发送微信模板消息
	 *
	 *
	 * return true;
	 * }
	 */

	/**
	 * 根据经纬度获取位置信息
	 * @param float $longitude 经度
	 * @param float $latitude 纬度
	 * @return string
	 */
	protected function _get_address_by_lnglat($longitude, $latitude) {

		// 获得地理位置
		$maps = new map ();
		$result = $maps->get_map ( $longitude, $latitude );
		if ($result == false) {
			// 无法获取地址位置信息
			return '';
		}

		if (empty ( $result ['address'] )) {
			return '';
		}

		return $result ['address'];
	}

	/**
	 * 根据IP获取地址信息
	 * @param string $ip
	 * @return string|boolean
	 */
	protected function _get_address_by_ip($ip) {
		$ip2address = new ip2address ();
		if (! $ip2address->get ( $ip )) {
			// $this->error = $ip2address->errcode.':'.$ip2address->errmsg;
			return '无法获取地理位置';
		}

		if (empty ( $ip2address->result ['address'] )) {
			return '';
		}

		return $ip2address->result ['address'];
	}
	/**
	 * 根据时间获取签到记录
	 * @param unknown $uid
	 * @param unknown $btime
	 * @param unknown $etime
	 * @return multitype:
	 */
	public function get_by_time($uid, $btime, $etime) {
		$serv = &service::factory ( 'voa_s_oa_sign_record' );

		$conds ['sr_signtime >= ?'] = $btime;
		$conds ['sr_signtime <= ?'] = $etime;
		$conds ['m_uid'] = $uid;
		$records = $serv->list_by_conds ( $conds );
		if (! $records) {
			$records = array ();
		}
		return $records;
	}
	/**
	 * 将大于24点的时间格式化
	 * @param unknown $ymd
	 * @param unknown $num
	 * @return number
	 */
	public function totime($ymd, $num) {
		$time = $num;
		$h = substr ( $time, 0, 2 );
		// 2015-08-15 25:23;
		if ($h - 24 > 0) {
			$diff = $h - 24;
			$m = substr ( $time, 3, 2 );
			$formattime = strtotime ( '+1 day', strtotime ( $ymd ) ) + $diff * 3600 + $m * 60 - 8 * 3600;
		}
		return $formattime;
	}
}
