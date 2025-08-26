<?php

namespace contentreactor\craftfriendlycaptcha\services;

use contentreactor\craftfriendlycaptcha\Plugin;
use Craft;
use craft\base\Component;
use FriendlyCaptcha\SDK\{Client, ClientConfig};
use Twig\Markup;
use craft\helpers\Html;
use craft\helpers\Template;

class ValidateService extends Component
{
	protected function getApiKey(): string
	{
		return Plugin::getInstance()->getSettings()->getApiKey();
	}

	protected function getSiteKey(): string
	{
		return Plugin::getInstance()->getSettings()->getSiteKey();
	}

	public function getConnection()
	{
		$settings = Plugin::$plugin->getSettings();
		$config = new ClientConfig();
		$config->setAPIKey($settings->getApiKey())->setSitekey($settings->getSiteKey());
		return new Client($config);
	}

	public function validateRequest(): bool
	{
		$didSubmit = Craft::$app->getRequest()->getIsPost();

		if (!$didSubmit) return false;

		$captchaResponse = Craft::$app->getRequest()->getBodyParam('frc-captcha-response');
		$captchaResult = $this->getConnection()->verifyCaptchaResponse($captchaResponse);

		if (!$captchaResult->wasAbleToVerify()) {
			Craft::info('Failed to verify captcha response: ' . $captchaResult->getErrorCode() . ' ' . print_r($captchaResult->getResponseError(), true), 'contentreactor-friendly-captcha');

			if ($captchaResult->isClientError()) {
				Craft::info('CAPTCHA CONFIG ERROR:' . $captchaResult->getErrorCode() . ' ' . print_r($captchaResult->getResponseError(), true), 'contentreactor-friendly-captcha');
			}

			return false;
		}
		return $captchaResult->shouldAccept();
	}

	public function renderWidget(array $attributes = []): Markup
	{
		$settings = Plugin::$plugin->getSettings();

		Craft::$app->view->registerJsFile(Craft::$app->assetManager->getPublishedUrl('@cfc/assets/js/friendlycaptcha.min.js', true), ['async' => true, 'defer' => true, 'nomodule' => true]);
		Craft::$app->view->registerJsFile(Craft::$app->assetManager->getPublishedUrl('@cfc/assets/js/friendlycaptcha.module.min.js', true), ['async' => true, 'defer' => true, 'type' => 'module']);

		$defaultAttributes = [
			'class' => 'frc-captcha',
			'data-sitekey' => $settings->getSiteKey(),
			'data-lang' => substr(Craft::$app->language ?? 'en', 0, 2),
			'data-start' => $settings->startEvent,
		];

		if ($settings->darkMode) {
			$defaultAttributes['class'] = 'frc-captcha dark';
		}

		$attributes = array_merge($defaultAttributes, $attributes);

		return Template::raw(
			Html::tag('div', '', $attributes)
		);
	}
}
