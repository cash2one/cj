<?php
/**
 * voa_c_admincp_api_member_fields
 * User: luckwang
 * Date: 15/4/15
 * Time: 下午2:36
 */

class voa_c_admincp_api_member_fields extends voa_c_admincp_api_member_base {

    public function execute() {
        $fields = $this->request->get('fields');

        if ($fields && is_array($fields)) {
            $this->_output_result($this->__save($fields));
        }
        $this->_output_result(array('errcode' => -1, 'errmsg' => '更新失败'));
    }

    private function __save($fields) {

        $settings = voa_h_cache::get_instance()->get('plugin.member.setting', 'oa');

        foreach ($fields as $k=>$field) {
            if (is_numeric($k)) {
                $k = rintval($k);
                $k++;
                if ($k < 1 || $k > 10) {
                    continue;
                }
            }
            if (isset($settings['fields'][$k])) {

                //设置字段排序
                if (isset($field['priority']) &&
                    is_numeric($field['priority'])) {
                    $settings['fields'][$k]['priority'] = rintval($field['priority']);
                }
                //判断是否为系统字段
                if ($settings['fields'][$k]['status'] == 2) {
                    continue;
                } else {
                    //字段描述
                    if (!empty($field['desc'])) {
                        $settings['fields'][$k]['desc'] = $field['desc'];
                    }
                    //字段状态
                    if (!empty($field['status'])) {
                        $settings['fields'][$k]['status'] = 1;
                    } else {
                        $settings['fields'][$k]['status'] = 0;
                    }
                }
            } else {
                $k = rintval($k);
                if ($k < 1 || $k > 10 ||
                        isset($settings['fields'][$k]) ||
                        empty($field['desc'])) {
                    continue;
                }

                $settings['fields'][$k]['desc'] = $field['desc'];
                $settings['fields'][$k]['priority'] = rintval($field['priority']);

                if (!empty($field['status'])) {
                    $settings['fields'][$k]['status'] = 1;
                } else {
                    $settings['fields'][$k]['status'] = 0;
                }

            }
        }
        //更新数据库和缓存
        try {
            $this->__delete($settings, $fields);
            $this->__sort($settings);

            $serv_ms = &service::factory('voa_s_oa_member_setting');
            $serv_ms->update_setting(array('fields' => serialize($settings['fields'])));

            voa_h_cache::get_instance()->get('plugin.member.setting', 'oa', true);

            return array('errcode' => 1, 'errmsg' => '更新成功');

        } catch(Exception $e) {

            return array('errcode' => -1, 'errmsg' => '更新失败');
            logger::error(print_r($e, true));
        }
    }

    /**
     * 删除字段
     * @param $settings
     * @param $fields
     */
    private function __delete(&$settings, $fields) {
        foreach ($settings['fields'] as $k=>$field) {
            if ($field['status'] != 2 && !array_key_exists(($k-1), $fields)) {
                unset($settings['fields'][$k]);
            }
        }

    }

    /**
     * 排序
     * @param $settings
     */
    private function __sort(&$settings) {
        $keys = array_keys($settings['fields']);
        $count = count($keys);
        for ($i = 0; $i < $count; $i++) {//循环比较
            for ($j = $i + 1; $j < $count; $j++) {
                if ($settings['fields'][$keys[$j]]['priority'] < $settings['fields'][$keys[$i]]['priority']) {//执行交换
                    $temp = $keys[$i];
                    $keys[$i] = $keys[$j];
                    $keys[$j] = $temp;
                }
            }
        }
        $fields = array();
        foreach ($keys as $key) {
            $fields[$key] = $settings['fields'][$key];
        }
        $settings['fields'] = $fields;
    }
}
