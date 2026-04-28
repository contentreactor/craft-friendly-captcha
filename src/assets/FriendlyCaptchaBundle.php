<?php
declare(strict_types=1);

namespace contentreactor\craftfriendlycaptcha\assets;

use contentreactor\craftfriendlycaptcha\models\Settings;
use Craft;
use craft\web\AssetBundle;

class FriendlyCaptchaBundle extends AssetBundle
{
	public $sourcePath = '@cfc/assets';
	/**
	 * @param string $version
	 * @param bool $invisible
	 * @return self
	 */
	public static function registerBundle(string $version = Settings::PLUGIN_VERSION_2, bool $invisible = false): self
	{
		$bundle = parent::register(Craft::$app->getView());

		if ($invisible) $bundle->css = ['css/friendlycaptcha.css'];

		$bundle->js = match ($version) {
			Settings::PLUGIN_VERSION_1 => [
				[
					'js/friendlycaptchav1.min.js',
					'async' => true,
					'defer' => true,
					'nomodule' => true,
				],
				[
					'js/friendlycaptchav1.module.min.js',
					'async' => true,
					'defer' => true,
					'type' => 'module',
				],
			],
			Settings::PLUGIN_VERSION_2 => [
				[
					'js/friendlycaptchav2.min.js',
					'async' => true,
					'defer' => true,
					'nomodule' => true,
				],
				[
					'js/friendlycaptchav2.module.min.js',
					'async' => true,
					'defer' => true,
					'type' => 'module',
				],
			],
			default => [],
		};

		return $bundle;
	}
}
