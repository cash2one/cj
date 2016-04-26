<?php
/**
 * 时间点标记
 * $Author$
 * $Id$
 */

class benchmark {

	public $maker = array();

	protected static $_instance = null;

	/**
	 * mark
	 * 设置一个 benchmark
	 *
	 * @param  mixed $mark
	 * @return void
	 */
	public static function mark($mark) {
		$banchmark = self::get_instance();

		$banchmark->maker[$mark] = microtime(true);
	}

	/**
	 * elapsed_time
	 * 计算两个mark之间的时间
	 *
	 * @param  string $mark1
	 * @param  string $mark2
	 * @param  integer $decimals
	 * @return void
	 */
	public static function elapsed_time($mark1 = '', $mark2 = '', $decimals = 4) {
		$banchmark = self::get_instance();

		if (!$banchmark->maker[$mark1]) {
			return '';
		}

		$end = microtime(true);
		if ($mark2) {
			if (!$banchmark->maker[$mark2]) {
				$banchmark->maker[$mark2] = microtime(true);
			}

			$end = $banchmark->maker[$mark2];
		}

		return number_format($end - $banchmark->maker[$mark1], $decimals);
	}

	/**
	 * get_instance
	 *
	 * @return void
	 */
	public static function get_instance() {

		if (!self::$_instance) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

}
