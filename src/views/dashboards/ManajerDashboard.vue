<template>
  <ion-page>
    <div class="dashboard-container">
      <!-- Header -->
      <div class="header">
        <h1><span class="brand">Resto<span class="highlight">Hub</span></span> Manager</h1>
        <div class="header-right">
          <div class="user-info">
            <span class="username">{{ user?.username }}</span>
            <span class="role">ADMIN</span>
          </div>
          <button @click="handleLogout" class="logout-btn">
            <ion-icon :icon="logOutOutline"></ion-icon>
            Logout
          </button>
        </div>
      </div>

      <!-- Tabs -->
      <div class="tabs">
        <button 
          :class="['tab-btn', { active: activeTab === 'menu' }]"
          @click="activeTab = 'menu'"
        >
          <ion-icon :icon="restaurantOutline"></ion-icon>
          Menu Makanan
        </button>
        <button 
          :class="['tab-btn', { active: activeTab === 'karyawan' }]"
          @click="activeTab = 'karyawan'"
        >
          <ion-icon :icon="peopleOutline"></ion-icon>
          Data Karyawan
        </button>
      </div>

      <!-- Menu Makanan Tab -->
      <div v-if="activeTab === 'menu'" class="content-section">
        <div class="section-header">
          <div>
            <h2>Daftar Menu</h2>
            <p>Kelola menu makanan dan minuman restoran</p>
          </div>
          <button @click="openAddMenuModal" class="add-btn">
            <ion-icon :icon="addOutline"></ion-icon>
            Tambah Menu
          </button>
        </div>

        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>GAMBAR</th>
                <th>NAMA MENU</th>
                <th>HARGA</th>
                <th>STOK</th>
                <th>KATEGORI</th>
                <th>AKSI</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in menuItems" :key="item.id_menu">
                <td>
                  <img :src="item.foto_makanan || '/assets/default-food.jpg'" class="menu-thumb" />
                </td>
                <td>{{ item.nama_menu }}</td>
                <td>Rp {{ item.harga.toLocaleString() }}</td>
                <td>
                  <span class="stock-badge">{{ item.stok }} Porsi</span>
                </td>
                <td>
                  <span :class="['category-badge', getCategoryClass(item.id_kategori)]">
                    {{ getCategoryName(item.id_kategori) }}
                  </span>
                </td>
                <td>
                  <div class="action-btns">
                    <button @click="openEditMenuModal(item)" class="edit-btn">
                      <ion-icon :icon="createOutline"></ion-icon>
                    </button>
                    <button @click="deleteMenu(item)" class="delete-btn">
                      <ion-icon :icon="trashOutline"></ion-icon>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Data Karyawan Tab -->
      <div v-if="activeTab === 'karyawan'" class="content-section">
        <div class="section-header">
          <div>
            <h2>Data Karyawan</h2>
            <p>Kelola akses pengguna sistem</p>
          </div>
          <button @click="openAddUserModal" class="add-btn">
            <ion-icon :icon="personAddOutline"></ion-icon>
            Tambah User
          </button>
        </div>

        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>NO / ID</th>
                <th>USERNAME</th>
                <th>ROLE / JABATAN</th>
                <th>AKSI</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users" :key="user.id_user">
                <td>#{{ user.id_user }}</td>
                <td>
                  <div class="user-cell">
                    <ion-icon :icon="personCircleOutline"></ion-icon>
                    {{ user.username }}
                  </div>
                </td>
                <td>
                  <span :class="['role-badge', user.role.toLowerCase()]">
                    {{ user.role.toUpperCase() }}
                  </span>
                </td>
                <td>
                  <div class="action-btns">
                    <button 
                      @click="deleteUser(user)" 
                      class="delete-btn"
                      :disabled="user.username === currentUser?.username"
                    >
                      <ion-icon :icon="trashOutline"></ion-icon>
                    </button>
                    <span v-if="user.username === currentUser?.username" class="anda-label">(Anda)</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Add/Edit Menu Modal -->
      <div v-if="menuModal.isOpen" class="modal-overlay" @click="closeMenuModal">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h3>{{ menuModal.isEdit ? 'Edit Menu' : 'Tambah Menu Baru' }}</h3>
            <button @click="closeMenuModal" class="close-btn">×</button>
          </div>
          
          <div class="modal-body">
            <div class="form-group">
              <label>Nama Menu</label>
              <input 
                v-model="menuModal.data.nama_menu" 
                type="text" 
                placeholder="Contoh: Nasi Goreng Spesial"
                class="form-input"
              />
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Harga (Rp)</label>
                <input 
                  v-model.number="menuModal.data.harga" 
                  type="number" 
                  placeholder="0"
                  class="form-input"
                />
              </div>
              <div class="form-group">
                <label>{{ menuModal.isEdit ? 'Stok' : 'Stok Awal' }}</label>
                <input 
                  v-model.number="menuModal.data.stok" 
                  type="number" 
                  placeholder="0"
                  class="form-input"
                />
              </div>
            </div>

            <div class="form-group">
              <label>Kategori</label>
              <select v-model="menuModal.data.id_kategori" class="form-select">
                <option value="1">Makanan</option>
                <option value="2">Minuman</option>
                <option value="3">Camilan</option>
              </select>
            </div>

            <div class="form-group">
              <label>{{ menuModal.isEdit ? 'Ganti Gambar (Opsional)' : 'Upload Gambar' }}</label>
              <div class="file-input-wrapper">
                <input 
                  type="file" 
                  @change="handleFileUpload" 
                  accept="image/*"
                  class="file-input"
                  id="fileInput"
                />
                <label for="fileInput" class="file-label">
                  <span>Pilih File</span>
                  <span class="file-name">{{ menuModal.fileName || 'Tidak ada file yang dipilih' }}</span>
                </label>
              </div>
            </div>

            <button 
              @click="saveMenu" 
              :disabled="processing"
              class="submit-btn"
            >
              <ion-spinner v-if="processing"></ion-spinner>
              <span v-else>{{ menuModal.isEdit ? 'Update Menu' : 'Simpan Menu' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Add User Modal -->
      <div v-if="userModal.isOpen" class="modal-overlay" @click="closeUserModal">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h3>Tambah Karyawan</h3>
            <button @click="closeUserModal" class="close-btn">×</button>
          </div>
          
          <div class="modal-body">
            <div class="form-group">
              <label>Username</label>
              <input 
                v-model="userModal.data.username" 
                type="text" 
                placeholder="Username untuk login"
                class="form-input"
              />
            </div>

            <div class="form-group">
              <label>Password</label>
              <input 
                v-model="userModal.data.password" 
                type="password" 
                placeholder="Password default"
                class="form-input"
              />
            </div>

            <div class="form-group">
              <label>Role / Jabatan</label>
              <select v-model="userModal.data.role" class="form-select">
                <option value="admin">Admin / Manajer</option>
                <option value="kasir">Kasir</option>
                <option value="koki">Koki</option>
                <option value="owner">Owner</option>
                <option value="pelanggan">Pelanggan</option>
              </select>
            </div>

            <button 
              @click="saveUser" 
              :disabled="processing"
              class="submit-btn"
            >
              <ion-spinner v-if="processing"></ion-spinner>
              <span v-else>Tambah User</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Success Notification -->
      <div v-if="notification" class="notification" :class="notification.type">
        <span>{{ notification.message }}</span>
        <button @click="notification = null" class="close-notif">×</button>
      </div>
    </div>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { IonPage, IonIcon, IonSpinner } from '@ionic/vue';
import { 
  logOutOutline, restaurantOutline, peopleOutline, addOutline, 
  createOutline, trashOutline, personAddOutline, personCircleOutline 
} from 'ionicons/icons';
import authService from '@/services/auth';
import { menuApi, usersApi } from '@/services/restaurant';

const router = useRouter();
const user = authService.getUser();
const currentUser = user;

// State
const activeTab = ref('menu');
const menuItems = ref<any[]>([]);
const users = ref<any[]>([]);
const processing = ref(false);
const notification = ref<{ message: string; type: 'success' | 'error' } | null>(null);

const menuModal = ref({
  isOpen: false,
  isEdit: false,
  data: {
    id_menu: null as number | null,
    nama_menu: '',
    harga: 0,
    stok: 0,
    id_kategori: 1,
    foto_makanan: ''
  },
  fileName: ''
});

const userModal = ref({
  isOpen: false,
  data: {
    username: '',
    password: '',
    role: 'pelanggan'
  }
});

// Methods
const loadMenu = async () => {
  try {
    const response = await menuApi.getAll();
    if (response.data.success) {
      menuItems.value = response.data.data;
    }
  } catch (error) {
    console.error('Failed to load menu:', error);
  }
};

const loadUsers = async () => {
  try {
    const response = await usersApi.getAll();
    if (response.data.success) {
      users.value = response.data.data;
    }
  } catch (error) {
    console.error('Failed to load users:', error);
  }
};

const getCategoryName = (id: number) => {
  const categories: any = { 1: 'Makanan', 2: 'Minuman', 3: 'Camilan' };
  return categories[id] || 'Lainnya';
};

const getCategoryClass = (id: number) => {
  const classes: any = { 1: 'makanan', 2: 'minuman', 3: 'camilan' };
  return classes[id] || '';
};

const openAddMenuModal = () => {
  menuModal.value = {
    isOpen: true,
    isEdit: false,
    data: {
      id_menu: null,
      nama_menu: '',
      harga: 0,
      stok: 0,
      id_kategori: 1,
      foto_makanan: ''
    },
    fileName: ''
  };
};

const openEditMenuModal = (item: any) => {
  menuModal.value = {
    isOpen: true,
    isEdit: true,
    data: { ...item },
    fileName: ''
  };
};

const closeMenuModal = () => {
  menuModal.value.isOpen = false;
};

const handleFileUpload = async (event: any) => {
  const file = event.target.files[0];
  if (file) {
    menuModal.value.fileName = file.name;
    
    try {
      console.log('Uploading file:', file.name, file.type, file.size);
      
      // Upload to server
      const response = await menuApi.uploadImage(file);
      
      console.log('Upload response:', response);
      
      if (response.data.success) {
        menuModal.value.data.foto_makanan = response.data.data.url;
        showNotification('Gambar berhasil diupload!', 'success');
      } else {
        console.error('Upload failed:', response.data.message);
        showNotification('Upload gagal: ' + response.data.message, 'error');
      }
    } catch (error: any) {
      console.error('Upload error:', error);
      console.error('Error response:', error.response?.data);
      const errorMsg = error.response?.data?.message || error.message || 'Unknown error';
      showNotification('Gagal upload gambar: ' + errorMsg, 'error');
    }
  }
};

const saveMenu = async () => {
  processing.value = true;
  
  try {
    const data: any = {
      nama_menu: menuModal.value.data.nama_menu,
      harga: menuModal.value.data.harga,
      stok: menuModal.value.data.stok,
      id_kategori: menuModal.value.data.id_kategori,
      status: 'tersedia'
    };

    // Only include foto_makanan if it was uploaded/changed
    if (menuModal.value.data.foto_makanan) {
      data.foto_makanan = menuModal.value.data.foto_makanan;
    }

    if (menuModal.value.isEdit && menuModal.value.data.id_menu) {
      await menuApi.update(menuModal.value.data.id_menu, data);
      showNotification('Menu berhasil diupdate!', 'success');
    } else {
      await menuApi.create(data);
      showNotification('Menu baru berhasil ditambahkan!', 'success');
    }
    
    await loadMenu();
    closeMenuModal();
  } catch (error) {
    showNotification('Gagal menyimpan menu', 'error');
  } finally {
    processing.value = false;
  }
};

const deleteMenu = async (item: any) => {
  if (!confirm(`Hapus menu "${item.nama_menu}"?`)) return;
  
  try {
    await menuApi.delete(item.id_menu);
    showNotification('Menu berhasil dihapus!', 'success');
    await loadMenu();
  } catch (error) {
    showNotification('Gagal menghapus menu', 'error');
  }
};

const openAddUserModal = () => {
  userModal.value = {
    isOpen: true,
    data: {
      username: '',
      password: '',
      role: 'pelanggan'
    }
  };
};

const closeUserModal = () => {
  userModal.value.isOpen = false;
};

const saveUser = async () => {
  processing.value = true;
  
  try {
    await usersApi.create(userModal.value.data);
    showNotification('User berhasil ditambahkan!', 'success');
    await loadUsers();
    closeUserModal();
  } catch (error) {
    showNotification('Gagal menambahkan user', 'error');
  } finally {
    processing.value = false;
  }
};

const deleteUser = async (user: any) => {
  if (user.username === currentUser?.username) return;
  if (!confirm(`Hapus user "${user.username}"?`)) return;
  
  try {
    await usersApi.delete(user.id_user);
    showNotification('User berhasil dihapus!', 'success');
    await loadUsers();
  } catch (error) {
    showNotification('Gagal menghapus user', 'error');
  }
};

const showNotification = (message: string, type: 'success' | 'error') => {
  notification.value = { message, type };
  setTimeout(() => {
    notification.value = null;
  }, 3000);
};

const handleLogout = () => {
  authService.logout();
  router.push('/login');
};

// Lifecycle
onMounted(() => {
  loadMenu();
  loadUsers();
});
</script>

<style scoped>
.dashboard-container {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  min-height: 100vh;
  color: white;
  padding: 24px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding: 16px 24px;
  background: #1e293b;
  border-radius: 12px;
  border: 1px solid #334155;
}

.brand {
  font-size: 24px;
  font-weight: 700;
}

.highlight {
  color: #8b5cf6;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.user-info {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.username {
  font-weight: 600;
  font-size: 14px;
}

.role {
  font-size: 11px;
  color: #94a3b8;
}

.logout-btn {
  padding: 8px 16px;
  background: transparent;
  border: 1px solid #dc2626;
  color: #dc2626;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
}

.tabs {
  display: flex;
  gap: 12px;
  margin-bottom: 24px;
}

.tab-btn {
  padding: 12px 24px;
  background: #1e293b;
  border: 1px solid #334155;
  color: #94a3b8;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
}

.tab-btn.active {
  background: #8b5cf6;
  color: white;
  border-color: #8b5cf6;
}

.content-section {
  background: #1e293b;
  border-radius: 12px;
  padding: 24px;
  border: 1px solid #334155;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
}

.section-header h2 {
  font-size: 20px;
  font-weight: 700;
  margin: 0 0 4px 0;
}

.section-header p {
  color: #94a3b8;
  font-size: 13px;
  margin: 0;
}

.add-btn {
  padding: 10px 20px;
  background: #8b5cf6;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
}

.table-container {
  overflow-x: auto;
  overflow-y: auto;
  max-height: calc(100vh - 320px);
  -webkit-overflow-scrolling: touch;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  text-align: left;
  padding: 12px;
  border-bottom: 2px solid #334155;
  color: #94a3b8;
  font-weight: 600;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.data-table td {
  padding: 16px 12px;
  border-bottom: 1px solid #334155;
  font-size: 14px;
}

.menu-thumb {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  object-fit: cover;
}

.stock-badge {
  background: #10b981;
  color: white;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.category-badge {
  padding: 5px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.category-badge.makanan {
  background: #8b5cf6;
  color: white;
}

.category-badge.minuman {
  background: #7c3aed;
  color: white;
}

.category-badge.camilan {
  background: #6366f1;
  color: white;
}

.action-btns {
  display: flex;
  align-items: center;
  gap: 8px;
}

.edit-btn,
.delete-btn {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.edit-btn {
  background: #3b82f6;
  color: white;
}

.edit-btn:hover {
  background: #2563eb;
}

.delete-btn {
  background: #dc2626;
  color: white;
}

.delete-btn:hover:not(:disabled) {
  background: #b91c1c;
}

.delete-btn:disabled {
  background: #334155;
  cursor: not-allowed;
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.role-badge {
  padding: 5px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.role-badge.admin {
  background: #3b82f6;
  color: white;
}

.role-badge.kasir {
  background: #10b981;
  color: white;
}

.role-badge.koki {
  background: #f59e0b;
  color: white;
}

.role-badge.owner {
  background: #8b5cf6;
  color: white;
}

.role-badge.pelanggan {
  background: #6366f1;
  color: white;
}

.anda-label {
  font-size: 12px;
  color: #64748b;
  font-style: italic;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

.modal-content {
  background: #1e293b;
  border-radius: 16px;
  width: 90%;
  max-width: 500px;
  border: 1px solid #334155;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #334155;
}

.modal-header h3 {
  font-size: 18px;
  font-weight: 700;
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  color: #94a3b8;
  font-size: 28px;
  cursor: pointer;
  line-height: 1;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-body {
  padding: 24px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  color: #94a3b8;
  font-size: 13px;
  margin-bottom: 8px;
  font-weight: 600;
}

.form-input,
.form-select {
  width: 100%;
  padding: 12px;
  background: #0f172a;
  border: 1px solid #334155;
  border-radius: 8px;
  color: white;
  font-size: 14px;
}

.form-input::placeholder {
  color: #64748b;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.file-input-wrapper {
  position: relative;
}

.file-input {
  display: none;
}

.file-label {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: #0f172a;
  border: 1px solid #334155;
  border-radius: 8px;
  cursor: pointer;
  transition: border-color 0.2s;
}

.file-label:hover {
  border-color: #8b5cf6;
}

.file-label span:first-child {
  background: #334155;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
}

.file-name {
  color: #64748b;
  font-size: 13px;
  flex: 1;
}

.submit-btn {
  width: 100%;
  padding: 14px;
  background: #8b5cf6;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.submit-btn:hover:not(:disabled) {
  background: #7c3aed;
}

.submit-btn:disabled {
  background: #334155;
  cursor: not-allowed;
}

/* Notification */
.notification {
  position: fixed;
  top: 24px;
  right: 24px;
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 8px;
  padding: 16px 20px;
  z-index: 2000;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
}

.notification.success {
  border-left: 4px solid #10b981;
}

.notification.error {
  border-left: 4px solid #dc2626;
}

.close-notif {
  background: none;
  border: none;
  color: #94a3b8;
  font-size: 20px;
  cursor: pointer;
  padding: 0;
  line-height: 1;
}

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .header {
    flex-direction: column;
    gap: 16px;
  }
}
</style>
