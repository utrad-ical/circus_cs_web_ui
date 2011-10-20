{if $displayPresenterParams.showID}
<div class="result-image-id"><span>ID: </span>{$display.display_id|escape}</div>
{/if}
<img class="result-image" src="{$cadResult->webPathOfCadResult()}/{$display.display_id|string_format:$displayPresenterParams.file|escape}"
{if $displayPresenterParams.width} width="{$displayPresenterParams.width}"{/if}
{if $displayPresenterParams.height} height="{$displayPresenterParams.height}"{/if} alt=""/>