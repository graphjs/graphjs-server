<?php

namespace GraphJS;

use Aws\Exception\MultipartUploadException;
use Aws\S3\Exception\S3Exception;
use Aws\S3\MultipartUploader;
use Aws\S3\ObjectUploader;
use Aws\S3\S3Client;

class S3Uploader
{
    private $s3Client;
    private $bucket;

    public function __construct(S3Client $s3Client, $bucket)
    {
        $this->s3Client = $s3Client;
        $this->bucket = $bucket;
    }

    public function upload($key, $source, $mime)
    {
        $currentAttempt = 0;
        $maxAttempts = 2;
        do {
            $currentAttempt++;
            $key = $this->generateKey($key);
            if (! $this->keyExists($key)) {
                $url = $this->uploadToS3($key, $source, $mime);
                return $url;
            }
        } while ($currentAttempt < $maxAttempts);

        return false;
    }

    public function generateKey($path)
    {
        $dirname = pathinfo($path, PATHINFO_DIRNAME);
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $key = join('/', array_merge(
            $dirname ? [ $dirname ] : [],
            [
                join('.', array_merge(
                    [ $filename . "-" . uniqid() ],
                    $extension ? [ $extension ] : []
                ))
            ]
        ));

        return $key;
    }

    public function keyExists($key)
    {
        try {
            $this->s3Client->headObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);
        } catch (S3Exception $ex) {
            if ($ex->getAwsErrorCode() === 'NotFound') {
                return false;
            }
            throw $ex;
        }

        return true;
    }

    public function uploadToS3($key, $source, $mime)
    {
        $uploader = new ObjectUploader(
            $this->s3Client,
            $this->bucket,
            $key,
            $source,
            'public-read',
            [
                'params' => [
                    'ContentType' => $mime,
                ],
            ]
        );

        do {
            try {
                $result = $uploader->upload();
                if ($result['@metadata']['statusCode'] == '200') {
                    return $result['ObjectURL'];
                }
            } catch (MultipartUploadException $ex) {
                rewind($source);
                $uploader = new MultipartUploader($this->s3Client, $source, [
                    'state' => $ex->getState(),
                ]);
            }
        } while (! isset($result));

        return false;
    }
}
