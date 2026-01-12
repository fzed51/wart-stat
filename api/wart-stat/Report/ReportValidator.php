<?php

namespace WartStat\Report;

use Monolog\Logger;

class ReportValidator
{
    private array $validCountries =  ['US', 'GER', 'URRS', 'UK', 'JAP', 'CH', 'IT', 'FR', 'SU', 'IS'];
    
    private array $errors = [];

    public function safeValidate(?array $data): bool
    {
        $this->errors = [];

        if (empty($data)) {
             $this->errors['data'] = 'Les données sont requises';
             return false;
        }
        if (empty($data['country'])) {
            $this->errors['country'] = 'Le pays est requis';
        } elseif (!in_array($data['country'], $this->validCountries)) {
            $this->errors['country'] = 'Pays invalide';
        }

        if (empty($data['datetime'])) {
            $this->errors['datetime'] = 'La date/heure est requise';
        } else {
            try {
                new \DateTime($data['datetime']);
            } catch (\Throwable $th) {
                $this->errors['datetime'] = 'La date/heure n\'est pas valide';
            }
        }

        if (empty($data['content'])) {
            $this->errors['content'] = 'Le rapport est requis';
        }
        return count(array_keys($this->errors)) === 0;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function validate(?array $data): void
    {
        if (!$this->safeValidate($data)) {
            $messages = implode('; ', $this->errors);
            throw new \InvalidArgumentException("Validation failed: {$messages}");
        }
    }
}