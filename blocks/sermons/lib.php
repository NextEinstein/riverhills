<?php

function sermon_block_make_name_safe_for_id($seriesname) {
	return preg_replace('/\'/', '&#39;',$seriesname);
}

?>