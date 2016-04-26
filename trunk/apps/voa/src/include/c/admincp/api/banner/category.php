<?php
/**
 * 内容详情
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/10/23
 * Time: 15:15
 */
class voa_c_admincp_api_banner_category extends voa_c_admincp_api_banner_base {

	public function execute() {

		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');

		$array_plugins = array('event', 'cnvote', 'community');
		$handpicktype = array();
		foreach ($plugins as $key => $val) {
			if (!in_array($val['cp_identifier'], $array_plugins)) {
				continue;
			}
			if($val['cp_available'] != 4) {
				continue;
			}
			$this->_format_name($val['cp_identifier'],$name);
			$handpicktype[] = $name;
		}

		// 返回结果
		$result = array(
			'list' => $handpicktype,
		);

		return $this->_output_result($result);
	}

	protected function _format_name($in, &$out) {
		$out = array();
		switch ($in) {
			case 'cnvote':
				$out = array('title' =>'投票', 'handpicktype'=> 3) ;
				break;
			case 'event':
				$out = array('title' =>'活动', 'handpicktype'=> 1);
				break;
			case 'community':
				$out = array('title' =>'话题', 'handpicktype'=> 2);
				break;
		}

		return true;
	}
}
