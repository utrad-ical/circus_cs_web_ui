{*
CIRCUS CS Templete for block content.
This renders the main result of the CAD.

Individual plugins can override this template.
*}
  <b>Image No.:</b> {$display.z|sprintf:"%d"}<br>
  <b>Slice Location:</b> {$display.slice_location|sprintf:"%.2f"} [mm]<br>
  <b>Volue:</b> {$display.volume|sprintf:"%.2f"} [mm<sup>3</sup>]<br>
  <b>Confidence:</b> {$display.confidence|sprintf:"%.3f"}
  <div class="image-area">
  {include file="cad_results/marked_image.tpl
    src=$display.src x=$display.x y=$display.y marker=$display.marker
    width=$attr.width height=$attr.height
    cropX=$attr.cropX cropY=$attr.cropY
    cropWidth=$attr.cropWidth cropHeight=$attr.cropHeight
    dispWidth=$attr.dispWidth}
  </div><!-- /image-area -->
