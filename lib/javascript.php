<?php  /// $Id: javascript.php,v 1.36.2.3 2008/07/18 07:14:12 scyrma Exp $
       /// Load up any required Javascript libraries

    if (!defined('MOODLE_INTERNAL')) {
        die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
    }

    if (!empty($CFG->aspellpath)) {      // Enable global access to spelling feature.
        echo '<script type="text/javascript" src="'.$CFG->httpswwwroot.'/lib/speller/spellChecker.js"></script>'."\n";
    }

    if (!empty($CFG->editorsrc) ) {
        foreach ( $CFG->editorsrc as $scriptsource ) {
            echo '<script type="text/javascript" src="'. $scriptsource .'"></script>'."\n";
        }
    }

?>
<!--<style type="text/css">/*<![CDATA[*/ body{behavior:url(<?php echo $CFG->httpswwwroot ?>/lib/csshover.htc);} /*]]>*/</style>-->

<!-- jquery files  -->
<script type="text/javascript" src="<?php echo $CFG->httpswwwroot ?>/lib/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="<?php echo $CFG->httpswwwroot ?>/lib/jquery-ui-1.8.2.custom.min.js"></script>
<script type="text/javascript" src="<?php echo $CFG->httpswwwroot ?>/lib/jquery.cookie.js"></script>
<script type="text/javascript" src="<?php echo $CFG->httpswwwroot ?>/lib/jquery.hotkeys.js"></script>
<script type="text/javascript" src="<?php echo $CFG->httpswwwroot ?>/lib/jquery.tree.js"></script>
<script type="text/javascript" src="<?php echo $CFG->httpswwwroot ?>/lib/jquery.prettyPhoto.js"></script>
<script type="text/javascript" src="<?php echo $CFG->httpswwwroot ?>/lib/jquery.mousewheel.min.js"></script>
<script type="text/javascript" src="<?php echo $CFG->httpswwwroot ?>/lib/jquery.thickbox.min.js"></script>
<script type="text/javascript" src="<?php echo $CFG->httpswwwroot ?>/lib/jquery.dropdownPlain.js"></script>
<script type="text/javascript" src="<?php echo $CFG->httpswwwroot ?>/lib/scripturizer.js"></script>
<script type="text/javascript" src="<?php echo $CFG->httpswwwroot ?>/lib/esvpopup.js"></script>
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>

<script type="text/javascript" src="<?php echo $CFG->httpswwwroot ?>/lib/javascript-static.js"></script>
<script type="text/javascript" src="<?php echo $CFG->httpswwwroot ?>/lib/javascript-mod.php"></script>
<script type="text/javascript" src="<?php echo $CFG->httpswwwroot ?>/lib/overlib/overlib.js"></script>
<script type="text/javascript" src="<?php echo $CFG->httpswwwroot ?>/lib/overlib/overlib_cssstyle.js"></script>
<script type="text/javascript" src="<?php echo $CFG->httpswwwroot ?>/lib/cookies.js"></script>
<script type="text/javascript" src="<?php echo $CFG->httpswwwroot ?>/lib/ufo.js"></script>
<script type="text/javascript" src="<?php echo $CFG->httpswwwroot ?>/lib/dropdown.js"></script>

<script type="text/javascript">

/** the following is for the jsddm version of drop down menu **/
var timeout    = 500;
var closetimer = 0;
var ddmenuitem = 0;

function jsddm_open()
{  jsddm_canceltimer();
   jsddm_close();
   ddmenuitem = $(this).find('ul').css('visibility', 'visible');}

function jsddm_close()
{  if(ddmenuitem) ddmenuitem.css('visibility', 'hidden');}

function jsddm_timer()
{  closetimer = window.setTimeout(jsddm_close, timeout);}

function jsddm_canceltimer()
{  if(closetimer)
   {  window.clearTimeout(closetimer);
      closetimer = null;}}

$(document).ready(function()
{  $('#jsddm > li').bind('mouseover', jsddm_open)
   $('#jsddm > li').bind('mouseout',  jsddm_timer)});

document.onclick = jsddm_close;
/** End jsddm version of drop down **/


function linkIsImage(link) {
    return /(.*?)\.(jpg|jpeg|png|gif)$/.test(link);
}

jQuery(document).ready(function () {
	jQuery('ul.activity-list div.plus-minus').click().toggle(function () {
		jQuery(this).parent().children('ul').toggle(300);
		jQuery(this).css({'background-image' : 'url(pix/t/switch_minus.gif)'});
	},
	function () {
		jQuery(this).parent().children('ul').toggle(300);
		jQuery(this).css({'background-image' : 'url(pix/t/switch_plus.gif)'});
	});
    jQuery('.my-message-box').click(function () {
        var linkelement = jQuery(this);
        var dialogbox = null;

        if (linkIsImage(linkelement.attr('href'))) {
            dialogbox = jQuery('<div style="text-align: center"><img src="'+linkelement.attr('href')+'"></div>')
                .dialog({
                    modal: true,
                    autoOpen: false,
                    title: linkelement.attr('title'),
                    width: 575,
                    height: 400
                });
        } else {
            if (jQuery(linkelement.attr('rel'))) {
                displayregion = linkelement.attr('rel');
            }
            mywidth = 575;
            if (jQuery(linkelement).attr('x')) {
                mywidth = jQuery(linkelement).attr('x');
                if (mywidth.search(/\%/) != -1) {       // this is a work in progress
                    mywidth = window.innerWidth * mywidth.replace(/\%/, '') / 100;
                    console.log(mywidth);
                }
            }
            mywidth = 400;
            if (jQuery(linkelement).attr('y')) {
                myheight = jQuery(linkelement).attr('x');
                if (myheight.search(/\%/) != -1) {  // this is a work in progress
                    myheight = window.innerHeight * myheight.replace(/\%/, '') / 100;
                    console.log(myheight);
                }
            }

            dialogbox = jQuery('<div></div>')
                .load(linkelement.attr('href') + ' #content')
                .dialog({
                    modal: true,
                    autoOpen: false,
                    title: linkelement.attr('title'),
                    width: mywidth,
                    height: myheight
            });
        }
        dialogbox.dialog('open');

        return false;
    });
});
</script>


<script type="text/javascript" defer="defer">
//<![CDATA[
setTimeout('fix_column_widths()', 20);
//]]>
</script>
<script type="text/javascript">
//<![CDATA[
function openpopup(url, name, options, fullscreen) {
    var fullurl = "<?php echo $CFG->httpswwwroot ?>" + url;
    var windowobj = window.open(fullurl, name, options);
    if (!windowobj) {
        return true;
    }
    if (fullscreen) {
        windowobj.moveTo(0, 0);
        windowobj.resizeTo(screen.availWidth, screen.availHeight);
    }
    windowobj.focus();
    return false;
}

function uncheckall() {
    var inputs = document.getElementsByTagName('input');
    for(var i = 0; i < inputs.length; i++) {
        inputs[i].checked = false;
    }
}

function checkall() {
    var inputs = document.getElementsByTagName('input');
    for(var i = 0; i < inputs.length; i++) {
        inputs[i].checked = true;
    }
}

function inserttext(text) {
<?php
    if (!empty($SESSION->inserttextform)) {
        $insertfield = "opener.document.forms['$SESSION->inserttextform'].$SESSION->inserttextfield";
    } else {
        $insertfield = "opener.document.forms['theform'].message";
    }
    echo "  text = ' ' + text + ' ';\n";
    echo "  if ( $insertfield.createTextRange && $insertfield.caretPos) {\n";
    echo "    var caretPos = $insertfield.caretPos;\n";
    echo "    caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;\n";
    echo "  } else {\n";
    echo "    $insertfield.value  += text;\n";
    echo "  }\n";
    echo "  $insertfield.focus();\n";
?>
}
<?php if (!empty($focus)) {
    if(($pos = strpos($focus, '.')) !== false) {
        //old style focus using form name - no allowed inXHTML Strict
        $topelement = substr($focus, 0, $pos);
        echo "addonload(function() { if(document.$topelement) document.$focus.focus(); });\n";
    } else {
        //focus element with given id
        echo "addonload(function() { if(el = document.getElementById('$focus')) el.focus(); });\n";
    }
    $focus=false; // Prevent themes from adding it to body tag which breaks addonload(), MDL-10249
} ?>

function getElementsByClassName(oElm, strTagName, oClassNames){
        var arrElements = (strTagName == "*" && oElm.all)? oElm.all : oElm.getElementsByTagName(strTagName);
        var arrReturnElements = new Array();
        var arrRegExpClassNames = new Array();
        if(typeof oClassNames == "object"){
                for(var i=0; i<oClassNames.length; i++){
                        arrRegExpClassNames.push(new RegExp("(^|\\s)" + oClassNames[i].replace(/\-/g, "\\-") + "(\\s|$)"));
                }
        }
        else{
                arrRegExpClassNames.push(new RegExp("(^|\\s)" + oClassNames.replace(/\-/g, "\\-") + "(\\s|$)"));
        }
        var oElement;
        var bMatchesAll;
        for(var j=0; j<arrElements.length; j++){
                oElement = arrElements[j];
                bMatchesAll = true;
                for(var k=0; k<arrRegExpClassNames.length; k++){
                        if(!arrRegExpClassNames[k].test(oElement.className)){
                                bMatchesAll = false;
                                break;
                        }
                }
                if(bMatchesAll){
                        arrReturnElements.push(oElement);
                }
        }
        return (arrReturnElements)
}
//]]>
</script>
