# Security and Performance Review

This review summarizes practical hardening and optimization opportunities for the current `Database` class implementation.

## High-priority security improvements

1. **Guard SQL identifiers used in helper methods**
   - Methods like `count()`, `exists()`, `insertBatch()`, `upsert()`, and `replace()` interpolate table/column names directly into SQL strings.
   - Prepared statements protect values, **not identifiers**. If table or column names can be influenced by user input, this can become SQL injection.
   - Recommendation:
     - Introduce an identifier allowlist (preferred), or
     - Add strict identifier validation (e.g., `^[A-Za-z_][A-Za-z0-9_]*$` for simple identifiers), plus explicit support for allowed qualified names.

2. **Treat `where` fragments as trusted-only input**
   - `count()` and `exists()` support raw SQL in `$where`.
   - Values are parameterized, which is good, but the clause text itself is still raw SQL.
   - Recommendation:
     - Document this as trusted/developer-only input.
     - Optionally add a higher-level filter builder to avoid ad-hoc SQL fragments.

3. **Reduce sensitive error exposure in production logs**
   - Exceptions currently log raw exception messages, which may include SQL or connection internals.
   - Recommendation:
     - In production, sanitize logs (generic message + error code), and keep full detail only in secure debug environments.

4. **Use least-privilege DB accounts**
   - Enforce DB users with only required permissions per service/runtime role.
   - This reduces blast radius if SQL injection or credential leakage occurs.

## High-impact performance improvements

1. **Lower debug logging overhead on hot paths**
   - `bindParams()` logs every parameter binding, which can be expensive at high throughput.
   - Recommendation:
     - Gate verbose logs behind a debug flag.
     - Keep lightweight aggregate metrics by default (query count, p95 latency).

2. **Query log memory controls when profiling is enabled**
   - Profiling stores all queries in memory (`$query_log`). This can grow unbounded.
   - Recommendation:
     - Add maximum log size (ring buffer) and optional sampling.

3. **Make SQLite pragmas configurable**
   - `PRAGMA synchronous = NORMAL`, `journal_mode = WAL`, and `temp_store = MEMORY` are fast defaults, but workload-dependent.
   - Recommendation:
     - Allow overrides via configuration for durability/performance tuning.

4. **Connection strategy by environment**
   - Persistent connections are configurable and disabled by default (safe baseline).
   - Recommendation:
     - Benchmark with and without persistent mode in your deployment model (FPM workers, long-running workers, CLI jobs) before enabling globally.

## Correctness and portability notes (worth addressing)

1. **PostgreSQL/SQLite upsert semantics**
   - `ON CONFLICT DO UPDATE` typically requires a conflict target or matching unique constraint logic.
   - Recommendation:
     - Add configurable conflict target support for robust cross-driver behavior.

2. **Return type consistency**
   - Methods return a mix of `false`, arrays/objects, counts, and IDs.
   - Recommendation:
     - Standardize return behavior (or provide strict variants) for easier caller handling and fewer edge-case bugs.

## Practical rollout plan

1. Add identifier validation/allowlists for helper methods.
2. Add production-safe logging mode.
3. Add query-log cap when profiling.
4. Add tests for identifier validation failures and allowed paths.
5. Benchmark representative workloads before enabling persistent connections.

## Quick operational checklist

- [ ] No user-controlled table/column names reach helper methods.
- [ ] All user-provided values use bound parameters.
- [ ] Production logs do not expose SQL internals or secrets.
- [ ] DB users follow least privilege.
- [ ] Profiling query log is bounded.
- [ ] Performance tested with realistic load and data size.
