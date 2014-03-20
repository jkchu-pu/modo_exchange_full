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
 * @ingroup UI_Detail UI_Object_Subclassable
 * @brief The abstract detail page superclass.
 *
 * ### Fields:
 *
 * + author (_string_)
 *     + The author of the item.  Defaults to `null` (no author).
 *
 */

class KGOUIDetailStackExchange extends KGOUIDetail
{
    protected static $hasFieldAuthor = true;
    protected static $hasFocalBackground = true;

    protected static $optionDefaultShare = false;

    /* Subclassed */
    protected function init() {
        parent::init();

    }

    /* Subclassed */
    protected function prepareForExport() {
        parent::prepareForExport();

    }
}
