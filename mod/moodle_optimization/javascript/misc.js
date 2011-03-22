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
    jQuery('.my-message-box').click(function (event) {
        var linkelement = jQuery(this);
        var dialogbox = null;

        event.preventDefault();

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
            myheight = 400;
            if (jQuery(linkelement).attr('y')) {
                myheight = jQuery(linkelement).attr('x');
                if (myheight.search(/\%/) != -1) {  // this is a work in progress
                    myheight = window.innerHeight * myheight.replace(/\%/, '') / 100;
                    console.log(myheight);
                }
            }

            $('<div></div>')
                .load(linkelement.attr('href') + ' #content *')
                .dialog({
                    modal: true,
                    autoOpen: true,
                    title: linkelement.attr('title'),
                    width: mywidth,
                    height: myheight
            });
        }

    }, false);
});