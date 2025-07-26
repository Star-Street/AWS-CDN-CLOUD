<?php

class ProductUploader extends UploadService
{
    const LIMIT = 1000;
    const LANG_ID = 1;

    /** @var TextHealper */
    private $textHealper;

    public function __construct(S3Service $s3Service, LogService $logger, FileService $fileService, TextHealper $textHealper) {
        parent::__construct($s3Service, $logger, $fileService);
        $this->textHealper = $textHealper;
    }

    /**
     * Get all images from product
     *
     * @param array|int $entityIds Product ids/id
     * @param bool $needRecreateImages If set, regenerate all image types for this product
     * @return array
     */
    protected function getAllImages($entityIds = null, bool $needRecreateImages): array {
        $page = 0;
        $results = [];
        $startLine = self::LIMIT * $page;

        while (true) {
            $elements = $this->getProducts($entityIds, $startLine, self::LIMIT);

            foreach ($elements["products"] as $elem) {
                $id = (int) $elem["id_product"];
                $name = $this->textHealper->clearText($elem["name"]);
                $description = $this->textHealper->clearText($elem["description"]);
                $description = $this->textHealper->shortText($description);

                $entityFiles = Image::getImages(self::LANG_ID, $id);

                foreach ($entityFiles as $image) {
                    $implodeId = $this->textHealper->implodeId($image["id_image"]);

                    if ($needRecreateImages) {
                        $this->generateImages($image, _PS_PROD_IMG_DIR_ . $implodeId);
                    }

                    $files = $this->fileService->getFiles(_PS_PROD_IMG_DIR_ . $implodeId . $image["id_image"]);
                    if (!empty($files)) {
                        $files = array_unique(array_merge($files, $this->fileService->getFiles(_PS_TMP_IMG_DIR_ . "product*_" . $image["id_image"])));
                    }

                    foreach ($files as $filePath) {
                        $location = $this->textHealper->getLocation($filePath);

                        $results[$id][] = [
                            "name"     => $name ?? "",
                            "legend"   => $description ?? "",
                            "location" => $location,
                            "path"     => $filePath,
                            "url"      => _PS_BASE_URL_ . $location,
                        ];
                    }
                }
            }

            if ($elements["count_products"] < self::LIMIT || !empty($entityIds)) break;

            $page++;
            $startLine = self::LIMIT * $page;
        }

        return $results;
    }

    /**
     * Get one image from product
     *
     * @param int $imageId Image id
     * @param bool $needRecreateImages If set, regenerate all image types for this product
     * @return array
     */
    protected function getImage(int $imageId, bool $needRecreateImages): array {
        $results = [];

        $image = new Image($imageId);
        $idProduct = (int) $image->id_product;
        unset($image);

        $elements = $this->getProducts($idProduct);

        foreach ($elements["products"] as $elem) {
            $id = (int) $elem["id_product"];
            $name = $this->textHealper->clearText($elem["name"]);
            $description = $this->textHealper->clearText($elem["description"]);
            $description = $this->textHealper->shortText($description);

            $entityFiles = Image::getImages(self::LANG_ID, $id);

            foreach ($entityFiles as $image) {
                if ($image["id_image"] == $imageId) {
                    $implodeId = $this->textHealper->implodeId($image["id_image"]);

                    if ($needRecreateImages) {
                        $this->generateImages($image, _PS_PROD_IMG_DIR_ . $implodeId);
                    }

                    $files = $this->fileService->getFiles(_PS_PROD_IMG_DIR_ . $implodeId . $image["id_image"]);
                    if (!empty($files)) {
                        $files = array_unique(array_merge($files, $this->fileService->getFiles(_PS_TMP_IMG_DIR_ . "product*_" . $image["id_image"])));
                    }

                    foreach ($files as $filePath) {
                        $location = $this->textHealper->getLocation($filePath);
    
                        $results[$id][] = [
                            "name"     => $name ?? "",
                            "legend"   => $description ?? "",
                            "location" => $location,
                            "path"     => $filePath,
                            "url"      => _PS_BASE_URL_ . $location,
                        ];
                    }
                }
            }
        }

        return $results;
    }

    public function getProducts($ids = null, int $startLine = 0, int $limit = 0): array {
        $products = [];

        if (!empty($ids)) {
            if (!is_array($ids)) {
                $ids = array_map("trim", explode(",", $ids));
            }

            $ids = array_map("intval", $ids);
            $idsList = implode(",", $ids);

            $sql = sprintf(
                "SELECT p.`id_product`, pl.`name`, pl.`description`, pl.`link_rewrite`
                FROM `%sproduct` p
                LEFT JOIN `%sproduct_lang` pl ON p.`id_product` = pl.`id_product` AND pl.`id_lang` = %d
                WHERE p.`state` = 1 AND p.`id_product` IN (%s)",
                _DB_PREFIX_, _DB_PREFIX_, self::LANG_ID, $idsList
            );
            $products = Db::getInstance()->executeS($sql);
        } else {
            $sql = sprintf(
                "SELECT p.`id_product`, pl.`name`, pl.`description`, pl.`link_rewrite`
                FROM `%sproduct` p
                LEFT JOIN `%sproduct_lang` pl ON p.`id_product` = pl.`id_product` AND pl.`id_lang` = %d
                WHERE p.`state` = 1
                ORDER BY p.`id_product` LIMIT %d OFFSET %d",
                _DB_PREFIX_, _DB_PREFIX_, self::LANG_ID, $limit, $startLine
            );
            $products = Db::getInstance()->executeS($sql);
        }

        return [
            "products" => $products,
            "count_products" => count($products)
        ];
    }

    public function generateImages(array $element, string $path): bool {
        $time = date("H:i");
        $counter = 0;
        $imgTypes = ImageType::getImagesTypes("products");
        $imgTypesCount = count($imgTypes) + 1;

        if (!empty($element)) {
            $id = (int) $element["id_image"];
            $time = date("H:i");
            $mainImg = $path . $id . ".jpg";

            if (!file_exists($mainImg)) return false;

            //generate base types image
            foreach($imgTypes as $type) {
                $nextImg = $path . $id . "-" . $type["name"] . ".jpg";
                if (ImageManager::resize($mainImg, $nextImg, $type["width"], $type["height"])) {
                    $counter++;
                    $this->logger->log("generate", "Time: {$time} | GenerateImages: $counter/$imgTypesCount | ID: $id | Target: Server | Status: 200 | Image: $nextImg");
                } else {
                    $this->logger->log("error", "Time: {$time} | GenerateImages: $counter/$imgTypesCount | ID: $id | Target: Server | Status: 400 | Image: $nextImg: Failed to generate file...");
                }
            }

            //generate tmp mini images
            $imgThumbMini = ImageManager::thumbnail(
                $mainImg,
                "product_mini_$id.jpg",
                HelperList::LIST_THUMBNAIL_SIZE,
                "jpg",
                true,
                true
            );
            if (!empty($imgThumbMini)) {
                $counter++;
                $this->logger->log("generate", "Time: {$time} | GenerateImages: $counter/$imgTypesCount | ID: $id | Target: Server | Status: 200 | Image: /img/tmp/product_mini_$id.jpg");
            } else {
                $this->logger->log("error", "Time: {$time} | GenerateImages: $counter/$imgTypesCount | ID: $id | Target: Server | Status: 400 | Image: /img/tmp/product_mini_$id.jpg: Failed to generate file...");
            }
        }

        return true;
    }
}
