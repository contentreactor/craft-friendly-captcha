<?php

namespace contentreactor\craftfriendlycaptcha\assets;

use craft\web\AssetBundle as BaseAssetBundle;

class CssBundle extends BaseAssetBundle
{
	public function init(): void
	{
		$this->sourcePath = '@cfc/assets';

		$this->css = [
			'css/friendlycaptcha.css',
		];

		parent::init();
	}
}
