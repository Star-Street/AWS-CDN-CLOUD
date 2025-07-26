<h3 class="tab-header">
    <i class="fa fa-cubes"></i>
    <span style="margin-left:5px;">{l s="Products" mod="awscdncloud"}</span>
</h3>
<div class="info-row">
    <div id="product_cache_alert" class="alert alert-info">
        <img id="product_cache_loader" src="../img/loader.gif" class="image-loader"/>
        <span id="product_cache_text">{l s="You can clear the image cache in the \"img/tmp/\" folder" mod="awscdncloud"}</span>
        <button id="product_cache_btn" type="button" data-type="product" class="btn btn-secondary cache-clear pull-right" style="margin:-8px 0 0 0;background:#FFF;">
            <i class="icon-trash"></i> 
            {l s="Clear cache" mod="awscdncloud"}
        </button>
    </div>
</div>
<div class="info-row">
    <div class="col-lg-6">
        <div class="card-header">
            <h4 class="card-title-size">{l s="Send images to CDN cloud" mod="awscdncloud"}</h4>
        </div>
        <div class="card-body">
            <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" id="aws_s3_enabled_product_on" name="aws_s3_enabled_product" class="cdn-enabled" data-type="product" value="1" {if $cdn_product_enabled}checked="checked"{/if}>
                <label for="aws_s3_enabled_product_on">{l s="Enabled" mod="awscdncloud"}</label>
                <input type="radio" id="aws_s3_enabled_product_off" name="aws_s3_enabled_product" class="cdn-enabled" data-type="product" value="0" {if !$cdn_product_enabled}checked="checked"{/if}>
                <label for="aws_s3_enabled_product_off">{l s="Disabled" mod="awscdncloud"}</label>
                <a class="slide-button btn"></a>
            </span>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card-header">
            <h4 class="card-title-size">{l s="Delete all images from server" mod="awscdncloud"}</h4>
        </div>
        <div class="card-body">
            <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" id="aws_s3_delete_product_on" name="aws_s3_delete_product" class="cdn-disable" data-type="product" value="1" {if $cdn_product_delete}checked="checked"{/if}>
                <label for="aws_s3_delete_product_on">{l s="Enabled" mod="awscdncloud"}</label>
                <input type="radio" id="aws_s3_delete_product_off" name="aws_s3_delete_product" class="cdn-disable" data-type="product" value="0" {if !$cdn_product_delete}checked="checked"{/if}>
                <label for="aws_s3_delete_product_off">{l s="Disabled" mod="awscdncloud"}</label>
                <a class="slide-button btn"></a>
            </span>
        </div>
    </div>
</div>
<div class="info-row">
    <div class="col-lg-12">
        <h3 class="in-the-middle">
            <i class="fa fa-image"></i>&nbsp; 
            <span class="card-title">{l s="Images" mod="awscdncloud"}</span>
        </h3>
        <div class="row">
            <div class="col-lg-3">
                <div class="input-group">
                    <input id="product_image_search_input" type="text">
                    <span class="input-group-addon">ID</span>
                </div>
            </div>
            <div class="col-lg-4">
                <button id="product_image_search_btn" type="button" data-type="product" class="btn btn-primary image-search">
                    <img id="product_image_search_loader" src="../img/loader.gif" class="image-loader"/>
                    {l s="Search" mod="awscdncloud"}
                </button>
                <button id="product_image_clear_btn" type="button" data-type="product" class="btn btn-light image-clear">
                    {l s="Clear" mod="awscdncloud"}
                </button>
            </div>
            <div class="col-lg-5">
                <button id="product_image_del_server_btn" type="button" data-type="product" class="btn btn-danger image-del-server pull-right" disabled>
                    <img id="product_image_del_server_loader" src="../img/loader.gif" class="image-loader"/>
                    {l s="Delete image" mod="awscdncloud"}
                </button>
                <button id="product_image_sync_btn" type="button" data-type="product" class="btn btn-success image-sync pull-right" disabled>
                    <img id="product_image_sync_loader" src="../img/loader.gif" class="image-loader"/>
                    {l s="Update CDN" mod="awscdncloud"}
                </button>
                <button id="product_image_sync_all_btn" type="button" data-type="product" class="btn btn-success image-sync-all pull-right">
                    <img id="product_image_sync_all_loader" src="../img/loader.gif" class="image-loader"/>
                    {l s="Update all" mod="awscdncloud"}
                </button>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <img id="product_image_search_list_loader" src="../img/loader.gif" class="images-search-list image-loader"/>
                <div id="product_image_search_list" class="images-search-list" hidden></div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="product_cache_text_tmp" value="{l s="You can clear the image cache in the \"img/tmp/\" folder" mod="awscdncloud"}">
<input type="hidden" id="product_loader_text_tmp" value="{l s="Clearing" mod="awscdncloud"}...">