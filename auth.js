// Authentication functionality
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initAuth);
} else {
  initAuth();
}
function initAuth() {
  const authTabs = document.querySelectorAll(".auth-tab");
  const authForms = document.querySelectorAll(".auth-form");
  const passwordToggles = document.querySelectorAll(".password-toggle");
  const loginForm = document.querySelector(".login-form");
  const registerForm = document.querySelector(".register-form");

  // Add early click guard for terms checkbox to avoid native validation blocking our message
  if (registerForm) {
    const registerSubmitBtn = registerForm.querySelector(".btn-auth");
    if (registerSubmitBtn) {
      registerSubmitBtn.addEventListener("click", function (e) {
        const termsChecked = !!registerForm.querySelector('input[name="terms"]')
          .checked;
        if (!termsChecked) {
          e.preventDefault();
          showNotification("利用規約に同意してください。", "error");
          const termsInput = registerForm.querySelector('input[name="terms"]');
          if (termsInput && typeof termsInput.focus === "function")
            termsInput.focus();
        }
      });
    }
  }

  // Tab switching functionality
  authTabs.forEach((tab) => {
    tab.addEventListener("click", function () {
      const targetTab = this.getAttribute("data-tab");

      // Remove active class from all tabs and forms
      authTabs.forEach((t) => t.classList.remove("active"));
      authForms.forEach((f) => f.classList.remove("active"));

      // Add active class to clicked tab and corresponding form
      this.classList.add("active");
      document.getElementById(`${targetTab}-form`).classList.add("active");
    });
  });

  // Password toggle functionality
  passwordToggles.forEach((toggle) => {
    toggle.addEventListener("click", function () {
      const input = this.previousElementSibling;
      const icon = this.querySelector("i");

      if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
      } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
      }
    });
  });

  // Login form handling
  if (loginForm) {
    loginForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      const email = this.querySelector("#login-email").value;
      const password = this.querySelector("#login-password").value;
      const remember = this.querySelector('input[name="remember"]').checked;

      if (!email || !password) {
        showNotification(
          "メールアドレスとパスワードを入力してください。",
          "error"
        );
        return;
      }

      showNotification("ログイン中...", "info");
      try {
        const { user } = await giftApi.login({ email, password });
        // Persist minimal state for UI compatibility
        localStorage.setItem("userData", JSON.stringify(user));
        localStorage.setItem("isLoggedIn", "true");
        if (remember) localStorage.setItem("rememberMe", "true");
        showNotification("ログインに成功しました！", "success");
        setTimeout(() => {
          window.location.href = "mypage.php";
        }, 800);
      } catch (err) {
        showNotification(err.message || "ログインに失敗しました。", "error");
      }
    });
  }

  // Register form handling
  if (registerForm) {
    registerForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      const firstName = this.querySelector("#register-firstname").value;
      const lastName = this.querySelector("#register-lastname").value;
      const email = this.querySelector("#register-email").value;
      const password = this.querySelector("#register-password").value;
      const confirmPassword = this.querySelector(
        "#register-confirm-password"
      ).value;
      const birthDate = this.querySelector("#register-birthdate").value;
      const terms = this.querySelector('input[name="terms"]').checked;

      if (
        !firstName ||
        !lastName ||
        !email ||
        !password ||
        !confirmPassword ||
        !birthDate
      ) {
        showNotification("すべての必須項目を入力してください。", "error");
        return;
      }
      if (password !== confirmPassword) {
        showNotification("パスワードが一致しません。", "error");
        return;
      }
      if (password.length < 8) {
        showNotification("パスワードは8文字以上で入力してください。", "error");
        return;
      }
      if (!terms) {
        showNotification("利用規約に同意してください。", "error");
        return;
      }

      showNotification("アカウントを作成中...", "info");
      try {
        const { user } = await giftApi.register({
          email,
          password,
          firstName,
          lastName,
          birthDate,
        });
        localStorage.setItem("userData", JSON.stringify(user));
        localStorage.setItem("isLoggedIn", "true");
        showNotification("アカウントが正常に作成されました！", "success");
        setTimeout(() => {
          window.location.href = "mypage.php";
        }, 800);
      } catch (err) {
        showNotification(err.message || "登録に失敗しました。", "error");
      }
    });
  }

  // Social login buttons
  document.querySelectorAll(".btn-social.btn-google").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();
      try {
        window.giftApi?.oauthGoogleStart?.();
      } catch (_) {}
    });
  });

  // Forgot password link
  const forgotPasswordLink = document.querySelector(".forgot-password");
  if (forgotPasswordLink) {
    forgotPasswordLink.addEventListener("click", function (e) {
      e.preventDefault();
      showNotification("パスワードリセット機能は現在開発中です。", "info");
    });
  }
}

// Handle OAuth errors shown via query string
(function () {
  const params = new URLSearchParams(window.location.search);
  const err = params.get("oauth_error");
  if (err) {
    const map = {
      google_not_configured:
        "Googleログインの設定が完了していません。管理者にお問い合わせください。",
      invalid_state: "セキュリティ検証に失敗しました。もう一度お試しください。",
      token_exchange_failed:
        "Googleとの通信に失敗しました。時間をおいて再度お試しください。",
      userinfo_failed: "Googleアカウント情報の取得に失敗しました。",
    };
    const msg = map[err] || "OAuthエラーが発生しました。";
    showNotification(msg, "error");
  }
})();

// Check if user is already logged in
async function checkLoginStatus() {
  const isLoggedIn = localStorage.getItem("isLoggedIn");
  if (isLoggedIn === "true") {
    // Attempt to sync session with backend (optional)
    try {
      await window.giftApi?.me?.();
    } catch (_) {}
    window.location.href = "mypage.php";
  }
}

// Run login status check when page loads
checkLoginStatus();
