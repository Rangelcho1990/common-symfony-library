# Linked work-item workflow

Apply this workflow to every linked work item relevant to this repository:
tasks, issues, bugs, epics, stories, feature requests, improvements, and other
work-item types, regardless of tracker or hosting platform. Do not require a
GitHub issue URL or the exact wording `Fix the following issue` to trigger it.

Read every work-item link supplied by the user, including its description,
acceptance criteria, relevant discussion, and linked requirements. For epics or
parent tasks, inspect the child items and dependencies needed to understand the
requested scope. Track each requested item separately so none is silently
skipped, and identify overlapping requirements before making changes.

Carry out the user's requested action and complete all four jobs below as
applicable before reporting completion. An implementation request includes
implementing and validating the requested changes; a review, investigation, or
planning request does not by itself authorize implementing every referenced
item. Respect explicit scope limits, and distinguish requested work from
background references and dependencies. If a link is inaccessible or essential
requirements are missing, report the specific blocker, request the missing
information, and continue independent work without inventing requirements.

Inspect the current branch, index, and working tree first. Preserve unrelated
changes. Compare against the locally available base/default branch, and include
relevant staged, unstaged, and untracked files in the review.

## 1. Possible bugs

- Review the affected code paths and the proposed changes for regressions, edge
  cases, error handling, compatibility, and shared-state or lifecycle problems.
- Fix defects within the authorized scope and add meaningful regression coverage.
  Record unrelated findings without expanding the implementation unnecessarily.
- Run relevant PHPUnit tests, PHPStan, and formatting checks for changed PHP
  files. Validate the Symfony container when service wiring changes.
- Report actual results and any checks that could not run. Do not describe
  static inspection as runtime validation.

## 2. MySQL changes

- Check whether the requested work affects Doctrine entities, mappings, repositories,
  queries, migrations, indexes, constraints, or database configuration.
- If database changes are required, implement and review the needed migration
  and query changes. Check upgrade/rollback behavior, existing-data preservation,
  MySQL compatibility, and the repository's supported PostgreSQL behavior where
  affected. Do not assume already-applied migrations will run again.
- Run relevant database tests only against the isolated test database, following
  `tests/Support/TestDatabaseGuard.php` and the documented `_csl_test` setup.
  Do not run schema-destructive tests against development or production data.
- If persistence is unaffected, record `Not required` with a short reason. Do not
  create migrations merely to satisfy this job. If database validation is blocked,
  record the blocker and distinguish reviewed SQL from executed SQL.

## 3. Cursor rules

- Read and apply `.cursor/rules/Code review and Improve.mdc` for every work item,
  even though its Cursor metadata says `alwaysApply: false`. Read any other
  applicable rules present under `.cursor/rules/`.
- Save or update `.docs/Branch_<branch-number>_CodeReviewImprove.md` using the
  branch/task number and fallback policy defined in that rule. Do not increment
  historical review counters.
- Use the rule's required sections and order. Include bug-review and MySQL
  outcomes in the review. Resolve in-scope findings before finalizing it.

## 4. Release Notes changes

- After the review, update `Release Notes.md` under `Unreleased` for noteworthy
  behavior, API, compatibility, configuration, or testing changes.
- Describe what changed, why, compatibility/migration impact, and actual test
  coverage. Update an existing entry for the work item instead of duplicating it.
- Preserve historical entries and generated counts. Record `Updated` or
  `Not required`, with a reason, in the review's `Release Notes` section.

## Documentation updates

- Review `ARCHITECTURE.md` and `README.md` for every task. Update them when changes affect architecture, execution order, public behavior, configuration, setup, usage, or development workflows.
- Keep subscriber priorities and lifecycle documentation consistent with the implementation. Record `Updated` or `Not required`, with a short reason, for each document in the task review or completion report.

## Completion report

Briefly report each requested work item's status and the outcome of all four
jobs. Shared reviews and validation may cover multiple items, but make their
coverage clear. Link the review and release notes when changed, summarize
validation, and identify unresolved findings or blockers. Do not silently omit
an item or a job that was not applicable; record `Not required` with a reason.

Present the completion result in canvas, including the requested work-item status,
changes, validation results, documentation updates, and any unresolved findings or
blockers. Keep the result concise and link the relevant repository documents.
