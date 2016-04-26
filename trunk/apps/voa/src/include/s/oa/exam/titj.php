<?php
/**
 * Create By wogu
 * $Author$
 * $Id$
 */

class voa_s_oa_exam_titj extends voa_s_abstract {

	protected $_d_class = null;
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
		if ($this->_d_class == null) {
			$this->_d_class = new voa_d_oa_exam_titj();
		}
	}

	public function list_by_paper_id($paperid) {
		$tjs = $this->_d_class->list_by_conds(array('paper_id' => $paperid));
		$return = array();
		foreach($tjs as $tj) {
			$return[$tj['ti_id']] = $tj;
		}

		return $return;
	}

	public function list_by_tj_id($tj_id) {
		$tjs = $this->_d_class->list_by_conds(array('tj_id' => $tj_id));
		$return = array();
		foreach($tjs as $tj) {
			$return[$tj['ti_id']] = $tj;
		}

		return $return;
	}
}
