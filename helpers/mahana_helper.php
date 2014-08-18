<?php
function get_streams() {
	
	$ci =& get_instance();
	
	$streams = $ci->streams->streams->get_streams('streams');
	$choices = ['0=No connection'];
	
	foreach($streams as $stream) {
		$choices[] = $stream->stream_slug.'='.$stream->stream_name;
	}
	return implode('|', $choices);
}