<?php 

namespace TBela\CSS;

use Exception;
use InvalidArgumentException;
use JsonSerializable;
use ArrayAccess;
use stdClass;
use TBela\CSS\Interfaces\RuleListInterface;
use TBela\CSS\Parser\SourceLocation;
use TBela\CSS\Query\Evaluator;
use TBela\CSS\Value\Set;
use function get_class;
use function is_callable;
use function is_null;
use function str_ireplace;

/**
 * Css node base class
 * @package TBela\CSS
 */
abstract class Element implements Query\QueryInterface, JsonSerializable, ArrayAccess, Interfaces\RenderableInterface   {

    use ArrayTrait;

    /**
     * @var stdClass|null
     * @ignore
     */
    protected $ast = null;
    /**
     * @ignore
     */
    protected ?RuleListInterface $parent = null;

    /**
     * Element constructor.
     * @param object|null $ast
     * @param RuleListInterface|null $parent
     * @throws Exception
     */
    public function __construct($ast = null, RuleListInterface $parent = null) {

        $this->ast = (object) ['type' => str_ireplace(Element::class.'\\', '', get_class($this))];

        if (!is_null($ast)) {

            foreach ($ast as $key => $value) {

                if ($value instanceof stdClass && is_callable([$this, 'create'.$key])) {

                    $value = $this->{'create'.$key}($value);
                }

                if (is_callable([$this, 'set'.$key])) {

                    $this->{'set'.$key}($value);
                }
                else if (is_callable([$this, $key])) {

                    $this->ast->{$key} = $value;
                }
            }
        }

        if (!is_null($parent)) {

            $parent->append($this);
        }
    }

    protected function createLocation ($location): SourceLocation {

        return SourceLocation::getInstance($location);
    }

    /**
     * create an instance from ast or another Element instance
     * @param Element|object $ast
     * @return mixed
     */
	public static function getInstance($ast) : Element {

        $type = '';

        if ($ast instanceof Element) {

            return clone $ast;
        }

        if (isset($ast->type)) {

            $type = $ast->type;
            unset($ast->parsingErrors);
        }

        if ($type === '') {

            throw new InvalidArgumentException('Invalid ast provided');
        }

        if (!empty($ast->children) && is_array($ast->children)) {

            $ast->children = array_map(__METHOD__, $ast->children);
        }

        $className = Element::class.'\\'.ucfirst($ast->type);
        
		return new $className($ast);
    }

    /**
     * @param string $query
     * @return array
     * @throws Parser\SyntaxError
     */
    public function query(string $query): array {

	    return (new Evaluator())->evaluate($query, $this);
    }
    /**
     * return the root element
     * @return Element
     */
    public function getRoot () {

        $element = $this;

        while ($parent = $element->parent) {

            $element = $parent;
        }

        return $element;
    }

    /**
     * return Value\Set|string
     * @return Value\Set|string
     */
    public function getValue () {

        if (isset($this->ast->value)) {

            return $this->ast->value;
        }

        return new Set;
    }

    /**
     * assign the value
     * @param Value\Set|string $value
     * @return $this
     */
    public function setValue ($value) {

        $this->ast->value = $value instanceof Set ? $value : Value::parse($value);
        return $this;
    }

    /**
     * get the parent node
     * @return RuleList|null
     */
    public function getParent () {

        return $this->parent;
    }

    /**
     * return the type
     * @return string
     */
    public function getType() {

        return $this->ast->type;
    }

    /**
     * Clone parents, children and the element itself. Useful when you want to render this element only and its parents.
     * @return Element
     */
    public function copy() {

        $parent = $this;
        $node = clone $this;

        while ($parent = $parent->parent) {

            $ast = clone $parent->ast;

            if (isset($ast->children)) {

                $ast->children = [];
            }

            $parentNode = Element::getInstance($ast);
            $parentNode->append($node);
            $node = $parentNode;
        }

        return $node;
    }

    public function setLocation(?SourceLocation $location) {

        $this->ast->location = $location;
        return $this;
    }

    public function getLocation(): ?SourceLocation {

        return $this->ast->location ?? null;
    }


    /**
     * merge css rules and declarations
     * @param array $options
     * @return Element
     */
    public function deduplicate(array $options = [])
    {

        if ((empty($options['allow_duplicate_rules']) || empty($options['allow_duplicate_declarations']) || $options['allow_duplicate_declarations'] !== true)) {

            switch ($this->ast->type) {

                case 'AtRule':

                    return !empty($ast->hasDeclarations) ? $this->deduplicateDeclarations($options) : $this->deduplicateRules($options);

                case 'Stylesheet':

                    return $this->deduplicateRules($options);

                case 'Rule':

                    return $this->deduplicateDeclarations($options);
            }
        }

        return $this;
    }

    /**
     * compute signature
     * @return string
     * @ignore
     */
    protected function computeSignature()
    {

        $signature = ['type:' . $this->ast->type];

        $name = $this->ast->name ?? null;

        if (isset($name)) {

            if (is_string($name)) {

                $name = Value::parse($name);
            }

            $signature[] = 'name:' . trim($name->render(['remove_comments' => true]));
        }

        $value = $this->ast->value ?? null;

        if (isset($value)) {

            if (is_string($value)) {

                $value = Value::parse($value);
            }

            $signature[] = 'value:' . trim($value->render(['remove_comments' => true]));
        }

        $selector = $this->ast->selector ?? null;

        if (isset($selector)) {

            $signature[] = 'selector:' . implode(',', array_map(function ($selector) {

                    if (is_string($selector)) {

                        $selector = Value::parse($selector);
                    }

                    return trim($selector->render(['remove_comments' => true]));
                }, $selector));
        }

        $vendor = $this->ast->vendor ?? null;

        if (isset($vendor)) {

            $signature[] = 'vendor:' . $vendor;
        }

        return implode(':', $signature);
    }

    /**
     * merge duplicate rules
     * @param array $options
     * @return object
     * @ignore
     */
    protected function deduplicateRules(array $options = [])
    {
        if (!is_null($this->ast->children ?? null)) {

            if (empty($options['allow_duplicate_rules'])) {

                $signature = '';
                $total = count($this->ast->children);
                $el = null;

                while ($total--) {

                    if ($total > 0) {

                        //   $index = $total;
                        $el = $this->ast->children[$total];

                        if ((string) $el->ast->type == 'Comment') {

                            continue;
                        }

                        $next = $this->ast->children[$total - 1];

                        while ($total > 1 && (string) $next->ast->type == 'Comment') {

                            $next = $this->ast->children[--$total - 1];
                        }

                        if ($signature === '') {

                            $signature = $el->computeSignature();
                        }

                        $nextSignature = $next->computeSignature();

                        while ($signature == $nextSignature) {

                            array_splice($this->ast->children, $total - 1, 1);

                            if ($el->ast->type != 'Declaration') {

                                $next->parent = null;
                                array_splice($el->ast->children, 0, 0, $next->ast->children);

                                if (isset($next->ast->location)) {

                                    if (!isset($el->ast->lcoation)) {

                                        $el->ast->lcoation = $next->ast->lcoation;
                                    }

                                    else {

                                        $el->ast->lcoation = $next->ast->lcoation;
                                    }
                                }
                            }

                            if ($total == 1) {

                                break;
                            }

                            $next = $this->ast->children[--$total - 1];

                            while ($total > 1 && $next->ast->type == 'Comment') {

                                $next = $this->ast->children[--$total - 1];
                            }

                            $nextSignature = $next->computeSignature();
                        }

                        $signature = $nextSignature;
                    }
                }
            }

            foreach ($this->ast->children as $key => $element) {

                if (is_callable([$element, 'deduplicate'])) {

                    $element->deduplicate($options);
                }
            }
        }

        return $this;
    }

    /**
     * merge duplicate declarations
     * @return Element
     * @ignore
     */
    protected function deduplicateDeclarations(array $options = [])
    {

        if (!empty($options['allow_duplicate_declarations']) && !empty($this->ast->children)) {

            $elements = $this->ast->children;

            $total = count($elements);

            $hash = [];
            $exceptions = is_array($options['allow_duplicate_declarations']) ? $options['allow_duplicate_declarations'] : !empty($options['allow_duplicate_declarations']);

            while ($total--) {

                $declaration = $this->ast->children[$total];

                if ($declaration->ast->type == 'Comment') {

                    continue;
                }

                $name = $declaration['name'];

                if ($name instanceof Value) {

                    $name = $name->render(['remove_comments' => true]);
                }

                if ($exceptions === true || isset($exceptions[$name])) {

                    continue;
                }

                if (isset($hash[$name])) {

                    $declaration->parent = null;
                    array_splice($this->ast->children, $total, 1);
                    continue;
                }

                $hash[$name] = 1;
            }
        }

        return $this;
    }

    /**
     * @return stdClass
     * @ignore
     */
    public function jsonSerialize () {

        $ast = clone $this->ast;

        if (isset($ast->value)) {

            $ast->value = trim($ast->value);
        }

        if (empty($ast->location)) {

            unset($ast->location);
        }

        return $ast;
    }

    /**
     * convert to string
     * @return string
     * @throws Exception
     */
    public function __toString()
    {
        try {

            return (new Renderer(['remove_empty_nodes' => false]))->render($this, null, true);
        }

        catch (Exception $ex) {

            error_log($ex->getTraceAsString());
        }

        return '';
    }

    /**
     * clone object
     * @ignore
     */
    public function __clone()
    {
        $this->ast = clone $this->ast;
        $this->parent = null;

        if (isset($this->ast->children)) {

            foreach ($this->ast->children as $key => $value) {

                $this->ast->children[$key] = clone $value;
                $this->ast->children[$key]->parent = $this;
            }
        }
    }
}