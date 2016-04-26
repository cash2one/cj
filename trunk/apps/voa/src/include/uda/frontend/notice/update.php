<?php
/**
 * voa_uda_frontend_notice_update
 * 统一数据访问/通知公告/更新（新增、编辑）
 * 涉及通知公告添加、更新以及状态改变
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_notice_update extends voa_uda_frontend_notice_base {

	/**
	 * 添加/编辑 一个通知公告
	 * @param array $history 公告旧数据，新增则使用表字段默认值
	 * @param array &$notice 公告新数据
	 * @return boolean
	 */
	public function notice_update($cp_pluginid, $history, &$notice, $attach_view_base_url) {
		$gps = array(
				'nt_subject' => 'val_subject',// 标题
				'nt_message' => 'val_message',// 正文
				'nt_author' => 'val_author',// 发布者
				'nt_tag' => 'var_tag',// 标签
				'nt_receiver' => array('var_receiver', 'array'),// 接受部门数组
				'nt_repeattimestamp' => array('val_repeattimestamp', 'int'),// 重复提醒的时间间隔
		);

		// 检查设置项
		if (!$this->_submit2table($gps, $notice, $history)) {
			return false;
		}

		if ($this->_request->get('nt_repeattimestamp') > 0 && empty($notice['nt_receiver'])) {
			// 设置了重复提醒时间 但并未设置要提醒的人，则重置为不重复提醒
			$notice['nt_repeattimestamp'] = 0;
		}

		if (!$notice) {
			$this->errmsg('201', '没有更新无须提交');
			return false;
		}

		$at_ids = array();
		if (isset($notice['nt_message'])) {
			// 如果公告内容发生改变，则尝试获取附件at_id以及替换附件地址为[attach]at_id[/attach]
			$ueditor = new ueditor();
			$ueditor->attachment_url_to_bbcode($notice['nt_message'], $attach_view_base_url, $at_ids);
		}

		// 更新到数据库
		$serv = &service::factory('voa_s_oa_notice');
		$serv_attach = &service::factory('voa_s_oa_notice_attachment');
		$serv_to = &service::factory('voa_s_oa_notice_to');

		// 接收部门列表
		$nt_receiver = false;
		if (isset($notice['nt_receiver'])) {
			$nt_receiver = $notice['nt_receiver'];
			$notice['nt_receiver'] = serialize($notice['nt_receiver']);
		}

		try {
			$serv->begin();

			if (empty($history['nt_id'])) {
				// 新增公告
				$nt_id = $serv->insert($notice, true);
				if (!$nt_id) {
					$this->errmsg('202', '新增公告信息到数据库发生错误');
					return false;
				}
			} else {
				// 更新通告
				$nt_id = $history['nt_id'];
				$serv->update($notice, array('nt_id' => $nt_id));
			}

			$notice['nt_id'] = $nt_id;

			if ($notice['nt_id'] && $at_ids) {
				// 更新附件
				$old_notice_attachment_list = $serv_attach->fetch_by_nt_id_at_id($notice['nt_id'], $at_ids);
				foreach ($at_ids as $_at_id) {
					if (!isset($old_notice_attachment_list[$_at_id])) {
						// 该附件不存在于公告附件表，则插入
						$serv_attach->insert(array(
								'nt_id' => $notice['nt_id'],
								'at_id' => $_at_id
						));
					}
				}
			}

			if (empty($history['nt_id']) || $nt_receiver !== false) {
				// 新增 或 接收部门发生改变

				if (empty($history['nt_id'])) {
					// 新增

					if (empty($nt_receiver)) {
						$nt_receiver = array(0);
					}
					foreach ($nt_receiver as $_cd_id) {
						$serv_to->insert(array(
							'nt_id' => $nt_id,
							'cd_id' => $_cd_id
						));
					}
				} else {
					// 编辑
					$old_receiver = @unserialize($history['nt_receiver']);
					if ($old_receiver != $nt_receiver) {
						// 发生改变
						$tmp_history = empty($history['nt_receiver']) ? array(0) : $history['nt_receiver'];
						$tmp_new = empty($nt_receiver) ? array(0) : $nt_receiver;
						// 找到需要删除的历史
						$delete_cd_id = array();
						foreach ($tmp_history as $_cd_id) {
							if (!isset($tmp_new[$_cd_id])) {
								$delete_cd_id[$_cd_id] = $_cd_id;
							}
						}
						// 找到需要增加的
						foreach ($tmp_new as $_cd_id) {
							if (!isset($tmp_history[$_cd_id])) {
								$add_cd_id[$_cd_id] = $_cd_id;
							}
						}

						if ($delete_cd_id) {
							// 删除
							$serv_to->delete_by_nt_id_cd_id($history['nt_id'], $delete_cd_id);
						}

						if ($add_cd_id) {
							// 新增
							foreach ($add_cd_id as $_cd_id) {
								$serv_to->insert(array(
									'nt_id' => $history['nt_id'],
									'cd_id' => $_cd_id
								));
							}
						}
					}
				}
			}

			// 更新后的公告数据
			$notice = array_merge($history, $notice);

			$serv->commit();

		} catch (Exception $e) {
			$serv->rollback();
			/** 入库操作失败 */
			$this->errmsg(100, '操作失败');
			return false;
		}

		// 尝试发送通知
		$this->send_remind($notice, $cp_pluginid);

		return true;
	}

	/**
	 * 发送单个通知公告的提醒通知
	 * @param array $notice 通知公告的数据
	 * @return boolean
	 */
	public function send_remind($notice, $cp_pluginid) {
		if (empty($notice)) {
			return false;
		}

		$to_partys = '';
		$to_users = '';
		if (empty($notice['nt_receiver']) || !($receiver = @unserialize($notice['nt_receiver']))) {
			// 无接收人，则发送给所有人
			$to_users = '@all';
		}

		if (empty($notice['_created'])) {
			// 可读的发布时间
			$notice['_created'] = !empty($notice['nt_created']) ? rgmdate($notice['nt_created'], 'Y-m-d H:i') : rgmdate(startup_env::get('timestamp'), 'Y-m-d H:i');
		}

		// 缓存配置
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$tplids = $sets['wxtplids'];
		$domain = $sets['domain'];

		// 读取应用表获取应用型代理id
		$serv_plugin = &service::factory('voa_s_oa_common_plugin');
		$plugin = $serv_plugin->fetch_by_cp_pluginid($cp_pluginid);
		$agentid = $plugin['cp_agentid'];

		// 消息发送实例
		$serv_qy = voa_wxqy_service::instance();

		$scheme = config::get('voa.oa_http_scheme');
		$url = $serv_qy->oauth_url($scheme.$domain.'/notice/view/'.$notice['nt_id']);

		$message = array();
		/*
		$message[] = '【通知公告】';
		$message[] = $notice['nt_subject'];
		if ($notice['nt_author']) {
			$message[] = '[发自] '.$notice['nt_author'];
		}
		$message[] = '[时间] '.$notice['_created'];
		$message[] = '';
		$message[] = '<a href="'.$url.'">点击查看详情</a>';
		*/
		$message[] = '标题：'.$notice['nt_subject'];
		$message[] = '时间：'.$notice['_created'];
		$message[] = '<a href="'.$url.'">点击查看详情</a>';

		$cd_ids = @unserialize($notice['nt_receiver']);
		if (!empty($cd_ids) && !isset($cd_ids[0])) {
			// 不是发送给所有部门的，则找到对应的部门的微信id
			$qywx_partyids = array();
			$serv_department = &service::factory('voa_s_oa_common_department');
			foreach ($serv_department->fetch_all_by_key($cd_ids) as $cd) {
				if ($cd['cd_qywxid']) {
					$qywx_partyids[] = $cd['cd_qywxid'];
				}
			}
			if ($qywx_partyids) {
				// 发给指定部门
				$to_partys = implode('|', $qywx_partyids);
			}
		}

		// 执行发送
		$serv_qy->post_text(implode("\r\n", $message), $agentid, $to_users, $to_partys);

		return true;
	}

	/**
	 * 标记某（多）个阅读用户已读指定的公告
	 * @param array $notice 通知公告数据
	 * @param number | array $m_uids 阅读者m_uid，也可以为一个数组
	 * @return boolean
	 */
	public function marked_read($notice, $m_uids) {
		$serv_read = &service::factory('voa_s_oa_notice_read');
		if (is_numeric($m_uids)) {
			$m_uids = array($m_uids);
		}
		if (is_numeric($notice)) {
			$serv_notice = &service::factory('voa_s_oa_notice');
			$notice = $serv_notice->fetch_by_id($notice);
			if (empty($notice)) {
				return false;
			}
		}

		// 找到已读的
		$readed = array();
		foreach ($serv_read->fetch_all_by_nt_id_m_uid($notice['nt_id'], $m_uids) as $r) {
			$readed[$r['m_uid']] = $r['m_uid'];
		}

		// 遍历写入已读的列表
		foreach ($m_uids as $m_uid) {
			if (isset($readed[$m_uid])) {
				// 已读，则忽略
				continue;
			}
			$this->serv_read->insert(array(
				'nt_id' => $notice['nt_id'],
				'm_uid' => $m_uid
			));
		}
	}


}
