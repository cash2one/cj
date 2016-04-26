<?php
/**
 * voa_uda_frontend_seperreport_delete
 * 统一数据访问/超级报表/删除报表
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_superreport_delete extends voa_uda_frontend_superreport_abstract {

	/** diy uda 类 */
	private $__diy = null;

	/**
	 * 初始化
	 */
	public function __construct() {
		parent::__construct();

		if ($this->__diy === null) {
			$this->__diy = new voa_uda_frontend_diy_data_get();
			$this->_init_diy_data($this->__diy);
		}
	}

	/**
	 * 根据条件删除报表
	 * @param array $ids 报表ID数组
	 */
	public function delete($ids) {
		$s_detail = new voa_s_oa_superreport_detail();
		$s_comment = new voa_s_oa_superreport_comment();
		$s_attach = new voa_s_oa_superreport_attachment();

		//删除月报、报表、报表详情、报表评论、报表附件
		try {
			$s_detail->beginTransaction();

			/** 删除月报数据 */
			$tablecols = $this->tablecol;
			$service_month = new voa_s_oa_superreport_monthlyreport();

			foreach ($ids as $dr_id) {
				$row = array();
				$this->__diy->execute(array('dr_id' => $dr_id), $row); //日报数据
				$year = date('Y', $row['created']);
				$month = date('m', $row['created']);
				$months = $service_month->get_month_data($year, $month, $row['csp_id']);  //月报数据
				if ($months) {
					$month_list = array();
					$updates = array();
					foreach ($months as $v) {  //取回本月数据
						$month_list[$v['fieldname']] = $v['fieldvalue'];
					}
					foreach ($tablecols as $col) {
						if ($col['ct_type'] == 'int') { //如果是int型字段，则将本日数据从本月数据中减去
							$updates[$col['field']] = (float)$month_list[$col['field']] - (float)$row[$col['field']];
						}
					}
					//更新本月统计数据
					if (!empty($updates)) {
						foreach ($updates as $k => $update) {
							$conds = array(
								'csp_id' => $row['csp_id'],
								'year' => $year,
								'month' => $month,
								'fieldname' => $k
							);
							$service_month->update_by_conds($conds, array('fieldvalue' => $update));
						}
					}
				}
			}
			/** 删除月报数据 end */

			$conds = array('dr_id' => $ids);

			$result = array();
			$uda_diy_delete = new voa_uda_frontend_diy_data_delete();
			$this->_init_diy_data($uda_diy_delete);
			$uda_diy_delete->execute($conds, $result);  		            //删除报表
			$s_detail->delete_by_conds($conds);                             //删除报表详情
			$s_comment->delete_by_conds($conds);                            //删除报表评论
			$s_attach->delete_by_conds($conds);                             //删除报表附件

			$s_detail->commit();
		} catch (Exception $e) {
			$s_detail->rollBack();

			return $this->set_errmsg(voa_errcode_api_superreport::DELETE_REPORT_FAILED);
		}
		return true;
	}

	/**
	 * 统一发送出口方法
	 * @return boolean
	 */
	public  function send_wxqy_notice($row,$session_obj) {

		$openids = array(); //人员与微信相关ID
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$user = $serv_m->fetch_by_uid($row['m_uid']);
		$openids[] = $user['m_openid'];

		// 微信消息内容，数组形式，便于后面组织排版，每个键名一行
		$content = array();
		$content[] = "您{$row['cdate']}的报表数据被管理员删除";

		// 构造微信消息发送需要的数据
		$data = array(
			'mq_touser' => implode('|', $openids),
			'mq_toparty' => '',
			'mq_msgtype' => voa_h_qymsg::MSGTYPE_TEXT,
			'mq_agentid' => (int)$this->plugin_setting['agentid'],
			'mq_message' => implode("\r\n", $content)
		);
		// 推入待发送队列
		voa_h_qymsg::push_send_queue($data);
		// 将队列ID写入成员变量，便于调用时提取写入cookie
		voa_h_qymsg::set_queue_session(array($data['mq_id']), $session_obj);

		return true;
	}

}
