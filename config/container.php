<?php

declare(strict_types=1);

use App\Core\ClockInterface;
use App\Core\FileLogger;
use App\Core\LoggerInterface;
use App\Core\Config;
use App\Core\Container;
use App\Core\Database;
use App\Core\SystemClock;
use App\Core\TransactionRunnerInterface;
use App\Http\Router;
use App\Http\Session;
use App\Service\AuthService;
use App\Service\Mail\MailerInterface;
use App\Service\Mail\MailSettings;
use App\Service\Mail\PhpMailerMailer;
use App\Service\Mail\RulingTemplateRenderer;
use App\Service\Upload\UploadService;
use App\View\ViewHelper;
use App\Repository\DriverRepositoryInterface;
use App\Repository\EvidenceRepositoryInterface;
use App\Repository\GrandPrixRepositoryInterface;
use App\Repository\IncidentDetailRepositoryInterface;
use App\Repository\IncidentListRepositoryInterface;
use App\Repository\IncidentRepositoryInterface;
use App\Repository\InfringementArticleRepositoryInterface;
use App\Repository\PdoDriverRepository;
use App\Repository\PdoEvidenceRepository;
use App\Repository\PdoGrandPrixRepository;
use App\Repository\PdoIncidentDetailRepository;
use App\Repository\PdoIncidentListRepository;
use App\Repository\PdoIncidentRepository;
use App\Repository\PdoInfringementArticleRepository;
use App\Repository\PdoPenaltyRepository;
use App\Repository\PdoStandingRepository;
use App\Repository\PdoUserRepository;
use App\Repository\PenaltyRepositoryInterface;
use App\Repository\StandingRepositoryInterface;
use App\Repository\PdoTeamRepository;
use App\Repository\TeamRepositoryInterface;
use App\Repository\UserRepositoryInterface;

$container = new Container();

// Interfaces are bound to implementations here; concrete controllers and services are autowired
// by Container from constructor type hints.
$container->singleton(Config::class, static fn (): Config => Config::fromEnvironment());
$container->singleton(Database::class, static fn (Container $c): Database => new Database($c->get(Config::class)));
$container->singleton(TransactionRunnerInterface::class, static fn (Container $c): object => $c->get(Database::class));
$container->singleton(ClockInterface::class, static fn (): ClockInterface => new SystemClock());
$container->singleton(LoggerInterface::class, static fn (): LoggerInterface => new FileLogger(dirname(__DIR__) . '/storage/logs/app.log'));
$container->singleton(MailSettings::class, static fn (Container $c): MailSettings => MailSettings::fromConfig($c->get(Config::class)));
$container->singleton(
    RulingTemplateRenderer::class,
    static fn (Container $c): RulingTemplateRenderer => new RulingTemplateRenderer(
        $c->get(ViewHelper::class),
        dirname(__DIR__) . '/public/views/emails',
    ),
);
$container->singleton(MailerInterface::class, static fn (Container $c): MailerInterface => $c->get(PhpMailerMailer::class));

$container->singleton(UserRepositoryInterface::class, static fn (Container $c): object => $c->get(PdoUserRepository::class));
$container->singleton(TeamRepositoryInterface::class, static fn (Container $c): object => $c->get(PdoTeamRepository::class));
$container->singleton(DriverRepositoryInterface::class, static fn (Container $c): object => $c->get(PdoDriverRepository::class));
$container->singleton(GrandPrixRepositoryInterface::class, static fn (Container $c): object => $c->get(PdoGrandPrixRepository::class));
$container->singleton(InfringementArticleRepositoryInterface::class, static fn (Container $c): object => $c->get(PdoInfringementArticleRepository::class));
$container->singleton(IncidentRepositoryInterface::class, static fn (Container $c): object => $c->get(PdoIncidentRepository::class));
$container->singleton(IncidentListRepositoryInterface::class, static fn (Container $c): object => $c->get(PdoIncidentListRepository::class));
$container->singleton(IncidentDetailRepositoryInterface::class, static fn (Container $c): object => $c->get(PdoIncidentDetailRepository::class));
$container->singleton(EvidenceRepositoryInterface::class, static fn (Container $c): object => $c->get(PdoEvidenceRepository::class));
$container->singleton(PenaltyRepositoryInterface::class, static fn (Container $c): object => $c->get(PdoPenaltyRepository::class));
$container->singleton(StandingRepositoryInterface::class, static fn (Container $c): object => $c->get(PdoStandingRepository::class));

// Autowired classes are not cached by Container, so stateful services are registered as singletons.
$container->singleton(Session::class, static fn (Container $c): Session => new Session($c->get(Config::class)));
$container->singleton(ViewHelper::class, static fn (Container $c): ViewHelper => new ViewHelper($c->get(Session::class)));
$container->singleton(
    AuthService::class,
    static fn (Container $c): AuthService => new AuthService($c->get(UserRepositoryInterface::class), $c->get(Session::class)),
);

$container->singleton(
    UploadService::class,
    static fn (): UploadService => new UploadService(dirname(__DIR__) . '/public/uploads'),
);

$container->singleton(Router::class, static fn (Container $c): Router => new Router($c));

return $container;
