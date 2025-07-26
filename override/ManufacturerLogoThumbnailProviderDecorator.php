<?php
namespace AwsCdnCloud\Override;

use HelperList;
use ImageManager;
use ToolsCore as Tools;
use PrestaShop\PrestaShop\Core\Image\ImageProviderInterface;
use PrestaShop\PrestaShop\Core\Image\Parser\ImageTagSourceParserInterface;


class ManufacturerLogoThumbnailProviderDecorator implements ImageProviderInterface
{
    // private $decoratedProvider;
    private $imageTagSourceParser;

    public function __construct(
        // ImageProviderInterface $decoratedProvider,
        ImageTagSourceParserInterface $imageTagSourceParser
    ) {
        // $this->provider = $decoratedProvider;
        $this->imageTagSourceParser = $imageTagSourceParser;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath($manufacturerId)
    {
        $pathToImage = _PS_MANU_IMG_DIR_ . $manufacturerId . ".jpg";
        $imageTag = ImageManager::thumbnail(
            $pathToImage,
            "manufacturer_mini_" . $manufacturerId . ".jpg",
            HelperList::LIST_THUMBNAIL_SIZE
        );

        //? awscdncloud (FIX for CDN)
        if (\Module::isInstalled("awscdncloud") && \Module::isEnabled("awscdncloud")) {
            $awscdncloud = \Module::getInstanceByName("awscdncloud");
            if ($awscdncloud->getCdnStatus()) {
                $result = $this->imageTagSourceParser->parse($imageTag);
                $server = Tools::getMediaServer($pathToImage);
                if (strpos($result, $server)) $result = ltrim($result, "/");
                unset($awscdncloud);
                return $result;
            }
            unset($awscdncloud);
        }

        return $this->imageTagSourceParser->parse($imageTag);
    }
}
