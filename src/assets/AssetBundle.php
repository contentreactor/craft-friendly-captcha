<?php

namespace contentreactor\craftfriendlycaptcha\assets;

use craft\web\AssetBundle as BaseAssetBundle;

class AssetBundle extends BaseAssetBundle
{
	public function init(): void
	{
		$this->sourcePath = '@cfc/assets/js';

		$this->js = [
			'friendlycaptchav2.min.js',
			'friendlycaptchav2.module.min.js',
			'friendlycaptchav1.min.js',
			'friendlycaptchav1.module.min.js',
		];

		parent::init();
	}
}
