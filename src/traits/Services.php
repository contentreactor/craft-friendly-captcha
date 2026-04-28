<?php
declare(strict_types=1);

namespace contentreactor\craftfriendlycaptcha\traits;

use contentreactor\craftfriendlycaptcha\Plugin;
use contentreactor\craftfriendlycaptcha\services\ValidateService;

/**
 * @mixin Plugin
 */
trait Services
{
	public static function config(): array
	{
		return [
			'components' => [
				'validateService' => ValidateService::class,
			],
		];
	}

	public function getValidate(): ValidateService
	{
		return $this->get('validateService');
	}
}