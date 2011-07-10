{*
CIRCUS CS Templete for block content.
This renders the main result of the CAD.

Individual plugins can override this template.
*}
  <b>Image No.:</b> {$display.location_z|sprintf:"%d"}<br>
  <b>Slice Location:</b> {$display.slice_location|sprintf:"%.2f"} [mm]<br>
  <b>Volume:</b> {$display.volume_size|sprintf:"%.2f"} [mm<sup>3</sup>]<br>
  <b>Confidence:</b> {$display.confidence|sprintf:"%.3f"}
  <div class="viewer"></div>
