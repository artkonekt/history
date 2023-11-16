# Eloquent History Changelog

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
