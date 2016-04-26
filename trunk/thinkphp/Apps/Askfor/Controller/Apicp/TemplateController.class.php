<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/11/22
 * Time: 下午11:31
 */

namespace Askfor\Controller\Apicp;
use Askfor\Model\AskforTemplateModel;
use Common\Common\Pager;

class TemplateController extends AbstractController {

	const TEMP_ORDERID_DEAFULT_DATA = 1; // 模板排序默认数据
	const UPLOAD_IMAGE = 1; // 是否 上传图片 (是)
	const DEAFULT_BU_ID = -1; // 默认允许的部门

	public function Temp_post() {

		$post = I('post.');

		// 判断数据
		$this->_judge_temp_insert_data($post);

		return true;
	}

	/**
	 * 判断提交的数据 并且入库
	 * @param $post
	 * @return bool|mixed
	 */
	protected function _judge_temp_insert_data($post) {

		// 审批人不能为空
		if (empty($post['approver'])) {
			E('_ERR_CP_APPROVERS_CAN_NOT_NULL');

			return false;
		}
		// 流程名称不得为空
		if (empty($post['title'])) {
			E('_ERR_CP_TEMP_NAME_CAN_NOT_NULL');

			return false;
		}

		// 判断审批人是否重复
		$approvers_uids = array();
		$approvers = array();
		foreach ($post['approver'] as $_level => &$_userlist) {
			foreach ($_userlist as &$_user) {
				// 去除多余数据
				unset($_user['$$hashKey']);
				unset($_user['selected']);
				// 审批人重复
				if (in_array($_user['m_uid'], $approvers_uids)) {
					E(L('_ERR_CP_APPROVERS_IS_REPEAT', array('appreover' => $_user['m_username'])));

					return false;
				}
				$approvers_uids[] = $_user['m_uid'];
			}
			if (!empty($post['approver'][$_level])) {
				$approvers[] = $post['approver'][$_level];
			}
		}

		// 判断自定义字段名称是否重复
		$temp = array();
		foreach ($post['custom'] as $_key => $_val) {
			if (in_array($_val['name'], $temp)) {
				E('_ERR_CP_CUSTOM_NAME_CAN_NOT_RECUR');

				return false;
			}
			$temp[] = $_val['name'];
		}

		// 判断审批人是否存在
		$serv_mem = D('Common/Member', 'Service');
		$user_data = $serv_mem->list_by_conds(array('m_uid' => $approvers_uids));
		$user_data_uids = array_column($user_data, 'm_uid');
		foreach ($user_data_uids as $_uid) {
			// 审批人不存在
			if (!in_array($_uid, $approvers_uids)) {
				E('_ERR_CP_APPROVER_IS_NOT_EXIST');

				return false;
			}
		}

		// 是否有抄送人
		if (!empty($post['copy'])) {
			// 提取抄送人
			$copy_uids = array_column($post['copy'], 'm_uid');
			$post['copy'] = array_combine_by_key($post['copy'], 'm_uid');
			// 判断抄送人是否存在
			$copy_data = $serv_mem->list_by_conds(array('m_uid' => $copy_uids));
			$copy_data_uids = array_column($copy_data, 'm_uid');
			// 如果不存在, 删除提交的抄送人
			foreach ($copy_data_uids as $_uids) {
				if (!in_array($_uids, $copy_uids)) {
					unset($post['copy'][$_uids]);
				}
				// 判断 抄送人里是否有审批人
				if (in_array($_uids, $user_data_uids)) {
					unset($post['copy'][$_uids]);
				}
			}
			// 剔除无用值
			foreach ($post['copy'] as &$value) {
				unset($value['$$hashKey']);
			}
		}

		// 部门使用
		$bu_id = '';
		$ser_buid = '';
		if (!empty($post['bu_id'])) {
			// 提取id
			$bu_ids = array_column($post['bu_id'], 'id');
			$bu_id = implode(',', $bu_ids);
			// 剔除无用值
			foreach ($post['bu_id'] as &$value) {
				unset($value['$$hashKey']);
			}
			// 序列化存储,用于编辑时用
			$ser_buid = serialize($post['bu_id']);
		}

		// 审批数据入
		$askfor_insert_data = array(
			'name' => $post['title'],
			'orderid' => !empty($post['id_title']) ? $post['id_title'] : self::TEMP_ORDERID_DEAFULT_DATA,
			'create_id' => $post['create_id'],
			'creator' => $post['create_username'],
			'approvers' => serialize($approvers),
			'custom' => !empty($post['custom']) ? serialize($post['custom']) : '',
			'copy' => !empty($post['copy']) ? serialize($post['copy']) : '',
			'bu_id' => !empty($post['bu_id']) ? $bu_id : self::DEAFULT_BU_ID,
			'sbu_id' => !empty($post['bu_id']) ? $ser_buid : '',
			'upload_image' => self::UPLOAD_IMAGE,
		);

		// 数据入库
		$serv_temp = D('Askfor/AskforTemplate', 'Service');
		if ($post['act'] == 'edit' && !empty($post['aft_id'])) {
			unset($askfor_insert_data['create_id']);
			unset($askfor_insert_data['creator']);
			$serv_temp->update_by_conds(array('aft_id' => $post['aft_id']), $askfor_insert_data);
		// 清理缓存
			clear_cache();
		} elseif ($post['act'] == 'add') {
			$aft_id = $serv_temp->insert($askfor_insert_data);
			clear_cache();
		} else {
			E('_ERR_CP_INSERT_ERROR');

			return false;
		}

		return true;
	}

	/**
	 * 后台模板列表接口
	 */
	public function List_get(){

		$params = I('get.');
		$page = $params['page'];
		$limit = $params['limit'];
		if(empty($params['page'])){
			$page = 1;
		}
		if(empty($params['limit'])){
			$limit = 10;
		}

		// 分页参数
		list($start, $limit, $page) = page_limit($page, $limit);
		// 分页参数
		$page_option = array($start, $limit);
		$list = array();
		$serv_template = D('Askfor/AskforTemplate');
		$list = $serv_template->list_all($page_option);
		$total = $serv_template->count();
		//分页
		$multi = null;
		if ($total > 0) {
			$pagerOptions = array(
				'total_items' => $total,
				'per_page' => $limit,
				'current_page' => $page,
				'show_total_items' => true,
			);
			$multi = Pager::make_links($pagerOptions);
		}
		//格式数据
		if(!empty($list)){
			$list = $this->_template_format($list);
		}

		$this->_result = array(
			'list' => $list,
			'page' => $page,
			'total' => $total,
			'limit' => $limit,
			'multi' => $multi,

		);
	}
	/**
	 * 格式化审批流程
	 * @param array $list 要格式的数据
	 * @return array $list 格式后的数据
	 */
	protected function _template_format($list = array()) {

		if (!empty($list)) {
			foreach ($list as &$v) {
				$v['_created'] = rgmdate($v['aft_created'], 'Y-m-d H:i');
				$v['_is_use'] = $v['is_use'] ? '启用' : '禁用';
			}
		}

		return $list;
	}

	/**
	 * 删除接口
	 */
	public function Delete_post() {

		$params = I('post.');
		$af_id = $params['aft_id'];
		if (empty($params['aft_id'])) {
			E('_ERR_MISS_PARAMETER_AFID');
			return false;
		}

		$conds['aft_id'] = $params['aft_id'];
		//删除审批记录
		$serv_askfor = D('Askfor/AskforTemplate', 'Service');
		$serv_askfor->delete_by_conds($conds);
		//更新缓存
		clear_cache();

		//删除关联自定义字段
		$serv_att = D('Askfor/AskforCustomcols', 'Service');
		$serv_att->delete_by_conds($conds);

		return true;
	}

	/**
	 * 模板开启/禁用接口
	 */
	public function Open_post(){

		$params = I('post.');
		//参数验证
		if(empty($params['aft_id'])){
			E('_ERR_MISS_PARAMETER_AFTID');
			return false;
		}

		//参数
		$aft_id = $params['aft_id'];
		$serv_template = D('Askfor/AskforTemplate');
		$value = $params['value'];

		$data = array();
		//更改记录开启状态
		if($value == AskforTemplateModel::ISUSE){
			$data['is_use'] = AskforTemplateModel::ISUSE;
		}elseif($value == AskforTemplateModel::NOUSE){
			$data['is_use'] = AskforTemplateModel::NOUSE;
		}
		//更改操作
		$conds_open['aft_id'] = $aft_id;
		$serv_template->update_by_conds($conds_open, $data);
		//跟新缓存
		clear_cache();

		return true;
	}

}
