<?php

namespace contentreactor\craftfriendlycaptcha\services;

use contentreactor\craftfriendlycaptcha\Plugin;
use Craft;
use craft\base\Component;
use FriendlyCaptcha\SDK\{Client, ClientConfig};
use Twig\Markup;
use craft\helpers\Html;
use craft\helpers\Template;
use Exception;

class ValidateService extends Component
{
	protected array $endpoints = [
		'global' => 'https://api.friendlycaptcha.com/api/v1/',
		'eu' => 'https://eu-api.friendlycaptcha.eu/api/v1/',
	];

	protected function getApiKey(): string
	{
		return Plugin::getInstance()->getSettings()->getApiKey();
	}

	protected function getSiteKey(): string
	{
		return Plugin::getInstance()->getSettings()->getSiteKey();
	}

	protected function getVersion(): string
	{
		return Plugin::getInstance()->getSettings()->getVersion();
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
		if ($this->getVersion() === 'v1') {
			$solution = Craft::$app->getRequest()->getParam('frc-captcha-solution');
			$siteKey = $this->getSiteKey();
			$apiKey = $this->getApiKey();
			return $this->validateSolution($solution, $siteKey, $apiKey);
		}

		if ($this->getVersion() === 'v2') {
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

		return false;
	}

	public function renderWidget(array $attributes = [], bool $invisible = false): Markup
	{
		$settings = Plugin::$plugin->getSettings();

		if ($this->getVersion() === 'v2') {
			Craft::$app->view->registerJsFile(Craft::$app->assetManager->getPublishedUrl('@cfc/assets/js/friendlycaptchav2.min.js', true), ['async' => true, 'defer' => true, 'nomodule' => true]);
			Craft::$app->view->registerJsFile(Craft::$app->assetManager->getPublishedUrl('@cfc/assets/js/friendlycaptchav2.module.min.js', true), ['async' => true, 'defer' => true, 'type' => 'module']);
		}

		if ($this->getVersion() === 'v1') {
			Craft::$app->view->registerJsFile(Craft::$app->assetManager->getPublishedUrl('@cfc/assets/js/friendlycaptchav1.min.js', true), ['async' => true, 'defer' => true, 'nomodule' => true]);
			Craft::$app->view->registerJsFile(Craft::$app->assetManager->getPublishedUrl('@cfc/assets/js/friendlycaptchav1.module.min.js', true), ['async' => true, 'defer' => true, 'type' => 'module']);
		}

		$defaultAttributes = [
			'class' => 'frc-captcha',
			'data-sitekey' => $settings->getSiteKey(),
			'data-lang' => substr(Craft::$app->language ?? 'en', 0, 2),
			'data-start' => $settings->startEvent,
		];

		if ($invisible && $this->getVersion() === 'v2') {
			$defaultAttributes['data-start'] = 'auto';
			$defaultAttributes['class'] .= ' frc-captcha-hidden';
		}

		if ($settings->darkMode) {
			if ($this->getVersion() === 'v2') {
				$defaultAttributes['data-theme'] = 'dark';
			}

			if ($this->getVersion() === 'v1') {
				$defaultAttributes['class'] .= ' frc-captcha dark';
			}
		}

		$attributes = array_merge($defaultAttributes, $attributes);

		return Template::raw(
			Html::tag('div', '', $attributes)
		);
	}

	public function validateSolution(string $solution, string $siteKey, string $apiKey, string $endpoint = 'global'): bool
	{
		$endpointUrl = $this->getEndpointUrl($endpoint, 'siteverify');

		$curlHandle = curl_init();
		curl_setopt($curlHandle, CURLOPT_URL, $endpointUrl);
		curl_setopt($curlHandle, CURLOPT_POST, true);
		curl_setopt($curlHandle, CURLOPT_POSTFIELDS, http_build_query([
			'solution' => $solution,
			'siteKey' => $siteKey,
			'secret' => $apiKey
		]));
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($curlHandle);
		$curlError = curl_error($curlHandle);

		if (!curl_errno($curlHandle) && curl_getinfo($curlHandle, CURLINFO_HTTP_CODE) == 200) {
			$object = json_decode($response);
			curl_close($curlHandle);

			if (!$object->success) {
				Craft::warning('Friendly Captcha response did not validate: ' . $response, __METHOD__);
			} else {
				Craft::info('Friendly Captcha response validated', __METHOD__);
			}

			return $object->success ?? false;
		}

		curl_close($curlHandle);

		$errorMessage = 'Error validating Friendly Captcha solution';

		if ($curlError) {
			$errorMessage .= ' CURL error: ' . $curlError;
		} else {
			$errorMessage .= ' response: ' . $response;
		}

		Craft::error($errorMessage, __METHOD__);

		if (Craft::$app->config->general->devMode) {
			throw new Exception($errorMessage);
		} elseif (CRAFT_ENVIRONMENT != 'production') {
			return false;
		}

		return true;
	}

	public function getEndpointUrl(string $endpoint, string $service): string
	{
		if (!isset($this->endpoints[$endpoint])) {
			throw new Exception('Unsupported Friendly Captcha endpoint');
		}
		return $this->endpoints[$endpoint] . $service;
	}
}
