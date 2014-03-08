<?php

class SEQuestionDataObject extends KGODataObject {

    const AUTHOR_ATTRIBUTE = 'se:author';
    const ANSWERS_ATTRIBUTE = 'se:answers';
    const AVATAR_ATTRIBUTE = 'se:avatar';

    public function getAuthor() {
        return $this->getAttribute(self::AUTHOR_ATTRIBUTE);
    }

    public function getAnswers() {
        $retriever = $this->getDataRetriever();
        if (is_callable(array($retriever, 'getAnswersForQuestion'))) {
            return $retriever->getAnswersForQuestion($this->getId());
        }
        return null;
    }

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