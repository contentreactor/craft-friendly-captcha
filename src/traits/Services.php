<?php

namespace contentreactor\craftfriendlycaptcha\traits;

use contentreactor\craftfriendlycaptcha\services\ValidateService;

trait Services
{
	public function getValidate(): ValidateService
	{
		return $this->get('validateService');
	}
}