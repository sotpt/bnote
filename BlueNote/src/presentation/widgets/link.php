<?php
/**
 * A class to make links consistent
 **/

class Link implements iWriteable {
	
	private $href;
	private $label;
	private $target;
	private $icon;

	/**
	 * Creates a link
	 * @param $href String to where the field links
	 * @param $label Label of the link field
	 */
	function __construct($href, $label) {
		$this->href = $href;
		$this->label = $label;
	}

	/**
	 * Sets the links target.
	 * @param String $target HTML target value.
	 */
	function setTarget($target) {
		$this->target = $target;
	}

	function write() {
		echo $this->generate();
	}

	/**
	 * Returns a string with the elements HTML code
	 */
	function toString() {
		return $this->generate();
	}
	
	private function generate() {
		if(isset($this->target) && $this->target != "") {
			$target = 'target="' . $this->target . '"';
		}
		else {
			$target = "";
		}
		
		if(isset($this->icon) && $this->icon != "") {
			$icon = "<img src=\"" . $GLOBALS["DIR_ICONS"] . $this->icon . ".png\" height=\"15px\" alt=\"\" border=\"0\" />&nbsp;";
		}
		else {
			$icon = "";
		}
		
		return '<a class="linkbox" ' . $target . 'href="' . $this->href . '">'
		     . '<span class="linkbox">' . $icon . $this->label . '</span></a>';
	}

	/**
	 * To add an icon in front of the caption, execute this function with a
	 * name of the icon from the icons folder.
	 * @param String $icon_id Name of the icon file in the icon folder.
	 */
	function addIcon($icon_id) {
		$this->icon = $icon_id;
	}
	
}

?>