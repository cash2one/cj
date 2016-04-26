<?php
/**
 * SignRecordService.class.php
 * $author$
 */

namespace Sign\Service;

class SignLocationService extends AbstractService {

	// 构造方法
	public function __construct() {
		$this->_d = D("Sign/SignLocation");
		parent::__construct();

	}

	/**
	 * 获取外勤记录
	 * @return $list
	 */
	public function list_recordsf() {

		return $this->_d->list_records();
	}

	/**
	 * 获取当天外勤记录
	 * @param unknown $params
	 */
	public function get_out_record($params, $page_option) {

		if (empty($params['udate'])) {
			$params['udate'] = rstrtotime(NOW_TIME);
		}

		return $this->_d->get_out_record($params, $page_option);
	}

	
	/**
	 * [sign_insert 签到方法]
	 * @param  [array] $params [传入的数据]
	 * @param  [array] $extend [传递的扩展数据]
	 * @return [array]         [签到成功后返回的数据]
	 */
	public function sign_insert($params, $extend) {

		// 初始化经纬度
		list($g_longitude, $g_latitude) = $this->get_lon_lat($params['location']);
		// 初始化插入数据
		$insert_data = $this->insert_data($g_longitude, $g_latitude, $params, $extend);
		// 执行入库操作
		if (!$id = $this->_d->insert($insert_data)) {
			$this->_set_error('_ERR_SIGN_INSERT');
			return false;
		}
		$insert_data['sl_id'] = $id;
		return $insert_data;
	}


	/**
	 * [get_lon_lat 初始化经纬度]
	 * @param  [string] $location [传递过来的location]
	 * @return [array] [返回的经纬度数组]
	 */
	protected function get_lon_lat($location) {
		
		if (!empty($location)) {
			list($g_longitude, $g_latitude) = explode(',', $location);
		} else {
			list($g_longitude, $g_latitude) = array(0, 0);
		}
		return array($g_longitude, $g_latitude);

	}

	/**
	 * [insert_data 生产所需的数据]
	 * @param  [string] $g_longitude [传递的经度]
	 * @param  [string] $g_latitude  [传递的纬度]
	 * @param  [array] $params       [传递的参数]
	 * @param  [array] $extend       [扩展的参数]
	 * @return [array]               [返回的数据]
	 */
	protected function insert_data($g_longitude, $g_latitude, $params, $extend) {
		
		return array(
			'm_uid' => $extend['uid'],
			'm_username' => $extend['username'],
			'sl_signtime' => NOW_TIME,
			'sl_ip' => get_client_ip(),
			'sl_longitude' => $g_longitude,
			'sl_latitude' => $g_latitude,
			'sl_address' => $params['address'],
			'sl_note' => isset($params['sl_note']) ? $params['sl_note'] : ''
		);

	}

	/**
	 * [make_data 把附件上传的数据加入到返回的数组中]
	 * @param  [array] &$in      [传递的数据]
	 * @param  [array] $img_info [需要加入的数据]
	 * @param  [string] $url      [传入的URL]
	 * @return [bool]
	 */
	public function make_data(&$in, $img_info, $url) {
		if (!empty($img_info)) {

			foreach ($img_info as $key => $v) {
				$in['attachs'][] = array(
					'at_id' => $v['at_id'], // 公共文件附件ID
					'filename' => $v['at_filename'], // 附件名称
					'filesize' => $v['at_filesize'], // 附件容量
					'mediatype' => $v['at_mediatype'], // 媒体文件类型
					'description' => $v['at_description'], // 附件描述
					'isimage' => $v['at_isimage'] ? 1 : 0, // 是否是图片
					'url' => cfg('PROTOCAL') . $url . '/attachment/read/' . $v['at_id'], // 附件文件url
					'thumb' => $v['at_isimage'] ? cfg('PROTOCAL') . $url . '/attachment/read/' . $v['at_id'] : ''
				);
			}
		} else {
			$in['attachs'] = null;
		}
		return;
	}

    /**
     * 根据签到时间分组查询外出考勤数据
     * @param $m_uid
     * @param $stime
     * @param $etime
     * @return array
     */
    public function list_by_condition_new($m_uid, $stime, $etime) {

        return $this->_d->list_by_condition_new($m_uid, $stime, $etime);
    }

}
