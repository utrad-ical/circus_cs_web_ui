<?php

/**
 * Very simple web form validator classes.
 * This is a part of CIRCUS CS project.
 * @author S. Miki <smiki-tky@umin.ac.jp>
 * @package formValidators
 */

/**
 * Very simple web form validator.
 * This class takes an array (usually $_REQUEST, $_POST, etc.) and
 * checks the content of the array according to a user-defined rule set.
 * @package formValidators
 */
class FormValidator
{
	private $validators = array(
		'int'       => 'IntegerValidator',
		'integer'   => 'IntegerValidator',
		'numeric'   => 'NumericValidator',
		'str'       => 'StringValidator',
		'string'    => 'StringValidator',
		//'pgregexp'  => 'PgRegexpValidator',
		'date'      => 'DateValidator',
		'datetime'  => 'DateTimeValidator',
		'time'      => 'TimeValidator',
		'uid'       => 'UIDValidator',
		'cadname'   => 'CadNameValidator',
		'version'   => 'CadVersionValidator',
		'select'    => 'SelectValidator',
		'array'     => 'ArrayValidator',
		'assoc'     => 'AssociativeArrayValidator',
		'callback'  => 'CallBackValidator',
		'pass'      => 'PassThroughValidator',
		'json'      => 'JsonValidator'
	);

	/**
	 * Normalized output of the validated data.
	 */
	public $output;
	
	/**
	 * If validation failes, this is an array which holds the error messages.
	 */
	public $errors;

	/**
	 * Associative array which holds the internal rule set.
	 */
	private $rules;
	
	/**
	 * Adds one validation rule to the internal rule set.
	 * @param string $keyName Name of the rule.
	 * @param array $rule Definition of the rule in associative array.
	 */
	public function addRule($keyName, $rule)
	{
		$validator = $this->validators[$rule['type']];
		if (class_exists($validator)) {
			$ruleObj = new $validator;
			$ruleObj->init($keyName, $rule, $this);
		} else {
			throw new Exception(
				"FormValidator exception. " .
				"(unknown validation type " . $rule['type'] . ")");
		}
		$this->rules[$keyName] = $ruleObj;
	}

	/**
	 * Adds multiple rules to the internal rule set.
	 * @param mixed $rules Associative array of rules.
	 * Alternatively you can pass array of rules in JSON format.
	 */
	public function addRules($rules)
	{
		if (is_array($rules) && count($rules) > 0) {
			foreach ($rules as $key => $value) {
				$this->addRule($key, $value);
			}
			return true;
		} else if (is_string($rules)) {
			$result = json_decode($rules, true);
			if (is_null($result) || !is_array($result)) {
				throw new Exception(
					"FormValidator exception. " .
					"(input rule set is not a valid JSON string)");
			}
			if (is_array($result)) {
				$this->addRules($result);
			} else return false;
		}
		return false;
	}

	/**
	 * Removes one rule from the internal rule set.
	 * @param string $keyName Name of the rule to delete.
	 */
	public function removeRule($keyName)
	{
		unset($this->rules[$keyName]);
	}

	/**
	 * Empties the internal rule set.
	 */
	public function resetRules()
	{
		$rules = array();
	}

	/**
	 * Executes the validation.
	 * @param array $input Associative array to validate.
	 * @return boolean true if validation is complete, false if errors exist.
	 * see $output member for the validation result if scceeded,
	 * or see $error member for the error details.
	 */
	public function validate($input)
	{
		$this->output = array();
		$this->errors = array();
		if (!is_array($input))
			return false;

		// begin validation
		foreach ($this->rules as $key => $rule) {
			if ($key == '*') continue;
			$success = $rule->check($input[$key]);
			if ($success) {
				$this->output[$key] = $rule->output;
			} else {
				//$this->errors[$key] = $rule->error;
				$this->errors[] = $rule->error;
			}
		}

		// grabs other items if '*' notation is used
		if ($this->rules['*'] instanceof ValidatorBase) {
			$validator = $this->rules['*'];
			foreach ($input as $key => $value) {
				if (!($this->rules[$key] instanceof ValidatorBase)) {
					$validator->setKey($key);
					$success = $validator->check($input[$key]);
					if ($success) {
						$this->output[$key] = $validator->output;
					} else {
						$this->errors[] = $validator->error;
					}
				}
			}
		}
		return (count($this->errors) == 0);
	}
	
	/**
	 * Register a custom (user-defined) validator class.
	 * @param $typeName mixed Name, or array of names, of the registered type.
	 * @param $className string Name of the class to be registered.
	 */
	public function registerValidator($typeName, $className) {
		if (is_string($typeName) && is_string($className) &&
			is_subclass_of($className, 'ValidatorBase')) {
				$this->validators[$typeName] = $className;
		} else if (is_array($typeName)) {
			foreach ($typeName as $t) {
				$this->registerValidator($t, $className);
			}
		} else {
			throw new Exception(
				"FormValidator exception. Registering invalid validator class."
			);
		}
	}
	
	/**
	 * Creates new FormValidator instance having the same configuration as this.
	 */
	public function createChildValidator() {
		$result = new FormValidator();
		$result->validators = $this->validators;
		return $result;
	}
}



////////////////////////////////////////////////////////////////////////////////
// Validators
////////////////////////////////////////////////////////////////////////////////

/**
 * Base class of indivisual validators.
 * Each validator will check the input and returns whether the data is valid.
 * When nesessary the validator can modify (normalize) the input data.
 * This is an abstract class: do not directly instantiate this class.
 * @package formValidators
 */
abstract class ValidatorBase
{
	/**
	 * The FormValidator instance associated to this validator.
	 */
	protected $owner;
	
	/**
	 * Corresponding key of the processing data.
	 */
	protected $key;
	
	/**
	 * This holds the label of the data. Labels are used as a caption
	 * which are used instead of $key.
	 */
	protected $label;
	
	protected $params;

	/**
	 * Normalized result of the validation. This may be modified from the
	 * original according to the validation rule.
	 */
	public $output;
	
	/**
	 * Error message when the validation fails.
	 */
	public $error;
	
	/**
	 * Initializes the validator.
	 */
	public function init($key, $params, $owner) {
		$this->owner = $owner;
		$this->params = $params;
		$this->setKey($key);
	}
	
	public function setKey($key) {
		$this->key = $key;
		$this->label = $key;
		if ($params['label']) $this->label = $params['label'];
	}
	
	/**
	 * Checks the input data.
	 * @return boolean true if $input is validated, false if $input is invalid.
	 */
	abstract public function check($input);
}


/**
 * Base class of many other validators which takes in single scalar value.
 * This class implements common rule options such as 'required', 'default',
 * 'preFilter', 'postFilter', and 'errorMes'.
 * If these functionalities are not necessary, use ValidatorBase instead.
 * @package formValidators
 */
abstract class ScalarValidator extends ValidatorBase
{
	abstract function validate($input);

	public function check($input) {
		if (strlen(trim($input)) == 0) {
			if ($this->params['required']) {
				if ($this->params['errorMes']) {
					$this->error = $this->params['errorMes'];
				} else {
					$this->error = "The field '$this->label' is required.";
				}
				return false;
			} else if ($this->params['default']) {
				$this->output = $this->params['default'];
				return true;
			} else {
				$this->output = NULL; // leave empty
				return true;
			}
		}
		if (!is_scalar($input)) {
			$this->error = "The field '$this->label' is invalid (non-scalar value)";
			return false;
		}
		$preFilter = $this->params['preFilter'];
		if (is_callable($preFilter)) {
			$input = $preFilter($input);
		}
		$valid = $this->validate($input);
		$postFilter = $this->params['postFilter'];
		if (is_callable($postFilter)) {
			$this->output = $postFilter($this->output);
		}
		if ($valid) {
			return true;
		} else {
			if ($this->params['errorMes']) {
				$this->error = $this->params['errorMes'];
			}
			return false;
		}
	}
}

/**
 * Validator for integers.
 * @package formValidators
 */
class IntegerValidator extends ScalarValidator
{
	public function validate($input) {
		$input = preg_replace('/\,|\s/', '', $input); // removes optional characters

		$label = $this->label;

		if (preg_match('/^(0|\-?[1-9]\d*)$/', $input)) {
			$min = $this->params['min'];
			if (isset($this->params['min']) && $input < $this->params['min']) {
				$this->error = "Input data '$label' must be at least $min";
				return false;
			}
			$max = $this->params['max'];
			if (isset($this->params['max']) && $input > $this->params['max']) {
				$this->error = "Input data '$label' must be no more than $max.";
				return false;
			}
			$this->output = $input;
			return true;
		} else {
			$this->error = "Input data '$label' is not a valid number.";
			return false;
		}
	}
}


/**
 * Validator for number or a numeric string
 * @package formValidators
 */
class NumericValidator extends ScalarValidator
{
	public function validate($input) {
		$input = preg_replace('/\,|\s/', '', $input); // removes optional characters

		$label = $this->label;

		if (is_numeric($input)) {
			$min = $this->params['min'];
			if (isset($this->params['min']) && $input < $this->params['min']) {
				$this->error = "Input data '$label' must be at least $min";
				return false;
			}
			$max = $this->params['max'];
			if (isset($this->params['max']) && $input > $this->params['max']) {
				$this->error = "Input data '$label' must be no more than $max.";
				return false;
			}
			$this->output = $input;
			return true;
		} else {
			$this->error = "Input data '$label' is not a valid number.";
			return false;
		}
	}
}



/**
 * Validator for strings.
 * @package formValidators
 */
class StringValidator extends ScalarValidator
{
	public function validate($input) {
		$label = $this->label;
		$min = $this->params['minLength'];
		if ($min > 0 && strlen($input) < $min) {
			$this->error = "Input data '$label' must be at least $min characters.";
			return false;
		}
		$max = $this->params['maxLength'];
		if ($max > 0 && strlen($input) > $max) {
			$this->error = "Input data '$label' must not be longer than $max characters.";
			return false;
		}
		if ($this->params['regex']) {
			if (!preg_match($this->params['regex'], $input)) {
				$this->error = "Input data '$label' is invalid.";
				return false;
			}
		}
		$this->output = $input;
		return true;
	}
}

/**
 * Validator for date and time.
 * This checks if the passed string is the valid expression which
 * PHP's internal DateTime::__construct can understand.
 * @link http://www.php.net/manual/ja/datetime.formats.php
 * @package formValidators
 */
class DateTimeValidator extends ScalarValidator
{
	protected $defaultFormat = 'Y-m-d H:i:s';
	
	protected function touchDate(DateTime $date) {
		return $date;
	}
	
	protected function timeStamp(DateTime $date) {
		return $date->format('U');
	}
	
	public function validate($input) {
		try {
			$date = new DateTime($input);
			$date = $this->touchDate(new DateTime($input));
			
			if ($this->params['min']) {
				$min = $this->touchDate(new DateTime($this->params['min']));
				if ($this->timeStamp($date) < $this->timeStamp($min)) {
					$this->error = "Input time '$this->label' must not be ".
						"before " . $min->format($this->defaultFormat);
					return false;
				}
			}
			if ($this->params['max']) {
				$max = $this->touchDate(new DateTime($this->params['max']));
				if ($this->timeStamp($date) > $this->timeStamp($max)) {
					$this->error = "Input time '$this->label' must not be ".
						"after " . $max->format($this->defaultFormat);
					return false;
				}
			}
			$format = $this->defaultFormat;
			if ($this->params['format']) $format = $this->params['format'];
			$this->output = $date->format($format);
			return true;
		} catch (Exception $e) {
			$this->error = "Input data '$this->label' is invalid time.";
			return false;
		}
	}
}

/**
 * Validator for date.
 * This is the same as {@link DateTimeValidator} except that
 * the time part of the parsed result will be truncated to '00:00:00'.
 * @package formValidators
 */
class DateValidator extends DateTimeValidator
{
	public function __construct() {
		$this->defaultFormat = 'Y-m-d';
	}
	
	protected function touchDate(DateTime $date) {
		$date->setTime(0, 0, 0);
		return $date;
	}
}

/**
 * Validator for time.
 * @author Y.Nomura
 */
class TimeValidator extends ScalarValidator
{
	public function validate($input) {

		if(preg_match('/^\d{1,2}:\d{1,2}:\d{1,2}$/', $input))
		{
			$vals = explode(":", $input);
				
			if(0<=$vals[0] && $vals[0]<=23 && 0<=$vals[1] && $vals[1]<=59
			    && 0<=$vals[2] && $vals[2]<=59){
				$this->output = $input;
				return true;
			} else {
				$this->error = "Input data '$this->label' is invalid.";
				return false;
			}
		} else {
			$this->error = "Input data '$this->label' is invalid.";
			return false;
		}
	}
}


/**
 * Validator which checks if the input is one of the given enumeraton data.
 * Unless 'required' option is true, an empty string will still be valid.
 * @package formValidators
 */
class SelectValidator extends ScalarValidator
{
	public function validate($input) {
		$options = $this->params['options'];
		if (is_array($options) && array_search($input, $options) !== false) {
			$this->output = $input;
			return true;
		} else {
			if ($this->params['otherwise']) {
				$this->output = $this->params['otherwise'];
				return true;
			}
			$this->error = "Input data '$this->label' is invalid.";
			return false;
		}
	}
}

/**
 * Validator that confirms the given data is an array.
 * Use 'required' option to guarantee there is at least one item in the array.
 * By default, no input or empty string will be normalized to an empty array.
 * Optinally, children of the array can be validated using the 'childenRule'
 * option.
 * Note that the keys (indices) of the given array will be discarded, (ie
 * the given array will be re-indexed from index zero).
 * @package formValidators
 */
class ArrayValidator extends ValidatorBase
{
	protected $childValidator;
	
	public function init($key, $params, $owner) {
		parent::init($key, $params, $owner);
		if (is_array($params['childrenRule'])) {
			$this->childValidator = $this->owner->createChildValidator();
			$this->childValidator->addRule('data', $params['childrenRule']);
		}
	}
	
	public function check($input) {
		$label = $this->label;
		$result = array();
		if (is_array($input)) {
			if ($this->childValidator) {
				foreach ($input as $item) {
					if (!$this->childValidator->validate(
						array('data' => $item))) {
						$this->error = "Array '$label' contains invalid data.";
						return false;
					}
					array_push($result, $this->childValidator->output['data']);
				}
			} else {
				$result = array_values($input);
			}
			$min = $this->params['minLength'];
			if ($min > 0 && count($input) < $min) {
				$this->error = "Array '$label' must have least $min items.";
				return false;
			}
			$max = $this->params['maxLength'];
			if ($max > 0 && count($input) > $max) {
				$this->error = "Array '$label' must not have more than $max items.";
				return false;
			}
			$this->output = $result;
			return true;
		} else {
			if ($this->params['required'] || $this->params['minLength'] > 0) {
				$this->error = "Array '$label' is empty.";
				return false;
			} else if (strlen(trim($input)) == 0) {
				$this->output = array();
				return true;
			} else {
				$this->error = "'$label' is invalid. (non-array data is passed)";
				return false;
			}
		}
	}
}

/**
 * Validator for associative array.
 * With this validator you can validate nested data structure.
 * @package formValidators
 */
class AssociativeArrayValidator extends ValidatorBase
{
	protected $childValidator;
	
	public function init($key, $params, $owner)
	{
		parent::init($key, $params, $owner);
		if ($params['rule']) {
			$this->childValidator = $this->owner->createChildValidator();
			$this->childValidator->addRules($params['rule']);
		}
	}
	
	public function check($input)
	{
		$cv = $this->childValidator;
		if (is_array($input)) {
			if ($cv) {
				if ($cv->validate($input)) {
					$this->output = $cv->output;
					return true;
				} else {
					$this->error = "$this->label is invalid.";
					return false;
				}
			}
			$this->output = $input;
			return true;
		} else {
			$this->error = "$this->label is invalid.";
			return false;
		}
	}
}

/**
 * Callback validator can be used to customize the validation routine.
 *
 * The callback function will be specified using the option 'callback',
 * which will be called using PHP's call_user_func function.
 * In simpler style, the callback function simply return boolean value
 * (true or false), according the input data is valid or not.
 * In complex style, the callback function returns an array containing two
 * elements. The first element is a boolean which will be true if the validation
 * succeeds. The second element holds the normalized value if the first element
 * is true, or an error message string if the first element is false.
 * @package formValidators
 */
class CallbackValidator extends ValidatorBase
{
	public function init($key, $params, $owner) {
		parent::init($key, $params, $owner);
		if (!is_callable($this->params['callback'])) {
			throw new Exception(
				"FormValidator exception. " .
				"(non-callable callback function (" . $key . ")");
		}
	}
	
	public function check($input) {
		$callback = $this->params['callback'];
		$result = call_user_func($callback, $input);
		if (is_scalar($result)) {
			if ($result) {
				$this->output = $input;
				return true;
			} else {
				$this->error = "The data '$this->label' is invalid.";
				if ($this->params['errorMes']) {
					$this->error = $this->params['errorMes'];
				}
				return false;
			}
		} else if (is_array($result) && count($result) >= 2) {
			if ($result[0]) {
				$this->output = $result[1];
				return true;
			} else {
				$this->error = $result[1];
				return false;
			}
		}
	}
}

/**
 * This validator actually validates nothing: the validation always succeeds
 * and just passes the input data as-is.
 * Usefull when combined with '*' (match-anything) rule.
 * @package formValidators
 */
class PassThroughValidator extends ValidatorBase
{
	public function check($input) {
		$this->output = $input;
		return true;
	}
}

/**
 * Validator for DICOM UID (allowed characters: 0-9 and period).
 * @package formValidators
 * @author Y. Nomura
 */
class UIDValidator extends ScalarValidator
{
	public function validate($input) {
		if (preg_match("/[^\d\\.]/", $input)) {
			$this->error = "Input data '$this->label' is invalid.";
			return false;
		}
		$this->output = $input;
		return true;
	}
}

/**
 * Validator for CAD name (allowed characters: 'all' or (\w and '-')).
 * @package formValidators
 * @author Y. Nomura
 */
class CadNameValidator extends ScalarValidator
{
	public function validate($input) {
		if ($input==='all' || !preg_match("/[^\w-_]/", $input)) {
			$this->output = $input;
			return true;
		} else if ($this->params['otherwise']) {
			$this->output = $this->params['otherwise'];
			return true;
		} else {
			$this->error = "Input data '$this->label' is invalid.";
			return false;
		}
	}
}

/**
 * Validator for CAD name (allowed characters: 'all' or (\w and '.')).
 * @package formValidators
 * @author Y. Nomura
 */
class CadVersionValidator extends ScalarValidator
{
	public function validate($input) {
		if ($input==='all' || !preg_match("/[^\w-_\.]/", $input)) {
			$this->output = $input;
			return true;
		} else if ($this->params['otherwise']) {
			$this->output = $this->params['otherwise'];
			return true;
		} else {
			$this->error = "Input data '$this->label' is invalid.";
			return false;
		}
	}	
}

/**
 * Base class that utilizes PDO and PostgreSQL for validating something.
 * @package formValidators
 */
abstract class PgValidator extends ScalarValidator
{
	public static $conn; // DB handle (static variable)
}

/**
 * Validates if the given input is a well-formed regular expression.
 * @package formValidators
 */
class PgRegexValidator extends PgValidator
{
	public function validate($input)
	{
		try {
			$st = self::$conn->prepare("select 'dummy' ~ ?");
			$st->bindParam(1, $input);
			$st->execute();
			// throw a new exception regardless of the value of PDO::ATTR_ERRMODE
			if ($st->errorCode() != '00000') {
				throw new PDOException();
			}
		} catch (Exception $e) {
			$this->error = $this->label . ": ";
			$arr = $st->errorInfo();
			$this->error .= $arr[2];
			return false;
		}
		$this->output = $input;
		return true;
	}
}

/**
 * Validator which confirms that the input is well-formed JSON string.
 * Output is converted into (associative) array representing the input string.
 * Validating the internal structure of JSON string is also available using
 * 'rule' option.
 * @package formValidators
 */
class JsonValidator extends ValidatorBase
{
	protected $childValidator;
	
	public function init($key, $params, $owner) {
		$this->childVaidator = null;
		parent::init($key, $params, $owner);
		if (is_array($params['rule'])) {
			$this->childValidator = $this->owner->createChildValidator();
			$this->childValidator->addRule("result", $params['rule']);
		}
	}

	public function check($input) {
		$label = $this->label;
		$cv = $this->childValidator;
		$result = json_decode($input, true); // decode as (associative) array
		if ($result !== null) {
			if ($cv) {
				if ($cv->validate(array("result" => $result))) {
					$this->output = $cv->output['result'];
					return true;
				} else {
					$this->error = "'$label' contains invalid data: " .
						implode('', $cv->errors);
					return false;
				}
			}
			$this->output = $result;
			return true;
		} else {
			if (strlen($input) > 0) {
				$this->error = "'$label' is invalid. (JSON parse failure)";
				return false;
			}
			if ($this->params['required']) {
				$this->error = "'$label' is empty.";
				return false;
			} else {
				$this->output = null;
				return true;
			}
		}
	}
}

?>
