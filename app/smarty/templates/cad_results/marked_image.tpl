{*
CIRCUS CS Template.
Displays cropped image file with a circle marker.
This file is called by lesion_block.tpl template, but can be used independently.

Required CSS file:
	layout.css
Parameters:
	src:
		The image file to display. Paths should be processed beforehand.
	width:
		The original width of the image. (Required)
	height:
		The original height of the image. (Required)
	marker:
		The type of the marker.
		Valid values are 'none', 'magenta', 'yellow', and 'double'.
		(Default: 'magenta')
	x:
		The x-coordinate of the marker. (Default: 0)
	y:
		The y-coordinate of the marker. (Default: 0)
	cropX:
	cropY:
	cropWidth:
	cropHeight:
		These four parameters define the viewport (visible range) of the image.
		The region outside of this range will be cropped.
		(Default: (0, 0, width, height), to show the entire image)
	dispWidth:
		The image on the monitor will be streatched/shrinked
		to fit this width. (Default: cropWidth, to maintain the scale)
		The display height will be calculated to maintain the aspect ratio.
*}
{assign var=marker value=$marker|default:magenta}
{if $marker eq "double"}{assign var=markerRadius value=15}{/if}
{assign var=cropWidth  value=$cropWidth|default:$width}
{assign var=cropHeight value=$cropHeight|default:$height}
{assign var=dispWidth  value=$dispWidth|default:$cropWidth}
{assign var=scale value=`$dispWidth/$cropWidth`}
{assign var=dispHeight value=`$cropHeight*$scale`}
{assign var=markerRadius value=12}
<div class="cropped-image" style="width: {$dispWidth}px; height: {$dispHeight}px">
	<img class="lesion-image" src="{$src}"
		style="width: {$width*$scale}px; height: {$height*$scale}px;
		left: -{$cropX*$scale}px; top: -{$cropY*$scale}px" />
{if $marker ne 'none'}
	<img class="lesion-marker" src="{$params.toTopDir}cad_results/images/{$marker}_circle.png"
		style="left: {$scale*$x-$scale*$cropX-$markerRadius}px; top: {$scale*$y-$scale*$cropY-$markerRadius}px" />
{/if}
</div><!-- /cropped-image -->

{* Add EOL at the end *}