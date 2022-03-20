<?php

namespace Larva\Flysystem\Qiniu;

use Exception;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperationFailed;
use League\Flysystem\InvalidVisibilityProvided;
use League\Flysystem\PathPrefixer;
use League\Flysystem\UnableToCheckExistence;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToWriteFile;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use League\MimeTypeDetection\MimeTypeDetector;
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use Throwable;

/**
 * 七牛适配器
 */
class QiniuKodoAdapter implements FilesystemAdapter
{
    /**
     * @var Auth
     */
    private Auth $auth;

    /**
     * @var BucketManager
     */
    private BucketManager $bucketManager;

    /**
     * @var UploadManager
     */
    private UploadManager $uploadManager;

    /**
     * @var PathPrefixer
     */
    private PathPrefixer $prefixer;

    /**
     * @var string
     */
    private string $bucket;

    /**
     * @var VisibilityConverter
     */
    private VisibilityConverter $visibility;

    /**
     * @var MimeTypeDetector
     */
    private MimeTypeDetector $mimeTypeDetector;

    /**
     * @var array
     */
    private array $options;

    /**
     * Adapter constructor.
     *
     * @param Auth $auth
     * @param string $bucket
     * @param string $prefix
     * @param VisibilityConverter|null $visibility
     * @param MimeTypeDetector|null $mimeTypeDetector
     * @param array $options
     */
    public function __construct(Auth $auth, string $bucket, string $prefix = '', VisibilityConverter $visibility = null, MimeTypeDetector $mimeTypeDetector = null, array $options = [])
    {
        $this->auth = $auth;
        $this->prefixer = new PathPrefixer($prefix);
        $this->bucketManager = new BucketManager($this->auth);
        $this->uploadManager = new UploadManager();
        $this->bucket = $bucket;
        $this->visibility = $visibility ?: new PortableVisibilityConverter();
        $this->mimeTypeDetector = $mimeTypeDetector ?: new FinfoMimeTypeDetector();
        $this->options = $options;
    }

    /**
     * 判断文件是否存在
     *
     * @throws FilesystemException
     * @throws UnableToCheckExistence
     */
    public function fileExists(string $path): bool
    {
        // TODO: Implement fileExists() method.
    }

    /**
     * 判断文件夹是否存在
     *
     * @throws FilesystemException
     * @throws UnableToCheckExistence
     */
    public function directoryExists(string $path): bool
    {
        // TODO: Implement directoryExists() method.
    }

    /**
     * @throws UnableToWriteFile
     * @throws FilesystemException
     */
    public function write(string $path, string $contents, Config $config): void
    {
        // TODO: Implement write() method.
    }

    /**
     * @param resource $contents
     *
     * @throws UnableToWriteFile
     * @throws FilesystemException
     */
    public function writeStream(string $path, $contents, Config $config): void
    {
        // TODO: Implement writeStream() method.
    }

    /**
     * @throws UnableToReadFile
     * @throws FilesystemException
     */
    public function read(string $path): string
    {
        // TODO: Implement read() method.
    }

    /**
     * @return resource
     *
     * @throws UnableToReadFile
     * @throws FilesystemException
     */
    public function readStream(string $path)
    {
        // TODO: Implement readStream() method.
    }

    /**
     * @throws UnableToDeleteFile
     * @throws FilesystemException
     */
    public function delete(string $path): void
    {
        // TODO: Implement delete() method.
    }

    /**
     * @throws UnableToDeleteDirectory
     * @throws FilesystemException
     */
    public function deleteDirectory(string $path): void
    {
        // TODO: Implement deleteDirectory() method.
    }

    /**
     * @throws UnableToCreateDirectory
     * @throws FilesystemException
     */
    public function createDirectory(string $path, Config $config): void
    {
        // TODO: Implement createDirectory() method.
    }

    /**
     * @throws InvalidVisibilityProvided
     * @throws FilesystemException
     */
    public function setVisibility(string $path, string $visibility): void
    {
        // TODO: Implement setVisibility() method.
    }

    /**
     * @throws UnableToRetrieveMetadata
     * @throws FilesystemException
     */
    public function visibility(string $path): FileAttributes
    {
        // TODO: Implement visibility() method.
    }

    /**
     * @throws UnableToRetrieveMetadata
     * @throws FilesystemException
     */
    public function mimeType(string $path): FileAttributes
    {
        // TODO: Implement mimeType() method.
    }

    /**
     * @throws UnableToRetrieveMetadata
     * @throws FilesystemException
     */
    public function lastModified(string $path): FileAttributes
    {
        // TODO: Implement lastModified() method.
    }

    /**
     * @throws UnableToRetrieveMetadata
     * @throws FilesystemException
     */
    public function fileSize(string $path): FileAttributes
    {
        // TODO: Implement fileSize() method.
    }

    /**
     * @return iterable<StorageAttributes>
     *
     * @throws FilesystemException
     */
    public function listContents(string $path, bool $deep): iterable
    {
        // TODO: Implement listContents() method.
    }

    /**
     * 移动对象到新的位置
     *
     * @throws UnableToMoveFile
     * @throws FilesystemException
     */
    public function move(string $source, string $destination, Config $config): void
    {
        try {
            $this->copy($source, $destination, $config);
            $this->delete($source);
        } catch (FilesystemOperationFailed $exception) {
            throw UnableToMoveFile::fromLocationTo($source, $destination, $exception);
        }
    }

    /**
     * 复制对象到新的位置
     *
     * @throws UnableToCopyFile
     * @throws FilesystemException
     */
    public function copy(string $source, string $destination, Config $config): void
    {
        try {
            [, $error] = $this->bucketManager->copy($this->bucket, $this->prefixer->prefixPath($source), $this->bucket, $this->prefixer->prefixPath($destination), true);
            if ($error != null) {
                throw new Exception($error->message(), $error->code);
            }
        } catch (Throwable $exception) {
            throw UnableToCopyFile::fromLocationTo($source, $destination, $exception);
        }
    }
}
