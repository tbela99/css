<?php

namespace TBela\CSS;

use axy\sourcemap\SourceMap;
use Exception;
use TBela\CSS\Exceptions\IOException;
use TBela\CSS\Interfaces\ParsableInterface;
use TBela\CSS\Interfaces\RenderableInterface;
use TBela\CSS\Interfaces\ElementInterface;
use TBela\CSS\Parser\Helper;
use TBela\CSS\Property\PropertyList;
use TBela\CSS\Value\Set;
use function is_string;

/**
 * Css node Renderer
 * @package TBela\CSS
 */
class Renderer
{

    protected array $options = [
        'glue' => "\n",
        'indent' => ' ',
        'css_level' => 4,
        'separator' => ' ',
        'charset' => false,
        'compress' => false,
        'sourcemap' => false,
        'convert_color' => false,
        'nesting_rules' => false,
        'remove_comments' => false,
        'preserve_license' => false,
        'compute_shorthand' => true,
        'remove_empty_nodes' => false,
        'allow_duplicate_declarations' => false
    ];

    /**
     * @var string
     * @ignore
     */
    protected $outFile = '';

    protected array $indents = [];

    /**
     * Identity constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {

        $this->setOptions($options);
    }

    /**
     * render an ElementInterface or a Property
     * @param RenderableInterface $element the element to render
     * @param null|int $level indention level
     * @param bool $parent render parent
     * @return string
     * @throws Exception
     */
    public function render(RenderableInterface $element, ?int $level = null, $parent = false)
    {

        if ($parent && ($element instanceof ElementInterface) && !is_null($element['parent'])) {

            $element = $element->copy()->getRoot();
        }

        return $this->renderAst($element->getAst(), $level);
    }

    /**
     * @param \stdClass|ParsableInterface $ast
     * @param int|null $level
     * @return string
     * @throws Exception
     */
    public function renderAst($ast, ?int $level = null)
    {

        $this->outFile = '';

        if ($ast instanceof ParsableInterface) {

            $ast = $ast->getAst();
        }

        switch ($ast->type) {

            case 'Rule':
            case 'AtRule':
            case 'Comment':
            case 'Property':
            case 'Stylesheet':
            case 'NestingRule':
            case 'Declaration':
            case 'NestingAtRule':
            case 'NestingMediaRule':

                return $this->{'render' . $ast->type}($ast, $level);

            default:

                throw new Exception('Type not supported ' . $ast->type);
        }

        return '';
    }

    /**
     * @param ParsableInterface|\stdClass $ast
     * @param string $file
     * @return Renderer
     * @throws IOException
     * @throws Exception
     */
    public function save($ast, $file)
    {

        if ($ast instanceof ParsableInterface) {

            $ast = $ast->getAst();
        }

        $data = (object)[
            'sourcemap' => new SourceMap(),
            'position' => (object)[
                'line' => 0,
                'column' => 0
            ]
        ];

        $this->outFile = Helper::absolutePath($file, Helper::getCurrentDirectory());

        $result = $this->walk($ast, $data);
        $map = $file . '.map';

        $json = $data->sourcemap->getData();
//        $json['mappings'] = preg_replace('#;+#', ';', $json['mappings']);

        if (file_put_contents($map, json_encode($json)) === false) {

            throw new IOException("cannot write map into $map", 500);
        }

        if (file_put_contents($file, $result->css . "\n/*# sourceMappingURL=" . Helper::relativePath($map, dirname($file)) . " */") === false) {

            throw new IOException("cannot write output into $file", 500);
        }

        return $this;
    }

    /**
     * @param \stdClass $ast
     * @param \stdClass $data \
     * @param int|null $level
     * @return object|null
     * @throws Exception
     * @ignore
     */
    protected function walk($ast, $data, ?int $level = null)
    {

        $result = [

            'css' => '',
            'type' => $ast->type,
        ];

        // rule
        switch ($ast->type) {

            case 'Rule':
            case 'AtRule':
            case 'Stylesheet':
            case 'NestingRule':

                if ($ast->type == 'AtRule' && $ast->name == 'media' && (($ast->value ?? '') === '' || $ast->value == 'all')) {
                    // render children
                    $css = '';
                    $d = clone $data;
                    $d->position = clone $d->position;

                    foreach ($ast->children as $c) {

                        $r = $this->walk($c, $d, $level);

                        if (is_null($r)) {

                            continue;
                        }

                        $p = $r->css . $this->options['glue'];
                        $css .= $p;

                        $this->update($d->position, $p);
                    }

                    $result['css'] = $css;
                    break;
                }

                $type = $ast->type;
                $map = [];

                if ($type == 'Stylesheet') {

                    $ast->css = '';

                    foreach ($ast->children as $c) {

                        $d = clone $data;
                        $d->position = clone $d->position;

                        $child = $this->walk($c, $d);

                        if (is_null($child) || $child->css === '') {

                            continue;
                        }

                        $css = $child->css . $this->options['glue'];
                        $this->update($data->position, $css);

                        $ast->css .= $css;
                    }

                    $result['css'] = rtrim($ast->css);
                } else {

                    if (in_array($type, ['Rule', 'NestingRule']) || ($type == 'AtRule' && isset($ast->children))) {

                        if (!isset($this->indents[$level])) {

                            $this->indents[$level] = str_repeat($this->options['indent'], $level);
                        }

                        $children = $ast->children ?? [];

                        if (empty($children) && $this->options['remove_empty_nodes']) {

                            return null;
                        }

                        if ($this->options['compute_shorthand'] || !$this->options['allow_duplicate_declarations']) {

                            $children = [];
                            $properties = new PropertyList(null, $this->options);

                            foreach (($ast->children ?? []) as $child) {

                                if (isset($child->name)) {

                                    $map[$child->name] = $child;
                                } else {

                                    $map[] = $child;
                                }

                                if (empty($children) && ($child->type == 'Declaration' || $child->type == 'Comment')) {

                                    $properties->set($child->name ?? null, $child->value, $child->type, $child->leadingcomments ?? null, $child->trailingcomments ?? null, $child->src ?? null, $child->vendor ?? null);
                                } else {

                                    $children[] = $child;
                                }
                            }

                            if (!$properties->isEmpty()) {

                                array_splice($children, 0, 0, iterator_to_array($properties->getProperties()));
                            }

                            if (empty($children) && $this->options['remove_empty_nodes']) {

                                return null;
                            }
                        }
                    }

                    if (!is_null($level)) {

                        $this->update($data->position, $this->indents[$level]);
                    }

                    $this->addPosition($data, $ast);

                    if ($type == 'Rule') {

                        $result['css'] .= $this->renderSelector($ast, $level) . $this->options['indent'] . '{' .
                            $this->options['glue'];

                        $this->update($data->position, substr($result['css'], $level));
                    } else {

                        $media = $this->renderAtRuleMedia($ast, $level);

                        if ($media === '') {

                            return null;
                        }

                        $result['css'] = $media;

                        if (!empty($ast->isLeaf)) {

                            $this->update($data->position, substr($result['css'], $level));
                            break;
                        }

                        $result['css'] .= $this->options['indent'] . '{' . $this->options['glue'];
                        $this->update($data->position, substr($result['css'], $level));
                    }

                    $res = [];

                    foreach ($children as $child) {

                        $declaration = $this->{'render' . $child->type}($child, $level + 1);

                        if ($declaration === '') {

                            continue;
                        }

                        if (isset($res[$declaration])) {

                            unset($res[$declaration]);
                        }

                        $res[$declaration] = [$declaration, $map[$child->name ?? null] ?? $child];
                    }

                    $css = '';
                    $d = clone $data;
                    $d->position = clone $d->position;
                    $glue = ';' . $this->options['glue'];

                    foreach ($res as $r) {

                        $this->update($d->position, $this->indents[$level + 1]);

                        if (!is_null($r[1]->position ?? ($r[1]->location->start ?? null)) && in_array($r[1]->type, ['AtRule', 'Rule'])) {

                            $this->addPosition($d, $r[1]);
                        }

                        $text = $r[0] . ($r[1]->type == 'Comment' ? $this->options['glue'] : $glue);
                        $this->update($d->position, substr($text, $level + 1));
                        $css .= $text;
                    }

                    $result['css'] .= rtrim($css, $glue) . $this->options['glue'] . $this->indents[$level] . '}';
                }

                break;

            case 'Comment':
//            case 'Property':
//            case 'Declaration':

                $css = $this->{'render' . $ast->type}($ast, $level);

                if ($css === '') {

                    return null;
                }

                if (!isset($this->indents[$level])) {

                    $this->indents[$level] = str_repeat($this->options['indent'], $level);
                }

                $c = clone $data;
                $c->position = clone $c->position;
                $this->update($c->position, $this->indents[$level]);
//                $this->addPosition($data, substr($css, $level));

                $result['css'] = $css;
                break;

            default:

                throw new Exception('Type not supported ' . $ast->type);
        }

        return (object)$result;
    }

    /**
     * @param \stdClass $position
     * @param string $string
     * @return \stdClass
     * @ignore
     */
    protected function update($position, string $string)
    {

        $j = strlen($string);

        for ($i = 0; $i < $j; $i++) {

            if ($string[$i] == "\n") {

                $position->line++;
                $position->column = 0;
            } else {

                $position->column++;
            }
        }

        return $position;
    }

    /**
     * @param \stdClass $data
     * @param \stdClass $ast
     * @ignore
     */
    protected function addPosition($data, $ast)
    {

        if (empty($ast->src)) {

            return;
        }

        $position = $ast->location->start ?? ($ast->position ?? null);

        if (is_null($position)) {

            return;
        }

        $data->sourcemap->addPosition([
            'generated' => [
                'line' => $data->position->line,
                'column' => $data->position->column,
            ],
            'source' => [
                'fileName' => $ast->src,
                'line' => $position->line - 1,
                'column' => $position->column - 1,
            ],
        ]);
    }

    /**
     * @param \stdClass $ast
     * @param int|null $level
     * @return string
     * @ignore
     */
    protected function renderStylesheet($ast, $level)
    {

        return $this->renderCollection($ast, $level);
    }

    /**
     * @param \stdClass $ast
     * @param int|null $level
     * @return string
     * @ignore
     */
    protected function renderComment($ast, ?int $level)
    {

        if ($this->options['remove_comments']) {

            if (!$this->options['preserve_license'] || substr($ast->value, 0, 3) != '/*!') {

                return '';
            }
        }

        settype($level, 'int');

        if (!isset($this->indents[$level])) {

            $this->indents[$level] = str_repeat($this->options['indent'], $level);
        }

        return $this->indents[$level] . $ast->value;
    }

    /**
     * render a rule
     * @param \stdClass $ast
     * @param int|null $level
     * @return string
     * @throws Exception
     * @ignore
     */
    protected function renderSelector($ast, $level)
    {

        $selector = $ast->selector;

        if (!isset($selector)) {

            throw new Exception('The selector cannot be empty');
        }

        settype($level, 'int');

        if (!isset($this->indents[$level])) {

            $this->indents[$level] = str_repeat($this->options['indent'], $level);
        }

        $indent = $this->indents[$level];

        $result = $indent;
        $join = ',' . $this->options['glue'] . $indent;

        if (is_string($selector) && preg_match('#[,\s"\']|(\b0)#', $selector)) {

            $selector = array_map(function (Set $set) {

                return $set->render($this->options);
            }, Value::parse($selector)->split(','));
        }

        if (is_array($selector)) {

            foreach ($selector as $sel) {

                $result .= $sel . $join;
            }
        } else {

            $result .= $selector;
        }

        $result = rtrim($result, $join);

        if (!$this->options['remove_comments'] && !empty($ast->leadingcomments)) {

            $comments = $ast->leadingcomments;

            if (!empty($comments)) {

                $join = $this->options['compress'] ? '' : ' ';

                foreach ($comments as $comment) {

                    $result .= $join . $comment;
                }
            }
        }

        return $result;
    }

    /**
     * render a rule
     * @param \stdClass $ast
     * @param int|null $level
     * @return string
     * @throws Exception
     * @ignore
     */
    protected function renderNestingAtRule($ast, $level, $parentStylesheet = null, $parentMediaRule = null)
    {

        return $this->renderNestingRule($ast, $level, $parentStylesheet, $parentMediaRule);
    }

    /**
     * render a rule
     * @param \stdClass $ast
     * @param int|null $level
     * @return string
     * @throws Exception
     * @ignore
     */
    protected function renderNestingRule($ast, $level, $parentStylesheet = null, $parentMediaRule = null)
    {

        if ($this->options['nesting_rules']) {

            $output = $this->renderRule($ast, $level);

            if($ast->type == 'NestingRule') {

                return  $output;
            }

            return $output === '' ? '' : str_repeat($this->options['indent'], (int) $level).'@nest '.ltrim($output);
        }

        $declarations = [];
        $rules = [];

        $cloned = new \stdClass;

        foreach ($ast as $key => $value) {

            if ($key == 'children') {

                continue;
            }

            $cloned->{$key} = $value;
        }

        if (is_string($cloned->selector)) {

            $cloned->selector = array_map('trim', Value::split($ast->selector, ','));
        }

        if (isset($parentStylesheet)) {

//            if (!isset($parentStylesheet->selector)) {
//
//                echo new Exception();
//            }

            $parentSelector = is_string($parentStylesheet->selector) ? array_map('trim', Value::split($parentStylesheet->selector, ',')) : $parentStylesheet->selector;
            $parentSelector = count($parentSelector) == 1 ? $parentSelector[0] : ':is(' . implode(',' . $this->options['indent'], $parentSelector) . ')';
            $cloned->selector = array_map(function ($selector) use($parentSelector) {

                return str_replace('&', $parentSelector, $selector);

            }, $cloned->selector);
        }

        $output = '';

        foreach ($ast->children as $key => $child) {

            if ($child->type != 'Declaration' && $child->type != 'Comment') {

                if (in_array($child->type, ['Rule', 'NestingRule', 'NestingAtRule']) &&
                    ((is_string($child->selector) && $child->selector == '&') ||
                        (is_array($child->selector) && $child->selector[0] == '&'))
                ) {

                    foreach ($child->children as $k => $c) {

                        if ($c->type != 'Declaration' && $c->type != 'Comment') {

                            $rules = array_slice($child->children, $k);
                            break;
                        }

                        $declarations[] = $c;
                    }

                    continue;
                }

                array_splice($rules, count($rules), 0, array_slice($ast->children, $key));
                break;
            }

            $declarations[] = $child;
        }

        if (!empty($declarations)) {

            $cloned->children = $declarations;
            $output = $this->renderRule($cloned, $level) . $this->options['glue'];
        }

        if (!empty($rules)) {

            $selectors = count($cloned->selector) == 1 ? $cloned->selector[0] : ':is(' . implode(',' . $this->options['indent'], $cloned->selector) . ')';

            foreach ($rules as $child) {

                if (in_array($child->type, ['Rule', 'NestingRule', 'NestingAtRule'])) {

                    $child = clone $child;
                    $child->selector = array_map(function ($selector) use ($selectors) {

                        return str_replace('&', $selectors, $selector);
                    }, is_string($child->selector) ? array_map('trim', Value::split($child->selector, ',')) : $child->selector);
                }

                $r = '';

                if (in_array($child->type, ['AtRule', 'NestingMediaRule']) && $child->name == 'media') {

                    if (isset($child->children)) {

                        $child = clone $child;

                        $cloned->children = $child->children;
                        $child->children = [$cloned];

                        $r = $this->{'render'.$child->type}($child, $level, $parentStylesheet);
                    }
                }

                else {

                    $r = $this->{'render' . $child->type}($child, $level, $cloned);
                }


                if ($r !== '') {

                    $output .= $r . $this->options['glue'];
                }
            }
        }

        return rtrim($output, $this->options['glue']);
    }

    /**
     * render a rule
     * @param \stdClass $ast
     * @param int|null $level
     * @return string
     * @throws Exception
     * @ignore
     */
    protected function renderRule($ast, $level)
    {

        settype($level, 'int');
        $result = $this->renderSelector($ast, $level);
        $output = $this->renderCollection($ast, $level + 1, $ast);

        if ($output === '' && $this->options['remove_empty_nodes']) {

            return '';
        }

        return $result . $this->options['indent'] . '{' .
            $this->options['glue'] .
            $output . $this->options['glue'] .
            $this->indents[$level] .
            '}';
    }

    /**
     * render a rule
     * @param \stdClass $ast
     * @param int|null $level
     * @return string
     * @throws Exception
     * @ignore
     */
    protected function renderAtRuleMedia($ast, $level)
    {

        if ($ast->name == 'charset' && !$this->options['charset']) {

            return '';
        }

        $output = '@' . $this->renderName($ast);
        $value = isset($ast->value) && $ast->value != 'all' ? $this->renderValue($ast) : '';

        if ($value !== '') {

            if ($this->options['compress'] && $value[0] == '(') {

                $output .= $value;
            } else {

                $output .= rtrim($this->options['separator'] . $value);
            }
        }

        settype($level, 'int');

        if (!isset($this->indents[$level])) {

            $this->indents[$level] = str_repeat($this->options['indent'], $level);
        }

        $indent = $this->indents[$level];

        if (!empty($ast->isLeaf)) {

            return $indent . $output . ';';
        }

        return $indent . $output;
    }

    /**
     * render at-rule
     * @param \stdClass $ast
     * @param ?int $level
     * @return string
     * @ignore
     */
    protected function renderAtRule($ast, $level, $parentStylesheet = null)
    {

        settype($level, 'int');
        $media = $this->renderAtRuleMedia($ast, $level);

        if ($media === '' || !empty($ast->isLeaf)) {

            return $media;
        }

        if($ast->name == 'media' && (!isset($ast->value) || $ast->value == 'all')) {

            return $this->renderCollection($ast, $level, $parentStylesheet, $ast->name == 'media' ? $ast : null);
        }

        $elements = $this->renderCollection($ast, $level + 1, $parentStylesheet, $ast->name == 'media' ? $ast : null);

        if ($elements === '' && !empty($this->options['remove_empty_nodes'])) {

            return '';
        }

        return $media . $this->options['indent'] . '{' . $this->options['glue'] .  $elements. $this->options['glue'] . $this->indents[$level] . '}';
    }

    /**
     * render a rule
     * @param \stdClass $ast
     * @param int|null $level
     * @return string
     * @throws Exception
     * @ignore
     */
    protected function renderNestingMediaRule($ast, $level, $parentStylesheet = null, $parentMediaRule = null)
    {

        if ($this->options['nesting_rules']) {

            return $this->renderAtRule($ast, $level, $parentStylesheet);
        }

        $declarations = [];
        $rules = [];

        $cloned = new \stdClass;

        foreach ($ast as $key => $value) {

            if ($key == 'children') {

                continue;
            }

            $cloned->{$key} = $value;
        }

        $cloned->children = [];

        $output = '';
        $clonedParent = isset($parentStylesheet) ? clone $parentStylesheet : null;

        foreach ($ast->children as $key => $child) {

            if ($child->type != 'Declaration' && $child->type != 'Comment') {

                if (in_array($child->type, ['Rule', 'NestingRule', 'NestingAtRule', 'NestingMediaRule', 'AtRule']) &&
                    ((is_string($child->selector) && $child->selector == '&') ||
                        (is_array($child->selector) && $child->selector[0] == '&'))
                ) {

                    foreach ($child->children as $k => $c) {

                        if ($c->type != 'Declaration' && $c->type != 'Comment') {

                            $rules = array_slice($child->children, $k);
                            break;
                        }

                        $declarations[] = $c;
                    }

                    continue;
                }

                array_splice($rules, count($rules), 0, array_slice($ast->children, $key));
                break;
            }

            $declarations[] = $child;
        }

        if (!empty($declarations)) {


//            if () {

            if ($clonedParent) {

                $clonedParent->children = $declarations;
                $cloned->children = [$clonedParent];
            }

            else {

                $cloned->children = $declarations;
            }

                $output = $this->renderAtRule($cloned, $level) . $this->options['glue'];
//            }
        }

        if (!empty($rules)) {

//            $selectors = count($cloned->selector) == 1 ? $cloned->selector[0] : ':is(' . implode(',' . $this->options['indent'], $cloned->selector) . ')';

            $children = [];

            foreach ($rules as $child) {

                if (in_array($child->type, ['AtRule', 'NestingMediaRule'])) {

                    if (!empty($children)) {

                        if ($clonedParent) {

                            $clonedParent->children = $children;
                            $cloned->children = [$clonedParent];
                        }

                        else {

                            $cloned->children = $children;
                        }

                        $children = [];

                        $r = $this->renderAtRule($cloned, $level);

                        if ($r !== '' || !$this->options['remove_empty_nodes']) {

                            $output .= $r.$this->options['glue'];
                        }
                    }

                    $c = clone $child;
                    $values = [];

                    if (isset($cloned->value)) {

                        $values[] = $cloned->value;
                    }

                    if (isset($c->value)) {

                        $values[] = $c->value;
                    }

                    $c->value = implode(' and ', $values);

                    if (isset($clonedParent)) {

                        $clonedParent->children = $c->children;
                        $c->children = [$clonedParent];
                    }

                    $r = $this->renderAtRule($c, $level, $parentStylesheet);

                    if ($r !== '' || !$this->options['remove_empty_nodes']) {

                        $output .= $r.$this->options['glue'];
                    }

                    continue;
                }

                $children[] = $child;
            }

            if (!empty($children)) {

                if (isset($clonedParent)) {

                    $clonedParent->children = $children;
                    $cloned->children = [$clonedParent];
                }

                else {

                    $cloned->children = $children;
                }

                $r = $this->renderAtRule($cloned, $level, $parentStylesheet);

                if ($r !== '' || !$this->options['remove_empty_nodes']) {

                    $output .= $r.$this->options['glue'];
                }
            }
        }

        return rtrim($output, $this->options['glue']);
    }

    /**
     * @param \stdClass $ast
     * @param int|null $level
     * @return string
     * @ignore
     */
    protected function renderDeclaration($ast, ?int $level)
    {

        return $this->renderProperty($ast, $level);
    }

    /**
     * render a property
     * @param \stdClass $ast
     * @param int|null $level
     * @return string
     * @ignore
     */

    protected function renderProperty($ast, ?int $level)
    {
        if ($ast->type == 'Comment') {

            return empty($this->options['compress']) ? '' : $ast->value;
        }

        $name = $this->renderName($ast);
        $value = $ast->value;

        $options = [
            'compress' => $this->options['compress'],
            'css_level' => $this->options['css_level'],
            'convert_color' => $this->options['convert_color'] === true ? 'hex' : $this->options['convert_color']
        ];
//
//        if (is_string($value)) {
//
//            if (!isset($this->indents[$level])) {
//
//                $this->indents[$level] = str_repeat($this->options['indent'], (int)$level);
//            }
//
//            return $this->indents[$level] . $name . ':' . $this->options['indent'] . $value;
//        }

        if (is_string($value)) {

            $value = Value::parse($value, $name);
        }

        if (empty($this->options['compress'])) {

            $value = implode(', ', array_map(function (Set $value) use ($options) {

                return $value->render($options);

            }, $value->split(',')));
        } else {

            $value = $value->render($options);
        }

        if ($value == 'none') {

            if (in_array($name, ['border', 'border-top', 'border-right', 'border-left', 'border-bottom', 'outline'])) {

                $value = 0;
            }
        } else if (in_array($name, ['background', 'background-image', 'src'])) {

            $value = preg_replace_callback('#(^|\s)url\(\s*(["\']?)([^)\\2]+)\\2\)#', function ($matches) {

                if (strpos($matches[3], 'data:') !== false) {

                    return $matches[0];
                }

                return $matches[1] . 'url(' . Helper::relativePath($matches[3], $this->outFile === '' ? Helper::getCurrentDirectory() : dirname($this->outFile)) . ')';
            }, $value);
        }

        if (!$this->options['remove_comments'] && !empty($ast->trailingcomments)) {

            $comments = $ast->trailingcomments;

            if (!empty($comments)) {

                foreach ($comments as $comment) {

                    $value .= ' ' . $comment;
                }
            }
        }

        settype($level, 'int');

        if (!isset($this->indents[$level])) {

            $this->indents[$level] = str_repeat($this->options['indent'], $level);
        }

        return $this->indents[$level] . trim($name) . ':' . $this->options['indent'] . trim($value);
    }

    /**
     * render a name
     * @param \stdClass $ast
     * @return string
     * @ignore
     */
    protected function renderName($ast)
    {

        $result = $ast->name;

        if (!empty($ast->vendor)) {

            $result = '-' . $ast->vendor . '-' . $result;
        }

        if (!$this->options['remove_comments'] && !empty($ast->leadingcomments)) {

            $comments = $ast->leadingcomments;

            if (!empty($comments)) {

                foreach ($comments as $comment) {

                    $result .= ' ' . $comment;
                }
            }
        }

        return $result;
    }

    /**
     * render a value
     * @param \stdClass $ast
     * @return string
     * @ignore
     */
    protected function renderValue($ast)
    {
        $result = $ast->value;

        if (!($result instanceof Set)) {

            $result = Value::parse($result, $ast->name);
            $ast->value = $result;
        }

        $result = $result->render($this->options);

        if (!$this->options['remove_comments'] && !empty($ast->trailingcomments)) {

            $trailingComments = $ast->trailingcomments;
        }

        if (!empty($trailingComments)) {

            $glue = $this->options['compress'] ? '' : ' ';

            foreach ($trailingComments as $comment) {

                $result .= $glue . $comment;
            }
        }

        return $result;
    }

    /**
     * render a list
     * @param \stdClass $ast
     * @param int|null $level
     * @return string
     * @ignore
     */
    protected function renderCollection($ast, ?int $level, $parentStylesheet = null, $parentMediaRule = null)
    {

        $type = $ast->type;
        $glue = ($type == 'Rule' || ($type == 'AtRule' && !empty($ast->hasDeclarations))) ? ';' : '';
        $count = 0;

        if (($this->options['compute_shorthand'] || !$this->options['allow_duplicate_declarations']) && $glue == ';') {

            $children = [];
            $properties = new PropertyList(null, $this->options);

            foreach ($ast->children ?? [] as $child) {

                if (!empty($children)) {

                    $children[] = $child;
                } else if ($child->type == 'Declaration' || $child->type == 'Comment') {

                    $properties->set($child->name ?? null, $child->value, $child->type, $child->leadingcomments ?? null, $child->trailingcomments ?? null, null, $child->vendor ?? null);
                } else {

                    $children[] = $child;
                }
            }

            if (!$properties->isEmpty()) {

                array_splice($children, 0, 0, iterator_to_array($properties->getProperties()));
            }

        } else {

            $children = $ast->children ?? [];
        }

        $result = [];
        settype($level, 'int');

        foreach ($children as $el) {

            if (!($el instanceof \stdClass)) {

                $el = $el->getAst();
            }

            $output = $this->{'render' . $el->type}($el, $level, $this->options['nesting_rules'] || in_array($ast->type, ['Stylesheet', 'AtRule', 'NestingMediaRule']) ? $parentStylesheet : $ast, $parentMediaRule);

            if (trim($output) === '') {

                continue;

            } else if ($el->type != 'Comment') {

                if ($count == 0) {

                    $count++;
                }
            }

            $output .= in_array($el->type, ['Declaration', 'Property']) ? ';' : '';

            if (isset($result[$output])) {

                unset($result[$output]);
            }

            $result[$output] = $output;
        }

        if ($this->options['remove_empty_nodes'] && $count == 0) {

            return '';
        }

        $join = $this->options['glue'];
        $output = '';

        foreach ($result as $res) {

            $output .= $res . $join;
        }

        return rtrim($output, ';' . $this->options['glue']);
    }

    /**
     * Set output formatting
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {

        if (!empty($options['compress'])) {

            $this->options['glue'] = '';
            $this->options['indent'] = '';

            if (!$this->options['convert_color']) {

                $this->options['convert_color'] = 'hex';
            }

            $this->options['charset'] = false;
            $this->options['remove_comments'] = true;
            $this->options['remove_empty_nodes'] = true;
        } else {

            $this->options['glue'] = "\n";
            $this->options['indent'] = ' ';
        }

        foreach ($options as $key => $value) {

            if (array_key_exists($key, $this->options)) {

                $this->options[$key] = $value;
            }
        }

        if ($this->options['convert_color'] === true) {

            $this->options['convert_color'] = 'hex';
        }

        if (isset($options['allow_duplicate_declarations'])) {

            $this->options['allow_duplicate_declarations'] = is_string($options['allow_duplicate_declarations']) ? [$options['allow_duplicate_declarations']] : $options['allow_duplicate_declarations'];
        }

        $this->indents = [];

        return $this;
    }

    /**
     * return the options
     * @param string|null $name
     * @param mixed $default return value
     * @return array
     */
    public function getOptions($name = null, $default = null)
    {

        if (is_null($name)) {

            return $this->options;
        }

        return $this->options[$name] ?? $default;
    }
}
