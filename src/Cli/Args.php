<?php

namespace TBela\CSS\Cli;


use InvalidArgumentException;
use TBela\CSS\Cli\Exceptions\DuplicateArgumentException;
use TBela\CSS\Cli\Exceptions\MissingParameterException;
use UnexpectedValueException;
use ValueError;

class Args
{

	// reject unknown flags
	protected bool $strict = false;

	// command groups
	protected array $groups = [
		'default' => [
			'description' => "\nUsage: \n\$ %s [OPTIONS] [PARAMETERS]\n"
		]
	];

	// command line args
	/**
	 * @var Option[]
	 * @ignore
	 */
	protected array $flags = [];

	/**
	 * @var array
	 * @ignore
	 */
	protected array $settings = [];

	// short flags
	protected array $alias = [];

	// command line params
	protected array $argv;
	protected array $args = [];
	protected string $version;

	protected string $exe = '';

	public function __construct(array $argv)
	{

		$this->argv = $argv;
		$this->exe = basename($argv[0]);
	}

	public function setDescription(string $description): static
	{

		$this->groups['default']['description'] = sprintf($description, basename($this->argv[0]));
		return $this;
	}

	/**
	 * enable or disable strict mode. in strict mode, all arguments must be declared
	 * @param bool $strict
	 * @return $this
	 */
	public function setStrict(bool $strict): static
	{

		$this->strict = $strict;
		return $this;
	}

	public function getGroups(): array
	{

		return $this->groups;
	}

	public function addGroup(string $group, string $description, bool $internal = false): static
	{

		$this->groups[$group]['description'] = $description;
		$this->groups[$group]['internal'] = $internal;
		return $this;
	}

	/**
	 * @throws Exceptions\DuplicateArgumentException
	 */
	public function add(string $name, string $description, string $type, array|string $alias = null, $multiple = true, $required = false, $defaultValue = null, ?array $options = [], array|string|null $dependsOn = null, $group = 'default'): static
	{

		if (isset($this->flags[$name])) {

			$exe = basename($this->argv[0]);
			throw new Exceptions\DuplicateArgumentException(sprintf("%s: duplicate flag: '%s'\nTry '%s --help' for more information", $exe, $name, $exe));
		}

		$this->flags[$name] = new Option($type, $multiple, $required, $defaultValue, $options);

		$this->groups[$group]['arguments'][$name]['description'] = $description;

		if (!empty($dependsOn)) {

			$this->settings['requires'][$name] = (array)$dependsOn;
		}

		if (!is_null($alias) && $alias !== '' && $alias !== []) {

			$this->alias($name, $alias);
		}

		return $this;
	}

	/**
	 * @throws Exceptions\MissingParameterException
	 * @throws Exceptions\UnknownParameterException
	 */
	public function parse(): static
	{

		$argc = count($this->argv);

		$args = [];
		$flags = [];
		$this->args = [];

		$flagReg = '#^((--?)([a-zA-Z][a-zA-Z\d_-]*))(=.*)?$#';

		$i = 0;

		/**
		 * @var Option[] $dynamicArgs
		 */
		$dynamicArgs = [];

		while ($i++ < $argc - 1) {

			if (preg_match($flagReg, $this->argv[$i], $matches)) {

				$value = str_starts_with($matches[4] ?? '', '=') ? substr($matches[4], 1) : null;
				$name = $matches[3];
				$names = [];

				if ($matches[2] == '-') {

					if (is_null($value)) {

						$j = strlen($name);

						for ($k = 1; $k < $j; $k++) {

							if (!isset($this->alias[$name[$k]])) {

								break;
							}
						}

						$value = substr($name, $k);
						$name = substr($name, 0, $k);

						if ($value === '') {

							$value = null;
						}
					}

					$names = str_split($name);
					$name = array_pop($names);
					$flags[$name] = $name;
				}

				try {

					$option = $this->parseFlag($name, $dynamicArgs);

					if (is_null($value) && $option->getType() != 'bool' && $i < $argc - 1 && !preg_match($flagReg, $this->argv[$i + 1])) {

						$option->addValue($this->argv[++$i]);
					} else {

						$option->addValue(is_null($value) && in_array($option->getType(), ['auto', 'bool']) ? true : $value);
					}

					$k = count($names);

					while ($k--) {

						$name = $names[$k];
						$flags[$name] = $name;

						$option = $this->parseFlag($name, $dynamicArgs);

						$option->addValue(true);
					}
				} catch (ValueError) {

					throw new ValueError(sprintf("%s: invalid value specified for -- '%s'\nTry '%s --help'\n", $this->exe, $name, $this->exe), 1);
				} catch (UnexpectedValueException) {

					throw new UnexpectedValueException(sprintf("%s: invalid value specified for -- '%s'\nTry '%s --help'\n", $this->exe, $name, $this->exe), 1);
				} catch (Exceptions\UnknownParameterException) {

					throw new Exceptions\UnknownParameterException(sprintf("%s: unknown parameter -- '%s'\nTry '%s --help'\n", $this->exe, $name, $this->exe), 1);
				} catch (InvalidArgumentException) {

					throw new InvalidArgumentException(sprintf("%s: expected string value -- '%s'\nTry '%s --help'\n", $this->exe, $name, $this->exe), 1);
				}

			} else {

				$args[] = $this->argv[$i];
			}
		}

		$result = [];
		foreach (array_merge($this->flags, $dynamicArgs) as $name => $option) {

			if (!$option->isValueSet()) {

				if (!$option->isRequired()) {

					continue;
				}

				throw new Exceptions\MissingParameterException(sprintf("%s: missing required parameter -- '%s'\nTry '%s --help'\n", $this->exe, $name, $this->exe), 1);
			}

			$result[$name] = $option->getValue();
		}

		foreach ($result as $name => $value) {

			if (!empty($this->settings['requires'][$name])) {

				foreach ($this->settings['requires'][$name] as $required) {

					if (!array_key_exists($required, $result)) {

						throw new MissingParameterException(sprintf("%s: missing required parameter -- '%s", $this->exe, $required), 400);
					}
				}
			}
		}


		if (array_key_exists('help', $result) ) {

			echo $this->showHelp(!array_key_exists('h', $flags));
			exit;
		}

		else if (!empty($this->version) && array_key_exists('version', $result)) {

			echo $this->version;
			exit;
		}

		$this->args = $result;
		$this->args['_'] = $args;

		return $this;
	}

	public function getArguments(): array
	{

		return $this->args;
	}

	/**
	 * @throws DuplicateArgumentException
	 */
	public function setVersion(string $info, string $description = 'display version info', string $flag = 'version'): static
	{

		unset($this->flags[$flag]);

		$this->add($flag, $description, Option::BOOL);
		$this->version = $info;

		return $this;
	}

	public function getExe(): string
	{

		return $this->exe;
	}

	/**
	 * @throws DuplicateArgumentException
	 */
	public function help(string $flag = 'help', string $description = 'display this help menu'): static
	{

		unset($this->flags[$flag]);

		$this->add($flag, $description, Option::BOOL);
		return $this;
	}

	public function showHelp($extended = false): string
	{

		$output = '';
		$groups = $this->groups;

		if (isset($groups['default'])) {

			$output .= $this->printGroupHelp($groups['default'], $extended) . "\n";
		}

		unset($groups['default']);

		foreach ($groups as $def) {

			if (!empty($def['internal'])) {

				continue;
			}

			$output .= $this->printGroupHelp($def, $extended) . "\n\n";
		}

		return $output;
	}

	protected function printGroupHelp(array $group, bool $extended): string
	{

		$output = '';

		if (isset($group['description'])) {

			$output .= sprintf($group['description'], basename($this->argv[0]));
		}

		$args = [];
		$length = 0;

		$flags = $group['arguments'] ?? [];

		ksort($flags);

		foreach ($flags as $option => $conf) {

			$rev = array_keys(array_filter($this->alias, function ($name) use ($option) {

				return $name == $option;
			}));

			$description = $conf['description'];

			if (!empty($this->settings['requires'][$option])) {

				$description .= ', requires --' . implode(', --', $this->settings['requires'][$option]);
			}

			$args[] = ['flags' => "\n" . ($rev ? '-' . implode(', -', $rev) . ', ' : '') . '--' . $option, 'description' => $description];
			$length = max($length, strlen(end($args)['flags']));

			if ($extended) {

				$args[] = ['flags' => " type", 'description' => $this->flags[$option]->getType()];
				$length = max($length, strlen(end($args)['flags']));

				$args[] = ['flags' => " required", 'description' => $this->flags[$option]->isRequired() ? 'yes' : 'no'];
				$length = max($length, strlen(end($args)['flags']));

				$args[] = ['flags' => " multiple", 'description' => $this->flags[$option]->isMultiple() ? 'yes' : 'no'];
				$length = max($length, strlen(end($args)['flags']));

				$options = $this->flags[$option]->getOptions();

				if (!empty($options)) {

					$args[] = ['flags' => " valid options", 'description' => implode(', ', $options)];
					$length = max($length, strlen(end($args)['flags']));
				}

				$defaultValue = $this->flags[$option]->getDefaultValue();

				if (isset($defaultValue)) {

					$args[] = ['flags' => " default value", 'description' => json_encode($defaultValue)];
					$length = max($length, strlen(end($args)['flags']));
				}
			}
		}

		foreach ($args as $arg) {

			$output .= str_pad($arg['flags'], $length) . "\t" . $arg['description'] . "\n";
		}

		return rtrim($output);
	}

	/**
	 * @param string $name
	 * @param array $dynamicArgs
	 * @return Option|null
	 * @throws Exceptions\UnknownParameterException
	 */
	protected function parseFlag(string &$name, array &$dynamicArgs): ?Option
	{
		if (isset($this->alias[$name])) {

			$name = $this->alias[$name];
		}

		$option = $this->flags[$name] ?? null;

		if (is_null($option)) {

			if ($this->strict) {

				throw new Exceptions\UnknownParameterException(sprintf("%s: invalid option -- '%s'", basename($this->argv[0]), $name));
			} else {

				if (!isset($dynamicArgs[$name])) {

					$dynamicArgs[$name] = $option;
				}

				$option = $dynamicArgs[$name] ?? new Option();

				if (!isset($dynamicArgs[$name])) {

					$dynamicArgs[$name] = $option;
				}
			}
		}

		return $option;
	}

	/**
	 * @param array|string $alias
	 * @param string $name
	 * @return Args
	 */
	public function alias(string $name, array|string $alias): static
	{
		foreach ((array)$alias as $a) {

			if (!preg_match('#^[a-zA-Z]$#', $a)) {

				throw new InvalidArgumentException(sprintf("command option must be a single letter [a-zA-Z]: '%s'", $a));
			}

			if (isset($this->alias[$a])) {

				throw new InvalidArgumentException(sprintf("duplicated alias '%s' for the flag '%s' is already defined by command '%s'", $a, $name, $this->alias[$a]));
			}

			$this->alias[$a] = $name;
		}

		return $this;
	}
}