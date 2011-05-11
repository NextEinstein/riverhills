<?php 
include_once($CFG->dirroot.'/course/format/page/lib.php');
echo '<div class="navbar2 clearfix">
        <table border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td class="dropdown-cell">'.
                page_print_site_page_structure_ul(null, 'dropdown').
            '</td>
            <td class="navbutton-cell">
            <div class="navbutton"><span>'.$button.'</span></div>
          </tr>
        </table>
      </div>';