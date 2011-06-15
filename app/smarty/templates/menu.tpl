<h1><a id="linkAbout" href="{$params.toTopDir}about.php"><img src="{$params.toTopDir}img_common/share/logo.jpg" width="208" height="63" alt="CIRCUS" /></a></h1>
{if $currentUser->hasPrivilege('menuShow')}
<script language="javascript" type="text/javascript" src="{$params.toTopDir}js/swfobject.js"></script>
<script type="text/javascript">
	swfobject.registerObject("today_{$smarty.session.colorSet}", "8.0.0");
</script>
<div id="menu">
	<ul>
		<li><a href="{$params.toTopDir}home.php" class="jq-btn jq-btn-home" title="home"></a></li>
		<li>
			<a id="linkTodayDisp" href="{$params.toTopDir}{if $smarty.session.todayDisp=='series'}series_list{else}cad_log{/if}.php?mode=today" class="jq-btn jq-btn-today" title="today">
				<div class="calendar">
					<object id="today" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="32" height="32" id="" align="middle">
						<param name="movie" value="{$params.toTopDir}img_common/btn/{$smarty.session.colorSet}/today.swf" />
						<param name="quality" value="high" />
						<param name="wmode" value="transparent" />
						<!--[if !IE]>-->
						<object type="application/x-shockwave-flash" data="{$params.toTopDir}img_common/btn/{$smarty.session.colorSet}/today.swf" quality="high" wmode="transparent" width="32" height="32" align="middle">
						<!--<![endif]-->
						<div>
							<img src="{$params.toTopDir}img_common/btn/{$smarty.session.colorSet}/today.jpg" width="32" height="32" />
						</div>
						<!--[if !IE]>-->
						</object>
						<!--<![endif]-->
					</object>
				</div>
			</a>
		</li>
		<li><a href="{$params.toTopDir}search.php" class="jq-btn jq-btn-search" title="search"></a></li>
		<li><a id="linkStatistics" href="{$params.toTopDir}personal_statistics.php" class="jq-btn jq-btn-statistics" title="statistics"></a></li>
		{if $currentUser->hasPrivilege('researchShow')}
		<li><a href="{$params.toTopDir}research/research_list.php" class="jq-btn jq-btn-research" title="research"></a></li>
		{/if}
	</ul>
	<p class="user">User: {$smarty.session.userID}</p>
	<ul>
		<li><a href="{$params.toTopDir}user_preference.php" class="jq-btn jq-btn-preference" title="preference"></a></li>
		{if $currentUser->hasPrivilege('serverOperation')}
		<li><a href="{$params.toTopDir}administration/administration.php" class="jq-btn jq-btn-administration" title="administration"></a></li>
		{/if}
		<li><a href="{$params.toTopDir}index.php?mode=logout" class="jq-btn jq-btn-logout" title="logout"></a></li>
	</ul>
</div>
<!-- / #menu END -->
{/if}
