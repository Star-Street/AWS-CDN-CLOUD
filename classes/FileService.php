<?php

class FileService
{
    public function __construct() {}

    /**
     * Get image paths from server
     *
     * @param string $filePath File path on the server
     * @return array
     */
    public function getFiles(string $filePath): array 
    {
        $files = array_merge(
            glob($filePath . "-*.jpg"),
            glob($filePath . ".jpg")
        );
        return $files;
    }

    /**
     * Get default image paths from server
     *
     * @param string $filePath Default file path on the server
     * @return array
     */
    public function getDefaultFiles(string $filePath): array 
    {
        $files = array_merge(
            glob($filePath . "*.jpg"),
            glob($filePath . "*.png"),
            glob($filePath . "*.gif"),
            glob($filePath . "*.ico")
        );
        return $files;
    }

    /**
     * Delete files with pattern from server
     *
     * @param string $folderPath Default folder path with pattern on the server
     * @return bool
     */
    public function deleteTmpFiles(string $folderPath): bool 
    {
        $files = $this->getDefaultFiles($folderPath);

        if (!empty($files)) {
            foreach($files as $file) {
                if (!$this->deleteFile($file)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Delete file from server
     * (image will not be removed from the product)
     *
     * @param string $filePath Image path on the server
     * @return bool
     */
    public function deleteFile(string $filePath): bool 
    {
        if (is_file($filePath) && file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    /**
     * [This method is deprecated]
     * 
     * Delete images from server
     * (image will not be removed from the product)
     *
     * @param string $folderPath Folder path on the server
     */
    public function cleanFolderFiles(string $folderPath) 
    {
        $exts = ["jpg", "jpeg", "png"];

        if (is_dir($folderPath)) {
            $files = scandir($folderPath);

            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;

                $file_path = $folderPath . '/' . $file;
                if (is_file($file_path)) {
                    $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

                    if (in_array($ext, $image_extensions)) unlink($file_path);
                }
            }
        }
    }

    /**
     * Delete image from product
     * (image will be removed from the product and not be displayed)
     *
     * @param int $imageId Image id
     * @return bool
     */
    public function deleteImageFromProduct(int $imageId): bool 
    {
        $image = new Image($imageId);

        if (Validate::isLoadedObject($image)) {
            if ($image->delete()) {
                return true;
            }
        }

        return false;
    }
}
