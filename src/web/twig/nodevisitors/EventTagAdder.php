<?php

namespace agencyleroy\craftcookieconsent\web\twig\nodevisitors;

use craft\web\twig\nodevisitors\EventTagAdder as BaseEventTagAdder;
use Twig\Environment;
use Twig\Node\DoNode;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Node;
use Twig\Node\TextNode;
use Craft;

/**
 *
 */
class EventTagAdder extends BaseEventTagAdder
{
    // Properties
    // =========================================================================

    /**
     * @var bool Whether we're in the middle of finding the `beginBody()` tag
     */
    private $_findingBeginHead = false;

    // Static
    // =========================================================================

    /**
     * @var bool Whether the head() tag has been found/added
     */
    protected static $foundBeginHead = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function enterNode(Node $node, Environment $env)
    {
        // Ignore if we're not rendering a page template
        if (!Craft::$app->getView()->getIsRenderingPageTemplate()) {
            return $node;
        }

        // If this is a text node and we're still adding event tags, process it
        if ($node instanceof TextNode && !static::$foundBeginHead) {
            $node = $this->_processTextNode($node, $env);
        }

        return $node;
    }

    // Private Methods
    // =========================================================================

    /**
     * Processes a text node.
     *
     * @param TextNode $node
     * @param Environment $env
     * @return Node
     */
    private function _processTextNode(TextNode $node, Environment $env): Node
    {
        $data = $node->getAttribute('data');

        // Are we looking for `<head>`?
        if (static::$foundBeginHead === false) {
            // We haven't found any part of `<head>` yet, right?
            if ($this->_findingBeginHead === false) {
                // Did we just find `<head(>)`?
                if (preg_match('/(<head\b[^>]*)(>)?/', $data, $matches, PREG_OFFSET_CAPTURE) === 1) {
                    // Did it include the `>`?
                    if (!empty($matches[2][0])) {
                        static::$foundBeginHead = true;
                        $beginHeadPos = $matches[0][1] + strlen($matches[0][0]);

                        return $this->_insertEventNode($node, $beginHeadPos, 'beginHead');
                    }

                    // Will have to wait for the next text node
                    $this->_findingBeginHead = true;
                }
            } else {
                // Did we just find the `>`?
                if (preg_match('/^[^>]*>/', $data, $matches)) {
                    $this->_findingBeginHead = false;
                    static::$foundBeginHead = true;
                    $beginHeadPos = strlen($matches[0]);

                    return $this->_insertEventNode($node, $beginHeadPos, 'beginHead');
                }
            }
        }

        return $node;
    }

    /**
     * Inserts a new event function node at a specific point in a given text node’s data.
     *
     * @param TextNode $node
     * @param Environment $env
     * @param int $pos
     * @param string $functionName
     * @return Node
     */
    private function _insertEventNode(TextNode $node, int $pos, string $functionName): Node
    {
        $data = $node->getAttribute('data');
        $preSplitHtml = substr($data, 0, $pos);
        $postSplitHtml = substr($data, $pos);
        $startLine = $node->getTemplateLine();
        $splitLine = $startLine + substr_count($preSplitHtml, "\n");

        return new Node([
            new TextNode($preSplitHtml, $startLine),
            new DoNode(new FunctionExpression($functionName, new Node(), $splitLine), $splitLine),
            new TextNode($postSplitHtml, $splitLine),
        ], [], $startLine);
    }
}
