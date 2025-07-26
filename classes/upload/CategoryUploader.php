<?php

class CategoryUploader extends UploadService
{
    const LANG_ID = 1;

    /** @var TextHealper */
    private $textHealper;

    public function __construct(S3Service $s3Service, LogService $logger, FileService $fileService, TextHealper $textHealper) {
        parent::__construct($s3Service, $logger, $fileService);
        $this->textHealper = $textHealper;
    }

    protected function getAllImages($entityIds = null, bool $needRecreateImages): array {
        $results = [];
        $elements = $this->getCategories($entityIds);

        $this->generateImages($elements, _PS_CAT_IMG_DIR_, $needRecreateImages);

        foreach ($elements as $elem) {
            $entityId = (int) $elem["id_category"];
            $name = $this->textHealper->clearText($elem["name"]);
            $description = $this->textHealper->clearText($elem["description"]);
            $description = $this->textHealper->shortText($description);

            $entityFiles = $this->fileService->getFiles(_PS_CAT_IMG_DIR_ . $entityId);
            if (!empty($entityFiles)) {
                $entityFiles = array_unique(array_merge($entityFiles, $this->fileService->getFiles(_PS_TMP_IMG_DIR_ . "category*_" . $entityId)));
            }

            foreach ($entityFiles as $filePath) {
                $location = $this->textHealper->getLocation($filePath);

                $results[$entityId][] = [
                    "name"     => $name ?? "",
                    "legend"   => $description ?? "",
                    "location" => $location,
                    "path"     => $filePath,
                    "url"      => _PS_BASE_URL_ . $location,
                ];
            }
        }

        return $results;
    }

    protected function getImage(int $imageId, bool $needRecreateImages): array {}

    public function getCategories($id = null): array {
        $categories = Category::getCategories(self::LANG_ID, false, false);

        if ($id) {
            $ids = is_array($id) ? $id : [$id];
            $categories = array_filter($categories, function ($element) use ($ids) {
                return in_array($element["id_category"], $ids);
            });
        }

        return $categories;
    }

    public function generateImages(array $elements, string $path, bool $needRecreateImages): bool {
        $time = date("H:i");
        $imgTypes = ImageType::getImagesTypes("categories");

        if (!empty($elements)) {
            if ($needRecreateImages) {
                foreach ($elements as $elem) {
                    $id = (int) $elem["id_category"];
                    $time = date("H:i");
                    $mainImg = $path . $id . ".jpg";

                    if (!file_exists($mainImg)) continue;

                    //generate base types image
                    foreach($imgTypes as $type) {
                        $nextImg = $path . $id . "-" . $type["name"] . ".jpg";
                        if (ImageManager::resize($mainImg, $nextImg, $type["width"], $type["height"])) {
                            $this->logger->log("generate", "Time: {$time} | ID: $id | Target: Server | Status: 200 | Image: $nextImg");
                        } else {
                            $this->logger->log("error", "Time: {$time} | ID: $id | Target: Server | Status: 400 | Failed to generate file: $nextImg");
                        }
                    }

                    //generate tmp images
                    $imgThumbPath = "category_$id.jpg";
                    $imgThumb = ImageManager::thumbnail(
                        $mainImg,
                        $imgThumbPath,
                        350,
                        "jpg",
                        true,
                        true
                    );
                    if (!empty($imgThumb)) {
                        $this->logger->log("generate", "Time: {$time} | ID: $id | Target: Server | Status: 200 | Image: /img/tmp/$imgThumbPath");
                    } else {
                        $this->logger->log("error", "Time: {$time} | ID: $id | Target: Server | Status: 400 | Failed to generate file: /img/tmp/$imgThumbPath");
                    }

                    //generate tmp thumb images
                    $imgThumbMiniPath = "category_$id-thumb.jpg";
                    $imgThumbMini = ImageManager::thumbnail(
                        $mainImg,
                        $imgThumbMiniPath,
                        125,
                        "jpg",
                        true,
                        true
                    );
                    if (!empty($imgThumbMini)) {
                        $this->logger->log("generate", "Time: {$time} | ID: $id | Target: Server | Status: 200 | Image: /img/tmp/$imgThumbMiniPath");
                    } else {
                        $this->logger->log("error", "Time: {$time} | ID: $id | Target: Server | Status: 400 | Failed to generate file: /img/tmp/$imgThumbMiniPath");
                    }
                }
            }
        } else {
            $this->logger->log("error", "Time: {$time} | ID: 0 | Target: Server | Status: 400 | Failed in generateAllImages(): empty elements...");
            return false;
        } 

        return true;
    }
}
