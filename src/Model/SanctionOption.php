<?php

declare(strict_types=1);

namespace App\Model;

/**
 * One sanction choice of the adjudication form. It is the single place that maps the form field
 * ("TIME_PENALTY:5", "REPRIMAND", ...) to a PenaltyType and a value; it feeds both the radio cards
 * and the validation of the submitted field.
 */
final class SanctionOption
{
    private function __construct(
        private readonly PenaltyType $type,
        private readonly ?int $value,
        private readonly string $title,
        private readonly string $text,
    ) {
    }

    /** @return list<self> in the order of the radio cards */
    public static function all(): array
    {
        return [
            new self(PenaltyType::TIME_PENALTY, 5, 'Kara 5 sekund', 'Doliczana do czasu wyścigu lub odbywana w boksie'),
            new self(PenaltyType::TIME_PENALTY, 10, 'Kara 10 sekund', 'Surowa kara czasowa w trakcie wyścigu'),
            new self(PenaltyType::GRID_DROP, 3, 'Cofnięcie o 3 miejsca', 'Stosowane na starcie następnego wyścigu'),
            new self(PenaltyType::DRIVE_THROUGH, null, 'Przejazd przez aleję', 'Wjazd do alei bez zatrzymania'),
            new self(PenaltyType::REPRIMAND, null, 'Formalna nagana', 'Ostrzeżenie dyscyplinarne bez kary czasowej'),
            new self(PenaltyType::DISQUALIFICATION, null, 'Dyskwalifikacja (DSQ)', 'Całkowite wykluczenie z klasyfikacji'),
        ];
    }

    /**
     * Parses and validates the "sanction" form field.
     *
     * @return ?self null when the field is not one of the offered sanctions
     */
    public static function fromField(string $field): ?self
    {
        foreach (self::all() as $option) {
            if ($option->toField() === $field) {
                return $option;
            }
        }

        return null;
    }

    /** The option that best matches an article preset (exact value first, then the same type), or null. */
    public static function forPreset(PenaltyType $type, ?int $value): ?self
    {
        $sameType = null;
        foreach (self::all() as $option) {
            if ($option->type !== $type) {
                continue;
            }
            if ($option->value === $value) {
                return $option;
            }
            $sameType ??= $option;
        }

        return $sameType;
    }

    /** Field value, e.g. "TIME_PENALTY:5" or "REPRIMAND". */
    public function toField(): string
    {
        return $this->type->value . ($this->value === null ? '' : ':' . $this->value);
    }

    public function getType(): PenaltyType { return $this->type; }
    public function getValue(): ?int { return $this->value; }
    public function getTitle(): string { return $this->title; }
    public function getText(): string { return $this->text; }

    /** TIME_PENALTY and GRID_DROP need a value greater than zero. */
    public function isValid(): bool
    {
        return !$this->type->requiresValue() || ($this->value !== null && $this->value > 0);
    }
}
