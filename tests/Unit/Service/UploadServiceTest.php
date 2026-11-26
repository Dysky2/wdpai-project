<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\Upload\InvalidUploadException;
use App\Service\Upload\UploadService;
use App\Tests\Support\FakeUploadedFileHandler;
use PHPUnit\Framework\TestCase;

final class UploadServiceTest extends TestCase
{
    private string $dir;
    private UploadService $service;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/apex_upload_' . bin2hex(random_bytes(4));
        mkdir($this->dir . '/target', 0777, true);
        $this->service = new UploadService($this->dir . '/target', new FakeUploadedFileHandler());
    }

    protected function tearDown(): void
    {
        foreach (array_merge(glob($this->dir . '/target/*') ?: [], glob($this->dir . '/*') ?: []) as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($this->dir . '/target');
        rmdir($this->dir);
    }

    private function tmpFile(string $content): string
    {
        $path = $this->dir . '/' . bin2hex(random_bytes(4)) . '.tmp';
        file_put_contents($path, $content);

        return $path;
    }

    private function png(): string
    {
        ob_start();
        imagepng(imagecreatetruecolor(8, 8));

        return (string) ob_get_clean();
    }

    private function jpeg(): string
    {
        ob_start();
        imagejpeg(imagecreatetruecolor(8, 8));

        return (string) ob_get_clean();
    }

    /** @return array{name: string, tmp_name: string, error: int, size: int} */
    private function upload(string $path, string $name): array
    {
        return ['name' => $name, 'tmp_name' => $path, 'error' => UPLOAD_ERR_OK, 'size' => (int) filesize($path)];
    }

    private function rejection(array $file): InvalidUploadException
    {
        try {
            $this->service->store($file);
        } catch (InvalidUploadException $e) {
            return $e;
        }
        self::fail('InvalidUploadException expected');
    }

    public function testValidPngIsStoredUnderRandomName(): void
    {
        $path = $this->tmpFile($this->png());

        $stored = $this->service->store($this->upload($path, 'onboard.png'));

        self::assertSame('image/png', $stored->mimeType);
        self::assertSame('onboard.png', $stored->originalName);
        self::assertMatchesRegularExpression('~^/uploads/[0-9a-f]{32}\.png$~', $stored->filePath);
        self::assertFileExists($this->dir . '/target/' . basename($stored->filePath));
        self::assertSame(64, strlen($stored->sha256));
    }

    public function testValidJpegGetsJpgExtension(): void
    {
        $stored = $this->service->store($this->upload($this->tmpFile($this->jpeg()), 'frame.jpeg'));

        self::assertSame('image/jpeg', $stored->mimeType);
        self::assertStringEndsWith('.jpg', $stored->filePath);
    }

    public function testClientSuppliedNameNeverReachesTheStoredPath(): void
    {
        $stored = $this->service->store($this->upload($this->tmpFile($this->png()), '../../evil.php.png'));

        self::assertStringNotContainsString('evil', $stored->filePath);
        self::assertSame('evil.php.png', $stored->originalName, 'only the basename is kept as a label');
    }

    public function testPhpScriptNamedJpgIsRejected(): void
    {
        $path = $this->tmpFile('<?php system($_GET["c"]);');

        $error = $this->rejection($this->upload($path, 'shell.jpg'));

        self::assertSame('validation.evidence_type', $error->getErrorKey());
        self::assertSame([], glob($this->dir . '/target/*') ?: []);
    }

    public function testPhpScriptHiddenBehindImageHeaderIsRejected(): void
    {
        // finfo says image/png (magic bytes) but the file does not decode as an image.
        $path = $this->tmpFile("\x89PNG\r\n\x1a\n<?php echo 1;");

        $error = $this->rejection($this->upload($path, 'trick.png'));

        self::assertSame('validation.evidence_type', $error->getErrorKey());
    }

    public function testDisallowedMimeTypeIsRejected(): void
    {
        $path = $this->tmpFile('GIF89a' . str_repeat("\0", 32));

        $error = $this->rejection($this->upload($path, 'anim.png'));

        self::assertSame('validation.evidence_type', $error->getErrorKey());
    }

    public function testFileAboveFiveMegabytesIsRejected(): void
    {
        $path = $this->tmpFile($this->png() . str_repeat("\0", UploadService::MAX_BYTES));

        $error = $this->rejection($this->upload($path, 'huge.png'));

        self::assertSame('validation.evidence_too_large', $error->getErrorKey());
        self::assertSame(['max' => 5], $error->getParams());
    }

    public function testIniSizeErrorIsReportedAsTooLarge(): void
    {
        $error = $this->rejection(['name' => 'a.png', 'tmp_name' => '', 'error' => UPLOAD_ERR_INI_SIZE, 'size' => 0]);

        self::assertSame('validation.evidence_too_large', $error->getErrorKey());
    }

    public function testEmptyFileIsRejected(): void
    {
        $error = $this->rejection($this->upload($this->tmpFile(''), 'empty.png'));

        self::assertSame('validation.evidence_failed', $error->getErrorKey());
    }

    public function testStoredFileCanBeLocatedByItsName(): void
    {
        $stored = $this->service->store($this->upload($this->tmpFile($this->jpeg()), 'onboard.jpg'));
        $name = basename($stored->filePath);

        self::assertTrue($this->service->isStoredName($name));
        self::assertTrue($this->service->exists($name));
        self::assertSame('image/jpeg', $this->service->mimeTypeOf($name));
    }

    public function testForeignNameIsNotAStoredFile(): void
    {
        file_put_contents($this->dir . '/target/shell.php', '<?php');

        self::assertFalse($this->service->isStoredName('shell.php'));
        self::assertFalse($this->service->exists('shell.php'));
        self::assertFalse($this->service->exists('../target/shell.php'));
        $this->expectException(\InvalidArgumentException::class);
        $this->service->mimeTypeOf('shell.php');
    }

    public function testDeleteRemovesAStoredFile(): void
    {
        $stored = $this->service->store($this->upload($this->tmpFile($this->png()), 'a.png'));

        $this->service->delete($stored);

        self::assertSame([], glob($this->dir . '/target/*') ?: []);
    }
}
