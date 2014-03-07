<?php

class SEQuestionDataObject extends KGODataObject {

    const AUTHOR_ATTRIBITE = 'se:author';
    const ANSWERS_ATTRIBUTE = 'se:answers';

    public function getAuthor() {
        return $this->getAttribute(self::AUTHOR_ATTRIBITE);
    }

    public function getAnswers() {
        $retriever = $this->getDataRetriever();
        if (is_callable(array($retriever, 'getAnswersForQuestion'))) {
            return $retriever->getAnswersForQuestion($this->getId());
        }
        return null;
    }
}