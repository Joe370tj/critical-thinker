# SkillBridge AI Release Checklist

## Before pilot
- [ ] `npm install` completes successfully in the target environment.
- [ ] `npm test` passes.
- [ ] App starts and `/api/health` returns `status: ok`.
- [ ] Create client, worker, and admin test accounts.
- [ ] Test complete journey: post job → apply → select worker → start → complete → review.
- [ ] Verify an unselected applicant cannot access private job messages.
- [ ] Verify users cannot change another user's profile or jobs.
- [ ] Verify admin endpoints reject non-admin accounts.
- [ ] Test duplicate applications and duplicate reviews.
- [ ] Test mobile layout on real devices.
- [ ] Test restart persistence and backup restore.

## Before public launch
- [ ] Production `JWT_SECRET` is strong and private; no setup key remains configured.
- [ ] HTTPS and persistent database storage are configured.
- [ ] Privacy and Terms placeholders are completed and reviewed.
- [ ] Support, complaints, moderation, and incident response are ready.
- [ ] Dependency/security audit and manual security review are complete.
- [ ] Hosting monitoring, backups, restore tests, and rollback plan are in place.
- [ ] Native app builds and store compliance work are completed if app-store distribution is required.
