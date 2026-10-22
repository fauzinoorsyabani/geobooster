# 2. Atomic Credential Pool Allocation with Pessimistic Locking

Date: 2026-10-22  
Status: Accepted

## Context & Decision

ZiuLabs sells finite digital assets (ChatGPT Plus accounts, JetBrains license keys, Canva team invite tokens) that must be delivered to customers instantly upon QRIS payment settlement.

When multiple customers checkout popular SKUs simultaneously, naive queries (`SELECT ... WHERE status = 'available'`) create race conditions where the same credential is assigned to two orders (double-allocation).

We decided to implement a dedicated **Credential Pool engine** using **Pessimistic Row-Level Locking (`SELECT ... FOR UPDATE`)** inside an atomic database transaction:

1. **Guaranteed uniqueness**: The row is locked until the transaction commits, ensuring no other concurrent worker or request can read or assign the same credential.
2. **Immediate stock reconciliation**: When available pool count for an SKU reaches zero, the engine automatically flags `skus.stock_status = 'sold_out'` to prevent checkout attempts.
3. **Graceful degradation**: If a pool is exhausted mid-flight, the order transitions to an operator-assisted queue without dropping the payment or losing state.
