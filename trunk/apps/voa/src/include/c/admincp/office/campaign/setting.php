<?php
/**
 * 企业后台/微办公管理/活动/设置分类
 * Create By linshiling
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_campaign_setting extends voa_c_admincp_office_campaign_base {

	public function execute() {

		$act = $this->request->get('act');
		if ($act) {
			$this->$act();
			exit();
		}

		// 取得分类列表
		$t = new voa_d_oa_campaign_type();
		$list = $t->list_all(null, array('order_sort' => 'asc'));
		$this->view->set('cats', $list);

		$this->output('office/campaign/setting');
	}

	private function delete() {

		$id = intval($_GET['id']);
		if (! $id) {
			$this->ajax(0, '参数错误');
		}

		$d = new voa_d_oa_campaign_campaign();
		$act = $d->list_by_conds(array('typeid' => $id));

		// 首次删除,如果分类下有活动,提醒用户是否确认
		if (! isset($_GET['force']) && $act) {
			$this->ajax(2, '分类下有活动,如果删除分类会删除其下所有活动,是否确认?');
		}

		// 用户确认后,强制删除,会删除分类下的活动,权限,统计等所有相关数据
		if ($act) {
			$ids = array();
			foreach ($act as $a) {
				$ids[] = $a['id'];
			}

			// 删除其下的活动数据
			$act = new voa_s_oa_campaign();
			$rs = $act->del_act($ids);
			if (! $rs) {
				$this->ajax(0, '删除活动数据失败');
			}
		}

		// 删除分类
		$type = new voa_d_oa_campaign_type();
		$rs = $type->delete($id);
		if (! $rs) {
			$this->ajax(0, '删除分类失败');
		}

		$this->ajax(1);
	}

	// 批量保存
	private function save() {

		if (! $_POST) {
			$this->ajax(0, '数据为空');
		}

		$type = new voa_d_oa_campaign_type();
		$ids = array();
		foreach ($_POST['id'] as $k => $id) {
			$data = array('order_sort' => intval($_POST['order_sort'][$k]), 'title' => strip_tags($_POST['title'][$k]));
			if ($id) {
				// 修改
				$type->update($id, $data);
			} else {
				// 添加
				$row = $type->insert($data);
				$ids[] = $row['id'];
			}
		}

		//返回新增的id,以修改前端隐藏input的值
		$this->ajax(1, $ids);
	}
}
