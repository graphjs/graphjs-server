<?php

namespace GraphJS\Controllers;

use Aws\S3\S3Client;
use CapMousse\ReactRestify\Http\Request;
use CapMousse\ReactRestify\Http\Response;
use CapMousse\ReactRestify\Http\Session;
use GraphJS\S3Uploader;
use Pho\Kernel\Kernel;
use Riverline\MultiPartParser\StreamedPart;

class FileUploadController extends AbstractController
{
    private $s3Uploader = null;

    public function __construct()
    {
        parent::__construct();
        if($this->isS3Active())
            $this->s3Uploader = new S3Uploader($this->getS3Client(), getenv('AWS_S3_BUCKET'));
    }

    private function isS3Active(): bool
    {
        $key = getenv('AWS_KEY');
        $secret = getenv('AWS_SECRET');
        $region = getenv('AWS_REGION');
        $version = getenv('AWS_VERSION');
        return !(empty($key)||empty($secret)||empty($region)||empty($version));
    }

    public function getS3Client()
    {
        $key = getenv('AWS_KEY');
        $secret = getenv('AWS_SECRET');
        $region = getenv('AWS_REGION');
        $version = getenv('AWS_VERSION');

        $s3Client = new S3Client([
            'credentials' => [
                'key' => $key,
                'secret' => $secret,
            ],
            'region' => $region,
            'version' => $version,
        ]);

        return $s3Client;
    }

    public function upload(Request $request, Response $response, Session $session, Kernel $kernel)
    {
        if(is_null($id = $this->dependOnSession(...\func_get_args()))) {
            return;
        }
        $uuid = getenv('UUID');
        $httpRequest = $request->httpRequest;
        $contentType = $httpRequest->getHeader('content-type');
        $content = $request->getContent();
        $requestData = "content-type:" . current($contentType) . "\n\n" . $content;
        $stream = fopen('php://temp', 'rw');
        fwrite($stream, $requestData);
        rewind($stream);

        $document = new StreamedPart($stream);
        $allowedContentTypes = [ 'image/jpeg'=>"jpg", 'image/png'=>"png", 'image/gif'=>"gif" ];
        $urls = [];
        if ($document->isMultiPart()) {
            $parts = $document->getParts();
            foreach ($parts as $part) {
                $mime = $part->getMimeType();
                if (! ($part->isFile() && array_key_exists($mime, $allowedContentTypes))) {
                    continue;
                }

                $body = $part->getBody();
                //$filename = $part->getFileName();
                $filename = sprintf("%s-%s.%s", $id, (string) time(), $allowedContentTypes[$mime]);

                $key = "{$uuid}/{$filename}";
                $url = $this->s3Uploader->upload($key, $body, $mime);
                if ($url !== false) {
                    $urls[] = $url;
                }
            }

            return $this->succeed($response, [
                'urls' => $urls,
            ]);
        }
        else {
            return $this->fail($response);
        }
    }
}
