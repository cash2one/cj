<?php
/**
 * SignRecordService.class.php
 * $author$
 */

namespace Sign\Service;

class SignDetailService extends AbstractService {

	// 构造方法
	public function __construct() {

		$this->_d = D("Sign/SignDetail");
		parent::__construct();

	}

	/**
	 * 获取备注
	 * @param array $srids 签到记录id
	 * @param String $today_date 今天年月日
	 * @return array $able_detail 备注列表
	 */

	public function get_detail_list($srids , $today_date) {

		$detail_list = array();
		//根据签到id获取备注记录
		$detail_list = $this->_d->get_in_srids($srids, $today_date);
		if (!empty($detail_list)) {
			
			$able_detail = array();
			// 格式显示数据
			foreach ($detail_list as $_detail) {
				$det = array();
				$det['sd_id'] = $_detail['sd_id'];
				$det['sr_id'] = $_detail['sr_id'];
				$det['sd_reason'] = $_detail['sd_reason'];
				$det['sd_id'] = $_detail['sd_id'];
				$det['sd_time'] = rgmdate($_detail['sd_created'], 'H:i:s');
				$able_detail[$_detail['sd_id']] = $det;
			}
		}

		return $able_detail;
	}

	/**
	 * 根据sr_id获取数据
	 * @param $sr_id
	 * @return mixed
	 */
	public function list_by_sr_id($sr_id) {

		return $this->_d->get_in_srids($sr_id);
	}

	/**
	 * 写入备注信息
	 * @param $data
	 * @return mixed
	 */
	public function insert_reason($data) {

		return $this->_d->insert($data);
	}

	/**
	 * 查询 本次提交的备注 相关的 type和签到id 备注
	 * @param $data
	 * @return mixed
	 */
	public function list_by_reason_post($data) {

		$list = $this->_d->list_by_reason_post($data);

		// 格式化时间
		foreach ($list as $k => &$v) {
			$v['sd_created'] = rgmdate($v['sd_created'], 'H:i:s');
		}

		return $list;
	}
}
