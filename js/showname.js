
function showByName(name)
{
    var cells=window.document.getElementsByName(name);
		var numElements=cells.length;
		for (var i=0; i<numElements; ++i) {
			var cell=cells.item(i);
			cell.style.display='block';
		}
}

function hideByName(name)
{
    var cells=window.document.getElementsByName(name);
		var numElements=cells.length;
		for (var i=0; i<numElements; ++i) {
			var cell=cells.item(i);
			cell.style.display='none';
		}
}

