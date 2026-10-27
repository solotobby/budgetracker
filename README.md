# 💰 Budget Tracker PWA

A fast, mobile-first, installable Progressive Web App (PWA) for personal and family budget tracking.

## ✨ Features

- **📱 Installable PWA**: Install on iPhone, Android, or Mac/PC desktop like a native app.
- **🔐 Multi-User Authentication**: Sign in or create accounts using a **Username/Email + Password**.
- **👨‍👩‍👧 Friends & Family Support**: Anyone can create their own private account on the same link. Each account's budgets and expenses are isolated and private.
- **☁️ Cloud Sync (Firebase)**: Sync across phone, tablet, and computer in real-time.
- **⚡ Dual Engine**: Works immediately in offline/local mode, and seamlessly upgrades to Firebase Cloud Sync when you paste your free Firebase config.
- **💱 Multi-currency (GBP + NGN)**: Track expenses in British Pounds or Nigerian Naira. Set your own exchange rate and optionally convert monthly totals to GBP. Fully offline.
- **🔁 Recurring expenses**: Mark items to repeat every month. They appear automatically in new months (works with multi-currency).
- **🏦 Basic loans**: Add a loan with a monthly payment. The payment appears automatically each month and reduces the remaining balance when marked paid.
- **🚗 PCP / Balloon**: Loans can be Personal, PCP or Lease. Track a balloon payment and an optional savings pot toward it.
- **💳 Overdraft**: Track an overdraft limit and amount used. Shows available balance.
- **📋 Debt Overview**: One place to see all loans and overdraft together, with monthly and remaining totals.
- **💡 Suggestions**: Simple local tips based on your loans, overdraft, and subscriptions.
- **📊 Comprehensive Financial Tools**:
  - Monthly navigation (January – December).
  - Monthly income setup & editing.
  - Expense categorization (Housing, Food, Transport, Utilities, Subscriptions, etc.).
  - Mark expenses as "Paid" or "Pending" with checkboxes.
  - Dynamic budget usage progress bar with alert colors.
  - Remaining balance tracker with positive/negative color highlights.
  - One-click JSON data backup and export.

---

## 🚀 Running Locally

To test locally on your computer:

```bash
# Start a local server
python3 -m http.server 8080
```

Open [http://localhost:8080](http://localhost:8080) in your browser.

---

## 📲 How to Install as an App (PWA)

### On iPhone (Safari):
1. Open the website in **Safari**.
2. Tap the **Share** button (box with an arrow pointing up).
3. Scroll down and tap **Add to Home Screen**.
4. The Budget icon will appear on your home screen and open full-screen!

### On Android (Chrome):
1. Open the website in **Chrome**.
2. Tap the "Install" banner at the top, or tap the three-dot menu and select **Add to Home Screen** / **Install App**.

### On Mac / Windows (Chrome, Safari, Edge):
1. Click the **Install** button in the browser address bar.
2. The app will be added to your Mac Applications / Dock or Windows taskbar.

---

## ☁️ Setting Up Free Cloud Sync (Firebase)

To sync your data across devices and let friends and family access their accounts from their own phones:

1. Create a free project at [Firebase Console](https://console.firebase.google.com/).
2. Under **Build > Authentication**, enable **Email/Password**.
3. Under **Build > Firestore Database**, click **Create Database** (Start in Test mode or configure user security rules).
4. Go to **Project Settings > General**, register a **Web App** (`</>`), and copy the `firebaseConfig` object.
5. In the Budget Tracker app, tap your **Profile > Cloud Sync Settings**, paste your config JSON, and click **Save & Connect**!

---

## 🌐 Free Hosting Options to Share with Friends & Family

### Option A: GitHub Pages (100% Free, 2 minutes)
1. Push this repository to GitHub.
2. In your repo on GitHub, go to **Settings > Pages**.
3. Under **Branch**, select `main` and root `/`, then click **Save**.
4. GitHub provides a live HTTPS link (e.g., `https://yourusername.github.io/budgetracker`) that you can send to anyone!

### Option B: Vercel (100% Free, 1 click)
1. Run `npx vercel` in this folder, or import your GitHub repository into [Vercel](https://vercel.com).
2. It gives you a fast global URL with automatic HTTPS.

---

## Development Notes

### Stage 1 – Multi-currency foundation (October 2026)
- Added optional `currency` field to expenses (`GBP` or `NGN`).
- User-controlled GBP ↔ NGN exchange rate stored in localStorage.
- Toggle to convert summary totals to GBP (income remains in GBP).
- New Currency Settings modal accessible from the user menu.
- All changes are fully offline and backward-compatible with existing data.

### Stage 2 – Recurring expenses (October 2026)
- Added “Repeat every month” option when creating an expense.
- Recurring items are automatically generated when a new month is loaded (using a stable recurringId to avoid duplicates).
- Small repeat icon shown next to recurring expenses in the list.
- Fully compatible with Stage 1 multi-currency.
- All data remains local.

### Stage 3 – Basic loans (October 2026)
- Added Loans section with name, monthly payment, currency, and remaining balance.
- Loan payment is created as a recurring expense (Stage 2).
- Marking the payment as paid reduces the remaining balance.
- Fully local and compatible with multi-currency.

### Stage 4 – PCP / Balloon + Savings Pot (October 2026)
- Loans now have a type (Personal, PCP, Lease).
- PCP and Lease loans can include a balloon amount.
- Simple balloon savings pot with an “Add” action.
- Progress shown on the loan card.
- Fully local and compatible with previous stages.

### Stage 5 – Overdraft (October 2026)
- Added simple overdraft tracker (limit, amount used, currency).
- Displayed in its own section below Loans.
- Fully local and compatible with previous stages.

### Stage 6 – Debt Overview (October 2026)
- Added a Debt Overview section that combines loans and overdraft.
- Shows monthly debt payments and total remaining debt.
- Reads existing data only — no new storage.
- Fully local and compatible with previous stages.

### Stage 7 – Suggestions (October 2026)
- Added a Suggestions section with simple local rules.
- Tips for balloon pot funding, high overdraft usage, and mixed-currency subscriptions.
- Runs entirely on data already stored — no external services.
- Fully local and compatible with previous stages.
