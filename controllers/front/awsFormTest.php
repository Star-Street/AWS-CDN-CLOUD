<?php

class awscdncloudAwsFormTestModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (Tools::getIsset("ajax")) {
            $token = Tools::getValue("token");
            $expected_token = Tools::getAdminToken("module-awscdncloud-awsFormTest");
            if (!$token || !hash_equals($expected_token, $token)) die(json_encode([]));

            parent::initContent();

            if (Tools::getValue("action") === "test-conn") {
                if (Tools::getIsset("params")) {
                    $success = false;
                    $params = json_decode(Tools::getValue("params"));

                    $s3Service = new S3Service(
                        $params->bucket, 
                        $params->access, 
                        $params->secret, 
                        $params->region, 
                        $params->endpoint
                    );
                    $result = $s3Service->testConnection();
                    unset($s3Service);

                    if (!empty($result["error"])) {
                        die(json_encode(["error" => $result["error"]]));
                    }

                    if (!empty($result["content"]) && count($result["content"]) > 0) $success = true;
                    die(json_encode(["success" => $success]));
                }
            } elseif (Tools::getValue("action") === "cache-clear") {
                if (Tools::getIsset("params")) {
                    $params = json_decode(Tools::getValue("params"));

                    if (isset($params->name) && !empty($params->name)) {
                        $fileService = new FileService();
                        $success = $fileService->deleteTmpFiles(_PS_TMP_IMG_DIR_ . $params->name);
                        unset($fileService);

                        die(json_encode(["success" => $success]));
                    } else {
                        die(json_encode(["error" => "Error: wrong name params..."]));
                    }
                }
            } elseif (Tools::getValue("action") === "cdn-enabled") {
                if (Tools::getIsset("params")) {
                    $params = json_decode(Tools::getValue("params"));

                    if (isset($params->name) && !empty($params->name)) {
                        if (isset($params->toggle)) {

                            if ($params->name == "product") {
                                die(json_encode(["success" => Configuration::updateValue("AWS_S3_PRODUCT_ENABLED", $params->toggle)]));
                            } elseif ($params->name == "manufacturer") {
                                die(json_encode(["success" => Configuration::updateValue("AWS_S3_MANUF_ENABLED", $params->toggle)]));
                            } elseif ($params->name == "category") {
                                die(json_encode(["success" => Configuration::updateValue("AWS_S3_CATEGORY_ENABLED", $params->toggle)]));
                            } else {
                                die(json_encode(["error" => "Error: wrong name params..."]));
                            }
                        } else {
                            die(json_encode(["error" => "Error: empty toggle params..."]));
                        }
                    } else {
                        die(json_encode(["error" => "Error: empty name params..."]));
                    }
                }
            } elseif (Tools::getValue("action") === "cdn-disable") {
                if (Tools::getIsset("params")) {
                    $params = json_decode(Tools::getValue("params"));

                    if (isset($params->name) && !empty($params->name)) {
                        if (isset($params->toggle)) {

                            if ($params->name == "product") {
                                die(json_encode(["success" => Configuration::updateValue("AWS_S3_PRODUCT_DELETE", $params->toggle)]));
                            } elseif ($params->name == "manufacturer") {
                                die(json_encode(["success" => Configuration::updateValue("AWS_S3_MANUF_DELETE", $params->toggle)]));
                            } elseif ($params->name == "category") {
                                die(json_encode(["success" => Configuration::updateValue("AWS_S3_CATEGORY_DELETE", $params->toggle)]));
                            } else {
                                die(json_encode(["error" => "Error: wrong name params..."]));
                            }
                        } else {
                            die(json_encode(["error" => "Error: empty toggle params..."]));
                        }
                    } else {
                        die(json_encode(["error" => "Error: empty name params..."]));
                    }
                }
            } elseif (Tools::getValue("action") === "cdn-info") {
                try {
                    $s3Service = new S3Service(
                        Configuration::get("AWS_S3_BUCKET"), 
                        Configuration::get("AWS_S3_KEY"), 
                        Configuration::get("AWS_S3_SECRET"), 
                        Configuration::get("AWS_S3_REGION"), 
                        Configuration::get("AWS_S3_ENDPOINT")
                    );
                    $cdnCloudState = $s3Service->testConnection();
                    unset($s3Service);

                    $cdnStatus = "offline";
                    if (empty($cdnCloudState["error"])) {
                        if (!empty($cdnCloudState["content"]) && count($cdnCloudState["content"]) > 0) $cdnStatus = "online";
                    }

                    $cdnUrl = Tools::getShopProtocol() . Tools::getMediaServer("awsFormTest");
                    $cdnObjectStatus = Configuration::get("AWS_S3_TOGGLE") ? $this->l("Enabled") : $this->l("Disabled");
                    $cdnObjectBacket = Configuration::get("AWS_S3_BUCKET");
                    $cdnProductStatus = Configuration::get("AWS_S3_PRODUCT_ENABLED") ? $this->l("Enabled") : $this->l("Disabled");
                    $cdnProductDelete = Configuration::get("AWS_S3_PRODUCT_DELETE") ? $this->l("Enabled") : $this->l("Disabled");
                    $cdnManufStatus = Configuration::get("AWS_S3_MANUF_ENABLED") ? $this->l("Enabled") : $this->l("Disabled");
                    $cdnManufDelete = Configuration::get("AWS_S3_MANUF_DELETE") ? $this->l("Enabled") : $this->l("Disabled");
                    $cdnCategoryStatus = Configuration::get("AWS_S3_CATEGORY_ENABLED") ? $this->l("Enabled") : $this->l("Disabled");
                    $cdnCategoryDelete = Configuration::get("AWS_S3_CATEGORY_DELETE") ? $this->l("Enabled") : $this->l("Disabled");

                    $result = [
                        "cdn_status" => $cdnStatus,
                        "cdn_url" => $cdnUrl,
                        "cdn_object_status" => $cdnObjectStatus,
                        "cdn_object_backet" => $cdnObjectBacket,
                        "cdn_product_status" => $cdnProductStatus,
                        "cdn_product_delete" => $cdnProductDelete,
                        "cdn_manuf_status" => $cdnManufStatus,
                        "cdn_manuf_delete" => $cdnManufDelete,
                        "cdn_category_status" => $cdnCategoryStatus,
                        "cdn_category_delete" => $cdnCategoryDelete,
                    ];
    
                    die(json_encode(["success" => true, "content" => $result]));
                } catch(\Exception $e) {
                    die(json_encode(["error" => "error"]));
                }
            } elseif (Tools::getValue("action") === "image-status") {
                if (Tools::getIsset("params")) {
                    $params = json_decode(Tools::getValue("params"));

                    if (isset($params->name)) {
                        $nameEntity = $params->name;

                        if (isset($params->id) ) {
                            $idEntity = (int) $params->id;

                            if ($idEntity > 1) {
                                $images = [];

                                switch($nameEntity) {
                                    case "product":
                                        $images = $this->getImagesInfo("products", $idEntity);
                                        break;
                                    case "manufacturer":
                                        $images = $this->getImagesInfo("manufacturers", $idEntity);
                                        break;
                                    case "category":
                                        $images = $this->getImagesInfo("categories", $idEntity);
                                        break;
                                }

                                die(json_encode(["success" => true, "images" => $images]));
                            }
                        }
                    }
                }
            } elseif (Tools::getValue("action") === "image-del-server") {
                if (Tools::getIsset("params")) {
                    $params = json_decode(Tools::getValue("params"));

                    if (isset($params->name)) {
                        $nameEntity = $params->name;

                        if (isset($params->id) ) {
                            $idEntity = (int) $params->id;

                            if ($idEntity > 1) {
                                $images = [];
                                $mediaUrl = Tools::getShopProtocol() . Tools::getMediaServer("awsFormTest") . "/";

                                switch($nameEntity) {
                                    case "product":
                                        $this->delImagesFromServer("products", $idEntity);
                                        $images = $this->getImagesInfo("products", $idEntity);
                                        break;
                                    case "manufacturer":
                                        $this->delImagesFromServer("manufacturers", $idEntity);
                                        $images = $this->getImagesInfo("manufacturers", $idEntity);
                                        break;
                                    case "category":
                                        $this->delImagesFromServer("categories", $idEntity);
                                        $images = $this->getImagesInfo("categories", $idEntity);
                                        break;
                                }

                                die(json_encode(["success" => true, "images" => $images]));
                            }
                        }
                    }
                }
            } elseif (Tools::getValue("action") === "image-sync") {
                if (Tools::getIsset("params")) {
                    $params = json_decode(Tools::getValue("params"));

                    if (isset($params->name)) {
                        $nameEntity = $params->name;

                        if (isset($params->id) ) {
                            $idEntity = (int) $params->id;

                            if ($idEntity > 1) {
                                $images = [];
                                $result = false;

                                switch($nameEntity) {
                                    case "product":
                                        $needRecreateImages = true;
                                        $needDeleteFileServerAfterUpload = Configuration::get("AWS_S3_PRODUCT_DELETE");
                                        $result = $this->addObjectsCdn("products", $idEntity, $needRecreateImages, $needDeleteFileServerAfterUpload);
                                        $images = $this->getImagesInfo("products", $idEntity);
                                        break;
                                    case "manufacturer":
                                        $needRecreateImages = true;
                                        $needDeleteFileServerAfterUpload = Configuration::get("AWS_S3_MANUF_DELETE");
                                        $result = $this->addObjectsCdn("manufacturers", $idEntity, $needRecreateImages, $needDeleteFileServerAfterUpload);
                                        $images = $this->getImagesInfo("manufacturers", $idEntity);
                                        break;
                                    case "category":
                                        $needRecreateImages = true;
                                        $needDeleteFileServerAfterUpload = Configuration::get("AWS_S3_CATEGORY_DELETE");
                                        $result = $this->addObjectsCdn("categories", $idEntity, $needRecreateImages, $needDeleteFileServerAfterUpload);
                                        $images = $this->getImagesInfo("categories", $idEntity);
                                        break;
                                }

                                die(json_encode(["success" => $result, "images" => $images]));
                            }
                        }
                    }
                }
            } elseif (Tools::getValue("action") === "image-sync-all") {
                if (Tools::getIsset("params")) {
                    $params = json_decode(Tools::getValue("params"));

                    if (isset($params->name)) {
                        $result = false;
                        $nameEntity = $params->name;

                        switch($nameEntity) {
                            case "product":
                                if (Configuration::get("AWS_S3_PRODUCT_SYNC_ALL")) {
                                    die(json_encode(["success" => $result, "msg" => $this->l("Please wait, synchronization in progress") . "..."]));
                                }

                                Configuration::updateValue("AWS_S3_PRODUCT_SYNC_ALL", true);
                                try {
                                    $needRecreateImages = true;
                                    $needDeleteFileServerAfterUpload = Configuration::get("AWS_S3_PRODUCT_DELETE");
                                    $result = $this->addObjectsCdn("products", null, $needRecreateImages, $needDeleteFileServerAfterUpload);
                                } catch(\Exception $e) {
                                    die(json_encode(["success" => $result, "msg" => $e]));
                                } finally {
                                    Configuration::updateValue("AWS_S3_PRODUCT_SYNC_ALL", false);
                                }
                                break;
                            case "manufacturer":
                                if (Configuration::get("AWS_S3_MANUFACTURER_SYNC_ALL")) {
                                    die(json_encode(["success" => $result, "msg" => $this->l("Please wait, synchronization in progress") . "..."]));
                                }

                                Configuration::updateValue("AWS_S3_MANUFACTURER_SYNC_ALL", true);
                                try {
                                    $needRecreateImages = true;
                                    $needDeleteFileServerAfterUpload = Configuration::get("AWS_S3_MANUF_DELETE");
                                    $result = $this->addObjectsCdn("manufacturers", null, $needRecreateImages, $needDeleteFileServerAfterUpload);
                                } catch(\Exception $e) {
                                    die(json_encode(["success" => $result, "msg" => $e]));
                                } finally {
                                    Configuration::updateValue("AWS_S3_MANUFACTURER_SYNC_ALL", false);
                                }
                                break;
                            case "category":
                                if (Configuration::get("AWS_S3_CATEGORY_SYNC_ALL")) {
                                    die(json_encode(["success" => $result, "msg" => $this->l("Please wait, synchronization in progress") . "..."]));
                                }

                                Configuration::updateValue("AWS_S3_CATEGORY_SYNC_ALL", true);
                                try {
                                    $needRecreateImages = true;
                                    $needDeleteFileServerAfterUpload = Configuration::get("AWS_S3_CATEGORY_DELETE");
                                    $result = $this->addObjectsCdn("categories", null, $needRecreateImages, $needDeleteFileServerAfterUpload);
                                } catch(\Exception $e) {
                                    die(json_encode(["success" => $result, "msg" => $e]));
                                } finally {
                                    Configuration::updateValue("AWS_S3_CATEGORY_SYNC_ALL", false);
                                }
                                break;
                        }

                        die(json_encode(["success" => $result, "msg" => ""]));
                    }
                }
            }

            die(json_encode(["error" => "Error: some error in request..."]));
        }

        die(json_encode([]));
    }

    private function implodeId(int $id): string 
    {
        $folders = str_split((string) $id);
        return implode("/", $folders) . "/";
    }

    private function getObjectsCdn(string $path): array
    {
        $objects = [];

        $s3Service = new S3Service(
            Configuration::get("AWS_S3_BUCKET"), 
            Configuration::get("AWS_S3_KEY"), 
            Configuration::get("AWS_S3_SECRET"), 
            Configuration::get("AWS_S3_REGION"), 
            Configuration::get("AWS_S3_ENDPOINT")
        );
        $listObjects = $s3Service->listObjects($path);
        $objects = array_column($listObjects, "Key");
        unset($s3Service, $listObjects);

        return $objects;
    }

    private function addObjectsCdn(string $type, $idEntity = null, bool $needRecreateImages, bool $needDeleteFileServerAfterUpload): array
    {
        $addedObjects = [];

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

        switch($type) {
            case "products":
                try {
                    $prod = new ProductUploader($s3Service, $logService, $fileService, $textHealper);
                    $addedObjects = $prod->uploadAll($idEntity, $needRecreateImages, $needDeleteFileServerAfterUpload);
                    unset($prod);
                } catch(\Exception $e) {
                    die(json_encode(["error" => $e]));
                }
                break;
            case "manufacturers":
                try {
                    $manuf = new ManufacturerUploader($s3Service, $logService, $fileService, $textHealper);
                    $addedObjects = $manuf->uploadAll($idEntity, $needRecreateImages, $needDeleteFileServerAfterUpload);
                    unset($manuf);
                } catch(\Exception $e) {
                    die(json_encode(["error" => $e]));
                }
                break;
            case "categories":
                try {
                    $cat = new CategoryUploader($s3Service, $logService, $fileService, $textHealper);
                    $addedObjects = $cat->uploadAll($idEntity, $needRecreateImages, $needDeleteFileServerAfterUpload);
                    unset($cat);
                } catch(\Exception $e) {
                    die(json_encode(["error" => $e]));
                }
                break;
        }

        unset($s3Service, $logService, $fileService, $textHealper);

        return $addedObjects;
    }

    private function getImagesInfo(string $type, int $idEntity): array
    {
        $images = [];
        $mediaUrl = Tools::getShopProtocol() . Tools::getMediaServer("awsFormTest") . "/";

        switch($type) {
            case "products":
                $imagesEntity = Image::getImages($this->context->language->id, $idEntity);

                foreach ($imagesEntity as $image) {
                    $files = [];
                    $cover = !empty($image["cover"]);
                    $idEntity = (int) $image["id_product"];
                    $idImage = (int) $image["id_image"];
                    $implodeId = $this->implodeId($idImage);

                    //* Server types image
                    $imagesTypes = ImageType::getImagesTypes("products");
                    foreach ($imagesTypes as $type) {
                        $type = $type["name"];
                        $fileName = "{$idImage}-{$type}.jpg";
                        $location = "img/p/{$implodeId}{$idImage}-{$type}.jpg";
                        $serverPath = _PS_PROD_IMG_DIR_ . "{$implodeId}{$idImage}-{$type}.jpg";
                        $serverUrl = _PS_BASE_URL_ . "/{$location}";
                        $isOnServer = file_exists($serverPath);

                        $files[] = [
                            "id_entity"  => $idEntity,
                            "id_image"   => $idImage,
                            "cover"      => $cover,
                            "type"       => $type,
                            "file_name"  => $fileName,
                            "location"   => $location,
                            "server_url" => $serverUrl,
                            "on_server"  => $isOnServer,
                        ];
                    }
                    unset($imagesTypes);

                    //* Server default image
                    $fileName = "{$idImage}.jpg";
                    $location = "img/p/{$implodeId}{$idImage}.jpg";
                    $serverPath = _PS_PROD_IMG_DIR_ . "{$implodeId}{$idImage}.jpg";
                    $serverUrl = _PS_BASE_URL_ . "/{$location}";
                    $isOnServer = file_exists($serverPath);
                    $files[] = [
                        "id_entity"  => $idEntity,
                        "id_image"   => $idImage,
                        "cover"      => $cover,
                        "type"       => "origin",
                        "file_name"  => $fileName,
                        "location"   => $location,
                        "server_url" => $serverUrl,
                        "on_server"  => $isOnServer,
                    ];

                    //* CDN
                    $objectsCdn = $this->getObjectsCdn("img/p/{$implodeId}");
                    foreach ($files as $key => $file) {
                        if (in_array($file["location"], $objectsCdn)) {
                            $files[$key]["on_cdn"] = true;
                            $files[$key]["cdn_url"] = $mediaUrl . $file["location"];
                        } else {
                            $files[$key]["on_cdn"] = false;
                            $files[$key]["cdn_url"] = "";
                        }
                    }

                    $images = array_merge($images, $files);
                }
                break;
            case "manufacturers":
                //* Server types image
                $files = [];

                $imagesTypes = ImageType::getImagesTypes($type);
                foreach ($imagesTypes as $type) {
                    $type = $type["name"];
                    $fileName = "{$idEntity}-{$type}.jpg";
                    $location = "img/m/{$idEntity}-{$type}.jpg";
                    $serverPath = _PS_MANU_IMG_DIR_ . "{$idEntity}-{$type}.jpg";
                    $isOnServer = file_exists($serverPath);
                    $serverUrl = _PS_BASE_URL_ . "/{$location}";

                    $files[] = [
                        "id_entity"  => $idEntity,
                        "id_image"   => $idEntity,
                        "cover"      => 1,
                        "type"       => $type,
                        "file_name"  => $fileName,
                        "location"   => $location,
                        "server_url" => $serverUrl,
                        "on_server"  => $isOnServer,
                    ];
                }

                //* Server default image
                $fileName = "{$idEntity}.jpg";
                $location = "img/m/{$idEntity}.jpg";
                $serverPath = _PS_MANU_IMG_DIR_ . "{$idEntity}.jpg";
                $isOnServer = file_exists($serverPath);
                $serverUrl = _PS_BASE_URL_ . "/{$location}";
                $files[] = [
                    "id_entity"  => $idEntity,
                    "id_image"   => $idEntity,
                    "cover"      => 1,
                    "type"       => "origin",
                    "file_name"  => $fileName,
                    "location"   => $location,
                    "server_url" => $serverUrl,
                    "on_server"  => $isOnServer,
                ];

                //* CDN
                $objectsCdn = $this->getObjectsCdn("img/m/{$idEntity}");
                foreach ($files as $key => $file) {
                    if (in_array($file["location"], $objectsCdn)) {
                        $files[$key]["on_cdn"] = true;
                        $files[$key]["cdn_url"] = $mediaUrl . $file["location"];
                    } else {
                        $files[$key]["on_cdn"] = false;
                        $files[$key]["cdn_url"] = "";
                    }
                }

                $images = array_merge($images, $files);
                break;
            case "categories":
                //* Server types image
                $files = [];
                $imagesTypes = ImageType::getImagesTypes($type);

                foreach ($imagesTypes as $type) {
                    $type = $type["name"];
                    $fileName = "{$idEntity}-{$type}.jpg";
                    $location = "img/c/{$idEntity}-{$type}.jpg";
                    $serverPath = _PS_CAT_IMG_DIR_ . "{$idEntity}-{$type}.jpg";
                    $isOnServer = file_exists($serverPath);
                    $serverUrl = _PS_BASE_URL_ . "/{$location}";

                    $files[] = [
                        "id_entity"  => $idEntity,
                        "id_image"   => $idEntity,
                        "cover"      => 1,
                        "type"       => $type,
                        "file_name"  => $fileName,
                        "location"   => $location,
                        "server_url" => $serverUrl,
                        "on_server"  => $isOnServer,
                    ];
                }

                //* Server default image
                $fileName = "{$idEntity}.jpg";
                $location = "img/c/{$idEntity}.jpg";
                $serverPath = _PS_CAT_IMG_DIR_ . "{$idEntity}.jpg";
                $isOnServer = file_exists($serverPath);
                $serverUrl = _PS_BASE_URL_ . "/{$location}";
                $files[] = [
                    "id_entity"  => $idEntity,
                    "id_image"   => $idEntity,
                    "cover"      => 1,
                    "type"       => "origin",
                    "file_name"  => $fileName,
                    "location"   => $location,
                    "server_url" => $serverUrl,
                    "on_server"  => $isOnServer,
                ];

                //* CDN
                $objectsCdn = $this->getObjectsCdn("img/c/{$idEntity}");
                foreach ($files as $key => $file) {
                    if (in_array($file["location"], $objectsCdn)) {
                        $files[$key]["on_cdn"] = true;
                        $files[$key]["cdn_url"] = $mediaUrl . $file["location"];
                    } else {
                        $files[$key]["on_cdn"] = false;
                        $files[$key]["cdn_url"] = "";
                    }
                }

                $images = array_merge($images, $files);
                break;
        }

        return $images;
    }

    private function delImagesFromServer(string $type, int $idEntity): bool
    {
        switch($type) {
            case "products":
                $imagesEntity = Image::getImages($this->context->language->id, $idEntity);

                foreach ($imagesEntity as $image) {
                    $implodeId = $this->implodeId($image["id_image"]);
                    $fileService = new FileService();
                    $filesServer = $fileService->getFiles(_PS_PROD_IMG_DIR_ . $implodeId . $image["id_image"]);
                    foreach ($filesServer as $filePath) {
                        $fileService->deleteFile($filePath);
                    }
                    unset($fileService, $filesServer);
                }
                break;
            case "manufacturers":
                $fileService = new FileService();
                $filesServer = $fileService->getFiles(_PS_MANU_IMG_DIR_ . $idEntity);
                foreach ($filesServer as $filePath) {
                    $fileService->deleteFile($filePath);
                }
                unset($fileService, $filesServer);
                break;
            case "categories":
                $fileService = new FileService();
                $filesServer = $fileService->getFiles(_PS_CAT_IMG_DIR_ . $idEntity);
                foreach ($filesServer as $filePath) {
                    $fileService->deleteFile($filePath);
                }
                unset($fileService, $filesServer);
                break;
        }

        return true;
    }
}
