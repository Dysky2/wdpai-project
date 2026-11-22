<?php

declare(strict_types=1);

namespace App\Service;

use App\Core\Translator;

/** Turns validation keys ("validation.lap_range" + params) into texts in the active language. */
final class ValidationMessages
{
    public function __construct(private readonly Translator $translator)
    {
    }

    /** @param array<string, scalar> $params */
    public function message(string $key, array $params = []): string
    {
        return $this->translator->translate($key, $params);
    }

    /**
     * @param array<string, array{key: string, params: array<string, scalar>}> $errors
     * @return array<string, string> field => text
     */
    public function messages(array $errors): array
    {
        return array_map(fn (array $error): string => $this->message($error['key'], $error['params']), $errors);
    }
}
