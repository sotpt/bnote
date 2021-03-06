<?php
/*
 * INTERFACE for BlueNote App
 * This interface can be called from anywhere in the web.
 * Parameters are given with the URL, results are returned as XML.
 * 
 * Usage
 * -----
 * bna-xml.php?pin=<pin>&func=<function name>[&parameter=value]*
 * 
 * @author Matti Maier
 */

require_once("bna-interface.php");

/*********************************************************
 * IMPLEMENTATION										 *
 *********************************************************/
require_once($dir_prefix . $GLOBALS['DIR_LIB'] . "xmlarray.php");

class BNAxml extends AbstractBNA {
	
	function beginOutputWith() {
		header('Content-type: application/xml');
		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		echo "<entities>\n";
	}
	
	function endOutputWith() {
		echo "</entities>";
	}

	function printEntities($selection, $line_node) {
		$root_node = $line_node . "s";
		echo "<$root_node>\n";
		
		$header = $selection[0];
		
		for($i = 1; $i < count($selection); $i++) {
			echo " <$line_node>\n";
			foreach($header as $j => $col) {
				$v = $selection[$i][strtolower($col)];
				$c = strtolower($col);
				echo "  <$c>".$v."</$c>\n";
			}
			echo " </$line_node>\n";
		}
		
		echo "</$root_node>";
	}
	
}

// run
new BNAxml();

?>