/* Sanlam Learn — app.js
   Simple client-side prototype storing users & progress in localStorage.
*/

/* ---------- Utility & demo data ---------- */
const COURSES = [
  { id: 'budgeting', title: 'Budgeting & Savings', description: 'Learn to make a budget and save.' },
  { id: 'debt', title: 'Debt Management', description: 'Manage and reduce debt.' },
  { id: 'insurance', title: 'Insurance Basics', description: 'Understand insurance and protection.' },
  { id: 'investment', title: 'Investment Tips', description: 'Intro to investing basics.' },
  { id: 'retirement', title: 'Retirement Planning', description: 'Plan for long-term financial security.' }
];

const DAILY_TIPS = [
  "Start by tracking every expense for one week.",
  "Save at least 10% of any income if you can.",
  "Pay off high-interest debt first.",
  "Automate savings so you don't forget.",
  "Compare insurance quotes at renewal time."
];

function uid() { return 'u'+Math.random().toString(36).slice(2,9); }

/* ---------- Storage helpers ---------- */
const DB = {
  getUsers(){ return JSON.parse(localStorage.getItem('sl_users') || '[]') },
  saveUsers(u){ localStorage.setItem('sl_users', JSON.stringify(u || [])) },
  getState(){ return JSON.parse(localStorage.getItem('sl_state') || '{}') },
  saveState(s){ localStorage.setItem('sl_state', JSON.stringify(s || {})) }
};

/* ---------- Simple auth system (client-side demo) ---------- */
function findUser(username){
  return DB.getUsers().find(u => u.username.toLowerCase() === username.toLowerCase());
}
function registerUser(username, password, email){
  const users = DB.getUsers();
  if(findUser(username)) throw 'Username already exists';
  const user = { id: uid(), username, password, email, completed: [] , created: Date.now() };
  users.push(user); DB.saveUsers(users);
  return user;
}
function verifyLogin(username, password){
  const u = findUser(username);
  if(!u) return null;
  return (u.password === password) ? u : null;
}

/* ---------- DOM & UI ---------- */
document.addEventListener('DOMContentLoaded', ()=> {
  // Elements
  const authArea = id('auth-area');
  const landing = id('landing');
  const dashboard = id('dashboard');
  const tools = id('tools');
  const certificate = id('certificate');

  // auth tabs
  id('tab-login').onclick = ()=> switchAuthTab('login');
  id('tab-register').onclick = ()=> switchAuthTab('register');
  id('tab-forgot').onclick = ()=> switchAuthTab('forgot');

  id('btn-show-login').onclick = ()=> { authArea.classList.remove('hidden'); landing.classList.add('hidden'); switchAuthTab('login'); }
  id('btn-show-register').onclick = ()=> { authArea.classList.remove('hidden'); landing.classList.add('hidden'); switchAuthTab('register'); }
  id('btn-browse-courses').onclick = ()=> { showDashboard(); }
  id('btn-try-demo').onclick = ()=> { tryDemo(); showDashboard(); }

  // forms
  id('form-register').onsubmit = (e)=> {
    e.preventDefault();
    try {
      const u = id('reg-username').value.trim();
      const p = id('reg-password').value;
      const p2 = id('reg-password2').value;
      if(p !== p2) return alert('Passwords do not match');
      registerUser(u,p,id('reg-email').value);
      alert('Registered! You can now log in.');
      switchAuthTab('login');
    } catch(err) { alert(err); }
  };

  id('form-login').onsubmit = (e)=> {
    e.preventDefault();
    const u = id('login-username').value.trim();
    const p = id('login-password').value;
    const user = verifyLogin(u,p);
    if(!user) return alert('Invalid username/password (demo uses localStorage)');
    localStorage.setItem('sl_current', JSON.stringify(user));
    loadState();
    showDashboard();
  };

  id('form-forgot').onsubmit = (e)=> {
    e.preventDefault();
    const u = id('forgot-username').value.trim();
    const user = findUser(u);
    if(!user) return alert('No user found');
    // Demo: show a reset token and allow password change in console or manual step.
    const token = Math.random().toString(36).slice(2,9);
    // Store token temporarily
    const st = DB.getState(); st.pwReset = st.pwReset||{}; st.pwReset[user.username] = token; DB.saveState(st);
    alert(`Reset token for ${user.username} (demo): ${token}\nIn production this would be emailed. Use this token to reset your password in the console or via an API.`);
  };

  id('btn-show-forgot').onclick = ()=> { switchAuthTab('forgot'); }

  // logout
  id('btn-logout').onclick = ()=> {
    localStorage.removeItem('sl_current');
    landing.classList.remove('hidden');
    dashboard.classList.add('hidden');
    authArea.classList.add('hidden');
  };

  // load courses into dashboard
  const courseList = id('course-list');
  function renderCourses(){
    courseList.innerHTML = '';
    COURSES.forEach(c=>{
      const li = document.createElement('li');
      li.innerHTML = `<div>
        <strong>${c.title}</strong><div class="muted">${c.description}</div>
      </div>
      <div>
        <button class="btn small" data-id="${c.id}" onclick="openCourse('${c.id}')">Open</button>
      </div>`;
      courseList.appendChild(li);
    });
  }

  // expose openCourse globally for inline onclick to work
  window.openCourse = (idCourse) => {
    const c = COURSES.find(x=>x.id===idCourse);
    if(!c) return;
    id('dashboard').querySelector('#course-player').classList.remove('hidden');
    id('course-title').textContent = c.title;
    // quiz area
    const qa = id('quiz-area');
    qa.innerHTML = '';
    // simple quiz demo
    qa.innerHTML = `
      <div><strong>Quick quiz</strong></div>
      <div>
        <label><input type="radio" name="q1" value="a" /> A) Save after spending</label><br/>
        <label><input type="radio" name="q1" value="b" /> B) Pay higher-interest debts first</label><br/>
        <label><input type="radio" name="q1" value="c" /> C) Max out credit</label>
      </div>
    `;
    // mark complete action
    id('mark-complete').onclick = ()=> {
      const cur = getCurrentUser();
      if(!cur) return alert('No user logged in');
      const users = DB.getUsers();
      const u = users.find(x=>x.username===cur.username);
      if(!u.completed.includes(c.id)) u.completed.push(c.id);
      DB.saveUsers(users);
      localStorage.setItem('sl_current', JSON.stringify(u));
      alert('Marked complete — certificate available');
    };
    id('get-certificate').onclick = ()=> {
      const curUser = getCurrentUser();
      if(!curUser) return alert('Login first');
      // quick check: must be completed
      const users = DB.getUsers();
      const found = users.find(x=>x.username===curUser.username);
      if(!found.completed.includes(c.id)) return alert('Complete the course to get certificate.');
      showCertificate(found.username, c.title);
    };
  };

  // close player
  id('close-player').onclick = ()=> {
    id('dashboard').querySelector('#course-player').classList.add('hidden');
  };

  // tools
  id('open-budget').onclick = ()=> { tools.classList.remove('hidden'); tools.scrollIntoView({behavior:'smooth'}); }
  id('open-goal').onclick = ()=> { tools.classList.remove('hidden'); tools.scrollIntoView({behavior:'smooth'}); }

  id('calc-budget').onclick = (e)=> {
    e.preventDefault();
    const inc = parseFloat(id('income').value)||0;
    const exp = parseFloat(id('expenses').value)||0;
    const spare = inc - exp;
    id('budget-result').textContent = (spare>=0) ? `You can save ${spare.toFixed(2)} per month.` : `You're overspending by ${Math.abs(spare).toFixed(2)} per month.`;
  };

  // goal planner save
  id('save-goal').onclick = (e)=> {
    e.preventDefault();
    const title = id('goal-title').value.trim();
    const amount = parseFloat(id('goal-amount').value)||0;
    const months = parseInt(id('goal-months').value,10)||1;
    if(!title) return alert('Enter goal name');
    const state = DB.getState();
    state.goals = state.goals||[];
    state.goals.push({id: uid(), title, amount, months, monthly: (amount/months).toFixed(2)});
    DB.saveState(state);
    renderGoals();
    id('goal-form').reset();
  };

  function renderGoals(){
    const state = DB.getState();
    const list = id('goals-list'); list.innerHTML = '';
    (state.goals||[]).forEach(g=>{
      const li = document.createElement('li');
      li.textContent = `${g.title} — R${g.amount} over ${g.months} months → R${g.monthly}/month`;
      list.appendChild(li);
    });
  }

  // certificate UI
  id('print-cert').onclick = ()=> window.print();
  id('close-cert').onclick = ()=> certificate.classList.add('hidden');

  // demo & helpers
  function switchAuthTab(tab){
    id('form-login').classList.toggle('hidden', tab!=='login');
    id('form-register').classList.toggle('hidden', tab!=='register');
    id('form-forgot').classList.toggle('hidden', tab!=='forgot');
    ['tab-login','tab-register','tab-forgot'].forEach(t=>{
      id(t).classList.toggle('active', t===('tab-'+tab));
    });
  }

  function showDashboard(){
    renderCourses();
    landing.classList.add('hidden');
    id('auth-area').classList.add('hidden');
    dashboard.classList.remove('hidden');
    // show user welcome if logged
    const u = getCurrentUser();
    id('welcome-user').textContent = u ? `Hello, ${u.username}` : 'Hello, learner';
    // daily tip pick random (persisted per day)
    const tip = DAILY_TIPS[Math.floor(Math.random()*DAILY_TIPS.length)];
    id('daily-tip').textContent = tip;
    renderGoals();
  }

  function showCertificate(username, courseTitle){
    certificate.classList.remove('hidden');
    const body = id('certificate-body');
    const date = new Date().toLocaleDateString();
    body.innerHTML = `<div style="text-align:center;">
      <h2>Certificate of Completion</h2>
      <p>This certifies that <strong>${escapeHtml(username)}</strong> has completed <strong>${escapeHtml(courseTitle)}</strong>.</p>
      <p><small>Date: ${date}</small></p>
    </div>`;
  }

  function getCurrentUser(){
    return JSON.parse(localStorage.getItem('sl_current') || 'null');
  }

  function loadState(){
    const st = DB.getState();
    // create demo user if none
    if(DB.getUsers().length === 0){
      // create demo account
      try {
        registerUser('demo','demo','demo@sanlam.local');
        const u = findUser('demo');
        u.completed = [];
        const users = DB.getUsers();
        DB.saveUsers(users);
      } catch(e){}
    }
    // if sl_current was stored earlier, update it from latest stored users (to reflect completed courses)
    const cur = getCurrentUser();
    if(cur){
      const real = findUser(cur.username);
      if(real) localStorage.setItem('sl_current', JSON.stringify(real));
    }
  }

  // demo quick login
  function tryDemo(){
    const demo = findUser('demo');
    if(!demo) registerUser('demo','demo','demo@sanlam.local');
    localStorage.setItem('sl_current', JSON.stringify(findUser('demo')));
  }

  // initial load:
  loadState();

  // small helper to expose functions to outside
  window.showDashboard = showDashboard;
  window.renderCourses = renderCourses;
  renderCourses();
});

/* ---------- Helpers (DOM) ---------- */
function id(i){ return document.getElementById(i); }
function escapeHtml(s){ return (s+'').replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' })[m]); }

/* ---------- Expose a few functions for quick dev / console ---------- */
/* In browser console, you can:
   - view users: JSON.parse(localStorage.getItem('sl_users')||'[]')
   - set password for user (demo): modify sl_users and save back
*/
