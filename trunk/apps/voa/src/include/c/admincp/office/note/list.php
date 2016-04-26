<?php
/**
* 知识列表
* Create By wowxavi
* $Author$
* $Id$
*/
class voa_c_admincp_office_note_list extends voa_c_admincp_office_note_base {

	public function execute() {
		$this->output('office/note/list');
	}

	/**
	 * 重构搜索条件
	 * @param array $searchDefault 初始条件
	 * @param array $searchBy 输入的查询条件
	 * @param array $conditons 组合的查询
	 */
	protected function _parse_search_cond($search_default, &$search_conds, &$conditons) {
		foreach ( $search_default AS $_k=>$_v ) {
			if ( isset($_GET[$_k]) && $_v != $this->request->get($_k) ) {
				$search_conds[$_k] = $this->request->get($_k);
				if ($_k == 'updated_begintime') {
					$conditons['updated>=?'] = rstrtotime($this->request->get($_k));
				} elseif ($_k == 'updated_endtime') {
					$conditons['updated<?'] = rstrtotime($this->request->get($_k)) + 86400;
				} elseif ($_k == 'title') {
					$conditons['title LIKE ?'] = '%'.($this->request->get($_k)).'%';
				} elseif ($_k == 'm_username') {
					$conditons['m_username LIKE ?'] = '%'.($this->request->get($_k)).'%';
				} elseif ($_k == 'cid') {
					$cid = $this->request->get($_k);
					if($cid > 0){
						$serv = &service::factory('voa_s_oa_note_category');
						$cid_arr = array($cid);
			        	$catas = $serv->list_by_conds(array('pid'=>$cid));
			        	foreach ($catas as $v) {
							$cid_arr[] = $v['id'];
						}
						$conditons['cid IN (?)'] = $cid_arr;
					}
				} else {
					$conditons[$_k] = ($this->request->get($_k));
				}
			}
		}
		return true;
	}
	
}