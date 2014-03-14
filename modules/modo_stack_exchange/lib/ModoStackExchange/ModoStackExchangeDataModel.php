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
 * @ingroup DataModel
 *
 * @brief Subclass of KGOItemsDataModel
 *
 */

class ModoStackExchangeDataModel extends KGOItemsDataModel
{
    /**
     * Returns the featured questions
     *
     * @return array    Array of KGODataObjects
     */
    public function getFeaturedQuestions() {
        return $this->retriever->getFeaturedQuestions();
    }

}