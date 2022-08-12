<?php

namespace TBela\CSS\Parser;

use Exception;

use TBela\CSS\Event\EventTrait;
use TBela\CSS\Interfaces\ValidatorInterface;
use TBela\CSS\Value;
class Lexer
{

    use ParserTrait;
    use EventTrait;

    // parent location index
    protected ?object $parentOffset = null;

    protected ?object $parentStylesheet = null;
    protected ?object $parentMediaRule = null;

    /**
     * css data
     * @var string
     * @ignore
     */
    protected string $css = '';
    protected string $src = '';
    protected ?object $context;
    protected bool $recover = false;

    /**
     * Parser constructor.
     * @param string $css
     * @param object|null $context
     */
    public function __construct(string $css = '', object $context = null)
    {

        $this->css = rtrim($css);
        $this->context = $context;
        $this->setParentOffset((object)[
            'line' => 1,
            'column' => 1,
            'index' => 0,
            'src' => ''
        ]);
    }

    /**
     * @param $css
     * @return Lexer
     */
    public function setContent($css): Lexer
    {

        $this->css = $css;
        $this->src = $this->parentOffset->src;
        return $this;
    }

    /**
     * @param object $context
     * @return Lexer
     */
    public function setContext(object $context): Lexer
    {

        $this->src = $context->src ?? $this->parentOffset->src;
        $this->context = $context;
        $this->parentStylesheet = $context;

        return $this;
    }

    /**
     * @return Lexer
     * @throws Exception
     */

    public function tokenize(): static
	{

        return $this->doTokenize($this->css, $this->src, $this->recover, $this->context, $this->parentStylesheet, $this->parentMediaRule);
    }

	/**
	 * @throws Exception
	 */
	public function doTokenize($css, $src, $recover, $context, $parentStylesheet, $parentMediaRule): static
	{

        $position = $context->location->end;

        $i = -1;
        $j = strlen($css) - 1;

        while ($i++ < $j) {

            while ($i < $j && static::is_whitespace($css[$i])) {

                $this->update($position, $css[$i]);
                $position->index += strlen($css[$i++]);
            }

            $comment = false;
            $token = null;

            if ($css[$i] == '/' && substr($css, $i + 1, 1) == '*') {

                $comment = static::match_comment($css, $i, $j);

                if ($comment === false) {

                    $comment = substr($css, $i);

                    $token = (object)[
                        'type' => 'InvalidComment',
                        'location' => (object)[
                            'start' => clone $position,
                            'end' => $this->update(clone $position, $comment)
                        ],
                        'value' => Value::escape($comment)
                    ];
                } else {

                    $token = (object)[
                        'type' => 'Comment',
                        'location' => (object)[
                            'start' => clone $position,
                            'end' => $this->update(clone $position, $comment)
                        ],
                        'value' => Value::escape($comment)
                    ];
                }
            } else if ($css[$i] == '<' && substr($css, $i, 4) == '<!--') {

                $k = $i + 3;
                $comment = '<!--';

                while ($k++ < $j) {

                    $comment .= $css[$k];

                    if ($css[$k] == '-' && substr($css, $k, 3) == '-->') {

                        $comment .= '->';

                        $token = (object)[
                            'type' => 'Comment',
                            'location' => (object)[
                                'start' => clone $position,
                                'end' => $this->update(clone $position, $comment)
                            ],
                            'value' => Value::escape($comment)
                        ];
                        break;
                    }
                }

                // unclosed comment
                if (is_null($token)) {

                    $token = (object)[
                        'type' => 'InvalidComment',

                        'location' => (object)[
                            'start' => clone $position,
                            'end' => $this->update(clone $position, $comment)
                        ],
                        'value' => Value::escape($comment)
                    ];
                }
            }

            if ($comment !== false) {

                $token->location->end->index += strlen($comment) - 1;
                $token->location->end->column = max($token->location->end->column - 1, 1);

                if ($src !== '') {

                    $token->src = $src;
                }

                $this->emit('enter', $token, $context, $parentStylesheet);

                $this->update($position, $comment);
                $position->index += strlen($comment);

                $i += strlen($comment) - 1;
                continue;
            }

            $name = static::substr($css, $i, $j, ['{', ';', '}']);

            if ($name === false) {

                $name = substr($css, $i);
            }

            if (trim($name) === '') {

                $this->update($position, $name);
                $position->index += strlen($name);
                continue;
            }

            $char = substr(trim($name), -1);

            if (!str_starts_with($name, '@') &&
                $char != '{') {

                // $char === ''
                if ('' === trim($name, "; \r\n\t")) {

                    $this->update($position, $name);
                    $position->index += strlen($name);
                    $i += strlen($name) - 1;
                    continue;
                }

                $declaration = ltrim(rtrim($name, " \r\n\t}"), " ;\r\n\t}");

                if ($declaration !== '') {

                    $parts = Value::split($declaration, ':', 2);

                    if (count($parts) < 2 || $context->type == 'Stylesheet') {

                        $token = (object)[
                            'type' => 'InvalidDeclaration',
                            'location' => (object)[
                                'start' => clone $position,
                                'end' => $this->update(clone $position, $declaration)
                            ],
                            'value' => rtrim($declaration, "\n\r\t ")
                        ];

                        $this->emit('enter', $token, $context, $parentStylesheet);

                    } else {

                        $end = clone $position;

                        $string = rtrim($name);
                        $this->update($end, $string);
                        $end->index += strlen($string);

                        $declaration = (object)array_merge(
                            [
                                'type' => 'Declaration',
                                'location' => (object)[
                                    'start' => clone $position,
                                    'end' => $end
                                ]
                            ],
                            $this->parseVendor(trim($parts[0])),
                            [
                                'value' => Value::escape(rtrim($parts[1], "\n\r\t "))
                            ]);

                        if ($src !== '') {

                            $declaration->src = $src;
                        }

                        if (in_array($declaration->name, ['src', 'background', 'background-image'])) {

                            $declaration->value = preg_replace_callback('#(^|[\s,/])url\(\s*(["\']?)([^)\\2]+)\\2\)#', function ($matches) {

                                $file = trim($matches[3]);
                                if (str_contains($file, 'data:')) {

                                    return $matches[0];
                                }

                                if (!preg_match('#^(/|((https?:)?//))#', $file)) {

                                    $file = Helper::absolutePath($file, dirname($this->src));
                                }

                                return $matches[1] . 'url(' . $file . ')';

                            }, $declaration->value);
                        }

                        $this->parseComments($declaration);

                        $data = $declaration->value;

                        if (is_array($data)) {

                            while (($end = end($data))) {

                                if (isset($end->value)) {

                                    if ($end->value == ';') {

                                        array_pop($data);
                                        continue;
                                    } else {

                                        if (empty($end->q)) {

                                            $end->value = rtrim($end->value, ';');
                                            $data[count($data) - 1] = $end;
                                        }

                                        break;
                                    }
                                }

                                break;
                            }

                            $isValidDeclaration = true;

                            foreach ($data as $key => $value) {

                                if ($isValidDeclaration && str_starts_with($value->type, 'invalid-')) {

                                    if ($value->type == 'invalid-css-function') {

                                        $c = count($value->arguments);

                                        while ($c--) {

                                            if ($value->arguments[$c]->type == 'invalid-comment' || str_ends_with($value->arguments[$c]->value ?? '', ';')) {

                                                if ($value->arguments[$c]->type == 'invalid-comment') {

                                                    array_splice($value->arguments, $c, 1);
                                                } else {

                                                    $isValidDeclaration = false;
                                                    break;
                                                }
                                            } else {

                                                break;
                                            }
                                        }
                                    }

                                    // invalid declaration
                                    if ($isValidDeclaration) {

                                        $className = Value::getClassName($value->type);
                                        $data[$key] = $className::doRecover($value);
                                    }
                                }
                            }

                            $declaration->value = $data;

                            if (!$isValidDeclaration) {

                                $declaration->type = 'InvalidDeclaration';
                            }
                        }

                        $this->emit('enter', $declaration, $context, $parentStylesheet);
                    }
                }

                $this->update($position, $name);
                $position->index += strlen($name);

                $i += strlen($name) - 1;
               continue;
            }

            if ($name[0] == '@' || $char == '{') {

                if ($name[0] == '@') {

                    // at-rule
                    if (preg_match('#^@([a-z-]+)([^{;}]*)#', trim($name, ";{ \n\r\t"), $matches)) {

                        $rule = (object)array_merge([
                            'type' => 'AtRule',
                            'location' => (object)[
                                'start' => clone $position,
                                'end' => clone $position
                            ],
                            'isLeaf' => true,
                            'hasDeclarations' => $char == '{',
                        ], $this->parseVendor($matches[1])
                        );

                        $rule->value = trim($matches[2]);

						if (str_contains($rule->value, '/*') || $rule->name == 'charset') {

							$rule->value = Value::parse($rule->value, null, true, '', '', $rule->name == 'charset');
						}

                        if ($rule->hasDeclarations) {

                            $rule->hasDeclarations = !in_array($rule->name, [
                                'media',
                                'document',
                                'container',
                                'keyframes',
                                'supports',
                                'font-feature-values'
                            ]);
                        }

                        if ($src !== '') {

                            $rule->src = $src;
                        }

                        if ($rule->name == 'import') {

                                preg_match('#^((url\((["\']?)([^\\3]+)\\3\))|((["\']?)([^\\6]+)\\6))(.*?$)#', is_array($rule->value) ? Value::renderTokens($rule->value) : $rule->value, $matches);

                                $media = trim($matches[8]);

                                if ($media == 'all') {

                                    $media = '';
                                }

                                $file = empty($matches[4]) ? $matches[7] : $matches[4];

                                $rule->value = trim("\"$file\" $media");
                                unset($rule->hasDeclarations);

                        } else if ($char == '{') {

                            unset($rule->isLeaf);
                        }

                        if (!empty($rule->isLeaf)) {

                            $rule->isLeaf = $char == ';' || $char === '' || !in_array($rule->name, [
                                    'page',
                                    'font-face',
                                    'viewport',
                                    'counter-style',
                                    'swash',
                                    'annotation',
                                    'ornaments',
                                    'stylistic',
                                    'styleset',
                                    'character-variant',
                                    'property',
                                    'color-profile'
                                ]);
                        }

                        if ($char == '{') {

                            unset($rule->isLeaf);
                        }

                    } else {

                        $body = static::_close($css, '}', '{', $i + strlen($name), $j);

                        if ($body === false) {

                            break;
                        } else {

                            $name .= $body;
                        }

                        $rule = (object)[

                            'type' => 'InvalidAtRule',
                            'name' => '',
                            'location' => (object)[
                                'start' => clone $position,
                                'end' => $this->update(clone $position, $name)
                            ],
                            'value' => Value::escape($name)
                        ];

                        $this->update($position, $name);
                        $position->index += strlen($name);

                        $rule->location->end = clone $position;
                        $rule->location->end->index = max(1, $rule->location->end->index - 1);

                        $rule->location->end->line = max(1, $rule->location->end->line - 1);
                        $rule->location->end->column = max($rule->location->end->column - 1, 1);

                        $this->emit('enter', $rule, $context, $parentStylesheet);

                        $i += strlen($name) - 1;
                        continue;
                    }

                    if (!empty($rule->isLeaf)) {

                        $this->update($position, $name);
                        $position->index += strlen($name);

                        $rule->location->end = clone $position;
                        $rule->location->end->index = max(1, $rule->location->end->index - 1);

                        $this->parseComments($rule);
                        $this->emit('enter', $rule, $context, $parentStylesheet);

                        $i += strlen($name) - 1;
                        continue;
                    }

                } else {

                    $selector = rtrim(substr($name, 0, -1));
                    $rule = (object)[

                        'type' => 'Rule',
                        'location' => (object)[

                            'start' => clone $position,
                            'end' => clone $position
                        ],
                        'selector' => Value::escape($selector)
                    ];

                    if ($src !== '') {

                        $rule->src = $src;
                    }
                }

                if ($rule->type == 'AtRule') {

                    if ($rule->name == 'nest') {

                        $rule->type = 'NestingAtRule';
                        $rule->selector = $rule->value;

                        unset($rule->name);
                        unset($rule->value);
                        unset($rule->hasDeclarations);
                    }
                }

                $this->update($rule->location->end, $name);
                $rule->location->end->index += strlen($name);

                $body = static::_close($css, '}', '{', $i + strlen($name), $j);

                $validRule = true;

                if (!str_ends_with($body, '}')) {

                    // if EOF then we must recover this rule #102
                    $recover = $context->type == 'Stylesheet' || $recover;

                   if ($recover) {

                        $body = substr($css, $i + strlen($name));
                    } else {

                        $validRule = false;
                        $rule->type = 'InvalidRule';
                        $rule->value = $body;
                        $this->emit('enter', $rule, $context, $parentStylesheet);
                    }
                }

                $ignoreRule = $rule->type == 'AtRule' && $rule->name == 'media' && (empty($rule->value) || $rule->value == 'all');

                if ($validRule) {

                    if (!$ignoreRule) {

                        $validRule = $this->getStatus('enter', $rule, $context, $parentStylesheet) == ValidatorInterface::VALID;
                    }
                }

                if ($validRule) {

                    $newParentMediaRule = $parentMediaRule;

                    if (isset($parentMediaRule) && $rule->type == 'NestingRule') {

                        $parentMediaRule->type = 'NestingMediaRule';
                    } else if ($rule->type == 'AtRule' && $rule->name == 'media' &&
                        isset($rule->value) && $rule->value != '' && $rule->value != 'all') {

                        // top level media rule
                        if (isset($newParentMediaRule)) {

                            $newParentMediaRule->type = 'NestingMediaRule';
                        }

                        if ($context->type == 'NestingRule' || $context->type == 'NestingAtRule') {

                            $rule->type = 'NestingMediaRule';
                        }

                        // change the current mediaRule
                        $newParentMediaRule = $rule;
                    }

                    $newParentStyleSheet = !in_array($rule->type, ['AtRule', 'NestingMediaRule']) ? $rule : $parentStylesheet;

                    if (($parentStylesheet->type ?? null) == 'Rule') {

                        $parentStylesheet->type = 'NestingRule';
                    }

                    $this->doTokenize($recover ? $body : substr($body, 0, -1), $src, $recover, $rule, $newParentStyleSheet, $newParentMediaRule);
                }

                $string = $name . $body;
                $this->update($position, $string);
                $position->index += strlen($name . $body);
                $i += strlen($string) - 1;

                $rule->location->end = clone $position;

                $rule->location->end->index--;
                $rule->location->end->column = max($rule->location->end->column - 1, 1);

                if (!$ignoreRule) {

                    $this->parseComments($rule);
                    $this->emit('exit', $rule, $context, $parentStylesheet);
                }
            }
        }

        $context->location->end->index = max(1, $context->location->end->index - 1);
        $context->location->end->column = max($context->location->end->column - 1, 1);

        return $this;
    }

    public function setParentOffset(object $parentOffset): static
    {

        $this->parentOffset = clone $parentOffset;
        $this->src = $parentOffset->src ?? '';
        return $this;
    }

    /**
     *
     * @return object
     * @ignore
     */
    public function createContext(): object
    {

        $context = (object)[
            'type' => 'Stylesheet',
            'location' => (object)[
                'start' => clone $this->parentOffset,
                'end' => clone $this->parentOffset
            ]
        ];

        if ($this->src !== '') {

            $context->src = $this->src;
        }

        return $context;
    }

	/**
	 * @throws Exception
	 */
	protected function parseComments(object $token): void
	{

        $property = property_exists($token, 'name') ? 'name' : (property_exists($token, 'selector') ? 'selector' : null);

        if ($property && !is_array($token->{$property})) {

            $comments = [];
            $formatted = Value::format($token->{$property}, $comments);

            if ($formatted !== false) {

                $token->{$property} = $formatted;

                if (!empty($comments)) {

                    $token->leadingcomments = $comments;
                }
            }

            else {

                $leading = [];
                $tokens = Value::parse($token->{$property}, null, true, '', '', true);

                $k = count($tokens);

                while ($k--) {

                    $t = $tokens[$k];

                    if ($t->type == 'Comment') {

                        $leading[] = $t->value;
                        array_splice($tokens, $k, 1);
                    }
                }

                $tokens = Value::reduce($tokens);
                $token->{$property} = $property == 'name' ? Value::renderTokens($tokens) : $tokens;

                if (!empty($leading)) {

                    $token->leadingcomments = $leading;
                }
            }
        }

        if (property_exists($token, 'value')) {

            if (is_array($token->value)) {

                return;
            }

            $comments = [];
            $formatted = Value::format($token->value, $comments);

            if ($formatted !== false) {

                $token->value = rtrim($formatted, "; \r\n\t");

                if (!empty($comments)) {

                    $token->trailingcomments = $comments;
                }
            }

            else {

                if (is_string($token->value)) {

                    $token->value = Value::parse($token->value, in_array($token->type, ['Declaration', 'Property']) ? $token->name : null, true, '', '', true);
                }

                $trailing = [];
                $k = count($token->value);

                while ($k--) {

                    if ($token->value[$k]->type == 'invalid-comment') {

                        array_splice($token->value, $k, 1);
                    } else if ($token->value[$k]->type == 'Comment') {

                        if (str_starts_with($token->value[$k]->value, '<!--')) {

                            array_splice($token->value, $k, 1);
                            continue;
                        }

                        $trailing[] = $token->value[$k]->value;
                        array_splice($token->value, $k, 1);
                    }
                }

                if (!empty($trailing)) {

                    $token->trailingcomments = array_reverse($trailing);
                }

                $token->value = Value::reduce($token->value, ['remove_defaults' => true]);
            }
        }
    }

    /**
     * @param string $str
     * @return array
     * @ignore
     */
    protected function parseVendor(string $str): array
    {

        if (preg_match('/^(-([a-zA-Z]+)-(\S+))/', trim($str), $match)) {

            return [

                'name' => $match[3],
                'vendor' => $match[2]
            ];
        }

        return ['name' => $str];
    }

	/**
	 * @param string $event
	 * @param object $rule
	 * @param $context
	 * @param $parentStylesheet
	 * @return int
	 */
    protected function getStatus(string $event, object $rule, $context, $parentStylesheet): int
    {
        foreach ($this->emit($event, $rule, $context, $parentStylesheet) as $status) {

            if (is_int($status)) {

                return $status;
            }
        }

        return ValidatorInterface::VALID;
    }
}