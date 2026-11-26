<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\NotFoundException;
use App\Model\User;
use App\Repository\EvidenceRepositoryInterface;
use App\Service\Upload\UploadService;

/**
 * Access to evidence images. A file is visible to exactly the users who may open its case:
 * the ownership rule is not repeated here, it is IncidentService::getForUser (steward = all cases,
 * team principal = cases their team reported or is accused in).
 */
final class EvidenceService
{
    public function __construct(
        private readonly EvidenceRepositoryInterface $evidence,
        private readonly IncidentService $incidents,
        private readonly UploadService $uploads,
    ) {
    }

    /**
     * @throws NotFoundException  when the name is not a stored file name, is unknown, or the file is missing on disk
     * @throws NotFoundException  also when the case belongs to another team
     */
    public function fileForUser(User $user, string $fileName): EvidenceFile
    {
        if (!$this->uploads->isStoredName($fileName)) {
            throw new NotFoundException('Evidence not found');
        }

        $evidence = $this->evidence->findByFilePath(UploadService::PUBLIC_PREFIX . $fileName)
            ?? throw new NotFoundException('Evidence not found');

        // Same rule as the case page: 404 both for a missing case and for another team's case.
        $this->incidents->getForUser($user, $evidence->getIncidentId());

        if (!$this->uploads->exists($fileName)) {
            throw new NotFoundException('Evidence file is missing on disk');
        }

        return new EvidenceFile($fileName, $this->uploads->mimeTypeOf($fileName));
    }
}
