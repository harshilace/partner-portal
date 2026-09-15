# AI Coding-Agent Rules

These rules are established from **Section 26 of the Partner Portal Master Project Documentation v1.0** and must be strictly followed by all AI coding agents working on this repository:

1. **Read PRD and architecture before coding.**
2. **Never invent business requirements.**
3. **Mark unresolved decisions as `NEEDS BUSINESS CONFIRMATION`.**
4. **Follow the approved database design.**
5. **Keep controllers thin.** Controllers should validate, authorize, and delegate. Complex business logic belongs in Actions / Domain services.
6. **Enforce authorization server-side.** Frontend restrictions are not security boundaries.
7. **Do not trust frontend IDs.** Always verify ownership and tenant boundaries against the authenticated user context to prevent IDOR vulnerabilities.
8. **Preserve historical records.** Do not cascade-delete core business history. Prefer `ON DELETE RESTRICT` for historical/business relationships.
9. **Use transactions for multi-step business operations.** Multi-step state changes must be wrapped in atomic database transactions.
10. **Write tests for new functionality.** Ensure comprehensive test coverage for all features, policies, and actions.
11. **Do not modify unrelated code.** Maintain isolation and focus on the assigned phase/feature.
12. **Do not introduce Redis without explicit approval.** MySQL remains the source of truth; Redis is excluded until explicitly approved.
13. **Do not choose a payment gateway without confirmation.** Use the `PaymentGateway` abstraction interface.
14. **Do not implement Phase 2 features in MVP.** Features such as the Customer Portal, Commission Management, and commission calculation/approval workflows belong strictly to Phase 2.
