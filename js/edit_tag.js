//--------------------------------------------------------------------------------------------------
// Show sub window for editing tag (common function)
//--------------------------------------------------------------------------------------------------
function EditTag(category, sid, toTopDir)
{
	var title ="";

	switch(category)
	{
		case 1: title = "Edit patient tags";  break;
		case 2: title = "Edit study tags";  break;
		case 3: title = "Edit series tags";  break;
		case 4: title = "Edit CAD tags";  break;
		case 5: title = "Edit tags for lesion candidate";  break;
		case 6: title = "Edit tags for research";  break;
	}

	var dstAddress = toTopDir + "edit_tags.php?category=" + category + "&referenceID=" + sid;
	window.open(dstAddress, title, "width=400,height=250,location=no,resizable=no,scrollbars=1");
}
//--------------------------------------------------------------------------------------------------

