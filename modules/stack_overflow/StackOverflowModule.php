<?php

/*
 * Copyright © 2010 - 2014 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

class StackOverflowModule extends KGOModule {

    protected function initializeForPageConfigObjects_index(KGOUIPage $page, $objects) {
        if (!($feed = $this->getFeed())) {
            return;
        }
    }

    protected function initializeForPageConfigObjects_search(KGOUIPage $page, $objects) {
        if (!($feed = $this->getFeed())) {
            return;
        }
    }

    protected function initializeForPageConfigObjects_detail(KGOUIPage $page, $objects) {
        if (!($feed = $this->getFeed())) {
            return;
        }
    }

    public function getFeaturedQuestions() {
        if ($feed = $this->getFeed()) {
            $questions = $feed->getFeaturedQuestions();
            return $questions;
        }
    }
}