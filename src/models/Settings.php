<?php

namespace contentreactor\craftfriendlycaptcha\models;

use craft\base\Model;
use craft\behaviors\EnvAttributeParserBehavior;
use craft\helpers\App;

/**
 * Craft Friendly Captcha settings
 */
class Settings extends Model
{
	/**
	 * siteKey from generated for the Friendly Captcha account
	 *
	 * @var string
	 */
	public string $siteKey = '';

	/**
	 * secret from generated for the Friendly Captcha account
	 *
	 * @var string
	 */
	public string $apiKey = '';

	/**
	 * Validate UsersRegistration
	 *
	 * @var bool
	 */
	public bool $validateUsersRegistration = false;

	/**
	 * start puzzle challenge on event
	 * https://docs.friendlycaptcha.com/#/widget_api?id=data-start-attribute
	 *
	 * @var string
	 */
	public string $startEvent = 'focus';

	/**
	 * @var bool
	 */
	public bool $darkMode = false;

	public function getSiteKey(): string
	{
		return App::parseEnv($this->siteKey);
	}

	public function getApiKey(): string
	{
		return App::parseEnv($this->apiKey);
	}

	public function behaviors(): array
	{
		return [
			'parser' => [
				'class' => EnvAttributeParserBehavior::class,
				'attributes' => ['siteKey', 'apiKey'],
			],
		];
	}

	public function rules(): array
	{
		return [
			['siteKey', 'string'],
			['apiKey', 'string'],
			['startEvent', 'in', 'range' => ['auto', 'focus', 'none']],
			[['siteKey', 'apiKey', 'startEvent'], 'required'],
			['validateUsersRegistration', 'boolean'],
		];
	}
}
