<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\LoginThrottle;
use App\Tests\Support\FixedClock;
use App\Tests\Support\InMemoryLoginAttemptRepository;
use PHPUnit\Framework\TestCase;

final class LoginThrottleTest extends TestCase
{
    private FixedClock $clock;
    private LoginThrottle $throttle;

    protected function setUp(): void
    {
        $this->clock = new FixedClock();
        $this->throttle = new LoginThrottle(new InMemoryLoginAttemptRepository(), $this->clock);
    }

    private function recordFailures(int $times, string $email = 'toto@mercedes.f1'): void
    {
        for ($i = 0; $i < $times; $i++) {
            $this->throttle->recordFailure($email, '127.0.0.1');
        }
    }

    public function testNotLockedBelowTheLimit(): void
    {
        $this->recordFailures(LoginThrottle::MAX_ATTEMPTS - 1);

        self::assertFalse($this->throttle->isLocked('toto@mercedes.f1'));
        self::assertSame(0, $this->throttle->minutesUntilUnlock('toto@mercedes.f1'));
    }

    public function testLockedAtTheLimit(): void
    {
        $this->recordFailures(LoginThrottle::MAX_ATTEMPTS);

        self::assertTrue($this->throttle->isLocked('toto@mercedes.f1'));
        self::assertSame(LoginThrottle::WINDOW_MINUTES, $this->throttle->minutesUntilUnlock('toto@mercedes.f1'));
    }

    public function testEmailKeyIgnoresCaseAndWhitespace(): void
    {
        $this->recordFailures(LoginThrottle::MAX_ATTEMPTS, 'Toto@Mercedes.F1');

        self::assertTrue($this->throttle->isLocked('  toto@mercedes.f1 '));
    }

    public function testOtherEmailsAreNotAffected(): void
    {
        $this->recordFailures(LoginThrottle::MAX_ATTEMPTS);

        self::assertFalse($this->throttle->isLocked('steward@fia.org'));
    }

    public function testLockExpiresAfterTheWindow(): void
    {
        $this->recordFailures(LoginThrottle::MAX_ATTEMPTS);
        $this->clock->advanceMinutes(LoginThrottle::WINDOW_MINUTES + 1);

        self::assertFalse($this->throttle->isLocked('toto@mercedes.f1'));
    }

    public function testRemainingMinutesShrinkOverTime(): void
    {
        $this->recordFailures(LoginThrottle::MAX_ATTEMPTS);
        $this->clock->advanceMinutes(4);

        self::assertSame(LoginThrottle::WINDOW_MINUTES - 4, $this->throttle->minutesUntilUnlock('toto@mercedes.f1'));
    }

    public function testResetClearsTheCounter(): void
    {
        $this->recordFailures(LoginThrottle::MAX_ATTEMPTS - 1);
        $this->throttle->reset('toto@mercedes.f1');
        $this->recordFailures(LoginThrottle::MAX_ATTEMPTS - 1);

        self::assertFalse($this->throttle->isLocked('toto@mercedes.f1'));
    }

    public function testUnknownAccountsAreThrottledToo(): void
    {
        $this->recordFailures(LoginThrottle::MAX_ATTEMPTS, 'nobody@nowhere.test');

        self::assertTrue($this->throttle->isLocked('nobody@nowhere.test'));
    }
}
