<?php
namespace Bitrix\Seo\Engine;

class YandexException
	extends \Exception
{
	protected $code;
	protected $message;

	protected $result;

	public function __construct(\CHTTP $queryResult, \Exception $previous = null)
	{
		$this->result = $queryResult;

		if($this->parseError())
		{
			parent::__construct($this->code.': '.$this->message, $status, $previous);
		}
		else
		{
			parent::__construct($queryResult->result, $queryResult->status, $previous);
		}
	}

	public function getStatus()
	{
		return $this->result->status;
	}

	protected function parseError()
	{
		if(preg_match("/<error code=\"([^\"]+)\"><message>([^<]+)<\/message><\/error>/", $this->result->result, $matches))
		{
			$this->code = $matches[1];
			$this->message = $matches[2];
			return true;
		}
		return false;
	}
}
