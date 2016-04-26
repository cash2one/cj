<?php
/**
 * Created by PhpStorm.
 * User: gaoyaqiu
 * Date: 15/11/24
 * Time: 22:05
 * 红包活动相关接口
 */
namespace BlessingRedpack\Controller\Api;
use Common\Common\Cache;

class ActiveController extends AbstractController{

    /**
     * 注册(来源扫描二维码)
     * @return bool
     */
    public function Register_post(){
        // 姓名
        $username = I('post.userName');
        if(empty($username)){
            E('_ERR_BLESSING_REDPACK_USERNAME_IS_EMPTY');
            return true;
        }

        // 手机
        $phone = I('post.phone');
        if(empty($phone)){
            E('_ERR_BLESSING_REDPACK_PHONE_IS_EMPTY');
            return true;
        }

        // 部门
        $depName = I('post.depName');
        if(empty($depName)){
            E('_ERR_BLESSING_REDPACK_DEPNAME_IS_EMPTY');
            return true;
        }

        // 职位
        $position = I('post.position');
        if(empty($position)){
            E('_ERR_BLESSING_REDPACK_POSITION_IS_EMPTY');
            return true;
        }

        // 验证手机号、姓名两个条件是否同时存在，已存在则更新信息，否则新增
        $_serv_redpack_member = D('BlessingRedpack/BlessingRedpackMember', 'Service');
        $_data = array(
            "phone" => $phone,
            "user_name" => $username
        );
        $member = $_serv_redpack_member->get_by_conds($_data);
        if(!empty($member)){
            // 库中已有该人，则更新
            $_data['position'] = $position;
            $_data['department_name'] = $depName;
            $_serv_redpack_member->update($member['id'], $_data);
        }else{
            // 如果手机号和姓名两个条件并存，库中数据不存在的话，在验证手机号是否存在,如果存在，则提示用户手机号已被注册
            $_tmp = array(
                "phone" => $phone
            );
            $member = $_serv_redpack_member->get_by_conds($_tmp);
            if(!empty($member)){
                // 手机号已被注册
                E('_ERR_BLESSING_REDPACK_PHONE_IS_REGISTER');
                return true;
            }else{
                // 插入用户主表member
                $_serv_member = D('Common/Member', 'Service');
                $_insert_data = array(
                    "m_mobilephone" => $phone,
                    "m_username" => $username,
                    "m_qywxstatus" => \Common\Model\MemberModel::QYSTATUS_UNSUBCRIBE,
                    "m_source" => \Common\Model\MemberModel::SOURCE_QRCODE
                );

                $mem_id = $_serv_member->insert($_insert_data);
                if(empty($mem_id)) {
                    E('_ERR_BLESSING_REDPACK__REGISTER_ERROR');
                    return true;
                }

                // 插入活动表
                $_data['position'] = $position;
                $_data['department_name'] = $depName;
                $_data['m_uid'] = $mem_id;
                $redpack_member_id = $_serv_redpack_member->insert($_data);
                if(empty($redpack_member_id)) {
                    E('_ERR_BLESSING_REDPACK__REGISTER_ERROR');
                    return true;
                }
            }
        }

        return true;
    }

}

