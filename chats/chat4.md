# Agent Web App Interface

_Started 2026-05-04 17:15 UTC_

---

## User

You are a senior UI/UX designer specialized in fintech admin tools.

Using the provided Frontend Specification Document as the single source of truth, design ONLY the Agent Web App UI for Lipa.

Ignore all other actors (Customer, Merchant, Terminal). Focus exclusively on the Agent section.

You must strictly follow and reuse the design already created for the Backoffice (BO). Do not introduce a new design style.

OBJECTIVE

Design an Agent interface that is:

Simple and clean
Professional and trustworthy
Pleasant to use daily
Efficient for operational tasks

This is an operational tool — prioritize usability over visual complexity.

DESIGN PRINCIPLES
Clarity over creativity
Minimal but not plain
Structured and consistent layouts
No unnecessary animations or visual noise
Fast, readable, and easy to navigate Fonts: Bricolage Grotesque + DM Mono

## Assistant

_[tool: list_files]_

Let me explore the existing files to understand the design system before building anything.

_[tool: read_file]_

_[tool: list_files]_

Let me read the spec and all existing components to fully understand the design system.

_[tool: read_file]_

_[tool: list_files]_

Let me read the key component files to extract the exact design vocabulary.

_[tool: read_file]_

_[tool: read_file]_

Now let me read the Dashboard and one more page to fully understand the layout patterns, then I'll start building.

_[tool: read_file]_

_[tool: read_file]_

Now I have a thorough understanding of the design system. Let me plan and build the Agent Web App.

_[tool: update_todos]_

Let me start building all the agent components simultaneously.

_[tool: write_file]_

Now let me build all the component files simultaneously.

_[tool: write_file]_

_[tool: write_file]_

Now let me build the navigation, dashboard, and remaining pages simultaneously.

_[tool: write_file]_

_[tool: write_file]_

_[tool: write_file]_

Now let me build the remaining pages (Statement, Commission, Profile + Limits) and the main HTML entry point.

_[tool: write_file]_

## Assistant

Now let me build the remaining pages (statement, commission, profile/limits) and the main HTML file.

_[tool: write_file]_

_[tool: write_file]_

_[tool: write_file]_

Now let me build the main HTML entry point.

_[tool: write_file]_

Let me check the app loads cleanly.

_[tool: done]_

Clean load. Let me fork the verifier.

_[tool: fork_verifier_agent]_

_[tool: update_todos]_

The Lipa Agent Web App is ready at `agent/Lipa Agent.html`. Here's a summary of what was built:

---

**5 screens** (bottom tab navigation):

| Screen | What's there |
|---|---|
| **Home** | Float balance (with show/hide toggle), today's Cash In / Out / Transactions strip, commission callout, recent transactions feed, agent info card. Low-float warning banner when below threshold. |
| **Transactions** | Grouped by date, filterable by All / Cash In / Cash Out / Failed, full-text search. Tap any row → slide-up detail with amount hero, counterparty, reference, fee & commission earned. |
| **Statement** | Wallet ledger grouped by date, balance-before/after shown per entry. Tap for detailed movement breakdown. |
| **Commission** | Pending payout hero on dark card, today/week/all-time strip, Cash In vs Cash Out breakdown with mini bars, 6-month history bar chart + table. |
| **Profile** | Three tabs — Profile (contact, permissions), Limits (float + daily/weekly/monthly bars for CI & CO), Security (TOTP setup sheet, session info). Sign-out button. |

**Login** uses the correct Agent OTP flow: phone entry → 6-digit OTP input with digit-by-digit focus, resend countdown, paste support.

**Design fidelity**: exact same CSS token system as the BO (`--accent`, `--sidebar-bg`, `--border-color`, etc.), same Bricolage Grotesque + DM Mono fonts, same badge/status system, same card/table/toast patterns — just adapted to a mobile-first layout.

**Tweaks panel** exposes accent colour, header shade, and quick-nav between all screens.

