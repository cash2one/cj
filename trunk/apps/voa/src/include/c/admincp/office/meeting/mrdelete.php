<?php
/**
 * voa_c_admincp_office_meeting_delete
 * 企业后台 - 会议通 -删除/下载二维码
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_meeting_mrdelete extends voa_c_admincp_office_meeting_base {

	public function execute() {

		$delete	=	$this->request->post('delete');
		$mr_id	=	$this->request->get('mr_id');

		$ids	=	0;
		if ( $delete ) {
			$ids	=	rintval($delete, true);
		} elseif ( $mr_id ) {
			$ids	=	rintval($mr_id, false);
		}

		if ( !$ids ) {
			$this->message('error', '请指定目标会议室');
		}
		if(isset($_POST['download'])) {
			//下载二维码
			
			//清空并创建临时目录
			$tmp = ROOT_PATH.'/apps/voa/tmp/qrcode/';
			if(is_dir($tmp)) {
				$files = glob($tmp.'*.*');
				foreach ($files as $f) {
					unlink($f);
				}
				rmdir($tmp);
			}
			ini_set('display_errors', 1);
			mkdir($tmp, 0777);
			
			//初始化压缩包
			include(ROOT_PATH.'/framework/lib/pclzip.php');
			$zipName = ROOT_PATH.'/apps/voa/tmp/qrcode.zip';
			$zip = new pclzip($zipName);
			$uda = new voa_uda_frontend_meeting_base();
			$room = new voa_d_oa_meeting_room();
			foreach($_POST['delete'] as $id)
			{
				$r = $room->fetch_by_id($id);
				$name = iconv('utf-8', 'gbk', $r['mr_address'].'-'.$r['mr_name']);
				$name = $tmp.$name.'.png';
				$uda->qrcode($id, $name);
				$names[] = $name;
			}
			
			if($names) {
				$names = implode(',', $names);
				$v_list = $zip->create($names, PCLZIP_OPT_REMOVE_PATH, $tmp);
				if ($v_list == 0) {
					die("Error : ".$zip->errorInfo(true));
				}
			}
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=".basename($zipName));
            readfile($zipName);
			exit;
		}else{
			//删除
			$this->_service_single('meeting_room', $this->_module_plugin_id, 'delete_by_ids', $ids);
			$this->message('success', '指定会议室删除操作完毕', get_referer($this->cpurl($this->_module, $this->_operation, 'mrlist', $this->_module_plugin_id)), false);
		}
	}

}
