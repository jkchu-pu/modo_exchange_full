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
 *  This retriever will make requests to get featured questions, search for questions, and retrieving answers for a specific question
 *  The initialization arguments described below can be used in feeds.yaml to customize the behavior of the retriever
 *
 * ### Initialization arguments:
 *
 * + __site__ (_string_)
 *     + String representing the StackExchange site to query
 *     + Defaults to 'stackoverflow'
 *
 * + __apiVersion__ (_string_)
 *     + Version of the Stack Exchange API to use.
 *     + Defaults to '2.1'
 *
 * + __apiKey__ (_string_)
 *     + String representing the StackExchange API key to use with each request
 *     + Optional. Requests will be rate limited if not configured. See http://api.stackexchange.com/docs/throttle
 *
 */

class ModoStackExchangeDataRetriever extends KGOURLDataRetriever implements KGOSearchDataRetriever, KGOItemDataRetriever
{
    protected $apiURL = 'api.stackexchange.com';            // See http://api.stackexchange.com/docs/ for full documentation
    protected $apiKey;
    protected $apiVersion;

    protected static $defaultSite = 'stackoverflow';
    protected static $defaultApiVersion = '2.1';

   /* questionParseMap is used when retrieving question items from API
    *
    *   A parse map can be used to parse an array of KGODataObjects from a data structure
    *   This parse map is run after the configured parser `KGOJSONDataParser` is used to parse JSON data into a PHP array. KGOJSONDataParser is configured in feeds.yaml
    *
    */
    private $questionParseMap = array (
            'key'                       => 'items',                                                   //  'key' defines the data attribute to be used to parse an object, the StackExchange API returns an array of 'items'
            'class'                     => 'ModoStackExchangeQuestionDataObject',                     //  'class' defines which KGODataObject subclass should be created, The ModoStackExchangeQuestionDataObject subclass exists because it needs to contain logic to request answers
            'includeUnmappedAttributes' => true,                                                      //  'includeUnmappedAttributes' tells the parse map to set attributes that are not explicitly defined below
            'prefix'                    => 'stack',                                                   //  'prefix' defines a prefix to be added to any unmapped attributes. It is recommended that a prefix is always used if includeUnmappedAttributes is true
            'attributes'                => array (                                                    //  'attributes' is the explicit list of KGODataObject attributes that will map to attributes in the data structure
                KGODataObject::ID_ATTRIBUTE            => 'question_id',                              //    The API will return a 'question_id' field for each item, this will become the KGODataObject's ID attribute
                KGODataObject::TITLE_ATTRIBUTE         => 'title',                                    //    Each item will also have a 'title' field, this will become the KGODataObject's TITLE attribute
                ModoStackExchangeQuestionDataObject::AVATAR_ATTRIBUTE => 'owner.profile_image',       //    A dot syntax can be used to extract from nested structures. The 'owner' field contains a 'profile_image' field that will be set to the QuestionDataObject's AVATAR attribute
                ModoStackExchangeQuestionDataObject::AUTHOR_ATTRIBUTE => 'owner.display_name'         //    Once again the dot syntax is used to extract the 'owner' 'display_name' field. This is set to the QuestionDataObject's AUTHOR attribute
            ),
            'processors' => array (                                                                   //  'processors' can be defined in a parse map for use on the parsed KGODataObject's attributes. Anytime this attribute is requested, the processors will be run
                ModoStackExchangeQuestionDataObject::AVATAR_ATTRIBUTE => array(                       //    Each processor is attached to a specific attribute, here the QuestionDataObject's AVATAR attribute
                    array(                                                                            //
                        'class'   => 'KGOImageDataProcessor',                                         //    The processor 'class' is used to define which subclass of KGODataProcessor should be applied
                        'options' => array(                                                           //    The KGOImageDataProcessor accepts some options. See the reference documentation for each KGODataProcessor to see availabel options
                            'maxWidth' => 60                                                          //    This option will limit the size of the KGOImage returned
                        )
                    )
                ),
            )
        );

    /* answerParseMap is used when retrieving answer items from API */
    private $answerParseMap = array (                                                                 //  The answerParseMap is a little more simple
            'key'                       => 'items',                                                   //  The StackExchange API returns an array of 'items' when answers are requested
            'class'                     => 'KGODataObject',                                           //  There is no need to add extra logic to an answer item, 'class' is KGODataObject for simplicity
            'includeUnmappedAttributes' => true,                                                      //  Again lets include unmapped attributes
            'prefix'                    => 'stack',                                                   //  Again lets include a prefix for unmapped attributes
            'attributes' => array (                                                                   //  The attributes we explicitly map
                                KGODataObject::ID_ATTRIBUTE => 'answer_id',                           //    'answer_id' will become our KGODataObject ID attribute
                                'profile_image' => 'owner.profile_image',                             //    The 'owner' field contains metadata about the answer author, we are interested in the 'profile_image'
                                'author' => 'owner.display_name'                                      //    The 'owner' field also contains a 'display_name' which will be used for the 'author' attribute
                            ),
        );

    /**
     *  Builds the base URL for the API by combining the `apiURL` and `apiVersion` into the required format
     *
     * @return string   Base URL for API requests
     */
    protected function getEndpoint(){
        return sprintf('http://%s/%s/', $this->apiURL, $this->apiVersion);
    }

    /*
     *  The `init` method takes an array of arguments. From these arguments we can initialize an instance of this retriever
     */
    /* Subclassed */
    protected function init($args){
        parent::init($args);

        $this->site       = kgo_array_val($args, 'site', static::$defaultSite);
        $this->apiVersion = kgo_array_val($args, 'apiVersion', static::$defaultApiVersion);
        $this->apiKey     = kgo_array_val($args, 'apiKey');
    }

    /*
     *  `initRequestIfNeeded` is called anytime a request may need to be initialized.
     *  The parent handles the logic of initializing the request, the method is subclassed so headers/parameters can be added to each request the retriever makes
     */
    /* Subclassed */
    protected function initRequestIfNeeded(){
        parent::initRequestIfNeeded();

        $this->addHeader('Accept-Encoding', 'deflate');
        $this->addParameter('site', $this->site);

        if ($limit = $this->getOption('limit')) {
          $this->addParameter('pagesize', $limit);
        }

        if ($this->apiKey) {
          $this->addParameter('key', $this->apiKey);
        }
    }

    /*
     *  `getFeaturedQuestions` reaches out to the 'questions/featured' API URL
     *  Notice that we set the parse map to the `questionParseMap` just before calling `getData`. Parse maps are run in the `KGODataParser` `parseResponse` method which automatically invoke `getData`.
     */
    /**
     * Retrieves featured questions from the Stack Exchange API
     *
     * @return array    Array of KGODataObjects defined by `questionParseMap`
     */
    public function getFeaturedQuestions() {
        $this->setBaseURL($this->getEndpoint() . 'questions/featured');
        // $this->addParameter('sort', 'votes');
        $this->setParseMap($this->questionParseMap);
        return $this->getData();
    }


    /*
     *  The search method below is an implementation of the `KGOSearchDataRetriever` interface
     *  This method will be invoked automatically by the `KGOItemsDataModel` when a search occurs. See the 'getItems' method in `KGOItemsDataModel` where it references `canSearch`
     */
    /**
     * Retrieves a list of questions whose title match `searchTerms`
     *
     * @param string               $searchTerms A string value to filter the items.
     * @param KGODataResponse|null $response    The data response.
     *
     * @return array    Array of KGODataObjects defined by `questionParseMap`
     */
    public function search($searchTerms, &$response=null) {
        $this->setBaseURL($this->getEndpoint() . 'search');
        $this->addParameter('intitle', $searchTerms);
        $this->addParameter('sort', 'votes');
        $this->setParseMap($this->questionParseMap);
        return $this->getData($response);
    }

    /*
     *  The `getQuestion` method is called below in `getItem`
     *  A 'filter' parameter is added so the question.body is included in the response. http://api.stackexchange.com/docs/filters
     *  The `questionParseMap` is set just before the call to getData. The API response structure is the same when requesting a quesiton or multiple questions, so the same parseMap can be used
     */
    /**
     * @param string                $id        The question id to retriever
     * @param KGODataResponse|null  $response  The data response.
     *
     * @return KGODataObject|null KGODataObject defined by `questionParseMap`
     */
    protected function getQuestion($id, &$response) {
        $this->setBaseURL(sprintf("%s%s/%s", $this->getEndpoint(), 'questions', $id));
        $this->addParameter('filter', '!9hnGss2Cz');    // This adds the question.body filter

        $this->setParseMap($this->questionParseMap);
        return reset($this->getData($response));        // return a single KGODataObject, not an array containing a single KGODataObject
    }

    /*
     *  `getAnswersForQuestion` fetches the list of answers for a particular question
     *  A 'sort' parameter is added to sort answers by their number of votes
     *  A 'filter' parameter is added so answer.body is included in the response. http://api.stackexchange.com/docs/filters
     *  The `answerParseMap` is set just before the call to getData.
     */
    /**
     * @param string                $id        The question id to retriever
     * @param KGODataResponse|null  $response  The data response.
     *
     * @return array    Array of KGODataObjects defined by `answerParseMap`
     */
    public function getAnswersForQuestion($id) {
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
    /*
     *  This method is called automatically by the `KGOItemsDataModel`
     *  Its return data is not actually used for anything, but the `$response` needs to be created. $response should never be initialized manually, so we call out to the `getData` method
     */
    public function getItems(&$response=null) {
        // need to call getData here so $response is created
        return $this->getData($response);
    }

    /*
     *  When we want a single item, we can assume that we want a single question object.
     *  We simply call the `getQuestion` method defined above to do so.
     */
    public function getItem($id, &$response=null) {
        return $this->getQuestion($id, $response);
    }

    //
    // !!! end of KGOItemDataRetriever interface
    //

}