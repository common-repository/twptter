function checkCheckbox(checkBox,checkBoxDependent)
{
if (document.getElementById(checkBox).checked==true) 
    {
     document.getElementById(checkBoxDependent).disabled=false;
     document.getElementById(checkBoxDependent).style.backgroundColor="white";
    }
else 
    {
     document.getElementById(checkBoxDependent).disabled=true;
     document.getElementById(checkBoxDependent).style.backgroundColor="#ccccff";
    }
}

function limitLength(field,maxlimit,counter,bcheckCharacters) 
{
  fieldValue=document.getElementById(field).value;
  if (fieldValue.length > maxlimit){document.getElementById(field).value = fieldValue.substring(0, maxlimit);}
  else {document.getElementById(counter).innerHTML=maxlimit-fieldValue.length;}
  if (bcheckCharacters){document.getElementById(field+"ERR").value=checkCharacters(fieldValue);}
}

function isBlank(val)
{
  if(val==null){return true;}
  if(val.length==0){return true;}
  return false;     
}
function isNone(val)
{
  if(val=="none"){return true;}
  return false;     
}
function checkCharacters(field)
{
  var errorText="";
  var illegalChars= /[\(\)\<\>\;\:\\\/\'\"\[\]]/
  if (field.match(illegalChars)){errorText="Contains illegal characters.";}
  return errorText;
}
function isEmailAddress(field)
{
  var emailFilter=/^.+@.+\..{2,3}$/;
  if ((emailFilter.test(field))) {return true;}
  return false;
}
function isValidUrl(url)
{
	var urlFilter=(((ht|f)tp(s?):\/\/)|(www\.[^ \[\]\(\)\n\r\t]+)|(([012]?[0-9]{1,2}\.){3}[012]?[0-9]{1,2})\/)([^ \[\]\(\),;&quot;'&lt;&gt;\n\r\t]+)([^\. \[\]\(\),;&quot;'&lt;&gt;\n\r\t])|(([012]?[0-9]{1,2}\.){3}[012]?[0-9]{1,2});
  if ((urlFilter.test(url))) {return true;}
  return false;
}

function formSubmit()  
{
  var myControls = new Array()
  myControls[0] = "twptext"
  myControls[1] = "twpanchor"
  myControls[2] = "twpurl"	

  var myControlsDesc = new Array()
  myControlsDesc[0] = "the text of your twp"
  myControlsDesc[1] = "the anchor text for any link from your twp"
  myControlsDesc[2] = "the url that your anchor will link to"

  var myControlsType = new Array()
  myControlsType[0] = "textarea"
  myControlsType[1] = "input-EMAIL"
  myControlsType[2] = "input-URL"
 
  var errorText="Please enter ";
  var foundError=false;     

  for (x in myControls)
  {  
		//check for a value, then check the value is not 'none', then ensure there is no existing error
    var myControlValue=document.getElementById(myControls[x]).value;
    if 
    (
      isBlank(myControlValue) 
      || 
      isNone(myControlValue)
      || 
      !(isBlank(document.getElementById(myControls[x]+"ERR").value))
      ||
      (myControlsType[x]=="input-EMAIL" && !(isEmailAddress(myControlValue)))
      ||
      (myControlsType[x]=="input-URL" && !(isValidUrl(myControlValue)))			                         
    ) 
    {
       if (foundError){errorText=errorText+' & '};
       errorText=errorText+myControlsDesc[x];
       document.getElementById(myControls[x]).style.backgroundColor="yellow";
       foundError=true;
    }
    else
    {
		   document.getElementById(myControls[x]).style.backgroundColor="white"; //may have been set to orange on previous validation             
    }
  }
  if (foundError)
  {
	 	document.getElementById("twpCommentary").style.color="red";
    document.getElementById("twpCommentary").innerHTML=errorText;
    return false;
  }
  alert('twp added');
  return true;
}
