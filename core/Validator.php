<?php

class Validator
{
    private $errors = array();
    private $data = array();

    public function validate(array $data, array $rules)
    {
        $this->data = $data;
        $this->errors = array();

        foreach ($rules as $field => $ruleString) {
            $value = isset($data[$field]) ? trim((string) $data[$field]) : '';
            $rulesForField = is_array($ruleString) ? $ruleString : explode('|', $ruleString);

            foreach ($rulesForField as $rule) {
                $parts = explode(':', $rule, 2);
                $name = $parts[0];
                $argument = $parts[1] ?? null;

                if ($name === 'required' && $value === '') {
                    $this->add($field, 'هذا الحقل مطلوب.');
                } elseif ($name === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->add($field, 'يرجى إدخال بريد إلكتروني صحيح.');
                } elseif ($name === 'min' && $value !== '' && mb_strlen($value) < (int) $argument) {
                    $this->add($field, 'القيمة قصيرة جداً.');
                } elseif ($name === 'max' && $value !== '' && mb_strlen($value) > (int) $argument) {
                    $this->add($field, 'القيمة طويلة جداً.');
                } elseif ($name === 'confirmed' && $value !== ($data[$field . '_confirmation'] ?? $data['password_confirmation'] ?? null)) {
                    $this->add($field, 'تأكيد القيمة غير متطابق.');
                } elseif ($name === 'url' && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->add($field, 'الرابط غير صحيح.');
                } elseif ($name === 'numeric' && $value !== '' && !is_numeric($value)) {
                    $this->add($field, 'يجب أن تكون القيمة رقمية.');
                } elseif ($name === 'in' && $value !== '' && !in_array($value, explode(',', $argument), true)) {
                    $this->add($field, 'القيمة المختارة غير مسموحة.');
                }
            }
        }

        return !$this->hasErrors();
    }

    public function errors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return !empty($this->errors);
    }

    public function firstError($field)
    {
        return $this->errors[$field][0] ?? null;
    }

    private function add($field, $message)
    {
        $this->errors[$field][] = $message;
    }
}
