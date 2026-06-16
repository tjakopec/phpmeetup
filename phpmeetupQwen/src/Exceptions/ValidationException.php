<?php

declare(strict_types=1);

namespace App\Exceptions;

final class ValidationException extends ShippingException
{
    /** @var array<string, string[]> */
    private array $errors = [];

    public function __construct(array $errors, int $code = 422)
    {
        $this->errors = $errors;
        parent::__construct(self::formatErrors($errors), $code);
    }

    /** @return array<string, string[]> */
    public function getErrors(): array
    {
        return $this->errors;
    }

    private static function formatErrors(array $errors): string
    {
        $messages = [];
        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $messages[] = "{$field}: {$error}";
            }
        }
        return 'Validation failed: ' . implode(', ', $messages);
    }
}