<?php
/**
 * http_request_exception
 * $Author$
 * $Id$
 */

class http_request_exception extends Exception {

    public function __construct($message, $code = 0) {
        if ($message instanceof Exception) {
            parent::__construct($message->getMessage(), $message->getCode());
        } else {
            parent::__construct($message, intval($code));
        }
    }

}
