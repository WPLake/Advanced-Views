---
name: elementor-phpstan-stubs
description: phpstan uses hand-written Elementor class stubs, not the real library - missing constants show up as classConstant.notFound false positives
metadata:
  type: reference
---

`tools/code_quality/stubs/Elementor/*.php` are minimal hand-written stubs (e.g. `Controls_Manager.php` only lists the
constants this project currently references: `TAB_ADVANCED`, `TEXT`, `TEXTAREA`, `SELECT2`, `RAW_HTML`) since the real
`elementor/elementor` package isn't a composer dependency here — it's only present on sites that have Elementor active.

**Why:** phpstan has no visibility into the real Elementor classes, so any new `Controls_Manager::SOME_CONSTANT` (or
other Elementor class member) referenced in plugin code that isn't already in the stub produces a genuine-looking
`classConstant.notFound` phpstan error, even though the code is correct against real Elementor.

**How to apply:** when phpstan flags an undefined Elementor class constant/method that you can confirm is a real,
correctly-named member of the actual Elementor API (check the real constant value/behavior, e.g. via Context7 or the
plugin's own vendored Elementor if locally installed), add it to the relevant stub file under
`tools/code_quality/stubs/Elementor/` rather than treating it as a bug in the plugin code or suppressing the error.
