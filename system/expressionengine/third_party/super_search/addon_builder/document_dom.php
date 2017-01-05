<?php if ( ! defined('EXT')) exit('No direct script access allowed');

//--------------------------------------------
//	THIS IS DEPRICATED DO NOT USE
//--------------------------------------------

 /**
 * Solspace - Add-On Builder Framework
 *
 * @package		Add-On Builder Framework
 * @author		Solspace DevTeam
 * @copyright	Copyright (c) 2008-2011, Solspace, Inc.
 * @link		http://solspace.com/docs/
 * @version		1.2.4
 */

 /**
 * DocumentDOM
 *
 * Allows the building of HTML Elements in a manner similar to how JavaScript does it.  This allows
 * one to build form elements initially in the code and then modify certain values in the View file
 * prior to outputting the actual HTML string
 *
 * @package 	Add-On Builder Framework
 * @subpackage	documentDOM
 * @author		Solspace DevTeam
 * @link		http://solspace.com/docs/
 */

class documentDOM_super_search {

	var $elements  = array();
	var $innerHTML = '';

    /** -------------------------------------
    /**  Constructor
    /** -------------------------------------*/

	function documentDOM_super_search()
	{
		// This is done by Add-On Builder too, but we do it here as well just in case this
		// class is instantiated separately from Add-On Builder.

		if (defined('NL') === FALSE)
		{
			define('NL', "\n");
		}
	}
	/* END documentDOM_super_search() */

	/** -------------------------------------
    /**  Create Element
    /** -------------------------------------*/

	function &createElement($name='', $attributes = array())
    {
    	$obj = new createElement_super_search($name, $attributes);
    	return $obj;
	}
	/* END */

	/** -------------------------------------
    /**  appendChild
    /** -------------------------------------*/

	function appendChild(&$obj)
    {
    	if ( ! is_object($obj)) return FALSE;

    	$this->elements[] =& $obj;
	}
	/* END */

	/** -------------------------------------
    /**  prependChild
    /** -------------------------------------*/

	function prependChild(&$obj)
    {
    	if ( ! is_object($obj)) return FALSE;

    	array_unshift($this->elements, $obj);
	}
	/* END */

	/** -------------------------------------
    /**  Create Document Output
    /** -------------------------------------*/

	function createOutput()
    {
    	if (sizeof($this->elements) == 0) return FALSE;

    	$str = '';

    	foreach($this->elements as $element)
    	{
    		$str .= $element->outputElement();
    	}

    	return $str;
	}
	/* END */

}
/* END documentDOM Class */


/** --------------------------------------------
/**  Creates a new HTML Element
/** --------------------------------------------*/

class createElement_super_search extends documentDOM_super_search
{
	var $element	= '';
	var $attributes = array();
	var $no_closing = array('input', 'br', 'img', 'meta', 'hr');

	/** --------------------------------------------
	/**  Creates a new HTML Element
	/** --------------------------------------------*/

	function createElement_super_search($name, $attributes = array())
	{
		if ($name == '') return;

		$this->element = $name;

		if ( is_array($attributes))
		{
			foreach($attributes as $name => $value)
			{
				$this->setAttribute($name, $value);
			}
		}
	}
	/* END */

	/** --------------------------------------------
	/**  Sets an Attribute for the Element Created by This Object
	/** --------------------------------------------*/

	function setAttribute($name, $value='')
	{
		$this->attributes[preg_replace("/[^a-z]/i", '', $name)] = $value;
	}
	/* END */


	/** --------------------------------------------
	/**  Returns the Value of an Existing Attribute
	/** --------------------------------------------*/

	function getAttribute($name)
	{
		return (isset($this->attributes[$name])) ? $this->attributes[$name] : NULL;
	}
	/* END */

	/** --------------------------------------------
	/**  Appends onto an Existing Attribute
	/** --------------------------------------------*/

	function appendAttribute($name, $value = '')
	{
		$name = preg_replace("/[^a-z]/i", '', $name);

		$this->attributes[$name] = ( ! isset($this->attributes[$name])) ? $value : $this->attributes[$name]." ".$value;
	}
	/* END */


	/** --------------------------------------------
	/**  Takes Our Object and Outputs Element as HTML String
	/** --------------------------------------------*/

	function output() { return $this->outputElement(); }

	function outputElement()
    {
    	if ($this->element == '') return '';

    	return NL.'<'.
    		   $this->element.
    		   $this->outputAttributes().
    		   ( ! in_array($this->element, $this->no_closing) ? '' : ' /').
    		   '>'.
    		   $this->outputChildren().
    		   $this->innerHTML.
    		   ( ! in_array($this->element, $this->no_closing) ? '</'.$this->element.'>' : '');
	}
	/* END */


	/** --------------------------------------------
	/**  Outputs all Attributes of Element
	/**  - Used by outputElement, but can be used separately too
	/** --------------------------------------------*/

	function outputAttributes()
    {
    	if (sizeof($this->attributes) == 0 ) return '';

    	$str = '';

    	foreach($this->attributes as $name => $value)
    	{
    		if ($value === FALSE) continue;

    		$str .= ' '.$name.'="'.htmlspecialchars($value).'"';
    	}

    	return $str;
	}
	/* END */


	/** --------------------------------------------
	/**  Regression Method for Parsing out Children of Element
	/** --------------------------------------------*/

	function outputChildren()
    {
    	$str = '';

    	foreach($this->elements as $element)
    	{
    		$str .= $element->outputElement().NL;
    	}

    	return $str;
	}
	/* END */
}
/* END createElement Class */


/*
=====================================================
 EXAMPLES
=====================================================

$document =& new documentDOM();

$input =& $document->createElement('input');
$input->setAttribute('type', 'checkbox');
$input->setAttribute('value', 'test');

$document->appendChild($input);

$input->setAttribute('checked', 'checked');

echo $document->createOutput();

echo "\n\n<br /><br />\n\n";

$document =& new documentDOM();

$table =& $this->document->createElement('table');
$table->setAttribute('cellspacing', '0');
$table->setAttribute('cellpadding', '0');

$row  =& $this->document->createElement('tr');
$cell =& $this->document->createElement('td');
$cell->innerHTML = "Text";

$row->appendChild($cell);
$table->appendChild($row);

echo $table->output();


*/
