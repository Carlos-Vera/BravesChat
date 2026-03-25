=== BravesChat ===
Contributors: carlosvera
Tags: chat, ai, n8n, chatbot, webhook
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 2.4.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect your WordPress site to your N8N AI agent. Professional chat widget with GDPR support, business hours, and full customization.

== Description ==

**BravesChat** is the bridge between your WordPress site and your **N8N** workflows: connect any AI agent you have built with your visitors, without extra code and in minutes.

= Designed for the N8N community =

* **N8N-ready webhook:** Point BravesChat to your workflow URL and start receiving messages instantly. Supports an authentication token in the header (`X-N8N-Auth`) to protect your endpoints.
* **Complete payload on every message:** Each request includes the current message and the user's unique `sessionId` — everything your N8N nodes need to maintain conversation context.
* **Markdown responses:** Your agent's messages are rendered with rich formatting — bold, lists, links, and code — with no extra configuration.
* **Conversation history with CSV export:** Browse all sessions from the WordPress dashboard and export them to your CRM, spreadsheet, or database with one click.

= Production-ready =

* **Configurable business hours:** Define when the chat is active and show a custom message outside those hours — ideal if your agent depends on a human in the loop.
* **Built-in GDPR compliance:** Consent banner that blocks the chat until the user accepts. Montserrat font loaded locally, no external requests.
* **Full brand customization:** Colors, texts, position, skin, and display mode (floating widget or full screen) — all adjustable without touching code.
* **Reinforced security:** The N8N authentication token travels only on the server — it is never exposed in the page HTML.
* **WooCommerce compatible:** Works in WooCommerce stores without conflicts, enabling conversational assistance throughout the purchase process.

= Session identification with Fingerprinting =

BravesChat generates a unique `sessionId` per visitor based on browser characteristics (SHA-256 hash), without storing personal data. This allows N8N to maintain conversation context even if the user reloads the page.

== Installation ==

1. Upload the `braves-chat` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin from the **Plugins** menu in WordPress.
3. Go to **BravesChat → Settings** and enter your N8N webhook URL.
4. (Optional) Customize colors, texts, and position under **Appearance**.
5. (Optional) Configure the consent banner under **GDPR**.

== Frequently Asked Questions ==

= Do I need an N8N account to use BravesChat? =

Yes. BravesChat acts as the chat widget on your WordPress, but the intelligence and responses are managed by your own N8N workflow. You can use N8N Cloud or your self-hosted instance.

= Does it work with any AI agent in N8N? =

Yes. BravesChat sends the message and the conversation `sessionId` to the webhook URL you configure. The agent can be connected to OpenAI, Claude, Gemini, Ollama, or any model your workflow supports — BravesChat imposes no restrictions.

= What data is sent to the webhook on each message? =

Each request includes: the user's message (`chatInput`) and the unique session identifier (`sessionId`). Conversation history is managed by N8N via the `sessionId`.

= Is the conversation history saved in the WordPress database? =

No. The history shown in the admin panel is fetched directly from your N8N data source (e.g., PostgreSQL) via a separate webhook that you configure.

= Can I hide the chat on certain pages? =

Yes. In the Settings section you can specify pages where the widget should not appear.

= Is it compatible with WooCommerce? =

Yes, BravesChat is compatible with WooCommerce and does not generate conflicts with the checkout process or store styles.

= Does the plugin comply with GDPR? =

Yes. You can enable a consent banner that blocks the chat until the user accepts. User fingerprinting does not collect personal data. The Montserrat font is loaded locally, with no requests to Google Fonts.

= Can I use BravesChat without N8N? =

Technically yes: the webhook can point to any HTTP endpoint that returns JSON with the `output` field. However, the plugin is optimized and documented for N8N workflows.

= Is the N8N authentication token secure? =

Yes. The token travels only on the server — it is never exposed in the page HTML or JavaScript. The frontend sends messages to the WordPress AJAX endpoint, which acts as a proxy and adds the token before contacting N8N.

== Screenshots ==

1. Floating widget on the frontend — Braves skin with avatar and custom header.
2. Administration panel — Dashboard view with quick access to all sections.
3. Settings — N8N webhook configuration, texts and chat behavior.
4. Appearance — color, position, skin, and widget icon customization.
5. Conversation history — session viewer with CSV export.

== Changelog ==

= 2.4.0 =
* ADDED: Mobile fullscreen mode — on devices up to 480px the chat opens as a full-screen overlay with its own header, back/close buttons, and iOS safe-area support.
* IMPROVED: WooCommerce compatibility — z-index adjusted so WooCommerce cart and checkout elements always render on top of the chat widget.
* IMPROVED: Logo now rendered as a standard img tag instead of inline SVG — compatible with strict Content Security Policy configurations.
* IMPROVED: Admin scripts moved from PHP templates to wp_add_inline_script — resolves Plugin Check (PCP) warnings about inline scripts in templates.
* IMPROVED: Menu icon SVG sanitized before encoding as data URI to prevent rendering issues in some browsers.
* IMPROVED: Style version added to wp_register_style for reliable cache busting on plugin updates.
* FIXED: Daily verse selection now uses gmdate instead of date for correct UTC-based rotation.
* FIXED: External service disclosure added to API.Bible integration for WordPress.org compliance.

= 2.3.8 =
* ADDED: Un versículo de la Biblia (NVI) aparece cada día en el encabezado del panel. Cambia solo — sin configurar nada.

= 2.3.7 =
* ADDED: Agent Name field in Appearance — label your agent to identify conversations in History.
* IMPROVED: Status notices (configuration warnings, save confirmations) moved to the header bar — cleaner page layout across all admin sections.
* IMPROVED: Sidebar navigation labels updated — "Schedules" → "Availability", "GDPR" → "Privacy", "History" → "Conversations".
* IMPROVED: Version badge in the header highlights when you are on the About page.
* IMPROVED: Display mode and skin option labels rewritten for clarity.
* IMPROVED: Changelog in the About page redesigned as a two-column timeline layout.

= 2.3.5 =
* FIXED: Image upload button in Appearance now correctly opens the WordPress Media Library.

= 2.3.4 =
* IMPROVED: Chat bubble is now smaller on mobile devices — default skin shrinks to 48×48px, Braves skin switches to a compact avatar + button layout.

= 2.3.3 =
* FIXED: Text domain updated to `braveschat` across all files to match the WordPress.org assigned slug. Resolves all Plugin Check (PCP) text domain errors.

= 2.3.2 =
* FIXED: Plugin Check text domain mismatch — the distributed ZIP now uses the correct plugin slug (`braves-chat`) so the text domain validates correctly on WordPress.org.

= 2.3.1 =
* IMPROVED: Input field stays active while the bot is responding — users can type and interrupt at any time.

= 2.3.0 =
* ADDED: N8N authentication token now travels server-side only — never exposed in the browser.
* ADDED: Three authentication methods for N8N: custom header, Basic Auth, or none.
* IMPROVED: Simplified frontend JavaScript by removing streaming/NDJSON logic — all N8N connection complexity is now handled server-side.
* IMPROVED: Plugin images converted to PNG for better browser and WordPress.org compatibility.
* IMPROVED: License updated to GPL-2.0-or-later, aligned with WordPress.org requirements.
* FIXED: Removed ZIP export detection class that caused false positives.

= 2.2.3 =
* ADDED: "View details" link in the plugins list with full plugin information.
* IMPROVED: Rich text editor for GDPR messages and out-of-hours messages.

= 2.2.1 =
* FIXED: Notices from other plugins no longer appear inside the BravesChat panel.

= 2.2.0 =
* ADDED: Full conversation history viewer with per-session modal.
* ADDED: History export to CSV with all relevant fields.
* IMPROVED: Conversations ordered from most recent to oldest.

= 2.1.2 =
* IMPROVED: CSS isolation system to prevent conflicts with themes.

= 2.1.1 =
* IMPROVED: Incremental real-time Markdown rendering.

= 2.1.0 =
* ADDED: Configurable typing speed slider.
* ADDED: HTML/Markdown support in the GDPR banner message.
* ADDED: Montserrat loaded locally (GDPR compliance).

= 2.0.0 =
* MAJOR: Complete system restructuring with new BravesChat namespace.
* ADDED: Maximize button, textarea auto-growth, minimized state.

= 1.1.1 =
* ADDED: Cookie system with fingerprinting for session identification.

= 1.0.0 =
* Initial plugin release.

== External services ==

= API.Bible (scripture.api.bible) =

This plugin connects to the API.Bible service, provided by the American Bible Society, to display a daily Bible verse in the WordPress admin dashboard header.

**What data is sent:** Only a verse reference identifier (e.g. "JHN.3.16") selected by the day of the year. No user data, visitor data, IP addresses, or site-specific information is ever transmitted.

**When:** Once per day, on the first admin page load after the 24-hour cache expires. The result is stored as a WordPress transient and reused for the rest of the day. Front-end visitors are never affected.

**Why:** To show an inspirational verse to the site administrator in the plugin's admin header.

* Service provider: American Bible Society
* Terms of Service: https://scripture.api.bible/admin/terms-of-service
* Privacy Policy: https://www.americanbible.org/privacy-policy

= N8N Webhook (user-configured) =

This plugin sends chat messages to an N8N webhook URL configured by the site administrator.

**What data is sent:** The visitor's chat message, conversation history, an anonymous session identifier (fingerprint), and the current page URL.

**When:** On every message sent by a visitor through the chat widget, but only if the administrator has configured a webhook URL.

**Why:** To forward the conversation to the administrator's N8N workflow for AI processing.

The webhook URL, destination server, and all data processing are fully controlled by the site administrator. No data is sent to any Braves-operated server.

== Upgrade Notice ==

= 2.4.0 =
Mobile fullscreen chat experience, WooCommerce z-index fix, and several Plugin Check compliance improvements. Recommended update for all users.

= 2.3.8 =
Versículo diario NVI en el header del panel. Se actualiza solo cada día.

= 2.3.7 =
Admin panel polish: notices moved to the header, cleaner navigation labels, and a new Agent Name field to organize your conversations.

= 2.3.5 =
Bug fix: the image upload button in Appearance now works correctly.

= 2.3.4 =
Mobile UX improvement: the chat bubble is now smaller and less intrusive on phones. No configuration needed.

= 2.3.3 =
Resolves all Plugin Check text domain errors. Required before WordPress.org review.

= 2.3.2 =
Fixes a text domain validation error on WordPress.org. Recommended update before submitting to the plugin directory.

= 2.3.0 =
Security update: N8N token is now handled server-side only. Includes three authentication methods and a simplified frontend.

= 2.2.3 =
Administration improvements: visual editor for GDPR and out-of-hours messages, and plugin details accessible from the plugins list.
