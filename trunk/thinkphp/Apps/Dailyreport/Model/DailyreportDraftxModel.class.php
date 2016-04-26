<?php

/**
 * User: keller
 * Date: 16/3/16
 * Time: 下午11:22
 */

namespace Dailyreport\Model;

class DailyreportDraftxModel extends AbstractModel {

    /**
     * 验证并处理基本数据
     */
    const TPL_TYPE_IMG = 'img';
    const TPL_TYPE_TEXT = 'text';
    const TPL_TYPE_TEXTAREA = 'textarea';
    const TPL_TYPE_DATE = 'date';
    const TPL_TYPE_TIME = 'time';
    const TPL_TYPE_DATEANDTIME = 'dateandtime';
    const TPL_TYPE_RADIO = 'radio';
    const TPL_TYPE_CHECKBOX = 'checkbox';
    const TPL_TYPE_NUMBER = 'number';

    private $_tpl_types = array(
        self::TPL_TYPE_IMG,
        self::TPL_TYPE_TEXT,
        self::TPL_TYPE_TEXTAREA,
        self::TPL_TYPE_DATE,
        self::TPL_TYPE_TIME,
        self::TPL_TYPE_DATEANDTIME,
        self::TPL_TYPE_RADIO,
        self::TPL_TYPE_CHECKBOX,
        self::TPL_TYPE_NUMBER
    );
    public $prefield = 'drd_';

    /**
     * 获取日报分页列表
     * @author keller<likai@vchangyi.com>
     * @route
     * @method
     * @param $page
     */
    public function add_draftx($post, $m_uid) {
        //验证数据
        $this->_validation_data($post);
        //保存草稿
        $this->_add_draftx($post, $m_uid);
        return true;
    }

    public function edit_draftx($post) {
        //验证数据
        $this->_validation_data($post);
        //保存草稿
        $this->_edit_draftx($post);
        return true;
    }

    /**
     * 验证数据
     * @param type $post
     */
    private function _validation_data(&$post) {
        //验证drt_id
        if ($post['drt_id'] <= 0) {
            //模板不正确
            E('_ERR_DAILYREPOR_TPL_ERR');
            return false;
        }
        //验证标题
        $post['drd_title'] = trim($post['drd_title'], " \t\r\n");
        $dr_title_len = mb_strlen($post['drd_title'], 'utf8');
        //标题不能为空
        if (!isset($post['drd_title']) || ($dr_title_len <= 0)) {
            E('_ERR_TITLE_NOT_NULL_ERR');
            return false;
        }
        //标题不能太长
        if ($dr_title_len > 25) {
            E('_ERR_TITLE_MAX_ERR');
            return false;
        }
        //验证输入的组件数量
//        $dr_module_len = count($post['drt_module']);
//        if ($dr_module_len > 15 || $dr_module_len <= 0) {
//            //组件数量不正确
//            E('_ERR_DAILYREPORTPL_MODULE_NUMBER_ERR');
//            return false;
//        }
        //验证每一个组件信息
        foreach ($post['drt_module'] as $mk => $mv) {
            if (isset($mv['type']) && in_array($mv['type'], $this->_tpl_types)) {
                //模板类型错误
                //$vfuncname = '_validation_' . $mv['type'];
                //$this->$vfuncname($mv);
                $post['drt_module'][$mk] = $mv;
                continue;
            }
            E('_ERR_DAILYREPORTPL_MODULE_TYPE_ERR');
            break;
        }
        return true;
    }

    private function _validation_date(&$module) {
        return $this->_validation_pub_date($module, '', '');
    }

    private function _validation_time(&$module) {
        return $this->_validation_pub_date($module, '', '');
    }

    private function _validation_dateandtime(&$module) {
        return $this->_validation_pub_date($module, '', '');
    }

    private function _validation_pub_date(&$module, $errcode, $regx) {
        $module['content'] = trim($module['content'], "\t\r\n");
        //1必填
        if ($module['is_null'] == 0 && !$module['content']) {
            return true;
        }
        if ($module['is_null'] == 1 && !$module['content']) {
            E('_ERR_NOT_NULL_ERR');
            return false;
        }
        return true;
    }

    private function _validation_textarea(&$module) {
        $module['content'] = trim($module['content'], " \t\r\n");
        //1必填
        if ($module['is_null'] == 0 && !$module['content']) {
            return true;
        }
        if ($module['is_null'] == 1 && !$module['content']) {
            E('_ERR_NOT_NULL_ERR');
            return false;
        }
        return false;
    }

    private function _validation_text(&$module) {
        $module['content'] = trim($module['content'], " \t\r\n");
        //1必填
        if ($module['is_null'] == 0 && !$module['content']) {
            return true;
        }
        if ($module['is_null'] == 1 && !$module['content']) {
            E('_ERR_NOT_NULL_ERR');
            return false;
        }
        return false;
    }

    private function _validation_number(&$module) {
        $module['content'] = trim($module['content'], " \t\r\n");
        //1必填
        if ($module['is_null'] == 0 && !$module['content']) {
            return true;
        }
        if ($module['is_null'] == 1 && !$module['content']) {
            E('_ERR_NOT_NULL_ERR');
            return false;
        }
        if (!preg_match('/^[0-9.]{1,}$/', $module['content'])) {
            //数字输入不正确
            E('_ERR_INPUT_NUMBER_ERR');
            return true;
        }
        return false;
    }

    private function _validation_img(&$module) {

        //1必填
        $img_len = count($module['content']);
        if ($module['is_null'] == 0 && $img_len == 0) {
            return true;
        }
        if ($module['is_null'] == 1 && $img_len == 0) {
            E('_ERR_NOT_NULL_ERR');
            return false;
        }
        $cache = &\Common\Common\Cache::instance();
        $setting = $cache->get('Common.setting');
        $domain = C('PROTOCAL') . $setting['domain'];
        foreach ($module['content'] as $imgk => $imgv) {
            $imgv['id'] = intval($imgv['id']);
            if ($imgv['id'] <= 0) {
                E('_ERR_DAILYREPORT_MEDIAID_ERR');
                break;
            }
            if (!$imgv['url']) {
                //拼接url
                $imgv['url'] = $domain . '/attachment/read/' . $imgv['id'];
            }
            unset($imgv['$$hashKey']);
            $module['content'][$imgk] = $imgv;
        }
        //保存图片
        return false;
    }

    private function _validation_radio(&$module) {
        $module['content'] = trim($module['content'], " \t\r\n");
        //1必填
        if ($module['is_null'] == 0 && !$module['content']) {
            return true;
        }
        if ($module['is_null'] == 1 && !$module['content']) {
            E('_ERR_NOT_NULL_ERR');
            return false;
        }
        //判断选项的个数
        $c_len = count(explode(',', $module['content']));
        if ($c_len != 1) {
            E('_ERR_DAILYREPORT_RADIO_CHK_ERR');
            return false;
        }
        return true;
    }

    private function _validation_checkbox(&$module) {
        $module['content'] = trim($module['content'], " \t\r\n");
        //1必填
        if ($module['is_null'] == 0 && !$module['content']) {
            return true;
        }
        if ($module['is_null'] == 1 && !$module['content']) {
            E('_ERR_NOT_NULL_ERR');
            return false;
        }
        //判断选项的个数
        $c_len = count(explode(',', $module['content']));
        if ($c_len <= 0) {
            E('_ERR_DAILYREPORT_RADIO_CHK_ERR');
            return false;
        }
        return true;
    }

    private function _add_draftx(&$post, $m_uid) {
        //保存
        $post['drt_module'] = json_encode($post['drt_module'], true);
        $post['m_uid'] = $m_uid;
        if ($this->insert($post)) {
            return true;
        }
        E('_ERR_DRAFTX_ADD_ERR');
        return false;
    }

    private function _edit_draftx(&$post) {
        //保存
        $post['drt_module'] = json_encode($post['drt_module'], true);
        $drd_id = intval($post['drd_id']);
        unset($post['drd_id']);
        if ($this->update($drd_id, $post) !== false) {
            return true;
        }
        E('_ERR_DRAFTX_EDIT_ERR');
        return false;
    }

    /**
     * 处理数据
     * @param type $post
     */
    private function _handle_data(&$post) {
        //上传图片
    }

    public function get_api_draftx($drd_id) {
        if ($drd = $this->get($drd_id)) {
            $drd['drt_module'] = json_decode($drd['drt_module'], true);
            $mem_m = M('member');
            //处理抄送人
            if ($drd['drd_a_uid'] != '') {
                $drd['drd_a_uid'] = $mem_m->field('m_face,m_username,m_uid')->where(array('m_uid' => array('IN', $drd['drd_a_uid'])))->select();
            } else {
                $drd['drd_a_uid'] = array();
            }
            //处理接收人
            if ($drd['drd_cc_uid'] != '') {
                $drd['drd_cc_uid'] = $mem_m->field('m_face,m_username,m_uid')->where(array('m_uid' => array('IN', $drd['drd_cc_uid'])))->select();
            } else {
                $drd['drd_cc_uid'] = array();
            }
            return array('drd' => $drd);
        }
        E('_ERR_NOT_DRAFT_ERR');
        return false;
    }

    public function del_api_draftx($drd_id, $m_uid) {
        $data['drd_status'] = 2;
        $data['drd_deleted'] = NOW_TIME;
        if ($this->_m->where(array('drd_id' => array('IN', $drd_id), 'm_uid' => $m_uid))->save($data) !== false) {
            return true;
        }
        E('_ERR_DEL_DRAFT_ERR');
        return false;
    }

    /**
     * 
     */
    public function get_draftx_list($m_uid, $page, $q, $k, $drt_id) {
        $where = array(
            $this->prefield . 'status' => array('eq', 1),
            'm_uid' => array('eq', $m_uid)
        );
        if ($drt_id) {
            $where['drt_id'] = array('eq', $drt_id);
        }
        switch ($q) {
            case 'd':
                $k = trim($k, " \t\n\r");
                if (preg_match('/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/', $k)) {
                    date_default_timezone_set('PRC');
                    $start_time = strtotime($k);
                    $stop_time = $start_time + 86399;
                    echo date('Y-m-d H:i:s',$stop_time);exit;
                    $where['_string'] = "drd_created>{$start_time} AND drd_created<$stop_time";
                } else {
                    E('_ERR_NOT_MORE_DRAFT_ERR');
                }
                break;
            case 't':
                $k = trim($k, " \t\n\r");
                if ($k != '') {
                    $where['drd_title'] = array('LIKE', "%{$k}%");
                } else {
                    E('_ERR_NOT_MORE_DRAFT_ERR');
                }
                break;
            default :
                break;
        }
        $count = $this->_m->where($where)->count();
        if ($count <= 0) {
            E('_ERR_NOT_MORE_DRAFT_ERR');
        }
        $page_len = 15;
        $page_num = ceil($count / $page_len);
        $page = $page <= 0 ? 1 : ($page > $page_num ? $page_num : $page);
        $field = 'drd_id,drd_title,drd_created';
        $drafts = $this->_m
                ->field($field)
                ->where($where)
                ->page($page, $page_len)
                ->order('drd_created DESC')
                ->select();
        $result = array(
            'page' => $page,
            'pages' => $page_num,
            'limit' => $page_len,
            'count' => $count,
            'list' => $drafts
        );
        return $result;
    }

}
