{* {debug} *}
<div class="bootstrap af clearfix">
	<div class="col-lg-2 col-md-3">
		<div class="nav flex-column nav-pills" role="tablist">
            <a href="#cdn-info" id="cdn-info-tab" class="nav-link list-group-item active" data-toggle="pill" role="tab" aria-controls="cdn-info" aria-selected="true">
                <i class="fa fa-bars"></i>
                <span style="margin-left:5px;">{l s="Information" mod="awscdncloud"}</span>
            </a>
            <a href="#cdn-config" id="cdn-config-tab" class="nav-link list-group-item" data-toggle="pill" role="tab" aria-controls="cdn-config" aria-selected="false">
                <i class="fa fa-wrench"></i>
                <span style="margin-left:5px;">{l s="Configuration" mod="awscdncloud"}</span>
            </a>
            <a href="#cdn-product" id="cdn-product-tab" class="nav-link list-group-item" data-toggle="pill" role="tab" aria-controls="cdn-product" aria-selected="false">
                <i class="fa fa-cubes"></i>
                <span style="margin-left:5px;">{l s="Products" mod="awscdncloud"}</span>
            </a>
            <a href="#cdn-manuf" id="cdn-manuf-tab" class="nav-link list-group-item" data-toggle="pill" role="tab" aria-controls="cdn-manuf" aria-selected="false">
                <i class="fa fa-group"></i>
                <span style="margin-left:5px;">{l s="Manufacturers" mod="awscdncloud"}</span>
            </a>
            <a href="#cdn-category" id="cdn-category-tab" class="nav-link list-group-item" data-toggle="pill" role="tab" aria-controls="cdn-category" aria-selected="false">
                <i class="fa fa-tag"></i>
                <span style="margin-left:5px;">{l s="Categories" mod="awscdncloud"}</span>
            </a>
		</div>
        <div class="list-group" style="margin-top:10px;">
            <a class="list-group-item" style="text-align:center;">
                <i class="icon-info"></i><span style="margin-left:5px;">CDN {$cdn_version|escape:"htmlall":"UTF-8"}</span><br>
                <i class="icon-info"></i><span style="margin-left:5px;">PrestaShop {$ps_version|escape:"htmlall":"UTF-8"}</span>
            </a>
        </div>
	</div>

    <div class="panel col-lg-10 col-md-9">
        <div class="tab-content">
            <div class="tab-pane fade in active" id="cdn-info" role="tabpanel" aria-labelledby="cdn-info-tab">
                {include file="./tabs/dataCdnInfo.tpl"}
            </div>
            <div class="tab-pane fade" id="cdn-config" role="tabpanel" aria-labelledby="cdn-config-tab">
                {include file="./tabs/dataCdnConfig.tpl"}
            </div>
            <div class="tab-pane fade" id="cdn-product" role="tabpanel" aria-labelledby="cdn-product-tab">
                {include file="./tabs/dataCdnProduct.tpl"}
            </div>
            <div class="tab-pane fade" id="cdn-manuf" role="tabpanel" aria-labelledby="cdn-manuf-tab">
                {include file="./tabs/dataCdnManuf.tpl"}
            </div>
            <div class="tab-pane fade" id="cdn-category" role="tabpanel" aria-labelledby="cdn-category-tab">
                {include file="./tabs/dataCdnCategory.tpl"}
            </div>
        </div>
    </div>
</div>