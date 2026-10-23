<?php
namespace App\Core;

final class Validator {
    private array $errors = [];
    public function required(string $field, mixed $value, string $label): self {
        if (trim((string)$value) === '') $this->errors[$field] = "$label est obligatoire.";
        return $this;
    }
    public function email(string $field, mixed $value, string $label): self {
        if ((string)$value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) $this->errors[$field] = "$label n'est pas valide.";
        return $this;
    }
    public function numeric(string $field, mixed $value, string $label, float $min = 0): self {
        if (!is_numeric($value) || (float)$value < $min) $this->errors[$field] = "$label doit être un nombre supérieur ou égal à $min.";
        return $this;
    }
    public function errors(): array { return $this->errors; }
    public function fails(): bool { return $this->errors !== []; }
}
