$(function() {
    const ALERT_DELAY = 1500;
    const ALERT_CLASSES = "alert-success alert-danger alert-warning alert-info";
    const SELECTORS = {
        awsBtn: "#AWS_TEST_BTN",
        awsForm: "#AWS_TEST_FORM",
        awsLoader: "#AWS_TEST_LOADER",
        awsResult: "#AWS_TEST_RESULT",
        awsBucket: "#AWS_S3_BUCKET",
        awsKey: "#AWS_S3_KEY",
        awsSecret: "#AWS_S3_SECRET",
        awsRegion: "#AWS_S3_REGION",
        awsEndpoint: "#AWS_S3_ENDPOINT",

        awsCdnEnabled: "aws_s3_enabled",
        awsCdnDelete: "aws_s3_delete",

        cacheBtn: "cache_btn",
        cacheAlert: "cache_alert",
        cacheLoaderImg: "cache_loader",
        cacheLoaderText: "cache_text",
        cacheDefaultText: "cache_text_tmp",
        cacheLoadingText: "loader_text_tmp",

        imageClearBtn: "image_clear_btn",
        imageSyncBtn: "image_sync_btn",
        imageSyncLoader: "image_sync_loader",
        imageSyncAllBtn: "image_sync_all_btn",
        imageSyncAllLoader: "image_sync_all_loader",
        imageDelBtn: "image_del_server_btn",
        imageDelLoader: "image_del_server_loader",
        imageSearchBtn: "image_search_btn",
        imageSearchLoader: "image_search_loader",
        imageSearchEntity: "image_search_input",
        imageSearchList: "image_search_list",
        imageSearchListLoader: "image_search_list_loader",

        infoCdnStatus: "cdn-info-status",
        infoCdnUrl: "cdn-info-url",
        infoObjectStatus: "cdn-object-status",
        infoObjectBacket: "cdn-object-backet",
        infoProductStatus: "cdn-product-status",
        infoProductDelete: "cdn-product-delete",
        infoManufStatus: "cdn-manuf-status",
        infoManufDelete: "cdn-manuf-delete",
        infoCategoryStatus: "cdn-category-status",
        infoCategoryDelete: "cdn-category-delete",
    };

    function setAlertClass($container, alertClass) {
        $container.removeClass(ALERT_CLASSES).addClass(alertClass);
    }

    function updateStatus($alert, $loaderImg, $loaderText, alertClass, text, disableBtn = false, $btn = null) {
        setAlertClass($alert, alertClass);
        if ($loaderText) $loaderText.text(text);
        if ($loaderImg) $loaderImg.toggle(alertClass === "alert-warning");
        if ($btn) $btn.prop("disabled", disableBtn);
    }

    function resetUI($alert, $loaderImg, $loaderText, defaultText, $btn) {
        setAlertClass($alert, "alert-info");
        if ($loaderText) $loaderText.text(defaultText);
        if ($loaderImg) $loaderImg.hide();
        if ($btn) $btn.prop("disabled", false);
    }

    function showInfoLoader(isEnabled) {
        if (isEnabled) {
            $(`#${SELECTORS.infoCdnStatus}-loader`).show();
            $(`#${SELECTORS.infoCdnUrl}-loader`).show();
            $(`#${SELECTORS.infoObjectStatus}-loader`).show();
            $(`#${SELECTORS.infoObjectBacket}-loader`).show();
            $(`#${SELECTORS.infoProductStatus}-loader`).show();
            $(`#${SELECTORS.infoProductDelete}-loader`).show();
            $(`#${SELECTORS.infoManufStatus}-loader`).show();
            $(`#${SELECTORS.infoManufDelete}-loader`).show();
            $(`#${SELECTORS.infoCategoryStatus}-loader`).show();
            $(`#${SELECTORS.infoCategoryDelete}-loader`).show();

            $(`#${SELECTORS.infoCdnStatus}`).hide();
            $(`#${SELECTORS.infoCdnUrl}`).hide();
            $(`#${SELECTORS.infoObjectStatus}`).hide();
            $(`#${SELECTORS.infoObjectBacket}`).hide();
            $(`#${SELECTORS.infoProductStatus}`).hide();
            $(`#${SELECTORS.infoProductDelete}`).hide();
            $(`#${SELECTORS.infoManufStatus}`).hide();
            $(`#${SELECTORS.infoManufDelete}`).hide();
            $(`#${SELECTORS.infoCategoryStatus}`).hide();
            $(`#${SELECTORS.infoCategoryDelete}`).hide();
        } else {
            $(`#${SELECTORS.infoCdnStatus}-loader`).hide();
            $(`#${SELECTORS.infoCdnUrl}-loader`).hide();
            $(`#${SELECTORS.infoObjectStatus}-loader`).hide();
            $(`#${SELECTORS.infoObjectBacket}-loader`).hide();
            $(`#${SELECTORS.infoProductStatus}-loader`).hide();
            $(`#${SELECTORS.infoProductDelete}-loader`).hide();
            $(`#${SELECTORS.infoManufStatus}-loader`).hide();
            $(`#${SELECTORS.infoManufDelete}-loader`).hide();
            $(`#${SELECTORS.infoCategoryStatus}-loader`).hide();
            $(`#${SELECTORS.infoCategoryDelete}-loader`).hide();

            $(`#${SELECTORS.infoCdnStatus}`).show();
            $(`#${SELECTORS.infoCdnUrl}`).show();
            $(`#${SELECTORS.infoObjectStatus}`).show();
            $(`#${SELECTORS.infoObjectBacket}`).show();
            $(`#${SELECTORS.infoProductStatus}`).show();
            $(`#${SELECTORS.infoProductDelete}`).show();
            $(`#${SELECTORS.infoManufStatus}`).show();
            $(`#${SELECTORS.infoManufDelete}`).show();
            $(`#${SELECTORS.infoCategoryStatus}`).show();
            $(`#${SELECTORS.infoCategoryDelete}`).show();
        }
    }

    function getInfoState() {
        if ($("#cdn-info-tab").hasClass("active")) {
            showInfoLoader(true);
    
            const formData = new FormData();
            formData.append("ajax",1);
            formData.append("token", awscdncloud_token);
            formData.append("action", "cdn-info");
    
            $.ajax({
                url: "/index.php?fc=module&module=awscdncloud&controller=awsFormTest",
                type: "POST",
                dataType: "json",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    showInfoLoader(false);
                    if (response.success) {
                        setInfoText(response.content);
                    } else {
                        setInfoText(response.error);
                    }
                },
                error: function() {
                    showInfoLoader(false);
                    setInfoText("error ajax");
                }
            });
        }
    }

    function setInfoText(content) {
        $(`#${SELECTORS.infoCdnStatus}`).html(content.cdn_status);
        $(`#${SELECTORS.infoCdnUrl}`).attr("href", content.cdn_url).html(content.cdn_url);
        $(`#${SELECTORS.infoObjectStatus}`).html(content.cdn_object_status);
        $(`#${SELECTORS.infoObjectBacket}`).html(content.cdn_object_backet);
        $(`#${SELECTORS.infoProductStatus}`).html(content.cdn_product_status);
        $(`#${SELECTORS.infoProductDelete}`).html(content.cdn_product_delete);
        $(`#${SELECTORS.infoManufStatus}`).html(content.cdn_manuf_status);
        $(`#${SELECTORS.infoManufDelete}`).html(content.cdn_manuf_delete);
        $(`#${SELECTORS.infoCategoryStatus}`).html(content.cdn_category_status);
        $(`#${SELECTORS.infoCategoryDelete}`).html(content.cdn_category_delete);
    }

    function handleCdnAction($btn, action, errorMsg) {
        const type = $btn.data("type");

        const $alert = $("#" + type + "_" + SELECTORS.cacheAlert);
        const $loaderImg = $("#" + type + "_" + SELECTORS.cacheLoaderImg);
        const $loaderText = $("#" + type + "_" + SELECTORS.cacheLoaderText);
        const defaultText = $("#" + type + "_" + SELECTORS.cacheDefaultText).val();

        const formData = new FormData();
        formData.append("ajax", 1);
        formData.append("token", awscdncloud_token);
        formData.append("action", action);
        formData.append("params", JSON.stringify({ name: type, toggle: $btn.val() }));

        $.ajax({
            url: "/index.php?fc=module&module=awscdncloud&controller=awsFormTest",
            type: "POST",
            dataType: "json",
            data: formData,
            contentType: false,
            processData: false,
            success(response) {
                if (response.success) {
                    updateStatus($alert, $loaderImg, $loaderText, "alert-success", "Success!", true, $btn);
                } else {
                    updateStatus($alert, $loaderImg, $loaderText, "alert-danger", errorMsg, true, $btn);
                }
                setTimeout(function() {
                    resetUI($alert, $loaderImg, $loaderText, defaultText, $btn);
                }, ALERT_DELAY);
            },
            error() {
                updateStatus($alert, $loaderImg, $loaderText, "alert-danger", "Server error...", true, $btn);
                setTimeout(function() {
                    resetUI($alert, $loaderImg, $loaderText, defaultText, $btn);
                }, ALERT_DELAY);
            }
        });
    }

    function imageSearchListControlUI(type, toggle) {
        if (toggle) {
            $("#" + type + "_" + SELECTORS.imageDelBtn).prop("disabled", true);
            $("#" + type + "_" + SELECTORS.imageSyncBtn).prop("disabled", true);
            $("#" + type + "_" + SELECTORS.imageSearchBtn).prop("disabled", true);
            $("#" + type + "_" + SELECTORS.imageClearBtn).prop("disabled", true);
            $("#" + type + "_" + SELECTORS.imageSyncAllBtn).prop("disabled", true);
            $("#" + type + "_" + SELECTORS.imageSearchList).empty();
            $("#" + type + "_" + SELECTORS.imageSearchListLoader).show();
        } else {
            $("#" + type + "_" + SELECTORS.imageDelBtn).prop("disabled", false);
            $("#" + type + "_" + SELECTORS.imageSyncBtn).prop("disabled", false);
            $("#" + type + "_" + SELECTORS.imageSearchBtn).prop("disabled", false);
            $("#" + type + "_" + SELECTORS.imageClearBtn).prop("disabled", false);
            $("#" + type + "_" + SELECTORS.imageSyncAllBtn).prop("disabled", false);
            $("#" + type + "_" + SELECTORS.imageSearchListLoader).hide();
        }
    }


    // ----------------- Menu Style Button -----------------
    $(".nav-pills a.list-group-item").on("click", function(e) {
        let target = $(this).attr("href");
        if (target && target[0] == "#") {
            e.preventDefault();
            $(".nav-pills a.list-group-item").removeClass("active");
            $(this).addClass("active");
        }
    });

    // ----------------- Menu Info Update Button -----------------
    $("#cdn-info-tab").on("click", function(e) {
        getInfoState();
    });

    // ----------------- Test Connection Button -----------------
    $(SELECTORS.awsBtn).on("click", function() {
        $(SELECTORS.awsForm).show();
        $(SELECTORS.awsLoader).show();
        $(SELECTORS.awsResult).html("Connection...");

        const params = {
            "bucket": $(SELECTORS.awsBucket).val(),
            "access": $(SELECTORS.awsKey).val(),
            "secret": $(SELECTORS.awsSecret).val(),
            "region": $(SELECTORS.awsRegion).val(),
            "endpoint": $(SELECTORS.awsEndpoint).val(),
        };

        const formData = new FormData();
        formData.append("ajax",1);
        formData.append("token", awscdncloud_token);
        formData.append("action", "test-conn");
        formData.append("params", JSON.stringify(params));

        $.ajax({
            url: "/index.php?fc=module&module=awscdncloud&controller=awsFormTest",
            type: "POST",
            dataType: "json",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                $(SELECTORS.awsLoader).hide();
                if (response.success) {
                    $(SELECTORS.awsResult).html('<strong class="text-success">Success!</strong>');
                } else {
                    $(SELECTORS.awsResult).html(`<strong class="text-danger">${response.error}</strong>`);
                }
            },
            error: function() {
                $(SELECTORS.awsLoader).hide();
                $(SELECTORS.awsResult).html('<strong class="text-danger">Error ajax request...</strong>');
            }
        });
    });

    // ----------------- Cache Clear Button -----------------
    $(".btn.cache-clear").on("click", function() {
        const $btn = $(this);
        const type = $btn.data("type");

        const $alert = $("#" + type + "_" + SELECTORS.cacheAlert);
        const $loaderImg = $("#" + type + "_" + SELECTORS.cacheLoaderImg);
        const $loaderText = $("#" + type + "_" + SELECTORS.cacheLoaderText);
        const defaultText = $("#" + type + "_" + SELECTORS.cacheDefaultText).val();
        const loadingText = $("#" + type + "_" + SELECTORS.cacheLoadingText).val();

        updateStatus($alert, $loaderImg, $loaderText, "alert-warning", loadingText, true, $btn);

        const formData = new FormData();
        formData.append("ajax", 1);
        formData.append("token", awscdncloud_token);
        formData.append("action", "cache-clear");
        formData.append("params", JSON.stringify({ name: type }));

        $.ajax({
            url: "/index.php?fc=module&module=awscdncloud&controller=awsFormTest",
            type: "POST",
            dataType: "json",
            data: formData,
            contentType: false,
            processData: false,
            success(response) {
                if (response.success) {
                    updateStatus($alert, $loaderImg, $loaderText, "alert-success", "Success!", true, $btn);
                } else {
                    updateStatus($alert, $loaderImg, $loaderText, "alert-danger", "Error clearing cache...", true, $btn);
                }
                setTimeout(function() {
                    resetUI($alert, $loaderImg, $loaderText, defaultText, $btn);
                }, ALERT_DELAY);
            },
            error() {
                updateStatus($alert, $loaderImg, $loaderText, "alert-danger", "Server error...", true, $btn);
                setTimeout(function() {
                    resetUI($alert, $loaderImg, $loaderText, defaultText, $btn);
                }, ALERT_DELAY);
            }
        });
    });

    // ----------------- CDN Image Send Enabled Button -----------------
    $(".switch input.cdn-enabled").on("click", function() {
        handleCdnAction($(this), "cdn-enabled", "Error CDN enabled...");
    });

    // ----------------- Image Server Delete Button -----------------
    $(".switch input.cdn-disable").on("click", function() {
        handleCdnAction($(this), "cdn-disable", "Error server image delete...");
    });

    // ----------------- Search Image Button -----------------
    $(".btn.image-search").on("click", function() {
        const $btn = $(this);
        const type = $btn.data("type");
        const idEntity = Number($("#" + type + "_" + SELECTORS.imageSearchEntity).val());

        if (idEntity > 1) {
            $("#" + type + "_" + SELECTORS.imageSearchList).hide();
            $("#" + type + "_" + SELECTORS.imageSearchList).empty();
            $("#" + type + "_" + SELECTORS.imageSearchBtn).prop("disabled", true);
            $("#" + type + "_" + SELECTORS.imageSearchLoader).show();
            $("#" + type + "_" + SELECTORS.imageDelBtn).prop("disabled", true);
            $("#" + type + "_" + SELECTORS.imageSyncBtn).prop("disabled", true);
            $("#" + type + "_" + SELECTORS.imageSyncAllBtn).prop("disabled", true);

            const formData = new FormData();
            formData.append("ajax", 1);
            formData.append("token", awscdncloud_token);
            formData.append("action", "image-status");
            formData.append("params", JSON.stringify({ name: type, id: idEntity }));

            $.ajax({
                url: "/index.php?fc=module&module=awscdncloud&controller=awsFormTest",
                type: "POST",
                dataType: "json",
                data: formData,
                contentType: false,
                processData: false,
                success(response) {
                    if (response.success) {
                        if (response.images.length) {
                            let content = ``;

                            // add content
                            response.images.forEach(image => {
                                const isOnServer = !!image.on_server;
                                const isOnCdn = !!image.on_cdn;
                                const isOrigin = image.type === "origin";

                                const serverStatus = isOnServer ? "alert-success" : "alert-danger";
                                const cdnStatus = isOnCdn ? "alert-success" : "alert-danger";
                                const entityOriginAttr = isOrigin ? "" : 'style="display:none;"';

                                let entityUrl = image.file_name;
                                if (isOnCdn) {
                                    if (image.type == "origin") {
                                        $("#" + type + "_" + SELECTORS.imageDelBtn).prop("disabled", false);
                                    }
                                    entityUrl = `<a href="${image.cdn_url}" target="_blank">${image.file_name}</a>`;
                                } else if (isOnServer) {
                                    entityUrl = `<a href="${image.server_url}" target="_blank">${image.file_name}</a>`;
                                }

                                content += 
                                    `<div class="images-search-item clearfix">
                                        <span class="images-search-name">${entityUrl}</span>
                                        <span class="images-search-status alert-info" ${entityOriginAttr}>${awscdncloud_image_text_origin}</span>
                                        <span class="images-search-status ${cdnStatus}">${awscdncloud_image_text_cdn}</span>
                                        <span class="images-search-status ${serverStatus}">${awscdncloud_image_text_server}</span>
                                        <div class="images-search-desc">${image.location}</div>
                                    </div>`;
                            });

                            // enable buttons option
                            if (content.length > 1) {
                                $("#" + type + "_" + SELECTORS.imageSyncBtn).prop("disabled", false);
                            }
                            
                            $("#" + type + "_" + SELECTORS.imageSyncAllBtn).prop("disabled", false);
                            $("#" + type + "_" + SELECTORS.imageSearchList).append(content);
                            $("#" + type + "_" + SELECTORS.imageSearchList).show();
                        } else {
                            $("#" + type + "_" + SELECTORS.imageDelBtn).prop("disabled", true);
                        }
                    } else {
                        console.error(response.error);
                    }

                    $("#" + type + "_" + SELECTORS.imageSearchBtn).prop("disabled", false);
                    $("#" + type + "_" + SELECTORS.imageSearchLoader).hide();
                },
                error() {
                    console.error("Error: some error in ajax...");
                    $("#" + type + "_" + SELECTORS.imageSearchBtn).prop("disabled", false);
                    $("#" + type + "_" + SELECTORS.imageSearchLoader).hide();
                }
            });
        }
    });

    // ----------------- Search Image Clear Button -----------------
    $(".btn.image-clear").on("click", function() {
        const $btn = $(this);
        const type = $btn.data("type");

        $("#" + type + "_" + SELECTORS.imageSearchEntity).val("");
        $("#" + type + "_" + SELECTORS.imageSearchList).hide();
        $("#" + type + "_" + SELECTORS.imageSearchList).empty();
        $("#" + type + "_" + SELECTORS.imageSyncBtn).prop("disabled", true);
        $("#" + type + "_" + SELECTORS.imageDelBtn).prop("disabled", true);
        $("#" + type + "_" + SELECTORS.imageSyncAllBtn).prop("disabled", false);
    });

    // ----------------- Search Image Del Server Button -----------------
    $(".btn.image-del-server").on("click", function() {
        const $btn = $(this);
        const type = $btn.data("type");
        const idEntity = Number($("#" + type + "_" + SELECTORS.imageSearchEntity).val());

        if (idEntity > 1) {
            imageSearchListControlUI(type, true);
            $("#" + type + "_" + SELECTORS.imageDelLoader).show();

            const formData = new FormData();
            formData.append("ajax", 1);
            formData.append("token", awscdncloud_token);
            formData.append("action", "image-del-server");
            formData.append("params", JSON.stringify({ name: type, id: idEntity }));

            $.ajax({
                url: "/index.php?fc=module&module=awscdncloud&controller=awsFormTest",
                type: "POST",
                dataType: "json",
                data: formData,
                contentType: false,
                processData: false,
                success(response) {
                    if (response.success) {
                        if (response.images.length) {
                            let content = ``;

                            // add content
                            response.images.forEach(image => {
                                const isOnServer = !!image.on_server;
                                const isOnCdn = !!image.on_cdn;
                                const isOrigin = image.type === "origin";

                                const serverStatus = isOnServer ? "alert-success" : "alert-danger";
                                const cdnStatus = isOnCdn ? "alert-success" : "alert-danger";
                                const entityOriginAttr = isOrigin ? "" : 'style="display:none;"';

                                let entityUrl = image.file_name;
                                if (isOnCdn) {
                                    if (image.type == "origin") {
                                        $("#" + type + "_" + SELECTORS.imageDelBtn).prop("disabled", false);
                                    }
                                    entityUrl = `<a href="${image.cdn_url}" target="_blank">${image.file_name}</a>`;
                                } else if (isOnServer) {
                                    entityUrl = `<a href="${image.server_url}" target="_blank">${image.file_name}</a>`;
                                }

                                content += 
                                    `<div class="images-search-item clearfix">
                                        <span class="images-search-name">${entityUrl}</span>
                                        <span class="images-search-status alert-info" ${entityOriginAttr}>${awscdncloud_image_text_origin}</span>
                                        <span class="images-search-status ${cdnStatus}">${awscdncloud_image_text_cdn}</span>
                                        <span class="images-search-status ${serverStatus}">${awscdncloud_image_text_server}</span>
                                        <div class="images-search-desc">${image.location}</div>
                                    </div>`;
                            });

                            // enable buttons option
                            if (content.length > 1) {
                                $("#" + type + "_" + SELECTORS.imageSyncBtn).prop("disabled", false);
                            }

                            $("#" + type + "_" + SELECTORS.imageSearchList).append(content);
                        } else console.error("Error: images array is empty...");
                    } else console.error(response.error);

                    $("#" + type + "_" + SELECTORS.imageDelLoader).hide();
                    imageSearchListControlUI(type, false);
                },
                error() {
                    console.error("Error: some error in ajax...");
                    $("#" + type + "_" + SELECTORS.imageDelLoader).hide();
                    imageSearchListControlUI(type, false);
                }
            });
        } else console.error("Error: id entity is empty...");
    });

    // ----------------- Search Image Sync Button -----------------
    $(".btn.image-sync").on("click", function() {
        const $btn = $(this);
        const type = $btn.data("type");
        const idEntity = Number($("#" + type + "_" + SELECTORS.imageSearchEntity).val());

        if (idEntity > 1) {
            imageSearchListControlUI(type, true);
            $("#" + type + "_" + SELECTORS.imageSyncLoader).show();

            const formData = new FormData();
            formData.append("ajax", 1);
            formData.append("token", awscdncloud_token);
            formData.append("action", "image-sync");
            formData.append("params", JSON.stringify({ name: type, id: idEntity }));

            $.ajax({
                url: "/index.php?fc=module&module=awscdncloud&controller=awsFormTest",
                type: "POST",
                dataType: "json",
                data: formData,
                contentType: false,
                processData: false,
                success(response) {
                    if (response.success) {
                        if (response.images.length) {
                            let content = ``;

                            // add content
                            response.images.forEach(image => {
                                const isOnServer = !!image.on_server;
                                const isOnCdn = !!image.on_cdn;
                                const isOrigin = image.type === "origin";

                                const serverStatus = isOnServer ? "alert-success" : "alert-danger";
                                const cdnStatus = isOnCdn ? "alert-success" : "alert-danger";
                                const entityOriginAttr = isOrigin ? "" : 'style="display:none;"';

                                let entityUrl = image.file_name;
                                if (isOnCdn) {
                                    if (image.type == "origin") {
                                        $("#" + type + "_" + SELECTORS.imageDelBtn).prop("disabled", false);
                                    }
                                    entityUrl = `<a href="${image.cdn_url}" target="_blank">${image.file_name}</a>`;
                                } else if (isOnServer) {
                                    entityUrl = `<a href="${image.server_url}" target="_blank">${image.file_name}</a>`;
                                }

                                content += 
                                    `<div class="images-search-item clearfix">
                                        <span class="images-search-name">${entityUrl}</span>
                                        <span class="images-search-status alert-info" ${entityOriginAttr}>${awscdncloud_image_text_origin}</span>
                                        <span class="images-search-status ${cdnStatus}">${awscdncloud_image_text_cdn}</span>
                                        <span class="images-search-status ${serverStatus}">${awscdncloud_image_text_server}</span>
                                        <div class="images-search-desc">${image.location}</div>
                                    </div>`;
                            });

                            // enable buttons option
                            if (content.length > 1) {
                                $("#" + type + "_" + SELECTORS.imageSyncBtn).prop("disabled", false);
                            }

                            $("#" + type + "_" + SELECTORS.imageSearchList).append(content);
                        } else console.error("Error: images array is empty...");
                    } else console.error(response.error);

                    $("#" + type + "_" + SELECTORS.imageSyncLoader).hide();
                    imageSearchListControlUI(type, false);
                },
                error() {
                    console.error("Error: some error in ajax...");
                    $("#" + type + "_" + SELECTORS.imageSyncLoader).hide();
                    imageSearchListControlUI(type, false);
                }
            });
        } else console.error("Error: id entity is empty...");
    });

    // ----------------- Search Image Sync All Button -----------------
    $(".btn.image-sync-all").on("click", function() {
        const $btn = $(this);
        const type = $btn.data("type");

        imageSearchListControlUI(type, true);
        $("#" + type + "_" + SELECTORS.imageSearchList).append("Внимание! Данная процедуры займет некоторое время.");
        $("#" + type + "_" + SELECTORS.imageSearchList).show();

        const formData = new FormData();
        formData.append("ajax", 1);
        formData.append("token", awscdncloud_token);
        formData.append("action", "image-sync-all");
        formData.append("params", JSON.stringify({ name: type }));

        $.ajax({
            url: "/index.php?fc=module&module=awscdncloud&controller=awsFormTest",
            type: "POST",
            dataType: "json",
            data: formData,
            contentType: false,
            processData: false,
            success(response) {
                if (response.success) {
                    $("#" + type + "_" + SELECTORS.imageSearchList).empty();
                    $("#" + type + "_" + SELECTORS.imageSearchList).append("<p>Все продукты успешно синхронизированы!</p>");
                    $("#" + type + "_" + SELECTORS.imageSearchList).append(`<p>Всего файлов: ${response.success.files} | Файлов загружено: ${response.success.uploaded} | Файлов удалено с сервера: ${response.success.deleted}</p>`);
                } else {
                    $("#" + type + "_" + SELECTORS.imageSearchList).empty();
                    $("#" + type + "_" + SELECTORS.imageSearchList).append(`<p>${response.msg}</p>`);
                    console.error(response.msg);
                }

                $("#" + type + "_" + SELECTORS.imageSyncLoader).hide();
                imageSearchListControlUI(type, false);
            },
            error() {
                console.error("Error: some error in ajax...");
                $("#" + type + "_" + SELECTORS.imageSearchList).empty();
                $("#" + type + "_" + SELECTORS.imageSearchList).append("Ошибка синхронизации продуктов...");
                $("#" + type + "_" + SELECTORS.imageSyncLoader).hide();
                imageSearchListControlUI(type, false);
            }
        });
    });

    getInfoState();
});