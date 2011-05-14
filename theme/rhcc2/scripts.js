
/* Tooltip from CSS Globe written by Alen Grakalic (http://cssglobe.com)----------  */

this.tooltip = function(){	

	/* CONFIG */		

		xOffset = -5;

		yOffset = 5;		

		// these 2 variable determine popup's distance from the cursor

		// you might want to adjust to get the right result		

	/* END CONFIG */		

	jQuery.noConflict();

  jQuery("a.tooltip").hover(function(e){											  

		this.t = this.title;

		this.title = "";									  

		jQuery("body").append("<p class='itooltip'>"+ this.t +"</p>");

		jQuery(".itooltip")

			.css("top",(e.pageY - xOffset) + "px")

			.css("left",(e.pageX + yOffset) + "px")

			.fadeIn(300);		

    },

	function(){

		this.title = this.t;		

		jQuery(".itooltip").remove();

    });	

	jQuery("a.tooltip").mousemove(function(e){

		jQuery(".itooltip")

			.css("top",(e.pageY - xOffset) + "px")

			.css("left",(e.pageX + yOffset) + "px");

	});			

};



jQuery.noConflict();

  jQuery(document).ready(function(){

	tooltip();

        // do some sideblock slide action stuff
	jQuery("#right-column .sideblock .title h2, #left-column .sideblock .title h2").css("cursor","pointer");
	jQuery("#right-column div.sideblock").each( function () {
               jQuery(this).find("div.content").slideDown("slow");
               return false;
        });
        jQuery("#left-column div.sideblock").each( function () {
               jQuery(this).find("div.content").slideDown("slow");
               return false;
        });

        jQuery("#right-column .sideblock .title h2, #left-column .sideblock .title h2").click(function () {
            var clickContentVisible = jQuery(this).closest("div.header").siblings('div.content').is(':visible');

            jQuery(this).closest('#layout-table td').find('div.content').slideUp();

            // only show the one clicked on if it wasn't already displayed
            if (!clickContentVisible) {
                jQuery(this).closest("div.header").siblings('div.content').slideToggle("slow");
            }
            
        });
        jQuery("#right-column .sideblock .title h2").hover(function () {
            jQuery(this).animate({paddingLeft : '25px'}, 500);
                    }, function() {
                    jQuery(this).animate({paddingLeft : '15px'}, 500);
        });

        // drop down menu stuff
	jQuery("#dropmenu ul").css({display: "none"}); // Opera Fix
	jQuery("#dropmenu a").removeAttr("title");
	jQuery("#dropmenu li").hover(function(){
		jQuery(this).find('ul:first').css({visibility: "visible",display: "none"}).show("slow");
		jQuery(this).css({background: "#6a3b14"});
		},function(){
		jQuery(this).find('ul:first').css({visibility: "hidden"});
		jQuery(this).css({background: "none"});
	});
	jQuery("#dropmenu ul li ul").parent().children("a").prepend("<span style='float:right;'>&rsaquo;</span>");

	jQuery("#new").hover(function() {
		jQuery(this).animate({marginTop : '0'}, 300);
		}, function() {
		jQuery(this).animate({marginTop : '-5px'}, 300);
	});

	jQuery("#footer #socialMedia a img").animate({opacity : ".60"}, 500);
	jQuery("#footer #socialMedia a img").hover(function() {
		jQuery(this).animate({opacity : '1'}, 500);
		}, function() {
		jQuery(this).animate({opacity : ".60"}, 500);
	});

});