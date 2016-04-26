<?php

/**
 * voa_c_cyadmin_api_message_list
 * 消息列表
 * author ppker
 * $Id$
 */
class voa_c_cyadmin_api_message_list extends voa_c_cyadmin_api_base {


	protected $_pay_status = array();

	public function execute() {

		$this->_pay_status = array(
			1 => '已付费',
			2 => '已付费-即将到期',
			3 => '已付费-已到期',
			5 => '试用期-即将到期',
			6 => '试用期-已到期',
			7 => '试用期'
		);

		if (!empty($this->_get['page'])) {
			$page = $this->_get['page'];
		} else {
			$page = 1;
		}

		if (!empty($this->_get['info_'])) {

			$string = authcode(rbase64_decode($this->_get['info_']), config::get('voa.development.cyadmin.urlkey'), 'DECODE');
			$info = explode('`', $string);
			$uid = $info['0'];
			$epid = $info['1'];
			//身份验证
			$serv_admin = &service::factory('voa_s_cyadmin_enterprise_profile');

			if (!$serv_admin->fetch($epid)) {
				$this->_errcode = '10009';
				$this->_errmsg = '非法操作';

				return false;
			}

		} else {
			$this->_errcode = '10009';
			$this->_errmsg = '非法操作';

			return false;
		}

		//  记录已读消息  扩展成单多条的情况
		if (!empty($this->_get['logid'])) {
			$logid = $this->_get['logid'];
			// 统统的转化成数组
			$logid_array = explode(',', $logid);

			$read = &uda::factory('voa_uda_cyadmin_enterprise_message_read');

			foreach ($logid_array as $k => $v) {
				$data['logid'] = $v;
				$data['uid'] = $uid;
				$re = $read->insert($data); // 某条失败的话暂时忽略
			}

			return true;
		}

		if (!empty($this->_get['title'])) {
			$title = "%" . $this->_get['title'] . "%";
		} else {
			$title = '';
		}


		$message = array();
		$uda = &uda::factory('voa_uda_cyadmin_enterprise_msglist');
		$all_count = '';
		$multi = '';
		$uda->list_page($page, $uid, $epid, $message, $all_count, $multi, $title); // 加上一个title的搜索

		// 获取应用配置信息 add by 10-26
		$appset = &uda::factory('voa_uda_cyadmin_enterprise_appset');
		$pay_ser = &service::factory('voa_s_cyadmin_company_paysetting');
		$appset_data = $appset->list_all();

		$true_appset = array(
			'trydate' => $appset_data['trydate']['value'],
			'syq_jjdq_set' => $appset_data['syq_jjdq_set']['value'],
			'yff_jjdq_set' => $appset_data['yff_jjdq_set']['value']
		);
		// 获取企业信息 套件 应用
		$status_pay = array();

		if (!empty($this->_get['ep_id'])) {
			$ep_id = (int)$this->_get['ep_id'];
		} else {
			$ep_id = null;
		}

		if (!empty($ep_id)) {
			// 套件列表啊
			$tao = voa_h_cache::get_instance()->get('domain_applist', 'cyadmin');
			$ep_data = $pay_ser->list_by_conds(array('ep_id = ?' => $ep_id)); // 多个

			foreach ($ep_data as $k => $v) {
				$status_pay[$k][] = $tao[$v['cpg_id']]['cpg_name'];
				$status_pay[$k][] = $this->_pay_status[$v['pay_status']];
			}
		}

		// 获取未读的消息总数返回给头部文件中
		if (!empty($this->_get['num'])) {
			// 输出结果
			$this->_result = array(
				'total' => $all_count,
				'status_pay' => $status_pay
			);
			//输出jsonp类型
			$this->_output($errcode = 0, $errmsg = '', $result = array(), $type = 'jsonp');
		}


		$list = array();
		if ($message) {
			//$uda->_formdata($message,$list);
			foreach ($message as &$_val) {
				$_val['created'] = rgmdate($_val['created'], 'Y-m-d  H:i');
			}
			$list = $message;
		}

		// 输出结果
		$this->_result = array(
			'total' => $all_count,
			'list' => $list,
			'multi' => $multi
		);

		//var_dump(date('Y-m-d H:i:s','1440559658'));die;
		//输出jsonp类型
		$this->_output($errcode = 0, $errmsg = '', $result = array(), $type = 'jsonp');
	}


}
