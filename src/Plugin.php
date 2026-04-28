<?php
declare(strict_types=1);

namespace contentreactor\craftfriendlycaptcha;

use contentreactor\craftfriendlycaptcha\models\Settings;
use contentreactor\craftfriendlycaptcha\services\ValidateService;
use contentreactor\craftfriendlycaptcha\traits\Services;
use contentreactor\craftfriendlycaptcha\variables\FriendlyCaptchaVariable;
use Craft;
use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\elements\User;
use craft\events\ModelEvent;
use craft\web\twig\variables\CraftVariable;
use yii\base\Event;

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

	public string $schemaVersion = '1.0.0';
	public bool $hasCpSettings = true;

	public function init(): void
	{
		parent::init();

		Craft::$app->onInit(function () {
			$this->attachEventHandlers();
			Craft::setAlias('@cfc', __DIR__);
		});
	}

	private function attachEventHandlers(): void
	{
		Event::on(
			CraftVariable::class,
			CraftVariable::EVENT_INIT,
			static function (Event $event): void {
				if (!$event->sender instanceof CraftVariable) return;

				$event->sender->set('friendlyCaptcha', FriendlyCaptchaVariable::class);
			},
		);

		if (Craft::$app->getRequest()->getIsSiteRequest()) {
			if ($this->getSettings()->validateUsersRegistration) {
				Event::on(
					User::class,
					User::EVENT_BEFORE_VALIDATE,
					function (ModelEvent $event): void {
						if (!$event->sender instanceof User) return;

						// Only new users
						if ($event->isNew) {
							if (!$this->getValidate()->validateRequest()) {
								$event->sender->addError('friendlyCaptcha', Craft::t('craft-friendly-captcha', 'Please verify you are human.'));
								$event->isValid = false;
							}
						}
					}
				);
			}
		}
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
}
