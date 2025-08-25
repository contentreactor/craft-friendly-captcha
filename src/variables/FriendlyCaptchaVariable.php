<?php

namespace contentreactor\craftfriendlycaptcha\variables;

use contentreactor\craftfriendlycaptcha\Plugin;
use Twig\Markup;
use yii\base\Exception;
use yii\base\InvalidConfigException;

class FriendlyCaptchaVariable
{

	/**
	 * {{ craft.friendlyCaptcha.siteKey }}
	 *
	 * @return string
	 */
	public function siteKey(): string
	{
		return Plugin::$plugin->getSettings()->getSiteKey();
	}

	/**
	 * {{ craft.friendlyCaptcha.validateRequest }}
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function validateRequest(): bool
	{
		return Plugin::getInstance()->getValidate()->validateRequest();
	}

	/**
	 * {{ craft.friendlyCaptcha.renderWidget() }}
	 *
	 * @param array $attributes
	 * @return Markup
	 * @throws Exception
	 * @throws InvalidConfigException
	 */
	public function renderWidget(array $attributes = []): Markup
	{
		return Plugin::getInstance()->getValidate()->renderWidget($attributes);
	}
}
