<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Mc_categoryOverride extends Mc_category
{
    public function hookDisplayHeaderMcCategory() 
    {
        $id_root_category = Context::getContext()->shop->getCategory();
        $categories       = $this->_getTreeCategories($id_root_category);
        $brands           = $this->_getTreeBrands();

        $this->context->smarty->assign([
            'categories' => $categories,
            'brands'     => $brands,
        ]);

        return $this->display(__FILE__, 'header.tpl');
    }

    public function ajaxGetCategories(int $id_parent_category = 2)
    {
        $ret = [];
        $categories = $this->_getTreeCategories($id_parent_category, 0);
        if(empty($categories)){
            exit(Tools::jsonEncode($ret));
        }

        $this->context->smarty->assign([
            'categories' => $categories,
            'id_parent_category' => $id_parent_category
        ]);

        $categories = $this->context->smarty->fetch(_PS_MODULE_DIR_.$this->name.'/views/templates/hook/categories.tpl');
        $ret['categories_childs'] = utf8_encode($categories);
        exit(Tools::jsonEncode($ret));
    }

    private function _getTreeCategories(int $id_parent_category = 2, int $childs = 0)
    {
        //? Alexey: added 05.05.2025 FIX for CDN
        if(empty($id_parent_category)) {
            return [];
        }
        
        $categories = Category::getChildren($id_parent_category, 1);

        foreach ($categories as $key => $value) {
            //add small images
            if (!isset($value["super_small_image"])) {
                if (Tools::hasMediaServer() || Tools::file_exists_cache(_PS_CAT_IMG_DIR_.$value['id_category'].'.jpg')) {
                    $image_url = $this->context->link->getCatImageLink($value['link_rewrite'], $value['id_category'], 'small_default');
                } else {
                    $image_url = "/img/no_photo.png";
                }

                $categories[$key]["img"] = $image_url;
            }
            //add url
            if (!isset($value["url"])) {
                $categories[$key]["url"] = $this->context->link->getCategoryLink($value["id_category"], $value['link_rewrite']);
            }

            $categories[$key]["has_childs"] = Category::hasChildren($value["id_category"], $this->context->language->id);

            if($childs > 0){
                $categories[$key]["childs"] = $this->_getTreeCategories($value["id_category"], ($childs - 1));
            }
        }

        return $categories;
    }

    private function _getTreeBrands() 
    {
        //? Alexey: added 05.05.2025 FIX for CDN
        $brands = Manufacturer::getManufacturers(true, $this->context->language->id, true);
        
        // add images && url
        foreach ($brands as $key => $brand) {
            if (Tools::hasMediaServer() || Tools::file_exists_cache(_PS_MANU_IMG_DIR_.$brand['id_manufacturer'].'.jpg')) {
                $image = $this->context->link->getManufacturerImageLink($brand['id_manufacturer'], 'small_default');
                $url   = $this->context->link->getManufacturerLink($brand['id_manufacturer']);
            } else {
                $image = '/img/m/ru-default-small_default.jpg';
            }

            $brands[$key]["url"] = (!empty($url)) ? $url : null;
            $brands[$key]["img"] = $image;
        }

        return $brands;
    }
}
