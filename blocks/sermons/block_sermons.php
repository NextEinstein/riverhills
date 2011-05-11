<?PHP //$Id: block_course_list.php,v 1.46.2.6 2008/08/29 04:23:38 peterbulmer Exp $

class block_sermons extends block_base {
    function init() {
        $this->title = get_string('sermons', 'resource');
        $this->version = 200708130;
    }
    
    function has_config() {
        return false;
    }

    function get_content() {
        global $CFG, $COURSE;

        $currentorderby = optional_param('orderby', 0, PARAM_INT);
        $currentorder   = optional_param('order'  , 0, PARAM_INT);

        $this->content->text = '<div id="sermon_block_sermon_player"></div>';

        $sorttypes = $resourcetypes = get_list_of_plugins('blocks/sermons/type');

        $sortby = new stdClass();

        $sortby->data = get_string('sortby', 'resource');
        $sortby->attributes = new stdClass();
        $sortby->attributes->id = "sortby";
        $sortby->state = 'open';

        $sortby->children = array();
        foreach ($sorttypes as $sorttype) {
            $child = new stdClass();

            $child->attributes = new stdClass();
            $child->attributes->id = $sorttype .'~';

            $child->data = get_string('sortby:'.$sorttype, 'resource');
            $child->state = "closed";

            $sortby->children[] = $child;
        }

        $jstree = 
          "var stat = ".json_encode($sortby).";
           jQuery(document).ready(function () {

            function parse_id(htmlid) {
                var result = /^(.*)~(.*)$/i.exec(htmlid);

                if (result == null) {
                    return false;
                }

                return result;
            }

            jQuery('#json_sermonlist_tree').tree({
                data : {
                    type : 'json',
                    async : true,
                    opts : {
                        async : true,
                        method : 'POST',
                        url : '{$CFG->wwwroot}/blocks/sermons/sermonsortedbranch.php'
                    }
                },
                type : {
                    draggable : false
                },
                callback : {
                    //stop using static after firsttime load
                    onload : function (t) {
                        t.settings.data.opts.static = false;
                    },
                    beforedata : function (n, t) { 
                        if(n == false) { 
                            t.settings.data.opts.static = stat;
                            return;
                        }else {
                            t.settings.data.opts.static = false;
                        }

                        parsedid = parse_id(jQuery(n).attr('id'));
                        sorttype = parsedid[1];
                        nodename = parsedid[2];

                        return { 'sorttype' : sorttype, 'nodename' : nodename };
 
                    },
                    ondblclk : function (node, tree_obj) {

                    window.location = '{$CFG->wwwroot}/mod/resource/view.php' +
                                      '?id=' + jQuery(node).attr('id');
                    },
                    onselect : function (node, tree_obj) {
                    	parsedid = parse_id(jQuery(node).attr('id'));
                    	id = parsedid[1];
                    	if (id > 0) {
                    		sermonname = parsedid[2];
                        	jQuery.ajax({
                            	type: 'POST',
	                            url: '{$CFG->wwwroot}/mod/resource/view.php',
    	                        data: 'id=' + jQuery(node).attr('id'),
        	                    success: function(html){
        	                    	jQuery('div#sermon_block_sermon_player').hide();
            	                    jQuery('div#sermon_block_sermon_player').html(html);
                	                var dialog = jQuery('div#sermon_block_sermon_player').dialog({autoOpen: false, height: 320, width: 850, modal: true});
                    	            dialog.dialog('option', 'title', sermonname);
                        	        dialog.dialog('open');
                        	        jQuery('div#sermon_block_sermon_player').show();
                            	}
                        	});
                        } else {
                        	return false;
                        }
                    }
                }
            });
        });";

$this->content->text .= '<script type="text/javascript">
                                    jQuery(document).ready(function () { 
                                        var scrolling = null;
 
                                        function scroll(direction, persistent) {

                                            var d = document.getElementById("json_sermonlist_tree");

                                            if (direction > 0) {
                                            	d.scrollTop = d.scrollTop - 25;
                                            } else if (direction < 0) {
                                            	d.scrollTop = d.scrollTop + 25;
                                            }

                                            if (persistent) {
                                            	scrolling = window.setTimeout(function() {
                                                	scroll(direction, persistent);
                                            	}, 100);
											}
                                        }

                                        function stop_scroll() {
                                            window.clearTimeout(scrolling);
                                        }

                                        jQuery("#more-sermons-bottom").mouseover(function() {scroll(-1, true)});
                                        jQuery("#more-sermons-bottom").mouseout(function() {stop_scroll()});

                                        jQuery("#more-sermons-top").mouseover(function() {scroll(1, true)});
                                        jQuery("#more-sermons-top").mouseout(function() {stop_scroll()});
                                        
                                        jQuery("#json_sermonlist_tree").bind("mousewheel",function(event, delta) { scroll(delta, false); return false; }); 
                                        
                                    });
                                </script>';
        $this->content->text .= '<div id="more-sermons-top" class="top">'.get_string('moresermonstop', 'resource').'</div>';
        $this->content->text .= '<script type="text/javascript">'.$jstree.'</script>';
        $this->content->text .= '<div id="json_sermonlist_tree"></div>';
        $this->content->text .= '<div id="more-sermons-bottom" class="bottom">'.get_string('moresermonsbottom', 'resource').'</div>';

        $this->content->footer = '';
        

        return $this->content->text;
    }

}


?>
<!--  -->