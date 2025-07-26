<?php
namespace AwsCdnCloud\Override;

use ImageManager;
use ToolsCore as Tools;
use PrestaShop\PrestaShop\Adapter\Manufacturer\AbstractManufacturerHandler;
use PrestaShop\PrestaShop\Core\Domain\Manufacturer\Query\GetManufacturerForEditing;
use PrestaShop\PrestaShop\Core\Domain\Manufacturer\QueryHandler\GetManufacturerForEditingHandlerInterface;
use PrestaShop\PrestaShop\Core\Domain\Manufacturer\QueryResult\EditableManufacturer;
use PrestaShop\PrestaShop\Core\Domain\Manufacturer\ValueObject\ManufacturerId;
use PrestaShop\PrestaShop\Core\Image\Parser\ImageTagSourceParserInterface;


class GetManufacturerForEditingHandlerDecorator extends AbstractManufacturerHandler implements GetManufacturerForEditingHandlerInterface
{
    /**
     * @var ImageTagSourceParserInterface
     */
    private $imageTagSourceParser;

    public function __construct(
        ImageTagSourceParserInterface $imageTagSourceParser
    ) {
        $this->imageTagSourceParser = $imageTagSourceParser;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(GetManufacturerForEditing $query)
    {
        $manufacturerId = $query->getManufacturerId();
        $manufacturer = $this->getManufacturer($manufacturerId);

        return new EditableManufacturer(
            $manufacturerId,
            $manufacturer->name,
            (bool) $manufacturer->active,
            $manufacturer->short_description,
            $manufacturer->description,
            $manufacturer->meta_title,
            $manufacturer->meta_description,
            $manufacturer->meta_keywords,
            $this->getLogoImage($manufacturerId),
            $manufacturer->getAssociatedShops()
        );
    }

    /**
     * @param ManufacturerId $manufacturerId
     *
     * @return array|null
     */
    private function getLogoImage(ManufacturerId $manufacturerId)
    {
        $pathToImage = _PS_MANU_IMG_DIR_ . $manufacturerId->getValue() . '.jpg';
        $imageTag = ImageManager::thumbnail(
            $pathToImage,
            'manufacturer_' . $manufacturerId->getValue() . '.jpg',
            350,
            'jpg',
            true,
            true
        );

        $imageSize = file_exists($pathToImage) ? filesize($pathToImage) / 1000 : '';

        //? awscdncloud (FIX for CDN)
        if (\Module::isInstalled("awscdncloud") && \Module::isEnabled("awscdncloud")) {
            $awscdncloud = \Module::getInstanceByName("awscdncloud");
            if ($awscdncloud->getCdnStatus()) {
                $result = $this->imageTagSourceParser->parse($imageTag);
                if (strpos($result, Tools::getMediaServer($result))) $result = ltrim($result, "/");

                unset($awscdncloud);

                return [
                    'size' => sprintf('%skB', $imageSize),
                    'path' => $result,
                ];
            }
            unset($awscdncloud);
        }

        if (empty($imageTag) || empty($imageSize)) {
            return null;
        }

        return [
            'size' => sprintf('%skB', $imageSize),
            'path' => $this->imageTagSourceParser->parse($imageTag),
        ];
    }
}
