<?php
/**
 * A table generator implementation for the PHPDocsMD package.
 *
 * @package tad\WPBrowser\Documentation
 * @since   TBD
 */

namespace tad\WPBrowser\Documentation;

use PHPDocsMD\FunctionEntity;

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

    /**
     * An array of functions added to the output.
     *
     * @var array
     */
    protected $index = [];

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
        $this->output .= '';
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

        // Skip the method if it's an @internal one.
        $methodReflection = new \ReflectionMethod($this->fullClassName, $func->getName());
        $methodFullDoc = $methodReflection->getDocComment();
        foreach (explode(PHP_EOL, $methodFullDoc) as $line) {
            if (strpos($line, ' @internal ') !== false) {
                return '';
            }
        }


        $str = '<h4 id="' . $func->getName() . '">' . $func->getName() . '</h4>' . PHP_EOL . '- - -' . PHP_EOL;
        $str .= $func->getDescription();

        if ($func->getExample()) {
            $rawExample = $func->getExample();
            $example = $this->parser->text($rawExample);
            $str .= PHP_EOL . $example;
        }
        if ($func->hasParams()) {
            $str .= PHP_EOL . '<h5>Parameters</h5><ul>';
            $params = [];
            foreach ($func->getParams() as $param) {
                $paramStr = '<li><em>' . $param->getType() . '</em> <strong>' . $param->getName() . '</strong>';

                if ($param->getDefault()) {
                    $paramStr .= ' = <em>' . $param->getDefault() . '</em>';
                }

                $description = $param->getDescription();

                if (!empty($description)) {
                    $paramStr .= ' - ' . $param->getDescription();
                }

                $paramStr .= '</li>';
                $params[] = $paramStr;
            }
            $str .= PHP_EOL . implode(PHP_EOL, $params) . '</ul>';
        }

        $this->output .= PHP_EOL . $str;
        $this->index[] = $func->getName();

        return $str;
    }

    /**
     * @return string
     */
    function getTable()
    {
        $output = trim($this->output);

        return '<h3>Methods</h3>' . $this->buildToc() . $output . '</br>';
    }

    protected function buildToc()
    {
        if (empty($this->index)) {
            return '';
        }

        $toc = '<nav><ul>';
        foreach ($this->index as $funcName) {
            $toc .= '<li><a href="#' . $funcName . '">' . $funcName . '</a></li>';
        }

        $toc .= '</ul></nav>';

        return $toc;
    }
}
