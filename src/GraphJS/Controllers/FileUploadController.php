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
        $max_upload_size = getenv('MAX_UPLOAD_SIZE') ?? "20M";
        @ini_set("upload_max_filesize", $max_upload_size);
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
        $allowedContentTypes = [
            "image/jpeg" => "jpg",
            "image/png" => "png",
            "image/gif" => "gif",
            "video/mp4" => "mp4",
            "video/mpeg" => "mpeg",
            "video/avi" => "avi",
            "video/flv" => "flv",
            "video/wmv" => "wmv",
            "application/pdf" => "pdf",
            "application/msword" => "doc",
            "application/msword" => "dot",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document" => "docx",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.template" => "dotx",
            "application/vnd.ms-word.document.macroEnabled.12" => "docm",
            "application/vnd.ms-word.template.macroEnabled.12" => "dotm",
            "application/vnd.ms-excel" => "xls",
            "application/vnd.ms-excel" => "xlt",
            "application/vnd.ms-excel" => "xla",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" => "xlsx",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.template" => "xltx",
            "application/vnd.ms-excel.sheet.macroEnabled.12" => "xlsm",
            "application/vnd.ms-excel.template.macroEnabled.12" => "xltm",
            "application/vnd.ms-excel.addin.macroEnabled.12" => "xlam",
            "application/vnd.ms-excel.sheet.binary.macroEnabled.12" => "xlsb",
            "application/vnd.ms-powerpoint" => "ppt",
            "application/vnd.ms-powerpoint" => "pot",
            "application/vnd.ms-powerpoint" => "pps",
            "application/vnd.ms-powerpoint" => "ppa",
            "application/vnd.openxmlformats-officedocument.presentationml.presentation" => "pptx",
            "application/vnd.openxmlformats-officedocument.presentationml.template" => "potx",
            "application/vnd.openxmlformats-officedocument.presentationml.slideshow" => "ppsx",
            "application/vnd.ms-powerpoint.addin.macroEnabled.12" => "ppam",
            "application/vnd.ms-powerpoint.presentation.macroEnabled.12" => "pptm",
            "application/vnd.ms-powerpoint.template.macroEnabled.12" => "potm",
            "application/vnd.ms-powerpoint.slideshow.macroEnabled.12" => "ppsm",
            "application/vnd.ms-access" => "mdb",
        ];
        $uploads = [];
        if ($document->isMultiPart()) {
            $parts = $document->getParts();
            foreach ($parts as $part) {
                $mime = $part->getMimeType();
                if (! ($part->isFile() && array_key_exists($mime, $allowedContentTypes))) {
                    continue;
                }

                $body = $part->getBody();
                $originalFilename = $part->getFileName();
                $filename = sprintf("%s-%s.%s", $id, (string) time(), $allowedContentTypes[$mime]);

                $key = strtolower("{$uuid}/{$filename}");
                $url = $this->s3Uploader->upload($key, $body, $mime);

                $previewUrl = null;

                if (substr($mime, 0, strlen('video/')) === 'video/') {
                    $previewKey = "{$key}-preview.jpg";
                    $previewMime = 'image/jpeg';
                    try {
                        $videoFile = $this->getTempFile();
                        file_put_contents($videoFile, $body);
                        $frameFile = $this->getTempFile();
                        $this->saveFrame($videoFile, $frameFile);

                        $resizedFrameFile = $this->getTempFile();
                        $this->resizeImage($frameFile, $resizedFrameFile, 600, 600);
                        $previewUrl = $this->s3Uploader->upload($previewKey, file_get_contents($resizedFrameFile), $previewMime, false);
                    }
                    catch (\Exception $ex) {
                        error_log('error occurred during preview image generation');
                        error_log($ex);
                    }
                }
                if ($url !== false) {
                    $bytes = strlen($body);
                    $humanFileSize = $this->human_filesize($bytes);
                    $uploads[] = [
                        'url' => $url,
                        'filetype' => $mime,
                        'original_filename' => $originalFilename,
                        'filesize' => $bytes,
                        'human_filesize' => $humanFileSize,
                        'preview_url' => $previewUrl,
                    ];
                }
            }

            return $this->succeed($response, [
                'uploads' => $uploads,
            ]);
        }
        else {
            return $this->fail($response);
        }
    }

    // Ref: https://www.php.net/manual/en/function.filesize.php#106569
    public function human_filesize($bytes, $decimals = 2) {
        $sz = 'BKMGTP';
        $factor = (int) floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }

    public function getTempFile()
    {
        $tmp = tmpfile();
        $tempFile = stream_get_meta_data($tmp)['uri'];
        return $tempFile;
    }

    public function saveFrame($videoFile, $frameFile)
    {
        $ffmpeg = \FFMpeg\FFMpeg::create();
        $ffprobe = \FFMpeg\FFProbe::create();
        $video = $ffmpeg->open($videoFile);
        $duration = (int) $ffprobe
            ->format($videoFile)
            ->get('duration');

        $frameAt = 10;
        if ($duration < 20) {
            $frameAt = (int) ($duration / 2);
        }
        $video
            ->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds($frameAt))
            ->save($frameFile);
    }

    /**
     * @see https://gist.github.com/janzikan/2994977
     *
     * Resize image - preserve ratio of width and height.
     * @param string $sourceImage path to source JPEG image
     * @param string $targetImage path to final JPEG image file
     * @param int $maxWidth maximum width of final image (value 0 - width is optional)
     * @param int $maxHeight maximum height of final image (value 0 - height is optional)
     * @param int $quality quality of final image (0-100)
     * @return bool
     */
    function resizeImage($sourceImage, $targetImage, $maxWidth, $maxHeight, $quality = 80)
    {
        // Obtain image from given source file.
        if (!$image = @imagecreatefromjpeg($sourceImage))
        {
            return false;
        }

        // Get dimensions of source image.
        list($origWidth, $origHeight) = getimagesize($sourceImage);

        if ($maxWidth == 0)
        {
            $maxWidth  = $origWidth;
        }

        if ($maxHeight == 0)
        {
            $maxHeight = $origHeight;
        }

        // Calculate ratio of desired maximum sizes and original sizes.
        $widthRatio = $maxWidth / $origWidth;
        $heightRatio = $maxHeight / $origHeight;

        // Ratio used for calculating new image dimensions.
        $ratio = min($widthRatio, $heightRatio);

        // Calculate new image dimensions.
        $newWidth  = (int)$origWidth  * $ratio;
        $newHeight = (int)$origHeight * $ratio;

        // Create final image with new dimensions.
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
        imagejpeg($newImage, $targetImage, $quality);

        // Free up the memory.
        imagedestroy($image);
        imagedestroy($newImage);

        return true;
    }
}
