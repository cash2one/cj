<?php
/**
 * voa_c_admincp_manage_member_delete
 * 删除员工信息
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_manage_member_delete extends voa_c_admincp_manage_member_base{

    //deprecated
	public function execute(){
return;
		// 批量删除
		$id = rintval((array)$this->request->get('id'), true);
		// 单独删除
		$m_uid = rintval($this->request->get('m_uid'), false);
		// 删除总数
		$total = (int)$this->request->get('total');

		// 超级管理员不能删除
		if (1 == $m_uid || $this->_user['m_uid'] == $m_uid) {
			$this->message('error', '删除操作失败, 不能删除自己或管理员。');
			return false;
		}

		if ($m_uid > 0) {
			// 单独删除请求

			$ids = array($m_uid => $m_uid);
			if (!$total) {
				// 第一次请求，则设定待删除总数
				$total = 1;
			}
		} else {
			// 批量删除请求

			$ids = array();
			foreach ($id as $_id) {
				if (isset($ids[$_id]) && $_id > 0) {
					continue;
				}
				$ids[$_id] = $_id;
			}
			unset($_id);
			if (!$total) {
				// 第一次请求，则设定待删除总数
				$total = count($ids);
			}
		}

		if (!$total) {
			$this->message('error', '请指定待删除的员工');
		}

		$m_uids = array();
		foreach ($ids as $_id) {
			// 不能删除自身
			if (1 == $_id || $this->_user['m_uid'] == $_id) {
				continue;
			}

			$m_uids[] = $_id;
		}
		// 当前进程要删除的m_uid
		$m_uid = $m_uids[0];
		unset($m_uids[0]);

		if ($this->_uda_member_delete->delete($m_uid)) {
			// 删除成功

			$next_m_uids = array_values($m_uids);

			// 未删除的数量
			$delete_count = count($m_uids);
			// 已删除数量
			$deleted_count = ($total - $delete_count);
			// 显示进度（已删数/总数）
			$delete_show = $deleted_count.' / '.$total;
			// 显示进度百分比数值
			$progress = round($deleted_count/$total, 2) * 100;


			if ($deleted_count >= $total) {
				$msg = '指定员工信息已删除完毕';
				$url = $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id);
			} else {

				$msg = <<<EOF
<p>正在删除指定的员工记录： {$delete_show}</p>
<div class="progress">
	<div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="{$progress}" aria-valuemin="0" aria-valuemax="100" style="width: {$progress}%;">
		<span class="sr-only">{$delete_show}</span>
	</div>
</div>
EOF;
				$url = $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('id' => $m_uids, 'total' => $total));
			}

			$this->message('success', $msg, $url, false);
		} else {
			// 删除失败
			$this->message('error', '删除员工操作失败。错误代码：'.$this->_uda_member_delete->errcode);
		}


		return false;
	}

}
