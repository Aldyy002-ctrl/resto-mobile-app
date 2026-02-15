import { authApi, LoginCredentials, RegisterData } from './api';

export interface User {
  id_user: number;
  username: string;
  role: string;
}

class AuthService {
  // Login method
  async login(credentials: LoginCredentials): Promise<{ success: boolean; message: string; user?: User }> {
    try {
      const response = await authApi.login(credentials);
      
      if (response.success && response.data) {
        // Save token and user data to localStorage
        localStorage.setItem('auth_token', response.data.token);
        localStorage.setItem('user_data', JSON.stringify(response.data.user));
        
        return {
          success: true,
          message: response.message,
          user: response.data.user
        };
      }
      
      return {
        success: false,
        message: response.message
      };
    } catch (error: any) {
      return {
        success: false,
        message: error.response?.data?.message || 'Login gagal. Silakan coba lagi.'
      };
    }
  }

  // Register method
  async register(data: RegisterData): Promise<{ success: boolean; message: string; user?: User }> {
    try {
      const response = await authApi.register(data);
      
      if (response.success && response.data) {
        // Save token and user data to localStorage
        localStorage.setItem('auth_token', response.data.token);
        localStorage.setItem('user_data', JSON.stringify(response.data.user));
        
        return {
          success: true,
          message: response.message,
          user: response.data.user
        };
      }
      
      return {
        success: false,
        message: response.message
      };
    } catch (error: any) {
      return {
        success: false,
        message: error.response?.data?.message || 'Registrasi gagal. Silakan coba lagi.'
      };
    }
  }

  // Logout method
  logout(): void {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user_data');
  }

  // Get current user
  getCurrentUser(): User | null {
    const userData = localStorage.getItem('user_data');
    if (userData) {
      try {
        return JSON.parse(userData);
      } catch {
        return null;
      }
    }
    return null;
  }

  // Alias for getCurrentUser
  getUser(): User | null {
    return this.getCurrentUser();
  }

  // Check if user is authenticated
  isAuthenticated(): boolean {
    return !!localStorage.getItem('auth_token');
  }

  // Get token
  getToken(): string | null {
    return localStorage.getItem('auth_token');
  }

  // Verify token
  async verifyToken(): Promise<boolean> {
    try {
      const response = await authApi.verifyToken();
      return response.success;
    } catch {
      this.logout();
      return false;
    }
  }
}

export default new AuthService();
