<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Model\IncidentStatus;
use App\Model\InfringementArticle;
use App\Model\PenaltyType;
use App\Model\SanctionOption;
use App\Service\AdjudicationForm;
use App\Tests\Support\Fixtures;
use PHPUnit\Framework\TestCase;

/** Article presets of the adjudication form are resolved here, not in the template. */
final class AdjudicationFormTest extends TestCase
{
    /** @param list<InfringementArticle> $articles */
    private function form(array $articles): AdjudicationForm
    {
        $sanctions = SanctionOption::all();

        return new AdjudicationForm(
            Fixtures::incidentDetail(42, IncidentStatus::UNDER_INVESTIGATION),
            $articles,
            $sanctions,
            $sanctions[0],
            2,
            4,
            6,
        );
    }

    public function testArticleOptionCarriesItsPreset(): void
    {
        $options = $this->form([Fixtures::article(7)])->getArticleOptions();

        self::assertCount(1, $options);
        self::assertSame(7, $options[0]['id']);
        self::assertSame('Art. 33.4', $options[0]['reference']);
        self::assertSame('Collision', $options[0]['title']);
        self::assertSame('TIME_PENALTY:5', $options[0]['sanction']);
        self::assertSame(2, $options[0]['points']);
        self::assertSame('Precedent', $options[0]['wording']);
    }

    public function testPresetWithoutExactValueFallsBackToTheSameType(): void
    {
        $article = new InfringementArticle(3, '1.1', 'Track limits', 'Art. 1.1', PenaltyType::TIME_PENALTY, 20, 1, '');

        self::assertSame('TIME_PENALTY:5', $this->form([$article])->getArticleOptions()[0]['sanction']);
    }

    public function testNoArticlesGiveNoOptions(): void
    {
        self::assertSame([], $this->form([])->getArticleOptions());
    }
}
