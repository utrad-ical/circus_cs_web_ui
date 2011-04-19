{*
CIRCUS CS Templete for block content.
This renders the main result of the CAD.

Individual plugins can override this template.
*}
  <b>Image No.:</b> {$block.z|sprintf:"%d"}<br>
  <b>Slice Location:</b> {$block.slice_location|sprintf:"%.2f"} [mm]<br>
  <b>Volue:</b> {$block.volume|sprintf:"%.2f"} [mm<sup>3</sup>]<br>
  <b>Confidence:</b> {$block.confidence|sprintf:"%.3f"}
  <div class="image-area">
  {include file="cad_results/marked_image.tpl
    src=$block.src x=$block.x y=$block.y marker=$block.marker
    width=$attr.width height=$attr.height
    cropX=$attr.cropX cropY=$attr.cropY
    cropWidth=$attr.cropWidth cropHeight=$attr.cropHeight
    dispWidth=$attr.dispWidth}
  </div><!-- /image-area -->
