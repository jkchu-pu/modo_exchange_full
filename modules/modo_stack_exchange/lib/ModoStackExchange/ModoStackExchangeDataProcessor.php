<?php

/*
 * Copyright © 2010 - 2014 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */


/**
 * @ingroup DataProcessor
 *
 * @brief Returns gzinflated on value
 *
 */

class ModoStackExchangeDataProcessor extends KGODataProcessor {

    protected function processValue($value, $object = null) {
        return gzinflate($value);
    }

    protected function canProcessValue($value, $object = null) {
        return is_scalar($value);
    }

}