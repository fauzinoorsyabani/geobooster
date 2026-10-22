# 1. Native PHP with Vanilla JS Islands and ClerkJS CDN

Date: 2026-10-22  
Status: Accepted

## Context & Decision

ZiuLabs required an ultra-fast digital commerce storefront with instant page loads, robust relational data modeling (MySQL), secure session authentication, and premium Web3-grade visual aesthetics.

We evaluated heavy full-stack JavaScript architectures (Node.js/Next.js/React SSR) versus modular Native PHP 8.3 with progressive Vanilla JS islands.

We decided to build with **Native PHP 8.3** paired with **Vanilla JS micro-islands** (command palette, 3D tilt, catalog filter) and **ClerkJS loaded via official CDN**:

1. **Zero build step & runtime friction**: Runs directly on standard PHP/Nginx/Laragon environments without complex containerization or Node daemon processes.
2. **Sub-30ms TTFB**: Server-renders catalog and layout instantaneously with zero client-side hydration delays.
3. **Turnkey enterprise auth**: ClerkJS provides Google OAuth, magic links, and secure token lifecycle in browser islands while bridging seamlessly into native PHP sessions and MySQL.
