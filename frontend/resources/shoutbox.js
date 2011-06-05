function TrackCount(fieldObj,countFieldName,maxChars)
{
	var countField = eval("fieldObj.form."+countFieldName);
    	var diff = maxChars - fieldObj.value.length;

      	// Need to check & enforce limit here also in case user pastes data
        if (diff < 0)
	{
		fieldObj.value = fieldObj.value.substring(0,maxChars);
	        diff = maxChars - fieldObj.value.length;
	}
	countField.value = diff;
}

function LimitText(fieldObj,maxChars)
{
	var result = true;
	if (fieldObj.value.length >= maxChars)
	result = false;
			        
	if (window.event)
		window.event.returnValue = result;
	return result;
}