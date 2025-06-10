// Toggle antara Login dan Signup
function showLogin() {
    document.getElementById('login-form').classList.remove('hidden');
    document.getElementById('signup-form').classList.add('hidden');
    
    document.querySelectorAll('.toggle-buttons button')[0].classList.add('active');
    document.querySelectorAll('.toggle-buttons button')[1].classList.remove('active');
  }
  
  function showSignup() {
    document.getElementById('signup-form').classList.remove('hidden');
    document.getElementById('login-form').classList.add('hidden');
    
    document.querySelectorAll('.toggle-buttons button')[0].classList.remove('active');
    document.querySelectorAll('.toggle-buttons button')[1].classList.add('active');
  }
  
  // Toggle Lihat/Sembunyikan Password
  function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    if (input.type === "password") {
      input.type = "text";
      button.textContent = '🙈'; // Password terlihat
    } else {
      input.type = "password";
      button.textContent = '👁️'; // Password tersembunyi
    }
  }
  