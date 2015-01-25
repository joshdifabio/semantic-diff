<?php
namespace SemanticDiff;

use PhpParser\Node\Stmt\Class_;

/**
 * @author Joshua Di Fabio <joshdifabio@gmail.com>
 */
class Property
{
    private $modifiers;
    private $name;
    private $defaultValue;
    
    public function __construct($modifiers, $name, $defaultValue = null)
    {
        $this->modifiers = $modifiers;
        $this->name = $name;
        $this->defaultValue = $defaultValue;
    }
    
    public function getModifiers()
    {
        return $this->modifiers;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function isPublic()
    {
        return (bool) ($this->modifiers & Class_::MODIFIER_PUBLIC);
    }

    public function isProtected()
    {
        return (bool) ($this->modifiers & Class_::MODIFIER_PROTECTED);
    }

    public function isPrivate()
    {
        return (bool) ($this->modifiers & Class_::MODIFIER_PRIVATE);
    }

    public function isStatic()
    {
        return (bool) ($this->modifiers & Class_::MODIFIER_STATIC);
    }
}
