<?php

/**
 * CommentModel.class.php
 * $author$
 */

namespace Dailyreport\Model;

class DailyreportTplModel extends AbstractModel {

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
    public $prefield = 'drt_';

    private function _deal_edit_add_post(&$post) {
        //验证模板名称
        if (!$post['drt_name']) {
            E('_ERR_DAILYREPORTPL_NAME_ERR');
            return false;
        }
        //判断序号
        if (!$post['drt_sort']) {
            E('_ERR_DAILYREPORTPL_SORT_ERR');
            return false;
        }
        //效验组件数量
        if (count($post['drt_module']) <= 0 || count($post['drt_module']) > 15) {
            E('_ERR_DAILYREPORTPL_MODULE_NUMBER_ERR'); //抛出组件数量不符合规范
            return false;
        }
        if (isset($post['drt_id'])) {
            $post['drt_id'] = intval($post['drt_id']);
            if ($post['drt_id'] <= 0) {
                E('_ERR_DAILYREPORTPL_DRT_ID_ERR'); //抛出组件数量不符合规范
                return false;
            }
        }
        //处理禁用启用的值
        $post['drt_switch'] = $post['drt_switch'] ? 1 : 0;
        $post['drt_sort'] = intval($post['drt_sort']);
        return true;
    }

    /**
     * 处理每一个组件
     */
    private function _handle_module_edit_add_post(&$post) {
        foreach ($post['drt_module'] as $k => $v) {
            //验证组件类型
            if (!isset($v['type']) || !in_array($v['type'], $this->_tpl_types)) {
                E('_ERR_DAILYREPORTPL_MODULE_TYPE_ERR');
                break;
            }
            $title_len = mb_strlen($v['title'], 'utf8');
            $_type = $v['type'];
            if ($_type == DailyreportTplModel::TPL_TYPE_DATE || $_type == DailyreportTplModel::TPL_TYPE_TIME || $_type == DailyreportTplModel::TPL_TYPE_DATEANDTIME || $_type == DailyreportTplModel::TPL_TYPE_NUMBER) {
                if ($title_len > 6 || $title_len <= 0) {
                    E('_ERR_DAILYREPORTPL_MODULE_TITLE_ERR');
                    break;
                }
            } else {
                if ($title_len > 10 || $title_len <= 0) {
                    E('_ERR_DAILYREPORTPL_MODULE_TITLE_ERR');
                    break;
                }
            }

            $func_name = '_handle_module_' . $v['type'];
            if (!isset($v['name'])) {
                $v['name'] = $v['type'] . '_' . $k;
            }
            $v['is_null'] = $v['is_null'] == '1' ? 1 : 0;
            $post['drt_module'][$k] = $this->$func_name($v);
        }
        return true;
    }

    /**
     * 处理图片组件
     */
    private function _handle_module_img($module) {
        $max = intval($module['value'][0]['max']);
        if (count($module['value']) > 1) {
            foreach ($module['value'] as $k => $v) {
                unset($module['value'][$k]);
            }
        }
        if ($max > 9) {
            $max = 9;
        } elseif ($max <= 0) {
            $max = 1;
        }
        unset($module['value']);
        $module['value'][0]['max'] = $max;
        return $module;
    }

    private function _handle_module_textarea($module) {
        $module['value'] = array();
        return $module;
    }

    private function _handle_module_text($module) {
        $module['value'] = array();
        return $module;
    }

    private function _handle_module_number($module) {
        $module['value'] = array();
        return $module;
    }

    private function _handle_module_date($module) {
        $module['value'] = array();
        return $module;
    }

    private function _handle_module_time($module) {
        $module['value'] = array();
        return $module;
    }

    private function _handle_module_dateandtime($module) {
        $module['value'] = array();
        return $module;
    }

    private function _handle_module_radio($module) {
        $name_arr = array();
        foreach ($module['value'] as $k => $v) {
            $str_len = mb_strlen($v['name'], 'utf8');
            if (!isset($v['name']) || ($str_len > 15 || $str_len <= 0) || in_array($v['name'], $name_arr)) {
                E('_ERR_DAILYREPORTPL_MODULE_RADIO_VALUE_ERR');
                break;
            }
            $module['value'][$k]['value'] = $module['name'] . '_' . $k;
            $name_arr[] = $v['name'];
        }
        return $module;
    }

    private function _handle_module_checkbox($module) {
        $name_arr = array();
        foreach ($module['value'] as $k => $v) {
            $str_len = mb_strlen($v['name'], 'utf8');
            if (!isset($v['name']) || ($str_len > 15 || $str_len <= 0) || in_array($v['name'], $name_arr)) {
                E('_ERR_DAILYREPORTPL_MODULE_RADIO_VALUE_ERR');
                break;
            }
            $module['value'][$k]['value'] = $module['name'] . '_' . $k;
            $name_arr[] = $v['name'];
        }
        return $module;
    }

    /**
     * 处理tpl所对应的部门
     * @param type $deparments
     */
    private function _handle_department($deparments, $tpl_id) {
        //检出所有的父id
        $p_ids = array();
        foreach ($deparments as $dpv) {
            if ($dpv['parent_id'] > 1) {
                if (!in_array($dpv['parent_id'], $p_ids)) {
                    $p_ids[] = $dpv['parent_id'];
                }
            }
        }
        //开始对所有父级 的子 进行计数 并判断是否需要递归查询
        foreach ($deparments as $dpk => $dpv) {
            if (in_array($dpv['dp_id'], $p_ids)) {
                $deparments[$dpk]['is_query'] = 0;
            } else {
                $deparments[$dpk]['is_query'] = 1;
            }
        }
        //开始查询所有部门
        $drt_dp_all = array();
        //获取部门的缓存
        $cache = &\Common\Common\Cache::instance();
        $departments_cache = $cache->get('Common.department');
        $_all_p2c = array();
        $this->_all_p2c($departments_cache, $_all_p2c);
        unset($departments_cache);
        foreach ($deparments as $dpv) {
            $dpv['drt_id'] = $tpl_id;
            if ($dpv['is_query']) {
                //查出该部门下所有的部门
                $childs = $this->_get_dp_child($dpv['dp_id'], $_all_p2c);
                foreach ($childs as $ck => $cv) {
                    $childs[$ck] = array(
                        'dp_id' => $cv,
                        'dp_is_show' => 0,
                        'drt_id' => $tpl_id
                    );
                }
                unset($dpv['parent_id'], $dpv['dp_name'], $dpv['is_query']);
                $drt_dp_all[] = $dpv;
                $drt_dp_all = array_merge($drt_dp_all, $childs);
            } else {
                unset($dpv['parent_id'], $dpv['dp_name'], $dpv['is_query']);
                $drt_dp_all[] = $dpv;
            }
        }
        $drt_dp_model = M('DailyreportTplDepartment');
        $drt_dp_model->where(array('drt_id' => array('eq', intval($tpl_id))))->delete();
        if ($drt_dp_all) {
            if ($drt_dp_model->addAll($drt_dp_all)) {
                return true;
            }
            E('_ERR_DAILYREPORTPL_MODULE_DEPARMENTS_ADD_ERR');
            return false;
        }
        return true;
    }

    //处理成树状结构
    private function _all_p2c(&$departments, &$_all_p2c) {
        foreach ($departments as $_dp) {
            //获取树状接口第一层
            if (empty($_all_p2c[$_dp['cd_upid']])) {
                $_all_p2c[$_dp['cd_upid']] = array();
            }
            $_all_p2c[$_dp['cd_upid']][$_dp['cd_id']] = $_dp['cd_id'];
        }
    }

    private function get_child_cd_id($cd_id, &$_all_p2c, $self = true) {
        $rets = array();
        if ($self) {
            $rets[] = $cd_id;
        }
        if (!empty($_all_p2c[$cd_id])) {
            foreach ($_all_p2c[$cd_id] as $_id) {
                $rets = array_merge($rets, $this->get_child_cd_id($_id, $_all_p2c));
            }
        }
        return $rets;
    }

    /**
     * 递归获取部门所有的子部门
     * @param type $cd_id
     * @param type $tpl_id
     */
    private function _get_dp_child($cd_id, &$_all_p2c) {
        return $this->get_child_cd_id($cd_id, $_all_p2c, false);
    }

    public function add_tpl($post) {
        $this->_deal_edit_add_post($post); //处理必填值
        $this->_handle_module_edit_add_post($post); //处理组件
        $this->_is_module_by_name($post['drt_name']); //通过组件名称判断是否存在
        $this->_save_tpl($post);
    }

    public function edit_tpl($post) {
        $this->_deal_edit_add_post($post); //处理必填值
        $this->_handle_module_edit_add_post($post); //处理组件
        $this->_is_edit_module_by_name($post['drt_name'], $post['drt_id']); //通过组件名称判断是否存在
        $this->_edit_tpl($post);
    }

    private function _edit_tpl($post) {
        $post['drt_module'] = json_encode($post['drt_module']);
        $deparments = $post['drt_departments'];
        if (!is_array($deparments)) {
            $deparments = array();
        }
        $post['drt_departments'] = json_encode($deparments);
        $this->start_trans();
        if ($this->update($post['drt_id'], $post) !== FALSE) {
            if ($this->_handle_department($deparments, $post['drt_id'])) {
                $this->commit();
                return true;
            }
            $this->rollback();
            E('_ERR_DAILYREPORTPL_MODULE_DEPARMENTS_ADD_ERR');
            return false;
        }
        $this->rollback();
        E('_ERR_DAILYREPORTPL_MODULE_ADD_ERR');
        return false;
    }

    private function _is_edit_module_by_name($tpl_name, $tpl_id) {
        if ($this->_m->field('drt_id')->where(array('drt_name' => array('eq', $tpl_name), 'drt_id' => array('neq', $tpl_id)))->find()) {
            E('_ERR_DAILYREPORTPL_MODULE_EXIST_ERR');
            return false;
        }
        return true;
    }

    private function _is_module_by_name($tpl_name) {
        if ($this->_m->field('drt_id')->where(array('drt_name' => array('eq', $tpl_name), 'drt_status' => array('NEQ', 3)))->find()) {
            E('_ERR_DAILYREPORTPL_MODULE_EXIST_ERR');
            return false;
        }
        return true;
    }

    /**
     * 保存模板
     * @param array $post 模板数据
     * @return boolean 保存状态
     */
    private function _save_tpl($post) {
        $post['drt_module'] = json_encode($post['drt_module']);
        //处理部门
        $deparments = $post['drt_departments'];
        if (!is_array($deparments)) {
            $deparments = array();
        }
        //判断序号
        if (!$post['drt_sort']) {
            E('_ERR_DAILYREPORTPL_SORT_ERR');
            return false;
        }
        $post['drt_departments'] = json_encode($deparments);
        $this->start_trans();
        if ($tpl_id = $this->insert($post)) {
            if ($this->_handle_department($deparments, $tpl_id)) {
                $this->commit();
                return true;
            }
            $this->rollback();
            E('_ERR_DAILYREPORTPL_MODULE_DEPARMENTS_ADD_ERR');
            return false;
        }
        $this->rollback();
        E('_ERR_DAILYREPORTPL_MODULE_ADD_ERR');
        return false;
    }

    /**
     * 获取模板分页列表
     * @param type $page
     * @return array 模板列表及分页数据
     */
    public function get_list($page) {
        $page = intval($page);
        $count = $this->_m->where(array('drt_id' => array('neq', 6), 'drt_status' => array('neq', 3)))->count();
        $page_len = 15;
        $page_num = ceil($count / $page_len);
        $page = $page <= 0 ? 1 : ($page > $page_num ? $page_num : $page);
        $tpls = $this->_m
                ->field('drt_id,drt_name,drt_departments,drt_switch,drt_sort')
                ->where(array($this->prefield . 'status' => array('neq', 3), 'drt_id' => array('neq', 6)))
                ->page($page, $page_len)
                ->order('drt_sort ASC')
                ->select();
//        //遍历查出所对应的部门
        $tpl_dp_m = M('DailyreportTplDepartment');
        $where['a.dp_is_show'] = array('eq', 1);
        foreach ($tpls as $tplk => $tplv) {
            if ($tplv['drt_departments'] == '[]') {
                $tpls[$tplk]['drt_departments'] = array();
            } else {
                $where['a.drt_id'] = array('eq', $tplv['drt_id']);
                $tpls[$tplk]['drt_departments'] = $tpl_dp_m->alias('a')
                        ->field('a.drt_id,a.dp_id,a.dp_is_show,b.cd_name')
                        ->where($where)
                        ->join('oa_common_department as b ON a.dp_id=b.cd_id')
                        ->select();
            }
        }
        $result = array(
            'page' => $page,
            'pages' => $page_num,
            'limit' => $page_len,
            'count' => $count,
            'list' => $tpls
        );
        return $result;
    }

    public function switch_tpl($drt_id, $drt_switch) {
        if ((intval($drt_id) <= 0)) {
            E('_ERR_DAILYREPORTPL_MODIFY_SWITCH_ERR');
            return false;
        }
        if ($this->_m->where(array('drt_id' => array('eq', intval($drt_id))))->save(array('drt_switch' => intval($drt_switch))) !== false) {
            return true;
        }
        E('_ERR_DAILYREPORTPL_MODIFY_SWITCH_ERR');
        return false;
    }

    public function del_tpl($drt_id) {
        if ($drt_id <= 0) {
            E('_ERR_DAILYREPORTPL_DEL_ERR');
            return false;
        }
        //更改此模板下所有的报告 dr_type 
        $this->start_trans();
        $dp_m = M('Dailyreport');
        if ($dp_m->where(array('dr_type' => array('eq', $drt_id)))->save(array('dr_type' => 6)) !== FALSE) {
            //更新此模板下所有的草稿
            $drfx_m = M('DailyreportDraftx');
            $twhere = "drt_id ={$drt_id} AND drd_status<>2";
            if ($drfx_m->where($twhere)->save(array('drt_id' => 6))!==false) {
                if ($this->delete($drt_id) !== false) {
                    $this->commit();
                    return true;
                }
            }
        }
        $this->rollback();
        E('_ERR_DAILYREPORTPL_DEL_ERR');
        return false;
    }

    public function get_tpl($drt_id) {
        if ((intval($drt_id) <= 0)) {
            E('_ERR_DAILYREPORTPL_GET_ERR');
            return false;
        }
        if ($tpl = $this->get(intval($drt_id))) {
            //查询出模板对应的部门
            $drt_dp_m = M('DailyreportTplDepartment');
            $drps = $drt_dp_m->alias('drp')
                    ->field('drp.dp_id,dp_is_show,ocdp.cd_name as dp_name,ocdp.cd_upid as parent_id')
                    ->join('oa_common_department AS ocdp ON drp.dp_id=ocdp.cd_id')
                    ->where(array('drp.drt_id' => array('eq', $drt_id)))
                    ->select();
            $tpl['drt_departments'] = $drps;
            $tpl['drt_module'] = json_decode($tpl['drt_module'], true);
            //dump($tpl);exit;
            return array('tpl' => $tpl);
        }
        E('_ERR_DAILYREPORTPL_GET_ERR');
        return false;
    }

    public function get_type() {
        $param = array();
        $param["drt_status"] = array('NEQ', 3);
        $param["drt_switch"] = array('EQ', 1);
        $types = $this->_m
                ->field("drt_id,drt_name")
                ->where($param)
                ->select();
        return $types;
    }

    public function get_typecp() {
        $param = array();
        $param["drt_status"] = array('NEQ', 3);
        //过滤 其它
        $dr_m = M('Dailyreport');
        if ($dr_m->where(array('dr_type' => array('eq', 6), 'dr_status' => array('NEQ', 3)))->count() <= 0) {
            $param["drt_id"] = array('NEQ', 6);
        }
        $types = $this->_m
                ->field("drt_id,drt_name")
                ->where($param)
                ->order('drt_sort')
                ->select();
        return $types;
    }

    public function get_api_tpl_list($m_uid, $type_id, $target_id) {
        //过滤其它
        //我发起的 type=1
        //我负责的 type=2
        //与我相关的 type=3
        //查看往期type=4
        //查看草稿type=5
        $dr_m = M('Dailyreport');
        switch ($type_id) {
            case 1:
                $twhere = "dr_type=6 AND dr_status<>3 AND((m_uid={$m_uid} AND dr_forword_uid=0) OR (dr_forword_uid={$m_uid}))";
                if ($dr_m->where($twhere)->count() <= 0) {
                    $where["drt_id"] = array('NEQ', 6);
                }
                break;
            case 2:
            case 3:
                $get_evel = $type_id == 2 ? 1 : 0;
                $twhere = array(
                    'a.dr_type' => array('eq', 6),
                    'a.dr_status' => array('neq', 3),
                    'b.m_uid' => array('eq', $m_uid),
                    'b.get_level' => array('eq', $get_evel)
                );
                if ($dr_m->alias('a')->join('oa_dailyreport_mem AS b ON a.dr_id=b.dr_id')->where($twhere)->count() <= 0) {
                    $where["drt_id"] = array('NEQ', 6);
                }
                break;
            case 4:
                $twhere = "a.dr_type=6 AND a.dr_status<>3 AND b.m_uid={$m_uid} AND((a.m_uid={$target_id} AND a.dr_forword_uid=0) OR a.dr_forword_uid={$target_id})";
                $join = 'oa_dailyreport_mem AS b ON a.dr_id=b.dr_id';
                if ($dr_m->alias('a')->join($join)->where($twhere)->count() <= 0) {
                    $where["drt_id"] = array('NEQ', 6);
                }
                break;
            case 5:
                //查询草稿是否包含其它
                $drfx_m = M('DailyreportDraftx');
                $twhere = array(
                    'drt_id' => array('eq', 6),
                    'drd_status' => array('neq',2),
                    'm_uid' => array('eq', $m_uid)
                );
                if ($drfx_m->where($twhere)->count() <= 0) {
                    $where["drt_id"] = array('NEQ', 6);
                }
                break;
            default:
                break;
        }
        //查出该用户所对应的部门
        $oMemDp_m = M('MemberDepartment');
        if ($cd_ids = $oMemDp_m->field('cd_id')->where(array('m_uid' => array('eq', $m_uid), 'md_status' => array('NEQ', 3)))->select()) {
            $cd_ids = implode(',', array_map(function ($v) {
                        return $v['cd_id'];
                    }, $cd_ids));
            //查询出当前用户所可用的模板
            $oTplDpM = M('dailyreport_tpl_department');
            $where['drt_status'] = array('NEQ', 3);
            if ($drt_ids = $oTplDpM->field('drt_id')->where(array('dp_id' => array('IN', $cd_ids)))->select()) {
                $dp_ids = implode(',', array_map(function ($v) {
                            return intval($v['drt_id']);
                        }, $drt_ids));
                $oRwhere['drt_departments'] = array('eq', '[]');
                $oRwhere['drt_id'] = array('IN', $dp_ids);
                $oRwhere['_logic'] = 'OR';
                $where['_complex'] = $oRwhere;
            } else {
                $where['drt_departments'] = array('eq', '[]');
            }
        } else {
            $where['drt_departments'] = array('eq', '[]');
        }

        if ($tpls = $this->_m->field('drt_id,drt_name')->where($where)->order('drt_sort')->select()) {
            $rel['list'] = $tpls;
            return $rel;
        }
        return false;
    }

    public function get_api_tpl_info($drt_id, $m_username = 'test') {
        if ($tpl = $this->_m->field('drt_id,drt_name,drt_module')->where(array('drt_id' => array('eq', $drt_id)))->find()) {
            date_default_timezone_set('PRC');
            $tpl['dr_title'] = $m_username . " " . date("Y-m-d") . " " . $tpl['drt_name'];
            $tpl['drt_module'] = json_decode($tpl['drt_module'], true);
            $rel['info'] = $tpl;
            return $rel;
        }
        return false;
    }

    public function get_api_in_departments($drt_id, $cd_id) {
        //查出部门id
        $oTplDpM = M('dailyreport_tpl_department');
        $where = array('cd_status' => array('neq', 3));
        //如果等于0则查出需要显示的
        $aWhere['drt_id'] = array('eq', $drt_id);
        if ($cd_id > 0) {
            $where['cd_upid'] = array('eq', $cd_id);
            //判断当前的部门是否有权限查看
            //判断是否有私有权限
            if ($oTplDpM->where(array('drt_id' => array('eq', $drt_id)))->count() > 0) {
                $dp_id_count = $oTplDpM->where(array('drt_id' => array('eq', $drt_id), 'dp_id' => array('eq', $cd_id)))->count();
                if ($dp_id_count <= 0) {
                    return array('departments' => array());
                }
            }

            $aWhere['dp_is_show'] = array('eq', 0);
        } else {
            $aWhere['dp_is_show'] = array('eq', 1);
        }
        $oDpM = M('common_department');
        if ($dp_ids = $oTplDpM->field('dp_id')->where($aWhere)->select()) {
            $dp_ids = array_map(function ($v) {
                return (int) $v['dp_id'];
            }, $dp_ids);
            $where['cd_id'] = array('IN', implode(',', $dp_ids));
        } else {
            if (!isset($where['cd_upid'])) {
                //获取顶级分类的cd_id
                $parent=$oDpM->where(array('cd_upid'=>array('eq',0),'cd_status'=>array('NEQ',3)))->find();
                $where['cd_upid'] = array('eq', (int)$parent['cd_id']);
            }
        }
        /**
         * 没有设置部门限制则查出第一层的部门
         */
        $dps = $oDpM->field('cd_id as id,cd_name as name,cd_usernum as count')->where($where)->select();
        $rel['departments'] = $dps;
        return $rel;
    }

    public function get_tpl_sort() {
        $count = $this->_m->where('drt_status<>3')->max('drt_sort');
        return array('sort' => $count + 1);
    }

    public function get_api_news_dailyreport_tpls($m_uid) {
        //查出该用户所对应的部门
        $oMemDp_m = M('MemberDepartment');
        if ($cd_ids = $oMemDp_m->field('cd_id')->where(array('m_uid' => array('eq', $m_uid), 'md_status' => array('neq', 3)))->select()) {
            $cd_ids = implode(',', array_map(function ($v) {
                        return $v['cd_id'];
                    }, $cd_ids));
            //查询出当前用户所可用的模板
            $oTplDpM = M('dailyreport_tpl_department');
            $where['drt_status'] = array('NEQ', 3);
            if ($drt_ids = $oTplDpM->field('drt_id')->where(array('dp_id' => array('IN', $cd_ids)))->select()) {
                $dp_ids = implode(',', array_map(function ($v) {
                            return intval($v['drt_id']);
                        }, $drt_ids));
                $oRwhere['drt_departments'] = array('eq', '[]');
                $oRwhere['drt_id'] = array('IN', $dp_ids);
                $oRwhere['_logic'] = 'OR';
                $where['_complex'] = $oRwhere;
                $where['drt_id'] = array('NEQ', 6);
            } else {
                $where['drt_departments'] = array('eq', '[]');
                $where['drt_id'] = array('NEQ', 6);
            }
            $where['drt_switch'] = array('eq', 1);
            if ($tpls = $this->_m->field('drt_id,drt_name')->where($where)->order('drt_sort')->select()) {
                $rel['list'] = $tpls;
                return $rel;
            }
            return false;
        } else {
            //抛出用户没有对应部门异常
            E('_ERR_NOT_DP_USER_ERR');
            return false;
        }
    }

}
