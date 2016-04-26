<?php
/**
 * service_exception
 *
 * $Author$
 * $Id$
 */

class service_exception extends Exception {

	public function __construct($message, $code = 0) {
		if ($message instanceof Exception) {
			parent::__construct($message->getMessage(), intval($message->getCode()));
		} else {
			parent::__construct($message, intval($code));
		}
		$traces = $this->getTrace();
		$trace = $traces[0];
		if ($traces[1]) {
			$trace = $traces[1];
		}
		$this->message = sprintf("%s%s%s : %s", $trace['class'], $trace['type'], $trace['function'], $this->message);
	}

}
