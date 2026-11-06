# Hazelchat for Kirby

Adds your [Hazelchat](https://hazelchat.co/?utm_source=kirby&utm_medium=plugin&utm_campaign=readme) AI chat agent to every page of a Kirby site from one pasted installation code. The agent answers visitors from the pages you approved, shows its sources and passes on their enquiries.

- Kirby 4 and 5, PHP 8.1 or later (Kirby 5 needs PHP 8.2).
- Needs a Hazelchat account. The Free plan covers one website and stays free.
- Hosted service: visitors' browsers load the chat from `app.hazelchat.co`, and Hazelchat processes their questions and any enquiry details they send ([privacy notice](https://hazelchat.co/privacy/), [terms](https://hazelchat.co/terms/)). The plugin itself sends nothing.

## Install

1. In Hazelchat, add your website and copy its installation code ([create a free account](https://app.hazelchat.co/signup?utm_source=kirby&utm_medium=plugin&utm_campaign=readme-signup)).
2. Install the plugin: `composer require hazelchat/kirby-hazelchat`, or download the latest release and put its folder into `site/plugins/hazelchat`.
3. In the Panel, open **Hazelchat** in the menu, paste the installation code and save. Or set it in `site/config/config.php`:

   ```php
   return [
       'hazelchat.kirby.code' => '<your installation code or its key>',
   ];
   ```
4. Back in Hazelchat, click **Publish and switch on**. Hazelchat finds the chat on your live page and verifies the website.

## What it does

- Every HTML page gets one tag just before `</body>`:
  `<script src="https://app.hazelchat.co/widget.js" id="hazelchat-kirby" defer data-agent="<key>"></script>`
- The installation code may be the pasted code or just its 64-character key. Only a valid key is saved or printed; anything else, such as an API key, is refused.
- **Wait for my consent manager** (off; `'hazelchat.kirby.consent' => true` in config.php) adds `data-require-consent="true"`: the chat starts only once your consent manager allows it. Only the chat waits: browsers still download `widget.js` from `app.hazelchat.co` straight away, so the visitor's IP address reaches Hazelchat before they agree. This is by design. If your site must prevent that too, use a consent tool that can block the script itself. See [Visitor consent](https://hazelchat.co/integrations/kirby/#consent).
- The script tag carries the id `hazelchat-kirby`. When Hazelchat checks your website, it reads this id to record that the chat was installed with this plugin, and it counts the plugin's installations that way.
- The Panel never loads the chat, and JSON, RSS and other content representations stay as they are. Pages from Kirby's page cache carry the tag; saving in the dialog clears that cache.
- The **Hazelchat** dialog is for administrators only. It saves the key and the switch in your site content (`hazelchat_key`, `hazelchat_consent`). A value in config.php takes precedence.
- Panel texts in English and German.
- No requests from Kirby to Hazelchat, no database, no tracking.

## Remove

Clear the installation code in the **Hazelchat** dialog (or remove the option from config.php), or uninstall the plugin (`composer remove hazelchat/kirby-hazelchat`, or delete `site/plugins/hazelchat`). Your Hazelchat account and plan are not affected.

## Deutsch

Hazelchat fügt Ihren KI-Chat-Agenten mit einem einzigen Installationscode in jede Seite Ihrer Kirby-Site ein (Kirby 4 und 5). Installieren Sie das Plugin (`composer require hazelchat/kirby-hazelchat` oder den Ordner nach `site/plugins/hazelchat` kopieren), öffnen Sie im Panel **Hazelchat**, fügen Sie den Code aus Hazelchat ein und speichern Sie. Die Browser Ihrer Besucher laden den Chat von `app.hazelchat.co`; deren Fragen und Anfragedaten verarbeitet Hazelchat. Wartet der Chat auf Ihren Consent-Manager, lädt der Browser `widget.js` trotzdem sofort, und die IP-Adresse des Besuchers erreicht Hazelchat schon vor der Einwilligung; nur der Chat wartet. Über die ID `hazelchat-kirby` im Skript-Tag zählt Hazelchat die Installationen dieses Plugins. Sie brauchen ein Hazelchat-Konto; der Free-Tarif gilt für eine Website und bleibt kostenlos.

## Licence

MIT. Kirby is a trademark of its makers; Hazelchat is not affiliated with or endorsed by them. A live Kirby site needs its own Kirby licence.
