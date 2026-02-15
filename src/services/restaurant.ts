import api from './api';

export interface Table {
  id_meja: number;
  nomor_meja: string;
  kapasitas: number;
  status: 'kosong' | 'terisi';
  id_pelanggan?: number;
}

export interface Menu {
  id_menu: number;
  nama_menu: string;
  kategori: string;
  harga: number;
  stok: number;
  gambar: string;
  deskripsi?: string;
  tersedia: boolean;
}

export interface OrderItem {
  id_menu: number;
  jumlah: number;
  catatan?: string;
}

export interface Order {
  id_transaksi: number;
  id_meja: number;
  id_pelanggan: number;
  id_kasir?: number;
  total_harga: number;
  status: 'pending' | 'cooking' | 'ready' | 'paid' | 'cancelled';
  metode_pembayaran?: 'cash' | 'qris';
  catatan?: string;
  created_at: string;
  completed_at?: string;
  items?: any[];
}

// Tables API
export const tablesApi = {
  getAll: () => api.get<Table[]>('/tables.php'),
  
  getById: (id: number) => api.get<Table>(`/tables.php/${id}`),
  
  select: (id: number, idPelanggan: number) => 
    api.put(`/tables.php/${id}`, { action: 'select', id_pelanggan: idPelanggan }),
  
  clear: (id: number) => 
    api.put(`/tables.php/${id}`, { action: 'clear' }),
};

// Menu API
export const menuApi = {
  getAll: (kategori?: string) => 
    api.get<Menu[]>('/menu.php', { params: { kategori, tersedia: 1 } }),
  
  getById: (id: number) => api.get<Menu>(`/menu.php/${id}`),
  
  create: (data: Partial<Menu>) => api.post('/menu.php', data),
  
  update: (id: number, data: Partial<Menu>) => api.put(`/menu.php/${id}`, data),
  
  delete: (id: number) => api.delete(`/menu.php/${id}`),
  
  uploadImage: async (file: File) => {
    const formData = new FormData();
    formData.append('image', file);
    
    // Use the proxied api instance
    return api.post('/post_image.php', formData);
  }
};

// Orders API
export const ordersApi = {
  getAll: (params?: { status?: string; id_pelanggan?: number; date_from?: string; date_to?: string }) => 
    api.get<Order[]>('/orders.php', { params }),
  
  getById: (id: number) => api.get<Order>(`/orders.php/${id}`),
  
  create: (data: { id_meja: number; id_user: number; items: OrderItem[]; catatan?: string; nama_pelanggan?: string }) => 
    api.post('/orders.php', data),
  
  updateStatus: (id: number, status: string) => 
    api.put(`/orders.php/${id}`, { action: 'update_status', status }),
  
  update: (id: number, data: any) => 
    api.put(`/orders.php/${id}`, data),
  
  complete: (id: number) => 
    api.put(`/orders.php/${id}`, { action: 'complete' }),
  
  processPayment: (id: number, metodePembayaran: 'cash' | 'qris', idKasir: number) => 
    api.put(`/orders.php/${id}`, { action: 'payment', metode_pembayaran: metodePembayaran, id_kasir: idKasir }),
  
  delete: (id: number) => api.delete(`/orders.php/${id}`),
};

// Reports API
export const reportsApi = {
  getRevenue: (params: { date_from: string; date_to: string; period: 'daily' | 'monthly' | 'yearly' }) => 
    api.get('/reports.php', { params: { type: 'revenue', ...params } }),
  
  getTopMenu: (params: { date_from: string; date_to: string; limit?: number }) => 
    api.get('/reports.php', { params: { type: 'top-menu', ...params } }),
  
  getEmployees: (params: { date_from: string; date_to: string; limit?: number }) => 
    api.get('/reports.php', { params: { type: 'employees', ...params } }),
  
  getSummary: (params: { date_from: string; date_to: string }) => 
    api.get('/reports.php', { params: { type: 'summary', ...params } }),
};

// Users API
export const usersApi = {
  getAll: (role?: string) => api.get('/users.php', { params: { role } }),
  
  getById: (id: number) => api.get(`/users.php/${id}`),
  
  create: (data: { username: string; password: string; role: string }) => 
    api.post('/users.php', data),
  
  update: (id: number, data: Partial<{ username: string; password: string; role: string }>) => 
    api.put(`/users.php/${id}`, data),
  
  delete: (id: number) => api.delete(`/users.php/${id}`),
};
