---
name: feedback-autofix-restart
description: When Biome's lint:<edition> autofixes a file mid-guard, re-run guard fresh rather than trusting the first pass; don't try to isolate the autofix diff from the feature's own uncommitted changes.
metadata:
  type: feedback
---

When `guard:<edition>` runs `lint:<edition>` and Biome reports "Fixed N file(s)" (visible via its own output, e.g.
`Checked 38 files in 58ms. Fixed 1 file.`), treat that as a fix per the protocol and restart from Step 1 — even if
`validate` passed cleanly just before. Confirmed this works: a second `guard:<edition>` run right after came back
"No fixes applied", proving the tree was actually clean.

Don't bother trying to diff out exactly what Biome changed when the same files already have unrelated uncommitted
feature changes (e.g. a new import line the feature itself added, mixed with Biome's own formatting tweaks on the
same file) — `git diff` on such a file conflates both and isn't worth untangling. Just trust the re-run's "no fixes
applied" as the actual signal of cleanliness, not manual diff inspection.

**Why:** the agent's own instructions already mandate the restart-on-any-fix rule; this note just confirms in practice
that a second immediate run is the fast, reliable way to prove it settled, without wasting time reverse-engineering
Biome's exact edit from a mixed diff.

**How to apply:** any time `lint:<edition>`'s own output says it fixed something, immediately re-run `guard:<edition>`
before moving to `build:<edition>`. Only proceed to build once a run reports zero fixes.
