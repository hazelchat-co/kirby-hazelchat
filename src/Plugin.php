<?php

declare(strict_types=1);

/*
 * This file is part of Hazelchat for Kirby.
 *
 * (C) 2026 Hazelchat. MIT License; see LICENSE.md.
 */

namespace Hazelchat\Kirby;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Toolkit\I18n;

/**
 * Where the key comes from, the page hook and the Panel dialog.
 *
 * The key is read from config.php (`hazelchat.kirby.code`) when it is set
 * there, otherwise from the site's content, where the Panel dialog saves it.
 * Only a valid key is ever saved or printed. Nothing is sent from Kirby to
 * Hazelchat; visitors' browsers load the chat from app.hazelchat.co.
 */
final class Plugin
{
    /** The site content fields the Panel dialog writes. */
    public const FIELD_KEY = 'hazelchat_key';
    public const FIELD_CONSENT = 'hazelchat_consent';

    /** @return array{key: ?string, consent: bool, fromConfig: bool} */
    public static function settings(?App $kirby = null): array
    {
        $kirby ??= App::instance();
        $code = $kirby->option('hazelchat.kirby.code');
        $consent = $kirby->option('hazelchat.kirby.consent');
        $fromConfig = $code !== null && $code !== '';

        if (!$fromConfig || $consent === null) {
            // In a multi-language site the site content of the default
            // language holds the setting, whatever language a page is in.
            $content = $kirby->site()->content($kirby->defaultLanguage()?->code());
            $code = $fromConfig ? $code : $content->get(self::FIELD_KEY)->value();
            $consent ??= $content->get(self::FIELD_CONSENT)->value() === 'true';
        }

        return [
            'key' => Key::fromInput($code),
            'consent' => $consent === true,
            'fromConfig' => $fromConfig,
        ];
    }

    /**
     * The page.render:after hook: one tag on every HTML page. JSON, RSS
     * and other representations are left alone; the Panel never runs it.
     * The result is part of the page cache like the rest of the page.
     */
    public static function render(string $contentType, string $html): string
    {
        if ($contentType !== 'html') {
            return $html;
        }
        $settings = self::settings();

        return Key::insert($html, Key::tag($settings['key'], $settings['consent']));
    }

    /** The Panel area: a menu entry for administrators that opens the dialog. */
    public static function area(): array
    {
        return [
            'label' => 'hazelchat.kirby.menu',
            'icon' => 'chat',
            'menu' => fn () => App::instance()->user()?->isAdmin() === true,
            'dialog' => 'hazelchat',
            // Kirby binds these to its route object, so `self` would not
            // be this class here.
            'dialogs' => [
                'hazelchat' => [
                    'load' => fn () => Plugin::dialog(),
                    'submit' => fn () => Plugin::save(),
                ],
            ],
        ];
    }

    public static function dialog(): array
    {
        self::requireAdmin();
        $settings = self::settings();
        $t = fn (string $key) => I18n::translate('hazelchat.kirby.' . $key);

        $fields = [];
        if ($settings['key'] === null && !$settings['fromConfig']) {
            $fields['start'] = ['type' => 'info', 'text' => $t('start')];
        }
        $fields['code'] = [
            'type' => 'textarea',
            'label' => $t('code'),
            'help' => $settings['fromConfig'] ? $t('code.config') : $t('code.help'),
            'buttons' => false,
            'font' => 'monospace',
            'size' => 'small',
            'counter' => false,
            'disabled' => $settings['fromConfig'],
        ];
        $fields['consent'] = [
            'type' => 'toggle',
            'label' => $t('consent'),
            'help' => $t('consent.help'),
        ];

        return [
            'component' => 'k-form-dialog',
            'props' => [
                'fields' => $fields,
                'submitButton' => I18n::translate('save'),
                'value' => [
                    'code' => $settings['fromConfig'] ? '' : ($settings['key'] ?? ''),
                    'consent' => $settings['consent'],
                ],
            ],
        ];
    }

    /** Saves only the key (never the pasted text) and the consent switch. */
    public static function save(): array
    {
        self::requireAdmin();
        $kirby = App::instance();
        $request = $kirby->request();
        $settings = self::settings();
        $update = [self::FIELD_CONSENT => \in_array($request->get('consent'), [true, 'true', 1, '1'], true) ? 'true' : 'false'];

        if (!$settings['fromConfig']) {
            $input = $request->get('code', '');
            $key = Key::fromInput($input);
            if ($key === null && \is_string($input) && trim($input) !== '') {
                throw new InvalidArgumentException([
                    'key' => 'hazelchat.kirby.invalid',
                    'fallback' => 'This is not a Hazelchat installation code.',
                    'httpCode' => 400,
                ]);
            }
            $update[self::FIELD_KEY] = $key;
        }

        $kirby->site()->update($update, $kirby->defaultLanguage()?->code());
        // Pages rendered before the change must not keep the old tag.
        $kirby->cache('pages')->flush();

        $saved = \array_key_exists(self::FIELD_KEY, $update) ? $update[self::FIELD_KEY] : $settings['key'];

        return ['message' => I18n::translate($saved === null ? 'hazelchat.kirby.removed' : 'hazelchat.kirby.saved')];
    }

    private static function requireAdmin(): void
    {
        if (App::instance()->user()?->isAdmin() !== true) {
            throw new PermissionException([
                'key' => 'hazelchat.kirby.admin',
                'fallback' => 'Only administrators can change the Hazelchat settings.',
            ]);
        }
    }
}
