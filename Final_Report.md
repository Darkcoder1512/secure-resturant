# Restaurant Management Web Application — Final Report

## Title Page
- **Project**: Restaurant Management Web Application
- **Group Members**:
  - Sahil Anis — 22K-4689
  - Hadi Ali — 22K-4693
  - Ali Ahmed — 22K-2681
- **Section**: [Your section]
- **Instructor**: Sir Abuzar Zafar
- **Date**: December 6, 2025

## Chapter 1: Introduction
A PHP/MySQL app running on XAMPP (Windows) that supports menu management, table booking, order placement and status, discount and bill calculation, payments, basic employee data, and price variation tracking. The project emphasizes resilience (self-healing DB tables) and introduces security improvements guided by tooling (OWASP ZAP, Snyk).

## Chapter 2: Business Logic
- **Menu**: `food` joined with `categories`, seeded items (biryani, nihari, burger, pizza).
- **Table Booking**: `table_bookings` auto-assigns `T1–T20`, capacity ≤6, prevents double-booking.
- **Orders**: Inserts into `Order_items`, ensures `order_details`, sets `order_status='Not served'`.
- **Discount**: Compares actual vs approximate prep time; applies a flat discount when SLA missed.
- **Bill**: Sums amounts, adds tax (18%), subtracts discount; inserts into `bill`.
- **Payment**: Records `Payement` with `Payement_mode` and `Net_amount`.
- **Employee**: Shows basic fields from `employee`.
- **Price Variation**: Logs changes in `price_increase` with before/after values; supports simulation.

## Chapter 3: Normal Flow
1. **Login** via `login.php` (now with prepared statements).
2. **Setup** via `setup_menu.php` and `setup_user.php`.
3. **Operations**: View Menu → Book Table → Place Order → Track Status → Calculate Discount → Calculate Bill → Pay Bill.
4. **Resilience**: `CREATE TABLE IF NOT EXISTS` guards prevent fatal errors.

## Chapter 4: Risk Assessment and Threat Modeling
- **Assets**: Orders, bills, payments, employee info, pricing.
- **Actors**: External attackers, insiders.
- **Surfaces**: Web forms, DB queries, session/auth.
- **Risks**:
  - High: SQL Injection (legacy queries), now mitigated for login.
  - Medium: Hardcoded DB credentials, CSRF, missing security headers.
  - Low: Server banner disclosure.
- **Mitigations**:
  - Prepared statements throughout (login completed, extend to all data-modifying endpoints).
  - Least-privilege DB user; avoid `root`.
  - CSRF tokens for forms; input validation; output encoding; security headers.
- **Residual Risk**: Reduced for login; remaining endpoints to be refactored.

## Chapter 5: Secure Coding & Techniques
- **Implemented**: Prepared statements in login; primary keys for upserts; output encoding in messages; defensive table creation.
- **Planned**: Extend prepared statements, CSRF, headers, least-privilege DB user, session hardening.

## Chapter 6: Testing & Results
- **Tools**: OWASP ZAP automated scan; Snyk Code analysis.
- **Findings**: SQL injection risks, hardcoded credentials, missing headers, absent anti-CSRF tokens.
- **Functional Tests**: Booking prevents duplicates; orders set `Not served`; discount and bill compute correctly; payments recorded; price changes logged.
- **Next Steps**: Refactor remaining queries; add CSRF and headers; create restricted DB user.

## Conclusion
The app fulfills core restaurant workflows with robustness. Security tooling guided improvements: prepared statements in login and structural changes reduce critical risks. Completing remaining mitigations (least-privilege DB user, CSRF, headers, full prepared statements) will achieve a secure lab-grade deployment.

## References
- OWASP Cheat Sheets: SQL Injection Prevention, CSRF Prevention, Security Headers, Authentication.
- OWASP ZAP User Guide.
- Snyk Code: PHP security patterns, CWE references.
- PHP Manual: `mysqli` prepared statements.
- MySQL Docs: constraints and indexing.
