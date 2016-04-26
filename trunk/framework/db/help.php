<?php
/**
 * 数据库辅助操作类
 * $Author$
 * $Id$
 */

class db_help {

	/**
	 * 控制数据读取个数
	 * @param int $start 起始位置
	 * @param number $limit 读取的数目
	 * @param int $max 指定最大读取值
	 * @return string
	 */
	public static function limit($start, $limit = 0, $max = 30000) {
		$start = intval($start > 0 ? $start : 0);
		$limit = intval($limit > 0 ? $limit : 0);
		if ($limit > $max) {
			$limit = $max;
		}

		if ($start > 0 && $limit > 0) {
			return " LIMIT $start, $limit";
		} elseif ($limit) {
			return " LIMIT $limit";
		} elseif ($start) {
			return " LIMIT $start";
		} else {
			return '';
		}
	}

	public static function order($field, $order = 'ASC') {
		if (empty($field)) {
			return '';
		}

		$order = strtoupper($order) == 'ASC' || empty($order) ? 'ASC' : 'DESC';
		return self::quote_field($field).' '.$order;
	}

	/**
	 * 构造多字段排序SQL语句
	 * @param array $fields 字段数组，以字段名为键名，以排序类型ASC|DESC为键值
	 * @return string SQL: ORDER BY ...
	 */
	public static function orders($fields) {
		if (!empty($fields)) {
			$sql = array();
			foreach ($fields as $field_name => $order_sort) {
				$sql[] = ' '.self::quote_field($field_name).' '.(rstrtoupper($order_sort) == 'DESC' ? 'DESC' : 'ASC');
			}
			return $sql ? ' ORDER BY'.implode(',', $sql) : '';
		} else {
			return '';
		}
	}

	public static function implode($array, $glue = ',') {
		$sql = $comma = '';
		$glue = ' '.trim($glue).' ';
		foreach ($array as $k => $v) {
			$sql .= $comma.self::quote_field($k).'='.self::quote($v);
			$comma = $glue;
		}

		return $sql;
	}

	public static function implode_field_value($array, $glue = ',') {
		return self::implode($array, $glue);
	}

	public static function quote_field($field) {
		if (is_array($field)) {
			foreach ($field as $k => $v) {
				$field[$k] = self::quote_field($v);
			}
		} else {
			if (strpos($field, '`') !== false) {
				$field = str_replace('`', '', $field);
			}

			$field = '`'.$field.'`';
		}

		return $field;
	}

	public static function field($field, $val, $glue = '=') {
		$field = self::quote_field($field);
		if (is_array($val)) {
			$glue = $glue == 'notin' ? 'notin' : 'in';
		} elseif ($glue == 'in') {
			$glue = '=';
		}

		switch ($glue) {
			case '=':
				return $field.$glue.self::quote($val);
				break;
			case '-':
			case '+':
				return $field.'='.$field.$glue.self::quote((string) $val);
				break;
			case '|':
			case '&':
			case '^':
				return $field.'='.$field.$glue.self::quote($val);
				break;
			case '>':
			case '<':
			case '<>':
			case '<=':
			case '>=':
				return $field.$glue.self::quote($val);
				break;
			case 'like':
				return $field.' LIKE('.self::quote($val).')';
				break;
			case 'in':
			case 'notin':
				$val = $val ? implode(',', self::quote($val)) : '\'\'';
				return $field.($glue == 'notin' ? ' NOT' : '').' IN('.$val.')';
				break;
			default:
				throw new db_exception('Not allow this glue between field and value: "'.$glue.'"');
		}
	}

	public static function quote($str, $noarray = false) {
		if (is_string($str)) {
			return '\''.addcslashes($str, "\n\r\\'\"\032").'\'';
		}

		if (is_int($str) or is_float($str)) {
			return '\''.$str.'\'';
		}

		if (is_array($str)) {
			if ($noarray === false) {
				foreach ($str as &$v) {
					$v = self::quote($v, true);
				}

				return $str;
			} else {
				return '\'\'';
			}
		}

		if (is_bool($str)) {
			return $str ? '1' : '0';
		}

		return '\'\'';
	}
}