// Discipleship Portal Login Handler
// Supports admin and user login, redirects to correct dashboard

const ADMIN_EMAIL = 'admin@heartsaftergod.org';
const ADMIN_PASSWORD = 'SuperSecret123!';

function getUsers() {
  return JSON.parse(localStorage.getItem('discipleship_users') || '[]');
}
function saveUsers(users) {
  localStorage.setItem('discipleship_users', JSON.stringify(users));
}
function seedAdmin() {
  let users = getUsers();
  if (!users.find(u => u.email === ADMIN_EMAIL)) {
    users.push({ name: 'Administrator', email: ADMIN_EMAIL, password: ADMIN_PASSWORD, role: 'admin' });
    saveUsers(users);
  }
}
function getUserByEmail(email) {
  return getUsers().find(u => u.email === email);
}

// Seed admin on load
seedAdmin();

// Login handler
function handleLogin(e) {
  e.preventDefault();
  const email = document.getElementById('email').value.trim().toLowerCase();
  const password = document.getElementById('password').value;
  if (email === ADMIN_EMAIL) {
    if (password === ADMIN_PASSWORD) {
      window.location.href = 'discipleship-admin.html';
      return;
    } else {
      alert('Invalid admin credentials.');
      return;
    }
  }
  const user = getUserByEmail(email);
  if (user && user.password === password && user.role === 'user') {
    // Store user session (optional)
    localStorage.setItem('discipleship_logged_in_user', JSON.stringify(user));
    window.location.href = 'discipleship-user.html';
    return;
  }
  alert('Invalid email or password.');
}

document.addEventListener('DOMContentLoaded', function() {
  const loginForm = document.getElementById('login-form');
  if (loginForm) loginForm.onsubmit = handleLogin;
}); 