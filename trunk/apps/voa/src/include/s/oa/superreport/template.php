<?php
/**
 * template.php
 * service/超级报表/模板
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_s_oa_superreport_template extends voa_s_oa_superreport_abstract {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 取回所有模板
	 * @return array
	 */
	public function list_all_templates() {

		$result = array();
		$d_tem_cat = new voa_d_oa_superreport_templatecategory();
		$d_tem = new voa_d_oa_superreport_template();
		$cats = $d_tem_cat->list_all();
		$tems = $d_tem->list_all();
		if ($cats) {
			foreach ($cats as  $cat) { //取回模板类别
				$result[$cat['stc_id']]['stc_id'] = $cat['stc_id'];
				$result[$cat['stc_id']]['title'] = $cat['title'];
				foreach ($tems as $tem) { //取回模板
					if ($cat['stc_id'] == $tem['stc_id']) {
						$result[$cat['stc_id']]['templates']['title'] = $tem['title'];
						$result[$cat['stc_id']]['templates']['content'] = unserialize($tem['content']);
					}
				}
			}
		}

		return $result;
	}

	/**
	 * 格式化日报数据
	 * @param array $list 日报列表
	 * @param int $date 日期
	 * @return array
	 */
	public  function format_template($list) {

		$result = array(
			'int' => array(),
			'text' => array()
		);
		if ($list) {
			foreach ($list as $k => $v) {
				if ($v['ct_type'] == 'text') {
					$result['text'][$k]['tc_id'] = $v['tc_id'];
					$result['text'][$k]['field'] = $v['field'] ? $v['field'] : '_'.$v['tc_id'];
					$result['text'][$k]['fieldname'] = $v['fieldname'];
					if (isset($v['fieldvalue'])) {
						$result['text'][$k]['fieldvalue'] = $v['fieldvalue'];
					}
					$result['text'][$k]['unit'] = $v['unit'];
					$result['text'][$k]['type'] = $v['ct_type'];
					$result['text'][$k]['sort'] = $v['orderid'];
					$result['text'][$k]['required'] = $v['required'];
					$result['text'][$k]['min'] = $v['min'];
					$result['text'][$k]['max'] = $v['max'];
				} else {
					$result['int'][$k]['tc_id'] = $v['tc_id'];
					$result['int'][$k]['field'] = $v['field'] ? $v['field'] : '_'.$v['tc_id'];
					$result['int'][$k]['fieldname'] = $v['fieldname'];
					if (isset($v['fieldvalue'])) {
						$result['int'][$k]['fieldvalue'] = $v['fieldvalue'];
					}
					$result['int'][$k]['unit'] = $v['unit'];
					$result['int'][$k]['type'] = $v['ct_type'];
					$result['int'][$k]['sort'] = $v['orderid'];
					$result['int'][$k]['required'] = $v['required'];
					$result['int'][$k]['min'] = $v['min'];
					$result['int'][$k]['max'] = $v['max'];
				}

			}
		}

		return $result;
	}

	/**
	 * 根据模板类别ID取得模板数据
	 * @param array $stc_id 模板类别ID
	 * @return array
	 */
	public function get_template($stc_id) {

		$content = array();
		$d_tem = new voa_d_oa_superreport_template();
		$template = $d_tem->get_by_conds(array('stc_id' => $stc_id));
		if ($template) {
			$content = unserialize($template['content']);
		}

		return $content;
	}

}
