<?php
/**
 * voa_c_api_talk_get_viewerlist
 * 查看客户信息列表
 * $Author$
 * $Id$
 */

class voa_c_api_talk_get_viewerlist extends voa_c_api_talk_abstract {

	public function execute() {

		$viewerlist = array();
		$uda = new voa_uda_frontend_talk_listlastviewer();
		if (!$uda->execute($this->_params, $viewerlist)) {
			return true;
		}
		
		function build_sorter($key) {
		    return function ($a, $b) use ($key) {
		        return $a[$key] < $b[$key];
		    };
		}
		
		usort($viewerlist, build_sorter('updated'));
		
		//将未读的记录排到前面
		$result = $result2 = array();
		foreach ($viewerlist as $r) {
			if($r['newct']) {
				$result[] = $r;
			}else{
				$result2[] = $r;
			}
		}

		$result = array_merge($result, $result2);
				
		$this->_result = $result;
		return true;
	}

}

