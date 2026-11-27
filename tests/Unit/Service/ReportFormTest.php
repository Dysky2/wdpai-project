<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\ReportForm;
use App\Tests\Support\Fixtures;
use PHPUnit\Framework\TestCase;

/** View data of the protest form, computed outside the template. */
final class ReportFormTest extends TestCase
{
    private function form(bool $withActiveGrandPrix): ReportForm
    {
        $grandPrix = Fixtures::grandPrix(4, 78);

        return new ReportForm(
            Fixtures::team(),
            'Toto Wolff',
            $withActiveGrandPrix ? [$grandPrix] : [],
            $withActiveGrandPrix ? $grandPrix : null,
            [],
            [],
            [],
        );
    }

    public function testActiveGrandPrixIsPreselectedAndBoundsTheLap(): void
    {
        $form = $this->form(true);

        self::assertSame('4', $form->selectedGrandPrixId(''));
        self::assertSame(78, $form->getMaxLap());
    }

    public function testSubmittedValueWinsAfterAnError(): void
    {
        self::assertSame('9', $this->form(true)->selectedGrandPrixId('9'));
    }

    public function testWithoutActiveGrandPrixNothingIsPreselected(): void
    {
        $form = $this->form(false);

        self::assertSame('', $form->selectedGrandPrixId(''));
        self::assertSame(0, $form->getMaxLap());
    }
}
