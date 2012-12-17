{if $cad_downloader_title}<h2>{$cad_downloader_title|escape}</h2>{/if}
<div id="cad-downloader"></div>
<script type="text/javascript">
var ext = circus.cadresult.presentation.extensions.CadDownloaderExtension;
$('#cad-downloader').cadDirInspector(ext['filesMatch'], ext['substitutes']);
</script>