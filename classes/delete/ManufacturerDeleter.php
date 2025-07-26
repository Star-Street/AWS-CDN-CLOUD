<?php

class ManufacturerDeleter extends DeleteService 
{

    const LANG_ID = 1;

    /** @var TextHealper */
    private $textHealper;

    public function __construct(S3Service $s3Service, LogService $logger, FileService $fileService, TextHealper $textHealper) {
        parent::__construct($s3Service, $logger, $fileService);
        $this->textHealper = $textHealper;
    }

    /**
     * Get all images from manufacturer
     *
     * @param array|int $entityIds Manufacturer ids/id
     * @return array
     */
    protected function getAllImages($entityIds = null): array {
        $results = [];
        $elements = $this->getManufacturers($entityIds);

        foreach ($elements as $elem) {
            $entityId = (int) $elem["id_manufacturer"];

            $entityFiles = $this->fileService->getFiles(_PS_MANU_IMG_DIR_ . $entityId);
            if (!empty($entityFiles)) {
                $entityFiles = array_unique(array_merge($entityFiles, $this->fileService->getFiles(_PS_TMP_IMG_DIR_ . "manufacturer*_" . $entityId)));
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
     * Get one image from manufacturer
     *
     * @param int $imageId Image id
     * @return array
     */
    protected function getImage(int $imageId): array {}

    public function getManufacturers($id = null): array {
        $manufacturers = Manufacturer::getManufacturers(false, self::LANG_ID, false);

        if ($id) {
            $ids = is_array($id) ? $id : [$id];
            $manufacturers = array_filter($manufacturers, function ($element) use ($ids) {
                return in_array($element["id_manufacturer"], $ids);
            });
        }

        return $manufacturers;
    }
}
