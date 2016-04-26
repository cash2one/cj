<?php
/**
 * Create By wogu
 * $Author$
 * $Id$
 */

class voa_s_oa_exam_tj extends voa_s_abstract {

	protected $_d_class = null;
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
		if ($this->_d_class == null) {
			$this->_d_class = new voa_d_oa_exam_tj();
		}
	}

	public function list_by_ids($ids) {
		return $this->_d_class->list_by_pks($ids);
	}

	public function delete_by_paper_id($paperid) {
		return $this->_d_class->delete_by_paper_id($paperid);
	}

	public function list_by_paper_id($paperid, $status = null) {
		$conds = array('paper_id' => $paperid);
		if(null !== $status) {
			$conds['status'] = $status;
		}
		
		return $this->_d_class->list_by_conds($conds);
	}

	public function get_tj_by_paper_id($paperid) {
		$tjs = $this->_d_class->list_by_conds(array('paper_id' => $paperid));
		$not_join_num = $join_num = $complete_num = 0;
		if(!empty($tjs)) {
			foreach($tjs as $tj) {
				switch ($tj['status']) {
					case 0:
						$not_join_num++;
						break;
					case 1:
						$join_num++;
						break;
					case 2:
						$join_num++;
						break;
				}
			}
		}

		return array('not_join' => $not_join_num, 'join' => $join_num, 'complete' => $complete_num);
	}


	public function list_stats_by_conds($conds, $start, $limit) {
		return $this->_d_class->list_stats_by_conds($conds, $start, $limit);
	}

}
