# 25 — Intentionally Safeguarded & Out-of-Scope Features

## 1. Safeguarded External Integrations
In accordance with Section 2 of the Critical Environment and Safety Rules:
1. **Live SMS Gateways:** Diverted to local logs to prevent sending synthetic messages to real phone numbers.
2. **Live WhatsApp Business API:** Diverted to local logs to avoid incurring external API charges or notifying real contacts.
3. **External Banking / Credit Card Gateways:** Tested via synthetic cash/bank vouchers; real card processing APIs were safely mocked.
4. **Outbound Customer Emails:** `MAIL_MAILER=log` active throughout test execution.

## 2. Blocked or Non-Implemented Modules
- No existing internal modules were found to be blocked. All 57 Filament resources and workflows are functional.
