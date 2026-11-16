<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Turns validation keys into Polish texts. Temporary stand-in until the PL/EN dictionaries (S5-24b)
 * take over; the keys already match the planned `validation.*` dictionary entries.
 */
final class ValidationMessages
{
    private const PL = [
        'validation.grand_prix_unknown' => 'Wybierz istniejącą sesję Grand Prix.',
        'validation.lap_range' => 'Numer okrążenia musi mieścić się w zakresie 1–:max.',
        'validation.accused_unknown' => 'Wybierz oskarżonego kierowcę z listy.',
        'validation.accused_own_team' => 'Nie można oskarżyć kierowcy własnego zespołu.',
        'validation.affected_unknown' => 'Wybierz kierowcę własnego zespołu albo pozostaw pole puste.',
        'validation.affected_not_own' => 'Zaangażowany kierowca musi pochodzić z Twojego zespołu.',
        'validation.affected_same_as_accused' => 'Zaangażowany kierowca musi być inny niż oskarżony.',
        'validation.article_unknown' => 'Wybierz rodzaj naruszenia z listy.',
        'validation.description_length' => 'Opis musi mieć od :min do :max znaków.',
        'validation.evidence_too_large' => 'Plik jest za duży (maks. :max MB).',
        'validation.evidence_type' => 'Dozwolone są wyłącznie prawdziwe obrazy JPG lub PNG.',
        'validation.evidence_failed' => 'Nie udało się wgrać pliku. Spróbuj ponownie.',
    ];

    /** @param array<string, scalar> $params */
    public function message(string $key, array $params = []): string
    {
        $text = self::PL[$key] ?? $key;
        foreach ($params as $name => $value) {
            $text = str_replace(':' . $name, (string) $value, $text);
        }

        return $text;
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
