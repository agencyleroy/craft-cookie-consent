<?php

namespace agencyleroy\craftcookieconsent\web\twig;

use agencyleroy\craftcookieconsent\web\twig\nodevisitors\EventTagAdder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Extension extends AbstractExtension
{

    const POS_HEAD_BEGIN = 6;

    const PH_HEAD_BEGIN = '<![CDATA[COOKIE-CONSENT-HEAD-BEGIN]]>';

    /**
     * @inheritdoc
     */
    public function getNodeVisitors()
    {
        return [
            new EventTagAdder(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('beginHead', [$this, 'beginHead']),
        ];
    }

    /**
     *
     */
    public function beginHead()
    {
        echo self::PH_HEAD_BEGIN;
    }
}
