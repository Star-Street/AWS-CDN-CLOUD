<?php

abstract class UploadService 
{
    protected $s3Service;
    protected $fileService;
    protected $logger;
    protected $shopName;

    public function __construct(S3Service $s3Service, LogService $logger, FileService $fileService) {
        $this->s3Service = $s3Service;
        $this->logger = $logger;
        $this->fileService = $fileService;
        $this->shopName = Configuration::get("PS_SHOP_NAME");
    }

    abstract protected function getAllImages($entityIds = null, bool $needRecreateImages): array;
    abstract protected function getImage(int $imageId, bool $needRecreateImages): array;

    public function uploadAll($entityIds = null, bool $needRecreateImages = true, bool $needDeleteFileServerAfterUpload = false): array {
        $images = [];
        $result = [];

        $images = $this->getAllImages($entityIds, $needRecreateImages);
        $result = ["files" => array_sum(array_map("count", $images)), "uploaded" => 0, "deleted" => 0];
        // return $images; //* debug

        foreach ($images as $index => $elem) {
            foreach ($elem as $image) {
                $time = date("H:i");

                try {
                    $object = $this->s3Service->uploadObject(
                        $image["location"],
                        $image["path"],
                        [
                            "author" => $this->shopName,
                            // "description" => $image["legend"] ?? "", //! show some error sometimes
                            "link" => $image["url"] ?? ""
                        ]
                    );

                    if (isset($object["@metadata"]) && $object["@metadata"]["statusCode"] == 200) {
                        $result["uploaded"]++;
                        $this->logger->log("upload", "Time: {$time} | UploadAll: {$result['uploaded']}/{$result['files']} | ID: $index | Target: CDN | Status: {$object['@metadata']['statusCode']} | Image: {$image['location']}");

                        if ($needDeleteFileServerAfterUpload) {
                            if ($this->fileService->deleteFile($image["path"])) {
                                $result["deleted"]++;
                                $this->logger->log("delete", "Time: {$time} | UploadAll: {$result['deleted']}/{$result['files']} | ID: $index | Target: Server | Status: 200 | Image: {$image['path']}");
                            } else {
                                $this->logger->log("error", "Time: {$time} | UploadAll: {$result['deleted']}/{$result['files']} | ID: $index | Target: Server | Status: 400 | Image: {$image['path']}: Failed to delete file...");
                            }
                        }
                    } else {
                        $this->logger->log("error", "Time: {$time} | UploadAll: {$result['uploaded']}/{$result['files']} | ID: $index | Target: CDN | Status: 400 | Image: {$image['location']}: Failed to upload file...");
                    }
                } catch (\Exception $e) {
                    $this->logger->log("error", "Time: {$time} | UploadAll: {$result['uploaded']}/{$result['files']} | ID: $index | Target: CDN | Status: 400 | Image: {$image['location']}: {$e->getMessage()}");
                }
            }
        }

        return $result;
    }

    public function uploadOne(int $imageId, bool $needRecreateImages = true, bool $needDeleteFileServerAfterUpload = false): array {
        $images = $this->getImage($imageId, $needRecreateImages);
        $result = ["files" => array_sum(array_map("count", $images)), "uploaded" => 0, "deleted" => 0];
        // return $images; //* debug

        foreach ($images as $index => $elem) {
            foreach ($elem as $image) {
                $time = date("H:i");

                try {
                    $object = $this->s3Service->uploadObject(
                        $image["location"],
                        $image["path"],
                        [
                            "author" => $this->shopName,
                            "description" => $image["legend"] ?? "",
                            "link" => $image["url"] ?? ""
                        ]
                    );

                    if (isset($object["@metadata"]) && $object["@metadata"]["statusCode"] == 200) {
                        $result["uploaded"]++;
                        $this->logger->log("upload", "Time: {$time} | UploadOne: {$result['uploaded']}/{$result['files']} | ID: $index | Target: CDN | Status: {$object['@metadata']['statusCode']} | Image: {$image['location']}");

                        if ($needDeleteFileServerAfterUpload) {
                            if ($this->fileService->deleteFile($image["path"])) {
                                $result["deleted"]++;
                                $this->logger->log("delete", "Time: {$time} | UploadOne: {$result['deleted']}/{$result['files']} | ID: $index | Target: Server | Status: 200 | Image: {$image['path']}");
                            } else {
                                $this->logger->log("error", "Time: {$time} | UploadOne: {$result['deleted']}/{$result['files']} | ID: $index | Target: Server | Status: 400 | Image: {$image['path']}: Failed to delete file...");
                            }
                        }
                    } else {
                        $this->logger->log("error", "Time: {$time} | UploadOne: {$result['uploaded']}/{$result['files']} | ID: $index | Target: CDN | Status: 400 | Image: {$image['location']}: Failed to upload file...");
                    }
                } catch (\Exception $e) {
                    $this->logger->log("error", "Time: {$time} | UploadOne: {$result['uploaded']}/{$result['files']} | ID: $index | Target: CDN | Status: 400 | Image: {$image['location']}: {$e->getMessage()}");
                }
            }
        }

        return $result;
    }
}
