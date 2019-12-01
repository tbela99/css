<?php 

namespace CSS;

/**
 * remove white space and empty declarations for now
 * @todo optimize css properties
 * - remove unit from 0
 * - optimize color
 * - compute short-hand properties? (color, background, border, border-radius, etc)?
 */

class Compress extends Identity {

	public function onEmit ($value) {

		return trim($value);
	}

	public function onComment ($value) {

		return '';
	}
	
	public function onMap($nodes, $type) {

		if ($type == 'rules' || $type == 'declarations') {

			// optimize css properties
		}

		return $nodes;
	}
	
	public function onCompile ($css) {

		return str_replace(';}', '}', $css);
	}

}

