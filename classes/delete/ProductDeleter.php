<?php

class ProductDeleter extends DeleteService
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
     * @return array
     */
    protected function getAllImages($entityIds = null): array {
        $page = 0;
        $results = [];
        $startLine = self::LIMIT * $page;

        while (true) {
            $elements = $this->getProducts($entityIds, $startLine, self::LIMIT);

            foreach ($elements["products"] as $elem) {
                $id = (int) $elem["id_product"];

                $entityFiles = Image::getImages(self::LANG_ID, $id);

                foreach ($entityFiles as $image) {
                    $implodeId = $this->textHealper->implodeId($image["id_image"]);
                    $filePath = _PS_PROD_IMG_DIR_ . $implodeId;
                    $location = $this->textHealper->getLocation($filePath);

                    $results[$id][] = [
                        "id_image" => (int) $image["id_image"],
                        "location" => $location,
                        "path"     => $filePath,
                    ];
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
     * @return array
     */
    protected function getImage(int $imageId): array {
        $results = [];

        $image = new Image($imageId);
        $idProduct = (int) $image->id_product;
        $idImage = (int) $image->id_image;
        unset($image);

        $implodeId = $this->textHealper->implodeId($imageId);
        $filePath = _PS_PROD_IMG_DIR_ . $implodeId;
        $location = $this->textHealper->getLocation($filePath);

        $results[$idProduct][] = [
            "id_image" => $idImage,
            "location" => $location,
            "path"     => $filePath,
        ];

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
                "SELECT `id_product` FROM `%sproduct` 
                WHERE `state` = 1 AND `id_product` IN (%s)",
                _DB_PREFIX_, $idsList
            );
            $products = Db::getInstance()->executeS($sql);
        } else {
            $sql = sprintf(
                "SELECT `id_product` FROM `%sproduct` 
                WHERE `state` = 1 
                ORDER BY `id_product` LIMIT %d OFFSET %d",
                _DB_PREFIX_, $limit, $startLine
            );
            $products = Db::getInstance()->executeS($sql);
        }

        return [
            "products" => $products,
            "count_products" => count($products)
        ];
    }
}
