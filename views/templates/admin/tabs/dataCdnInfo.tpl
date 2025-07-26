<h3 class="tab-header">
    <i class="fa fa-bars"></i>
    <span style="margin-left:5px;">CDN {l s="Information" mod="awscdncloud"}</span>
</h3>
<div class="info-row">
    <p>{l s="This module allows you to work with the object storage of the CDN: sends images, replaces links, etc." mod="awscdncloud"}</p>
</div>
<div class="info-row">
    <div class="col-lg-4">
        <div class="card-header">
            <h4 class="card-title-size">
                <i class="fa fa-object-group"></i>&nbsp;
                <span class="card-title">{l s="Object storage" mod="awscdncloud"}</span>
            </h4>
        </div>
        <div class="card-body">
            <p class="card-text">
                {l s="Status" mod="awscdncloud"}: 
                <img id="cdn-object-status-loader" src="../img/loader.gif" style="height:15px;" hidden/>
                <b id="cdn-object-status"></b>
            </p>
            <p class="card-text">
                {l s="Bucket" mod="awscdncloud"}: 
                <img id="cdn-object-backet-loader" src="../img/loader.gif" style="height:15px;" hidden/>
                <span id="cdn-object-backet"></span>
            </p>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card-header">
            <h4 class="card-title-size">
                <i class="fa fa-cloud"></i>&nbsp;
                <span class="card-title">CDN {l s="Cloud" mod="awscdncloud"}</span>
            </h4>
        </div>
        <div class="card-body">
            <p class="card-text">
                {l s="Status" mod="awscdncloud"}: 
                <img id="cdn-info-status-loader" src="../img/loader.gif" style="height:15px;" hidden/>
                <b id="cdn-info-status"></b>
            </p>
            <p class="card-text">
                {l s="Media Server" mod="awscdncloud"}: 
                <img id="cdn-info-url-loader" src="../img/loader.gif" style="height:15px;" hidden/>
                <span><a id="cdn-info-url" href="" target="_blank"></a></span>
                <span>({l s="use this link" mod="awscdncloud"} <a id="cdn-info-url" href="{$media_server_link|escape:"htmlall":"UTF-8"}" target="_blank">Admin Media Server</a>)</span>
            </p>
        </div>
    </div>
</div>
<div class="info-row">
    <div class="col-lg-4">
        <div class="card-header">
            <h4 class="card-title-size">
                <i class="fa fa-cubes"></i>&nbsp;
                <span class="card-title">{l s="Products" mod="awscdncloud"}</span>
            </h4>
        </div>
        <div class="card-body">
            <p class="card-text">
                {l s="Send image in CDN cloud" mod="awscdncloud"}: 
                <img id="cdn-product-status-loader" src="../img/loader.gif" style="height:15px;" hidden/>
                <b id="cdn-product-status"></b>
            </p>
            <p class="card-text">
                {l s="Delete original images from server" mod="awscdncloud"}: 
                <img id="cdn-product-delete-loader" src="../img/loader.gif" style="height:15px;" hidden/>
                <b id="cdn-product-delete"></b>
            </p>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card-header">
            <h4 class="card-title-size">
                <i class="fa fa-group"></i>&nbsp;
                <span class="card-title">{l s="Manufacturers" mod="awscdncloud"}</span>
            </h4>
        </div>
        <div class="card-body">
            <p class="card-text">
                {l s="Send image in CDN cloud" mod="awscdncloud"}: 
                <img id="cdn-manuf-status-loader" src="../img/loader.gif" style="height:15px;" hidden/>
                <b id="cdn-manuf-status"></b>
            </p>
            <p class="card-text">
                {l s="Delete original images from server" mod="awscdncloud"}: 
                <img id="cdn-manuf-delete-loader" src="../img/loader.gif" style="height:15px;" hidden/>
                <b id="cdn-manuf-delete"></b>
            </p>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card-header">
            <h4 class="card-title-size">
                <i class="fa fa-tag"></i>&nbsp;
                <span class="card-title">{l s="Categories" mod="awscdncloud"}</span>
            </h4>
        </div>
        <div class="card-body">
            <p class="card-text">
                {l s="Send image in CDN cloud" mod="awscdncloud"}: 
                <img id="cdn-category-status-loader" src="../img/loader.gif" style="height:15px;" hidden/>
                <b id="cdn-category-status"></b>
            </p>
            <p class="card-text">
                {l s="Delete original images from server" mod="awscdncloud"}: 
                <img id="cdn-category-delete-loader" src="../img/loader.gif" style="height:15px;" hidden/>
                <b id="cdn-category-delete"></b>
            </p>
        </div>
    </div>
</div>