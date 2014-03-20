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
 * @brief Subclass of KGOUIDetail used to display detailed information about a Stack Exchange question object
 *
 */

class KGOUIDetailModoStackExchange extends KGOUIDetail
{
    protected static $hasFieldAuthor = true;        // enables 'author' field
    protected static $hasFocalBackground = true;    // enables background to have 'focal' style
    protected static $optionDefaultShare = false;   // disables 'share' button
}
