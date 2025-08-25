<?php

namespace contentreactor\craftfriendlycaptcha;

use Craft;
use contentreactor\craftfriendlycaptcha\models\Settings;
use yii\base\Event;
use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\web\twig\variables\CraftVariable;
use contentreactor\craftfriendlycaptcha\variables\FriendlyCaptchaVariable;
use craft\elements\User;
use craft\events\ModelEvent;
use contentreactor\craftfriendlycaptcha\services\ValidateService as ValidateService;
use contentreactor\craftfriendlycaptcha\traits\Services;

/**
 * Craft Friendly Captcha plugin
 *
 * @method static Plugin getInstance()
 * @method Settings getSettings()
 * @author ContentReactor <noreply@contentreactor.com>
 * @copyright ContentReactor
 * @license MIT
 *
 * @property ValidateService $validate
 */
class Plugin extends BasePlugin
{
	use Services;

	/**
	 * Static property that is an instance of this plugin class so that it can be accessed via
	 * FriendlyCaptcha::$plugin
	 *
	 * @var Plugin
	 */
	public static Plugin $plugin;

	public string $schemaVersion = '1.0.0';
	public bool $hasCpSettings = true;

	public static function config(): array
	{
		return [
			'components' => [
				'validateService' => ValidateService::class,
			],
		];
	}

	public function init(): void
	{
		parent::init();
		self::$plugin = $this;
		$this->attachEventHandlers();

		Craft::$app->onInit(function () {
			$this->_setVariable();
			$this->_handleUserRegistration();
			Craft::setAlias('@cfc', __DIR__);
		});
	}

	protected function createSettingsModel(): ?Model
	{
		return Craft::createObject(Settings::class);
	}

	protected function settingsHtml(): ?string
	{
		return Craft::$app->view->renderTemplate('craft-friendly-captcha/_settings.twig', [
			'plugin' => $this,
			'settings' => $this->getSettings(),
		]);
	}

	private function attachEventHandlers(): void
	{
		// Register event handlers here ...
		// (see https://craftcms.com/docs/5.x/extend/events.html to get started)
	}

	private function _setVariable(): void
	{
		Event::on(
			CraftVariable::class,
			CraftVariable::EVENT_INIT,
			function (Event $event) {
				/** @var CraftVariable $variable */
				$variable = $event->sender;
				$variable->set('friendlyCaptcha', FriendlyCaptchaVariable::class);
			}
		);
	}

	private function _handleUserRegistration()
	{
		if ($this->settings->validateUsersRegistration && Craft::$app->getRequest()->getIsSiteRequest()) {
			Event::on(User::class, User::EVENT_BEFORE_VALIDATE, function (ModelEvent $event) {
				/** @var User $user */
				$user = $event->sender;

				// Only new users
				if ($user->id === null && $user->uid === null && $user->contentId === null) {
					if (!$this->validate->validateRequest()) {
						$user->addError('friendlyCaptcha', Craft::t('friendly-captcha', 'Please verify you are human.'));
						$event->isValid = false;
					}
				}
			});
		}
	}
}
