<?php
/**
 * 日报相关的入库操作
 * $Author$
 * $Id$
 */
class voa_uda_frontend_dailyreport_insert extends voa_uda_frontend_dailyreport_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 对报告的回复
	 * @param array $post (引用结果)回复信息
	 * @param array $dailyreport (引用结果)日报信息
	 * @return boolean
	 */
	public function dailyreport_reply(&$post, &$dailyreport = array()) {
		// 内容
		$message = (string) $this->_request->get('message');
		if (! $this->val_message($message)) {
			return false;
		}

		$message = nl2br($message);

		// 报告 id
		$dr_id = intval($this->_request->get('dr_id'));

		// 获取报告信息
		$serv = &service::factory('voa_s_oa_dailyreport', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$dailyreport = $serv->fetch_by_id($dr_id);
		if (empty($dr_id) || empty($dailyreport)) {
			$this->set_errmsg(voa_errcode_api_dailreport::VIEW_NOT_EXISTS);
			return false;
		}

		// 获取报告用户
		$serv_m = &service::factory('voa_s_oa_dailyreport_mem', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$mem = $serv_m->fetch_by_conditions(array(
			'dr_id' => $dr_id,
			'm_uid' => startup_env::get('wbs_uid')
		));
		if (empty($mem)) {
			$this->set_errmsg(voa_errcode_api_dailreport::VIEW_NO);
			return false;
		}

		// 评论信息入库
		$serv_pt = &service::factory('voa_s_oa_dailyreport_post', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$post = array(
			'dr_id' => $dr_id,
			'm_uid' => startup_env::get('wbs_uid'),
			'm_username' => startup_env::get('wbs_username'),
			'drp_message' => $message
		);
		$drp_id = $serv_pt->insert($post, true);
		$post['drp_id'] = $drp_id;

		return true;
	}

	/**
	 * 新报告入库
	 *
	 * @param unknown $dailyreport
	 * @param unknown $post
	 * @param unknown $mem
	 * @param unknown $cculist
	 * @throws Exception
	 * @return boolean
	 */
	public function dailyreport_new(&$dailyreport, &$post, &$mem, &$cculist) {

		// 报告类型
		$dr_type = (string) $this->_request->get('daily_type');

		// 报告日期+标题
		$report_str = (string) $this->_request->get('reporttime' . $dr_type);
		if (empty($report_str)) {
			$report_str = (string)$this->_request->get('reporttime');
		}
		$report_arry = explode("|", $report_str);

		// 报告内容
		$message = (string) $this->_request->get('message');
		if (! $this->val_message($message)) {
			return false;
		}
		//过滤js代码
		$message = preg_replace( "@<script(.*?)</script>@is", "", $message );

		// 接收人
		$approveuid = (string) $this->_request->get('approveuid');
		if (!$this->val_approveuid($approveuid)) {
			return false;
		}
		if (!is_array($approveuid)) {
			$approveuid = array($approveuid => $approveuid);
		}

        // 抄送人
        $carboncopyuid = (string) $this->_request->get('carboncopyuids');
        $carboncopy = array();

        if (!$this->val_carboncopyuids($carboncopyuid, $carboncopy)) {

            return false;
        }

		$servm = &service::factory('voa_s_oa_member', array());
		// 读取用户信息, 包括日报人和接收人信息
		$ccuids = $approveuid;
        $ccuids = array_merge($ccuids,$carboncopy);
        $alluids = $ccuids; // 作为存日报阅读状态的m_uid
		$ccuids[] = startup_env::get('wbs_uid'); // 报告人id
		$cculist = $servm->fetch_all_by_ids($ccuids);
		// 日报人信息
		$mem = array();
		// 从用户列表中取出日报人信息
		foreach ($cculist as $k => $v) {
			if (isset($approveuid[$v['m_uid']])) {
				$mem[] = $v;
				unset($cculist[$v['m_uid']]);
				//break;
			}
		}

		if (empty($approveuid) || !count($mem)) {
			$this->set_errmsg(voa_errcode_api_dailreport::APPROVEUSER_IS_EMPTY);
			return false;
		}
		// 日报不能发给自己
        foreach ($mem as $member) {
            if ($member['m_uid'] == startup_env::get('wbs_uid')) {
                $this->set_errmsg(voa_errcode_api_dailreport::NEW_APPRAVEUID_SET_NULL);
                return false;
            }
        }

		// 上传的附件id by Deepseath@20141222#310
		$upload_attach_ids = (string) $this->_request->post('at_ids');
		$upload_attach_ids = trim($upload_attach_ids);
		// 检查附件id
		$attach_ids = array();
		// 判断是否上传了附件 且 系统是否允许上传图片
		if (! empty($upload_attach_ids) && ! empty($this->_sets['upload_image'])) {

			// 整理附件id
			foreach (explode(',', $upload_attach_ids) as $_id) {
				if (! is_numeric($_id)) {
					continue;
				}
				$_id = (int) $_id;
				if ($_id > 0 && ! isset($attach_ids[$_id])) {
					$attach_ids[$_id] = $_id;
				}
			}
			// 上传的图片数
			$count = count($attach_ids);
			// 设置了最少上传图片数 且上传的图片数量小于要求的数
			if (! empty($this->_sets['upload_image_min_count']) && $count < $this->_sets['upload_image_min_count']) {
				$this->errmsg(1151, '至少要求上传 ' . $this->_sets['upload_image_min_count'] . ' 张图片，您上传了 ' . $count . ' 张');
				return false;
			}
			// 不能超出系统要求的上传数
			if ($count > $this->_sets['upload_image_max_count']) {
				$this->errmsg(1152, '最多只允许上传 ' . $this->_sets['upload_image_max_count'] . ' 张图片，您已上传了 ' . $count . ' 张');
				return false;
			}
		}

		// 获取附件信息 by Deepseath@20141222#310
		$attachs = array();
		if (! empty($attach_ids)) {
			$serv_at = &service::factory('voa_s_oa_common_attachment', array(
				'pluginid' => 0
			));
			$attachs = array();
			$attachs = $serv_at->fetch_by_conditions(array(
				'at_id' => array(
					$attach_ids,
					'='
				),
				'm_uid' => startup_env::get('wbs_uid')
			));
		}

		// 数据入库
		$serv_dr = &service::factory('voa_s_oa_dailyreport', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$serv_p = &service::factory('voa_s_oa_dailyreport_post', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$serv_m = &service::factory('voa_s_oa_dailyreport_mem', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$serv_drat = &service::factory('voa_s_oa_dailyreport_attachment', array(
			'pluginid' => startup_env::get('pluginid')
		));
        $serv_read = &service::factory('voa_s_oa_dailyreport_read');

		try {
			$servm->begin();
			// 报告标题信息入库
            // 处理报告标题

            $dailyreport_type = array('1' => '日报', '2' => '周报', '3' => '月报', '4' => '季报', '5' => '年报');
            $type_text = $dailyreport_type[$dr_type];
			$dailyreport = array(
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'dr_subject' => sprintf('%s %s',$report_arry[1],$type_text),
				'dr_reporttime' => $report_arry[0],
				'dr_status' => voa_d_oa_dailyreport::STATUS_NORMAL,
				'dr_type' => $dr_type
			);
			$dr_id = $serv_dr->insert($dailyreport, true);
			$dailyreport['dr_id'] = $dr_id;
			$dailyreport['dr_created'] = startup_env::get('timestamp');
			$dailyreport['dr_updated'] = startup_env::get('timestamp');

			if (empty($dr_id)) {
				throw new Exception('dailyreport_new_failed');
			}
			// 报告信息入库
			$post = array(
				'dr_id' => $dr_id,
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'drp_subject' => '',
				'drp_message' => $message,
				'drp_first' => voa_d_oa_dailyreport_post::FIRST_YES
			);
			$drp_id = $serv_p->insert($post, true);

			if (empty($drp_id)) {
				throw new Exception('dailyreport_new_failed');
			}
			// 接收人信息入库
            foreach ($mem as $v){

                $serv_m->insert(array(
                    'dr_id' => $dr_id,
                    'm_uid' => $v['m_uid'],
                    'm_username' => $v['m_username'],
                    'drm_status' => voa_d_oa_dailyreport_mem::STATUS_NORMAL
                ));

            }

            // 抄送人信息入库
            foreach ($cculist as $car) {

                $serv_m->insert(array(
                    'dr_id' => $dr_id,
                    'm_uid' => $car['m_uid'],
                    'm_username' => $car['m_username'],
                    'drm_status' => voa_d_oa_dailyreport_mem::STATUS_CARBON_COPY
                ));

            }

			/*$serv_m->insert(array(
				'dr_id' => $dr_id,
				'm_uid' => $mem['m_uid'],
				'm_username' => $mem['m_username'],
				'drm_status' => voa_d_oa_dailyreport_mem::STATUS_NORMAL
			));*/

			// 抄送人信息入库
			/*foreach ($cculist as $v) {
				// 如果是目标人
				if ($v['m_uid'] == $mem['m_uid']) {
					continue;
				}

				$serv_m->insert(array(
					'dr_id' => $dr_id,
					'm_uid' => $v['m_uid'],
					'm_username' => $v['m_username'],
					'drm_status' => voa_d_oa_dailyreport_mem::STATUS_CARBON_COPY
				));
			}*/

			// 附件入库 by Deepseath@20141222#310
			foreach ($attachs as $v) {
				$serv_drat->insert(array(
					'dr_id' => $dr_id,
					'drp_id' => 0, // 标记为日报的图片
					'at_id' => $v['at_id'],
					'm_uid' => startup_env::get('wbs_uid'),
					'm_username' => startup_env::get('wbs_username'),
					'drat_status' => voa_d_oa_dailyreport_attachment::STATUS_NORMAL
				));
			}

            // 阅读状态入库 by liyongjian@2015-06-30
            foreach ($alluids as $uid) {

                $serv_read->insert(array(
                    'is_read' => 1,
                    'dr_id' => $dr_id,
                    'm_uid' => $uid,
                ));

            }
			$servm->commit();
		} catch (Exception $e) {
			$servm->rollback();
			// 如果 $id 值为空, 则说明入库操作失败
			// $this->errmsg(150, 'dailyreport_new_failed');
			$this->set_errmsg(voa_errcode_api_dailreport::DAILYREPORT_NEW_FAILED);
			return false;
		}

		return true;
	}

	/**
	 * 转发报告
	 * @param unknown $dailyreport
	 * @param unknown $post
	 * @param unknown $mem
	 * @param unknown $cculist
	 * @throws Exception
	 * @return boolean
	 */
	public function dailyreport_forward($dailyreport, &$cculist) {
		// 报告id
		$dr_id = intval($dailyreport['dr_id']);

		// 获取报告信息
		$serv = &service::factory('voa_s_oa_dailyreport', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$dailyreport = $serv->fetch_by_id($dr_id);
		if (empty($dr_id) || empty($dailyreport)) {
			$this->set_errmsg(voa_errcode_api_dailreport::VIEW_NOT_EXISTS);
			return false;
		}

		// 转发人
		if(isset($dailyreport['carboncopyuids'])) {
			$uidstr = $dailyreport['carboncopyuids'];
		} else {
			$uidstr = (string) $this->_request->get('carboncopyuids');
		}

		$ccuids = array();
		if (! $this->val_carboncopyuids($uidstr, $ccuids)) {
			return false;
		}

		$servm = &service::factory('voa_s_oa_member', array());
		// 读取用户信息, 包括日报人和转发人信息
		$cculist = $servm->fetch_all_by_ids($ccuids);

		// 获取报告用户
		$serv_m = &service::factory('voa_s_oa_dailyreport_mem', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$mem = $serv_m->fetch_by_conditions(array(
			'dr_id' => $dr_id
		));

		foreach ($mem as $k => $v) {
			if ($uidstr == $v['m_uid']) {
				unset($cculist[$uidstr]);
			}
		}

		if (empty($cculist)) {
			return true;
		}

		try {
			$serv_m->begin();

			// 转发人信息入库
			foreach ($cculist as $v) {
				// 如果是目标人
				if ($v['m_uid'] == $mem['m_uid']) {
					continue;
				}
				$serv_m->insert(array(
					'dr_id' => $dr_id,
					'm_uid' => $v['m_uid'],
					'm_username' => $v['m_username'],
					'drm_status' => voa_d_oa_dailyreport_mem::STATUS_NORMAL
				));
			}

			$serv_m->commit();
		} catch (Exception $e) {
			$serv_m->rollback();
			// 如果 $id 值为空, 则说明入库操作失败
			$this->set_errmsg(voa_errcode_api_dailreport::DAILYREPORT_NEW_FAILED);
			return false;
		}

		return true;
	}
}
