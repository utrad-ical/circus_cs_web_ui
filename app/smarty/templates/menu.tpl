<h1><a id="linkAbout" href="{$params.toTopDir}about.php"><img src="{$params.toTopDir}img_common/share/logo.jpg" width="206" height="61" alt="CIRCUS" id="about-circus-btn"/></a></h1>
<div id="menu">
	<ul>
		<li><a href="{$params.toTopDir}home.php" class="jq-btn jq-btn-home" title="home">home</a></li>
		{if $currentUser->hasPrivilege('listSearch')}
		<li>
			<a href="{$params.toTopDir}{if $currentUser->today_disp == 'series'}series_list{else}cad_log{/if}.php?mode=today"
			class="jq-btn jq-btn-today" title="today">
			<div class="month"></div>
			<div class="day"></div>
			today</a>
		</li>
		<li><a href="{$params.toTopDir}search.php" class="jq-btn jq-btn-search" title="search">search</a></li>
		{/if}
		<li><a id="linkStatistics" href="{$params.toTopDir}personal_statistics.php" class="jq-btn jq-btn-statistics" title="statistics">statistics</a></li>
		{if $currentUser->hasPrivilege('researchShow')}
		<li><a href="{$params.toTopDir}research/research_list.php" class="jq-btn jq-btn-research" title="research">research</a></li>
		{/if}
	</ul>
	<p class="user">User: {$smarty.session.userID|escape}</p>
	<ul>
		<li><a href="{$params.toTopDir}user_preference.php" class="jq-btn jq-btn-preference" title="preference">preference</a></li>
		{if $currentUser->hasPrivilege('serverOperation')}
		<li><a href="{$params.toTopDir}administration/administration.php" class="jq-btn jq-btn-administration" title="administration">administration</a></li>
		{/if}
		<li><a href="{$params.toTopDir}index.php?mode=logout" class="jq-btn jq-btn-logout" title="logout">logout</a></li>
	</ul>
</div>
<!-- / #menu END -->
