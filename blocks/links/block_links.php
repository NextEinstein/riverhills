<?php //$Id$

class block_links extends block_base {

    function init() {
        $this->title = get_string('title', 'block_links');
        $this->version = 2007101509;
    }

    function applicable_formats() {
        return array('all' => true);
    }

    function specialization() {
        $this->title = isset($this->config->title) ? format_string($this->config->title) : format_string(get_string('newlinksblock', 'block_links'));
    }

    function instance_allow_multiple() {
        return true;
    }

    function get_content() {
        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;

        $this->content->text = '<table align="center" style="width: 800px;" class="link-table"><tr>';

        $counter = 1;
        foreach ($this->config->text as $idx => $text) {
            if (empty($this->config->links[$idx])) {
                $this->content->text .= '<td class="c1" colspan="3"><h2>'.$text.'</h2></td>';
            } else {
                $this->content->text .= '<td class="c1" style="width: 15px">&nbsp;</td>';

                $this->content->text .= '<td class="c2" style="width: 16px; height: 16px;">';
                if (!empty($this->config->images[$idx])) {
                    $this->content->text .= '<img style="width: 16px; height: 16px;" src="'.$this->config->images[$idx].'" />';
                } else {
                    $this->content->text .= '&nbsp;';
                }
                $this->content->text .= '</td>';

                $this->content->text .= '<td class="c3"><a href="'.$this->config->links[$idx].'">'.$text.'</a></td>';
            }

            if ($counter++ % 2 == 0) {
                $this->content->text .= '</tr><tr>';
            }
        }

        $this->content->text .= '</tr></table>';

        $this->content->footer = '';

        return $this->content;
    }

    /**
     * Will be called before an instance of this block is backed up, so that any links in
     * any links in any HTML fields on config can be encoded.
     * @return string
     */
    function get_backup_encoded_config() {
        /// Prevent clone for non configured block instance. Delegate to parent as fallback.
        if (empty($this->config)) {
            return parent::get_backup_encoded_config();
        }
        $data = clone($this->config);
        $data->text = backup_encode_absolute_links($data->text);
        return base64_encode(serialize($data));
    }

    /**
     * This function makes all the necessary calls to {@link restore_decode_content_links_worker()}
     * function in order to decode contents of this block from the backup 
     * format to destination site/course in order to mantain inter-activities 
     * working in the backup/restore process. 
     * 
     * This is called from {@link restore_decode_content_links()} function in the restore process.
     *
     * NOTE: There is no block instance when this method is called.
     *
     * @param object $restore Standard restore object
     * @return boolean
     **/
    function decode_content_links_caller($restore) {
        global $CFG;

        if ($restored_blocks = get_records_select("backup_ids","table_name = 'block_instance' AND backup_code = $restore->backup_unique_code AND new_id > 0", "", "new_id")) {
            $restored_blocks = implode(',', array_keys($restored_blocks));
            $sql = "SELECT bi.*
                      FROM {$CFG->prefix}block_instance bi
                           JOIN {$CFG->prefix}block b ON b.id = bi.blockid
                     WHERE b.name = 'links' AND bi.id IN ($restored_blocks)";

            if ($instances = get_records_sql($sql)) {
                foreach ($instances as $instance) {
                    $blockobject = block_instance('links', $instance);
                    $blockobject->config->text = restore_decode_absolute_links($blockobject->config->text);
                    $blockobject->config->text = restore_decode_content_links_worker($blockobject->config->text, $restore);
                    $blockobject->instance_config_commit($blockobject->pinned);
                }
            }
        }

        return true;
    }

    /*
     * Hide the title bar when none set..
     */
    function hide_header(){
        return empty($this->config->title);
    }
}
?>
