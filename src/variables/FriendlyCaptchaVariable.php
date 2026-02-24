<?php

namespace contentreactor\craftfriendlycaptcha\variables;

use contentreactor\craftfriendlycaptcha\assets\CssBundle;
use contentreactor\craftfriendlycaptcha\Plugin;
use Craft;
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
	 * {{ craft.friendlyCaptcha.renderWidget({
	 *		invisible: true
	 *	}) }}
	 *
	 * @param array $attributes
	 * @return Markup
	 * @throws Exception
	 * @throws InvalidConfigException
	 */
	public function renderWidget(array $attributes = []): Markup
	{
		$attributes = $config['attributes'] ?? [];
		$invisible  = $config['invisible'] ?? false;
		Craft::$app->getView()->registerAssetBundle(CssBundle::class);
		return Plugin::getInstance()->getValidate()->renderWidget($attributes, $invisible);
	}
}
