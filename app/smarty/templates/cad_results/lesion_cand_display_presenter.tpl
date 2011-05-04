{*
CIRCUS CS Templete for block content.
This renders the main result of the CAD.

Individual plugins can override this template.
*}
  <b>Image No.:</b> {$display.location_z|sprintf:"%d"}<br>
  <b>Slice Location:</b> {$display.slice_location|sprintf:"%.2f"} [mm]<br>
  <b>Volume:</b> {$display.volume_size|sprintf:"%.2f"} [mm<sup>3</sup>]<br>
  <b>Confidence:</b> {$display.confidence|sprintf:"%.3f"}
  <div class="image-area">
  {capture name="resultImage"}{$cadResult->webPathOfCADResult()}/{$display.display_id|string_format:"result%03d.png"}{/capture}
  {include file="cad_results/marked_image.tpl
    src=$smarty.capture.resultImage x=$display.location_x y=$display.location_y
    marker=$display.marker
    width=$displayPresenter->imageWidth height=$displayPresenter->imageHeight
    cropX=$attr.crop_org_x cropY=$attr.crop_org_y
    cropWidth=$attr.crop_width cropHeight=$attr.crop_height
    dispWidth=$displayPresenterParams.dispWidth}
  </div><!-- /image-area -->
