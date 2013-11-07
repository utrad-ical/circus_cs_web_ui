{capture name="extra"}
{literal}
<style type="text/css">

#stats_menu {
	margin-top: 2em;
}
#stats_menu li {
	height: 36px;
	width: 240px;
	background-image: url('../images/statistics/icons.png');
	background-repeat: no-repeat;
	margin-bottom: 5px;
	border: 1px solid #777;
}
#stats_menu.hi li {
	background-image: url('../images/statistics/icons-hi.png');
	background-size: 36px 144px;
}
#stats_menu li a {
	line-height: 36px;
	display: block;
	text-decoration: none;
	text-indent: 60px;
	text-align: center;
	font-weight: bold;
	color: white;
	text-shadow: 2px 2px 3px #666;
}
#stats_menu li:hover a {
	text-decoration: underline;
}
#stats_menu li.disabled { background-color: #ddd; border-color: #bbb; }
#stats_menu li.plot { background-position: 12px 0; }
#stats_menu li.time_for_feedback { background-position: 12px -36px; }
#stats_menu li.export { background-position: 12px -72px; }
#stats_menu li.froc { background-position: 12px -108px; }
</style>
{/literal}
{/capture}
{include file="header.tpl" head_extra=$smarty.capture.extra body_class="spot"}
<h2>Analysis Menu</h2>

<p class="themeColor"><strong>Note:</strong> These analysis functions may
heavily consume server processing time and memory.<br/>
Plsease be patient and avoid running those functions in working hours.</p>

<ul id="stats_menu">
<li class="plot themeBackground"><a href="personal_statistics.php">Lesion Locations</a></li>
<li class="time_for_feedback themeBackground"><a href="time_for_feedback_entry.php">Feedback Time</a></li>
<li class="froc themeBackground"><a href="../research/research_list.php">FROC Analysys</a></li>
<li class="export disabled"><a>Export Feedback</a></li>
</ul>

{include file="footer.tpl"}