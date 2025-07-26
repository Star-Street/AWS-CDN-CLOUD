<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\Category\CategoryProductSearchProvider;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;

class Ps_FeaturedProductsOverride extends Ps_FeaturedProducts
{
    protected function getCategories(int $id_parent_category = 2) 
    {
        //* uses in home page for catalog menu categories
        if(empty($id_parent_category)) {
            return [];
        }

        $categories = Category::getChildren($id_parent_category, 1);
        foreach ($categories as $key => $value) {
            //add images
            if (!isset($value["super_small_image"])) {
                //? Alexey: added 05.05.2025 FIX for CDN
                if(Tools::hasMediaServer() || Tools::file_exists_cache(_PS_CAT_IMG_DIR_.$value['id_category'].'.jpg')){
                    $image_url = $this->context->link->getCatImageLink($value['link_rewrite'], $value['id_category'], 'subcategory_default');
                }else{
                    $image_url = '/img/no_photo.png';
                }
                $categories[$key]["img"] = $image_url;
            }
            //add url
            if (!isset($value["url"])) {
                $categories[$key]["url"] = $this->context->link->getCategoryLink($value["id_category"], $value['link_rewrite']);
            }
        }

        return $categories;
    }
}
