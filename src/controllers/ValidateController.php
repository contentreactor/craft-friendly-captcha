<?php

namespace contentreactor\craftfriendlycaptcha\controllers;

use contentreactor\craftfriendlycaptcha\Plugin;
use craft\web\Controller;
use craft\web\Response;

class ValidateController extends Controller
{
	protected array|int|bool $allowAnonymous = true;

	public function actionIndex(): Response
	{
		$valid = Plugin::getInstance()->getValidate()->validateRequest();

		return $this->asJson([
			'success' => $valid,
		]);
	}

}