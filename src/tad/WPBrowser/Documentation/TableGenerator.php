<?php
/**
 * A table generator implementation for the PHPDocsMD package.
 *
 * @package tad\WPBrowser\Documentation
 * @since   TBD
 */

namespace tad\WPBrowser\Documentation;

use Parsedown;
use PHPDocsMD\FunctionEntity;
use ReflectionMethod;
use RuntimeException;

/**
 * Class TableGenerator
 *
 * @package tad\WPBrowser\Documentation
 */
class TableGenerator implements \PHPDocsMD\TableGenerator
{

    /**
     * Whether the abstract nature of methods should be declared in the method documentation or not.
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
     * The Markdown parser instance dedicated to formatting examples only.
     *
     * @since TBD
     *
     * @var \Parsedown
     */
    protected $exampleParser;

    /**
     * An array of functions added to the output.
     *
     * @var array<string>
     */
    protected $index = [];

    /**
     * The current class fully qualified name.
     *
     * @var string
     */
    protected $fullClassName;

    public function __construct()
    {
        $this->parser = new Parsedown();
        $this->exampleParser = new Parsedown();
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
     *
     * @return void
     */
    public function appendExamplesToEndOfTable($toggle)
    {
        // no-op
    }

    /**
     * Begin generating a new markdown-formatted table
     *
     * @return void
     */
    public function openTable()
    {
        $this->output .= '';
    }

    /**
     * Toggle whether or not methods being abstract (or part of an interface)
     * should be declared as abstract in the table
     *
     * @param bool $toggle
     *
     * @return void
     */
    public function doDeclareAbstraction($toggle)
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
     * @throws \DOMException
     * @throws \ReflectionException
     */
    public function addFunc(FunctionEntity $func)
    {
        $this->fullClassName = $func->getClass();

        // Skip the method if it's an @internal one.
        $methodFullDoc = (new ReflectionMethod($this->fullClassName, $func->getName()))->getDocComment();
        foreach ((array)explode(PHP_EOL, (string)$methodFullDoc) as $line) {
            if (strpos($line, ' @internal ') !== false) {
                return '';
            }
        }

        $str = PHP_EOL . '<h3>' . $func->getName() . '</h3>' . "\n\n<hr>\n\n";

        $str .= $this->parser->text($func->getDescription());

        $rawExample = $func->getExample();

        if (!$rawExample) {
            throw new RuntimeException("Method {$func->getClass()}::{$func->getName()} is missing an example.");
        }

        $exampleLines = array_filter(array_map('trim', explode("\n", $rawExample)), static function ($line) {
               return ! preg_match('/^`/', $line) ;
        });
        $str .= "\n```php\n" . implode("\n  ", $exampleLines) . "\n```\n";

        if ($func->hasParams()) {
            $str .= PHP_EOL . '<h4>Parameters</h4>' . PHP_EOL . '<ul>';
            $params = [];
            foreach ($func->getParams() as $param) {
                $paramStr = sprintf('<li><code>%s</code> <strong>%s</strong>', $param->getType(), $param->getName());

                $description = $param->getDescription();

                if (!empty($description)) {
                    $paramStr .= ' - ' . $this->parser->line($param->getDescription());
                }

                $params[] = $paramStr . '</li>';
            }
            $str .= PHP_EOL . implode(PHP_EOL, $params);
            $str .= '</ul>';
        }


        $this->output .= PHP_EOL . $str . PHP_EOL  . '  ';
        $this->index[] = $func->getName();
        return $str;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        $this->parser->setBreaksEnabled(true);
        $toc = '## Public API' . PHP_EOL . $this->buildToc();
        return $toc . PHP_EOL . trim($this->output) . PHP_EOL;
    }

    /**
     * Builds the table of contents.
     *
     * @return string The built table of contents.
     */
    protected function buildToc()
    {
        if (empty($this->index)) {
            return '';
        }

        $toc = '<nav>' . "\n\t<ul>";
        foreach ($this->index as $funcName) {
            $toc .= "\n\t\t\<li>\n\t\t\t"
                    . '<a href="#'
                    . strtolower($funcName)
                    . '">'
                    . $funcName
                    . '</a>'
                    . "\n\t\t</li>";
        }

        $toc .= "\n\t</ul>" . PHP_EOL . '</nav>' . PHP_EOL;

        return $toc;
    }
}
