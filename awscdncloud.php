<?php
if (!defined("_PS_VERSION_")) exit;

require_once(dirname(__FILE__) . "/classes/S3Service.php");


class AwsCdnCloud extends Module
{
    public function __construct()
    {
        $this->name = "awscdncloud";
        $this->tab = "administration";
        $this->version = "1.0.0";
        $this->author = $this->l("YOURNAME");
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = array("min" => "1.6", "max" => _PS_VERSION_);

        parent::__construct();

        $this->displayName = "AWS S3 CDN cloud";
        $this->description = $this->l("Uploads media files to AWS S3 CDN");
        $this->confirmUninstall = $this->l("Are you sure you want to uninstall?");
    }

    public function install()
    {
        return parent::install() 
            && $this->registerHook("displayBackOfficeHeader")
            && $this->registerHook("actionAfterUpdateManufacturerFormHandler")
            && $this->registerHook("actionAfterUpdateCategoryFormHandler")
            && $this->resetConfigParams();
            // && $this->resetConfigParams() && $this->registerOverride();
    }

    public function uninstall()
    {
        Configuration::deleteByName("AWS_S3_BUCKET");
        Configuration::deleteByName("AWS_S3_KEY");
        Configuration::deleteByName("AWS_S3_SECRET");
        Configuration::deleteByName("AWS_S3_REGION");
        Configuration::deleteByName("AWS_S3_ENDPOINT");
        Configuration::deleteByName("AWS_S3_TOGGLE");
        Configuration::deleteByName("AWS_S3_PRODUCT_ENABLED");
        Configuration::deleteByName("AWS_S3_PRODUCT_DELETE");
        Configuration::deleteByName("AWS_S3_MANUF_ENABLED");
        Configuration::deleteByName("AWS_S3_MANUF_DELETE");
        Configuration::deleteByName("AWS_S3_CATEGORY_ENABLED");
        Configuration::deleteByName("AWS_S3_CATEGORY_DELETE");

        return parent::uninstall();
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        return null;
        // $controller = Tools::getValue("controller");
        // if (in_array($controller, ["AdminSuppliers", "AdminManufacturers"])) {
            // Media::addJsDef([
            //     "cdn_url" => Configuration::get("AWS_S3_CDNURL"),
            // ]);
            // $this->context->controller->addJS($this->_path . "views/js/aws-cloud-urlpatch.js?v=" . $this->version);
        // }
    }

    /**
     * Send image brands in CDN Backet after upload
     * (/sell/catalog/brands/[id]/edit)
     */
    public function hookActionAfterUpdateManufacturerFormHandler($params)
    {
        if (Configuration::get("AWS_S3_TOGGLE") && Configuration::get("AWS_S3_MANUF_ENABLED")) {
            $manufacturerId = (int) $params['id'];
            $imagePath = _PS_MANU_IMG_DIR_ . $manufacturerId . ".jpg";

            if (isset($params["form_data"]["logo"]) && $params["form_data"]["logo"]) {
                if (file_exists($imagePath)) {
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

                        $needRecreateImages = true;
                        $needDeleteFileServerAfterUpload = Configuration::get("AWS_S3_MANUF_DELETE");
                        $manuf = new ManufacturerUploader($s3Service, $logService, $fileService, $textHealper);
                        $manuf->uploadAll($manufacturerId, $needRecreateImages, $needDeleteFileServerAfterUpload);
                        unset($s3Service, $logService, $fileService, $textHealper, $manuf);
                    } catch (\Exception $e) {
                        $time = date("H:i");
                        $logService = new LogService();
                        $logService->log("error", "Time: {$time} | hookActionAfterUpdateManufacturerFormHandler | ID: $manufacturerId | Target: CDN | Status: 500 | Image: $imagePath: {$e->getMessage()}");
                        unset($time, $logService);
                        return $e;
                    }
                } else {
                    $time = date("H:i");
                    $logService = new LogService();
                    $logService->log("error", "Time: {$time} | hookActionAfterUpdateManufacturerFormHandler | ID: $manufacturerId | Target: CDN | Status: 500 | Image: $imagePath: file not found...}");
                    unset($time, $logService);
                }
            }
        }

        return false;
    }

    /**
     * Send image categories in CDN Backet after upload
     * (/sell/catalog/categories/[id]/edit)
     */
    public function hookActionAfterUpdateCategoryFormHandler($params)
    {
        if (Configuration::get("AWS_S3_TOGGLE") && Configuration::get("AWS_S3_CATEGORY_ENABLED")) {
            $categoryId = (int) $params['id'];
            $imagePath = _PS_CAT_IMG_DIR_ . $categoryId . ".jpg";

            if (   (isset($params["form_data"]["cover_image"]) && $params["form_data"]["cover_image"])
                || (isset($params["form_data"]["thumbnail_image"]) && $params["form_data"]["thumbnail_image"])
                || (isset($params["form_data"]["menu_thumbnail_images"]) && $params["form_data"]["menu_thumbnail_images"])) 
            {
                if (file_exists($imagePath)) {
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

                        $needRecreateImages = true;
                        $needDeleteFileServerAfterUpload = Configuration::get("AWS_S3_CATEGORY_DELETE");
                        $cat = new CategoryUploader($s3Service, $logService, $fileService, $textHealper);
                        $cat->uploadAll($categoryId, $needRecreateImages, $needDeleteFileServerAfterUpload);
                        unset($s3Service, $logService, $fileService, $textHealper, $cat);
                    } catch (\Exception $e) {
                        $time = date("H:i");
                        $logService = new LogService();
                        $logService->log("error", "Time: {$time} | hookActionAfterUpdateCategoryFormHandler | ID: $categoryId | Target: CDN | Status: 500 | Image: $imagePath: {$e->getMessage()}");
                        unset($time, $logService);
                        return $e;
                    }
                } else {
                    $time = date("H:i");
                    $logService = new LogService();
                    $logService->log("error", "Time: {$time} | hookActionAfterUpdateCategoryFormHandler | ID: $categoryId | Target: CDN | Status: 500 | Image: $imagePath: file not found...}");
                    unset($time, $logService);
                }
            }
        }

        return false;
    }

    public function getContent()
    {
        $output = "";

        if (Tools::isSubmit("submitAWSSettings")) {
            $awsBucket = Tools::getValue("AWS_S3_BUCKET");
            $awsKey = Tools::getValue("AWS_S3_KEY");
            $awsSecret = Tools::getValue("AWS_S3_SECRET");
            $awsRegion = Tools::getValue("AWS_S3_REGION");
            $awsEndpoint = Tools::getValue("AWS_S3_ENDPOINT");
            $awsToggle = Tools::getValue("AWS_S3_TOGGLE");

            Configuration::updateValue("AWS_S3_BUCKET", $awsBucket);
            Configuration::updateValue("AWS_S3_KEY", $awsKey);
            Configuration::updateValue("AWS_S3_SECRET", $awsSecret);
            Configuration::updateValue("AWS_S3_REGION", $awsRegion);
            Configuration::updateValue("AWS_S3_ENDPOINT", $awsEndpoint);
            Configuration::updateValue("AWS_S3_TOGGLE", $awsToggle);

            $output .= $this->displayConfirmation($this->l("Settings updated successfully"));
        } elseif (Tools::isSubmit("resetAWSSettings")) {
            $this->resetConfigParams();
        }

        return $output . $this->renderFormAdminAws();
    }

    public function getCdnStatus() 
    {
        if (Tools::hasMediaServer()) return Configuration::get("AWS_S3_TOGGLE");
        else return false;
    }

    public function getMediaServer($urlPath)
    {
        if (Tools::hasMediaServer()) return Tools::getMediaServer($urlPath);
        else return false;
    }

    private function resetConfigParams() 
    {
        return 
            Configuration::updateValue("AWS_S3_BUCKET", "") &&
            Configuration::updateValue("AWS_S3_KEY", "") &&
            Configuration::updateValue("AWS_S3_SECRET", "") &&
            Configuration::updateValue("AWS_S3_REGION", "") &&
            Configuration::updateValue("AWS_S3_ENDPOINT", "") &&
            Configuration::updateValue("AWS_S3_TOGGLE", false) &&
            Configuration::updateValue("AWS_S3_PRODUCT_ENABLED", false) &&
            Configuration::updateValue("AWS_S3_PRODUCT_DELETE", false) &&
            Configuration::updateValue("AWS_S3_MANUF_ENABLED", false) &&
            Configuration::updateValue("AWS_S3_MANUF_DELETE", false) &&
            Configuration::updateValue("AWS_S3_CATEGORY_ENABLED", false) &&
            Configuration::updateValue("AWS_S3_CATEGORY_DELETE", false);
    }

    private function addHeaderList()
    {
        $this->context->controller->addCSS($this->_path . "views/css/aws-cloud-styles.css", "all");
        $this->context->controller->addJS($this->_path . "views/js/aws-cloud-admin.js?v=" . $this->version);
    }

    private function addJsDefList() 
    {
        Media::addJsDef([
            "awscdncloud_token" => Tools::getAdminToken("module-awscdncloud-awsFormTest"),
            "awscdncloud_image_text_cdn" => "CDN",
            "awscdncloud_image_text_server" => $this->l("Server"),
            "awscdncloud_image_text_origin" => $this->l("Origin"),
        ]);
    }

    private function addSmartyList() 
    {
        $this->context->smarty->assign([
            "cdn_conf_params" => $this->renderFormConfigSettings(),
            "cdn_product_enabled" => Configuration::get("AWS_S3_PRODUCT_ENABLED"),
            "cdn_product_delete" => Configuration::get("AWS_S3_PRODUCT_DELETE"),
            "cdn_manuf_enabled" => Configuration::get("AWS_S3_MANUF_ENABLED"),
            "cdn_manuf_delete" => Configuration::get("AWS_S3_MANUF_DELETE"),
            "cdn_category_enabled" => Configuration::get("AWS_S3_CATEGORY_ENABLED"),
            "cdn_category_delete" => Configuration::get("AWS_S3_CATEGORY_DELETE"),
            "media_server_link" => $this->context->link->getAdminLink('AdminPerformance'),
            "cdn_version" => $this->version,
            "ps_version" => _PS_VERSION_,
        ]);
    }

    private function renderFormAdminAws()
    {
        $this->addHeaderList();
        $this->addJsDefList();
        $this->addSmartyList();

        return $this->context->smarty->fetch($this->local_path . "views/templates/admin/menu.tpl");
    }

    private function renderFormConfigSettings()
    {
        $adminPerformanceLink = $this->context->link->getAdminLink('AdminPerformance');

        $fields_form = [
            "form" => [
                "input" => [
                    [
                        "type" => "text",
                        "label" => "S3 Bucket",
                        "name" => "AWS_S3_BUCKET",
                        "required" => true,
                    ],
                    [
                        "type" => "text",
                        "label" => "S3 Access Key",
                        "name" => "AWS_S3_KEY",
                        "required" => true,
                    ],
                    [
                        "type" => "text",
                        "label" => "S3 Secret Key",
                        "name" => "AWS_S3_SECRET",
                        "required" => true,
                    ],
                    [
                        "type" => "text",
                        "label" => "S3 Region",
                        "name" => "AWS_S3_REGION",
                        "required" => true,
                    ],
                    [
                        "type" => "text",
                        "label" => "S3 endpoint",
                        "name" => "AWS_S3_ENDPOINT",
                        "required" => true,
                        "desc" => "e.g. s3.ru-1.storage.selcloud.ru",
                    ],
                    [
                        "type" => "html",
                        "label" => $this->l("Media Server"),
                        "name" => "AWS_S3_CDNURL",
                        "html_content" => "<p style=\"margin:8px 0 0 0;\">" . $this->l("use this link") . " <a href=\"$adminPerformanceLink\" target=\"_blank\">Admin Media Server</a></p>",
                    ],
                    [
                        "type" => "switch",
                        "label" => $this->l("CDN CloudFront"),
                        "name" => "AWS_S3_TOGGLE",
                        "is_bool" => true,
                        "desc" => $this->l("Be careful, this option affects the display of images"),
                        "values" => [
                            [
                                "id" => "active_on",
                                "value" => 1,
                                "label" => $this->l("Enabled"),
                            ],
                            [
                                "id" => "active_off",
                                "value" => 0,
                                "label" => $this->l("Disabled"),
                            ],
                        ],
                    ],
                    [
                        "type" => "html",
                        "label" => "",
                        "name" => "AWS_TEST_LOADER_FIELDS",
                        "html_content" => 
                            '<div id="AWS_TEST_FORM" style="margin:0px 0px 20px;display:none;">
                                <img id="AWS_TEST_LOADER" src="../img/loader.gif" style="height:24px;vertical-align:middle;" />
                                <span id="AWS_TEST_RESULT">' . $this->l('Connection...') . '</span>
                            </div>
                            <button id="AWS_TEST_BTN" type="button" class="btn btn-primary">' . $this->l('Test Connection') . '</button>',
                    ],
                ],
                "submit" => [
                    "title" => $this->l("Save"),
                    "name" => "submitAWSSettings",
                ],
                "buttons" => [
                    [
                        "title" => $this->l("Reset settings"),
                        "name"  => "resetAWSSettings",
                        "type"  => "submit",
                        "class" => "btn btn-secondary",
                    ],
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite("AdminModules");
        $helper->currentIndex = AdminController::$currentIndex . "&configure=" . $this->name;
        $helper->default_form_language = (int)Configuration::get("PS_LANG_DEFAULT");
        $helper->allow_employee_form_lang = Configuration::get("PS_BO_ALLOW_EMPLOYEE_FORM_LANG") ? Configuration::get("PS_BO_ALLOW_EMPLOYEE_FORM_LANG") : 0;

        $helper->fields_value = [
            "AWS_S3_BUCKET" => Configuration::get("AWS_S3_BUCKET"),
            "AWS_S3_KEY" => Configuration::get("AWS_S3_KEY"),
            "AWS_S3_SECRET" => Configuration::get("AWS_S3_SECRET"),
            "AWS_S3_REGION" => Configuration::get("AWS_S3_REGION"),
            "AWS_S3_ENDPOINT" => Configuration::get("AWS_S3_ENDPOINT"),
            "AWS_S3_TOGGLE" => Configuration::get("AWS_S3_TOGGLE"),
        ];

        return $helper->generateForm([$fields_form]);
    }

    private function registerOverride()
    {
        //! dont use this, is this work!?
        $this->copyOverride("classes/Category.php");
        $this->copyOverride("classes/ImageManager.php");
        $this->copyOverride("classes/Tools.php");
        $this->copyOverride("classes/Link.php");
        $this->copyOverride("controllers/admin/AdminProductsController.php");
        $this->copyOverride("modules/mc_category/mc_category.php");
        $this->copyOverride("modules/ps_featuredproducts/ps_featuredproducts.php");

        if (method_exists("PrestaShop\PrestaShop\Adapter\Module\ModuleManagerBuilder", "buildOverrides")) {
            $builder = PrestaShop\PrestaShop\Adapter\Module\ModuleManagerBuilder::getInstance();
            $builder->buildOverrides();
        }
        
        // Tools::generateIndex();
        // Tools::clearSmartyCache();
        // Tools::clearXMLCache();
        Tools::clearCache();

        return true;
    }

    private function copyOverride($relativePath)
    {
        $src = $this->getLocalPath() . "override/" . $relativePath;
        $dst = _PS_ROOT_DIR_ . "/override/" . $relativePath;

        if (!file_exists(dirname($dst))) {
            mkdir(dirname($dst), 0755, true);
        }

        copy($src, $dst);
    }
}
