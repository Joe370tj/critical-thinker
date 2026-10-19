require('dotenv').config();
const express = require('express');
const path = require('path');
const fs = require('fs');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const Database = require('better-sqlite3');
const rateLimit = require('express-rate-limit');

const app = express();
app.disable('x-powered-by');
app.use(express.json({ limit: '1mb' }));
app.use(express.static(path.join(__dirname, '..', 'public')));
const PORT = Number(process.env.PORT || 3000);
const JWT_SECRET = process.env.JWT_SECRET || 'development-only-change-this-secret-before-deploying';
if (process.env.NODE_ENV === 'production' && (!process.env.JWT_SECRET || JWT_SECRET.length < 32)) {
  console.error('Production requires a JWT_SECRET of at least 32 characters.'); process.exit(1);
}
const db = new Database(process.env.DB_PATH || path.join(__dirname, 'skillbridge.db'));
db.pragma('journal_mode = WAL');
db.pragma('foreign_keys = ON');
db.exec(`
CREATE TABLE IF NOT EXISTS users (
 id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, email TEXT NOT NULL UNIQUE,
 password_hash TEXT NOT NULL, role TEXT NOT NULL CHECK(role IN ('client','worker','admin')),
 area TEXT DEFAULT '', bio TEXT DEFAULT '', skills TEXT DEFAULT '', availability TEXT DEFAULT 'Flexible',
 experience TEXT DEFAULT '', status TEXT NOT NULL DEFAULT 'active', created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS jobs (
 id INTEGER PRIMARY KEY AUTOINCREMENT, client_id INTEGER NOT NULL REFERENCES users(id),
 title TEXT NOT NULL, category TEXT NOT NULL, description TEXT NOT NULL, area TEXT NOT NULL,
 scheduled_at TEXT DEFAULT '', budget_min REAL, budget_max REAL,
 status TEXT NOT NULL DEFAULT 'Posted', created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS applications (
 id INTEGER PRIMARY KEY AUTOINCREMENT, job_id INTEGER NOT NULL REFERENCES jobs(id),
 worker_id INTEGER NOT NULL REFERENCES users(id), message TEXT DEFAULT '', status TEXT NOT NULL DEFAULT 'Applied',
 created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, UNIQUE(job_id, worker_id)
);
CREATE TABLE IF NOT EXISTS messages (
 id INTEGER PRIMARY KEY AUTOINCREMENT, job_id INTEGER NOT NULL REFERENCES jobs(id),
 sender_id INTEGER NOT NULL REFERENCES users(id), message_text TEXT NOT NULL,
 created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS reviews (
 id INTEGER PRIMARY KEY AUTOINCREMENT, job_id INTEGER NOT NULL REFERENCES jobs(id),
 reviewer_id INTEGER NOT NULL REFERENCES users(id), reviewee_id INTEGER NOT NULL REFERENCES users(id),
 rating INTEGER NOT NULL CHECK(rating BETWEEN 1 AND 5), comment TEXT DEFAULT '',
 created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, UNIQUE(job_id, reviewer_id)
);
CREATE TABLE IF NOT EXISTS reports (
 id INTEGER PRIMARY KEY AUTOINCREMENT, reporter_id INTEGER NOT NULL REFERENCES users(id),
 target_type TEXT NOT NULL, target_id INTEGER NOT NULL, reason TEXT NOT NULL,
 status TEXT NOT NULL DEFAULT 'Open', created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS audit_log (
 id INTEGER PRIMARY KEY AUTOINCREMENT, admin_id INTEGER REFERENCES users(id), action TEXT NOT NULL,
 target_type TEXT NOT NULL, target_id INTEGER, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
`);

const safeUser = (u) => ({ id: u.id, name: u.name, email: u.email, role: u.role, area: u.area || '', bio: u.bio || '', skills: u.skills || '', availability: u.availability || 'Flexible', experience: u.experience || '', status: u.status });
function auth(req, res, next) {
  const h = req.headers.authorization || '';
  if (!h.startsWith('Bearer ')) return res.status(401).json({ error: 'Please sign in to continue.' });
  try {
    const payload = jwt.verify(h.slice(7), JWT_SECRET);
    const user = db.prepare('SELECT * FROM users WHERE id=? AND status=?').get(payload.id, 'active');
    if (!user) return res.status(401).json({ error: 'Account not found or inactive.' });
    req.user = user; next();
  } catch { return res.status(401).json({ error: 'Your session has expired. Please sign in again.' }); }
}
function role(...roles) { return (req,res,next) => roles.includes(req.user.role) ? next() : res.status(403).json({error:'You do not have permission to do that.'}); }
function canSeeJob(user, job) {
  if (!job) return false;
  if (user.role === 'admin' || job.client_id === user.id) return true;
  return !!db.prepare("SELECT 1 FROM applications WHERE job_id=? AND worker_id=? AND status='Selected'").get(job.id, user.id);
}
const loginLimiter = rateLimit({ windowMs: 15*60*1000, limit: 30, standardHeaders: true, legacyHeaders: false, message: {error:'Too many attempts. Please try again later.'} });

app.get('/api/health', (req,res) => res.json({status:'ok', app:'SkillBridge AI', time:new Date().toISOString()}));
app.post('/api/auth/register', loginLimiter, async (req,res) => {
  try {
    const {name,email,password,role: userRole,area='',bio='',skills='',availability='Flexible',experience=''} = req.body || {};
    if (!name?.trim() || !email?.trim() || !password) return res.status(400).json({error:'Name, email, and password are required.'});
    if (String(name).trim().length > 80 || String(email).length > 180) return res.status(400).json({error:'Please shorten the name or email.'});
    if (!/^\S+@\S+\.\S+$/.test(String(email))) return res.status(400).json({error:'Enter a valid email address.'});
    if (String(password).length < 8) return res.status(400).json({error:'Password must contain at least 8 characters.'});
    if (!['client','worker'].includes(userRole)) return res.status(400).json({error:'Choose client or worker account type.'});
    const hash = await bcrypt.hash(String(password), 12);
    const info = db.prepare('INSERT INTO users(name,email,password_hash,role,area,bio,skills,availability,experience) VALUES(?,?,?,?,?,?,?,?,?)')
      .run(String(name).trim(),String(email).trim().toLowerCase(),hash,userRole,String(area).slice(0,120),String(bio).slice(0,800),String(skills).slice(0,500),String(availability).slice(0,120),String(experience).slice(0,300));
    const user = db.prepare('SELECT * FROM users WHERE id=?').get(info.lastInsertRowid);
    const token = jwt.sign({id:user.id},JWT_SECRET,{expiresIn:'12h'});
    res.status(201).json({token,user:safeUser(user)});
  } catch(e) { if (String(e.code)==='SQLITE_CONSTRAINT_UNIQUE') return res.status(409).json({error:'An account with this email already exists.'}); console.error(e); res.status(500).json({error:'Could not create account.'}); }
});
app.post('/api/auth/login', loginLimiter, async (req,res) => {
  const email = String(req.body?.email || '').trim().toLowerCase(); const password = String(req.body?.password || '');
  const user = db.prepare('SELECT * FROM users WHERE email=?').get(email);
  if (!user || !(await bcrypt.compare(password,user.password_hash)) || user.status !== 'active') return res.status(401).json({error:'Email or password is incorrect, or the account is inactive.'});
  const token = jwt.sign({id:user.id},JWT_SECRET,{expiresIn:'12h'}); res.json({token,user:safeUser(user)});
});
app.get('/api/me',auth,(req,res)=>res.json({user:safeUser(req.user)}));
app.patch('/api/me',auth,(req,res)=>{
  const fields=['name','area','bio','skills','availability','experience']; const updates=[]; const values=[];
  for(const f of fields) if(req.body[f] !== undefined){ updates.push(`${f}=?`); values.push(String(req.body[f]).slice(0, f==='bio'?800:500)); }
  if(!updates.length) return res.json({user:safeUser(req.user)});
  values.push(req.user.id); db.prepare(`UPDATE users SET ${updates.join(',')} WHERE id=?`).run(...values);
  res.json({user:safeUser(db.prepare('SELECT * FROM users WHERE id=?').get(req.user.id))});
});

app.get('/api/workers', (req,res)=>{
 const {q='',area=''}=req.query; let sql=`SELECT u.id,u.name,u.area,u.bio,u.skills,u.availability,u.experience, COALESCE(AVG(r.rating),0) rating, COUNT(r.id) review_count FROM users u LEFT JOIN reviews r ON r.reviewee_id=u.id WHERE u.role='worker' AND u.status='active'`;
 const args=[]; if(q){sql+=' AND (u.skills LIKE ? OR u.bio LIKE ?)';args.push(`%${q}%`,`%${q}%`)} if(area){sql+=' AND u.area LIKE ?';args.push(`%${area}%`)} sql+=' GROUP BY u.id ORDER BY rating DESC, u.created_at DESC LIMIT 100'; res.json({workers:db.prepare(sql).all(...args)});
});
app.get('/api/jobs',(req,res)=>{
 const {q='',area='',status='Posted'}=req.query; let sql=`SELECT j.*,u.name client_name,(SELECT COUNT(*) FROM applications a WHERE a.job_id=j.id) application_count FROM jobs j JOIN users u ON u.id=j.client_id WHERE 1=1`; const args=[];
 if(status!=='all'){sql+=' AND j.status=?';args.push(status)} if(q){sql+=' AND (j.title LIKE ? OR j.category LIKE ? OR j.description LIKE ?)';args.push(`%${q}%`,`%${q}%`,`%${q}%`)} if(area){sql+=' AND j.area LIKE ?';args.push(`%${area}%`)} sql+=' ORDER BY j.created_at DESC LIMIT 200'; res.json({jobs:db.prepare(sql).all(...args)});
});
app.post('/api/jobs',auth,role('client'),(req,res)=>{
 const {title,category,description,area,scheduled_at='',budget_min=null,budget_max=null}=req.body||{};
 if(!title?.trim()||!category?.trim()||!description?.trim()||!area?.trim()) return res.status(400).json({error:'Title, category, description, and area are required.'});
 const min=budget_min===''||budget_min==null?null:Number(budget_min), max=budget_max===''||budget_max==null?null:Number(budget_max);
 if((min!==null&&(!Number.isFinite(min)||min<0))||(max!==null&&(!Number.isFinite(max)||max<0))||(min!==null&&max!==null&&min>max)) return res.status(400).json({error:'Check the budget range.'});
 const r=db.prepare('INSERT INTO jobs(client_id,title,category,description,area,scheduled_at,budget_min,budget_max) VALUES(?,?,?,?,?,?,?,?)').run(req.user.id,String(title).trim().slice(0,120),String(category).trim().slice(0,80),String(description).trim().slice(0,3000),String(area).trim().slice(0,120),String(scheduled_at).slice(0,80),min,max);
 res.status(201).json({job:db.prepare('SELECT * FROM jobs WHERE id=?').get(r.lastInsertRowid)});
});
app.patch('/api/jobs/:id',auth,role('client','admin'),(req,res)=>{
 const job=db.prepare('SELECT * FROM jobs WHERE id=?').get(Number(req.params.id)); if(!job)return res.status(404).json({error:'Job not found.'}); if(req.user.role!=='admin'&&job.client_id!==req.user.id)return res.status(403).json({error:'You can only edit your own jobs.'});
 const allowed=['title','category','description','area','scheduled_at','budget_min','budget_max']; const updates=[],vals=[];
 for(const f of allowed) if(req.body[f]!==undefined){if(f==='status'&&!['Draft','Posted','Applications Received','Worker Selected','In Progress','Completed','Cancelled','Disputed'].includes(req.body[f]))return res.status(400).json({error:'Invalid job status.'});updates.push(`${f}=?`);vals.push(req.body[f]);}
 if(!updates.length)return res.json({job}); vals.push(job.id);db.prepare(`UPDATE jobs SET ${updates.join(',')} WHERE id=?`).run(...vals);res.json({job:db.prepare('SELECT * FROM jobs WHERE id=?').get(job.id)});
});
app.get('/api/jobs/:id/applications',auth,(req,res)=>{const job=db.prepare('SELECT * FROM jobs WHERE id=?').get(Number(req.params.id));if(!job||!canSeeJob(req.user,job)|| (req.user.role!=='admin'&&job.client_id!==req.user.id))return res.status(403).json({error:'Only the job owner can view applications.'});const rows=db.prepare(`SELECT a.*,u.name worker_name,u.area worker_area,u.skills worker_skills,u.bio worker_bio FROM applications a JOIN users u ON u.id=a.worker_id WHERE a.job_id=? ORDER BY a.created_at DESC`).all(job.id);res.json({applications:rows});});
app.post('/api/jobs/:id/apply',auth,role('worker'),(req,res)=>{const job=db.prepare('SELECT * FROM jobs WHERE id=?').get(Number(req.params.id));if(!job||job.status!=='Posted')return res.status(400).json({error:'This job is not open for applications.'});if(job.client_id===req.user.id)return res.status(400).json({error:'You cannot apply to your own job.'});try{const r=db.prepare('INSERT INTO applications(job_id,worker_id,message) VALUES(?,?,?)').run(job.id,req.user.id,String(req.body?.message||'').slice(0,1000));res.status(201).json({application:db.prepare('SELECT * FROM applications WHERE id=?').get(r.lastInsertRowid)});}catch(e){if(String(e.code)==='SQLITE_CONSTRAINT_UNIQUE')return res.status(409).json({error:'You have already applied for this job.'});throw e;}});
app.post('/api/applications/:id/select',auth,role('client'),(req,res)=>{const a=db.prepare('SELECT a.*,j.client_id,j.status job_status FROM applications a JOIN jobs j ON j.id=a.job_id WHERE a.id=?').get(Number(req.params.id));if(!a)return res.status(404).json({error:'Application not found.'});if(a.client_id!==req.user.id)return res.status(403).json({error:'This is not your job.'});if(!['Posted','Applications Received'].includes(a.job_status))return res.status(400).json({error:'This job cannot select another worker now.'});const tx=db.transaction(()=>{db.prepare("UPDATE applications SET status=CASE WHEN id=? THEN 'Selected' ELSE 'Not selected' END WHERE job_id=?").run(a.id,a.job_id);db.prepare("UPDATE jobs SET status='Worker Selected' WHERE id=?").run(a.job_id)});tx();res.json({ok:true,message:'Worker selected.'});});
app.get('/api/my/jobs',auth,(req,res)=>{if(req.user.role==='client'){return res.json({jobs:db.prepare(`SELECT j.*,(SELECT COUNT(*) FROM applications a WHERE a.job_id=j.id) application_count FROM jobs j WHERE client_id=? ORDER BY created_at DESC`).all(req.user.id)});} if(req.user.role==='worker'){return res.json({jobs:db.prepare(`SELECT j.*,a.status application_status,a.id application_id,u.name client_name FROM applications a JOIN jobs j ON j.id=a.job_id JOIN users u ON u.id=j.client_id WHERE a.worker_id=? ORDER BY a.created_at DESC`).all(req.user.id)});}res.json({jobs:db.prepare('SELECT * FROM jobs ORDER BY created_at DESC LIMIT 200').all()});});
app.patch('/api/jobs/:id/status',auth,(req,res)=>{const job=db.prepare('SELECT * FROM jobs WHERE id=?').get(Number(req.params.id));if(!job)return res.status(404).json({error:'Job not found.'});const next=req.body?.status;const allowed=['In Progress','Completed','Cancelled','Disputed'];if(!allowed.includes(next))return res.status(400).json({error:'Invalid status update.'});const isOwner=job.client_id===req.user.id;const isSelectedWorker=!!db.prepare("SELECT 1 FROM applications WHERE job_id=? AND worker_id=? AND status='Selected'").get(job.id,req.user.id);const involved=isOwner||isSelectedWorker;if(!involved&&req.user.role!=='admin')return res.status(403).json({error:'You are not assigned to this job.'});if(next==='In Progress'&&job.status!=='Worker Selected')return res.status(400).json({error:'A worker must be selected first.'});if(next==='Completed'&&job.status!=='In Progress')return res.status(400).json({error:'Only work in progress can be completed.'});if(next==='In Progress'&&!isOwner&&!isSelectedWorker)return res.status(403).json({error:'Only the client or selected worker can update this job.'});if(next==='Cancelled'&&['Completed','Cancelled','Disputed'].includes(job.status))return res.status(400).json({error:'This job can no longer be cancelled.'});if(next==='Disputed'&&!['Worker Selected','In Progress','Completed'].includes(job.status))return res.status(400).json({error:'A dispute can only be opened after a worker is selected.'});db.prepare('UPDATE jobs SET status=? WHERE id=?').run(next,job.id);res.json({job:db.prepare('SELECT * FROM jobs WHERE id=?').get(job.id)});});
app.get('/api/jobs/:id/messages',auth,(req,res)=>{const job=db.prepare('SELECT * FROM jobs WHERE id=?').get(Number(req.params.id));if(!canSeeJob(req.user,job))return res.status(403).json({error:'You cannot access this conversation.'});res.json({messages:db.prepare('SELECT m.*,u.name sender_name FROM messages m JOIN users u ON u.id=m.sender_id WHERE job_id=? ORDER BY m.created_at ASC LIMIT 500').all(job.id)});});
app.post('/api/jobs/:id/messages',auth,(req,res)=>{const job=db.prepare('SELECT * FROM jobs WHERE id=?').get(Number(req.params.id));const message=String(req.body?.message||'').trim();if(!canSeeJob(req.user,job))return res.status(403).json({error:'You cannot access this conversation.'});if(!message||message.length>2000)return res.status(400).json({error:'Message must be between 1 and 2000 characters.'});const r=db.prepare('INSERT INTO messages(job_id,sender_id,message_text) VALUES(?,?,?)').run(job.id,req.user.id,message);res.status(201).json({message:db.prepare('SELECT m.*,u.name sender_name FROM messages m JOIN users u ON u.id=m.sender_id WHERE m.id=?').get(r.lastInsertRowid)});});
app.post('/api/jobs/:id/reviews',auth,(req,res)=>{const job=db.prepare('SELECT * FROM jobs WHERE id=?').get(Number(req.params.id));if(!job||job.status!=='Completed')return res.status(400).json({error:'Reviews are available after a job is completed.'});const selected=db.prepare("SELECT worker_id FROM applications WHERE job_id=? AND status='Selected'").get(job.id);if(!selected)return res.status(400).json({error:'No selected worker was found.'});let reviewee;if(req.user.id===job.client_id)reviewee=selected.worker_id;else if(req.user.id===selected.worker_id)reviewee=job.client_id;else return res.status(403).json({error:'You are not part of this job.'});const rating=Number(req.body?.rating),comment=String(req.body?.comment||'').slice(0,1000);if(!Number.isInteger(rating)||rating<1||rating>5)return res.status(400).json({error:'Rating must be from 1 to 5.'});try{const r=db.prepare('INSERT INTO reviews(job_id,reviewer_id,reviewee_id,rating,comment) VALUES(?,?,?,?,?)').run(job.id,req.user.id,reviewee,rating,comment);res.status(201).json({review:db.prepare('SELECT * FROM reviews WHERE id=?').get(r.lastInsertRowid)});}catch(e){if(String(e.code)==='SQLITE_CONSTRAINT_UNIQUE')return res.status(409).json({error:'You have already reviewed this job.'});throw e;}});
app.post('/api/reports',auth,(req,res)=>{const {target_type,target_id,reason}=req.body||{};if(!['user','job','message','review'].includes(target_type)||!Number.isInteger(Number(target_id))||!String(reason||'').trim())return res.status(400).json({error:'Select what you are reporting and provide a reason.'});const r=db.prepare('INSERT INTO reports(reporter_id,target_type,target_id,reason) VALUES(?,?,?,?)').run(req.user.id,target_type,Number(target_id),String(reason).slice(0,1000));res.status(201).json({report:db.prepare('SELECT * FROM reports WHERE id=?').get(r.lastInsertRowid)});});
app.get('/api/admin/summary',auth,role('admin'),(req,res)=>res.json({users:db.prepare('SELECT COUNT(*) n FROM users').get().n,jobs:db.prepare('SELECT COUNT(*) n FROM jobs').get().n,openJobs:db.prepare("SELECT COUNT(*) n FROM jobs WHERE status='Posted'").get().n,reports:db.prepare("SELECT COUNT(*) n FROM reports WHERE status='Open'").get().n,usersList:db.prepare('SELECT id,name,email,role,status,created_at FROM users ORDER BY created_at DESC LIMIT 200').all(),reportsList:db.prepare('SELECT r.*,u.name reporter_name FROM reports r JOIN users u ON u.id=r.reporter_id ORDER BY r.created_at DESC LIMIT 200').all()}));
app.patch('/api/admin/users/:id',auth,role('admin'),(req,res)=>{const status=req.body?.status;if(!['active','suspended'].includes(status))return res.status(400).json({error:'Invalid account status.'});if(Number(req.params.id)===req.user.id&&status!=='active')return res.status(400).json({error:'You cannot suspend your own account.'});const info=db.prepare('UPDATE users SET status=? WHERE id=?').run(status,Number(req.params.id));if(!info.changes)return res.status(404).json({error:'User not found.'});db.prepare('INSERT INTO audit_log(admin_id,action,target_type,target_id) VALUES(?,?,?,?)').run(req.user.id,`Set status to ${status}`,'user',Number(req.params.id));res.json({ok:true});});
app.patch('/api/admin/reports/:id',auth,role('admin'),(req,res)=>{const status=req.body?.status;if(!['Open','Under review','Resolved','Dismissed'].includes(status))return res.status(400).json({error:'Invalid report status.'});const info=db.prepare('UPDATE reports SET status=? WHERE id=?').run(status,Number(req.params.id));if(!info.changes)return res.status(404).json({error:'Report not found.'});db.prepare('INSERT INTO audit_log(admin_id,action,target_type,target_id) VALUES(?,?,?,?)').run(req.user.id,`Report status: ${status}`,'report',Number(req.params.id));res.json({ok:true});});
app.get('/api/matches',auth,role('worker'),(req,res)=>{const worker=req.user;const jobs=db.prepare("SELECT j.*,u.name client_name FROM jobs j JOIN users u ON u.id=j.client_id WHERE j.status='Posted' ORDER BY j.created_at DESC LIMIT 100").all();const skills=String(worker.skills||'').toLowerCase().split(/[,;\n]/).map(x=>x.trim()).filter(Boolean);const matches=jobs.map(j=>{const text=(j.category+' '+j.title+' '+j.description).toLowerCase();const hits=skills.filter(s=>s&&text.includes(s));const skillScore=skills.length?Math.min(100,Math.round(hits.length/Math.min(skills.length,3)*100)):25;const areaScore=worker.area&&j.area&&worker.area.toLowerCase()===j.area.toLowerCase()?100:(worker.area&&j.area&& (worker.area.toLowerCase().includes(j.area.toLowerCase())||j.area.toLowerCase().includes(worker.area.toLowerCase()))?70:40);const availabilityScore=worker.availability&&j.scheduled_at&&worker.availability.toLowerCase().includes(j.scheduled_at.toLowerCase())?100:(worker.availability.toLowerCase()==='flexible'?85:60);const review=db.prepare('SELECT AVG(rating) avg,COUNT(*) n FROM reviews WHERE reviewee_id=?').get(worker.id);const reviewScore=review.n?Math.round(review.avg/5*100):60;const score=Math.round(skillScore*.4+areaScore*.25+availabilityScore*.2+reviewScore*.15);const reasons=[];if(hits.length)reasons.push('Skills match: '+hits.slice(0,3).join(', '));if(areaScore>=70)reasons.push('Service area is close');if(availabilityScore>=80)reasons.push('Availability looks suitable');if(!review.n)reasons.push('New worker reviews are not required');return {...j,match_score:score,match_reason:reasons.join('; ')||'Suggested based on the available job details'};}).sort((a,b)=>b.match_score-a.match_score);res.json({matches});});

// Optional one-time admin bootstrap: set ADMIN_SETUP_KEY, then POST /api/setup-admin once.
app.post('/api/setup-admin',loginLimiter,async(req,res)=>{const setupKey=process.env.ADMIN_SETUP_KEY;if(!setupKey||req.body?.setupKey!==setupKey)return res.status(404).json({error:'Not found.'});if(db.prepare("SELECT COUNT(*) n FROM users WHERE role='admin'").get().n>0)return res.status(409).json({error:'An administrator already exists. Disable this setup endpoint.'});const {name,email,password}=req.body;if(!name||!email||!password||String(password).length<12)return res.status(400).json({error:'Name, email, and a password of at least 12 characters are required.'});try{const hash=await bcrypt.hash(String(password),12);const r=db.prepare("INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,'admin')").run(String(name).trim().slice(0,80),String(email).trim().toLowerCase(),hash);res.status(201).json({message:'Admin created. Remove ADMIN_SETUP_KEY and restart the server.',id:r.lastInsertRowid});}catch(e){if(String(e.code)==='SQLITE_CONSTRAINT_UNIQUE')return res.status(409).json({error:'Email already exists.'});throw e;}});
app.use('/api', (req,res,next)=>{if(req.path.startsWith('/')) return res.status(404).json({error:'API endpoint not found.'});next();});
app.use('/api', (err,req,res,next)=>{console.error(err);res.status(500).json({error:'Unexpected server error. Please try again.'});});
app.get('*',(req,res)=>res.sendFile(path.join(__dirname,'..','public','index.html')));
app.listen(PORT,()=>console.log(`SkillBridge AI running at http://localhost:${PORT}`));
