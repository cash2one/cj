<?php
/**
 * 红包活动->注册
 * $Author$
 * $Id$
 */
class voa_c_api_blessingredpack_post_reg extends voa_c_api_blessingredpack_base {

	protected function _before_action($action) {

		$this->_require_login = false;
		return parent::_before_action($action);
	}

	public function execute() {

        $post = $this->request->postx();

        $submit['redpack_id'] = isset($post['id']) ? trim($post['id']) : '';
        $submit['department_name'] = isset($post['depName']) ? trim($post['depName']) : '';
        $submit['m_mobilephone'] = isset($post['phone']) ? trim($post['phone']) : '';
        $submit['position'] = isset($post['position']) ? trim($post['position']) : '';
        $submit['m_username'] = isset($post['userName']) ? trim($post['userName']) : '';

        // 活动id
        if(empty($submit['redpack_id'])){
            $this->_set_errcode("4300100:参数id不能为空");
            return false;
        }

        // 姓名
        if(empty($submit['m_username'])){
            $this->_set_errcode("4300302:姓名不能为空");
            return false;
        }

        // 手机
        if(empty($submit['m_mobilephone'])){
            $this->_set_errcode("4300303:手机不能为空");
            return false;
        }

        // 部门
        if(empty($submit['department_name'])){
            $this->_set_errcode("4300304:部门不能为空");
            return false;
        }

        // 职位
        if(empty($submit['position'])){
            $this->_set_errcode("4300305:职位不能为空");
            return false;
        }

        // 检测系统是否已设定通过自由红包扫码进入的邀请部门
        $_blessing_setting_ser = voa_h_cache::get_instance()->get('plugin.blessingredpack.setting', 'oa');
        $invite_department = $_blessing_setting_ser['invite_department'];
        if(empty($invite_department)){
            $this->_set_errcode("1000015:请先设置活动邀请部门");
            return false;
        }

        // 当前活动验证手机号、姓名两个条件是否同时存在，已存在则更新信息，否则新增
        $_serv_redpack_member = service::factory('voa_s_oa_blessingredpack_blessingmember');
        $_data = array(
            "redpack_id" => $submit['redpack_id'],
            "m_mobilephone" => $submit['m_mobilephone'],
            "m_username" => $submit['m_username']
        );

        $member = $_serv_redpack_member->get_by_conds($_data);
        if(!empty($member)){
            // 之前已注册，库中已有该人，则更新
            $_serv_redpack_member->update($member['id'], $submit);
        }else{
            // 如果手机号和姓名两个条件并存，库中数据不存在的话，在验证手机号是否存在,如果存在，则提示用户手机号已被注册
            $_tmp = array(
                "redpack_id" => $submit['redpack_id'],
                "m_mobilephone" => $submit['m_mobilephone']
            );
            $member = $_serv_redpack_member->get_by_conds($_tmp);
            if(!empty($member)){
                // 手机号已被注册
                $this->_set_errcode("4300306:手机号已被注册");
                return false;
            }else{
                // 插入用户基础表数据
                $uda_member_update = &uda::factory('voa_uda_frontend_member_update');
                $member = array();
                try{
                    $submit['m_qywxstatus'] = 4;
                    $submit['m_source'] = 1;
                    $submit['cd_id'] = array($invite_department); // 默认部门

                    $uda_member_update->update($submit, $member, array());
                    // 更新部门人数
                    $uda_member_update->update_department_usernum();
                } catch (Exception $e) {
                    logger::error($e);
                    $this->_set_errcode("4300307:注册失败");
                    return false;
                }

                $errcode = $uda_member_update->errcode;
                $errmsg = $uda_member_update->errmsg;
                if($errcode != 0){
                    $this->_set_errcode($errcode.":".$errmsg);
                    return false;
                }

                // 插入活动表
                $submit['m_uid'] = $member['m_uid'];
                $submit['is_new'] =  0; // 是新用户
                unset($submit['m_qywxstatus']);
                unset($submit['m_source']);
                unset($submit['cd_id']);
                $redpack_member_id = $_serv_redpack_member->insert($submit);
                if(empty($redpack_member_id)) {
                    $this->_set_errcode("4300307:注册失败");
                    return false;
                }
            }
        }

		return true;
	}
}
