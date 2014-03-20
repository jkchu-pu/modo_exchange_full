<?php

/*
 * Copyright © 2010 - 2014 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

/*
 *  This StackExchange API compresses all its response. http://api.stackexchange.com/docs/compression
 *  This DataProcessor is used as a preprocessor so each response can be inflated before it is parsed
 */

/**
 * @ingroup DataProcessor
 *
 * @brief Returns gzinflated value
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