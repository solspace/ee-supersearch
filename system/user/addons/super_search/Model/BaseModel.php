<?php

namespace Solspace\Addons\SuperSearch\Model;

use EllisLab\ExpressionEngine\Service\Model\Model;

//extend model and add some helpful methods
class BaseModel extends Model
{
	// --------------------------------------------------------------------

	/**
	 * To Array
	 *
	 * @access	public
	 * @return	array	key->value array of not null object var values
	 */

	public function asArray()
	{
		$vars = get_class_vars(get_class($this));

		$result = array();

		foreach ($vars as $key => $value)
		{
			if (substr($key, 0, 1) !== '_' && $this->$key !== null)
			{
				$result[$key] = $this->$key;
			}
		}
		return $result;
	}
	//END asArray
}
//END BaseModel
