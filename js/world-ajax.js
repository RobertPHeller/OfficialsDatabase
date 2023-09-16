if (!Array.prototype.indexOf)
  {

       Array.prototype.indexOf = function(searchElement /*, fromIndex */)

    {


    "use strict";

    if (this === void 0 || this === null)
      throw new TypeError();

    var t = Object(this);
    var len = t.length >>> 0;
    if (len === 0)
      return -1;

    var n = 0;
    if (arguments.length > 0)
    {
      n = Number(arguments[1]);
      if (n !== n)
	n = 0;
      else if (n !== 0 && n !== (1 / 0) && n !== -(1 / 0))
	n = (n > 0 || -1) * Math.floor(Math.abs(n));
    }

    if (n >= len)
      return -1;

    var k = n >= 0
	  ? n
	  : Math.max(len - Math.abs(n), 0);

    for (; k < len; k++)
    {
      if (k in t && t[k] === searchElement)
	return k;
    }
    return -1;
  };

}

function log (message) {
  try {
    console.log(message);
  } catch(err) { 
    /*alert(message);*/
  }
}

function Editor_Ajax(fun,formdivid,url)
{
  var data = 'function='+fun;
  var formdiv = document.getElementById(formdivid);
  var inputs  = formdiv.getElementsByTagName('input');
  var textareas = formdiv.getElementsByTagName('textarea');
  for (var i=0; i<inputs.length; i++) {
    data += '&' + encodeURI(inputs[i].name) + '=' + encodeURI(inputs[i].value);
  }
  for (i=0; i<textareas.length; i++) {
    data += '&' + encodeURI(textareas[i].name) + '=' + encodeURI(textareas[i].value);
  }
  jQuery.post(url,data,DoEditorUpdates,'xml');
}

function CopyAndAppendNode(parent,newchild) {
  //log('*** CopyAndAppendNode: parent = '+parent+', newchild = '+newchild);
  //log('*** CopyAndAppendNode: newchild.nodeType = '+newchild.nodeType);
  if (newchild.nodeType == 1) {	/* HTML Tag ? */
    //log('*** CopyAndAppendNode: newchild.tagName = '+newchild.tagName);
    var newnode = document.createElement(newchild.tagName);
    //log('*** CopyAndAppendNode: newchild.attributes.length = '+newchild.attributes.length);
    for (var k = 0; k < newchild.attributes.length; k++) {
      var name = newchild.attributes[k].nodeName;
      var value = newchild.attributes[k].nodeValue;
      //log('*** CopyAndAppendNode: name = '+name+', value = '+value);
      var newattr = document.createAttribute(name);
      newattr.nodeValue = value;
      newnode.setAttributeNode(newattr);
    }
    parent.appendChild(newnode);
    //log('*** CopyAndAppendNode: newchild.childNodes.length = '+newchild.childNodes.length);
    for (k = 0; k < newchild.childNodes.length; k++) {
      //log('*** CopyAndAppendNode: k = '+k);
      CopyAndAppendNode(newnode,newchild.childNodes[k]);
      //log('*** CopyAndAppendNode: newnode.innerHTML after recursive call: '+newnode.innerHTML);
      //log('*** CopyAndAppendNode: k (after recursive call) = '+k);
    }
  } else if (newchild.nodeType == 3) { /* Text node */
    parent.innerHTML += newchild.wholeText;
  }
}

function flatHTMLfromXML(xmlnode) {

  var selfClosingTags = ["br", "hr", "img", "input"];
  
  var result = '';
  var k;

  if (xmlnode.nodeType == 1) { /* HTML Tag ? */
    result += '<'+xmlnode.tagName;
    for (k = 0; k < xmlnode.attributes.length; k++) {
      result += ' '+xmlnode.attributes[k].nodeName+'="'+
		    xmlnode.attributes[k].nodeValue+'"';
    }
    if (xmlnode.childNodes.length == 0 && 
	selfClosingTags.indexOf(xmlnode.tagName.toLowerCase()) != -1) {
      result += ' />';
    } else {
      result += '>';
      for (k = 0; k < xmlnode.childNodes.length; k++) {
	result += flatHTMLfromXML(xmlnode.childNodes[k]);
      }
      result += '</'+xmlnode.tagName+'>';
    }
  } else {
    result = xmlnode.wholeText;
  }
  return result;
}

function DoEditorUpdates (data) {
  debug = data;
  var resultmessHTML = data.getElementsByTagName('messages')[0].childNodes;
  if (resultmessHTML.length > 0) {
    var messagesDiv = document.getElementById('ajax-messages');
    messagesDiv.innerHTML = '';
    for (var j = 0; j < resultmessHTML.length; j++) {
      messagesDiv.innerHTML += flatHTMLfromXML(resultmessHTML[j]);
    }
  }
  var updates = data.getElementsByTagName('update');
  for (var i=0; i<updates.length; i++) {
    //log('*** DoEditorUpdates: in updates loop, i = '+i);
    var theid = updates[i].getElementsByTagName('id')[0].childNodes[0].nodeValue;
    //log('*** DoEditorUpdates: theid.length = '+theid.length);
    if (theid.length == 0) continue;
    var thecontent = updates[i].getElementsByTagName('content')[0].childNodes;
    //log('*** DoEditorUpdates: thecontent.length = '+thecontent.length);
    var thedocument = document;
    var thediv = thedocument.getElementById(theid);
    if (thediv == null) {
        thedocument = parent.document
	thediv = thedocument.getElementById(theid);
	if (thediv == null) {
	  thedocument = top.document;
	  thediv = thedocument.getElementById(theid);
	}
    }	  
    if (thediv != null) {
      thediv.innerHTML = '';
      for (var j = 0; j < thecontent.length; j++) {
        thediv.innerHTML += flatHTMLfromXML(thecontent[j]);
	//log('*** DoEditorUpdates (after append): thecontent.length = '+thecontent.length);
      }
      scripts = thedocument.getElementsByTagName('script');
      for (var j=0; j < scripts.length; j++) {
	eval(scripts[i].text);
      }
    }
  }
  var deletes = data.getElementsByTagName('delete');
  for (i=0; i<deletes.length; i++) {
    //log('*** DoEditorUpdates: in deletes loop, i = '+i);
    theid = deletes[i].childNodes[0].nodeValue;
    if (theid == '') continue;
    thedocument = document;
    var element = thedocument.getElementById(theid);
    if (element == null) {
        thedocument = parent.document;
	element = thedocument.getElementById(theid);
	if (element == null) {
	    thedocument = top.document;
	    element = thedocument.getElementById(theid);
	}
    }
    if (element != null) {
      element.parentNode.removeChild(element);
    }
  }
  var adds = data.getElementsByTagName('add');
  for (i=0; i<adds.length; i++) {
    //log('*** DoEditorUpdates: in adds loop, i = '+i);
    var theparentid = adds[i].getElementsByTagName('parentid')[0].childNodes[0].nodeValue;
    //log('*** DoEditorUpdates: in adds loop: theparentid = '+theparentid);
    /*if (theparentid.length == 0) continue;*/
    var thecontent_ = adds[i].getElementsByTagName('content')[0];
    //log('*** DoEditorUpdates: in adds loop: thecontent_.textContent = '+thecontent_.textContent);
    thecontent  = thecontent_.childNodes;
    //log('*** DoEditorUpdates: in adds loop, thecontent = '+thecontent);
    var theattrs   = adds[i].getElementsByTagName('attribute');
    //log('*** DoEditorUpdates: in adds loop, theattrs = '+theattrs);
    thedocument = document;
    var theparent = document.getElementById(theparentid);
    if (theparent == null) {
	thedocument = parent.document;
	theparent = parent.document.getElementById(theparentid);
	if (theparent == null) {
	   thedocument = top.document;
	   theparent = top.document.getElementById(theparentid);
	}
    }
    //log('*** DoEditorUpdates: in adds loop, theparent = '+theparent);
    //log('*** DoEditorUpdates: in adds loop, theparent.innerHTML is '+theparent.innerHTML);
    if (!theparent) continue;
    var thetag_ = adds[i].getElementsByTagName('tag');
    if (thetag_.length == 0) {
      var tag = 'div';
    } else {
      var tag = thetag_[0].childNodes[0].nodeValue;
    }
    //log('*** DoEditorUpdates: in adds loop, tag = '+tag);
    var node = thedocument.createElement(tag);
    //log('*** DoEditorUpdates: in adds loop, node = '+node);
    for (j = 0; j < theattrs.length; j++) {
      //log('*** DoEditorUpdates: in adds loop; theattrs loop, j = '+j);
      name = theattrs[j].getElementsByTagName('name')[0].childNodes[0].nodeValue;
      //log('*** DoEditorUpdates: in adds loop; theattrs loop, name is '+name);
      value = theattrs[j].getElementsByTagName('value')[0].childNodes[0].nodeValue;
      //log('*** DoEditorUpdates: in adds loop; theattrs loop, value is '+value);
      newattr = thedocument.createAttribute(name);
      //log('*** DoEditorUpdates: in adds loop; theattrs loop, newattr = '+newattr);
      newattr.nodeValue = value;
      node.setAttributeNode(newattr);
    }
    theparent.appendChild(node);
    //log('*** DoEditorUpdates: in adds loop; after theparent.appendChild(node), theparent.innerHTML is '+theparent.innerHTML);
    node.innerHTML = '';
    for (j = 0; j < thecontent.length; j++) {
      node.innerHTML += flatHTMLfromXML(thecontent[j]);
      //log('*** DoEditorUpdates (after append): node.innerHTML is '+node.innerHTML);
    }
    scripts = thedocument.getElementsByTagName('script');
    for (var j=0; j < scripts.length; j++) {
      eval(scripts[i].text);
    }
  }
  var clearforms = data.getElementsByTagName('clearform');
  for (i=0; i<clearforms.length; i++) {
    theid = clearforms[i].childNodes[0].nodeValue;
    var formdiv = document.getElementById(theid);
    var inputs  = formdiv.getElementsByTagName('input');
    var textareas = formdiv.getElementsByTagName('textarea');
    for (var i=0; i<inputs.length; i++) {
      if (inputs[i].type == 'text' || inputs[i].type == 'password') {
	inputs[i].value = '';
      }
    }
    for (i=0; i<textareas.length; i++) {
      textareas[i].value = '';
    }
  }
  Ps.update(document.getElementById('popup'));
}
function Move_Ajax(direction,url)
{
    //log('*** Move_Ajax("'+direction+'","'+url+'")');
    data = 'direction='+direction;
    jQuery.post(url,data,Move,'xml');
}

function Move (data) {
    //log('*** Move("'+data+'")');
    debug = data;
    var resultmesstag = data.getElementsByTagName('messages');
    //log('*** Move(): resultmesstag is '+resultmesstag);
    var messagesDiv = document.getElementById('ajax-messages');
    messagesDiv.innerHTML = '';
    if (resultmesstag.length > 0) {
        var resultmessHTML = resultmesstag[0].childNodes;
        if (resultmessHTML.length > 0) {
            for (var j = 0; j < resultmessHTML.length; j++) {
                //log('*** Move(): '+flatHTMLfromXML(resultmessHTML[j]));
                messagesDiv.innerHTML += flatHTMLfromXML(resultmessHTML[j]);
            }
        }
    }
    var mapsectors = data.getElementsByTagName('mapsector');
    //log('*** Move(): mapsectors is '+mapsectors);
    //log('*** Move(): mapsectors.length is '+mapsectors.length);
    for (var i=0; i<mapsectors.length; i++) {
        //log('*** Move(): in mapsectors loop: i = '+i);
        var isector = mapsectors[i].getAttribute('i');
        var jsector = mapsectors[i].getAttribute('j');
        //log('*** Move(): in mapsectors loop: isector = '+isector+', '+jsector);
        var themapsectorid = 'map_'+isector+'_'+jsector;
        //log('*** Move(): in mapsectors loop: themapsectorid = '+themapsectorid);
        var imgsrc = mapsectors[i].childNodes[0].nodeValue;
        var theimg = document.getElementById(themapsectorid);
        if (theimg == null) continue;
        //log('*** Move(): in mapsectors loop: theimg = '+theimg);
        var oldimg = theimg.getAttribute('src');
        if (oldimg != imgsrc) {
            theimg.setAttribute('src',imgsrc);
        }
    }
    //log('*** Move(): mapsectors updated');
    var sector_sidebarURLtag = data.getElementsByTagName('sector_sidebarURL');
    if (sector_sidebarURLtag == null || sector_sidebarURLtag.length == 0) return;
    var sector_sidebarURL = sector_sidebarURLtag[0].childNodes[0].nodeValue;
    var sector_sidebarIFrame = document.getElementById("sector_sidebar");
    sector_sidebarIFrame.setAttribute('src',sector_sidebarURL);
}



/**** Chat Code ****/

function SendWorldChatMessage(id, user, other, msg) {
    //log("*** SendWorldChatMessage(): id: "+id+",user: "+user+", other: "+other+", msg: "+msg);
    //log("*** SendWorldChatMessage(): from "+user.id);
    //log("*** SendWorldChatMessage(): to   "+other);
    chatCheckUpdate();
    data = 'function=send&other='+other+'&message='+encodeURI(msg);
    jQuery.post(chatURL,data,function(data) {chatCheckUpdate();},'json');
}
function chatCheckUpdate() {
    if(!instanse){
        instanse = true;
        data = 'function=update';
        jQuery.post(chatURL,data,DoChatUpdate,'json');
    } else {
        setTimeout(chatCheckUpdate, 1500);
    }
}
function DoChatUpdate(data) {
    //log("*** DoChatUpdate("+data+")");
    theother = data.other;
    //log("*** DoChatUpdate(): theother = "+theother);
    if (theother != null) {
        if (thechatbox == null) {
            other = theother;
            options = {id:"chat_div", 
                user:{screen : "", id : ""},
                title : "World Chat",
                messageSent : function(id, user, msg) {
                    //log("*** messageSent(): id: "+id+",user: "+user+", msg: "+msg);
                    //log("*** messageSent(): user.screen = "+user.screen);
                    //log("*** messageSent(): user.id = "+user.id);
                    $("#chat_div").chatbox("option", "boxManager").addMsg(user.screen,msg);
                    SendWorldChatMessage(id, user, other, msg);
                },
                boxClosed: function(id) {
                    jQuery.post(chatURL,"function=end",
                                function(data) {},'json');
                    reusechatbox = true;}
            };
            options.user.screen = userScreen;
            options.user.id = userId;
            options.title = "World Chat: "+data.otherscreen;
            thechatbox = $("#chat_div").chatbox(options);
        } else if (reusechatbox) {
            other = theother;
            $("#chat_div").chatbox("option", "title", "World Chat: "+data.otherscreen);
            //log("*** DoChatUpdate(): unhiding chatbox");
            $("#chat_div").chatbox("option", "hidden", false);
            reusechatbox = false;
        }
        themessage = data.message;
        //log("*** DoChatUpdate(): themessage = "+themessage);
        if (themessage != "") {
            lines = themessage.split("\n");
            for (i = 0; i < lines.length; i++) {
                wholeline = lines[i];
                colon = wholeline.indexOf(':');
                screenname = wholeline.substring(0,colon);
                message = wholeline.substring(colon+1,wholeline.length);
                $("#chat_div").chatbox("option", "boxManager").addMsg(screenname,message);
            }
        }
    } else if (thechatbox != null && !reusechatbox) {
        //log("*** DoChatUpdate(): clearing messages");
        //$("#chat_div").chatbox("option", "boxManager").clearMessages();
        //log("*** DoChatUpdate(): hiding chatbox");
        $("#chat_div").chatbox("option", "hidden", true);
        jQuery.post(chatURL,"function=end",function(data) {},'json');
        reusechatbox = true;
    }
    
    instanse = false;
}
function DoStartChat(data) {
    //log("*** DoStartChat("+data+")");
    state = data.state;
}
function Chat_Ajax(other) {
    //log("*** Chat_Ajax("+other+")");
    //log("*** Chat_Ajax(): thechatbox = "+thechatbox);
    if (thechatbox != null && !reusechatbox) return;
    data = 'function=start&other='+other;
    jQuery.post(chatURL,data,DoStartChat,'json');
}

/*** EMail Ajax code ***/

function Email_Ajax(toid,formdivid,url)
{
    var data = 'function=send&toid='+toid;
    var formdiv = document.getElementById(formdivid);
    var inputs  = formdiv.getElementsByTagName('input');
    var textareas = formdiv.getElementsByTagName('textarea');
    for (var i=0; i<inputs.length; i++) {
        data += '&' + encodeURI(inputs[i].name) + '=' + encodeURI(inputs[i].value);
    }
    for (i=0; i<textareas.length; i++) {
        data += '&' + encodeURI(textareas[i].name) + '=' + encodeURI(textareas[i].value);
    }
    jQuery.post(url,data,Email_Complete,'xml');
}

function Email_Complete(data)
{
    log("*** Email_Complete("+data+")");
    debug = data; 
    var resultmessHTML = data.getElementsByTagName('messages')[0].childNodes;
    if (resultmessHTML.length > 0) {
        var messagesDiv = document.getElementById('ajax-messages');
        messagesDiv.innerHTML = '';
        for (var j = 0; j < resultmessHTML.length; j++) {
            messagesDiv.innerHTML += flatHTMLfromXML(resultmessHTML[j]);
        }
    }
}

function resizeText(eventObject) {
    //log("*** resizeText("+eventObject+")");
    //log("*** resizeText() eventObject.type is "+eventObject.type);
    text = eventObject.target;
    //log("*** resizeText(): text.id is "+text.id);
    text.style.height = "auto";
    //log("*** resizeText(): text.scrollHeight is "+text.scrollHeight);
    text.style.height = (text.scrollHeight)+"px";
    Ps.update(document.getElementById(eventObject.data));
}




    
var W3CDOM = (document.createElement && document.getElementsByTagName);

function initFileUploads() {
    if (!W3CDOM) return;
    var fakeFileUpload = document.createElement('div');
    fakeFileUpload.className = 'fakefile';
    textinput = document.createElement('input');
    textinput.type = "text";
    fakeFileUpload.appendChild(textinput);
    var browsespan = document.createElement('span');
    browsespan.className = "button";
    browsespan.innerHTML = "Browse";
    fakeFileUpload.appendChild(browsespan);
    var x = document.getElementsByTagName('input');
    for (var i=0;i<x.length;i++) {
        if (x[i].type != 'file') continue;
        if (x[i].parentNode.className != 'fileinputs') continue;
        x[i].className = 'file hidden';
        var clone = fakeFileUpload.cloneNode(true);
        x[i].parentNode.appendChild(clone);
        x[i].relatedElement = clone.getElementsByTagName('input')[0];
        x[i].onchange = x[i].onmouseout = function () {
            this.relatedElement.value = this.value;
        }
    }
}

