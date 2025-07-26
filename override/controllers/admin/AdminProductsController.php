<?php

/**
 * @property Product $object
 */
class AdminProductsController extends AdminProductsControllerCore
{
    /**
     * Ajax process upload images.
     *
     * @param int|null $idProduct
     * @param string $inputFileName
     * @param bool $die If method must die or return values
     *
     * @return array
     */
    public function ajaxProcessaddProductImage($idProduct = null, $inputFileName = 'file', $die = true)
    {
        $result = parent::ajaxProcessaddProductImage($idProduct, $inputFileName, $die);

        //? Alexey: added 05.05.2025 FIX for CDN
        if (Tools::hasMediaServer() && !empty($result)) {
            if (!empty($result) && isset($result[0]["id"])) {
                require_once _PS_MODULE_DIR_ . "awscdncloud/classes/S3Service.php";

                //? always loads one picture at a time
                $imageId = (int) $result[0]["id"];

                $s3Service = new S3Service(
                    Configuration::get("AWS_S3_BUCKET"), 
                    Configuration::get("AWS_S3_KEY"), 
                    Configuration::get("AWS_S3_SECRET"), 
                    Configuration::get("AWS_S3_REGION"), 
                    Configuration::get("AWS_S3_ENDPOINT")
                );
                $logService = new LogService();
                $fileService = new FileService();
                $textHealper = new TextHealper();

                //? upload image in CDN (object storage) - with recreate images && delete files from server
                $prodCdn = new ProductUploader($s3Service, $logService, $fileService, $textHealper);
                $prodCdn->uploadOne($imageId, true, true);

                unset($s3Service, $logService, $fileService, $textHealper, $prodCdn);
            }
        }

        return $result;
    }

    public function ajaxProcessDeleteProductImage($id_image = null)
    {
        //? Alexey: added 06.05.2025 FIX for CDN
        if (Tools::hasMediaServer()) {
            if ($id_image) {
                require_once _PS_MODULE_DIR_ . "awscdncloud/classes/S3Service.php";

                try {
                    $s3Service = new S3Service(
                        Configuration::get("AWS_S3_BUCKET"), 
                        Configuration::get("AWS_S3_KEY"), 
                        Configuration::get("AWS_S3_SECRET"), 
                        Configuration::get("AWS_S3_REGION"), 
                        Configuration::get("AWS_S3_ENDPOINT")
                    );
                    $logService = new LogService();
                    $fileService = new FileService();
                    $textHealper = new TextHealper();
    
                    //? delete image from CDN (object storage)
                    $prodCdn = new ProductDeleter($s3Service, $logService, $fileService, $textHealper);
                    $prodCdn->deleteOne($id_image);
    
                    unset($s3Service, $logService, $fileService, $textHealper, $prodCdn);
                } catch (\Exception $e) {
                    $time = date("H:i");
                    $logService = new LogService();
                    $logService->log("error", "Time: {$time} | DeleteOne: ajaxProcessDeleteProductImage | ID: $id_image | Target: CDN | Status: 500 | Failed to delete file: $e");
                    unset($logService);
                    $this->jsonError($this->trans('An error occurred while attempting to delete the product image.', [], 'Admin.Catalog.Notification'));
                }
            }
        }

        return parent::ajaxProcessDeleteProductImage($id_image);
    }
}
