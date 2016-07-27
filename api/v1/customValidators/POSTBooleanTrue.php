<?php namespace API\Validation\Rules;

use Respect\Validation\Rules;
use Respect\Validation\Exceptions;

class POSTBooleanTrue extends Rules\AbstractRule {
    public function validate($input) {
        return (1 === $input ||
                '1' === $input ||
                true === $input ||
                'true' === strtolower($input) ||
                'yes' === strtolower($input) ||
                'on' === strtolower($input));
    }
}

class POSTBooleanTrueException extends Exceptions\BoolTypeException {
    /**
     * We will use the same messages templates as the
     * "AllOf" exception, so nothing is needed here.
     * 
     * https://gist.github.com/augustohp/d9275aca4cf6d833888e
     */
}