<?php

/*
 * Copyright © 2010 - 2014 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

class StackExchangeDataRetriever extends KGOURLDataRetriever implements KGOSearchDataRetriever, KGOItemDataRetriever
{

    protected $site = 'stackoverflow';
    protected $apiURL = 'api.stackexchange.com';            // See http://api.stackexchange.com/docs/ for full documentation
    protected $version = '2.1';
    protected $apiKey;

    protected function getEndpoint(){
        return sprintf('http://%s/%s/', $this->apiURL, $this->version);
    }

    protected function init($args){
        parent::init($args);

        if($site = Kurogo::arrayVal($args, 'site')){
          $this->site = $site;
        }

        if($version = Kurogo::arrayVal($args, 'apiVersion')){
          $this->version = $version;
        }

        if($apiKey = Kurogo::arrayVal($args, 'apiKey')){
          $this->apiKey = $apiKey;
        }
    }

    protected function initRequestIfNeeded(){
        parent::initRequestIfNeeded();

        $this->addHeader('Accept-Encoding', 'deflate');
        $this->addParameter('site', $this->site);

        if ($limit = $this->getOption('limit')) {
          $this->addParameter('pagesize', $limit);
        }

        if($this->apiKey){
          $this->addParameter('key', $this->apiKey);
        }
    }

    public function getFeaturedQuestions(){
        $this->setOption('action', 'getFeaturedQuestions');
        $this->setBaseURL($this->getEndpoint() . 'questions/featured');
        $this->addParameter('sort', 'votes');
        $this->setParseMap($this->questionParseMap);
        return $this->getData();
    }

    public function search($searchTerms, &$response=null){
        $this->setBaseURL($this->getEndpoint() . 'search');
        $this->addParameter('intitle', $searchTerms);
        $this->addParameter('sort', 'votes');
        $this->setParseMap($this->questionParseMap);
        return $this->getData($response);
    }

    public function getQuestion($id, &$response){
        $this->setBaseURL(sprintf("%s%s/%s", $this->getEndpoint(), 'questions', $id));
        # This adds the question.body filter
        $this->addParameter('filter', '!9hnGss2Cz');

        $this->setParseMap($this->questionParseMap);
        // kgo_debug($this->getData($response), true);
        return reset($this->getData($response));
    }

// KGOItemDataRetriever interface
    public function getItems(&$response=null) {
        // need to call getData here so $response is created
        return $this->getData($response);
    }

    public function getItem($id, &$response=null) {
        return $this->getQuestion($id, $response);
    }





    public function getQuestions(){
        $this->setBaseURL($this->getEndpoint() . 'questions');
        $this->addParameter('sort', 'votes');
        return $this->getData();
    }

    public function getAnswersForQuestion($questionID){
        $this->setBaseURL(sprintf("%s%s/%s/%s", $this->getEndpoint(), 'questions', $questionID, 'answers'));
        $this->addParameter('sort', 'votes');
        # Filter to add total and answer.body
        $this->addParameter('filter', '!9hnGsyXaB');
        return $this->getData();
    }

    public function getAnswer($id){
        # Clear cache because this is potentially the second
        # data request during the current request.
        $this->clearInternalCache();
        $this->setBaseURL(sprintf("%s%s/%s", $this->getEndpoint(), 'answers', $id));
        # This adds the answer.body filter
        $this->addParameter('filter', '!9hnGsyXaB');
        return $this->getData();
    }


    public $questionParseMap = array (
            'key' => 'items',
            'class' => 'KGODataObject',
            'includeUnmappedAttributes' => true,
            'attributes' => array (
                                KGODataObject::ID_ATTRIBUTE => 'question_id',
                                KGODataObject::TITLE_ATTRIBUTE => 'title',
                                'profile_image' => 'owner.profile_image',
                                'author' => 'owner.display_name'
                            ),
        );

}