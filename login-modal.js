// Load the login modal HTML into the page
function loadLoginModal(callback) {
  fetch('login-modal.html')
    .then(res => res.text())
    .then(html => {
      document.body.insertAdjacentHTML('beforeend', html);
      if (callback) callback();
    });
}

// Show/hide modal logic
function setupLoginModalTriggers() {
  document.querySelectorAll('#open-auth').forEach(btn => {
    btn.onclick = () => document.getElementById('auth-modal').classList.remove('hidden');
  });
  document.getElementById('close-auth').onclick = () => {
    document.getElementById('auth-modal').classList.add('hidden');
  };
  document.getElementById('show-login').onclick = () => {
    document.getElementById('login-form').classList.remove('hidden');
    document.getElementById('register-form').classList.add('hidden');
  };
  document.getElementById('show-register').onclick = () => {
    document.getElementById('login-form').classList.add('hidden');
    document.getElementById('register-form').classList.remove('hidden');
  };
}

// Firebase Auth logic
function setupAuthHandlers() {
  // Login
  document.getElementById('login-form').onsubmit = async function(e) {
    e.preventDefault();
    const email = document.getElementById('login-email').value;
    const password = document.getElementById('login-password').value;
    const msg = document.getElementById('auth-message');
    msg.textContent = '';
    try {
      const userCredential = await firebase.auth().signInWithEmailAndPassword(email, password);
      const user = userCredential.user;
      // Check for admin status
      let isAdmin = false;
      // 1. Check custom claims
      await user.getIdTokenResult(true).then(idTokenResult => {
        if (idTokenResult.claims.admin) isAdmin = true;
      });
      // 2. Check Firestore user profile
      if (!isAdmin) {
        const db = firebase.firestore();
        const doc = await db.collection('users').doc(user.uid).get();
        if (doc.exists && (doc.data().role === 'admin' || doc.data().admin === true)) {
          isAdmin = true;
        }
      }
      // Redirect
      if (isAdmin) {
        window.location.href = 'discipleship-admin.html';
      } else {
        window.location.href = 'discipleship-user.html';
      }
    } catch (error) {
      msg.textContent = error.message;
    }
  };
  // Register
  document.getElementById('register-form').onsubmit = async function(e) {
    e.preventDefault();
    const email = document.getElementById('register-email').value;
    const password = document.getElementById('register-password').value;
    const msg = document.getElementById('auth-message');
    msg.textContent = '';
    try {
      await firebase.auth().createUserWithEmailAndPassword(email, password);
      msg.textContent = 'Registration successful! Please log in.';
      document.getElementById('show-login').click();
    } catch (error) {
      msg.textContent = error.message;
    }
  };
}

// Initialize modal and handlers
loadLoginModal(() => {
  setupLoginModalTriggers();
  setupAuthHandlers();
}); 