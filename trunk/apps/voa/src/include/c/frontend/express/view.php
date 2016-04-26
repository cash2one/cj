<?php

/**
 * 话题详情|用户点赞
 * $Author$
 * $Id$
 */
class voa_c_frontend_express_view extends voa_c_frontend_express_base
{

	public function execute()
	{
		try {

			$act = $this->request->get('act');
			//加载子动作
			if($act)  {
				$this->$act();
				exit;
			}
			
			$eid = rintval($this->request->get('eid'));
			// 读取快递详情
			$uda_express = &uda::factory('voa_uda_frontend_express_view');
			$express = array();
			if (! $uda_express->execute(array(
						'eid' => $eid
					), $express)) {
				$this->_error_message($uda_express->errmsg);
				return true;
			}


		} catch (help_exception $e) {
			$this->_error_message($e->getMessage());
			return true;
		}
		
		// 附件
		$ids = explode(",", $express['at_id']);
		$attach = array();
		foreach ($ids as $_v) {
			if (! empty($_v)) {
				$attach[]['aid'] = $_v;
			}
		}
			
		//快递关联列表(收件人、代领人)
		$list_mem = array();
		$uda_list_mem = &uda::factory('voa_uda_frontend_express_mem_list');
		$uda_list_mem->execute(array('eid'=>$express['eid']),$list_mem);
			
		//整理数据
		foreach ($list_mem as $k => $v) {
			if ($v['flag'] == voa_d_oa_express_mem::COLLECTION) {//设置代领人姓名
				$express['c_username'] = $v['username'];
				continue;
			}elseif ($v['flag'] == voa_d_oa_express_mem::RECEIVE) {
				$express['r_username'] = $v['username'];
				continue;
			}
		}
		
		$this->view->set('attachs', $attach); // 附件
		$this->view->set('express', $express);
		//已领取快递模板
		if ($express['flag'] == voa_d_oa_express::GET_YES) {
			$this->_output('mobile/express/getview');
		}else {
			$this->_output('mobile/express/view');//未领取快递模板
		}
	}
	
	//获取二维码,并将文字写入图片中
	private function qrcode()
	{
		$eid = $this->request->get('eid');
		//获取二维码,并将文字写入图片中
		$uda_code = new voa_uda_frontend_express_abstract();
		$uda_code->qrcode($eid, '', 0);
	}
	
	
}

