# ZiuLabs Commerce

Digital product and verified AI tool commerce platform providing instant automated credential fulfillment and verified WhatsApp support.

## Language

**Product**:
A digital application, service, or tool family listed in the catalog (e.g., Google AI Pro, Canva Pro, JetBrains Pack).
_Avoid_: Item, package, tool

**SKU**:
A distinct, purchasable unit of a Product defined by duration, license terms, and fixed integer minor-unit pricing (e.g., CapCut 1 Month, JetBrains Edu 12M).
_Avoid_: Plan, variant, tier

**Order**:
A customer's explicit intent to purchase one or more SKUs, tracked through lifecycle states (`inquiry`, `awaiting_payment`, `paid`, `fulfilling`, `fulfilled`, `cancelled`, `manual_review`).
_Avoid_: Transaction, checkout, purchase

**Credential Pool**:
A dedicated inventory of pre-provisioned digital credentials (email/password accounts, license keys, or invitation links) mapped to SKUs for instant dispatch.
_Avoid_: Key vault, stock list, accounts table

**Fulfillment**:
The automated or operator-guided action of allocating digital credentials to an Order upon confirmed payment settlement.
_Avoid_: Delivery, shipping, completion

**Customer**:
The end-user acquiring a Product SKU, authenticated via Clerk or identified by WhatsApp number and email.
_Avoid_: Buyer, client, account

**Operator**:
An internal team member authenticated into the backoffice control room with role-based permissions (catalog, inventory, orders, audit).
_Avoid_: Admin, staff, employee

**Minor Unit**:
Financial integer representation of currency amounts where 100 units equal Rp1 (IDR) to eliminate floating-point precision loss.
_Avoid_: Cent, decimal price, float
