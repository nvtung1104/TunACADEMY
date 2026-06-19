# TunAcademy — Complete Audit & Refactor Roadmap

> Generated: 2026-05-12  
> Last audited: 2026-05-12 (full re-audit)  
> Skills used: spec-driven-development, planning-and-task-breakdown, incremental-implementation, code-review-and-quality, security-and-hardening, frontend-ui-engineering  
> Stack: Laravel 11 · Vue 3 · Inertia · TailwindCSS · Pinia · MySQL

---

## Progress Legend
- ✅ Complete
- 🔄 In Progress  
- ⬜ Pending
- 🔴 Critical — fix before production

---

## Executive Summary

TunAcademy is a K-12 online learning & exam platform with 3 roles (admin/teacher/student).
The re-audit reflects **17 issues resolved** across Phases 0–5B, with **27 remaining** across
performance, UI refactoring, state management, and UX polish.

**Resolved (since initial audit): 17**  
**Remaining Critical: 1** (localStorage auth token)  
**Remaining High: 6**  
**Remaining Medium: 12**  
**Remaining Low / nice-to-have: 8**

---

## Dependency Graph (implementation order)

```
.env + Config fixes           ✅ DONE
      │
      ▼
Security & Validation         ✅ DONE
      │
      ▼
Backend Architecture          ✅ DONE
      │
      ▼
Database & Query Optimisation ⬜ Partial (N+1 fixed; indices/soft-deletes pending)
      │
      ▼
Frontend Architecture         🔄 Partial (API layer done; stores/router guards pending)
      │
      ▼
UI/UX Uplift                  🔄 In Progress (toast system → next)
      │
      ▼
Component Refactor            ⬜ Pending (large views need splitting)
```

---

## Phase 0 — Pre-flight (Config & Secrets) ✅ COMPLETE

All tasks done:
- ✅ Task 0.1 — Real DB/TURN credentials removed from `.env.example`
- ✅ Task 0.2 — AI model config moved to `config/services.php`
- ✅ Task 0.3 — Boot-time validation for `OPENAI_API_KEY` in `AppServiceProvider`

---

## Phase 1 — Security & Validation ✅ COMPLETE

### 1A — Authentication & Authorization
- ✅ Task 1.1 — Laravel Policies created: `LessonPolicy`, `ExamPolicy`, `AssignmentPolicy`
- ✅ Task 1.2 — Policies registered in `AppServiceProvider`
- ✅ Task 1.3 — Exam scores immutable (server-side only via `ExamGradingService`)
- ✅ Task 1.4 — Route throttling: auth (5,1), public submit (30,1), AI review (10,1)

### 1B — Input Validation
- ✅ Task 1.5 — MIME type validation on file uploads (`LessonController`)
- ✅ Task 1.6 — `SanitizesHtml` trait with `stripAllHtml()` / `stripDangerousTags()` applied to all rich-text Form Requests
- ✅ Task 1.7 — `per_page` capped at 100 in Admin controllers
- ✅ Task 1.8 — Conditional validation: `classroom_id` required when `role === 'student'`

### 1C — Environment & Secret Safety
- ✅ Task 1.9 — `.gitignore` updated: `*.pem`, `*.key`, `.env.local`, `.env.*.local`

### 🔴 NEW: localStorage Auth Token (Critical)
**Discovered in re-audit:** `stores/auth.js` stores Sanctum token in `localStorage`:
```javascript
localStorage.setItem('auth_token', token.value)
```
This is an XSS risk (any injected script can steal the token). Migration to
httpOnly Sanctum session cookies is in Phase 4C below.

---

## Phase 2 — Backend Architecture ✅ COMPLETE

### 2A — Service Layer
- ✅ Task 2.1 — `ExamGradingService` extracted (grading, scoring, answer feedback)
- ✅ Task 2.2 — AI service error handling: returns structured result instead of silent null
- ✅ Task 2.3 — `QuestionService` completed (import, create, reorder, duplicate, saveToBank — unified private helpers)
- ✅ Task 2.4 — `AssignmentGradingService` created (mirrors ExamGradingService)

### 2B — Repository Pattern ⬜ Deferred
Repositories not yet created. Controllers are clean enough post-service extraction.
**When to revisit:** If any controller query grows beyond 5 lines of Eloquent chaining.

### 2C — API Response Standardisation ✅ COMPLETE
- ✅ Task 2.7 — `ApiResponse` trait unified; `PublicController` and all raw `response()->json()` calls migrated

### 2D — Enums & Constants ✅ COMPLETE
- ✅ Task 2.8 — PHP 8.1 backed enums: `ExamType`, `ExamAttemptStatus`, `SubmissionStatus`, `Visibility`, `ShowResult`
- ✅ Enum casts wired to `Exam`, `ExamAttempt`, `AssignmentSubmission` models

---

## Phase 3 — Database & Query Optimisation 🔄 PARTIAL

### Completed
- ✅ N+1 fix: `ExamGradingService` — pre-keyed question collection
- ✅ N+1 fix: `QuestionBankController.usage()` — DISTINCT SQL instead of PHP filter
- ✅ N+1 fix: `ExamController@start` — added `sub_questions` to question response
- ✅ Single-query update in `QuestionService` (DB::raw replacing increment + update)

### Remaining

| # | Issue | Severity |
|---|-------|----------|
| 3.1 | Missing composite indices on FK pairs | MEDIUM |
| 3.2 | No soft deletes on Lesson/Exam/Assignment | MEDIUM |
| 3.3 | LIKE searches on unindexed columns | MEDIUM |
| 3.4 | Exam attempts not paginated in teacher view | HIGH |

#### Tasks
- [ ] **Task 3.1** — Add performance indices migration
  ```php
  // new migration: 2026_05_add_performance_indices
  $table->index(['exam_id', 'student_id']);        // exam_attempts
  $table->index(['assignment_id', 'student_id']);  // assignment_submissions
  $table->index(['classroom_id', 'school_year_id']); // classroom_subjects
  $table->index('created_at');                     // lessons (feed queries)
  ```
  - Verify: `EXPLAIN SELECT` on common queries shows index usage

- [ ] **Task 3.2** — Add `SoftDeletes` to content models
  - Models: `Lesson`, `Exam`, `Assignment`, `ExamQuestion`, `AssignmentQuestion`
  - Migration: Add `deleted_at` column to each table
  - Update queries that should include trashed records (teacher archive view)
  - Verify: Deleting an exam doesn't break historical student attempt records

- [ ] **Task 3.3** — Paginate exam attempts in teacher view
  ```php
  $exam->attempts()
      ->with(['student:id,name,email'])
      ->paginate(30);
  ```
  - Verify: Exam with 500 attempts loads in <500ms

---

## Phase 4 — Frontend Architecture 🔄 PARTIAL

### 4A — API Client Layer ✅ COMPLETE
- ✅ `exams.js` — full teacher + student exam endpoints
- ✅ `lessons.js` — teacher + student lesson endpoints
- ✅ `assignments.js` — teacher + student assignment endpoints
- ✅ `public.js` — public endpoints
- ✅ `auth.js` — login, logout, me
- ⬜ `live.js` — EMPTY STUB (WebRTC/live session endpoints)
- ⬜ `aiChat.js` — EMPTY STUB (AI chat endpoints)

### 4B — State Management (Pinia Stores)

| Store | Status |
|-------|--------|
| `auth.js` | ✅ Implemented (login, logout, fetchUser, role helpers) |
| `notification.js` | ⬜ Empty stub |
| `exam.js` | ⬜ Empty stub (exam session state) |
| `liveRoom.js` | ⬜ Empty stub |
| `aiChat.js` | ⬜ Empty stub |

#### Tasks

- [ ] **Task 4.1** — Create `stores/toast.js` (Phase 5C — next immediate task)
  ```javascript
  export const useToastStore = defineStore('toast', () => {
    const toasts = ref([])
    let nextId = 0
    function add(type, message, duration = 4000) {
      const id = ++nextId
      toasts.value.push({ id, type, message })
      if (duration > 0) setTimeout(() => remove(id), duration)
    }
    function remove(id) { toasts.value = toasts.value.filter(t => t.id !== id) }
    const success = (msg, d) => add('success', msg, d)
    const error   = (msg, d) => add('error', msg, d)
    const warning = (msg, d) => add('warning', msg, d)
    const info    = (msg, d) => add('info', msg, d)
    return { toasts, add, remove, success, error, warning, info }
  })
  ```
  - Replace all `alert()` and `confirm()` calls in teacher views
  - Verify: Successful save shows green toast; API error shows red toast; no browser dialogs remain

- [ ] **Task 4.2** — Implement `stores/exam.js` (exam session persistence)
  ```javascript
  export const useExamStore = defineStore('examSession', () => {
    const currentAttemptId = ref(null)
    const answers = ref({})
    const examId  = ref(null)
    function saveAnswer(questionId, value) { answers.value[questionId] = value }
    function clearSession() { currentAttemptId.value = null; answers.value = {}; examId.value = null }
    return { currentAttemptId, answers, examId, saveAnswer, clearSession }
  }, { persist: { storage: sessionStorage } })
  ```
  - Verify: Student refreshes during exam — answers restored from sessionStorage

- [ ] **Task 4.3** — Implement `stores/notification.js`
  ```javascript
  export const useNotificationStore = defineStore('notifications', () => {
    const notifications = ref([])
    const unreadCount   = computed(() => notifications.value.filter(n => !n.read_at).length)
    async function fetch() { ... }
    async function markRead(id) { ... }
    async function markAllRead() { ... }
    return { notifications, unreadCount, fetch, markRead, markAllRead }
  })
  ```

### 4C — Router & Navigation Guards ⬜ PENDING

- [ ] **Task 4.4** — Async role validation on page reload
  ```javascript
  router.beforeEach(async (to) => {
    const auth = useAuthStore()
    if (to.meta.requiresAuth && !auth.user) {
      try { await auth.fetchUser() } catch { return { name: 'login', query: { redirect: to.fullPath } } }
    }
    if (to.meta.role && auth.role !== to.meta.role) return { name: 'forbidden' }
  })
  ```
  - Add `meta: { requiresAuth: true, role: 'teacher' }` to all role-protected routes
  - Verify: Direct URL access to `/teacher/exams` as student → redirects to forbidden page

- [ ] **Task 4.5** — 🔴 Migrate auth token from localStorage to Sanctum httpOnly cookie
  - Backend: Switch from token-based to session-based Sanctum
  - Frontend: Remove `localStorage.setItem('auth_token')` from `auth.js` store
  - Frontend: Remove `Authorization: Bearer` header from axios config
  - Verify: `document.cookie` does not contain session token; JS cannot read session

### 4D — Composables ✅ PARTIAL

| Composable | Status |
|------------|--------|
| `useExamTimer.js` | ✅ Implemented |
| `useAsync.js` | ✅ Implemented |
| `useAuth.js` | ⬜ Empty stub (bridge to auth store) |
| `useWebRTC.js` | ⬜ Empty stub |
| `useProctoring.js` | ⬜ Empty stub |
| `useLiveRoom.js` | ⬜ Empty stub |
| `useAiChat.js` | ⬜ Empty stub |

---

## Phase 5 — UI/UX Uplift 🔄 IN PROGRESS

### 5A — ExamTimer Component ✅ COMPLETE
- ✅ `ExamTimer.vue` presentational component created
- ✅ `ExamTakeView.vue` wired to `useExamTimer` composable
- ✅ `alert()` replaced with `submitError` ref + slide-up toast

### 5B — ExamManageView Split ✅ COMPLETE
- ✅ `ExamFormModal.vue` extracted (create/edit form + question picker)
- ✅ `ExamShareModal.vue` extracted
- ✅ `ExamResultsModal.vue` extracted
- ✅ `ExamManageView.vue` reduced from 662 → 239 lines

### 5C — Global Toast System ✅ COMPLETE

- [ ] **Task 5.1** — Create `stores/toast.js` (see Task 4.1 above)
- [ ] **Task 5.2** — Create `components/common/AppToast.vue` (toast container + individual toast)
  ```vue
  <!-- Fixed bottom-right toast stack -->
  <div class="fixed bottom-6 right-6 z-[100] flex flex-col gap-2 items-end">
    <TransitionGroup name="toast">
      <AppToastItem v-for="t in toasts" :key="t.id" v-bind="t" @dismiss="remove(t.id)" />
    </TransitionGroup>
  </div>
  ```
- [ ] **Task 5.3** — Replace `alert()` / `confirm()` in all teacher views
  - `ExamManageView.vue`: `publishExam`, `deleteExam` (2 places)
  - `AssignmentManageView.vue`: audit all alert/confirm calls
  - `LessonManageView.vue`: audit all alert/confirm calls
  - `QuestionBankView.vue`: audit all alert/confirm calls
  - Replace `confirm()` deletes with a `ConfirmDialog` modal component
- [ ] **Task 5.4** — Mount `AppToast` in the root `App.vue` or main layout
  - Verify: No `window.alert()` or `window.confirm()` calls remain in production code

### 5D — Implement Empty UI Component Stubs ⬜ PENDING

These stubs block DRY patterns across the app:

| Component | Lines | Priority |
|-----------|-------|----------|
| `AppButton.vue` | 1 (stub) | HIGH |
| `AppInput.vue`/`TextInput.vue` | 1 (stub) | HIGH |
| `AppLoading.vue` | 1 (stub) | MEDIUM |
| `AppTable.vue` | 1 (stub) | MEDIUM |
| `ProgressBar.vue` | 1 (stub) | LOW |
| `RichTextEditor.vue` | 1 (stub) | MEDIUM |
| `FileUpload.vue` | 1 (stub) | MEDIUM |

- [ ] **Task 5.5** — Implement `AppButton.vue`
  ```vue
  <!-- Props: variant (primary|secondary|ghost|danger), size (sm|md|lg), loading, disabled -->
  ```
- [ ] **Task 5.6** — Implement `AppInput.vue`
  ```vue
  <!-- Props: label, error, hint, modelValue; Slots: prefix, suffix -->
  ```
- [ ] **Task 5.7** — Implement `AppLoading.vue` (spinner + skeleton variants)

---

## Phase 6 — Vue Component Refactor ⬜ PENDING

### 6A — Oversized Views

These are the highest-priority splits remaining:

| View | Lines | Critical Sections |
|------|-------|-------------------|
| `PublicExamTakeView.vue` | 708 | Timer, AI review panel, sidebar nav, result screen |
| `QuestionCreateView.vue` | 705 | Type selector, content editor, answer builder, audio upload |
| `PublicAssignmentDoView.vue` | 651 | Duplicates ExamTakeView structure |
| `LiveRoomView.vue` | 619 | Video grid, chat, controls (stubs blocking split) |
| `LiveLobbyView.vue` | 586 | Participant list, settings, room controls |
| `ProfileView.vue` | 539 | Avatar upload, info form, password form |
| `AssignmentDoView.vue` | 534 | Near-duplicate of PublicAssignmentDoView |
| `AssignmentManageView.vue` | 529 | Mirrors ExamManageView — same modal pattern |

#### Tasks

- [x] **Task 6.1** — Refactor `AssignmentManageView.vue` (same pattern as ExamManageView)
  - Extract: `AssignmentFormModal.vue`, `AssignmentShareModal.vue`, `AssignmentResultsModal.vue`
  - Target: view < 200 lines
  - Verify: All existing assignment CRUD operations still work

- [ ] **Task 6.2** — Refactor `QuestionCreateView.vue` (705 lines)
  - Extract: `QuestionContentEditor.vue` (content + type-specific answer builder)
  - Extract: `QuestionAudioUpload.vue` (audio recording/upload for listening questions)
  - The `QuestionTypeSelector.vue` component already exists (159 lines) — wire it in
  - Target: view < 200 lines
  - Verify: All 13 question types still create/save correctly

- [ ] **Task 6.3** — Consolidate duplicate exam/assignment take views
  - `ExamTakeView.vue` (462) and `PublicExamTakeView.vue` (708) share 80% logic
  - Extract shared `useExamSession` composable
  - Use `ExamTimer.vue` in both (already extracted)
  - Create shared `QuestionCard.vue` component for rendering all question types
  - Target: both views < 250 lines
  - Verify: Student exam, public exam, and practice exam all work correctly

- [ ] **Task 6.4** — Implement `QuestionCard.vue` component
  - Renders any question type (multiple_choice, true_false, fill_blank, essay, ordering, matching, listening, writing, calculation, reading, speaking)
  - Accepts: `question`, `answer`, `readonly` props
  - Emits: `update:answer`
  - This removes ~150 lines of duplicated question rendering from ExamTakeView, PublicExamTakeView, AssignmentDoView, PublicAssignmentDoView

### 6B — Accessibility & Responsiveness ⬜ PENDING

- [ ] **Task 6.5** — Add `aria-label` to all icon-only buttons
  - Pattern: `<button aria-label="Xóa câu hỏi">` on trash icons
  - Affects: ExamManageView, AssignmentManageView, QuestionBankView, LessonManageView

- [ ] **Task 6.6** — Focus management in all modals
  - On open: focus first interactive element
  - On close: return focus to trigger button
  - Applies to: AppModal.vue, ExamFormModal.vue, ExamShareModal.vue, ExamResultsModal.vue

- [ ] **Task 6.7** — Mobile responsiveness audit
  - Tables need horizontal scroll or card-stack on ≤640px
  - Exam take view must work on phone (touch-friendly inputs)
  - Test at: 320px, 768px, 1024px, 1440px

---

## Phase 7 — Live Session & AI Features ⬜ FUTURE

These stubs are separate from the core learning/exam platform:

**Empty stubs blocking implementation:**
- `components/live/VideoTile.vue`
- `components/live/VideoGrid.vue`
- `components/live/LiveControls.vue`
- `components/live/BreakoutRoom.vue`
- `components/chat/ChatWidget.vue`
- `components/chat/MessageBubble.vue`
- `components/chat/FlashcardViewer.vue`
- `composables/useWebRTC.js`
- `composables/useLiveRoom.js`
- `composables/useAiChat.js`
- `composables/useProctoring.js`
- `api/live.js`
- `api/aiChat.js`
- `stores/liveRoom.js`
- `stores/aiChat.js`

**Recommendation:** Implement live session as a dedicated Phase 7 sprint after the core learning platform (Phases 0–6) is stable.

---

## Checkpoints

### ✅ Checkpoint 1 — After Phase 0 + 1
- ✅ No credentials in `.env.example`
- ✅ All protected endpoints enforce ownership (Policies)
- ✅ Student cannot inject scores (server-side only)
- ✅ Rate limiting on auth/submit routes
- ✅ File uploads reject non-allowed MIME types
- ✅ HTML sanitized in all rich-text Form Requests

### ✅ Checkpoint 2 — After Phase 2
- ✅ Business logic in services (ExamGradingService, AssignmentGradingService, QuestionService)
- ✅ Consistent `{ success, message, data }` response format
- ✅ Enums replace magic strings (ExamType, ExamAttemptStatus, Visibility, ShowResult)
- ✅ N+1 queries fixed in critical paths

### 🔄 Checkpoint 3 — After Phase 4
- ⬜ Auth token migrated from localStorage to httpOnly cookie
- ⬜ Route guards validate role on direct URL access
- ✅ API calls through typed modules (not direct axios)
- ✅ `useAsync` composable for loading/error patterns

### ⬜ Checkpoint 4 — After Phase 5 + 6
- ⬜ No `alert()` or `confirm()` in production code
- ⬜ Every list view has skeleton + empty + error states
- ⬜ No component > 200 lines (target: all views < 250 lines)
- ⬜ Lighthouse accessibility score ≥ 90
- ⬜ Lighthouse mobile performance score ≥ 85

---

## Risks & Mitigations

| Risk | Impact | Mitigation | Status |
|------|--------|------------|--------|
| localStorage token stolen via XSS | 🔴 CRITICAL | Migrate to httpOnly cookie (Phase 4C) | ⬜ Pending |
| Policy enforcement breaks teacher workflows | MEDIUM | Policy tests written; shadow-mode first | ✅ Mitigated |
| SoftDeletes migration alters production tables | MEDIUM | Run in staging; backup before production | ⬜ Pending |
| Component splits change prop shapes | MEDIUM | Verify with existing views after each split | ✅ Pattern established |
| Exam answer loss on refresh | HIGH | Use sessionStorage-persisted exam store | ⬜ Pending (Task 4.2) |

---

## Remaining Work — Priority Order

### Immediate (this sprint)
1. **Phase 5C** — Global toast system (`stores/toast.js` + `AppToast.vue` + replace all alert/confirm)
2. **Phase 4B** — `stores/exam.js` (answer persistence during exam)
3. **Phase 4B** — `stores/notification.js`

### High Priority
4. **Phase 6A Task 6.1** — `AssignmentManageView.vue` split (same pattern as ExamManageView, already proven)
5. **Phase 6A Task 6.4** — `QuestionCard.vue` shared component (removes ~150 lines from 4 views)
6. **Phase 4C Task 4.5** — 🔴 localStorage → httpOnly cookie migration

### Medium Priority
7. **Phase 3 Task 3.1** — DB indices migration
8. **Phase 6A Task 6.2** — `QuestionCreateView.vue` split
9. **Phase 6A Task 6.3** — Consolidate duplicate take views
10. **Phase 4C Task 4.4** — Router guard async validation

### Lower Priority
11. **Phase 5D** — Implement empty UI component stubs (AppButton, AppInput, AppLoading)
12. **Phase 3 Task 3.2** — SoftDeletes
13. **Phase 6B** — Accessibility & responsiveness
14. **Phase 7** — Live session + AI chat feature stubs

---

## Open Questions (needs human input)

1. **Cookie-based auth**: Does the deployment support httpOnly cookies on the same domain?
2. **Soft deletes**: Should deleted exams still appear in student exam history?
3. **Exam answer persistence**: sessionStorage (per-tab) or localStorage (cross-tab)?
4. **Grade export**: CSV only, or Excel + PDF?
5. **Live session**: Timeline for implementing live video features?
6. **Library views**: 4 empty views — what should they contain?

---

*This roadmap follows incremental-implementation: each phase leaves the system in a working,
deployable state. No phase depends on a future phase. Verify checkpoint before advancing.*
