<?php
namespace app\core;

abstract class Model
{
  public const RULE_REQUIRED = 'required';
  public const RULE_EMAIL = 'email';
  public const RULE_MIN = 'min';
  public const RULE_MAX = 'max';

  public function loadData($data)
  {
    foreach($data as $key => $value) {
      if (property_exists($this, $key)) {
        $this->{$key} = $value;
      }
    }
  }

  abstract public function rules();

  public array $errors = [];

  public function validate()
  {
    foreach($this->rules() as $attribute => $rules) {
      $value = $this->{$attribute};
      foreach ($rules as $rule) {
        $ruleName = $rule;
        if(!is_string($ruleName)) {
          $ruleName = $rule[0];
        }
        if($ruleName === self::RULE_REQUIRED && !$value) {
          $this->addErrorForRule($attribute, self::RULE_REQUIRED);
        }
        if($ruleName === self::RULE_EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
          $this->addErrorForRule($attribute, self::RULE_EMAIL);
        }
        if($ruleName === self::RULE_MIN && strlen($value) < $rule['min']) {
          $this->addErrorForRule($attribute, self::RULE_MIN, $rule);
        }
        if($ruleName === self::RULE_MAX && strlen($value) > $rule['max']) {
          $this->addErrorForRule($attribute, self::RULE_MAX, $rule);
        }

      }
    }

    return empty($this->errors);
  }

  private function addErrorForRule($attribute, $rule, $params = [])
  {
    $message = $this->errorMessages()[$rule] ?? '';
    foreach($params as $key => $value) {
      $message = str_replace("{{$key}}", $value, $message);
    }
    $this->errors[$attribute][] = $message;
  }


  public function addError($attribute, $message)
  {
    $this->errors[$attribute][] = $message;
    print_r($this->errors);
  }

  public function errorMessages()
  {
    return [
      self::RULE_REQUIRED => 'This field is required',
      self::RULE_EMAIL => 'This field must be valid email address',
      self::RULE_MIN => 'Min length of this field must be {min}',
      self::RULE_MAX => 'Max length of this field must be {max}'
    ];
  }

  public function hasError($attribute)
  {
    return $this->errors[$attribute] ?? false;
  }

  public function getFirstError($attribute)
  {
    return $this->errors[$attribute][0] ?? false;
  }
}