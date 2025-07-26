<?php

abstract class DeleteService 
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

    abstract protected function getAllImages($entityIds = null): array;
    abstract protected function getImage(int $imageId): array;

    public function deleteAll($entityIds = null, bool $needDeleteFileServer = false): array {
        $images = $this->getAllImages($entityIds);
        $result = ["files" => array_sum(array_map("count", $images)), "deleted" => 0];
        // return $images; //* debug

        foreach ($images as $index => $elem) {
            foreach ($elem as $image) {
                $time = date("H:i");

                try {
                    $object = $this->s3Service->deleteFolderFile($image["location"]);

                    if (isset($object["@metadata"]) && $object["@metadata"]["statusCode"] == 200) {
                        $result["deleted"]++;
                        $this->logger->log("delete", "Time: {$time} | DeleteAll: {$result['deleted']}/{$result['files']} | ID: $index | Target: CDN | Status: {$object['@metadata']['statusCode']} | Image: {$image['location']}");

                        if ($needDeleteFileServer) {
                            if ($this->fileService->deleteImageFromProduct($image["id_image"])) {
                                $this->logger->log("delete", "Time: {$time} | DeleteAll: {$result['deleted']}/{$result['files']} | ID: $index | Target: Server | Status: 200 | Image: {$image['path']}");
                            } else {
                                $this->logger->log("error", "Time: {$time} | DeleteAll: {$result['deleted']}/{$result['files']} | ID: $index | Target: Server | Status: 400 | Image: {$image['path']}: Failed to delete file...");
                            }
                        }
                    } else {
                        $this->logger->log("error", "Time: {$time} | DeleteAll: {$result['deleted']}/{$result['files']} | ID: $index | Target: CDN | Status: 400 | Image: {$image['location']}: Failed to delete file...");
                    }
                } catch (\Exception $e) {
                    $this->logger->log("error", "Time: {$time} | DeleteAll: {$result['deleted']}/{$result['files']} | ID: $index | Target: CDN | Status: 400 | Image: {$image['location']}: {$e->getMessage()}");
                }
            }
        }

        return $result;
    }

    public function deleteOne(int $imageId, bool $needDeleteFileServer = false): array {
        $images = $this->getImage($imageId);
        $result = ["files" => array_sum(array_map("count", $images)), "deleted" => 0];
        // return $images; //* debug

        foreach ($images as $index => $elem) {
            foreach ($elem as $image) {
                $time = date("H:i");

                try {
                    $object = $this->s3Service->deleteFolderFile($image["location"]);

                    if (isset($object["@metadata"]) && $object["@metadata"]["statusCode"] == 200) {
                        $result["deleted"]++;
                        $this->logger->log("delete", "Time: {$time} | DeleteOne: {$result['deleted']}/{$result['files']} | ID: $index | Target: CDN | Status: {$object['@metadata']['statusCode']} | Image: {$image['location']}");

                        if ($needDeleteFileServer) {
                            if ($this->fileService->deleteImageFromProduct($image["id_image"])) {
                                $this->logger->log("delete", "Time: {$time} | DeleteOne: {$result['deleted']}/{$result['files']} | ID: $index | Target: Server | Status: 200 | Image: {$image['path']}");
                            } else {
                                $this->logger->log("error", "Time: {$time} | DeleteOne: {$result['deleted']}/{$result['files']} | ID: $index | Target: Server | Status: 400 | Image: {$image['path']}: Failed to delete file...");
                            }
                        }
                    } else {
                        $this->logger->log("error", "Time: {$time} | DeleteOne: {$result['deleted']}/{$result['files']} | ID: $index | Target: CDN | Status: 400 | Image: {$image['location']}: Failed to delete file...");
                    }
                } catch (\Exception $e) {
                    $this->logger->log("error", "Time: {$time} | DeleteOne: {$result['deleted']}/{$result['files']} | ID: $index | Target: CDN | Status: 400 | Image: {$image['location']}: {$e->getMessage()}");
                }
            }
        }

        return $result;
    }
}
