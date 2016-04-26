<?php
/**
 * 值对象基类
 *
 * $Author$
 * $Id$
 */

class vo {

    /**
     * convert_list
     * 批量转换实体对象列表
     *
     * @param string $vo
     * @param array $data
     * @param string $key_field
     * @return array
     */
    public static function convert_list($vo, array $data, $key_field = '') {

        if (!is_array(current($data))) {
            throw new vo_exception('Invalid $data');
        }

        $ret = array();
        foreach ($data as $key => $value) {
            /** 如果包含的数组为空，则略过 */
            if (!$value) {
                continue;
            }

            if ($key_field) {
                $ret[$value[$key_field]] = self::factory($vo, $value);
            } else {
                $ret[$key] = self::factory($vo, $value);
            }
        }

        return $ret;
    }

    /**
     * factory
     * 把数组转成实体
     *
     * @param string $vo 实体名称
     * @param array $data
     * @return vo
     */
    public static function factory($vo, array $data = array()) {

        $obj = new $vo();
        if ($data) {
            foreach ($data as $key => $value) {
                $obj->$key = $value;
            }
        }

        return $obj;
    }

    /**
     * set
     * 给实体属性批量赋值
     *
     * @param vo $obj
     * @param array $data
     * @return vo
     */
    public static function set(vo $obj, array $data) {

        if ($data) {
            foreach ($data as $key => $value) {
                $obj->$key = $value;
            }
        }

        return $obj;
    }

    /**
     * to_array
     * 把实体转换成数组，忽略未赋值的字段
     * 忽略数组和对象，这两种类型也需要单独处理
     *
     * @param vo $obj
     * @return array
     */
    public static function to_array(vo $obj) {

        $vars = get_object_vars($obj);
        $ret = array();
        foreach ($vars as $key => $value) {
            if ($value !== null && !is_object($value) && !is_array($value)) {
                $ret[$key] = $value;
            }
        }

        return $ret;
    }

    /**
     * serialize
     * 序列化实体
     *
     * @param vo $object
     * @throw InvalidArgumentException
     * @throw vo_exception
     * @return string
     */
    public static function serialize($object) {

        if (!$object) {
            throw new InvalidArgumentException('Invalid object');
        }

        if (!($object instanceof vo)) {
            throw new vo_exception('Is not a vo');
        }

        return serialize($object);
    }

    /**
     * unserialize
     * 反序列化实体
     *
     * @param string $string
     * @throw InvalidArgumentException
     * @throw vo_exception
     * @return vo
     */
    public static function unserialize($string) {

        if (!$string) {
            throw new InvalidArgumentException('Invalid string');
        }

        $object = unserialize($string);
        if (!($object instanceof vo)) {
            throw new vo_exception('Is not a vo');
        }

        return $object;
    }

    /**
     * __set
     * 判断如果不是实体定义的属性则报异常
     *
     * @param string $field
     * @param mixed $value
     * @throw vo_exception
     * @return void
     */
    public function __set($field, $value) {

        if (!isset($this->$field)) {
            if ($_SERVER['RUN_MODE'] == 'development') {
                throw new vo_exception('Disallowed field : ' . $field);
            }
        } else {
            $this->$field = $value;
        }
    }

    /**
     * __get
     * 判断如果不是实体定义的属性则报异常
     *
     * @param string $field
     * @throw vo_exception
     * @return mixed
     */
    public function __get($field) {

        if (!isset($this->$field)) {
            if ($_SERVER['RUN_MODE'] == 'development') {
                throw new vo_exception('Disallowed field : ' . $field);
            }
        } else {
            return $this->$field;
        }
    }
}
