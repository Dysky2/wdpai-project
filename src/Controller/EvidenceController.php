<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\UnauthorizedException;
use App\Http\Response;
use App\Service\AuthService;
use App\Service\EvidenceService;
use App\View\ViewHelper;

/**
 * GET /uploads/{name}: evidence image for users allowed to open its case.
 *
 * PHP only decides access; nginx sends the bytes through X-Accel-Redirect to an `internal` location,
 * so the file is never reachable without this check. A guest gets 401 (not a redirect: the request
 * comes from an <img>, where a login page would only be a broken image).
 */
final class EvidenceController extends AbstractController
{
    /** nginx `internal` location that maps onto public/uploads/ (docker/nginx/nginx.conf). */
    public const INTERNAL_PREFIX = '/_protected/uploads/';

    public function __construct(
        ViewHelper $view,
        private readonly AuthService $auth,
        private readonly EvidenceService $evidence,
    ) {
        parent::__construct($view);
    }

    public function show(string $fileName): Response
    {
        $user = $this->auth->currentUser() ?? throw new UnauthorizedException();
        $file = $this->evidence->fileForUser($user, $fileName);

        return new Response('', 200, [
            'Content-Type' => $file->mimeType,
            'Cache-Control' => 'private, no-store',
            'X-Accel-Redirect' => self::INTERNAL_PREFIX . $file->fileName,
        ]);
    }
}
