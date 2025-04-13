function showLogin() {
    document.getElementById('loginForm').style.display = 'block';
    document.getElementById('signupForm').style.display = 'none';
    document.getElementById('loginTab').classList.add('active');
    document.getElementById('signupTab').classList.remove('active');
  }
  
  function showSignup() {
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('signupForm').style.display = 'block';
    document.getElementById('loginTab').classList.remove('active');
    document.getElementById('signupTab').classList.add('active');
  }
  
  // Toggle password visibility for both forms
  var togglePasswordIcons = document.querySelectorAll('.toggle-password');
  togglePasswordIcons.forEach(function(icon) {
    icon.addEventListener('click', function() {
      var input = this.previousElementSibling;
      if (input.getAttribute('type') === 'password') {
        input.setAttribute('type', 'text');
      } else {
        input.setAttribute('type', 'password');
      }
    });
  });
  