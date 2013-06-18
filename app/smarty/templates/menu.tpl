<h1><a id="linkAbout" href="{$totop}about.php"><img src="{$totop}img_common/share/logo.jpg" width="206" height="61" alt="CIRCUS" id="about-circus-btn"/></a></h1>
<div id="menu">
	<ul>
		<li><a href="{$totop}home.php" class="topmenu topmenu-home" title="home">home</a></li>
		{if $currentUser->hasPrivilege('listSearch')}
		<li>
			<a href="{$totop}{if $currentUser->today_disp == 'series'}series_list{else}cad_log{/if}.php?mode=today"
			class="topmenu topmenu-today" title="today">
			<div class="month"></div>
			<div class="day"></div>
			today</a>
		</li>
		<li><a href="{$totop}search.php" class="topmenu topmenu-search" title="search">search</a></li>
		{/if}
		<li><a href="{$totop}personal_statistics.php" class="topmenu topmenu-statistics" title="statistics">statistics</a></li>
		{if $currentUser->hasPrivilege('researchShow')}
		<li><a href="{$totop}research/research_list.php" class="topmenu topmenu-research" title="research">research</a></li>
		{/if}
	</ul>
	<p class="user">User: {$currentUser->user_id|escape}</p>
	<ul>
		<li><a href="{$totop}user_preference.php" class="topmenu topmenu-preference" title="preference">preference</a></li>
		{if $currentUser->hasPrivilege('serverOperation')}
		<li><a href="{$totop}administration/administration.php" class="topmenu topmenu-administration" title="administration">administration</a></li>
		{/if}
		<li><a href="{$totop}index.php?mode=logout" class="topmenu topmenu-logout" title="logout">logout</a></li>
	</ul>
</div>
<!-- / #menu END -->
