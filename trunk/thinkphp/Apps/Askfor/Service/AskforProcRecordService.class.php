<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/11/26
 * Time: 下午1:32
 */

namespace Askfor\Service;
use Askfor\Model\AskforProcRecordModel;

class AskforProcRecordService extends AbstractService {

	//构造方法
	public function __construct() {
		$this->_d = D("Askfor/AskforProcRecord");
		parent::__construct();
	}

	/**
	 * 添加催办记录
	 * @param array  $af_id 审批id
	 * @param int    $uid 发起人id
	 * @param string $username 发起人姓名
	 * @param string $mark 备注
	 * @return bool
	 */
	public function press_record($af_id, $uid, $username, $mark = null) {

		//构造数据
		$data['m_uid'] = $uid;
		$data['af_id'] = $af_id;
		$data['m_username'] = $username;
		$data['rafp_condition'] = AskforProcRecordModel::PRESSASK;
		if (!empty($mark)) {
			$data['rafp_note'] = $mark;
		}

		$this->_d->insert($data);

		return true;
	}

	/**
	 * 判断两次催办时间间隔
	 * @param $af_id int 审批id
	 * @return bool 返回值
	 */
	public function last_press($af_id) {

		//获取催办记录
		$conds['af_id'] = $af_id;
		$conds['rafp_condition'] = AskforProcRecordModel::PRESSASK;
		$order_option = array('rafp_created' => 'ASC');
		$page_option = array();
		$last_record = $this->_d->list_by_conds($conds, $page_option, $order_option);

		//判断间隔时间
		if (!empty($last_record)) {
			//取最后一条记录
			foreach($last_record as $_last){
				$last_record = $_last;
			}
			//时间差
			$diff = NOW_TIME - $last_record['rafp_created'];
			if ($diff < AskforProcRecordModel::PRESS_TIME) {
				E('_ERR_PRESS_TIME_MACH_MORE');

				return false;
			}
		}

		return true;
	}

	/**
	 * 添加撤销记录
	 * @param array  $af_id 审批id
	 * @param int    $uid 发起人id
	 * @param string $username 发起人姓名
	 * @param sgring $mark 备注
	 * @return bool
	 */
	public function cancel_record($af_id, $uid, $username, $mark) {

		//构造数据
		$data['m_uid'] = $uid;
		$data['af_id'] = $af_id;
		$data['m_username'] = $username;
		$data['rafp_condition'] = AskforProcRecordModel::CENCEL;
		$data['rafp_note'] = $mark;

		$this->_d->insert($data);

		return true;
	}
}
