<?php
/**
 * A table generator implementation for the PHPDocsMD package.
 *
 * @package tad\WPBrowser\Documentation
 * @since   TBD
 */

namespace tad\WPBrowser\Documentation;

use PHPDocsMD\FunctionEntity;
use PHPDocsMD\ParamEntity;

/**
 * Class TableGenerator
 *
 * @package tad\WPBrowser\Documentation
 */
class TableGenerator implements \PHPDocsMD\TableGenerator
{

    /**
     * Whether the abstract nature of methods should be declared in the method documentaion or not.
     *
     * @var bool
     */
    protected $declareAbstraction;

    /**
     * The string that will hold the output produced by the class.
     *
     * @var string
     */
    protected $output = '';

    /**
     * The Markdown parser instance.
     *
     * @var \Parsedown
     */
    protected $parser;

    public function __construct()
    {
        $this->parser = new \Parsedown();
    }

    /**
     * Create a markdown-formatted code view out of an example comment
     *
     * @param string $example
     *
     * @return string
     */
    public static function formatExampleComment($example)
    {
        return '';
    }

    /**
     * All example comments found while generating the table will be
     * appended to the end of the table. Set $toggle to false to
     * prevent this behaviour
     *
     * @param bool $toggle
     */
    function appendExamplesToEndOfTable($toggle)
    {
        // no-op
    }

    /**
     * Begin generating a new markdown-formatted table
     */
    function openTable()
    {
        $this->output .= '<table style="width: 100%;">
        <thead>
        <tr>
            <th>Method</th>
            <th>Example</th>
        </tr>
        </thead>';
    }

    /**
     * Toggle whether or not methods being abstract (or part of an interface)
     * should be declared as abstract in the table
     *
     * @param bool $toggle
     */
    function doDeclareAbstraction($toggle)
    {
        $this->declareAbstraction = (bool)$toggle;
    }

    /**
     * Generates a markdown formatted table row with information about given function. Then adds the
     * row to the table and returns the markdown formatted string.
     *
     * @param FunctionEntity $func
     *
     * @return string
     */
    function addFunc(FunctionEntity $func)
    {
        $this->fullClassName = $func->getClass();

        $str = '<strong>';

        if ($this->declareAbstraction && $func->isAbstract()) {
            $str .= 'abstract ';
        }

        $str .= $func->getName() . '(';

        if ($func->hasParams()) {
            $params = [];
            foreach ($func->getParams() as $param) {
                $paramStr = '<em>' . $param->getType() . '</em> <strong>' . $param->getName();
                if ($param->getDefault()) {
                    $paramStr .= '=' . $param->getDefault();
                }
                $paramStr .= '</strong>';
                $params[] = $paramStr;
            }
            $str .= '</strong>' . implode(', ', $params) . ')';
        } else {
            $str .= ')';
        }

        $str .= '</strong> : <em>' . $func->getReturnType() . '</em>';

        if ($func->isDeprecated()) {
            $str = '<strike>' . $str . '</strike>';
            $str .= '<br /><em>DEPRECATED - ' . $func->getDeprecationMessage() . '</em>';
        } elseif ($func->getDescription()) {
            $str .= '<br /><br /><em>' . $func->getDescription() . '</em>';
        }

        $str = str_replace(['</strong><strong>', '</strong></strong> '], ['', '</strong>'], trim($str));

        $example = '';
        if ($func->getExample()) {
            $rawExample = $func->getExample();
            $example = $this->parser->text($rawExample);
        }

        $function = ($func->isStatic() ? 'static ' : '') . $str;
        $paramDescription = '';
        if ($func->hasParams()) {
            $paramHead = '<p><strong>Parameters:</strong><ul>';
            $paramDescription = $paramHead . implode(PHP_EOL, array_map(function (ParamEntity
                $param) {
                    return sprintf('%s <strong>%s</strong>: %s',
                        $param->getType(),
                        $param->getName(),
                        $param->getDescription()
                    );
                }, $func->getParams()));
            $paramDescription .= '</ul></p>';
        }
        $function .= $paramDescription;
        $row = '<tr><td>' . $function . '</td>';
        $row .= '<td>' . $example . '</td></tr>';

        $this->output .= PHP_EOL . $row;

        return $row;
    }

    /**
     * @return string
     */
    function getTable()
    {
        return trim($this->output) . '</table>';
    }
}
