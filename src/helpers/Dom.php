<?php

namespace agencyleroy\craftcookieconsent\helpers;

use DOMDocument;

/**
 *
 */
class Dom
{

    /**
     *
     */
    public static function loadHtml($html)
    {
        $libxmlErrflag = libxml_use_internal_errors(true);
        $oldValue = libxml_disable_entity_loader(true);

        $dom = new DOMDocument();
        $dom->loadHTML(mb_convert_encoding(trim($html), 'HTML-ENTITIES', 'UTF-8'));

        libxml_disable_entity_loader($oldValue);
        libxml_use_internal_errors($libxmlErrflag);

        return $dom;
    }
}
