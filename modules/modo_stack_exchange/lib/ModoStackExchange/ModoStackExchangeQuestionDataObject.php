<?php

class ModoStackExchangeQuestionDataObject extends KGODataObject {

    const AUTHOR_ATTRIBUTE = 'se:author';
    const ANSWERS_ATTRIBUTE = 'se:answers';
    const AVATAR_ATTRIBUTE = 'se:avatar';

    /**
     * Returns the title for this object.
     *
     * @return string
     */
    public function getAuthor() {
        return $this->getAttribute(self::AUTHOR_ATTRIBUTE);
    }

    /**
     *  Returns answers for this question object
     *
     *  @return array|null    Array of KGODataObjects or null if data retriever can not get answers
     */
    public function getAnswers() {
        $retriever = $this->getDataRetriever();
        if (is_callable(array($retriever, 'getAnswersForQuestion'))) {
            return $retriever->getAnswersForQuestion($this->getId());
        }
        return null;
    }


    //
    // !!! KGOUIObjectInterface
    //
    public function getUIField(KGOUIObject $object, $field) {
        switch ($field) {
            case 'thumbnail':
                return $this->getAttribute(self::AVATAR_ATTRIBUTE);
            case 'subtitle':
                return $this->getAuthor();
            default:
                return parent::getUIField($object, $field);
        }
    }
}