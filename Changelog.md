# Eloquent History Changelog

## Unreleased
##### 2025-XX-YY

- Added the `action_name` field and the `actionName()` getter method to history events
- Added PHP 8.5 support
- Dropped PHP 8.1 support
- Changed the minimum Laravel version requirements to v10.48, v11.46.2 and v12.38 respectively

## 1.7.0
##### 2025-03-03

- Added Laravel 12 support

## 1.6.1
##### 2024-12-13

- Fixed MySQL/Postgres truncation errors when the user-agent string is longer than 255 characters by truncating the string to 255 chars before inserting to the DB

## 1.6.0
##### 2024-11-28

- Added Job Tracking and logging feature
- Added job-tracking-related events

## 1.5.0
##### 2024-11-01

- Added the `action` Operation type
- Added the `details` field to the model history table/model
- Added the `logActionSuccess()` and `logActionFailure()` factory methods to the `History` class

## 1.4.0
##### 2024-03-12

- Added Laravel 11 support
- Changed the minimum PostgreSQL version to v12, due to an incompatibility detected with Laravel 11.0.0 and PostgreSQL 11

## 1.3.0
##### 2023-11-17

- Added the `oldIsUndefined()` & `newIsUndefined()` methods to the `Change` class

## 1.2.0
##### 2023-11-16

- Changed the behavior of logRecentUpdate: if there are no changes, then it won't write an empty event in the history
- Changed the behavior of logUpdate: it saves "now" as happened_at instead of the model's updated_at field
- Added the `isEmpty()` and `isNotEmpty()` methods to the `Diff` class

## 1.1.1
##### 2023-11-16

- Fixed N+1 query by eager loading the user relationship when retrieving history

## 1.1.0
##### 2023-11-16

- Added the `ModelHistoryEvent::user(): BelongsTo` relationship
- Added the `konekt.history.user_model` configuration to manually set the user class of the BelongsTo relationship 

## 1.0.1
##### 2023-11-16

- Fixed possible `BadMethodCallException` by changing minimum Laravel version to v10.10.1 due to `Arr::mapWithKeys()` being present from that version

## 1.0.0
##### 2023-11-16

- Initial Release
- Log model creation, update, delete, and retrieval events
- Log model diffs
- Optional comments to events
- Comment-only history events
- Optionally define included/excluded fields on a per-model basis
- Detect the scenery of the event: Via (web, queue, cli) and depending on the context:
  - IP, URL, user agent,
  - artisan command name
  - queue / job
