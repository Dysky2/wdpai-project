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
    ) {
    }

    /** @return list<self> in the order of the radio cards */
    public static function all(): array
    {
        return [
            new self(PenaltyType::TIME_PENALTY, 5),
            new self(PenaltyType::TIME_PENALTY, 10),
            new self(PenaltyType::GRID_DROP, 3),
            new self(PenaltyType::DRIVE_THROUGH, null),
            new self(PenaltyType::REPRIMAND, null),
            new self(PenaltyType::DISQUALIFICATION, null),
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
    /** Dictionary key of the radio card title, e.g. "sanction.TIME_PENALTY_5.title". */
    public function getTitleKey(): string
    {
        return $this->key() . '.title';
    }

    /** Dictionary key of the radio card description. */
    public function getTextKey(): string
    {
        return $this->key() . '.text';
    }

    private function key(): string
    {
        return 'sanction.' . $this->type->value . ($this->value === null ? '' : '_' . $this->value);
    }

    /** TIME_PENALTY and GRID_DROP need a value greater than zero. */
    public function isValid(): bool
    {
        return !$this->type->requiresValue() || ($this->value !== null && $this->value > 0);
    }
}
