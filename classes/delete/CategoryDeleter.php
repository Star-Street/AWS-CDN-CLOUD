<?php

class CategoryDeleter extends DeleteService
{
    const LANG_ID = 1;

    /** @var TextHealper */
    private $textHealper;

    public function __construct(S3Service $s3Service, LogService $logger, FileService $fileService, TextHealper $textHealper) {
        parent::__construct($s3Service, $logger, $fileService);
        $this->textHealper = $textHealper;
    }

    /**
     * Get all images from category
     *
     * @param array|int $entityIds Category ids/id
     * @return array
     */
    protected function getAllImages($entityIds = null): array {
        $results = [];
        $elements = $this->getCategories($entityIds);

        foreach ($elements as $elem) {
            $entityId = (int) $elem["id_category"];

            $entityFiles = $this->fileService->getFiles(_PS_CAT_IMG_DIR_ . $entityId);
            if (!empty($entityFiles)) {
                $entityFiles = array_unique(array_merge($entityFiles, $this->fileService->getFiles(_PS_TMP_IMG_DIR_ . "category*_" . $entityId)));
            }

            foreach ($entityFiles as $filePath) {
                $location = $this->textHealper->getLocation($filePath);

                $results[$entityId][] = [
                    "id_image" => $entityId,
                    "location" => $location,
                    "path"     => $filePath,
                ];
            }
        }

        return $results;
    }

    /**
     * Get one image from category
     *
     * @param int $imageId Image id
     * @return array
     */
    protected function getImage(int $imageId): array {}

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
}
