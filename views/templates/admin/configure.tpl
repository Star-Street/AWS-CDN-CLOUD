<div class="panel">
    <h3><i class="icon-cloud"></i>&nbsp;&nbsp;AWS S3 Status</h3>
    <p>Bucket: <strong>{$aws_info_name|escape:'html'}</strong></p>
    <p>Status: {if ($aws_info_state)}<strong class="text-success">online</strong>{else}<strong class="text-danger">offline</strong>{/if}</p>
    <p>CDN cloud: {if ($aws_info_toggle)}<strong class="text-success">enabled</strong>{else}<strong class="text-danger">disabled</strong>{/if}</p>
    <p>Version: {$aws_info_ver}</p>
</div>

<div class="panel">
    <h3><i class="icon-wrench"></i>&nbsp;&nbsp;AWS S3 Params</h3>

    <ul class="nav nav-tabs">
        <li class="active"><a href="#settings_form" data-toggle="tab">Config</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="settings_form">{$aws_params_settings_form}</div>
    </div>
</div>

<input type="hidden" id="aws_form_token" value="{$aws_form_token}">