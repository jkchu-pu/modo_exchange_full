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
 * @ingroup DataRetriever
 *
 * @brief This retriever handles multiple endpoints of the stackechange api.
 *
 *  This retriever will make requests to get featured questions,
 *  searching for questions, and retrieving answers for questions.
 *
 */

class ModoStackExchangeDataRetriever extends KGOURLDataRetriever implements KGOSearchDataRetriever, KGOItemDataRetriever
{

    protected $site = 'stackoverflow';
    protected $apiURL = 'api.stackexchange.com';            // See http://api.stackexchange.com/docs/ for full documentation
    protected $version = '2.1';
    protected $apiKey;


    /* questionParseMap is used when retrieving question items from API */
    private $questionParseMap = array (
            'key'                       => 'items',
            'class'                     => 'ModoStackExchangeQuestionDataObject',
            'includeUnmappedAttributes' => true,
            'attributes'                => array (
                KGODataObject::ID_ATTRIBUTE            => 'question_id',
                KGODataObject::TITLE_ATTRIBUTE         => 'title',
                ModoStackExchangeQuestionDataObject::AVATAR_ATTRIBUTE => 'owner.profile_image',
                ModoStackExchangeQuestionDataObject::AUTHOR_ATTRIBUTE => 'owner.display_name'
            ),
            'processors' => array (
                ModoStackExchangeQuestionDataObject::AVATAR_ATTRIBUTE => array(
                    array(
                        'class'   => 'KGOImageDataProcessor',
                        'options' => array(
                            'maxWidth' => 60
                        )
                    )
                ),
            )
        );

    /* answerParseMap is used when retrieving answer items from API */
    private $answerParseMap = array (
            'key' => 'items',
            'class' => 'KGODataObject',
            'includeUnmappedAttributes' => true,
            'attributes' => array (
                                KGODataObject::ID_ATTRIBUTE => 'answer_id',
                                'profile_image' => 'owner.profile_image',
                                'author' => 'owner.display_name'
                            ),
        );

    /**
     *
     * @return string   Base URL for API requests
     */
    protected function getEndpoint(){
        return sprintf('http://%s/%s/', $this->apiURL, $this->version);
    }

    /* Subclassed */
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

    /* Subclassed */
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

    /**
     * Retrieves featured questions.
     *
     * @return array    Array of KGODataObjects defined by `questionParseMap`
     */

    public function getFeaturedQuestions(){
        $this->setBaseURL($this->getEndpoint() . 'questions/featured');
        // $this->addParameter('sort', 'votes');
        $this->setParseMap($this->questionParseMap);
        return $this->getData();
    }

    /**
     * Retrieves a list of questions whose title match `searchTerms`
     *
     * @param string               $searchTerms A string value to filter the items.
     * @param KGODataResponse|null $response    The data response.
     *
     * @return array    Array of KGODataObjects defined by `questionParseMap`
     */

    public function search($searchTerms, &$response=null){
        $this->setBaseURL($this->getEndpoint() . 'search');
        $this->addParameter('intitle', $searchTerms);
        $this->addParameter('sort', 'votes');
        $this->setParseMap($this->questionParseMap);
        return $this->getData($response);
    }

    /**
     * @param string                $id        The question id to retriever
     * @param KGODataResponse|null  $response  The data response.
     *
     * @return KGODataObject|null KGODataObject defined by `questionParseMap`
     */

    public function getQuestion($id, &$response){
        $this->setBaseURL(sprintf("%s%s/%s", $this->getEndpoint(), 'questions', $id));
        # This adds the question.body filter
        $this->addParameter('filter', '!9hnGss2Cz');

        $this->setParseMap($this->questionParseMap);
        // kgo_debug($this->getData($response), true);
        return reset($this->getData($response));
    }

    /**
     * @param string                $id        The question id to retriever
     * @param KGODataResponse|null  $response  The data response.
     *
     * @return array    Array of KGODataObjects defined by `answerParseMap`
     */
    public function getAnswersForQuestion($id){
        $this->setBaseURL(sprintf("%s%s/%s/%s", $this->getEndpoint(), 'questions', $id, 'answers'));
        $this->addParameter('sort', 'votes');
        # Filter to add total and answer.body
        $this->addParameter('filter', '!9hnGsyXaB');

        $this->setParseMap($this->answerParseMap);
        return $this->getData();
    }

    //
    // !!! KGOItemDataRetriever interface
    //
    public function getItems(&$response=null) {
        // need to call getData here so $response is created
        return $this->getData($response);
    }

    //
    // !!! KGOItemDataRetriever interface
    //
    public function getItem($id, &$response=null) {
        return $this->getQuestion($id, $response);
    }

}