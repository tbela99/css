<?php

namespace TBela\CSS\Element\Declaration;

use InvalidArgumentException;
use TBela\CSS\Value;

/**
 * Compute shorthand properties. Used internally by PropertyList to compute shorthand for properties of different types
 * @package TBela\CSS\Element\Declaration
 */
class PropertyMap
{

	use PropertyTrait;

	/**
	 * @var array
	 * @ignore
	 */
	protected array $config;

	/**
	 * @var Property[]
	 * @ignore
	 */
	protected array $properties = [];

	/**
	 * @var array
	 * @ignore
	 */
	protected array $property_type = [];
	/**
	 * @var string
	 * @ignore
	 */
	protected string $shorthand;

	/**
	 * PropertySet constructor.
	 * @param string $shorthand
	 * @param array $config
	 */
	public function __construct(string $shorthand, array $config)
	{

		$this->shorthand = $shorthand;

		$config['required'] = [];

		if (isset($config['properties'])) {

			foreach ($config['properties'] as $property) {

				$config[$property] = Config::getPath('map.' . $property);

				unset($config[$property]['shorthand']);

				$this->property_type[$property] = $config[$property];

				if (empty($config[$property]['optional'])) {
					$config['required'][] = $property;
				}
			}
		}

		$this->config = $config;
	}

	// @todo vendor prefix support
	public function has($property): bool
	{

		return isset($this->properties[$property]);
	}

	// @todo vendor prefix support
	public function remove($property): static
	{

		unset($this->properties[$property]);

		return $this;
	}

	/**
	 * set property value
	 * @param string $name
	 * @param string|object[] $value
	 * @param array|null $leadingcomments
	 * @param array|null $trailingcomments
	 * @param string|null $src
	 * @return PropertyMap
	 */
	public function set(string $name, array|string $value, ?array $leadingcomments = null, ?array $trailingcomments = null, string $src = null): static
	{

		// is valid property
		if (($this->shorthand != $name) && !in_array($name, $this->config['properties'])) {

			throw new InvalidArgumentException('Invalid property ' . $name, 400);
		}

		$optionalShorthand = !empty($this->config['settings']['optional-shorthand']);

		// if all properties are present, is it safe to use the shorthand?
		// font -> no?
		// background -> no?
		// text-decoration -> yes?
		if ($optionalShorthand) {

			foreach ($this->config['required'] as $property) {

				if (!isset($this->properties[$property]) && $name != $property) {

					$optionalShorthand = false;
					break;
				}
			}
		}

		if ($optionalShorthand && !isset($this->properties[$this->shorthand])) {

			$this->properties = array_merge([$this->shorthand => new Property($this->shorthand)], $this->properties);
		}

		// the type matches the shorthand - example system font
		if ($name == $this->shorthand || !isset($this->properties[$this->shorthand])) {

			if ($name == $this->shorthand) {

				$this->properties = [];
			}

			if (!isset($this->properties[$name])) {

				$this->properties[$name] = new Property($name);
			}

			if ($src !== null) {

				$this->properties[$name]->setSrc($src);
			}

			$this->properties[$name]->setValue($value)->
			setLeadingComments($leadingcomments)->
			setTrailingComments($trailingcomments);

			return $this;
		}

		$this->properties[$name] = (new Property($name))->setValue($value)->
		setLeadingComments($leadingcomments)->
		setTrailingComments($trailingcomments);

		if ($src !== null) {

			$this->properties[$name]->setSrc($src);
		}

		$separator = Config::getPath('map.' . $this->shorthand . '.separator');

		$all = [];

		if (is_null($separator)) {

			$all = [$this->properties[$this->shorthand]->getValue()];
		} else {

			// shorthand is set
			if (!is_array($value)) {

				$value = Value::parse($value, $name, true, '', '', true);
			}

			$index = 0;
			foreach ($this->properties[$this->shorthand]->getValue() as $v) {

				if ($v->type == 'separator' && $v->value == $separator) {

					$index++;
					continue;
				}

				$all[$index][] = $v;
			}

			$index = 0;
			foreach ($value as $v) {

				if ($v->type == 'separator' && $v->value == $separator) {

					$index++;
					continue;
				}

				$all[$index][] = $v;
			}
		}

		$props = [];
		foreach ($this->properties as $key => $prop) {

			if ($key == $this->shorthand) {

				continue;
			}

			$sep = Config::getPath('properties.' . $key . '.separator');
			$v = [];

			if (is_null($sep)) {

				$v = [$prop->getValue()];
			} else {

				$index = 0;

				foreach ($prop->getValue() as $val) {

					if ($val->type == 'separator' && $val->value == $separator) {

						$index++;
						continue;
					}

					$v[$index][] = $val;
				}
			}

			if (count($v) != count($all)) {

				return $this;
			}

			$props[$key] = $v;
		}

		$properties = $this->property_type;
		$results = [];

		foreach ($all as $index => $values) {

			$data = [];

			if (is_null($values)) {

				$values = [];
			}

			foreach ($values as $val) {

				if (in_array($val->type, ['separator', 'whitespace'])) {

					continue;
				}

				if (!isset($data[$val->type])) {

					$data[$val->type] = $val;
				} else {

					if (!is_array($data[$val->type])) {

						$data[$val->type] = [$data[$val->type]];
					}

					$data[$val->type][] = $val;
				}
			}

			foreach ($props as $k => $prop) {

				if ($name === $this->shorthand) {

					continue;
				}

				$data[$k] = $prop[$index];
			}

			// match
			$patterns = $this->config['pattern'];

			foreach ($patterns as $p => $pattern) {

				foreach (preg_split('#(\s+)#', $pattern, -1, PREG_SPLIT_NO_EMPTY) as $token) {

					if (empty($this->property_type[$token]['optional']) && (!isset($data[$token]) || (is_array($data[$token]) && !isset($data[$token][$index])))) {

						unset($patterns[$p]);
					}
				}
			}


			if (empty($patterns)) {

				// custom properties
				foreach (array_keys($data) as $key) {

					if (!isset($this->config[$key])) {

						return $this;
					}
				}

				if (isset($this->properties[$this->shorthand]) && isset($data[$name]) && $name != $this->shorthand) {

					$className = Value::getClassName($name);

					if (count($data[$name]) == 1 && is_callable($className . '::matchDefaults') && call_user_func($className . '::matchDefaults', $data[$name][0])) {

						unset($this->properties[$name]);
					}
				}

				else {

					var_dump(123);
					return $this;
				}
			}

			//
			foreach ($data as $key => $val) {

				if (!is_array($val)) {

					$val = [$val];
				}

				$set = [];

				if (isset($properties[$key]['prefix'])) {

					$prefix = $properties[$key]['prefix'];
					$set[] = (object)['type' => 'separator', 'value' => is_array($prefix) ? $prefix[1] : $prefix];
				}

				$set[] = $val[0];

				//
				if (Config::getPath('map.' . $key . '.multiple')) {

					$i = 0;
					$j = count($val);
					$sp = Config::getPath('map.' . $key . '.separator', ' ');

					$sp = $sp == ' ' ? ['type' => 'whitespace'] : ['type' => 'separator', 'value' => $sp];

					while (++$i < $j) {

						$set[] = clone((object)$sp);
						$set[] = $val[$i];
					}
				}

				$data[$key] = $set;
			}

			$set = [];

			foreach (preg_split('#(\s+)#', $pattern, -1, PREG_SPLIT_DELIM_CAPTURE) as $token) {

				if (isset($data[$token]) && isset($properties[$token]['prefix']) && is_array($properties[$token]['prefix'])) {

					$res = $set;
					$j = count($res);

					while ($j--) {

						if (in_array($res[$j]->type, ['whitespace', 'separator'])) {

							continue;
						}

						if ((isset($properties[$token]['multiple']) && $res[$j]->type == $token) ||
							$res[$j]->type == $properties[$token]['prefix'][0]['type']) {

							break;
						}

						if ($res[$j]->type !== $properties[$token]['prefix'][0]['type']) {

							return $this;
						}
					}
				}

				if (trim($token) == '') {

					$set[] = (object)['type' => 'whitespace'];
				} else if (isset($data[$token])) {

					array_splice($set, count($set), 0, $data[$token]);
				}
			}

			$results[] = $set;
		}

		$set = [];

		$i = 0;
		$j = count($results);

		array_splice($set, count($set), 0, $results[0]);

		while (++$i < $j) {
			$set[] = (object)['type' => 'separator', 'value' => $separator];

			array_splice($set, count($set), 0, $results[$i]);
		}

		$data = Value::reduce($set, ['remove_defaults' => true]);

		if (empty($data)) {

			$this->properties[$name] = (new Property($name))->setValue($value);
			return $this;
		}

		$this->properties = [$this->shorthand => (new Property($this->shorthand))->setValue($data)->
		setLeadingComments($leadingcomments)->
		setTrailingComments($trailingcomments)];

		return $this;
	}

	/**
	 * set property
	 * @param string $name
	 * @param string $value
	 * @return PropertyMap
	 * @ignore
	 */
	protected function setProperty($name, $value)
	{

		if (!isset($this->properties[$name])) {

			$this->properties[$name] = new Property($name);
		}

		$this->properties[$name]->setValue($value);

		return $this;
	}

	/**
	 * return Property array
	 * @return Property[]
	 */
	public function getProperties()
	{

		return array_values($this->properties);
	}

	/**
	 * convert this object to string
	 * @param string $join
	 * @return string
	 */
	public function render($join = "\n")
	{
		$glue = ';';
		$value = '';

		foreach ($this->properties as $property) {

			$value .= $property->render() . $glue . $join;
		}

		return rtrim($value, $glue . $join);
	}

	/**
	 * @return bool
	 */

	public function isEmpty()
	{

		return empty($this->properties);
	}

	/**
	 * convert this object to string
	 * @return string
	 */
	public function __toString()
	{

		return $this->render();
	}
}