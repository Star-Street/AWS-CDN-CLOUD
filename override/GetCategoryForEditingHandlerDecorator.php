<?php
namespace AwsCdnCloud\Override;

use Category;
use Db;
use ImageManager;
use ImageType;
use PDO;
use ToolsCore as Tools;
use PrestaShop\PrestaShop\Core\Domain\Category\Exception\CannotEditRootCategoryException;
use PrestaShop\PrestaShop\Core\Domain\Category\Exception\CategoryNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\Category\Query\GetCategoryForEditing;
use PrestaShop\PrestaShop\Core\Domain\Category\QueryHandler\GetCategoryForEditingHandlerInterface;
use PrestaShop\PrestaShop\Core\Domain\Category\QueryResult\EditableCategory;
use PrestaShop\PrestaShop\Core\Domain\Category\ValueObject\CategoryId;
use PrestaShop\PrestaShop\Core\Domain\Category\ValueObject\MenuThumbnailId;
use PrestaShop\PrestaShop\Core\Image\Parser\ImageTagSourceParserInterface;
use Shop;


class GetCategoryForEditingHandlerDecorator implements GetCategoryForEditingHandlerInterface
{
    /**
     * @var ImageTagSourceParserInterface
     */
    private $imageTagSourceParser;

    /**
     * @param ImageTagSourceParserInterface $imageTagSourceParser
     */
    public function __construct(ImageTagSourceParserInterface $imageTagSourceParser)
    {
        $this->imageTagSourceParser = $imageTagSourceParser;
    }

    /**
     * {@inheritdoc}
     *
     * @throws CategoryNotFoundException
     * @throws CannotEditRootCategoryException
     */
    public function handle(GetCategoryForEditing $query)
    {
        $category = new Category($query->getCategoryId()->getValue());

        if (!$category->id || (!$category->isAssociatedToShop() && Shop::getContext() == Shop::CONTEXT_SHOP)) {
            throw new CategoryNotFoundException($query->getCategoryId(), sprintf('Category with id "%s" was not found', $query->getCategoryId()->getValue()));
        }

        if ($category->isRootCategory()) {
            throw new CannotEditRootCategoryException();
        }

        /**
         * Select recursivly the subcategories in one SQL request
         */
        $subcategories = Db::getInstance()->query(
            'SELECT id_category ' .
            'FROM ( ' .
            '  SELECT * FROM `' . _DB_PREFIX_ . 'category`' .
            '  ORDER BY id_parent, id_category' .
            ') category_sorted, ' .
            '(SELECT @pv := ' . (int) $category->id . ') initialisation ' .
            'WHERE FIND_IN_SET(id_parent, @pv) ' .
            'AND LENGTH(@pv := CONCAT(@pv, \',\', id_category))'
        );

        $editableCategory = new EditableCategory(
            $query->getCategoryId(),
            $category->name,
            (bool) $category->active,
            $category->description,
            (int) $category->id_parent,
            $category->meta_title,
            $category->meta_description,
            $category->meta_keywords,
            $category->link_rewrite,
            $category->getGroups(),
            $category->getAssociatedShops(),
            (bool) $category->is_root_category,
            $this->getCoverImage($query->getCategoryId()),
            $this->getThumbnailImage($query->getCategoryId()),
            $this->getMenuThumbnailImages($query->getCategoryId()),
            $subcategories->fetchAll(PDO::FETCH_COLUMN)
        );

        return $editableCategory;
    }

    /**
     * @param CategoryId $categoryId
     *
     * @return array|null cover image data or null if category does not have cover
     */
    private function getCoverImage(CategoryId $categoryId)
    {
        $imageType = 'jpg';
        $image = _PS_CAT_IMG_DIR_ . $categoryId->getValue() . '.' . $imageType;

        $imageTag = ImageManager::thumbnail(
            $image,
            'category' . '_' . $categoryId->getValue() . '.' . $imageType,
            350,
            $imageType,
            true,
            true
        );

        $imageSize = file_exists($image) ? filesize($image) / 1000 : '';

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

    /**
     * @param CategoryId $categoryId
     *
     * @return array|null
     */
    private function getThumbnailImage(CategoryId $categoryId)
    {
        $image = _PS_CAT_IMG_DIR_ . $categoryId->getValue() . '.jpg';
        $imageTypes = ImageType::getImagesTypes('categories');

        if (count($imageTypes) > 0) {
            $thumb = '';
            $imageTag = '';
            $formattedSmall = ImageType::getFormattedName('small');
            $imageType = new ImageType();
            foreach ($imageTypes as $k => $imageType) {
                if ($formattedSmall == $imageType['name']) {
                    $thumb = _PS_CAT_IMG_DIR_ . $categoryId->getValue() . '-' . $imageType['name'] . '.jpg';
                    if (is_file($thumb)) {
                        $imageTag = ImageManager::thumbnail(
                            $thumb,
                            'category_' . (int) $categoryId->getValue() . '-thumb.jpg',
                            (int) $imageType['width'],
                            'jpg',
                            true,
                            true
                        );
                    }
                }
            }

            if (!is_file($thumb)) {
                $thumb = $image;
                $imageName = 'category_' . $categoryId->getValue() . '-thumb.jpg';

                $imageTag = ImageManager::thumbnail($image, $imageName, 125, 'jpg', true, true);
                ImageManager::resize(
                    _PS_TMP_IMG_DIR_ . $imageName,
                    _PS_TMP_IMG_DIR_ . $imageName,
                    (int) $imageType['width'],
                    (int) $imageType['height']
                );
            }

            $thumbSize = file_exists($thumb) ? filesize($thumb) / 1000 : false;

            //? awscdncloud (FIX for CDN)
            if (\Module::isInstalled("awscdncloud") && \Module::isEnabled("awscdncloud")) {
                $awscdncloud = \Module::getInstanceByName("awscdncloud");
                if ($awscdncloud->getCdnStatus()) {
                    $result = $this->imageTagSourceParser->parse($imageTag);

                    if (strpos($result, Tools::getMediaServer($result))) $result = ltrim($result, "/");
                    unset($awscdncloud);

                    return [
                        'size' => sprintf('%skB', $thumbSize),
                        'path' => $result,
                    ];
                }
                unset($awscdncloud);
            }

            if (empty($imageTag) || false === $thumbSize) {
                return null;
            }

            return [
                'size' => sprintf('%skB', $thumbSize),
                'path' => $this->imageTagSourceParser->parse($imageTag),
            ];
        }

        return null;
    }

    /**
     * @param CategoryId $categoryId
     *
     * @return array
     */
    private function getMenuThumbnailImages(CategoryId $categoryId)
    {
        $menuThumbnails = [];

        foreach (MenuThumbnailId::ALLOWED_ID_VALUES as $id) {
            $thumbnailPath = _PS_CAT_IMG_DIR_ . $categoryId->getValue() . '-' . $id . '_thumb.jpg';

            if (file_exists($thumbnailPath)) {
                $imageTag = ImageManager::thumbnail(
                    $thumbnailPath,
                    'category_' . $categoryId->getValue() . '-' . $id . '_thumb.jpg',
                    100,
                    'jpg',
                    true,
                    true
                );

                $menuThumbnails[$id] = [
                    'path' => $this->imageTagSourceParser->parse($imageTag),
                    'id' => $id,
                ];
            } else {
                //? awscdncloud (FIX for CDN)
                if (\Module::isInstalled("awscdncloud") && \Module::isEnabled("awscdncloud")) {
                    $awscdncloud = \Module::getInstanceByName("awscdncloud");
                    if ($awscdncloud->getCdnStatus()) {
                        $imageTag = ImageManager::thumbnail(
                            $thumbnailPath,
                            'category_' . $categoryId->getValue() . '-' . $id . '_thumb.jpg',
                            100,
                            'jpg',
                            true,
                            true
                        );

                        $menuThumbnails[$id] = [
                            'path' => ltrim($this->imageTagSourceParser->parse($imageTag), "/"),
                            'id' => $id,
                        ];

                        unset($awscdncloud);

                        break;
                    }

                    unset($awscdncloud);
                }
            }
        }

        return $menuThumbnails;
    }
}
