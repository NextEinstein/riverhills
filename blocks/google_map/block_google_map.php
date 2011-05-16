<?php //$Id: block_html.php,v 1.8.22.8 2009/10/30 23:36:26 poltawski Exp $

class block_google_map extends block_base {

    function init() {
        $this->title = get_string('googlemap', 'block_google_map');
        $this->version = 2011030600;
    }

    function applicable_formats() {
        return array('all' => true);
    }

    function specialization() {
        $this->title = isset($this->config->title) ? format_string($this->config->title) : format_string(get_string('googlemap', 'block_google_map'));
    }

    function instance_allow_multiple() {
        return true;
    }

    /*
     * Hide the title bar when none set..
     */
    function hide_header(){
        return empty($this->config->title);
    }

    function get_content() {
        global $CFG;

        require_js('http://maps.google.com/maps/api/js?sensor=true');

        static $content = false;

        if ($content !== false) {
            return $content;    // blocks get generated multiple times per page
        }

        $this->content = new stdClass();

        $uniqueid = 'google-map-'.$this->instance->id;

        $settings = new stdClass();
        $settings->mapcenter = !empty($this->config->center) ? $this->config->center : 0;
        $settings->zoom = !empty($this->config->zoom) ? $this->config->zoom : 9;
        $settings->mapCanvasId = $uniqueid;

        $markersarray = array();
        if (!empty($this->config->markers)) {
            foreach ($this->config->markers as $key => $marker) {
                $markersarray[$marker] = '';
                if (!empty($this->config->markerbubbles[$key])) {
                    $markersarray[$marker] = $this->config->markerbubbles[$key];
                }
            }
        }
        $settings->markers = $markersarray;

        if (!empty($this->config->polypoints)) {
            $settings->polygons = array();
            foreach ($this->config->polypoints as $key => $latlng) {
                $polygon = new stdClass();
                $polygon->latlng =          !empty($this->config->polypoints[$key])         ? $this->config->polypoints[$key]       : '';
                $polygon->hex =             !empty($this->config->polyhex[$key])            ? $this->config->polyhex[$key]          : '';
                $polygon->fillopacity =     !empty($this->config->polyfillopacity[$key])    ? $this->config->polyfillopacity[$key]  : 0;
                $polygon->strokeopacity =   !empty($this->config->polylineopacity[$key])    ? $this->config->polylineopacity[$key]  : 0;

                $settings->polygons[] = (array)$polygon;
            }
        }

        $settings = json_encode($settings);

        $width = !empty($this->config->width) ? $this->config->width : 200;
        $height = !empty($this->config->height) ? $this->config->height: 200;

        $content = "<div class=\"google-map\" id=\"{$uniqueid}\" style=\"width: {$width}px; height: {$height}px;\"></div>";
        $content .= "<script type=\"text/javascript\">
                        jQuery(document).ready(function () {
                            googlemap.initialize({$settings});
                        });
                     </script>";

        $this->content->text = $content;
        $this->content->footer = '';

        return $this->content;
    }

}
?>
