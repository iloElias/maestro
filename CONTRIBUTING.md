# Contribution Guide

Thank you for your interest in contributing to this project! This document outlines our best practices for contributing in a way that is efficient, readable, and welcoming for everyone.

---

## **1. Branch Naming Convention**

Consistent naming helps keep our repository organized and easy to navigate. Please follow this branch naming format:

```
<type>/<short-description>
```

Where `<type>` describes the purpose of the branch, and `<short-description>` is a concise summary using hyphens to separate words (stick to English if possible).

### **Branch Types**

- **feature**: For new features.
  - Example: `feature/login-page`
- **fix**: For bug fixes.
  - Example: `fix/button-alignment`
- **chore**: For maintenance, upgrades, or minor housekeeping.
  - Example: `chore/update-dependencies`
- **refactor**: For code improvements that don’t change user-facing behavior.
  - Example: `refactor/auth-module`
- **test**: For adding or improving tests.
  - Example: `test/api-endpoints`
- **hotfix**: For urgent and critical fixes.
  - Example: `hotfix/payment-error`
- **docs**: For documentation changes.
  - Example: `docs/contributing-guide`

If your branch doesn’t fit any of these types, pick the closest match or reach out in Discussions or Issues for clarification.

---

## **2. Commit Message Guidelines**

Commit messages should be short, descriptive, and follow this format:

```
<type>(scope): short description in English
```

- `<type>`: The category of change (see list below).
- `(scope)`: The affected part of the codebase (optional but recommended).
- `short description`: A succinct summary of the change—imperative mood, present tense.

### **Commit Types**

- **feat**: Add a new feature.
- **fix**: Bug fixes.
- **chore**: Chores such as repo maintenance or build updates.
- **docs**: Documentation-only changes.
- **style**: Code style changes (formatting, white-space, missing semi-colons, etc.).
- **refactor**: Code changes that neither fix bugs nor add features.
- **test**: Adding or updating tests.
- **perf**: Performance improvements.
- **ci**: Continuous integration or pipeline changes.

### **Commit Message Examples**

- `feat(auth): add user login flow`
- `fix(ui): resolve crash on mobile devices`
- `chore(deps): update React to v18`
- `docs(readme): clarify development setup`
- `refactor(api): restructure fetch logic for readability`
- `style(button): standardize padding across variants`

**Tip:** Use English for your commits so the global community can read and understand your contributions.

---

## **3. Contribution Workflow**

1. **Create a Branch**
   - Always create a new branch for your work based on `main` (or the latest active development branch).
   - Example:

     ```bash
     git checkout -b feature/login-page
     ```

2. **Develop and Commit Frequently**
   - Commit your changes early and often.
   - Example:

     ```bash
     git commit -m "feat(auth): add user authentication system"
     ```

3. **Keep Your Branch Up-to-date**
   - Sync with the latest upstream changes to avoid merge conflicts.

     ```bash
     git pull origin main
     ```

4. **Open a Pull Request (PR)**
   - Push your branch to the remote repository.

     ```bash
     git push origin feature/login-page
     ```

   - Open a PR with a clear description:
     - What changes have you made?
     - Why are they necessary?
     - Any context, screenshots, or steps to reproduce/testing appreciated!

5. **Participate in Code Review**
   - Address feedback promptly and engage in discussion if necessary.
   - Be respectful and collaborative in all interactions.

---

## **4. Best Practices**

- **Small, Focused Changes:** Break large changes into smaller, independent PRs where possible. This eases review and increases merge velocity.
- **Clear Rationale:** In your PR or commit messages, explain _why_ you made a given change, not just _what_ changed.
- **Tests:** Add or update tests as needed to cover your changes and help prevent regressions.
- **Consistent Style:** Align with the project's established code style and conventions. Use our formatter and linter (if available).
- **Documentation:** Update relevant documentation when adding features or changing behavior. Well-documented code reduces friction for all contributors.
- **Respectful Communication:** Be considerate of other contributors. Good communication leads to smoother collaboration.

---

By following these guidelines, we aim to ensure a streamlined, inclusive, and enjoyable development process for everyone involved.

Thanks for being part of our community! 🚀
