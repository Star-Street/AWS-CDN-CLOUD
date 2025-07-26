<?php

class DefaultUploader extends UploadService
{
    const DEF_PATH = _PS_IMG_DIR_;
    const CAT_PATH = _PS_IMG_DIR_ . "c/";
    const PROD_PATH = _PS_IMG_DIR_ . "p/";
    const MANUF_PATH = _PS_IMG_DIR_ . "m/";

    /** @var TextHealper */
    private $textHealper;

    public function __construct(S3Service $s3Service, LogService $logger, FileService $fileService, TextHealper $textHealper) {
        parent::__construct($s3Service, $logger, $fileService);
        $this->textHealper = $textHealper;
    }

    protected function getAllImages($entityIds = null, bool $needRecreateImages): array {
        $results = [];
        $imageFiles = [];

        // $imageFiles = array_merge(
        //     $this->fileService->getFiles(self::DEF_PATH, true),
        //     $this->fileService->getFiles(self::PROD_PATH . "ru"),
        //     $this->fileService->getFiles(self::CAT_PATH . "ru"),
        //     $this->fileService->getFiles(self::MANUF_PATH . "ru")
        // );

        $imageFiles["img"] = $this->fileService->getDefaultFiles(self::DEF_PATH);
        $imageFiles["products"] = $this->fileService->getFiles(self::PROD_PATH . "ru");
        $imageFiles["categories"] = $this->fileService->getFiles(self::CAT_PATH . "ru");
        $imageFiles["manufacturers"] = $this->fileService->getFiles(self::MANUF_PATH . "ru");

        foreach ($imageFiles as $index => $elem) {
            foreach ($elem as $filePath) {
                $location = $this->textHealper->getLocation($filePath);
    
                $results[$index][] = [
                    "name"     => "",
                    "legend"   => "",
                    "location" => $location,
                    "path"     => $filePath,
                    "url"      => _PS_BASE_URL_ . $location,
                ];
            }
        }

        return $results;
    }
    
    protected function getImage(int $imageId, bool $needRecreateImages): array {}
}
