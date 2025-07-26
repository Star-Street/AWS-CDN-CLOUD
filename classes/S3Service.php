<?php
if (!defined('_PS_VERSION_')) exit;

require dirname(__FILE__) . "/../libs/aws-sdk-php/aws-autoloader.php";
require dirname(__FILE__) . "/LogService.php";
require dirname(__FILE__) . "/TextHealper.php";
require dirname(__FILE__) . "/FileService.php";
require dirname(__FILE__) . "/upload/UploadService.php";
require dirname(__FILE__) . "/upload/DefaultUploader.php";
require dirname(__FILE__) . "/upload/ManufacturerUploader.php";
require dirname(__FILE__) . "/upload/CategoryUploader.php";
require dirname(__FILE__) . "/upload/ProductUploader.php";
require dirname(__FILE__) . "/delete/DeleteService.php";
require dirname(__FILE__) . "/delete/ProductDeleter.php";
require dirname(__FILE__) . "/delete/CategoryDeleter.php";
require dirname(__FILE__) . "/delete/ManufacturerDeleter.php";

use Aws\S3\S3Client;
use Aws\Credentials\Credentials;
use Aws\Exception\AwsException;

/**
 * Uses AWS SDK and S3 for PHP 
 * (3.278.0 - 2023-08-10)
 * 
 * https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html
 * 
 * @author Alexey (ZAV) <test@mail.ru>
 * @version 1.0.0
 */
class S3Service 
{
    private $s3Client;
    private $bucketName;

    public function __construct($bucketName, $accessKey, $secretKey, $region, $endpointUrl) 
    {
        $this->bucketName = $bucketName;
        $credentials = new Credentials($accessKey, $secretKey);

        //* https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.S3.S3Client.html
        $this->s3Client = new S3Client([
            "version" => "latest",
            "region" => $region,
            "credentials" => $credentials,
            "endpoint" => $endpointUrl,
            "use_path_style_endpoint" => true,
        ]);
    }

    public function deleteFolderFile(string $folderPath = "") 
    {
        $result = [];
        $objects = $this->listObjects($folderPath);

        if (!empty($objects)) {
            $deleteObjects = array_column($objects, "Key");
            $result = $this->deleteObjects($deleteObjects);
        }

        return $result;
    }

    public function uploadObject(string $key, string $sourceFile, array $metadata = []) 
    {
        //* https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html#putobject
        //? кеш хранится 24 часа, затем CDN обновляет изображение
        return $this->s3Client->putObject([
            "Bucket" => $this->bucketName,
            "Key" => $key,
            "SourceFile" => $sourceFile,
            "ACL" => "public-read",
            "ContentType" => "image/jpg",
            "CacheControl" => "public, max-age=86400, immutable",
            "Expires" => gmdate("D, d M Y H:i:s T", strtotime("+24 hours")),
            "Metadata" => $metadata,
        ]);
    }

    public function deleteObjects(array $keys) 
    {
        //* https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html#deleteobjects
        return $this->s3Client->deleteObjects([
            "Bucket" => $this->bucketName,
            "Delete" => [
                "Objects" => array_map(function($key) {
                    return ["Key" => $key];
                }, $keys),
                "Quiet" => true,
            ],
        ]);
    }

    public function listObjects(string $prefix = ""): array 
    {
        $objects = [];
        $isTruncated = true;
        $continuationToken = null;

        while ($isTruncated) {
            //* https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html#listobjectsv2
            $result = $this->s3Client->listObjectsV2([
                "Bucket" => $this->bucketName,
                "Prefix" => $prefix,
                "MaxKeys" => 1000,
                "ContinuationToken" => $continuationToken,
            ]);

            if (isset($result["Contents"])) {
                foreach ($result["Contents"] as $object) {
                    $objects[] = $object;
                }
            }

            $isTruncated = $result["IsTruncated"] ?? false;
            $continuationToken = $result["NextContinuationToken"] ?? null;
        }

        return $objects;
    }

    public function testConnection(): array 
    {
        $objects = [];
        $objects["content"] = [];
        $objects["error"] = [];

        try {
            //* https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html#listobjectsv2
            $result = $this->s3Client->listObjectsV2([
                "Bucket" => $this->bucketName,
                "Prefix" => "",
                "MaxKeys" => 5,
            ]);

            if (isset($result["Contents"])) {
                foreach ($result["Contents"] as $object) {
                    $objects["content"][] = $object;
                }
            }
        } catch (\Exception $e) {
            //? the response contains both a string and an xml code
            //? we need read xml code
            if (preg_match('/(<Error>.*?<\/Error>)/s', $e->getMessage(), $matches)) {
                $xmlString = $matches[1];
                $xml = simplexml_load_string($xmlString);

                if ($xml && isset($xml->Code)) {
                    $objects["error"] = "Error: " . (string) $xml->Code . "...";
                } else {
                    $objects["error"] = "Error: request is not correct...";
                }
            } else {
                $objects["error"] = "Error: request is not correct...";
            }
        }

        return $objects;
    }
}
