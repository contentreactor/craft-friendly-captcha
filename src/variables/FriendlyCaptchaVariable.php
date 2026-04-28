<?php
declare(strict_types=1);

namespace contentreactor\craftfriendlycaptcha\variables;

use contentreactor\craftfriendlycaptcha\Plugin;
use Exception;
use Twig\Markup;

class FriendlyCaptchaVariable
{
	/**
	 * ```twig
	 *
	 * {{ craft.friendlyCaptcha.siteKey }}
	 * ```
	 *
	 * @return string
	 */
	public function siteKey(): string
	{
		return Plugin::getInstance()->getSettings()->getSiteKey();
	}

	/**
	 * ```twig
	 *
	 * {{ craft.friendlyCaptcha.validateRequest }}
	 * ```
	 *
	 * @throws Exception
	 */
	public function validateRequest(): bool
	{
		return Plugin::getInstance()->getValidate()->validateRequest();
	}

	/**
	 * ```twig
	 *
	 * {{ craft.friendlyCaptcha.renderWidget() }}
	 *
	 * {{ craft.friendlyCaptcha.renderWidget({ invisible: true }) }}
	 * ```
	 *
	 * @param array $attributes
	 * @return Markup
	 */
	public function renderWidget(array $attributes = []): Markup
	{
		$invisible  = $attributes['invisible'] ?? false;
		return Plugin::getInstance()->getValidate()->renderWidget($attributes, $invisible);
	}
}
