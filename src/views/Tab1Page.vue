<template>
  <ion-page>
    <ion-content :fullscreen="true" class="login-page">
      <!-- Top Wave Background -->
      <div class="wave-container">
        <svg class="wave-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" preserveAspectRatio="none">
          <path fill="#8B5CF6" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,144C960,149,1056,139,1152,122.7C1248,107,1344,85,1392,74.7L1440,64L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path>
        </svg>
        
        <!-- Restaurant Icon/Logo -->
        <div class="logo-container">
          <div class="logo-circle">
            <ion-icon :icon="restaurantOutline" class="logo-icon"></ion-icon>
          </div>
          <h1 class="brand-title">Resto App</h1>
          <p class="brand-subtitle">Delicious Food, Delivered Fresh</p>
        </div>
      </div>

      <!-- Login Form Card -->
      <div class="form-container">
        <div class="login-card">
          <h2 class="form-title">Sign in</h2>
          <p class="form-subtitle">Welcome back! Please login to continue</p>

          <form @submit.prevent="handleLogin">
            <!-- Username -->
            <div class="input-group">
              <ion-icon :icon="personOutline" class="input-icon"></ion-icon>
              <input
                v-model="loginData.username"
                type="text"
                placeholder="Username"
                required
                :disabled="loading"
                class="custom-input"
              />
            </div>

            <!-- Password -->
            <div class="input-group">
              <ion-icon :icon="lockClosedOutline" class="input-icon"></ion-icon>
              <input
                v-model="loginData.password"
                type="password"
                placeholder="Password"
                required
                :disabled="loading"
                class="custom-input"
              />
            </div>

            <!-- Login Button -->
            <button type="submit" :disabled="loading" class="login-btn">
              <ion-spinner v-if="loading" name="crescent"></ion-spinner>
              <span v-else>Login</span>
            </button>

            <!-- Register Link -->
            <div class="register-link">
              <span>Don't have an account? </span>
              <router-link to="/register" class="link">Sign up</router-link>
            </div>
          </form>
        </div>
      </div>

      <!-- Toast -->
      <ion-toast
        :is-open="toast.isOpen"
        :message="toast.message"
        :duration="2000"
        :color="toast.color"
        position="top"
        @didDismiss="toast.isOpen = false"
      ></ion-toast>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { 
  IonPage, 
  IonContent, 
  IonToast,
  IonSpinner,
  IonIcon
} from '@ionic/vue';
import { restaurantOutline, personOutline, lockClosedOutline } from 'ionicons/icons';
import authService from '@/services/auth';

const router = useRouter();

// Reactive data
const loginData = ref({
  username: '',
  password: ''
});

const loading = ref(false);

const toast = ref({
  isOpen: false,
  message: '',
  color: 'success'
});

// Login handler
const handleLogin = async () => {
  // Validation
  if (!loginData.value.username || !loginData.value.password) {
    showToast('Username dan password harus diisi', 'warning');
    return;
  }

  loading.value = true;

  try {
    const result = await authService.login({
      username: loginData.value.username,
      password: loginData.value.password
    });

    if (result.success) {
      showToast(result.message, 'success');
      
      // Get user role and redirect to appropriate dashboard
      const user = authService.getCurrentUser();
      const dashboardPath = getDashboardPath(user?.role);
      
      setTimeout(() => {
        router.push(dashboardPath);
      }, 500);
    } else {
      showToast(result.message, 'danger');
    }
  } catch (error) {
    showToast('Terjadi kesalahan. Silakan coba lagi.', 'danger');
  } finally {
    loading.value = false;
  }
};

// Helper function to get dashboard path based on role
const getDashboardPath = (role: string | undefined): string => {
  switch (role) {
    case 'pelanggan':
      return '/pelanggan';
    case 'kasir':
      return '/kasir';
    case 'koki':
      return '/kitchen';
    case 'owner':
      return '/owner';
    case 'admin':
      return '/manajer';
    default:
      return '/login';
  }
};

// Show toast notification
const showToast = (message: string, color: string = 'success') => {
  toast.value = {
    isOpen: true,
    message,
    color
  };
};

// Navigate to register
const navigateToRegister = () => {
  router.push('/tabs/tab2');
};
</script>

<style scoped>
.login-page {
  --background: #16213e;
}

/* Wave Container */
.wave-container {
  position: relative;
  height: 45vh;
  background: linear-gradient(135deg, #8B5CF6 0%, #6D28D9 100%);
  overflow: hidden;
}

.wave-svg {
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 100%;
  height: 120px;
}

/* Logo */
.logo-container {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  z-index: 2;
}

.logo-circle {
  width: 100px;
  height: 100px;
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(10px);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-10px);
  }
}

.logo-icon {
  font-size: 3.5rem;
  color: white;
}

.brand-title {
  font-size: 2rem;
  font-weight: 700;
  color: white;
  margin: 0 0 0.5rem 0;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.brand-subtitle {
  font-size: 0.95rem;
  color: rgba(255, 255, 255, 0.9);
  margin: 0;
  font-weight: 400;
}

/* Form Container */
.form-container {
  position: relative;
  margin-top: -60px;
  padding: 0 1.5rem 2rem;
  z-index: 3;
}

.login-card {
  background: white;
  border-radius: 24px;
  padding: 2rem 1.5rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.form-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0 0 0.5rem 0;
}

.form-subtitle {
  font-size: 0.9rem;
  color: #666;
  margin: 0 0 2rem 0;
}

/* Input Groups */
.input-group {
  position: relative;
  margin-bottom: 1.25rem;
}

.input-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  font-size: 1.3rem;
  color: #8B5CF6;
  z-index: 1;
}

.custom-input {
  width: 100%;
  padding: 1rem 1rem 1rem 3rem;
  border: 2px solid #E8E8E8;
  border-radius: 12px;
  font-size: 1rem;
  background: #F9F9F9;
  color: #1a1a2e;
  transition: all 0.3s ease;
  outline: none;
}

.custom-input::placeholder {
  color: #999;
}

.custom-input:focus {
  border-color: #8B5CF6;
  background: white;
  box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}

.custom-input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Login Button */
.login-btn {
  width: 100%;
  padding: 1rem;
  background: linear-gradient(135deg, #8B5CF6 0%, #6D28D9 100%);
  border: none;
  border-radius: 12px;
  color: white;
  font-size: 1.05rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 1rem;
  box-shadow: 0 6px 20px rgba(139, 92, 246, 0.3);
}

.login-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(139, 92, 246, 0.4);
}

.login-btn:active:not(:disabled) {
  transform: translateY(0);
}

.login-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Register Link */
.register-link {
  text-align: center;
  margin-top: 1.5rem;
  font-size: 0.95rem;
  color: #666;
  position: relative;
  z-index: 100;
  pointer-events: auto;
}

.link {
  color: #8B5CF6;
  text-decoration: none;
  font-weight: 600;
  cursor: pointer;
  transition: color 0.3s ease;
}

.link:hover {
  color: #6D28D9;
}

/* Responsive */
@media (min-width: 768px) {
  .login-card {
    max-width: 420px;
    margin: 0 auto;
    padding: 2.5rem 2rem;
  }
  
  .wave-container {
    height: 50vh;
  }
}
</style>
