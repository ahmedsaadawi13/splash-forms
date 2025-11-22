<?php
// FILE: /app/core/Validator.php

class Validator {
    private $data;
    private $rules;
    private $errors = [];

    public function __construct($data, $rules) {
        $this->data = $data;
        $this->rules = $rules;
    }

    public static function validate($data, $rules) {
        $validator = new self($data, $rules);
        return $validator->run();
    }

    public function run() {
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            foreach ($rules as $rule) {
                $this->applyRule($field, $rule);
            }
        }

        return [
            'valid' => empty($this->errors),
            'errors' => $this->errors
        ];
    }

    private function applyRule($field, $rule) {
        $value = $this->data[$field] ?? null;

        if (strpos($rule, ':') !== false) {
            list($ruleName, $ruleValue) = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $ruleValue = null;
        }

        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->addError($field, "$field is required");
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "$field must be a valid email address");
                }
                break;

            case 'min':
                if (!empty($value) && strlen($value) < $ruleValue) {
                    $this->addError($field, "$field must be at least $ruleValue characters");
                }
                break;

            case 'max':
                if (!empty($value) && strlen($value) > $ruleValue) {
                    $this->addError($field, "$field must not exceed $ruleValue characters");
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, "$field must be numeric");
                }
                break;

            case 'integer':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->addError($field, "$field must be an integer");
                }
                break;

            case 'url':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, "$field must be a valid URL");
                }
                break;

            case 'alpha':
                if (!empty($value) && !ctype_alpha($value)) {
                    $this->addError($field, "$field must contain only letters");
                }
                break;

            case 'alphanumeric':
                if (!empty($value) && !ctype_alnum($value)) {
                    $this->addError($field, "$field must contain only letters and numbers");
                }
                break;

            case 'in':
                $allowed = explode(',', $ruleValue);
                if (!empty($value) && !in_array($value, $allowed)) {
                    $this->addError($field, "$field must be one of: " . implode(', ', $allowed));
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if ($value !== ($this->data[$confirmField] ?? null)) {
                    $this->addError($field, "$field confirmation does not match");
                }
                break;

            case 'unique':
                list($table, $column) = explode(',', $ruleValue);
                $db = Database::getInstance();
                $count = $db->fetchColumn(
                    "SELECT COUNT(*) FROM $table WHERE $column = ?",
                    [$value]
                );
                if ($count > 0) {
                    $this->addError($field, "$field already exists");
                }
                break;
        }
    }

    private function addError($field, $message) {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function getErrors() {
        return $this->errors;
    }
}
