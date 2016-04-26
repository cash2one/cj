<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/11/10
 * Time: 下午3:41
 */
namespace Askfor\Service;

use Askfor\Model\AskforModel;
use Askfor\Model\AskforProcModel;

class AskforService extends AbstractService {

	// 构造方法
	public function __construct() {

		$this->_d = D("Askfor/Askfor");
		parent::__construct();
	}

	/**
	 * 根据af_id 单个查询
	 * @param $af_id
	 * @return mixed
	 */
	public function get_data_by_af_id($af_id) {
		return $this->_d->get_by_conds(array('af_id' => $af_id));
	}

	/**
	 * 新建自由流程入库
	 * @param array $params 接收参数
	 * @return int $af_id 审批id
	 */
	public function free_insert($params) {

		//数据验证
		$this->__verify($params);

		//构造入库数据
		$data['af_subject'] = $params['af_subject'];
		$data['af_message'] = $params['af_message'];
		$data['af_condition'] = AskforModel::ASKING;
		$data['m_uid'] = $params['m_uid'];
		$data['m_username'] = $params['m_username'];

		//入库操作
		$af_id = $this->_d->insert($data);

		return $af_id;
	}

	/**
	 * 验证数据是否为空
	 * @param array $params 接收的参数
	 * @return bool
	 */
	private function __verify($params) {

		//标题不能为空
		if (empty($params['af_subject'])) {
			E('_ERR_MISS_SUBJECT');

			return false;
		}
		//审批标题不能超过15个字
		if(mb_strstr($params['af_subject']) > AskforModel::SUBJECT_LENGTH){
			E('_ERR_SUBJECT_OVER_LENGTH');

			return false;
		}
		//申请内容不能为空
		if (empty($params['af_message'])) {
			E('_ERR_MISS_MESSAGE');

			return false;
		}

		return true;
	}

	/**
	 * 判断是否有可操作的记录
	 * @param int $m_uid 用户id
	 * @param int $af_id 审批id
	 * @param string $act 操作类型
	 * @return array $record 操作的记录
	 */
	public function is_end($m_uid, $af_id, $act) {

		//获取记录
		$conds_askfor['m_uid'] = $m_uid;
		$conds_askfor['af_id'] = $af_id;

		if ($act == 'cancel') { //撤销操作
			$conds_askfor['af_condition'] = array(
				AskforModel::ASKING,
				AskforModel::PRESSASK,
			);
			//判断进度表中有没有人操作
			$conds_pro['af_id'] = $af_id;
			$conds_pro['afp_condition'] = array(
				AskforProcModel::ASKPASS,
				AskforProcModel::TURNASK,
				AskforProcModel::ASKFAIL,
				AskforProcModel::CENCEL,
			);
			$model_proc = D('Askfor/AskforProc', 'Model');

			$proc_rec = $model_proc->list_by_conds($conds_pro);
			//不为空则为已开始
			if (!empty($proc_rec)) {
				E('_ERR_NOT_CANCEL');

				return false;
			}

		} elseif ($act == 'press') { //催办操作
			$conds_askfor['af_condition'] = array(
				AskforModel::ASKING,
				AskforModel::TURNASK,
				AskforModel::PRESSASK,
			);
		}
		$record = $this->_d->get_by_conds($conds_askfor);

		//没有记录
		if (!$record) {
			E('_ERR_NULL_OR_NO_PERMISSION');

			return false;
		}

		return $record;
	}

	/**
	 * 更改审批状态为撤销
	 * @param int $af_id 审批id
	 * @return bool
	 */
	public function cancel($af_id) {

		//更改状态
		$data['af_condition'] = AskforModel::CENCEL;

		//更改操作
		$this->_d->update($af_id, $data);

		return true;
	}

	/**
	 * 获取审批中的数据
	 * @param array $page_option 分页参数
	 * @param int   $uid 用户id
	 * @return $list 审批中的数据
	 */
	public function list_asking($page_option, $uid) {

		//获取审批中的
		$conds['m_uid'] = $uid;
		$conds['af_condition'] = array(AskforModel::ASKING, AskforModel::TURNASK, AskforModel::PRESSASK);
		$order_option = array('af_created' => 'DESC');

		$list = $this->_d->list_by_conds($conds, $page_option, $order_option);

		return $list;
	}

	/**
	 * 获取审批中的数据总数
	 * @param int   $uid 用户id
	 * @return $list 审批中的数据
	 */
	public function count_list_asking($uid) {

		//获取审批中的
		$conds = array();
		$conds['m_uid'] = $uid;
		$conds['af_condition'] = array(AskforModel::ASKING, AskforModel::TURNASK, AskforModel::PRESSASK);

		$list = $this->_d->count_by_conds($conds);

		return $list;
	}

	/**
	 * 获取已完成的数据
	 * @param array $page_option 分页参数
	 * @param int   $uid 用户id
	 * @return $list 已完成的数据
	 */
	public function list_approve($page_option, $uid) {

		// 获取审批中的
		$conds['m_uid'] = $uid;
		$conds['af_condition'] = AskforModel::ASKPASS;
		$order_option = array('af_created' => 'DESC');

		$list = $this->_d->list_by_conds($conds, $page_option, $order_option);

		return $list;
	}

	/**
	 * 获取已完成的数据总数
	 * @param int   $uid 用户id
	 * @return $list 已完成的数据
	 */
	public function count_list_approve($uid) {

		// 获取审批中的
		$conds = array();
		$conds['m_uid'] = $uid;
		$conds['af_condition'] = AskforModel::ASKPASS;

		$list = $this->_d->count_by_conds($conds);

		return $list;
	}

	/**
	 * 获取驳回的数据
	 * @param array $page_option 分页参数
	 * @param int   $uid 用户id
	 * @return array $list 返回的数组
	 */
	public function list_refuse($page_option, $uid) {

		//获取审批中的
		$conds['m_uid'] = $uid;
		$conds['af_condition'] = AskforModel::ASKFAIL;
		$order_option = array('af_created' => 'DESC');

		$list = $this->_d->list_by_conds($conds, $page_option, $order_option);

		return $list;
	}

	/**
	 * 获取驳回的数据总数
	 * @param int   $uid 用户id
	 * @return array $list 返回的数组
	 */
	public function count_list_refuse($uid) {

		//获取审批中的
		$conds = array();
		$conds['m_uid'] = $uid;
		$conds['af_condition'] = AskforModel::ASKFAIL;

		$list = $this->_d->count_by_conds($conds);

		return $list;
	}

	/**
	 * 格式列表数据
	 * @param array $in 待处理数据
	 * @return array $data 处理后数组
	 */
	public function format_send($in) {

		//除去其他字段信息
		$data = array();
		foreach ($in as $val) {
			$tmp = array();
			$tmp['af_id'] = $val['af_id'];
			$tmp['m_username'] = $val['m_username'];
			$tmp['af_subject'] = $val['af_subject'];
			$tmp['af_created'] = $val['af_created'];
			$tmp['af_condition'] = $val['af_condition'];
			$tmp['aft_id'] = $val['aft_id'];

			//赋给输出数组
			$data[] = $tmp;
		}

		return $data;
	}

	/**
	 * 催办
	 * @param int $af_id 审批id
	 * @return bool
	 */
	public function press($af_id) {

		//催办
		$data['af_condition'] = AskforModel::PRESSASK;
		$this->_d->update($af_id, $data);

		return true;
	}

	/**
	 * 后台根据条件查询方法
	 * @param array $params 查询条件
	 * @param array $page_option 分页参数
	 * @return array $list 列表
	 */
	public function cp_list_by_conds($params, $page_option) {

		$list = $this->_d->cp_list_by_conds($params, $page_option);

		return $list;
	}

	/**
	 * 获取单条记录详情
	 * @param string $af_id 审批id
	 * @return array $info 单条数据详情
	 */
	public function askfor_get($af_id) {

		$info = $this->_d->get($af_id);
		if (!$info) {
			E('_ERR_NOT_EXISTS');
		}

		return $info;
	}

	/**
	 * 后台根据条件统计
	 * @param array $params 接收参数
	 * @return mixed
	 */
	public function cp_count_by_conds($params) {

		return $this->_d->cp_count_by_conds($params);
	}

	/**
	 * 连proc表查询 获取 我收到的 审批列表
	 * @param int $m_uid 操作人
	 * @param string $afp_condition 状态
	 * @param int $is_active 是否当前人操作
	 * @param array $page_option 分页参数
	 * @param string $askfor_condition 审批主状态
	 * @return mixed
	 */
	public function left_join_proc($m_uid, $afp_condition, $is_active, $page_option, $askfor_condition) {
		return $this->_d->left_join_proc($m_uid, $afp_condition, $is_active, $page_option, $askfor_condition);
	}

	/**
	 * 统计数量 连proc表查询 获取 我收到的 审批列表
	 * @param int $m_uid 操作人
	 * @param string $afp_condition 状态
	 * @param int $is_active 是否当前人操作
	 * @param string $askfor_condition 审批主状态
	 * @return mixed
	 */
	public function count_left_join_proc($m_uid, $afp_condition, $is_active, $askfor_condition) {
		return $this->_d->count_left_join_proc($m_uid, $afp_condition, $is_active, $askfor_condition);
	}
}
