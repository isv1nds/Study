<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Main\Entity;

class Event extends \Bitrix\Main\Event
{
	protected $entity;

	/**
	 * @param Base $entity
	 * @param string $type
	 * @param array $parameters
	 */
	public function __construct(Base $entity, $type, array $parameters = array())
	{
		parent::__construct($entity->getModule(), $entity->getName().$type, $parameters);
		$this->entity = $entity;
	}

	/**
	 * Returns entity
	 */
	public function getEntity()
	{
		return $this->entity;
	}

	/**
	 * Checks the result of the event for errors, fills the Result object.
	 * Returns true on errors, false on no errors.
	 *
	 * @param Result $result
	 * @return bool
	 */
	public function getErrors(Result $result)
	{
		$hasErrors = false;
		if ($this->getResults() != null)
		{
			/** @var $evenResult EventResult */
			foreach($this->getResults() as $evenResult)
			{
				if($evenResult->getResultType() === EventResult::ERROR)
				{
					$hasErrors = true;
					$result->addErrors($evenResult->getErrors());
				}
			}
		}
		return $hasErrors;
	}

	/**
	 * Merges the data fields set in the event handlers with the source fields.
	 * Returns a merged array of the data fields from the all event handlers.
	 *
	 * @param array $data
	 * @return array
	 */
	public function mergeFields(array $data)
	{
		if ($this->getResults() != null)
		{
			/** @var $evenResult EventResult */
			foreach($this->getResults() as $evenResult)
			{
				if($evenResult->getResultType() !== EventResult::ERROR)
				{
					$removed = $evenResult->getUnset();
					foreach($removed as $val)
					{
						unset($data[$val]);
					}

					$modified = $evenResult->getModified();
					if(!empty($modified))
					{
						$data = array_merge($data, $modified);
					}
				}
			}
		}
		return $data;
	}
}
