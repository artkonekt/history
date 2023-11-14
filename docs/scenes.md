# Scenes

When tracing back what happened in the past, the location and the running context can be relevant.

In order to have that information on record, the `scene` and the `via` fields get populated as well.

## Via

The via field can be one of the followings:
 
- `web`
- `cli`
- `queue`

## Scene

The `scene` holds information about the location where the change has happened. It contains:

- The **URL**, when the event happened via `web`,
- the **artisan command name** when the event happened via `cli`, and
- the **Job Name or Class** when the event happened via `queue`.

The via/scene information is detected and recorded automatically when creating history events the `History` class.

> To customize Scenes see the [Customization](customization.md) section of this document.

---

**Next**: [Diffs &raquo;](diffs.md)
