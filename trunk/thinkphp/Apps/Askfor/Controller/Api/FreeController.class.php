<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 15/11/11
 * Time: 上午9:48
 */
namespace Askfor\Controller\Api;

use Askfor\Model\AskforModel;
use Askfor\Model\AskforProcModel;

class FreeController extends AbstractController {

	const ASKING = 'asking'; // 审批中
	const APPROVE = 'approve'; // 已通过
	const REFUSE = 'refuse'; // 已驳回

	/**
	 * 新建自由流程初始化数据接口
	 */
	public function Initial_get() {

		//获取默认审批人和默认抄送人数据
		$serv_draft = D('Askfor/AskforDraft', 'Service');
		$last_info = $serv_draft->get_last($this->_login->user['m_uid']);
		$splist = array();
		$cslist = array();

		if (isset($last_info['splist'])) {
			$splist = array_values($last_info['splist']);
		}
		if (isset($last_info['cslist'])) {
			$cslist = array_values($last_info['cslist']);
		}

		//返回值
		$this->_result = array(
			'splist' => $splist,
			'cslist' => $cslist,
		);
	}

	/**
	 * 新建自由流程新建操作接口
	 */
	public function Insert_post() {

		//审批数据入库
		$serv_askfor = D('Askfor/Askfor', 'Service');

		$params = I('post.');

		$params['m_uid'] = $this->_login->user['m_uid'];
		$params['m_username'] = $this->_login->user['m_username'];
		//判断审批人非空
		if (empty($params['s_uid'])) {
			E('_ERR_MISS_SPLIST');

			return false;
		}
		if (empty($params['c_uid'])) {
			$params['c_uid'] = array();
		}
		//整合人员数据
		$all_uid = array_merge($params['s_uid'], $params['c_uid']);
		//获取相关的人员信息
		$serv_member = D('Common/Member', 'Service');
		$conds_mem['m_uid'] = $all_uid;
		$all_list = $serv_member->list_by_conds($conds_mem);

		//以m_uid做键
		$tmp_m = array();
		foreach ($all_list as $uids) {
			$tmp_m[$uids['m_uid']] = $uids;
		}
		//格式审批人和抄送人信息
		$params = $this->info_format($params, $tmp_m);
		if (!$params) {
			return false;
		}

		//新建自由流程入库
		$af_id = $serv_askfor->free_insert($params);

		//审批人抄送人入库
		$serv_proc = D('Askfor/AskforProc', 'Service');
		$serv_proc->sp_add($params, $af_id);

		//抄送人非空
		if (!empty($params['c_uids'])) {
			//抄送人信息入库
			$serv_proc->cs_add($params, $af_id);
		}

		//如果有上传图片
		if (!empty($params['atids'])) {
			$serv_attachment = D('Askfor/AskforAttachment', 'Service');
			$serv_attachment->img_add($params, $af_id);
		}

		//更新审批人和抄送人
		$serv_draft = D('Askfor/AskforDraft', 'Service');
		//判断之前是否有记录
		$conds_first['m_uid'] = $this->_login->user['m_uid'];
		$first_r = $serv_draft->get_by_conds($conds_first);
		//有记录更改
		if (!empty($first_r)) {
			$serv_draft->update_by_conds_draft($params['m_uid'], $params['s_uids'], $params['c_uids']);
		} else {
			$serv_draft->insert_draft($params['m_uid'], $params['s_uids'], $params['c_uids']);
		}

		//发消息给审批人
		$data['af_id'] = $af_id;
		$data['aft_id'] = 0;
		$data['title'] = '您收到一条待处理审批';
		$data['content'] = "主题：".$params['af_subject']."\n内容：".$params['af_message']."\n申请人：".$this->_login->user['m_username'];
		$this->send_msg($data, $params['s_uid']);

		//发消息给抄送人
		$data_c['af_id'] = $af_id;
		$data_c['aft_id'] = 0;
		$data_c['title'] = '抄送'.$this->_login->user['m_username'].'审批申请';
		$data_c['content'] = "主题：".$params['af_subject']."\n申请人：".$this->_login->user['m_username'];
		$this->send_msg($data_c, $params['c_uid']);

		//返回值
		$this->_result = array(
			'af_id' => $af_id,
		);
	}

	/**
	 * 我发起的列表接口
	 */
	public function ListSend_get() {

		//实例化
		$serv_askfor = D('Askfor/Askfor', 'Service');
		$params = I('get.');
		$type = $params['type'];
		$page = $params['page'];
		$limit = $params['limit'];
		// 判断是否为空
		if (empty($params['page'])) {
			$page = 1;
			$params['page'] = 1;
		}
		if (empty($params['limit'])) {
			$limit = 10;
			$params['limit'] = 10;
		}
		// 分页参数
		list($start, $limit, $page) = page_limit($page, $limit);
		// 分页参数
		$page_option = array($start, $limit);
		$list = array();
		//判断请求的类型
		$count = 0;
		switch ($type) {
			//审批中
			case self::ASKING:
				$temp = $serv_askfor->list_asking($page_option, $this->_login->user['m_uid']);
				$count = $serv_askfor->count_list_asking($this->_login->user['m_uid']);
				break;
			//已审批
			case self::APPROVE:
				$temp = $serv_askfor->list_approve($page_option, $this->_login->user['m_uid']);
				$count = $serv_askfor->count_list_approve($this->_login->user['m_uid']);
				break;
			//已驳回
			case self::REFUSE:
				$temp = $serv_askfor->list_refuse($page_option, $this->_login->user['m_uid']);
				$count = $serv_askfor->count_list_refuse($page_option, $this->_login->user['m_uid']);
				break;
		}
		//格式返回的数据
		if (!empty($temp)) {
			$list = $serv_askfor->format_send($temp);
		}
		//返回值
		$this->_result = array(
			'result' => $list,
			'count' => (int)$count,
			'page' => $page,
			'limit' => $limit,
		);
	}

	/**
	 * 格式审批人和抄送人信息
	 * @param $params array 接收参数
	 * @param $tmp_m array 用户信息列表
	 * @return mixed array $params
	 */
	public function info_format($params, $tmp_m) {

		//格式提交的审批人和抄送人信息
		foreach ($params['s_uid'] as $v_s) {
			if (isset($tmp_m[$v_s])) {
				$params['s_uids'][$v_s]['m_uid'] = $tmp_m[$v_s]['m_uid'];
				$params['s_uids'][$v_s]['m_username'] = $tmp_m[$v_s]['m_username'];
				$params['s_uids'][$v_s]['m_face'] = $tmp_m[$v_s]['m_face'];
			} else {
				E('_ERR_SP_NOT_EXISTS');

				return false;
			}
		}
		//审批人不能为空
		if (empty($params['s_uids'])) {
			E('_ERR_MISS_SPLIST');

			return false;
		}
		//如果抄送人是自己则不抄送
		if (!empty($params['c_uid'])) {
			foreach ($params['c_uid'] as $va) {
				if ($va == $params['m_uid']) {
					E('_ERR_USER_IS_NOT_CS');

					return false;
				}
			}
		}
		//如果抄送人中有审批人
		foreach ($params['s_uid'] as $s) {
			foreach ($params['c_uid'] as $c) {
				if ($s == $c) {
					E('_ERR_CS_IS_NOT_SP');

					return false;
				}
			}
		}

		//审批人不能是自己
		foreach ($params['s_uids'] as $v) {
			if ($params['m_uid'] == $v['m_uid']) {
				E('_ERR_RECUR_SPLIST');

				return false;
			}
		}
		//格式抄送人
		if (!empty($params['c_uid'])) {
			foreach ($params['c_uid'] as $v_s) {
				if (isset($tmp_m[$v_s])) {
					$params['c_uids'][$v_s]['m_uid'] = $tmp_m[$v_s]['m_uid'];
					$params['c_uids'][$v_s]['m_username'] = $tmp_m[$v_s]['m_username'];
					$params['c_uids'][$v_s]['m_face'] = $tmp_m[$v_s]['m_face'];
				} else {
					E('_ERR_CS_NOT_EXISTS');

					return false;
				}
			}
		} else {
			$params['c_uids'] = array();
		}

		return $params;
	}
}
