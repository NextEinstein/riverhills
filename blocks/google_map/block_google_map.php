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
        static $content = false;

        if ($content !== false) {
            return $content;    // blocks get generated multiple times per page
        }

        $this->content = new stdClass();

        $uniqueid = 'google-map-'.$this->instance->id;

        $settings = new stdClass();
        $settings->mapcenter = $this->config->center;
        $settings->zoom = $this->config->zoom;
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

        $settings = json_encode($settings);

        $content = "<div class=\"google-map\" id=\"{$uniqueid}\" style=\"width: {$this->config->width}px; height: {$this->config->height}px;\"></div>";
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
