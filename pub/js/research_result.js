/**
 * research_result.js
 * Used in the research_result.php(.tpl) page.
 */

var circus = circus || {};

$(function(){

	// tags
	var refresh = function(tags) {
		$('#research-tags').refreshTags(tags, 'research_list.php', 'filterTag');
	};
	$('#edit-research-tags').click(function() {
		circus.edittag.openEditor(6, circus.jobID, '../', refresh);
	})
	circus.edittag.load(6, circus.jobID, '../', refresh);
});