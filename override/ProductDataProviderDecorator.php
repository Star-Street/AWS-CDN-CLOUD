<?php
namespace AwsCdnCloud\Override;

use Context;
use Image;
use Product;
use StockAvailable;
use ToolsCore as Tools;
use PrestaShop\PrestaShop\Adapter\Product\ProductDataProvider;


class ProductDataProviderDecorator extends ProductDataProvider
{
    /**
     * Get an image.
     *
     * @param int $id_image
     *
     * @return array()
     */
    public function getImage($id_image)
    {
        $imageData = new Image((int) $id_image);
        $imageUrlCdn = "";
        $baseUrlImage = _THEME_PROD_DIR_ . $imageData->getImgPath();

        //? awscdncloud (FIX for CDN)
        if (\Module::isInstalled("awscdncloud") && \Module::isEnabled("awscdncloud")) {
            $awscdncloud = \Module::getInstanceByName("awscdncloud");
            if ($awscdncloud->getCdnStatus()) {
                if (!Tools::file_exists_cache(_PS_CAT_IMG_DIR_ . $baseUrlImage . ".jpg") && $id_image) {
                    $imageUrlCdn = Tools::getShopProtocol() . Tools::getMediaServer($baseUrlImage);
                }
            }
            unset($awscdncloud);
        }

        return [
            "id" => $imageData->id,
            "id_product" => $imageData->id_product,
            "position" => $imageData->position,
            "cover" => $imageData->cover ? true : false,
            "legend" => $imageData->legend,
            "format" => $imageData->image_format,
            // "base_image_url" => _THEME_PROD_DIR_ . $imageData->getImgPath(),
            "base_image_url" => $imageUrlCdn . $baseUrlImage,
        ];
    }
}
