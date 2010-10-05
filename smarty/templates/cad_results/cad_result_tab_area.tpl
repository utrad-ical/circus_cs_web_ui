<div id="cadResultTab" class="tabArea">
	<ul>
		{if $param.srcList!="" && $smarty.session.listAddress!=""}
			<li><a href="../{$smarty.session.listAddress}" class="btn-tab" title="{$param.listTabTitle}">{$param.listTabTitle}</a></li>
		{else}
			<li><a href="../series_list.php?mode=study&studyInstanceUID={$param.studyInstanceUID}" class="btn-tab" title="Series list">Series list</a></li>
		{/if}
		<li><a href="#" class="btn-tab" title="list" style="background-image: url(../img_common/btn/{$smarty.session.colorSet}/tab0.gif); color:#fff">CAD result</a></li>
	</ul>
	<p class="add-favorite"><a href="#" title="favorite"><img src="../img_common/btn/favorite.jpg" width="100" height="22" alt="favorite"></a></p>
</div><!-- / .tabArea END -->

<div id="cadDetailTab" class="tabArea" style="display:none;">
	<ul>
		{if $param.srcList!="" && $smarty.session.listAddress!=""}
			<li><a href="../{$smarty.session.listAddress}" class="btn-tab" title="{$param.listTabTitle}">{$param.listTabTitle}</a></li>
		{else}
			<li><a href="../series_list.php?mode=study&studyInstanceUID={$param.studyInstanceUID}" class="btn-tab" title="Series list">Series list</a></li>
		{/if}
		<li><a href="#" onclick="ShowCADResult();" class="btn-tab" title="CAD result">CAD result</a></li>
		<li><a href="#" class="btn-tab" title="list" style="background-image: url(../img_common/btn/{$smarty.session.colorSet}/tab0.gif); color:#fff">CAD detail</a></li>
	</ul>
	<p class="add-favorite"><a href="#" title="favorite"><img src="../img_common/btn/favorite.jpg" width="100" height="22" alt="favorite"></a></p>
</div><!-- / .tabArea END -->
