---
name: work-plan
description: "MUST use when user says 'copy plan', 'append plan', 'resume plan',
             'load plan', 'start the plan', 'continue the plan', 'execute plan',
             'run the plan', 'pick up where we left off', or when the AI exits
             plan mode and needs to transfer the plan into execution format. This
             skill manages the full lifecycle of project plans -- from plan output
             to tracked checkbox execution with per-todo commits."
---

# Work -- Plan Execution Skill
*Plan lifecycle management with tracked execution and context recovery*

## Skill Dependencies

This skill cooperates with two companion skills that MUST also be active during execution:

| Skill | Role |
|-------|------|
| **phase-guard** | Enforces phase scope -- prevents out-of-scope edits during execution |
| **arch-guard** | Protects architecture zones -- hard stops before structural changes |

Activate both at the start of every `copy plan`, `append plan`, and `resume plan` command.

## Activation

Three commands, each with its own activation message:

| Command | Activation Message |
|---------|-------------------|
| **Copy Plan** | `"Copying plan to execution format..."` |
| **Append Plan** | `"Appending to existing plan..."` |
| **Resume Plan** | `"Resuming plan execution..."` |

## Context Guard

| Context | Status |
|---------|--------|
| **User says "copy plan", "start the plan"** | ACTIVE -- copy and begin execution |
| **User says "append plan"** | ACTIVE -- append to existing plan |
| **User says "resume plan", "continue the plan"** | ACTIVE -- resume from checkpoint |
| **AI exits plan mode with approved plan** | READY -- suggest "copy plan" to user |
| **After context reset in a project with plan file** | READY -- suggest "resume plan" |
| **No project context** | DORMANT -- no plan action |
| **Personal/casual conversation** | DORMANT -- no plan action |

## Command Dispatch

| Command | What It Does |
|---------|-------------|
| `"copy plan"` | Copy latest plan to `docs/project-plan.md` (fresh start) |
| `"append plan"` | Append latest plan to existing `project-plan.md` (add sections) |
| `"resume plan"` | Resume execution after context reset (pick up from next `[ ]`) |

---

## Copy Plan

### Step 1: Find Latest Plan
- [ ] Scan `~/.claude/plans/` for plan files
- [ ] Sort by modification date, pick most recently modified
- [ ] If no plan files found: ask user to specify a plan file path or enter plan mode first

### Step 2: Transform to Project Plan Format
- [ ] Convert plan steps/items into `- [ ]` checkbox todo items
- [ ] Preserve all architecture diagrams (ASCII, mermaid) from the original plan
- [ ] Add standard instructions header (see `plan-format.md` in plan location folder)
- [ ] Maintain logical phase/section grouping from the original plan
- [ ] No emoji in the plan file -- clean, parseable markdown only

### Step 3: Write Project Plan
- [ ] Check if `docs/` folder exists -- create if needed
- [ ] Write to `docs/project-plan.md` (overwrite if exists)
- [ ] Report: "Plan copied -- [X] todo items ready for execution"

### Step 4: Begin Execution
- [ ] Execute the **Shared Execution Loop** (see below)

---

## Append Plan

### Step 1: Find Latest Plan
- [ ] Same as Copy Plan Step 1

### Step 2: Transform to Project Plan Format
- [ ] Same as Copy Plan Step 2

### Step 3: Check Existing Plan + Line Limit
- [ ] Read current `docs/project-plan.md`
- [ ] Count total lines in the existing file
- [ ] If appending would **NOT** exceed 1000 lines:
  - Append new content with a date separator:
    ```
    ---
    ## Appended: [YYYY-MM-DD]
    ```
  - Report: "Plan extended -- [X] new items added, [Y] total items"
- [ ] If appending **WOULD** exceed 1000 lines:
  - Rename current file to `project-plan-YYYYMMDD.md` (archived)
  - Create fresh `project-plan.md` with the new content only
  - Report: "Previous plan archived as project-plan-[date].md, new plan created"

### Step 4: Begin Execution
- [ ] Execute the **Shared Execution Loop** (see below)

---

## Resume Plan

### Step 1: Read Current Project Plan
- [ ] Read `docs/project-plan.md`
- [ ] If file not found: report "No plan found -- use 'copy plan' to create one"

### Step 2: Parse Progress
- [ ] Count `[x]` items (completed)
- [ ] Count `[ ]` items (pending)
- [ ] Count `[~]` items (blocked)
- [ ] Identify the next pending `[ ]` item as the resumption point
- [ ] Read the Architecture section to restore technical context

### Step 3: Report Status
- [ ] Display progress summary:
  ```
  Plan Status: [X] completed, [Y] pending, [Z] blocked
  Current Phase: [phase name]
  Next Task: [description of next pending item]
  ```
- [ ] Read architecture diagrams to restore technical understanding

### Step 4: Resume Execution
- [ ] Execute the **Shared Execution Loop** from the next pending item

### Recovery Context
After a context reset, the AI loses its working state. `"resume plan"` restores it entirely from the file:
- The plan file **IS** the recovery mechanism
- No user explanation needed -- the AI reads the file and continues

---

## Shared Execution Loop

The core cycle that all three commands use after setup:

```
On first execution:
  0a. Activate arch-guard  -> load architecture journal, surface any pending reviews
  0b. Activate phase-guard -> identify the scope of the first phase

For each [ ] todo item in order:
  1. phase-guard: confirm item is in-scope before touching any file
  2. arch-guard:  if the item touches a protected zone, halt for approval before proceeding
  3. Execute the task (write code, create files, make changes)
  4. pest-testing: run the minimum test set that covers this item; fix before continuing
  5. Auto-Commit triggers -> structured commit for this completed item
  6. Mark the item as [x] in the plan file
  7. Every 5 completed items -> save/update the plan file (checkpoint)
  8. Move to the next [ ] item
  9. If item is [~] (blocked) -> skip and continue to next
 10. On phase boundary: phase-guard exit protocol -> diff check, phase commit, user approval before next phase
```

### Key Behaviors
- **per-task commits** -- each completed todo gets its own commit via Auto-Commit (not batched)
- **checkpoint saves** -- plan file is updated every 5 items to prevent loss
- **skip blocked items** -- `[~]` items are flagged and skipped, not stalled on
- **user can pause** -- if user says "stop" or "pause", halt at the current item
- **arch-guard takes precedence** -- if an arch approval is needed, the execution loop pauses until resolved
- **phase transitions require user confirmation** -- phase-guard blocks auto-advancing to the next phase

---

## Mandatory Rules

1. **Never commit plan files** -- `project-plan*.md` stays local as the AI's working reference. Only code changes are committed.
2. **Preserve diagrams** -- all visual elements (ASCII art, mermaid diagrams) from the original plan must be carried over to the plan file.
3. **No emoji in plan files** -- clean, parseable markdown only.
4. **Line limit enforcement** -- if the plan file exceeds 1000 lines during append, rotate (archive old, create fresh).
5. **Recovery-first design** -- the plan file IS the recovery mechanism after any context reset. Everything needed to resume must be in the file.
6. **Skip blocked items** -- if a task is blocked, mark it `[~]`, flag it to the user, and continue to the next item.
7. **Checkpoint discipline** -- update the plan file every 5 completed items, even mid-execution.

## Edge Cases

| Situation | Behavior |
|-----------|----------|
| **Plan file not found** | Prompt user: "No plan found -- use 'copy plan' to create one" |
| **All items completed** | Report: "Plan complete! All [X] items done." |
| **Blocked task** | Mark `[~]`, flag to user with reason, continue to next item |
| **User says "stop" or "pause"** | Halt at current item, save plan file, report progress |
| **Plan exceeds line limit** | Archive old file as `project-plan-YYYYMMDD.md`, start fresh |
| **No plan source files found** | Ask user to enter plan mode first or specify a file path |
| **Context reset mid-execution** | User says "resume plan" to continue from last checkpoint |
| **Multiple plan files in source** | Pick most recently modified, confirm with user |
