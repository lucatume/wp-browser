<?php

namespace tad\WPBrowser\Interactions;


abstract class BaseButler
{

	/**
	 * @var Validator
	 */
	protected $validator;

	public function __construct(Validator $validator = null)
	{
		$this->validator = $validator ?: new Validator();
	}
}