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
 * @ingroup Module
 *
 * @brief Used to search/browse StackExchange information
 */

class ModoStackExchangeModule extends KGOModule {

    /*
     *  The initializeForPageConfigObjects_ methods below don't need to do much, they simply check if a feed has been configured
     *  The $objects configured in the page objdefs will take control from here
     */

    protected function initializeForPageConfigObjects_index(KGOUIPage $page, $objects) {
        if (!($feed = $this->getFeed())) {
            $this->setPageError($page, 'modo_stack_exchange.error.notConfigured');
            return;
        }
    }

    protected function initializeForPageConfigObjects_search(KGOUIPage $page, $objects) {
        if (!($feed = $this->getFeed())) {
            $this->setPageError($page, 'modo_stack_exchange.error.notConfigured');
            return;
        }
    }

    protected function initializeForPageConfigObjects_detail(KGOUIPage $page, $objects) {
        if (!($feed = $this->getFeed())) {
            $this->setPageError($page, 'modo_stack_exchange.error.notConfigured');
            return;
        }
    }

    /*
     *  The getFeaturedQuestions method simply asks the configure dataModel (returned from `getFeed`) for the featuredQuestions
     */

    public function getFeaturedQuestions() {
        if ($feed = $this->getFeed()) {
            $questions = $feed->getFeaturedQuestions();
            return $questions;
        }
    }
}