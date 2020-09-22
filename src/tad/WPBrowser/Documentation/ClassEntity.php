<?php
/**
 * Object describing a class or an interface.
 *
 * This is really a bad hack to "replace" the base implementation at run-time with one that will
 * not return a title.
 *
 * @package PHPDocsMD
 */
namespace PHPDocsMD;

/**
 * Class ClassEntity
 *
 * @package PHPDocsMD
 */
class ClassEntity extends CodeEntity
{

    /**
     * A list of the class functions.
     *
     * @var array<\PHPDocsMD\FunctionEntity>
     */
    private $functions = [];

    /**
     * Whether the class is an interface or not.
     *
     * @var bool
     */
    private $isInterface = false;

    /**
     * Whether the class is abstract or not.
     *
     * @var bool
     */
    private $abstract = false;

    /**
     * Whether the class has the ignore tag or not.
     *
     * @var bool
     */
    private $hasIgnoreTag = false;

    /**
     * Whether the class has the internal tag or not.
     *
     * @var bool
     */
    private $hasInternalTag = false;

    /**
     * The class extended bu the class, if any.
     *
     * @var string
     */
    private $extends = '';

    /**
     * A list of interfaces the class implements.
     *
     * @var array<string>
     */
    private $interfaces = [];

    /**
     * A list of see links for the class.
     *
     * @var array<string>
     */
    private $see = [];

    /**
     * Whether the class is PHP native or not.
     *
     * @var bool
     */
    protected $isNative;

    /**
     * Sets or gets whether a class is abstract or not.
     *
     * @param bool|null $isAbstract Whether a class is abstract or not.
     *
     * @return bool Whether a class is abstract or not.
     */
    public function isAbstract($isAbstract = null)
    {
        if ($isAbstract === null) {
            return $this->abstract;
        }

        return $this->abstract = (bool) $isAbstract;
    }

    /**
     * Sets or gets whether a class has the ignore tag or not.
     *
     * @param bool|null $hasIgnoreTag Whether a class has the ignore tag or not.
     *
     * @return bool Whether a class has the ignore tag or not.
     */
    public function hasIgnoreTag($hasIgnoreTag = null)
    {
        if ($hasIgnoreTag === null) {
            return $this->hasIgnoreTag;
        } else {
            return $this->hasIgnoreTag = (bool)$hasIgnoreTag;
        }
    }

    /**
     * Returns the class description.
     *
     * @return string The class description.
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * Sets or gets whether a class entity has the internal tag or not.
     *
     * @param bool|null $hasInternalTag Whether a class entity has the internal tag or not.
     *
     * @return bool Whether a class entity has the internal tag or not.
     */
    public function hasInternalTag($hasInternalTag = null)
    {
        if ($hasInternalTag === null) {
            return $this->hasInternalTag;
        }

        return $this->hasInternalTag = (bool) $hasInternalTag;
    }

    /**
     * Sets or gets whether a class entity is an interface or not.
     *
     * @param bool|null $isInterface Whether the class entity is an interface or not.
     *
     * @return bool Whether the class entity is an interface or not.
     */
    public function isInterface($isInterface = null)
    {
        if ($isInterface === null) {
            return $this->isInterface;
        }

        return $this->isInterface = (bool) $isInterface;
    }

    /**
     * Sets , or returns, whether  the class is a PHP native one or not.
     *
     * @param bool|null $toggle Whether the class is a PHP native one or not; if `null` the current value will be
     *                          returned.
     *
     * @return bool Whether the class is native or not.
     */
    public function isNative($toggle = null)
    {
        if ($toggle === null) {
            return $this->isNative;
        }

        return $this->isNative = (bool)$toggle;
    }

    /**
     * Sets the class extended by the class.
     *
     * @param string $extends The class extended by the class.
     *
     * @return void
     */
    public function setExtends($extends)
    {
        $this->extends = Utils::sanitizeClassName($extends);
    }

    /**
     * Returns the parent class.
     *
     * @return string The parent class of the class.
     */
    public function getExtends()
    {
        return $this->extends;
    }

    /**
     * Sets the list of class functions.
     *
     * @param array<\PHPDocsMD\FunctionEntity> $functions The list of class functions.
     *
     * @return void
     */
    public function setFunctions(array $functions)
    {
        $this->functions = $functions;
    }

    /**
     * Sets the list of class see links.
     *
     * @param array<string> $see The list of class see links.
     *
     * @return void
     */
    public function setSee(array $see)
    {
        $this->see = [];
        foreach ($see as $i) {
            $this->see[] = $i;
        }
    }

    /**
     * Sets the list of interfaces implemented by the class.
     *
     * @param array<string> $implements The list of interfaces implemented by the class.
     *
     * @return void
     */
    public function setInterfaces(array $implements)
    {
        $this->interfaces = [];
        foreach ($implements as $interface) {
            $this->interfaces[] = Utils::sanitizeClassName($interface);
        }
    }

    /**
     * Returns a list of Interfaces implemented by the class.
     *
     * @return array<string> A list of interfaces implemented by the class.
     */
    public function getInterfaces()
    {
        return $this->interfaces;
    }

    /**
     * Returns a list of see links for the class.
     *
     * @return array<string> A list of see links.
     */
    public function getSee()
    {
        return $this->see;
    }

    /**
     * @return \PHPDocsMD\FunctionEntity[]
     */
    public function getFunctions()
    {
        return $this->functions;
    }

    /**
     * Sets the class name.
     *
     * @param string $name
     *
     * @return void
     */
    public function setName($name)
    {
        parent::setName(Utils::sanitizeClassName($name));
    }

    /**
     * Check whether this object is referring to given class name or object instance
     * @param string|object $class
     * @return bool
     */
    public function isSame($class)
    {
        $className = is_object($class) ? get_class($class) : $class;
        return Utils::sanitizeClassName($className) == $this->getName();
    }

    /**
     * Generate a title describing the class this object is referring to
     * @param string $format
     * @return string
     */
    public function generateTitle($format = '%label%: %name% %extra%')
    {
        return '';
    }

    /**
     * Generates an anchor link out of the generated title (see generateTitle)
     * @return string
     */
    public function generateAnchor()
    {
        $title = $this->generateTitle();
        return strtolower(str_replace([':', ' ', '\\', '(', ')'], ['', '-', '', '', ''], $title));
    }
}
