<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 15/11/11
 * Time: 上午10:01
 */
namespace Askfor\Service;

class AskforDraftService extends AbstractService {
	//构造方法
	public function __construct() {
		$this->_d = D("Askfor/AskforDraft");
		parent::__construct();
	}

	/**
	 * 获取上次存入的审批人和抄送人数据
	 * @param $uid int 用户id
	 * @return $result array 上次审批人和抄送人数据
	 */
	public function get_last($uid) {

		//根据用户id查上次存入的审批人数据
		$conds_draft['m_uid'] = $uid;
		$data = $this->_d->get_by_conds($conds_draft);

		$result = array();
		//有记录
		if (!empty($data)) {
			//审批人
			if (!empty($data['last_afid'])) {
				$result['splist'] = unserialize($data['last_afid']);
			}
			//抄送人
			if (!empty($data['last_csid'])) {
				$result['cslist'] = unserialize($data['last_csid']);
			}
		}

		return $result;
	}

	/**
	 * 根据条件更新数据
	 * @param array $m_uid 发起人id
	 * @param array $s_list 审批人列表
	 * @praam array $c_list 抄送人列表
	 * @return bool
	 */
	public function update_by_conds_draft($m_uid, $s_list, $c_list) {

		//更新条件
		$conds['m_uid'] = $m_uid;
		$data['last_afid'] = serialize($s_list);
		if (!empty($c_list)) {
			$data['last_csid'] = serialize($c_list);
		}

		//更新操作
		$this->_d->update_by_conds($conds, $data);

		return true;
	}

	/**
	 * 新建默认数据
	 * @param string $m_uid 发起人id
	 * @param array  $s_list 审批人列表
	 * @param bool   $c_list 抄送人列表
	 * @return bool
	 */
	public function insert_draft($m_uid, $s_list, $c_list) {

		//待插入数据
		$data['m_uid'] = $m_uid;
		$data['last_afid'] = serialize($s_list);
		if (!empty($c_list)) {
			$data['last_csid'] = serialize($c_list);
		}

		//入库操作
		$this->_d->insert($data);

		return true;
	}
}
